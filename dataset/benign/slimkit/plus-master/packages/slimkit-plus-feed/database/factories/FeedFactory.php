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

use Faker\Generator as Faker;

$factory->define(\Zhiyi\Component\ZhiyiPlus\PlusComponentFeed\Models\Feed::class, function (Faker $faker) {
    return [
        'feed_content' => $faker->paragraph,
        'feed_from' => 5,
        'user_id' => $faker->randomNumber(),
        'feed_mark' => $faker->unique()->randomNumber(),
    ];
});
