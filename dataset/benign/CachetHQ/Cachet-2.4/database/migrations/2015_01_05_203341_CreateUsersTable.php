<?php

/*
 * This file is part of Cachet.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('username');
            $table->string('password');
            $table->rememberToken();
            $table->string('email');
            $table->string('api_key');
            $table->boolean('active')->default(1);
            $table->tinyInteger('level')->default(2);
            $table->timestamps();

            $table->index('remember_token');
            $table->index('active');
            $table->unique('username');
            $table->unique('api_key');
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('users');
    }
}
