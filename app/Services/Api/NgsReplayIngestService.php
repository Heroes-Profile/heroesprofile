<?php

namespace App\Services\Api;

use App\Models\Hero;
use App\Models\Map;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * NGS custom-game ingestion.
 *
 * A port of the old API's `NGSAPIController@uploadNGSGames`. The shape is kept —
 * fetch the replay from the NGS bucket, parse it, write a fully-formed row set into
 * the NGS schema — because the NGS tooling on the other end depends on it. What
 * changed: the caller is authenticated by middleware rather than by interpolating
 * their token into SQL, the source URL is pinned by NgsReplayUrlValidation, every
 * write is bound, and the whole row set lands in one transaction.
 *
 * Unlike the main pipeline this parses synchronously and inserts the result itself.
 * It does not share `ReplayUploadService`: different schema, different tables, and a
 * URL rather than an uploaded file.
 */
class NgsReplayIngestService
{
    /** Written when a team supplies no image. */
    private const PLACEHOLDER_IMAGE = 'no-image.png';

    public function __construct(private readonly ReplayParserClient $parser) {}

    /**
     * @param  array<string, mixed>  $input  the validated request
     * @return array<string, mixed> the payload the NGS tooling expects back
     *
     * @throws RuntimeException with a message meant for the caller
     */
    public function ingest(array $input, string $connection): array
    {
        $disk = config('api.ngs.storage_disk', 'gcs-ngs');

        $key = $this->fetchToBucket($input['replay_url'], $disk);

        // The parser reads the object out of the bucket by name, so it has to be the
        // same one the disk just wrote to. Read from the disk config rather than
        // repeated here, or the two drift and the parse 404s.
        $parsed = $this->parser->parse($key, (string) config('filesystems.disks.'.$disk.'.bucket'));

        if (! is_array($parsed) || ! array_key_exists('mode', $parsed)) {
            throw new RuntimeException('Replay Incomplete');
        }

        if ($parsed['mode'] !== 'Custom') {
            throw new RuntimeException('Game mode is not custom.  Invalid replay sent');
        }

        $players = $parsed['players'] ?? [];

        // Both named players must actually appear, or the team-to-side mapping below
        // is guesswork and the game gets filed under the wrong teams.
        if (! $this->playerPresent($players, $input['team_one_player'])
            || ! $this->playerPresent($players, $input['team_two_player'])) {
            throw new RuntimeException('Invalid parameter. A player provided was not in the game');
        }

        $heroes = $this->heroLookups();
        $maps = $this->mapLookups();

        [$t0, $t1] = $this->assignSides($input, $players);

        $mapId = $this->resolveMap($parsed, $maps);

        return DB::connection($connection)->transaction(function () use (
            $input, $connection, $parsed, $players, $heroes, $maps, $t0, $t1, $mapId
        ) {
            $replayID = $this->insertReplay($input, $connection, $parsed, $heroes, $maps, $t0, $t1, $mapId);

            $this->insertDraftOrder($connection, $replayID, $parsed['draft_order'] ?? []);
            $this->insertBans($connection, $replayID, $parsed['bans'] ?? [], $heroes);

            [$t0Id, $t1Id] = $this->resolveTeamIds($input, $connection, $t0, $t1);

            $this->upsertTeams($input, $connection, $players, $t0, $t1, $t0Id, $t1Id);
            $this->insertPlayers($connection, $replayID, $parsed, $players, $heroes, $t0Id, $t1Id);

            $returnData = $this->returnPayload($input, $players, $replayID);

            $this->log($input, $replayID, $returnData);

            return $returnData;
        });
    }

    /**
     * Removes a game and everything hanging off it.
     *
     * The old version left `replay_draft_order` behind — nothing else ever deletes
     * those rows, so every removed game orphaned its draft. Included here.
     */
    public function delete(int $replayID, string $connection): void
    {
        DB::connection($connection)->transaction(function () use ($replayID, $connection) {
            foreach (['replay_draft_order', 'replay_bans', 'scores', 'talents', 'player', 'replay'] as $table) {
                DB::connection($connection)->table($table)->where('replayID', $replayID)->delete();
            }
        });
    }

