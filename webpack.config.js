// webpack.config.js
const {VueLoaderPlugin} = require('vue-loader');
const path = require('path');
const webpack = require('webpack');

const env = process.env.NODE_ENV;

const srcDir = path.join(__dirname, 'src', 'assets', 'src');
const outputDir = path.join(__dirname, 'src', 'assets', 'dist');

module.exports = {
  entry: path.join(srcDir, 'js', 'app.js'),
  mode: env,
  output: {
    path: outputDir
  },
  devtool: 'source-map',
  module: {
    rules: [
      {
        test: /\.vue$/,
        include: srcDir,
        use: [
          'vue-loader',
        ],
      },
      {
        test: /\.js$/,
        include: srcDir,
        use: [
          'babel-loader',
        ]
      },
      {
        test: /\.css$/,
        include: srcDir,
        use: [
          'css-loader',
        ],
      },
      {
        test: /\.scss$/,
        include: srcDir,
        use: [
          'css-loader',
          'sass-loader',
        ],
      },
    ],
  },
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js'
    }
  },
  plugins: [
    new VueLoaderPlugin(),
    new webpack.DefinePlugin({
      __VUE_OPTIONS_API__: true,
      __VUE_PROD_DEVTOOLS__: false
    }),
  ],
};
