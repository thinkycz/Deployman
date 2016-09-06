const elixir = require('laravel-elixir');

require('laravel-elixir-vue');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix){
    mix.copy('node_modules/sweetalert/dist/sweetalert.css', 'resources/assets/css/');
    mix.copy('node_modules/sweetalert/dist/sweetalert.min.js', 'resources/assets/js/');
    mix.sass('app.scss', 'resources/assets/css/app.css');
    mix.stylesIn('resources/assets/css');
    mix.scriptsIn('resources/assets/js');
});
