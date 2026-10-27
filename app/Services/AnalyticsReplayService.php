<?php

namespace App\Services;

use App\Models\AbilityLink;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToReadFile;

/**
 * The replay viewer's data: the player's processed season games, and one game's extract doc
 * trimmed to what the viewer draws, with site heroes, battletags and talents attached.
 */
class AnalyticsReplayService
{
    private const DISK = 'gcs-analytics';

    // Stop / hold: the only command that doesn't dismount
    private const STOP_LINK = 23;

    /**
     * Abilities that move a hero further than it could walk, by ability_links' ability_id: whether it
     * disappears and reappears ('teleport') or crosses the map in a straight line ('flight'), roughly how
     * many seconds after the press it goes, and a flight's speed in map units per second. The timings are
     * approximate; the hero's next exact position fixes where it really ended up.
     */
    private const TRAVEL = [
        'Hearthstone' => ['teleport', 6, 0],
        'AbathurDeepTunnel' => ['teleport', 1, 0],
        'DehakaBrushstalker' => ['teleport', 2, 0],
        'BrightwingPhaseShift' => ['teleport', 1.5, 0],
        'ZagaraExitNydusWorm' => ['teleport', 0.5, 0],
        'DeathwingDragonflight' => ['teleport', 2, 0],
        'L90ETCStageDive' => ['teleport', 1.5, 0],
        'FalstadFlight' => ['flight', 1, 25],
        'JunkratRocketRide' => ['flight', 1.5, 30],
        'IllidanTheHunt' => ['flight', 0.25, 30],
    ];

    public function __construct(
        private GlobalDataService $globalDataService,
        private AnalyticsService $analytics,
    ) {}

    /** A season game: the only ones archived and processed. */
    public function isSeasonGame(int $replayId): bool
    {
        return DB::connection('heroesprofile')->table('replay')
            ->where('replayID', $replayId)
            ->where('game_date', '>=', config('analytics.cutover'))
            ->exists();
    }

    /** Processed at the current extract version and loaded into BigQuery. */
    public function isReady(int $replayId): bool
    {
        return DB::connection('heroesprofile')->table('replay_deep')
            ->where('replayID', $replayId)
            ->where('extract_v', '>=', config('analytics.extract_v'))
            ->exists();
    }

    /**
     * Queues one game's analytics parse, for the viewer opened on a game nobody has processed yet. A
     * second request inside the lock window queues nothing, so refreshing costs nothing.
     */
    public function process(int $replayId): bool
    {
        if ($this->isReady($replayId) || ! Cache::add('analytics:replay_request:'.$replayId, 1, config('analytics.request_lock_seconds'))) {
            return false;
        }

        return $this->analytics->fanOut([$replayId]) > 0;
    }

