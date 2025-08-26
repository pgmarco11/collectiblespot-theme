const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = (env, argv) => {
  const isDevelopment = argv.mode === 'development';

  return {
    entry: {
      main: './includes/js/index.js'
    },
    output: {
      path: path.resolve(__dirname, 'public'),
      filename: isDevelopment ? 'js/[name].js' : 'js/[name].[contenthash].js',
    },
    devtool: isDevelopment ? 'inline-source-map' : false,
    devServer: {
      static: {
        directory: path.join(__dirname, 'public'),
        watch: true,
      },
      hot: true,
      open: true,
      port: 3000,
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
            options: {
              presets: ['@babel/preset-env', '@babel/preset-react'],
            },
          },
        },
        {
          test: /\.scss$/,
          use: [
            isDevelopment ? 'style-loader' : MiniCssExtractPlugin.loader,
            'css-loader',
            'sass-loader',
          ],
        },
        {
          test: /\.(html)$/,
          use: ['html-loader'],
        },
      ],
    },
    optimization: {
      minimizer: [
        `...`, // Extends default minimizers (terser-webpack-plugin for JS)
        new CssMinimizerPlugin(), // Optimizes CSS
      ],
    },
    plugins: [
      new CleanWebpackPlugin(),
      new MiniCssExtractPlugin({
        filename: isDevelopment ? 'css/[name].css' : 'css/[name].[contenthash].css',
      }),
      new WebpackManifestPlugin({
        fileName: 'manifest.json',
        publicPath: '',
        generate: (seed, files) => {
          return files.reduce((acc, file) => {
            if (file.name.endsWith('.js')) acc['index.js'] = file.path;
            if (file.name.endsWith('.css')) acc['index.css'] = file.path;
            return acc;
          }, seed);
        },
      }),
    ],
  };
};
