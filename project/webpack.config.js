let Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/')
    .setPublicPath('/')
    .addEntry('app', './assets/js/app.js')
    .addStyleEntry('styles', './assets/css/app.scss')
    .enableReactPreset()
    .disableSingleRuntimeChunk()
    .enableSassLoader(function(options) {}, {
        resolveUrlLoader: false
    })
;

module.exports = Encore.getWebpackConfig();