    /**
     * The player's processed season games, shaped like Player Match History rows so the same
     * game-summary-box cards and table render them.
     *
     * @param  array{game_types: array<int>, maps: array<int>, hero: int}  $filters
     * @return array<int, array<string, mixed>>
     */
    public function games(int $blizzId, int $region, array $filters): array
    {
        $heroes = $this->globalDataService->getHeroes()->keyBy('id');
        $maps = $this->globalDataService->getAllMapsKeyed();
        $gameTypes = $this->globalDataService->getGameTypeIDtoString();
        $talentData = $this->globalDataService->getAllTalentsKeyed();
        $levels = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        // Every season game, processed or not, so the Games tab can offer to process the rest
        return DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->leftJoin('replay_deep', function ($join) {
                $join->on('replay_deep.replayID', '=', 'replay.replayID')
                    ->where('replay_deep.extract_v', '>=', config('analytics.extract_v'));
            })
            ->leftJoin('talents', function ($join) {
                $join->on('talents.replayID', '=', 'replay.replayID')
                    ->on('talents.battletag', '=', 'player.battletag');
            })
            ->where('player.blizz_id', $blizzId)
            ->where('replay.region', $region)
            ->where('replay.game_date', '>=', config('analytics.cutover'))
            ->when($filters['game_types'] !== [], fn ($q) => $q->whereIn('replay.game_type', $filters['game_types']))
            ->when($filters['maps'] !== [], fn ($q) => $q->whereIn('replay.game_map', $filters['maps']))
            ->when($filters['hero'] > 0, fn ($q) => $q->where('player.hero', $filters['hero']))
            ->orderByDesc('replay.game_date')
            ->get(array_merge([
                'replay.replayID', 'replay.game_date', 'replay.game_type', 'replay.game_map', 'player.hero', 'player.winner',
                'player.player_conservative_rating', 'player.player_change', 'player.hero_conservative_rating', 'player.hero_change',
                'player.role_conservative_rating', 'player.role_change', 'replay_deep.replayID as processed_id',
            ], array_map(fn ($level) => 'talents.'.$level, $levels)))
            ->map(function ($row) use ($heroes, $maps, $gameTypes, $talentData, $levels) {
                $game = [
                    'replayID' => (int) $row->replayID,
                    'game_date' => $row->game_date,
                    'game_type' => $gameTypes[$row->game_type] ?? null,
                    'game_map' => $maps->get((int) $row->game_map),
                    'hero_id' => (int) $row->hero,
                    'hero' => $heroes->get((int) $row->hero),
                    'winner' => (int) $row->winner,
                    'player_mmr' => round(1800 + (40 * $row->player_conservative_rating)),
                    'player_change' => $row->player_change,
                    'hero_mmr' => round(1800 + (40 * $row->hero_conservative_rating)),
                    'hero_change' => $row->hero_change,
                    'role_mmr' => round(1800 + (40 * $row->role_conservative_rating)),
                    'role_change' => $row->role_change,
                    'processed' => $row->processed_id !== null,
                ];
                foreach ($levels as $level) {
                    $game[$level] = $row->{$level} ? ($talentData[$row->{$level}] ?? null) : null;
                }

                return $game;
            })
            ->all();
    }

