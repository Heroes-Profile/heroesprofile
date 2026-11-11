// The replay viewer's map objectives, bosses and pushing mercenaries: the doc's objective_units
// (extract v6). Older games only have the few the parser always kept (the tribute, Dragon Shire's
// shrines and Dragon Knight...), from units, with no positions in between.
//
// icon: a file in /images/analytics/minimaps/icons; tint: false keeps the icon's own colours;
// letter: drawn instead of an icon (the capture points).
const UNITS = {
  // Battlefield of Eternity
  BossDuelBossPreviewUnitHeaven: { kind: 'warning', icon: 'immortal_heaven', size: 9, label: 'Heaven Immortal (spawning)' },
  BossDuelBossPreviewUnitHell: { kind: 'warning', icon: 'immortal_hell', size: 9, label: 'Hell Immortal (spawning)' },
  BossDuelBossHeaven: { kind: 'objective', icon: 'immortal_heaven', size: 9, label: 'Heaven Immortal' },
  BossDuelBossHell: { kind: 'objective', icon: 'immortal_hell', size: 9, label: 'Hell Immortal' },
  BossDuelLanerHeaven: { kind: 'objective', icon: 'immortal_heaven', size: 9, label: 'Immortal' },
  BossDuelLanerHell: { kind: 'objective', icon: 'immortal_hell', size: 9, label: 'Immortal' },
  // Cursed Hollow
  RavenLordTributePreview: { kind: 'warning', icon: 'tribute', size: 7, label: 'Tribute (spawning)' },
  RavenLordTributeWarning: { kind: 'warning', icon: 'tribute', size: 7, label: 'Tribute (spawning)' },
  RavenLordTribute: { kind: 'objective', icon: 'tribute', size: 7, label: 'Tribute' },
  // Garden of Terror
  SeedSpawnPreview: { kind: 'warning', icon: 'garden_seed', size: 7, label: 'Seed (spawning)' },
  Seed: { kind: 'objective', icon: 'garden_seed', size: 7, label: 'Seed' },
  GardenTerror: { kind: 'objective', icon: 'garden_terror', size: 9, label: 'Garden Terror' },
  // Dragon Shire
  DragonShireShrineMoon: { kind: 'objective', icon: 'moon_shrine', size: 7, label: 'Moon Shrine' },
  DragonShireShrineSun: { kind: 'objective', icon: 'sun_shrine', size: 7, label: 'Sun Shrine' },
  VehicleDragon: { kind: 'objective', icon: 'dragon_knight', size: 9, label: 'Dragon Knight' },
  // Hanamura Temple, Volskaya Foundry
  Payload_Neutral_Warning: { kind: 'warning', icon: 'payload', size: 8, label: 'Payload (spawning)' },
  Payload_Neutral: { kind: 'objective', icon: 'payload', size: 8, label: 'Payload' },
  ControlPointMinimapPreviewIconA: { kind: 'warning', letter: 'A', size: 7, label: 'Capture point (opening)' },
  ControlPointMinimapPreviewIconB: { kind: 'warning', letter: 'B', size: 7, label: 'Capture point (opening)' },
  ControlPointMinimapPreviewIconC: { kind: 'warning', letter: 'C', size: 7, label: 'Capture point (opening)' },
  ControlPointMinimapIconA: { kind: 'objective', letter: 'A', size: 7, label: 'Capture point' },
  ControlPointMinimapIconB: { kind: 'objective', letter: 'B', size: 7, label: 'Capture point' },
  ControlPointMinimapIconC: { kind: 'objective', letter: 'C', size: 7, label: 'Capture point' },
  VolskayaVehicle: { kind: 'objective', icon: 'protector', size: 9, label: 'Triglav Protector' },
  // Infernal Shrines
  DiabloShrine: { kind: 'objective', icon: 'shrine', size: 7, label: 'Shrine' },
  MercPunisherLaner: { kind: 'objective', icon: 'boss', size: 9, label: 'Punisher' },
  // Tomb of the Spider Queen
  SoulEater: { kind: 'objective', icon: 'soul_eater', size: 8, label: 'Webweaver' },
  // Warhead Junction
  NukeTargetMinimapPreviewIconUnit: { kind: 'warning', icon: 'nuke', size: 6, label: 'Warhead (spawning)' },
  WarheadSingle: { kind: 'objective', icon: 'nuke', size: 6, label: 'Warhead' },
  NukeTargetMinimapIconUnit: { kind: 'objective', icon: 'nuke', size: 9, label: 'Nuke landing' },
  // Towers of Doom
  ScoringAltar: { kind: 'objective', icon: 'altar', size: 7, label: 'Altar' },
  // Sky Temple
  LuxoriaTemple: { kind: 'objective', icon: 'shrine', size: 7, label: 'Temple' },
  // Blackheart's Bay
  DocksTreasureChest: { kind: 'objective', icon: 'treasure_chest', size: 7, tint: false, label: 'Treasure chest' },
  JungleBattleship: { kind: 'objective', icon: 'ghost_ship', size: 9, label: 'Ghost ship' },
  // Alterac Pass
  AllianceCavalry: { kind: 'objective', icon: 'cavalry_alliance', size: 7, tint: false, label: 'Alliance cavalry' },
  HordeCavalry: { kind: 'objective', icon: 'cavalry_horde', size: 7, tint: false, label: 'Horde cavalry' },
  CapturedSoldier: { kind: 'objective', icon: 'prisoners', size: 7, label: 'Prisoners' },
  // Braxis Holdout
  ZergUltralisk: { kind: 'objective', icon: 'zerg', size: 6, label: 'Ultralisk' },
  ZergZergling: { kind: 'objective', icon: 'zerg', size: 3.5, label: 'Zergling' },
  ZergBaneling: { kind: 'objective', icon: 'zerg', size: 3.5, label: 'Baneling' },
  ZergHydralisk: { kind: 'objective', icon: 'zerg', size: 4.5, label: 'Hydralisk' },
  ZergGuardian: { kind: 'objective', icon: 'zerg', size: 5, label: 'Guardian' },
  // Haunted Mines
  UnderworldBoss: { kind: 'objective', icon: 'mines_golem', size: 9, label: 'Grave Golem' },
  UnderworldSummonedBoss: { kind: 'objective', icon: 'mines_golem', size: 9, label: 'Grave Golem' },
  // Bosses: the camp, then the captured boss pushing
  JungleGraveGolemDefender: { kind: 'boss', icon: 'boss', size: 8, label: 'Boss' },
  SlimeBossDefender: { kind: 'boss', icon: 'boss', size: 8, label: 'Boss' },
  TerranArchangelDefender: { kind: 'boss', icon: 'boss', size: 8, label: 'Archangel' },
  MercHorsemanDefender: { kind: 'boss', icon: 'boss', size: 8, label: 'Headless Horseman' },
  JungleGraveGolemLaner: { kind: 'boss_laner', icon: 'boss', size: 8, label: 'Boss' },
  SlimeBossLaner: { kind: 'boss_laner', icon: 'boss', size: 8, label: 'Boss' },
  TerranArchangelLaner: { kind: 'boss_laner', icon: 'boss', size: 8, label: 'Archangel' },
  MercHorsemanLaner: { kind: 'boss_laner', icon: 'boss', size: 8, label: 'Headless Horseman' },
};

