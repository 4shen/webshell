<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Permission_roles table migration
 * Many-to-many mapping between permissions and roles.
 * Version 4.0.0.
 *
 * See https://laravel.com/docs/5.8/migrations#tables
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionRolesTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('permission_roles')) {
            $this->schema->create('permission_roles', function (Blueprint $table) {
                $table->integer('permission_id')->unsigned();
                $table->integer('role_id')->unsigned();
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->primary(['permission_id', 'role_id']);
                //$table->foreign('permission_id')->references('id')->on('permissions');
                //$table->foreign('role_id')->references('id')->on('roles');
                $table->index('permission_id');
                $table->index('role_id');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->drop('permission_roles');
    }
}
