const path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const CopyPlugin = require('copy-webpack-plugin');

// console.log(process.env.NODE_ENV);

let app = {
    entry: {
        main: './src/index.js',
    },

    optimization: {
        splitChunks: {
            name: 'vender',
            chunks: 'initial',
        },
        minimize: true,
        minimizer: [new TerserPlugin({
            test: /\.js(\?.*)?$/i,
        })],
    },

    output: {
        path: `${__dirname}/dist`,
        // publicPath: '/dist/', // 本番環境における設置パス
        filename: 'asset/js/[name].js',
    },

    module: {
        rules: [
            // JSのコンパイル設定
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: [
                    {
                        // 利用するローダー(options内容は.babelrc)
                        loader: 'babel-loader',
                        // Babel のオプションを指定する
                        options: {
                            presets: [
                                // プリセットを指定することで、ES2020 を ES5 に変換
                                '@babel/preset-env',
                            ],
                        },
                    },
                ],
            },
            // HTML
            {
                test: /\.ejs$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'html-loader',
                        options: {
                            sources: false, // url()の取り込み一括禁止
                            minimize: false,
                        },
                    },
                    {
                        loader: 'ejs-plain-loader'
                    },
                ],
            },
            // Sassファイルの読み込みとコンパイル
            {
                test: /\.scss/, // 対象となるファイルの拡張子
                use: [
                    // CSSファイルを書き出すオプションを有効にする
                    {
                        // mode: dev
                        // loader: 'style-loader'
                        // mode: prod
                        loader: MiniCssExtractPlugin.loader,
                        options: {
                            publicPath: '../../',
                        },
                    },
                    // CSSをバンドルするための機能
                    {
                        loader: 'css-loader',
                        options: {
                            // オプションでCSS内のurl()メソッドの取り込みを禁止する -> false を指定
                            // url: false,
                            // ソースマップの利用有無
                            sourceMap: true,

                            // 0 => no loaders (default);
                            // 1 => postcss-loader;
                            // 2 => postcss-loader, sass-loader
                            importLoaders: 2,
                        },
                    },
                    // PostCSSのための設定
                    {
                        loader: 'postcss-loader',
                        options: {
                        // PostCSS側でもソースマップを有効にする
                        // sourceMap: true,
                            postcssOptions: {
                                plugins: [
                                // Autoprefixerを有効化
                                // ベンダープレフィックスを自動付与する
                                ['autoprefixer', { grid: true }],
                                ],
                            },
                        },
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                        // ソースマップの利用有無
                        sourceMap: true,
                        },
                    },
                ],
            },
            
            // fonts
            {
                test: /\.(woff(2)?|ttf|otf|eot)(\?v=\d+\.\d+\.\d+)?$/,
                type: 'asset/resource',
                generator: {
                    filename: 'asset/fonts/[name][ext]'
                },
            },
            // images
            {
                test: /\.(gif|png|jpe?g|svg)$/i,
                type: 'asset/resource',
                generator: {
                    filename: 'asset/img/[name][ext]'
                },
            },
        ],
    },

    resolve: {
        extensions: ['.js', '.jsx', '.ts'],
    },

    plugins: [
        // new CleanWebpackPlugin(['dist/*']) // for < v2 versions of CleanWebpackPlugin
        new CleanWebpackPlugin(),
        new HtmlWebpackPlugin({
            template: path.resolve( __dirname, 'src', 'html', 'template.ejs' ),
            filename: 'index.html',
            inject: false, //バンドルしたjsファイルを読み込むscriptタグを自動出力しない
            minify: false //minifyしない
        }),
        // new HtmlWebpackPlugin({
        //     template: path.resolve( __dirname, 'src', 'html', 'template_itamisugi.ejs' ),
        //     filename: 'index_itamisugi.html',
        //     inject: false, //バンドルしたjsファイルを読み込むscriptタグを自動出力しない
        //     minify: false //minifyしない
        // }),
        // new HtmlWebpackPlugin({
        //     template: path.resolve( __dirname, 'src', 'html', 'template_sompo.ejs' ),
        //     filename: 'index_sompo.html',
        //     inject: false, //バンドルしたjsファイルを読み込むscriptタグを自動出力しない
        //     minify: false //minifyしない
        // }),

        // CSSファイルを外だしにするプラグイン
        new MiniCssExtractPlugin({
            // ファイル名を設定します
            filename: 'asset/css/style.css',
        }),

        // 画像をまるごとsrcからdevへコピー
        new CopyPlugin({
            patterns: [
                { from: 'src/img', to: 'asset/img' },
            ],
        }),
    ],
};

module.exports = app;