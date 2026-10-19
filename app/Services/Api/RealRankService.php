<?php

namespace App\Services\Api;

use App\Models\Battletag;
use App\Models\BattletagRealRank;
use App\Models\SeasonDate;
use App\Models\UploadedReplayData;
use App\Models\UploadedReplayPlayer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Real Storm League ranks, read off the loading screen by the uploader's opt-in rank reader and sent once
 * the game's replay is on the site (POST ranks, signed - see VerifyUploaderSignature).
 *
 * A rank is only kept for a player who was in that replay: the upload-time parse's players
 * (uploaded_replay_players), or once the full parse has run, `player`. Each player's ranks are kept as
 * periods (battletags_real_ranks): the same rank again in the same season extends the latest period; a
 * different rank, or a new season, starts a new one. Ranks are stored as read - how far to trust one is
 * worked out when it's shown.
 */
class RealRankService
{
    /**
     * league_tiers ids, by the names the uploader sends: 1 bronze to 6 master (7 is "all", and wood never
     * shows on a loading screen). Grandmaster - the top of Master - is Master with its flag set and its
     * place on the ladder.
     */
    public const LEAGUES = [
        'Bronze' => 1, 'Silver' => 2, 'Gold' => 3, 'Platinum' => 4, 'Diamond' => 5, 'Master' => 6, 'Grandmaster' => 6,
    ];

    /** Storm League's game_types id. */
    private const STORM_LEAGUE = 5;

    /** How alike the name or hero on the card must have read to the player's for the card to count as theirs. */
    public const MIN_CONFIDENCE = 0.7;

    /**
     * Stores what's acceptable of $ranks for $replayID and says how it went: `stored` ranks, `skipped` ones
     * (with why), or null when the replay can't be checked yet at all.
     *
     * @param  array<int, array<string, mixed>>  $ranks
     * @return array{stored: int, skipped: array<int, string>}|null
     */
    public function store(int $replayID, array $ranks, string $ip, ?string $uploaderVersion): ?array
    {
        $players = $this->replayPlayers($replayID);
        $gameDate = $this->gameDate($replayID);
        if ($players === [] || $gameDate === null) {
            return null;
        }

        $reporter = hash_hmac('sha256', $ip, (string) config('app.key'));
        $stored = 0;
        $skipped = [];
        foreach ($ranks as $i => $rank) {
            $reason = $this->storeOne($replayID, $gameDate, $players, $rank, $reporter, $uploaderVersion);
            if ($reason === null) {
                $stored++;
            } else {
                $skipped[$i] = $reason;
            }
        }

        return ['stored' => $stored, 'skipped' => $skipped];
    }

    /**
     * @param  array<string, true>  $players
     * @param  array<string, mixed>  $rank
     * @return string|null Why it wasn't stored, or null when it was.
     */
    private function storeOne(int $replayID, Carbon $gameDate, array $players, array $rank, string $reporter, ?string $version): ?string
    {
        $battletag = $rank['battletag'] ?? null;
        $region = $rank['region'] ?? null;
        $league = $rank['league'] ?? null;
        if (! is_string($battletag) || ! is_int($region) || ! is_string($league)) {
            return 'malformed';
        }
        if (! isset($players[$battletag.'|'.$region])) {
            return 'not in this replay';
        }
        if (max((float) ($rank['nameConfidence'] ?? 0), (float) ($rank['heroConfidence'] ?? 0)) < self::MIN_CONFIDENCE) {
            return 'card not identified';
        }

        $values = $this->rankValues($league, $rank['division'] ?? null, $rank['ladderPosition'] ?? null);
        if ($values === null) {
            return 'unknown rank';
        }

        $account = Battletag::where('battletag', $battletag)->where('region', $region)
            ->orderByDesc('latest_game')->first(['player_id', 'blizz_id']);
        if ($account === null) {
            return 'unknown player';
        }

        return DB::connection('heroesprofile')->transaction(function () use ($account, $region, $replayID, $gameDate, $values, $reporter, $version) {
            $latest = BattletagRealRank::where('blizz_id', $account->blizz_id)->where('region', $region)
                ->orderByDesc('last_game_date')->lockForUpdate()->first();

            if ($latest !== null && ($latest->first_replayID === $replayID || $latest->last_replayID === $replayID)) {
                return 'already have this game';
            }

            $season = $this->season($gameDate);

            if ($latest !== null && $this->sameRank($latest, $values) && $latest->season === $season) {
                if ($gameDate->lt($latest->first_game_date)) {
                    $latest->first_game_date = $gameDate;
                    $latest->first_replayID = $replayID;
                } else {
                    $latest->last_game_date = $gameDate;
                    $latest->last_replayID = $replayID;
                }
                $latest->read_count++;
                $latest->uploader_version = $version;
                $latest->save();

                return null;
            }

            BattletagRealRank::create($values + [
                'player_id' => $account->player_id,
                'blizz_id' => $account->blizz_id,
                'region' => $region,
                'season' => $season,
                'first_replayID' => $replayID,
                'first_game_date' => $gameDate,
                'last_replayID' => $replayID,
                'last_game_date' => $gameDate,
                'read_count' => 1,
                'reporter' => $reporter,
                'uploader_version' => $version,
            ]);

            return null;
        });
    }

