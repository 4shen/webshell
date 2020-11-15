<?php

declare(strict_types=1);

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

use function Zhiyi\Component\ZhiyiPlus\PlusComponentMusic\base_path as component_base_path;

Route::middleware('web')
    ->namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentMusic\\Controllers')
    ->group(component_base_path('/routes/web.php'));

Route::middleware('web')
    ->prefix('/music/admin')
    ->namespace('Zhiyi\\Component\\ZhiyiPlus\\PlusComponentMusic\\AdminControllers')
    ->group(component_base_path('/routes/admin.php'));

Route::prefix('api/v2')
    ->middleware('api')
    ->namespace('Zhiyi\\Plus\\Packages\\Music\\API\\Controllers')
    ->group(component_base_path('/routes/api.php'));