    /** Any season game; null until it has been processed and loaded. */
    public function replay(int $replayId, int $blizzId): ?array
    {
        if (! $this->isReady($replayId)) {
            return null;
        }

        $replay = $this->trimmed($replayId);
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

    /**
     * Each player's index in the viewer, by blizz ID, so other data about the game can be matched to
     * the viewer's players without blizz IDs leaving the server. Empty when the game isn't ready.
     *
     * @return array<int, int>
     */
    public function playerIndexes(int $replayId): array
    {
        $replay = $this->isReady($replayId) ? $this->trimmed($replayId) : null;

        return $replay === null ? [] : array_column($replay['players'], 'idx', 'blizz_id');
    }

    /** @return array<string, mixed>|null */
    private function trimmed(int $replayId): ?array
    {
        return Cache::remember('analytics:replay:v'.config('analytics.extract_v').'.5:'.$replayId, now()->addDay(), function () use ($replayId) {
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
            'unit_tracks' => $doc['unit_tracks'] ?? [],
            'selections' => $doc['selections'] ?? [],
            'team_levels' => $doc['team_levels'],
            'xp_breakdown' => $doc['xp_breakdown'],
            'objectives' => $doc['objectives'],
            'camps' => $doc['camps'],
            'talents' => $this->talents($doc['talent_picks'], $players),
            'disconnects' => $doc['disconnects'],
            'minion_deaths' => $doc['minion_deaths'],
            'mounts' => $this->mounts($doc),
            'travel' => $this->travel($doc),
            'symbiotes' => $doc['symbiotes'] ?? [],
            'camera' => $doc['camera'],
            'pings' => $doc['pings'],
        ];
    }

    /**
     * [player, born t, x, y, died t or null, unit id?]: each hero life, from its spawn point (the unit id only
     * for a player with several heroes at once). The parser only
     * tags heroes it knows by name as hero units, so newer heroes (Xal'atath, Deathwing, Hogger and
     * others) have none; their lives come from their positions instead, split at each death.
     */
    private function lives(array $doc): array
    {
        $lives = [];
        // Players with several heroes at once (the Lost Vikings): a life per hero unit, with its id
        $multi = [];
        foreach ($doc['unit_tracks'] ?? [] as $track) {
            $multi[$track['p']] = true;
            $lives[] = [$track['p'], $track['b'][0], $track['b'][1], $track['b'][2], $track['d'][0] ?? null, $track['id']];
        }
        foreach ($doc['units'] as $unit) {
            if (isset($multi[$unit['owner']])) {
                continue;
            }
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

    /**
     * [t, player, 1] for each mount press and [t, player, 0] for each other cast (which dismounts), so the
     * viewer can walk mounted heroes faster. Stop doesn't dismount. Mount abilities come from the game
     * build's ability links: the generic Mount and each hero's own mount slot.
     */
    private function mounts(array $doc): array
    {
        $mountLinks = array_flip($this->mountLinks(isset($doc['build']) ? (int) $doc['build'] : null));
        $commands = $doc['commands'] ?? [];
        usort($commands, fn ($a, $b) => $a[0] <=> $b[0]);
        $out = [];
        $last = [];
        foreach ($commands as [$t, $player, $link]) {
            if ($link === self::STOP_LINK) {
                continue;
            }
            $on = isset($mountLinks[$link]) ? 1 : 0;
            // A dismount only matters after a mount press
            if ($on === 0 && ($last[$player] ?? 0) === 0) {
                continue;
            }
            $out[] = [$t, $player, $on];
            $last[$player] = $on;
        }

        return $out;
    }

    /**
     * [t, player, kind, delay, speed, x, y] for each press of a TRAVEL ability; x and y are its target
     * point, or -1 when it targets a unit or nothing (the viewer then uses the next exact position).
     */
    private function travel(array $doc): array
    {
        $links = $this->travelLinks(isset($doc['build']) ? (int) $doc['build'] : null);
        $out = [];
        foreach ($doc['commands'] ?? [] as [$t, $player, $link, , $x, $y]) {
            $kind = self::TRAVEL[$links[$link] ?? ''] ?? null;
            if ($kind !== null) {
                $out[] = [$t, $player, $kind[0], $kind[1], $kind[2], $x, $y];
            }
        }
        usort($out, fn ($a, $b) => $a[0] <=> $b[0]);

        return $out;
    }

    /** The build's ability links mapping, from the newest mapped build at or before it. */
    private function mappedBuild(?int $build): ?int
    {
        $builds = AbilityLink::query()->distinct()->orderBy('build')->pluck('build')->all();
        if ($builds === []) {
            return null;
        }
        $mapped = $builds[0];
        foreach ($builds as $candidate) {
            if ($build !== null && $candidate <= $build) {
                $mapped = $candidate;
            }
        }

        return $mapped;
    }

    /** Mount ability links for a build. Travel abilities in the Mount slot (Deep Tunnel, Flight...) aren't mounts. */
    private function mountLinks(?int $build): array
    {
        return Cache::remember('analytics:mount_links:v2:'.($build ?? 0), now()->addDay(), function () use ($build) {
            $mapped = $this->mappedBuild($build);
            if ($mapped === null) {
                return [];
            }

            return AbilityLink::where('build', $mapped)
                ->where(fn ($q) => $q->where('slot', 'Mount')->orWhere('ability_id', 'Mount'))
                ->whereNotIn('ability_id', array_keys(self::TRAVEL))
                ->pluck('abil_link')->map(fn ($link) => (int) $link)->all();
        });
    }

    /** abil_link => ability_id for the TRAVEL abilities in a build. */
    private function travelLinks(?int $build): array
    {
        return Cache::remember('analytics:travel_links:'.($build ?? 0), now()->addDay(), function () use ($build) {
            $mapped = $this->mappedBuild($build);
            if ($mapped === null) {
                return [];
            }

            return AbilityLink::where('build', $mapped)
                ->whereIn('ability_id', array_keys(self::TRAVEL))
                ->pluck('ability_id', 'abil_link')->mapWithKeys(fn ($id, $link) => [(int) $link => $id])->all();
        });
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
