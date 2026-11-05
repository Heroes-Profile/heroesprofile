// Flair the frontend resolves itself: who found the Xal'atath eye, and whether the owner hid the crown.
// The layout writes it into the page as window.flairState.
const data = window.flairState || {};
const holders = new Set(Array.isArray(data.void_eye) ? data.void_eye : []);
const ownerHidden = !!data.owner_hidden;

export function hasVoidEye(blizzId, region) {
  if (blizzId === null || blizzId === undefined || region === null || region === undefined) {
    return false;
  }
  return holders.has(`${blizzId}|${region}`);
}

export function ownerFlairHidden() {
  return ownerHidden;
}