const MERC = { icon: 'mercenary', size: 4.5, label: 'Mercenary' };

// A unit's name for people, for the Objectives & Bosses tab
export function objectiveLabel(name) {
  return UNITS[name]?.label || name;
}

function look(unit) {
  return UNITS[unit.name] || (unit.k === 'merc' ? MERC : null);
}

// The game's objective units, from objective_units, or for older games from units
export function objectiveUnits(data) {
  const units = data.objective_units
    ?? (data.units || []).filter(u => UNITS[u.name]).map(u => ({ ...u, k: UNITS[u.name].kind, pts: [] }));
  return units.map(u => ({ ...u, look: look(u) })).filter(u => u.look);
}

// Where a unit is at time t: along its spawn, recorded points and death; null while it doesn't exist
function positionAt(unit, t) {
  if (t < unit.b[0] || (unit.d && t > unit.d[0])) return null;
  const points = [unit.b, ...unit.pts, ...(unit.d ? [unit.d] : [])].filter(p => p[1] >= 0);
  if (!points.length) return null;
  let i = 0;
  while (i < points.length - 1 && points[i + 1][0] <= t) i++;
  const a = points[i];
  const b = points[i + 1];
  if (!b || b[0] === a[0]) return [a[1], a[2]];
  const f = (t - a[0]) / (b[0] - a[0]);
  return [a[1] + (b[1] - a[1]) * f, a[2] + (b[2] - a[2]) * f];
}

// What to draw at time t: icon or letter, spot, team (-1 neutral), and whether it's a spawn warning
export function objectivesAt(units, t) {
  return units.flatMap(u => {
    const at = positionAt(u, t);
    if (!at) return [];
    const team = u.team ?? -1;
    const { icon, letter, size, tint, label } = u.look;
    return [{
      key: u.name + ':' + u.b[0] + ':' + u.b[1] + ':' + u.b[2],
      x: at[0], y: at[1], size, letter, team,
      src: icon ? `/images/analytics/minimaps/icons/${icon}.png` : null,
      tint: tint !== false,
      warning: u.k === 'warning',
      label,
    }];
  });
}
