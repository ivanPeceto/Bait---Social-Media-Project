/**
 * @file tailwind.config.js
 * @brief Configuration file for TailwindCSS.
 * @description This file configures Tailwind, including the paths to the source files
 * that should be scanned for utility classes.
 */
module.exports = {
  content: [
    "./src/**/*.{html,ts}", // This line tells Tailwind to scan ALL .html and .ts files inside the src folder
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};