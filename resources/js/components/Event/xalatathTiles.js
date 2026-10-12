// Scoreboard tiles, shared by the live scoreboard, the final banner and the recap page.
// totals is the summed xalatath_data row.
function stat(totals, column) {
  return Number((totals || {})[column] || 0);
}

function hours(seconds) {
  return Math.floor(seconds / 3600);
}

export function mainTiles(totals) {
  return [
    { label: 'Minds Consumed', value: stat(totals, 'takedowns'), info: 'Takedowns (kills + assists) by Xal\'atath.' },
    { label: 'Void Damage Unleashed', value: stat(totals, 'spell_damage'), info: 'Total spell damage dealt by Xal\'atath.' },
    { label: 'Realms Shattered', value: Math.floor(stat(totals, 'siege_damage') / 12900), info: 'Total siege damage by Xal\'atath, divided by 12,900.' },
    { label: 'Mass Consumptions', value: stat(totals, 'multikill'), info: 'Multikills by Xal\'atath.' },
    { label: 'Banished to the Void', value: stat(totals, 'deaths'), info: 'Xal\'atath deaths.' },
    { label: 'Sealed Away', value: stat(totals, 'bans'), info: 'Times Xal\'atath was banned in draft.' },
    { label: 'Cold Feet', value: stat(totals, 'escapes'), info: 'Escapes by Xal\'atath (surviving at low health).' },
  ];
}

export function extraTiles(totals) {
  return [
    { label: 'Games Played', value: stat(totals, 'games_played'), info: 'Games played as Xal\'atath.' },
    { label: 'Victories', value: stat(totals, 'wins'), info: 'Games won as Xal\'atath.' },
    { label: 'Void Unleashed in Battle', value: stat(totals, 'teamfight_hero_damage'), info: 'Team fight hero damage by Xal\'atath.' },
    { label: 'Hours Ablaze', value: hours(stat(totals, 'on_fire_time')), info: 'Time spent on fire, in hours.' },
    { label: 'Longest Rampage', value: stat(totals, 'highest_kill_streak'), info: 'Highest kill streak in a single game.' },
    { label: 'Outnumbered & Undone', value: stat(totals, 'outnumbered_deaths'), info: 'Deaths while outnumbered.' },
    { label: 'Hours Sealed in the Dagger', value: hours(stat(totals, 'time_spent_dead')), info: 'Time spent dead, in hours.' },
  ];
}

export function formatNumber(value) {
  return Number(value || 0).toLocaleString('en-US');
}
