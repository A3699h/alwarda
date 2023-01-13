var Encore = require('@symfony/webpack-encore');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
// directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')

    .copyFiles({
        from: './assets/images'
    })
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')
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
    .addEntry('navbar', './assets/js/navbar.js')
    .addEntry('sidebar', './assets/js/sidebar.js')
    .addEntry('user', './assets/js/user.js')
    .addEntry('categories', './assets/js/categories.js')
    .addEntry('product_form', './assets/js/product_form.js')
    .addEntry('product', './assets/js/product.js')
    .addEntry('dashboard', './assets/js/dashboard.js')
    .addEntry('discountVoucher', './assets/js/discountVoucher.js')
    .addEntry('area_form', './assets/js/area_form.js')
    .addEntry('productsShop', './assets/js/productsShop.js')
    .addEntry('order_form', './assets/js/order_form.js')
    .addEntry('blogForm', './assets/js/blogForm.js')
    .addEntry('params', './assets/js/params.js')


    .addEntry('frontApp', './assets/js/front/frontApp.js')
    .addEntry('frontIndex', './assets/js/front/frontIndex.js')
    .addEntry('frontCatalog', './assets/js/front/frontCatalog.js')
    .addEntry('frontProfile', './assets/js/front/frontProfile.js')
    .addEntry('resetPassword', './assets/js/front/resetPassword.js')
    .addEntry('manageOrders', './assets/js/front/manageOrders.js')
    .addEntry('recipientsList', './assets/js/front/recipientsList.js')
    .addEntry('blogList', './assets/js/front/blogList.js')
    .addEntry('blogSingle', './assets/js/front/blogSingle.js')

    .addEntry('newCatalog', './assets/js/front/newCatalog.js')



    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

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
    .autoProvidejQuery()

// uncomment if you use API Platform Admin (composer req api-admin)
//.enableReactPreset()
//.addEntry('admin', './assets/js/admin.js')
;

module.exports = Encore.getWebpackConfig();
