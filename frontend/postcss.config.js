/**
 * @file postcss.config.js
 * @brief Configuration file for PostCSS.
 * @description This file registers TailwindCSS and Autoprefixer as PostCSS plugins,
 * allowing them to be used in the Angular build process.
 */
module.exports = {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
  },
};