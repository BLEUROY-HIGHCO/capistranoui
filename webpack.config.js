// webpack.config.js
var Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('web/build/')
  .setPublicPath('/build')
  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .addEntry('environment', './assets/js/Environment/environment.js')
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
