var Encore = require('@symfony/webpack-encore');
var CopyPlugin = require('copy-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath(require('path').resolve('./public/dist'))
    // public path used by the web server to access the output path
    .setPublicPath(!Encore.isProduction() ? '/ppe_4/public/dist' : '/dist')
    // only needed for CDN's or sub-directory deploy
    // .setManifestKeyPrefix('dist')

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')

    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // .addStyleEntry('bootstrap_datepicker', './assets/styles/external/bootstrap-datepicker3.min.css')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')
;

// J'ai du surcharger la configuration de base de webpack encore fonts
Encore
    .configureLoaderRule('fonts', loaderRule => {
        loaderRule.test = /\.(woff|woff2|ttf|eot|otf)$/;
        loaderRule.loader = 'file-loader';
        loaderRule.options = {
            name: 'fonts/[name].[hash:8].[ext]',

            // Attention ici c'est le recalifieur de chemin vers le fichier de fonts dans le css
            publicPath: !Encore.isProduction() ? '/ppe_4/public/dist' : '/dist'
        };

        return loaderRule;
    })
;

/*let patterns = [
    {
        from: `./node_modules/mdbootstrap/js/mdb${!Encore.isProduction() ? '.min' : ''}.js`,
        to: 'mdb.js'
    }
];
if (!Encore.isProduction()) {
    patterns.push({
        from: './node_modules/mdbootstrap/js/mdb.min.js.map',
        to: 'mdb.min.js.map'
    });
}

Encore.addPlugin(new CopyPlugin(patterns));*/

// console.log(Encore.getWebpackConfig());

module.exports = Encore.getWebpackConfig();