<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */
return [
    /**
     * Customer 模块的配置，您可以在@apphtml5/config/fecshop_local_modules/Customer.php 
     * 中进行配置，二开，或者重写该模块（在上面路径中如果文件不存在，自行新建配置文件。）
     */
    'customer' => [
        'class' => '\fecshop\app\apphtml5\modules\Customer\Module',
        /**
         * 模块内部的params配置。
         */
        'params'=> [
            //'register' => [
                // 账号注册成功后，是否自动登录
            //    'successAutoLogin' => true,
                // 注册登录成功后，跳转的url
            //    'loginSuccessRedirectUrlKey' => 'customer/account',
                // 注册页面的验证码是否开启
            //    'registerPageCaptcha' => true,

            //],
            //'login' => [
                // 在登录页面 customer/account/login 页面登录成功后跳转的urlkey，
            //    'loginPageSuccessRedirectUrlKey' => 'customer/account',
                // 在其他页面的弹框方式登录的账号成功后，的页面跳转，如果是false，则代表返回原来的页面。
            //    'otherPageSuccessRedirectUrlKey' => false,
                // 登录页面的验证码是否开启
            //    'loginPageCaptcha' => false,
                // 邮件信息，登录账号后是否发送邮件

            //],
            //'forgotPassword' => [
                // 忘记密码页面的验证码是否开启
            //    'forgotCaptcha' => true,

            //],

            'leftMenu'  => [
                'Account Information' => 'customer/editaccount',
                'Address Book' => 'customer/address',
                'My Orders' => 'customer/order',
                'My Product Reviews' => 'customer/productreview',
                'My Favorite' => 'customer/productfavorite',

            ],

            //'contacts'    => [
                // 联系我们页面的验证码是否开启
            //    'contactsCaptcha' => true,
                // 设置联系我们邮箱，如果不设置，则从email service配置中读取。
                //'address' => '',
            //],
            //'newsletterSubscribe' => [

            //],
        ],
    ],
];
