<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;

/**
 * The replay viewer's data: the player's processed season games, and one game's deep doc
 * trimmed to what the viewer draws, with site heroes, battletags and talents attached.
 */
class DeepAnalyticsReplayService
{
    private const DISK = 'gcs-deep';

    public function __construct(private GlobalDataService $globalDataService) {}

    /**
     * @param  array{game_types: array<int>, maps: array<int>, hero: int}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function games(int $blizzId, int $region, array $filters): array
    {
        $heroes = $this->globalDataService->getHeroesByID();
        $maps = $this->globalDataService->getAllMapsKeyed();

        return DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->join('replay_deep', 'replay_deep.replayID', '=', 'replay.replayID')
            ->where('player.blizz_id', $blizzId)
            ->where('replay.region', $region)
            ->where('replay.game_date', '>=', config('deep_analytics.cutover'))
            ->where('replay_deep.extract_v', '>=', config('deep_analytics.extract_v'))
            ->when($filters['game_types'] !== [], fn ($q) => $q->whereIn('replay.game_type', $filters['game_types']))
            ->when($filters['maps'] !== [], fn ($q) => $q->whereIn('replay.game_map', $filters['maps']))
            ->when($filters['hero'] > 0, fn ($q) => $q->where('player.hero', $filters['hero']))
            ->orderByDesc('replay.game_date')
            ->get(['replay.replayID', 'replay.game_date', 'replay.game_type', 'replay.game_map', 'replay.game_length', 'player.hero', 'player.winner'])
            ->map(function ($row) use ($heroes, $maps) {
                $hero = $heroes->get((int) $row->hero);
                $map = $maps->get((int) $row->game_map);

                return [
                    'replay_id' => (int) $row->replayID,
                    'game_date' => $row->game_date,
                    'game_type' => (int) $row->game_type,
                    'game_length' => (int) $row->game_length,
                    'winner' => (bool) $row->winner,
                    // Shaped for hero-image-wrapper and map-image-wrapper
                    'hero' => $hero ? ['id' => $hero->id, 'name' => $hero->name, 'short_name' => $hero->short_name] : null,
                    'map' => $map ? ['map_id' => $map->map_id, 'name' => $map->name, 'sanitized_map_name' => $map->sanitized_map_name] : null,
                ];
            })
            ->all();
    }

    /** Null when the account didn't play the game, or it has no current doc. */
    public function replay(int $replayId, int $blizzId, int $region): ?array
    {
        $played = DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->join('replay_deep', 'replay_deep.replayID', '=', 'replay.replayID')
            ->where('player.replayID', $replayId)
            ->where('player.blizz_id', $blizzId)
            ->where('replay.region', $region)
            ->where('replay_deep.extract_v', '>=', config('deep_analytics.extract_v'))
            ->exists();
        if (! $played) {
            return null;
        }

        $replay = Cache::remember('deep_analytics:replay:v'.config('deep_analytics.extract_v').'.3:'.$replayId, now()->addDay(), function () use ($replayId) {
            try {
                $raw = Storage::disk(self::DISK)->get('doc/'.$replayId.'.json.gz');
            } catch (UnableToReadFile) {
                return null;
            }
            // Stored gzipped; take it as is if it arrives already decompressed
            $json = $raw === null ? null : (@gzdecode($raw) ?: $raw);
            $doc = $json === null ? null : json_decode($json, true);
            if (! is_array($doc) || isset($doc['error'])) {
                return null;
            }

            return $this->trim($replayId, $doc);
        });
        if ($replay === null) {
            return null;
        }

        // Mark the viewer's own player; no one's blizz ID leaves the server
        $replay['players'] = array_map(function ($p) use ($blizzId) {
            $p['you'] = $p['blizz_id'] === $blizzId;
            unset($p['blizz_id']);

            return $p;
        }, $replay['players']);

        return $replay;
    }

