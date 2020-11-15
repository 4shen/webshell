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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendUserCheckInExtrasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 签到统计
        if (! Schema::hasColumn('user_extras', 'checkin_count')) {
            Schema::table('user_extras', function (Blueprint $table) {
                $table->integer('checkin_count')->unsigned()->nullable()->default(0)->comment('用户签到统计');
            });
        }

        // 用户连续
        if (! Schema::hasColumn('user_extras', 'last_checkin_count')) {
            Schema::table('user_extras', function (Blueprint $table) {
                $table->integer('last_checkin_count')->unsigned()->nullable()->default(0)->comment('用户连续签到统计');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('user_extras', 'checkin_count')) {
            Schema::table('user_extras', function (Blueprint $table) {
                $table->dropColumn('checkin_count');
            });
        }

        if (Schema::hasColumn('user_extras', 'last_checkin_count')) {
            Schema::table('user_extras', function (Blueprint $table) {
                $table->dropColumn('last_checkin_count');
            });
        }
    }
}
