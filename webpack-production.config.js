const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
    mode: 'production',

    entry: {
        public: './public/js/owlthslider-public.js', // Entry point for frontend
        admin: './admin/js/owlthslider-admin.js',       // Entry point for admin
    },

    // Define output point
    output: {
        filename: '[name]/js/owlthslider.min.js', // Output JS filename
        path: path.resolve(__dirname, 'build'), // Output directory
        publicPath: '/wp-content/plugins/owlthslider/build', // Public URL path (used by WordPress)
        clean: true,
    },

    module: {
        rules: [
            {
                test: /\.s?css$/, // Apply to .css and .scss files
                exclude: /node_modules/,
                use: [
                    MiniCssExtractPlugin.loader, // Extract CSS into separate files
                    'css-loader', // Translates CSS into CommonJS
                    'sass-loader', // Compiles Sass to CSS
                ],
            },
        ],
    },

    optimization: {
        minimize: true,
        minimizer: [
            `...`, // Extends existing minimizers (i.e., `terser-webpack-plugin`)
            new CssMinimizerPlugin(), // Minify CSS
        ],
    },

    plugins: [
        new CleanWebpackPlugin(), // Cleans the assets/dist folder before each build
        new MiniCssExtractPlugin({
            filename: '[name]/css/owlthslider.min.css', // Output CSS filename
        }),
    ],

    // Disable source maps in production for better performance
    devtool: false,
};