    private function trim(int $replayId, array $doc): array
    {
        $replay = DB::connection('heroesprofile')->table('replay')->where('replayID', $replayId)->first(['game_date', 'game_type', 'game_map']);
        $rows = DB::connection('heroesprofile')->table('player')
            ->leftJoin('battletags', 'battletags.player_id', '=', 'player.battletag')
            ->where('player.replayID', $replayId)
            ->get(['player.blizz_id', 'player.hero', 'player.team', 'player.winner', 'battletags.battletag', 'battletags.opt_out'])
            ->keyBy('blizz_id');
        $heroes = $this->globalDataService->getHeroesByID();
        $maps = $this->globalDataService->getAllMapsKeyed();

        $players = array_map(function ($p) use ($rows, $heroes) {
            $row = $rows->get($p['blizz_id']);
            $hero = $row ? $heroes->get((int) $row->hero) : null;

            return [
                'idx' => $p['idx'],
                'blizz_id' => (int) $p['blizz_id'],
                'team' => $p['team'],
                'winner' => $row ? (bool) $row->winner : null,
                'hero_id' => $hero?->id,
                'hero' => $hero?->name ?? $p['parser_hero'],
                'short_name' => $hero?->short_name,
                // Name only for players who haven't opted out
                'battletag' => $row && ! $row->opt_out && $row->battletag ? explode('#', $row->battletag)[0] : null,
            ];
        }, $doc['players']);

        return [
            'replay_id' => $replayId,
            'extract_v' => $doc['extract_v'],
            'game_date' => $replay?->game_date,
            'game_map' => $replay ? ($maps->get($replay->game_map)?->name) : null,
            'length_s' => $doc['length_s'],
            'gates_open' => $doc['gates_open'],
            'players' => $players,
            'positions' => $doc['hero_positions'],
            'moves' => $doc['moves'] ?? [],
            'globe_points' => $doc['globe_points'] ?? [],
            'deaths' => $doc['deaths'],
            'structures' => $doc['structures'],
            'units' => array_values(array_filter($doc['units'], fn ($u) => $u['g'] !== 'Hero')),
            'lives' => $this->lives($doc),
            'team_levels' => $doc['team_levels'],
            'xp_breakdown' => $doc['xp_breakdown'],
            'objectives' => $doc['objectives'],
            'camps' => $doc['camps'],
            'talents' => $this->talents($doc['talent_picks'], $players),
            'disconnects' => $doc['disconnects'],
            'minion_deaths' => $doc['minion_deaths'],
            'camera' => $doc['camera'],
            'pings' => $doc['pings'],
        ];
    }

    /**
     * [player, born t, x, y, died t or null]: each hero life, from its spawn point. The parser only
     * tags heroes it knows by name as hero units, so newer heroes (Xal'atath, Deathwing, Hogger and
     * others) have none; their lives come from their positions instead, split at each death.
     */
    private function lives(array $doc): array
    {
        $lives = [];
        foreach ($doc['units'] as $unit) {
            if ($unit['g'] === 'Hero' && $unit['owner'] >= 0) {
                $lives[] = [$unit['owner'], $unit['b'][0], $unit['b'][1], $unit['b'][2], $unit['d'][0] ?? null];
            }
        }

        $known = array_unique(array_column($lives, 0));
        foreach ($doc['hero_positions'] as $track) {
            $p = $track['p'];
            if (in_array($p, $known, true) || $track['pts'] === []) {
                continue;
            }

            $deaths = array_values(array_map(fn ($d) => $d['t'], array_filter($doc['deaths'], fn ($d) => $d['v'] === $p)));
            sort($deaths);
            $points = $track['pts'];
            $start = $points[0];
            foreach ($deaths as $died) {
                if ($start === null || $died < $start[0]) {
                    continue;
                }
                $lives[] = [$p, $start[0], $start[1], $start[2], $died];
                // Back in the game at the first position after the death
                $start = null;
                foreach ($points as $point) {
                    if ($point[0] > $died) {
                        $start = $point;
                        break;
                    }
                }
            }
            if ($start !== null) {
                $lives[] = [$p, $start[0], $start[1], $start[2], null];
            }
        }

        return $lives;
    }

    /** Talent picks with the site's talent record, matched on the hero and the game's talent name. */
    private function talents(array $picks, array $players): array
    {
        $byHero = [];
        foreach ($players as $player) {
            $byHero[$player['idx']] = $player['hero'];
        }

        $known = DB::connection('heroesprofile')->table('heroes_data_talents')
            ->whereIn('hero_name', array_unique(array_values($byHero)))
            ->whereIn('talent_name', array_unique(array_column($picks, 'name')))
            ->get(['talent_id', 'hero_name', 'talent_name', 'title', 'icon', 'hotkey', 'description', 'level', 'status'])
            ->keyBy(fn ($t) => $t->hero_name.'|'.$t->talent_name);

        return array_map(function ($pick) use ($byHero, $known) {
            $talent = $known->get(($byHero[$pick['p']] ?? '').'|'.$pick['name']);

            return [
                'p' => $pick['p'],
                'tier' => $pick['tier'],
                't' => $pick['t'],
                // The site's talent record, as talent-image-wrapper takes it
                'talent' => $talent ? (array) $talent : ['talent_name' => $pick['name'], 'title' => $pick['name'], 'icon' => null, 'hotkey' => '', 'description' => ''],
            ];
        }, $picks);
    }
}
