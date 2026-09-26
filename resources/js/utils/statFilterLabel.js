export function statFilterLabel(statFilter){
  return 'Avg ' + String(statFilter || '').replace(/_/g, ' ').toLowerCase();
}
