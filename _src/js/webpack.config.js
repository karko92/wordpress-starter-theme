const path = require('path');

module.exports = {
  entry: {
    main: path.resolve(__dirname, './main.js'),
  },
  output: {
    filename: '[name].js',
  },
  devtool: '#source-map',
  resolve: {
    root: [path.resolve(__dirname, '.')],
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        loaders: [
          'babel?presets[]=es2015',
        ],
      },
    ],
  },
  externals: {
    jquery: 'jQuery',
    selecter: 'selecter'
  },
};
