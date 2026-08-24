<?php

namespace App\Services\Api;

use App\Models\GameType;
use App\Models\Map;
use App\Services\GlobalDataService;
use Illuminate\Support\Facades\DB;

/**
 * A page of replays, for callers building their own copy of the data.
 *
 * The one endpoint in the public API with a query of its own rather than a
 * wrapper around a site controller: nothing on the site enumerates replays, and
 * fetching them one at a time would cost a call each — a caller would exhaust any
 * tier long before it caught up.
 *
 * Paged by replay id rather than by offset. Ids only ever increase, so a cursor
 * cannot skip or repeat a row when new replays land between calls, which is
 * exactly what `LIMIT/OFFSET` would do here.
 */
class ReplayIndexService
{
    /** What the old API returned per page, kept so porting is a rename. */
    public const PAGE_SIZE = 1000;

    public function __construct(private readonly GlobalDataService $globalDataService) {}

    /**
     * @param  array<string, mixed>  $filters  after, timeframe_type, timeframe, game_type, game_map
     * @return array<string, mixed>
     */
    public function page(array $filters): array
    {
        $after = (int) ($filters['after'] ?? 0);

        $gameTypes = $this->gameTypeIds($filters['game_type'] ?? null);
        $gameMaps = $this->gameMapIds($filters['game_map'] ?? null);

        $rows = DB::table('replay')
            ->join('replay_fingerprints', 'replay_fingerprints.replayID', '=', 'replay.replayID')
            ->select([
                'replay.replayID',
                'replay.region',
                'replay.game_type',
                'replay.game_version',
                'replay.game_map',
                'replay.game_date',
                'replay.date_added',
                'replay_fingerprints.fingerprint',
                'replay_fingerprints.parsed',
                'replay_fingerprints.valid',
                'replay_fingerprints.deleted',
            ])
            // Exclusive, unlike the old `min_id`: a caller passes back the last id
            // it saw, and gets what follows rather than that row again.
            ->where('replay.replayID', '>', $after)
            ->where('replay_fingerprints.valid', 1)
            ->when($gameTypes !== null, fn ($query) => $query->whereIn('replay.game_type', $gameTypes))
            ->when($gameMaps !== null, fn ($query) => $query->whereIn('replay.game_map', $gameMaps))
            ->when(
                ($filters['timeframe'] ?? null) !== null,
                fn ($query) => ($filters['timeframe_type'] ?? 'minor') === 'minor'
                    ? $query->where('replay.game_version', $filters['timeframe'])
                    : $query->where('replay.game_version', 'LIKE', $filters['timeframe'].'%')
            )
            ->orderBy('replay.replayID')
            ->limit(self::PAGE_SIZE)
            ->get();

        $gameTypeNames = $this->globalDataService->getGameTypeIDtoString();
        $maps = Map::select('map_id', 'name')->get()->keyBy('map_id');

        $replays = $rows->map(fn ($row) => [
            'replayID' => (int) $row->replayID,
            'region' => (int) $row->region,
            'fingerprint' => $row->fingerprint,
            'game_type' => $gameTypeNames[$row->game_type]['name'] ?? null,
            'game_version' => $row->game_version,
            'game_map' => $maps[$row->game_map]->name ?? null,
            'game_date' => $row->game_date,
            'parsed' => (int) $row->parsed,
            'deleted' => (int) $row->deleted,
            // Replay files are purged on a rolling window, so availability is a
            // question of age. The old API hardcoded a replay id as the cutoff,
            // which went stale the day after it was written.
            'downloadable' => $row->deleted != 1
                && $this->globalDataService->replayFileIsRetained($row->date_added),
        ])->values();

        $last = $replays->last();

        return [
            'replays' => $replays,
            // Where to resume. Null once a page comes back short, which is the
            // caller's signal that it has caught up.
            'next_after' => $replays->count() === self::PAGE_SIZE ? $last['replayID'] : null,
            'max_replay_id' => (int) DB::table('replay')->max('replayID'),
        ];
    }

    /** @return array<int, int>|null null meaning every type */
    private function gameTypeIds(mixed $shortNames): ?array
    {
        if (! is_string($shortNames) || $shortNames === '') {
            return null;
        }

        $ids = GameType::whereIn('short_name', explode(',', $shortNames))->pluck('type_id')->all();

        return $ids === [] ? null : $ids;
    }

    /** @return array<int, int>|null null meaning every map */
    private function gameMapIds(mixed $names): ?array
    {
        if (! is_string($names) || $names === '') {
            return null;
        }

        $ids = Map::whereIn('name', explode(',', $names))->pluck('map_id')->all();

        return $ids === [] ? null : $ids;
    }
}
