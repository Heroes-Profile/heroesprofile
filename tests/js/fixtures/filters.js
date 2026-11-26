// A small copy of GlobalDataService::getFilterData(): the keys the filter bar reads, a few options each
export default {
    timeframe_type: [
        { code: 'minor', name: 'Minor' },
        { code: 'major', name: 'Major' },
    ],
    timeframes: [
        { code: '2.57.1.98785', name: '2.57.1.98785' },
        { code: '2.57.0.98746', name: '2.57.0.98746' },
        { code: '2.55.4.91418', name: '2.55.4.91418' },
    ],
    timeframes_grouped: [
        { code: '2.57', name: '2.57' },
        { code: '2.55', name: '2.55' },
    ],
    timeframes_sub_grouped: [
        { code: '2.57.1', name: '2.57.1' },
        { code: '2.57.0', name: '2.57.0' },
        { code: '2.55.4', name: '2.55.4' },
    ],
    combinable_major_patches: [],
    real_rank_minimum_patch: '2.55',
    regions: [
        { code: 'NA', name: 'NA' },
        { code: 'EU', name: 'EU' },
    ],
    game_types: [
        { code: 'qm', name: 'Quick Match' },
        { code: 'sl', name: 'Storm League' },
        { code: 'ar', name: 'ARAM' },
    ],
    game_types_full: [
        { code: 'qm', name: 'Quick Match' },
        { code: 'sl', name: 'Storm League' },
        { code: 'ar', name: 'ARAM' },
    ],
    game_types_full_add_custom: [],
    game_maps: [
        { code: 'Cursed Hollow', name: 'Cursed Hollow' },
        { code: 'Dragon Shire', name: 'Dragon Shire' },
    ],
    heroes: [
        { code: 1, name: 'Abathur' },
        { code: 81, name: 'Yrel' },
    ],
    role: [
        { code: 'Support', name: 'Support' },
        { code: 'Tank', name: 'Tank' },
    ],
    hero_role: [],
    stat_filter: [
        { code: 'win_rate', name: 'Win Rate' },
        { code: 'kills', name: 'Kills' },
    ],
    hero_level: [{ code: '1-5', name: '1-5' }],
    rank_tiers: [{ code: 1, name: 'Bronze' }],
    mirror: [
        { code: 0, name: 'Exclude' },
        { code: 1, name: 'Include' },
    ],
    talent_build_types: [{ code: 'Popular', name: 'Popular' }],
    minimum_games: [{ code: '0', name: '0' }],
    party_combinations: [
        { code: '00005', name: 'Five Stack' },
        { code: '11111', name: 'Solo' },
    ],
    group_size: [
        { code: 'Solo', name: 'Solo' },
        { code: 'All', name: 'All' },
    ],
    seasons: [{ code: 34, name: '2026 Season 2' }],
    match_prediction_seasons: [],
    sl_leagues: [],
    type: [
        { code: 'Player', name: 'Player' },
        { code: 'Hero', name: 'Hero' },
        { code: 'Role', name: 'Role' },
        { code: 'Map', name: 'Map' },
    ],
    leaderboard_type: [
        { code: 'Player', name: 'Player' },
        { code: 'Hero', name: 'Hero' },
    ],
    chart_type: [],
    minimum_account_level: [],
    x_axis_increments: [],
};
