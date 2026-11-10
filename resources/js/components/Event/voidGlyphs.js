// The Void glyph alphabet, used on the recap page.
export const GLYPHS = {
  a: '⏃', b: '⏚', c: '☊', d: '⎅', e: '⟒', f: '⎎', g: '☌', h: '⊑', i: '⟟', j: '⟊', k: '☍', l: '⌰', m: '⋔',
  n: '⋏', o: '⍜', p: '⌿', q: '⍾', r: '⍀', s: '⌇', t: '⏁', u: '⎍', v: '⎐', w: '⍙', x: '⌖', y: '⊬', z: '⋉',
};

export const FALLBACK = Object.values(GLYPHS);

// Letters map to their glyph; anything else (spaces, punctuation) is kept.
export function toGlyphs(text) {
  return Array.from(text).map((char) => GLYPHS[char.toLowerCase()] || char).join('');
}