    /**
     * The columns for a rank as the uploader names it, or null when it isn't one.
     *
     * @return array{league_tier: int|null, division: int|null, grandmaster: bool, ladder_position: int|null, unranked: bool}|null
     */
    public function rankValues(string $league, mixed $division, mixed $ladderPosition): ?array
    {
        if ($league === 'Unranked') {
            return ['league_tier' => null, 'division' => null, 'grandmaster' => false, 'ladder_position' => null, 'unranked' => true];
        }
        if (! isset(self::LEAGUES[$league])) {
            return null;
        }
        $hasDivision = ! in_array($league, ['Master', 'Grandmaster'], true);
        if ($hasDivision && ! (is_int($division) && $division >= 1 && $division <= 5)) {
            return null;
        }
        $position = $league === 'Grandmaster' && is_int($ladderPosition) && $ladderPosition >= 1 && $ladderPosition <= 999 ? $ladderPosition : null;

        return [
            'league_tier' => self::LEAGUES[$league],
            'division' => $hasDivision ? $division : null,
            'grandmaster' => $league === 'Grandmaster',
            'ladder_position' => $position,
            'unranked' => false,
        ];
    }

    /** @param  array<string, mixed>  $values */
    private function sameRank(BattletagRealRank $period, array $values): bool
    {
        return $period->league_tier === $values['league_tier']
            && $period->division === $values['division']
            && $period->grandmaster === $values['grandmaster']
            && $period->ladder_position === $values['ladder_position']
            && $period->unranked === $values['unranked'];
    }

    /** The Storm League season (season_dates id) a game date falls in, or null when none covers it yet. */
    private function season(Carbon $date): ?int
    {
        return SeasonDate::where('start_date', '<=', $date)->where('end_date', '>', $date)->value('id');
    }

    /**
     * The replay's players, as "battletag|region" => true: from the upload-time parse, or once the full parse
     * has run, from `player` (Storm League games only).
     *
     * @return array<string, true>
     */
    private function replayPlayers(int $replayID): array
    {
        $players = UploadedReplayPlayer::where('replayID', $replayID)->get(['battletag', 'region'])
            ->mapWithKeys(fn ($p) => [$p->battletag.'|'.$p->region => true])->all();
        if ($players !== []) {
            return $players;
        }

        return DB::connection('heroesprofile')->table('player')
            ->join('replay', 'replay.replayID', '=', 'player.replayID')
            ->join('battletags', 'battletags.player_id', '=', 'player.battletag')
            ->where('player.replayID', $replayID)
            ->where('replay.game_type', self::STORM_LEAGUE)
            ->get(['battletags.battletag', 'battletags.region'])
            ->mapWithKeys(fn ($p) => [$p->battletag.'|'.$p->region => true])->all();
    }

    /** When the replay was played: from the upload record, or the parsed replay. */
    private function gameDate(int $replayID): ?Carbon
    {
        $date = UploadedReplayData::where('replayID', $replayID)->whereNotNull('game_date')->value('game_date')
            ?? DB::connection('heroesprofile')->table('replay')->where('replayID', $replayID)->value('game_date');

        return $date === null ? null : Carbon::parse($date);
    }
}