    /**
     * Copies the replay out of the NGS bucket into our own, which is what the parser
     * reads. The URL has already been pinned to that bucket by the validation rule —
     * this is the one place a caller-supplied URL is fetched server-side.
     */
    private function fetchToBucket(string $url, string $disk): string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH) ?: '';
        $segments = array_values(array_filter(explode('/', $path)));

        // Path-style carries the bucket as the first segment; the virtual-host form
        // has it in the hostname, so every segment there is already the key. Mirrors
        // the split NgsReplayUrlValidation makes.
        if ($host === 's3.amazonaws.com') {
            array_shift($segments);
        }

        $key = implode('/', $segments);

        if ($key === '') {
            throw new RuntimeException('Invalid replay URL');
        }

        $response = (new Client)->get($url, ['http_errors' => false]);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('Could not fetch the replay from that URL');
        }

        Storage::disk($disk)->put($key, $response->getBody()->getContents());

        return $key;
    }

    /** @return array{attributeToName: array<string, string>, lowerToAttribute: array<string, string>, translations: array<string, string>} */
    private function heroLookups(): array
    {
        $heroes = Hero::select('name', 'short_name', 'alt_name', 'attribute_id')->get();

        $attributeToName = [];
        $lowerToAttribute = [];

        foreach ($heroes as $hero) {
            $attributeToName[$hero->attribute_id] = $hero->name;

            $alias = $hero->alt_name !== '' && $hero->alt_name !== null
                ? $hero->alt_name
                : $hero->short_name;

            $lowerToAttribute[mb_strtolower((string) $alias)] = $hero->attribute_id;
        }

        // The parser emits a blank attribute for an empty ban slot.
        $attributeToName[' '] = ' ';
        $lowerToAttribute[' '] = ' ';
        $lowerToAttribute['none'] = ' ';

        return [
            'attributeToName' => $attributeToName,
            'lowerToAttribute' => $lowerToAttribute,
            'translations' => DB::connection('heroesprofile')
                ->table('heroes_translations')
                ->pluck('name', 'translation')
                ->all(),
        ];
    }

    /** @return array{idsByName: array<string, int>, translations: array<string, string>, namesByShort: array<string, string>} */
    private function mapLookups(): array
    {
        return [
            'idsByName' => Map::pluck('map_id', 'name')->all(),
            'translations' => DB::connection('heroesprofile')
                ->table('maps_translations')
                ->pluck('name', 'translation')
                ->all(),
            'namesByShort' => Map::pluck('name', 'short_name')->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $players
     */
    private function playerPresent(array $players, string $battletag): bool
    {
        foreach ($players as $player) {
            if ($this->battletagOf($player) === $battletag) {
                return true;
            }
        }

        // Retried on the name alone. The NGS tooling sends HTML-encoded battletags and
        // the discriminator drifts when a player changes it mid-season.
        $name = html_entity_decode(explode('#', $battletag)[0]);

        foreach ($players as $player) {
            if (($player['battletag_name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Works out which side each named team played, since the request names teams but
     * the replay only knows slots.
     *
     * @return array{0: array<string, ?string>, 1: array<string, ?string>}
     */
    private function assignSides(array $input, array $players): array
    {
        $t0 = ['name' => null, 'image' => null, 'division' => null];
        $t1 = ['name' => null, 'image' => null, 'division' => null];

        $sides = [
            ['player' => $input['team_one_player'], 'name' => $input['team_one_name'], 'image' => $input['team_one_image_url'] ?? null, 'division' => $input['team_one_division']],
            ['player' => $input['team_two_player'], 'name' => $input['team_two_name'], 'image' => $input['team_two_image_url'] ?? null, 'division' => $input['team_two_division']],
        ];

        foreach ($sides as $side) {
            $team = $this->sideOf($players, $side['player']);

            if ($team === null) {
                continue;
            }

            $target = ['name' => $side['name'], 'image' => $side['image'], 'division' => $side['division']];

            if ($team === 0) {
                $t0 = $target;
            } else {
                $t1 = $target;
            }
        }

        if ($t0['name'] === null || $t1['name'] === null) {
            throw new RuntimeException('Invalid parameter. A player provided was not in the game');
        }

        return [$t0, $t1];
    }

    /** Which slot a named player occupied, matching on the full battletag then the name. */
    private function sideOf(array $players, string $battletag): ?int
    {
        foreach ($players as $player) {
            if ($this->battletagOf($player) === $battletag) {
                return (int) $player['team'];
            }
        }

        $name = html_entity_decode(explode('#', $battletag)[0]);

        foreach ($players as $player) {
            if (($player['battletag_name'] ?? null) === $name) {
                return (int) $player['team'];
            }
        }

        return null;
    }

    private function battletagOf(array $player): string
    {
        return ($player['battletag_name'] ?? '').'#'.($player['battletag_id'] ?? '');
    }

    /** Map id for the parsed map, by name, then translation, then short name. */
    private function resolveMap(array $parsed, array $maps): int
    {
        $name = $parsed['map'] ?? '';

        if (array_key_exists($name, $maps['idsByName'])) {
            return (int) $maps['idsByName'][$name];
        }

        if (array_key_exists($name, $maps['translations'])) {
            $translated = $maps['translations'][$name];

            if (array_key_exists($translated, $maps['idsByName'])) {
                return (int) $maps['idsByName'][$translated];
            }
        }

        $short = $parsed['map_short'] ?? '';

        if (array_key_exists($short, $maps['namesByShort'])) {
            $full = $maps['namesByShort'][$short];

            if (array_key_exists($full, $maps['idsByName'])) {
                return (int) $maps['idsByName'][$full];
            }
        }

        throw new RuntimeException('Invalid Map');
    }

    private function insertReplay(
        array $input,
        string $connection,
        array $parsed,
        array $heroes,
        array $maps,
        array $t0,
        array $t1,
        int $mapId
    ): int {
        $mapBans = [];

        foreach (['team_one_map_ban_1', 'team_one_map_ban_2', 'team_two_map_ban_1', 'team_two_map_ban_2'] as $field) {
            $ban = $input[$field];

            if (! array_key_exists($ban, $maps['idsByName'])) {
                throw new RuntimeException('Invalid map ban: '.$ban);
            }

            $mapBans[$field] = $maps['idsByName'][$ban];
        }

        return (int) DB::connection($connection)->table('replay')->insertGetId([
            'tournament' => $input['tournament'],
            'season' => $input['season'],
            'division_0' => $t0['division'],
            'division_1' => $t1['division'],
            'team_0_name' => $t0['name'],
            'team_1_name' => $t1['name'],
            'round' => $input['round'],
            'game' => $input['game'],
            'first_pick' => $this->firstPick($parsed, $heroes),
            'game_date' => date('Y-m-d H:i:s', strtotime((string) $parsed['date'])),
            'game_length' => $this->toSeconds((string) $parsed['length']),
            'game_map' => $mapId,
            'team_0_map_ban' => $mapBans['team_one_map_ban_1'],
            'team_0_map_ban_2' => $mapBans['team_one_map_ban_2'],
            'team_1_map_ban' => $mapBans['team_two_map_ban_1'],
            'team_1_map_ban_2' => $mapBans['team_two_map_ban_2'],
            'game_version' => $parsed['version'],
            'region' => $parsed['region'],
            'date_added' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Which side picked first, worked out from whose ban list holds the first pick. */
    private function firstPick(array $parsed, array $heroes): int
    {
        $draft = $parsed['draft_order'] ?? [];
        $bans = $parsed['bans'] ?? [];

        if (empty($draft)) {
            return -1;
        }

        $first = $heroes['lowerToAttribute'][mb_strtolower((string) $draft[0]['HeroSelected'])] ?? null;

        foreach ($bans as $team => $teamBans) {
            foreach ($teamBans as $ban) {
                if ($ban === $first) {
                    return (int) $team;
                }
            }
        }

        return -1;
    }

    private function insertDraftOrder(string $connection, int $replayID, array $draft): void
    {
        foreach ($draft as $index => $pick) {
            // Type 2 is the pick confirmation, which duplicates the pick itself.
            if ((int) $pick['PickType'] === 2) {
                continue;
            }

            DB::connection($connection)->table('replay_draft_order')->insert([
                'replayID' => $replayID,
                'type' => $pick['PickType'],
                'pick_number' => $index,
                'player_slot' => $pick['SelectedPlayerSlotId'],
                'hero' => $pick['HeroSelected'] === 'NONE' ? 0 : $this->heroIdByAlias($pick['HeroSelected']),
            ]);
        }
    }

    private function insertBans(string $connection, int $replayID, array $bans, array $heroes): void
    {
        foreach ($bans as $team => $teamBans) {
            foreach ($teamBans as $ban) {
                if ($ban === null) {
                    continue;
                }

                $name = $heroes['attributeToName'][$ban] ?? null;

                if ($name === null) {
                    continue;
                }

                DB::connection($connection)->table('replay_bans')->insert([
                    'replayID' => $replayID,
                    'team' => $team,
                    'hero' => $this->heroIdByName($name),
                ]);
            }
        }
    }

    /**
     * Existing team rows are reused; new ones take the next id.
     *
     * The old version derived that id from `MAX(team_id) + 1` outside any transaction,
     * so two uploads landing together could claim the same id. Held inside the
     * transaction here, which is as far as this goes without an auto-increment.
     *
     * @return array{0: int, 1: int}
     */
    private function resolveTeamIds(array $input, string $connection, array $t0, array $t1): array
    {
        $find = fn (array $team) => DB::connection($connection)
            ->table('teams')
            ->where('tournament', $input['tournament'])
            ->where('season', $input['season'])
            ->where('division', $team['division'])
            ->where('team_name', $team['name'])
            ->value('team_id');

        $t0Id = $find($t0);
        $t1Id = $find($t1);

        $next = null;

        if ($t0Id === null || $t1Id === null) {
            $next = (int) DB::connection($connection)->table('teams')->max('team_id');
        }

        if ($t0Id === null) {
            $t0Id = ++$next;
        }

        if ($t1Id === null) {
            $t1Id = ++$next;
        }

        return [(int) $t0Id, (int) $t1Id];
    }

    private function upsertTeams(array $input, string $connection, array $players, array $t0, array $t1, int $t0Id, int $t1Id): void
    {
        foreach ([[$t0, $t0Id], [$t1, $t1Id]] as [$team, $teamId]) {
            $image = $team['image'] ?: self::PLACEHOLDER_IMAGE;

            $exists = DB::connection($connection)->table('teams')->where('team_id', $teamId)->exists();

            if ($exists) {
                // Only the image: a rename would detach the row from games already filed
                // against the old name.
                DB::connection($connection)->table('teams')->where('team_id', $teamId)->update(['image' => $image]);

                continue;
            }

            DB::connection($connection)->table('teams')->insert([
                'team_id' => $teamId,
                'tournament' => $input['tournament'],
                'season' => $input['season'],
                'division' => $team['division'],
                'team_name' => $team['name'],
                'image' => $image,
            ]);
        }
    }

    private function insertPlayers(string $connection, int $replayID, array $parsed, array $players, array $heroes, int $t0Id, int $t1Id): void
    {
        foreach ($players as $player) {
            $teamId = (int) $player['team'] === 0 ? $t0Id : $t1Id;

            $playerId = $this->battletagId($connection, $player, $parsed['region'], $teamId);

            $heroName = $heroes['translations'][$player['hero']] ?? $player['hero'];

            DB::connection($connection)->table('player')->insert([
                'replayID' => $replayID,
                'blizz_id' => $player['blizz_id'],
                'battletag' => $playerId,
                'hero' => $this->heroIdByName($heroName),
                'hero_level' => $player['hero_level'],
                'mastery_tier' => $this->masteryTier($player, $heroes),
                'team_name' => $teamId,
                'team' => $player['team'],
                'winner' => ($player['winner'] === 'true' || $player['winner'] === true) ? 1 : 0,
                'party' => $player['party'],
            ]);

            $this->insertTalents($connection, $replayID, $playerId, $heroName, $player['talents'] ?? []);
            $this->insertScore($connection, $replayID, $playerId, $player['score'] ?? []);
        }
    }

    /** Existing battletag row for this team, created if it is their first game for them. */
    private function battletagId(string $connection, array $player, string $region, int $teamId): int
    {
        $battletag = $this->battletagOf($player);

        $existing = DB::connection($connection)->table('battletags')
            ->where('team_id', $teamId)
            ->where('blizz_id', $player['blizz_id'])
            ->where('battletag', $battletag)
            ->where('region', $region)
            ->value('player_id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::connection($connection)->table('battletags')->insertGetId([
            'team_id' => $teamId,
            'blizz_id' => $player['blizz_id'],
            'battletag' => $battletag,
            'region' => $region,
        ]);
    }

    private function masteryTier(array $player, array $heroes): int
    {
        foreach ($player['hero_level_taunt'] ?? [] as $taunt) {
            $name = $heroes['attributeToName'][$taunt['HeroAttributeId']] ?? null;

            if ($name === $player['hero']) {
                return (int) $taunt['TierLevel'];
            }
        }

        return 0;
    }

    private function insertTalents(string $connection, int $replayID, int $playerId, string $hero, array $talents): void
    {
        $tiers = ['level_one', 'level_four', 'level_seven', 'level_ten', 'level_thirteen', 'level_sixteen', 'level_twenty'];

        $row = ['replayID' => $replayID, 'battletag' => $playerId];

        // A null first entry means the parser found no talent data at all; the row is
        // still written so the game has a complete set.
        $blank = ($talents[0] ?? null) === null;

        foreach ($tiers as $index => $column) {
            $row[$column] = ($blank || ! isset($talents[$index]))
                ? 0
                : $this->talentId($hero, $talents[$index]);
        }

        DB::connection($connection)->table('talents')->insert($row);
    }

    private function talentId(string $hero, string $talent): int
    {
        $id = DB::connection('heroesprofile')
            ->table('heroes_data_talents')
            ->where('hero_name', $hero)
            ->where('talent_name', $talent)
            ->value('talent_id');

        if ($id !== null) {
            return (int) $id;
        }

        // The parser can hand back a localised hero name.
        $translated = DB::connection('heroesprofile')
            ->table('heroes_translations')
            ->where('translation', $hero)
            ->orWhere('translation', mb_strtolower($hero))
            ->value('name');

        if ($translated === null) {
            return 0;
        }

        return (int) DB::connection('heroesprofile')
            ->table('heroes_data_talents')
            ->where('hero_name', $translated)
            ->where('talent_name', $talent)
            ->value('talent_id');
    }

    private function insertScore(string $connection, int $replayID, int $playerId, array $score): void
    {
        $direct = [
            'level' => 'Level',
            'kills' => 'SoloKills',
            'assists' => 'Assists',
            'takedowns' => 'Takedowns',
            'deaths' => 'Deaths',
            'highest_kill_streak' => 'HighestKillStreak',
            'hero_damage' => 'HeroDamage',
            'siege_damage' => 'SiegeDamage',
            'structure_damage' => 'StructureDamage',
            'minion_damage' => 'MinionDamage',
            'creep_damage' => 'CreepDamage',
            'summon_damage' => 'SummonDamage',
            'healing' => 'Healing',
            'self_healing' => 'SelfHealing',
            'damage_taken' => 'DamageTaken',
            'experience_contribution' => 'ExperienceContribution',
            'town_kills' => 'TownKills',
            'merc_camp_captures' => 'MercCampCaptures',
            'watch_tower_captures' => 'WatchTowerCaptures',
            'meta_experience' => 'MetaExperience',
            'protection_allies' => 'ProtectionGivenToAllies',
            'silencing_enemies' => 'TimeSilencingEnemyHeroes',
            'rooting_enemies' => 'TimeRootingEnemyHeroes',
            'stunning_enemies' => 'TimeStunningEnemyHeroes',
            'clutch_heals' => 'ClutchHealsPerformed',
            'escapes' => 'EscapesPerformed',
            'vengeance' => 'VengeancesPerformed',
            'outnumbered_deaths' => 'OutnumberedDeaths',
            'teamfight_escapes' => 'TeamfightEscapesPerformed',
            'teamfight_healing' => 'TeamfightHealingDone',
            'teamfight_damage_taken' => 'TeamfightDamageTaken',
            'teamfight_hero_damage' => 'TeamfightHeroDamage',
            'multikill' => 'Multikill',
            'physical_damage' => 'PhysicalDamage',
            'spell_damage' => 'SpellDamage',
            'regen_globes' => 'RegenGlobes',
        ];

        $row = ['replayID' => $replayID, 'battletag' => $playerId];

        foreach ($direct as $column => $field) {
            $row[$column] = $this->orZero($score[$field] ?? null);
        }

        // Both arrive as durations, not counts.
        $row['time_cc_enemy_heroes'] = $this->toSeconds((string) ($score['TimeCCdEnemyHeroes'] ?? ''));
        $row['time_spent_dead'] = $this->toSeconds((string) ($score['TimeSpentDead'] ?? ''));

        DB::connection($connection)->table('scores')->insert($row);
    }

    /** Hero id for a name, falling back through the translation table as the old one did. */
    private function heroIdByName(string $name): int
    {
        $id = Hero::where('name', $name)->value('id');

        if ($id !== null) {
            return (int) $id;
        }

        $translated = DB::connection('heroesprofile')
            ->table('heroes_translations')
            ->where('translation', $name)
            ->orWhere('translation', mb_strtolower($name))
            ->value('name');

        return $translated === null ? 0 : (int) Hero::where('name', $translated)->value('id');
    }

    /** Hero id for the alt or short name the draft uses. */
    private function heroIdByAlias(string $alias): int
    {
        return (int) Hero::where('alt_name', $alias)
            ->orWhere('short_name', mb_strtolower($alias))
            ->value('id');
    }

    /** "mm:ss" or "hh:mm:ss" to seconds. */
    private function toSeconds(string $value): int
    {
        if ($value === '') {
            return 0;
        }

        $padded = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", '00:$1:$2', $value);

        sscanf((string) $padded, '%d:%d:%d', $hours, $minutes, $seconds);

        return ((int) $hours * 3600) + ((int) $minutes * 60) + (int) $seconds;
    }

    private function orZero(mixed $value): mixed
    {
        return ($value === '' || $value === null) ? 0 : $value;
    }

    /** @return array<string, mixed> */
    private function returnPayload(array $input, array $players, int $replayID): array
    {
        $teams = [[], []];

        foreach ($players as $player) {
            $side = (int) $player['team'];

            if ($side !== 0 && $side !== 1) {
                continue;
            }

            $teams[$side][$this->battletagOf($player).' Profile'] =
                'https://www.heroesprofile.com/Esports/NGS/Player/'.$player['battletag_name'].'/'.$player['blizz_id'];
        }

        return [
            'url' => 'https://www.heroesprofile.com/Esports/NGS/Match/Single/'.$replayID,
            $input['team_one_name'] => $teams[0],
            $input['team_two_name'] => $teams[1],
        ];
    }

    private function log(array $input, int $replayID, array $returnData): void
    {
        DB::connection('heroesprofile_logs')->table('ngs_replays_sent')->insert([
            'replayID' => $replayID,
            'dev_prod' => $input['mode'],
            // The old column held the caller's raw token. Keys are stored hashed here
            // and the plaintext is never recoverable, so this records which key rather
            // than the secret itself.
            'api_key' => $input['api_key_reference'],
            'tournament' => $input['tournament'],
            'season' => $input['season'],
            'division_0' => $input['team_one_division'],
            'division_1' => $input['team_two_division'],
            'replay_url' => $input['replay_url'],
            'round' => $input['round'],
            'game' => $input['game'],
            'team_one_name' => $input['team_one_name'],
            'team_one_player' => $input['team_one_player'],
            'team_one_map_ban_1' => $input['team_one_map_ban_1'],
            'team_one_map_ban_2' => $input['team_one_map_ban_2'],
            'team_one_image_url' => $input['team_one_image_url'] ?? null,
            'team_two_name' => $input['team_two_name'],
            'team_two_player' => $input['team_two_player'],
            'team_two_map_ban_1' => $input['team_two_map_ban_1'],
            'team_two_map_ban_2' => $input['team_two_map_ban_2'],
            'team_two_image_url' => $input['team_two_image_url'] ?? null,
            'return_data' => json_encode($returnData),
            'date_sent' => date('Y-m-d H:i:s'),
        ]);
    }
}
