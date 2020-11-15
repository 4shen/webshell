/*
|--------------------------------------------------------
| 文档 webpack 配置文件
|--------------------------------------------------------
|
| 配置文件使用 ES6 语法配置，这样能保证整个文档项目的语法统一性
| 修改配置文件请使用 ES6 语法对 webpack 进行配置。
|
| @author Seven Du <shiweidu@outlook.com>
|
*/

import path from 'path';
import webpack from 'webpack';
import WebpackLaravelMixManifest from 'webpack-laravel-mix-manifest';
import UglifyJsPlugin from 'uglifyjs-webpack-plugin';
import CleanPlugin from 'clean-webpack-plugin';
import CopyPlugin from 'copy-webpack-plugin';

/*
|--------------------------------------------------------
| 获取 NODE 环境变量模式
|--------------------------------------------------------
|
| 获取变量的用处用于判断当前运行环境是否属于正式编译使用。
|
*/

const NODE_ENV = process.env.NODE_ENV || 'development';

/*
|--------------------------------------------------------
| 获取是否是正式环境
|--------------------------------------------------------
|
| 定义该常量的用处便于程序中多处的判断，用于快捷判断条件。
|
*/

const isProd = NODE_ENV === 'production';

/*
|---------------------------------------------------------
| 源代码根
|---------------------------------------------------------
|
| 获取源代码所处的根路径
|
*/

const src = path.join(__dirname, 'admin');

/*
|---------------------------------------------------------
| 解决路径位置
|---------------------------------------------------------
|
| 解析并正确的返回已经存在的相对于根下的文件或者目录路径。
|
*/

const resolve = pathname => path.resolve(src, pathname);

const outPath = path.resolve(__dirname, isProd ? 
'assets' : '../../public/assets/feed');

/*
|---------------------------------------------------------
| 合并路径位置
|---------------------------------------------------------
|
| 合并得到相对于源根路径下的文件路径。
|
*/

const webpackConfig = {

  mode: isProd ? 'production' : 'development',
  /*
|---------------------------------------------------------
| 开发工具
|---------------------------------------------------------
|
| 判断是不是正式环境，非正式环境，加载 source-map
|
*/

  devtool: isProd ? false : 'source-map',

  /*
|---------------------------------------------------------
| 配置入口
|---------------------------------------------------------
|
| 入口配置，多个入口增加更多配置项。这里配置需要编译的资源入口。
|
*/

  entry: {
    admin: resolve('main.js')
  },

  /*
|---------------------------------------------------------
| 输出配置
|---------------------------------------------------------
|
| 输出配置用于配制输出的文件路径和 js 文件的地方
|
*/

  output: {
    path: outPath,
    filename: !isProd ? '[name].js' : '[name].js'
  },

  /*
  |---------------------------------------------------------
  | 解决配置
  |---------------------------------------------------------
  |
  | 用语解决和处理路径配置和后缀自动加载
  |
  */

  resolve: {
  // 忽略加载的后缀
    extensions: [ '.js', '.jsx', '.json' ],
    // 模块所处的目录
    modules: [ src, path.resolve(__dirname, 'node_modules') ]
  },

/*
|---------------------------------------------------------
| 模块
|---------------------------------------------------------
|
| 配置模块相关的设置
|
*/

  module: {
    rules: [
      // js文件加载规则
      {
        test: /\.jsx?$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
        }
      }
    ]
  },

  /*
|---------------------------------------------------------
| 插件配置
|---------------------------------------------------------
|
| 定义在编译环境中所使用的插件
|
*/

  plugins: [
  // Defined build env.
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: JSON.stringify(NODE_ENV)
      }
    }),
    new WebpackLaravelMixManifest(),
    ...(isProd ? [
      // Prod env.
      new UglifyJsPlugin({
        sourceMap: false
      })
    ] : [
      // Development env.
      new webpack.NoEmitOnErrorsPlugin(),
    ]),
    new CleanPlugin(outPath, {
      root: isProd ? __dirname : path.resolve(__dirname, '../..')
    }),
    new CopyPlugin([{
      from: path.resolve(__dirname, 'admin/static'),
      to: outPath
    }]),
  ],
};

export default webpackConfig;
