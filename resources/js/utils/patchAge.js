const DAY = 24 * 60 * 60 * 1000;

const relativeTime = new Intl.RelativeTimeFormat('en', { numeric: 'auto', style: 'short' });
const fullDate = new Intl.DateTimeFormat('en', { dateStyle: 'long' });

// When a patch group first appeared: the earliest date_added among its builds.
export function earliestPatchDate(code, timeframes){
  let earliest = null;
  for(const timeframe of timeframes || []){
    if(!timeframe.date_added || !(timeframe.code === code || timeframe.code.startsWith(code + '.'))){
      continue;
    }
    if(earliest === null || new Date(timeframe.date_added) < new Date(earliest)){
      earliest = timeframe.date_added;
    }
  }
  return earliest;
}

// A compact age such as "5 days ago", "3 wk. ago" or "2 yr. ago"; units get coarser with age.
export function patchAge(dateAdded, now = new Date()){
  const date = new Date(dateAdded);
  if(!dateAdded || isNaN(date.getTime())){
    return '';
  }
  const days = Math.max(0, Math.floor((now.getTime() - date.getTime()) / DAY));
  if(days < 14){
    return relativeTime.format(-days, 'day');
  }
  if(days < 60){
    return relativeTime.format(-Math.round(days / 7), 'week');
  }
  const years = Math.floor(days / 365.25);
  if(years < 2){
    return relativeTime.format(-Math.round(days / 30.44), 'month');
  }
  return relativeTime.format(-years, 'year');
}

export function patchDate(dateAdded){
  const date = new Date(dateAdded);
  return !dateAdded || isNaN(date.getTime()) ? '' : fullDate.format(date);
}
