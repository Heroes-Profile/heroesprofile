const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
.js('resources/js/custom-datatables.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

mix.copy('resources/js/createTableAjax.js', 'public/js');
mix.copy('resources/js/createTableJS.js', 'public/js');

mix.copy('resources/js/datatables.min.js', 'public/js');
mix.copy('resources/css/datatables.min.css', 'public/css');

mix.copy('resources/js/popup.js', 'public/js');
mix.copy('resources/js/bootbox.min.js', 'public/js');

mix.copy('resources/js/extraHeader.js', 'public/js');
