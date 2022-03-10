const mix = require('laravel-mix');

require('vuetifyjs-mix-extension')

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

mix.copy('resources/icon', 'public/icon');
mix.copy('resources/css', 'public/css');
mix.copy('resources/sw', 'public');
mix.copy('resources/images', 'public/images');

mix.js('resources/js/app.js', 'public/js')
    .vuetify()
    .vue()
    .sourceMaps()

mix.js('resources/js/bookstore-signage.js', 'public/js')
    .vuetify()
    .vue()
    .sourceMaps()

mix.sass('resources/sass/app.scss', 'public/css');
mix.sass('resources/sass/bookstore-signage.scss', 'public/css');
