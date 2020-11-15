<?php

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2016-Present ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to enterprise private license, that is   |
 * | bundled with this package in the file LICENSE, and is available      |
 * | through the world-wide-web at the following url:                     |
 * | https://github.com/slimkit/plus/blob/master/LICENSE                  |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

use Illuminate\Contracts\Routing\Registrar as RouteContract;
use Illuminate\Support\Facades\Route;
use Zhiyi\Plus\EaseMobIm;
use Zhiyi\Plus\Http\Controllers\APIs\V2 as API2;

Route::any('/develop', \Zhiyi\Plus\Http\Controllers\DevelopController::class.'@index');

/*
|--------------------------------------------------------------------------
| RESTful API version 2.
|--------------------------------------------------------------------------
|
| Define the version of the interface that conforms to most of the
| REST ful specification.
|
*/

Route::group(['prefix' => 'v2'], function (RouteContract $api) {

    /*
    |-----------------------------------------------------------------------
    | No user authentication required.
    |-----------------------------------------------------------------------
    |
    | Here are some public routes, public routes do not require user
    | authentication, and if it is an optional authentication route to
    | obtain the current authentication user, use `$request-> user ('api')`.
    |
    */

    $api->post('/pingpp/webhooks', API2\PingPlusPlusChargeWebHooks::class.'@webhook');

    $api->post('/plus-pay/webhooks', API2\NewWalletRechargeController::class.'@webhook');

    $api->post('/currency/webhooks', API2\CurrencyRechargeController::class.'@webhook');

    // 钱包充值验证
    $api->post('/alipay/notify', API2\PayController::class.'@alipayNotify');
    $api->post('/wechat/notify', API2\PayController::class.'@wechatNotify');

    // 积分充值验证
    $api->post('/alipayCurrency/notify', API2\CurrencyPayController::class.'@alipayNotify');
    $api->post('/wechatCurrency/notify', API2\CurrencyPayController::class.'@wechatNotify');
    /*
    | 应用启动配置.
    */

    $api->get('/bootstrappers', API2\BootstrappersController::class.'@show');

    // User authentication.
    $api->group(['prefix' => 'auth'], function (RouteContract $api) {
        $api->post('login', API2\AuthController::class.'@login');
        $api->any('logout', API2\AuthController::class.'@logout');
        $api->any('refresh', API2\AuthController::class.'@refresh');
    });

    // Search location.
    $api->get('/locations/search', API2\LocationController::class.'@search');

    // Get hot locations.
    // @GET /api/v2/locations/hots
    $api->get('/locations/hots', API2\LocationController::class.'@hots');

    // Get Advertising space
    $api->get('/advertisingspace', API2\AdvertisingController::class.'@index');

    // Get Advertising.
    $api->get('/advertisingspace/{space}/advertising', API2\AdvertisingController::class.'@advertising');
    $api->get('/advertisingspace/advertising', API2\AdvertisingController::class.'@batch');

    // Get a html for about us.
    $api->get('/aboutus', API2\SystemController::class.'@about');

    // 注册协议
    $api->get('/agreement', API2\SystemController::class.'@agreement');

    // Get all tags.
    // @Get /api/v2/tags
    $api->get('/tags', API2\TagController::class.'@index');

    /*
    |-----------------------------------------------------------------------
    | 用户验证验证码
    |-----------------------------------------------------------------------
    |
    | 定义与用户操作相关的验证码操作
    |
    */

    $api->group(['prefix' => 'verifycodes'], function (RouteContract $api) {

        /*
        | 注册验证码
        */

        $api->post('/register', API2\VerifyCodeController::class.'@storeByRegister');

        /*
        | 已存在用户验证码
        */

        $api->post('/', API2\VerifyCodeController::class.'@store');
    });

    // 排行榜相关
    // @Route /api/v2/user/ranks
    $api->group(['prefix' => 'ranks'], function (RouteContract $api) {

        // 获取粉丝排行
        // @GET /api/v2/user/ranks/followers
        $api->get('/followers', API2\RankController::class.'@followers');

        // 获取财富排行
        // @GET /api/v2/user/ranks/balance
        $api->get('/balance', API2\RankController::class.'@balance');

        // 获取收入排行
        // @GET /api/v2/user/ranks/income
        $api->get('/income', API2\RankController::class.'@income');
    });
    /*
    | 获取文件.
    */

    tap($api->get('/files/{fileWith}', API2\FilesController::class.'@show'), function ($route) {
        $route->setAction(array_merge($route->getAction(), [
            'middleware' => ['cors-should', 'bindings'],
        ]));
    });

    /*
    |-----------------------------------------------------------------------
    | 与公开用户相关
    |-----------------------------------------------------------------------
    |
    | 定于公开用户的相关信息路由
    |
    */

    /*
    | 找人
    */
    $api->group(['prefix' => 'user'], function (RouteContract $api) {
        // @get find users by phone
        $api->post('/find-by-phone', API2\FindUserController::class.'@findByPhone');

        // @get popular users
        $api->get('/populars', API2\FindUserController::class.'@populars');

        // @get latest users
        $api->get('/latests', API2\FindUserController::class.'@latests');

        // @get recommended users
        $api->get('/recommends', API2\FindUserController::class.'@recommends');

        // @get search name
        $api->get('/search', API2\FindUserController::class.'@search');

        // @get find users by user tags
        $api->get('/find-by-tags', API2\FindUserController::class.'@findByTags');
    });

    $api->group(['prefix' => 'users'], function (RouteContract $api) {

        /*
        | 创建用户
        */

        $api->post('/', API2\UserController::class.'@store')
            ->middleware('sensitive:name');

        /*
        | 批量获取用户
        */

        $api->get('/', API2\UserController::class.'@index');

        /*
        | 获取单个用户资源
         */

        $api->get('/{user}', API2\UserController::class.'@show');

        // 获取用户关注者
        $api->get('/{user}/followers', API2\UserFollowController::class.'@followers');

        // 获取用户关注的用户
        $api->get('/{user}/followings', API2\UserFollowController::class.'@followings');

        // Get the user's tags.
        // @GET /api/v2/users/:user/tags
        $api->get('/{user}/tags', API2\TagUserController::class.'@userTgas');
    });

    // Retrieve user password.
    $api->put('/user/retrieve-password', API2\ResetPasswordController::class.'@retrieve');

    // IAP帮助页
    $api->view('/currency/apple-iap/help', 'apple-iap-help');

    /*
    |-----------------------------------------------------------------------
    | Define a route that requires user authentication.
    |-----------------------------------------------------------------------
    |
    | The routes defined here are routes that require the user to
    | authenticate to access.
    |
    */

    $api->group(['middleware' => 'auth:api'], function (RouteContract $api) {

        /*
        |--------------------------------------------------------------------
        | Define the current authentication user to operate the route.
        |--------------------------------------------------------------------
        |
        | Define the routes associated with the current authenticated user,
        | such as getting your current user, updating user data, and so on.
        |
        */

        $api->group(['prefix' => 'user'], function (RouteContract $api) {

            /*
            | 获取当前用户
            */

            $api->get('/', API2\CurrentUserController::class.'@show');

            // Update the authenticated user
            $api->patch('/', API2\CurrentUserController::class.'@update');

            // Update phone or email of the authenticated user.
            $api->put('/', API2\CurrentUserController::class.'@updatePhoneOrMail');

            // 查看用户未读消息统计
            $api->get('/unread-count', API2\UserUnreadCountController::class.'@index');

            /*
            | 用户收到的评论
            */

            $api->get('/comments', API2\UserCommentController::class.'@index');

            /*
            | 用户收到的赞
             */

            $api->get('/likes', API2\UserLikeController::class.'@index');

            // User certification.
            $api->group(['prefix' => 'certification'], function (RouteContract $api) {

                // Send certification.
                $api->post('/', API2\UserCertificationController::class.'@store');

                // Update certification.
                $api->patch('/', API2\UserCertificationController::class.'@update');

                // Get user certification.
                $api->get('/', API2\UserCertificationController::class.'@show');
            });

            // send a feedback.
            $api->post('/feedback', API2\SystemController::class.'@createFeedback');

            // get a list of system conversation.
            $api->get('/conversations', API2\SystemController::class.'@getConversations');

            /*
            | 用户关注
             */

            $api->group(['prefix' => 'followings'], function (RouteContract $api) {

                // 我关注的人列表
                $api->get('/', API2\CurrentUserController::class.'@followings');

                // 关注一个用户
                $api->put('/{target}', API2\CurrentUserController::class.'@attachFollowingUser');

                // 取消关注一个用户
                $api->delete('/{target}', API2\CurrentUserController::class.'@detachFollowingUser');
            });

            $api->group(['prefix' => 'followers'], function (RouteContract $api) {

                // 获取关注我的用户
                $api->get('/', API2\CurrentUserController::class.'@followers');
            });

            // 获取相互关注的用户
            $api->get('/follow-mutual', API2\CurrentUserController::class.'@followMutual');

            // Reset password.
            $api->put('/password', API2\ResetPasswordController::class.'@reset');

            // The tags route of the authenticated user.
            // @Route /api/v2/user/tags
            $api->group(['prefix' => 'tags'], function (RouteContract $api) {

                // Get all tags of the authenticated user.
                // @GET /api/v2/user/tags
                $api->get('/', API2\TagUserController::class.'@index');

                // Attach a tag for the authenticated user.
                // @PUT /api/v2/user/tags/:tag
                $api->put('/{tag}', API2\TagUserController::class.'@store');

                // Detach a tag for the authenticated user.
                // @DELETE /api/v2/user/tags/:tag
                $api->delete('/{tag}', API2\TagUserController::class.'@destroy');
            });

            // 打赏用户
            tap($api->post('/{target}/rewards', API2\UserRewardController::class.'@store'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });

            // 新版打赏用户
            tap($api->post('/{target}/new-rewards', API2\NewUserRewardController::class.'@store'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });

            /*
             * 解除手机号码绑定.
             *
             * @DELETE /api/v2/user/phone
             * @author Seven Du <shiweidu@outlook.com>
             */
            $api->delete('/phone', API2\UserPhoneController::class.'@delete');

            /*
             * 解除用户邮箱绑定.
             *
             * @DELETE /api/v2/user/email
             * @author Seven Du <shiweidu@outlook.com>
             */
            $api->delete('/email', API2\UserEmailController::class.'@delete');

            $api->post('/black/{targetUser}', API2\UserBlacklistController::class.'@black');
            $api->delete('/black/{targetUser}', API2\UserBlacklistController::class.'@unBlack');
            $api->get('/blacks', API2\UserBlacklistController::class.'@blackList');
        });

        /*
        |--------------------------------------------------------------------
        | Wallet routing.
        |--------------------------------------------------------------------
        |
        | Defines routes related to wallet operations.
        |
        */

        $api->group(['prefix' => 'wallet'], function (RouteContract $api) {
            /*
            | 获取提现记录
             */
            $api->get('/cashes', API2\WalletCashController::class.'@show');

            /*
            | 发起提现申请
             */

            tap($api->post('/cashes', API2\WalletCashController::class.'@store'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });

            /*
            | 充值钱包余额
             */

            $api->post('/recharge', API2\WalletRechargeController::class.'@store');

            /*
            | 获取凭据列表
             */

            $api->get('/charges', API2\WalletChargeController::class.'@list');

            /*
            | 获取单条凭据
             */

            $api->get('/charges/{charge}', API2\WalletChargeController::class.'@show');
        });

        // 新版支付
        $api->group(['prefix' => 'walletRecharge'], function (RouteContract $api) {
            // 申请凭据入口
            $api->post('/orders', API2\PayController::class.'@entry');

            // 手动检测支付宝订单的支付状态
            $api->post('/checkOrders', API2\PayController::class.'@checkAlipayOrder');
        });

        $api->group(['prefix' => 'currencyRecharge'], function (RouteContract $api) {
            $api->post('/orders', API2\CurrencyPayController::class.'@entry');
            $api->post('/checkOrders', API2\CurrencyPayController::class.'@checkAlipayOrder');
        });

        // 新版钱包
        $api->group(['prefix' => 'plus-pay'], function (RouteContract $api) {

            // 获取提现记录
            $api->get('/cashes', API2\NewWalletCashController::class.'@show');

            // 发起提现申请
            tap($api->post('/cashes', API2\NewWalletCashController::class.'@store'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });

            // 发起充值
            $api->post('/recharge', API2\NewWalletRechargeController::class.'@store');

            // 钱包订单列表
            $api->get('/orders', API2\NewWalletRechargeController::class.'@list');

            // 取回凭据
            $api->get('/orders/{order}', API2\NewWalletRechargeController::class.'@retrieve');

            // 转账
            tap($api->post('/transfer', API2\TransferController::class.'@transfer'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });

            // 转换积分
            tap($api->post('/transform', API2\NewWalletRechargeController::class.'@transform'), function ($route) {
                $route->setAction(array_merge($route->getAction(), [
                    'middleware' => [
                        'cors-should',
                        'bindings',
                        'throttle:5,0.1',
                        'auth:api',
                    ],
                ]));
            });
        });

        /*
        | 检查一个文件的 md5, 如果存在着创建一个 file with id.
         */

        $api->get('/files/uploaded/{hash}', API2\FilesController::class.'@uploaded');

        /*
        | 上传一个文件
         */

        $api->post('/files', API2\FilesController::class.'@store');

        /*
        | 显示一个付费节点
         */

        $api->get('/purchases/{node}', API2\PurchaseController::class.'@show');

        /*
        | 为一个付费节点支付
         */

        $api->post('/purchases/{node}', API2\PurchaseController::class.'@pay');

        $api->group(['prefix' => 'report'], function (RouteContract $api) {

            // 举报一个用户
            $api->post('/users/{user}', API2\ReportController::class.'@user');

            // 举报一条评论
            $api->post('/comments/{comment}', API2\ReportController::class.'@comment');
        });

        /*
        | 环信
         */
        $api->group(['prefix' => 'easemob'], function (RouteContract $api) {

            // 注册环信用户(单个)
            $api->post('register/{user_id}', EaseMobIm\EaseMobController::class.'@createUser')->where(['user_id' => '[0-9]+']);

            //批量注册环信用户
            $api->post('/register', EaseMobIm\EaseMobController::class.'@createUsers');

            // 为未注册环信用户注册环信（兼容老用户）
            $api->post('/register-old-users', EaseMobIm\EaseMobController::class.'@registerOldUsers');

            // 重置用户环信密码
            $api->put('/password', EaseMobIm\EaseMobController::class.'@resetPassword');

            // 获取环信用户密码
            $api->get('/password', EaseMobIm\EaseMobController::class.'@getPassword');

            // 创建群组
            $api->post('/group', EaseMobIm\GroupController::class.'@store');

            // 修改群组信息
            $api->patch('/group', EaseMobIm\GroupController::class.'@update');

            // 删除群组
            $api->delete('/group', EaseMobIm\GroupController::class.'@delete');

            // 获取指定群组信息
            $api->get('/group', EaseMobIm\GroupController::class.'@getGroup');
            $api->get('/groups', EaseMobIm\GroupController::class.'@newGetGroup');

            // 获取群头像
            $api->get('/group/face', EaseMobIm\GroupController::class.'@getGroupFace');

            // 添加群成员
            $api->post('/group/member', EaseMobIm\GroupController::class.'@addGroupMembers');

            // 移除群成员
            $api->delete('/group/member', EaseMobIm\GroupController::class.'@removeGroupMembers');

            // 获取聊天记录Test
            $api->get('/group/message', EaseMobIm\EaseMobController::class.'@getMessage');
        });

        // 积分部分
        $api->group(['prefix' => 'currency'], function (RouteContract $api) {
            // 积分流水
            $api->get('/orders', API2\CurrencyRechargeController::class.'@index');

            // 发起充值
            $api->post('/recharge', API2\CurrencyRechargeController::class.'@store');

            // 取回凭据
            $api->get('/orders/{order}', API2\CurrencyRechargeController::class.'@retrieve');

            // 发起提现
            $api->post('/cash', API2\CurrencyCashController::class.'@store');

            // 通过积分购买付费节点
            $api->post('/purchases/{node}', API2\PurchaseController::class.'@payByCurrency');

            // 调用IAP发起充值
            $api->post('/recharge/apple-iap', API2\CurrencyApplePayController::class.'@store');

            // IAP支付完成后的验证
            $api->post('/orders/{order}/apple-iap/verify', API2\CurrencyApplePayController::class.'@retrieve');

            // IAP商品列表
            $api->get('/apple-iap/products', API2\CurrencyApplePayController::class.'@productList');

            // 积分商城（待开发）
            $api->view('/show', 'currency-developing');
        });
    });

    /*
     * 获取用户未读数信息
     */
    $api->get('/user/counts', \Zhiyi\Plus\API2\Controllers\UserCountsController::class.'@count');

    /*
     * 重置未读信息
     */
    $api->patch('/user/counts', \Zhiyi\Plus\API2\Controllers\UserCountsController::class.'@reset');

    // Feed group
    // @Route /api/v2/feed
    $api->group(['prefix' => 'feed'], function (RouteContract $api) {
        // Feed Topics Group
        // @Route /api/v2/feed/topics
        $api->group(['prefix' => 'topics'], function (RouteContract $api) {
            /*
             * Topic Index
             *
             * @Get /api/v2/feed/topics
             * @Param::query {q} Search topic name keyword.
             * @Param::query {limit} Featch data limit.
             * @Param::query {index} Featch data start index.
             * @Param::query {direction} Can be one of `asc` or `desc`.
             * @Param::query('only', 'string', 'The value is `hot`')
             * @Response::header('Status', 200, 'OK')
             * @Response::json('<pre>
             *  [{
             *   "id": 1,        // Topic ID
             *   "name": "Plus", // Topic name
             *   "logo": 2,      // Topic logo, file with ID
             *   "created_at": "2018-07-23T15:04:23Z" // Topic created datetime
             *  }]
             *  </pre>')
             */
            $api->get('', \Zhiyi\Plus\API2\Controllers\Feed\Topic::class.'@index');

            /*
             * Create an topic
             *
             * @Post /api/v2/feed/topics
             * @Param::input('name', 'string', 'The name of the topic.')
             * @Param::input('desc', 'string', 'The desc of the topic.')
             * @Param::input('logo', 'integer', 'The topic logo file with     ID.')
             * @Response::header('Status', 201, 'Created')
             * @Response::json('<pre>
             * {
             *     "id": 2 // Created topic id
             * }
             * </pre>')
             */
            $api->post('', \Zhiyi\Plus\API2\Controllers\Feed\Topic::class.'@create');

            /*
             * Edit an topic.
             *
             * @Patch /api/v2/feed/topics/:topicID
             * @Param::input('desc', 'string', 'The desc of the topic')
             * @Param::input('logo', 'integer', 'The topic logo file with ID')
             * @Response::header('Status', 204, 'No Content')
             */
            $api->patch('{topic}', \Zhiyi\Plus\API2\Controllers\Feed\Topic::class.'@update');

            /*
             * Get a single topic.
             *
             * @Get /api/v2/feed/topics/:topicID
             * @Response::header('Status', 200, 'OK')
             */
            $api->get('{topic}', \Zhiyi\Plus\API2\Controllers\Feed\Topic::class.'@show');

            /*
             * List feeds for a topic.
             *
             * @Get /api/v2/feed/topics/:topicID/feeds
             * @Param::query('limit', 'integer', 'The data limit, default `15`.')
             * @Param::query('index', 'integer', 'fetch data start index')
             * @Param::query('direction', 'string', 'Can be one of `asc` or `desc`.')
             * @Response::header('Status', 200, 'OK')
             * @Response::json('<pre>
             * [{
             *     ""
             * }]
             * </pre>')
             */
            $api->get('{topic}/feeds', Zhiyi\Plus\API2\Controllers\Feed\TopicFeed::class);

            /*
             *
             * List participants for a topic.
             *
             * @Get /api/v2/feed/topic/:topicID/participants
             * @Param::query('limit', 'integer', 'The data limit, default `15`.')
             * @Param::query('offset', 'integer', 'The data offset, default `0`.')
             * @Response::header('Status', 200, 'OK')
             * @Response::json('<pre>
             * [2, 3, 4, 5]
             * </pre>')
             */
            $api->get('{topic}/participants', \Zhiyi\Plus\API2\Controllers\Feed\TopicParticipant::class.'@index');
        });
    });

    /*
     * Follow a feed topic.
     *
     * @Put /api/v2/user/feed-topics/:topicID
     * @Response::header('Status', 204, 'No Content')
     */
    $api->put('user/feed-topics/{topicID}', \Zhiyi\Plus\API2\Controllers\Feed\TopicFollow::class.'@follow');

    /*
     * Unfollow a feed topic
     *
     * @Delete /api/v2/user/feed-topics/:topicID
     * @Response::header('Status', 204, 'No Content')
     */
    $api->delete('user/feed-topics/{topicID}', \Zhiyi\Plus\API2\Controllers\Feed\TopicFollow::class.'@unfollow');

    /*
     * Report a feed topic.
     *
     * @Put /api/v2/user/report-feed-topics/:topicID
     * @Patam::query('message', 'string', 'Report the feed topic message.')
     * @Response::header('Status', 204, 'No Content')
     */
    $api->put('user/report-feed-topics/{topic}', \Zhiyi\Plus\API2\Controllers\Feed\TopicReport::class);

    /*
     * List at me messages.
     *
     * @Get /api/v2/user/message/atme
     * @Param::query('limit', 'integer', 'The query data limit.')
     * @Param::query('index', 'integer', 'The query start index.')
     * @param::query('direction', 'enum:asc,desc', 'The data order by id direction.')
     * @Response::header('Sttaus', 200, 'OK')
     * @response::json('<pre>
     * [
     *  {
     *      "id": 1,
     *      "user_id": 1,
     *      "resourceable": {
     *          "type": "feeds",
     *          "id": "id"
     *      },
     *      "created_at": "2018-08-13T08:06:54Z"
     *  }
     * ]
     * </pre>')
     */
    $api->get('user/message/atme', \Zhiyi\Plus\API2\Controllers\User\Message\At::class);

    /*
     * List all comments
     *
     * @Get /api/v2/comments
     * @Param::query('limit', 'integer', 'The query data limit.')
     * @Param::query('index', 'integer', 'The query data start index.')
     * @Param::query('direction', 'enum:asc,desc', 'The data order by id direction.')
     * @Param::query('author', 'integer', 'The comment author user id')
     * @Param::query('for_user', 'integer', 'The comment target user id')
     * @Param::query('for_type', 'enum:all,target,reply', 'for user type')
     * @Param::query('id', 'string', 'Comment IDs, using `,` slicing')
     * @Param::query('resourceable_id', 'string', 'Resourceable IDs, using `,` slicing')
     * @Param::query('resourceable_type', 'string', 'Resourceabe type name')
     * @Response::header('Sttaus', 200, 'OK')
     * @Response::json('<pre>
     * [
     *     {
     *          "id": 1,
     *          "user_id": 1,
     *          "target_user": 2,
     *          "reply_user": 3,
     *          "body": "Hi, I love you.",
     *          "resourceable": {
     *              "type": "feeds",
     *              "id": 1
     *          },
     *          "created_at": "2018-08-15T05:57:01Z"
     *     }
     * ]
     * </pre>')
     */
    $api->get('comments', \Zhiyi\Plus\API2\Controllers\Comment::class.'@index');

    // List all authed user abilities
    $api->get('user/abilities', \Zhiyi\Plus\API2\Controllers\User\AbilityController::class);

    // User Notifications
    $api->get('user/notifications', \Zhiyi\Plus\API2\Controllers\NotificationController::class.'@index');
    $api->patch('user/notifications', \Zhiyi\Plus\API2\Controllers\NotificationController::class.'@update');
    $api->get('user/notification-statistics', \Zhiyi\Plus\API2\Controllers\NotificationController::class.'@statistics');
    $api->patch('user/clear-follow-notification', \Zhiyi\Plus\API2\Controllers\NotificationController::class.'@clearFollowNotifications');

    // News Posts
    $api->delete('news/posts/{post}', \Zhiyi\Plus\API2\Controllers\NewsPostController::class.'@destroy');
});
