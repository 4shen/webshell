<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMinPriceAndMaxPriceColumnInProductFlatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_flat', function (Blueprint $table) {
            $table->decimal('min_price', 12, 4)->nullable();
            $table->decimal('max_price', 12, 4)->nullable();
            $table->decimal('special_price', 12, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_flat', function (Blueprint $table) {
            $table->dropColumn('min_price');
            $table->dropColumn('max_price');
        });
    }
}
