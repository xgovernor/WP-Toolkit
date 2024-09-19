const mix = require( 'laravel-mix' );

mix
    .js('src/index.js', 'dist').react({version: 1})
    .autoload({ 'react': 'React', 'react-dom': 'ReactDOM' })
    .extract()
    .copy('src/images', 'dist/images')
    .setPublicPath('dist');
