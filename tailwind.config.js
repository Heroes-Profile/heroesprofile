/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    colors: {
      'gray-dark': '#333',
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

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }
    },
    fontFamily: {
      'sans' : ['Roboto', 'sans-serif'],
      'logo' : ['Ruslan Display'],
    },
    extend: {},
  },
  plugins: [],
}
