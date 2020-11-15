<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CachetHQ\Cachet\Integrations\Contracts\System;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterTableIncidentsRemoveScheduledColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // We need a better way of handling data migrations...
        $system = app(System::class);
        $prefix = $system->getTablePrefix();
        DB::update("INSERT INTO {$prefix}schedules (name, message, scheduled_at, created_at, updated_at) SELECT name, message, scheduled_at, created_at, updated_at FROM {$prefix}incidents WHERE scheduled_at IS NOT NULL");

        DB::table('incidents')->whereNotNull('scheduled_at')->delete();

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->timestamp('scheduled_at')->before('created_at')->nullable()->default(null);
        });
    }
}
