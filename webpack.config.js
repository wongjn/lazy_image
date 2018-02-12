/**
 * @file
 * Webpack configuration.
 */

const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: {
    loader: path.resolve(__dirname, 'js/src/loader.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'js/dist'),
  },
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: 'babel-loader',
      },
    ],
  },
};
