/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    colors: {
      'gray-dark': '#303340', // was #333 (warm); now the select-box grey, same as surface-3
      'gray-light' : '#ddd',
      'gray-xlight' : '#eee',
      'gray-md' : '#cbcbcb',
      'gray-medium' : '#aaa',
      'blue' : '#213d7a',
      'lblue' : '#315bb6',
      'teal' : '#008b8b',
      'green' : '#22c55e',
      'purple' : '#a855f7',
      
      'lteal' : '#00c0c0',
      /*'yellow' : '#ffc93e',*/
      'lyellow' : '#ffd465',
      /*'yellow' : '#118e6d',*/
      'yellow' : '#d98c40',

      'red' : '#b33616',
      'hred' : 'rgba(214, 142, 124, 0.5)',
      'lred' : '#e44a23',
      'black' : '#0F121D',
      'white' : '#ffffff',
      // Redesign surfaces, darkest to lightest (Figma Background levels)
      'page' : '#14182a',
      'surface' : '#1f2229',
      'surface-1' : '#23252f',
      'surface-2' : '#272a34',
      'surface-3' : '#303340',
      'surface-4' : '#393e4d',
      'field' : '#2b2e3a', // input backgrounds: header hue, a little lighter
      'header' : '#deffff',
      'subtext' : '#9ca3af',
      'soft' : '#c9cdd6',
      'quest' : '#e4b800',
      'win' : '#8cd9b8',
      'lighten' : 'rgba(255, 255, 255, 0.1)',
      'darken' : 'rgba(0,0,0,.3)'
    },
     screens: {
      'sm': '640px',
      // => @media (min-width: 640px) { ... }

      'md': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1500px',
      // => @media (min-width: 1500px) { ... } -- where the site starts scaling (app.css), so 1500 looks like 2133
    },
    fontFamily: {
      // --lang-font is the page language's own font (app.css), for the characters Roboto doesn't have
      'sans' : ['Roboto', 'var(--lang-font)', 'sans-serif'],
      'logo' : ['Ruslan Display'],
    },
    // Tailwind's sizes, times a scale from app.css: 1 normally, larger for Korean and Chinese.
    // xs and sm (labels, captions) use --text-scale-small, which grows more, since small characters
    // are the hardest to read; the rest use --text-scale. Only text grows; widths, padding and gaps don't.
    fontSize: Object.fromEntries(Object.entries({
      'xs': ['0.75rem', '1rem'],
      'sm': ['0.875rem', '1.25rem'],
      'base': ['1rem', '1.5rem'],
      'lg': ['1.125rem', '1.75rem'],
      'xl': ['1.25rem', '1.75rem'],
      '2xl': ['1.5rem', '2rem'],
      '3xl': ['1.875rem', '2.25rem'],
      '4xl': ['2.25rem', '2.5rem'],
      '5xl': ['3rem', '1'],
      '6xl': ['3.75rem', '1'],
      '7xl': ['4.5rem', '1'],
      '8xl': ['6rem', '1'],
      '9xl': ['8rem', '1'],
    }).map(([name, [size, lineHeight]]) => {
      const scale = ['xs', 'sm'].includes(name) ? 'var(--text-scale-small)' : 'var(--text-scale)';
      return [name, [
        `calc(${size} * ${scale})`,
        { lineHeight: lineHeight === '1' ? '1' : `calc(${lineHeight} * ${scale})` },
      ]];
    })),
    extend: {
      borderRadius: {
        DEFAULT: '.7rem',
        control: '.5rem',
      },
    },
  },
  plugins: [],
}
