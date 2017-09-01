// webpack.config.js
var Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('web/build/')
  .setPublicPath('/build')
  .cleanupOutputBeforeBuild()
  .enableSassLoader()
  .addStyleEntry('show', './assets/scss/Environment/show.scss')
  .addEntry('environment', './assets/js/Environment/environment.js')
  .addEntry('main', './assets/js/main.js')
  .addEntry('common', './assets/scss/common.scss')
  .addEntry('security/login', './assets/scss/Security/login.scss')
  .enableSourceMaps(true)
  .enableVersioning()
;

// export the final configuration
module.exports = Encore.getWebpackConfig();
