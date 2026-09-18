export function formatNumber(value){
  return Number(value).toLocaleString();
}

export function formatBytes(bytes){
  if(!bytes){
    return '0 MB';
  }

  const mb = bytes / 1024 / 1024;

  return mb < 1024
    ? `${mb.toFixed(mb < 10 ? 2 : 1)} MB`
    : `${(mb / 1024).toFixed(2)} GB`;
}

export function formatDuration(ms){
  const seconds = (ms || 0) / 1000;

  if(seconds < 60){
    return `${seconds.toFixed(1)}s`;
  }

  if(seconds < 3600){
    return `${Math.floor(seconds / 60)}m ${Math.round(seconds % 60)}s`;
  }

  return `${Math.floor(seconds / 3600)}h ${Math.round((seconds % 3600) / 60)}m`;
}

// Rounding a real cost to $0.00 reads as free rather than as small, so anything
// under a cent keeps enough places to stay a number.
export function formatCost(cost){
  if(!cost){
    return '$0.00';
  }

  return cost < 0.01 ? `$${cost.toFixed(4)}` : `$${cost.toFixed(2)}`;
}
