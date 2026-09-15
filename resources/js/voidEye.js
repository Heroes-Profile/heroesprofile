import axios from 'axios';
import { reactive } from 'vue';

// Flair the frontend resolves itself: who found the Xal'atath eye, and whether the owner hid the crown.
// Fetched once per page and shared by every flair spot.
const state = reactive({ holders: new Set(), ownerHidden: false });
let request = null;

function load() {
  if (!request) {
    request = axios.get('/Flair/State')
      .then((response) => {
        const data = response.data || {};
        state.holders = new Set(Array.isArray(data.void_eye) ? data.void_eye : []);
        state.ownerHidden = !!data.owner_hidden;
      })
      .catch(() => {});
  }
  return request;
}

export function hasVoidEye(blizzId, region) {
  load();
  if (blizzId === null || blizzId === undefined || region === null || region === undefined) {
    return false;
  }
  return state.holders.has(`${blizzId}|${region}`);
}

export function ownerFlairHidden() {
  load();
  return state.ownerHidden;
}
