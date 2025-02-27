const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
  // directory where compiled assets will be stored
  .setOutputPath("public/build/")
  // public path used by the web server to access the output path
  .setPublicPath("/build")
  // For Docker environment, we need to make sure the assets are accessible
  .setManifestKeyPrefix("build/")

  /*
   * ENTRY CONFIG
   *
   * Each entry will result in one JavaScript file (e.g. app.js)
   * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
   */
  .addEntry("app", "./assets/app.js")
  .addEntry(
    "soft-ui-dashboard-tailwind",
    "./assets/scripts/soft-ui-dashboard-tailwind.js"
  )
  .addEntry("popper", "./assets/scripts/popper-wrapper.js")
  .addEntry("tooltips", "./assets/scripts/tooltips.js")

  // Copy static files
  .copyFiles([
    {
      from: "./assets/styles",
      to: "styles/[path][name].[ext]",
    },
    {
      from: "./assets/scripts",
      to: "js/[path][name].[ext]",
    },
    {
      from: "./assets/fonts",
      to: "fonts/[path][name].[ext]",
    },
    {
      from: "./assets/img",
      to: "images/[path][name].[ext]",
    },
  ])

  // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
  .splitEntryChunks()

  // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
  .enableStimulusBridge("./assets/controllers.json")

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

  // Enable versioning in production
  .enableVersioning(Encore.isProduction())

  // configure Babel
  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })

  // enables and configure @babel/preset-env polyfills
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = "usage";
    config.corejs = "3.38";
  })

  // enables PostCSS support
  .enablePostCssLoader((options) => {
    options.postcssOptions = {
      config: "./postcss.config.js",
    };
  })

  // enables Sass/SCSS support
  .enableSassLoader();

// Get the full Webpack config
const config = Encore.getWebpackConfig();

// Add optimization configuration
if (Encore.isProduction()) {
  config.optimization = {
    minimize: true,
    minimizer: [
      new (require("terser-webpack-plugin"))({
        terserOptions: {
          compress: true,
          mangle: true,
        },
      }),
    ],
  };
}

module.exports = config;
