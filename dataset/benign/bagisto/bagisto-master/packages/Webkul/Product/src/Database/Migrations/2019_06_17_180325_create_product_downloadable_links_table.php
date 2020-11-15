<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductDownloadableLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_downloadable_links', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url')->nullable();
            $table->string('file')->nullable();
            $table->string('file_name')->nullable();
            $table->string('type');
            $table->decimal('price', 12, 4)->default(0);
            $table->string('sample_url')->nullable();
            $table->string('sample_file')->nullable();
            $table->string('sample_file_name')->nullable();
            $table->string('sample_type')->nullable();
            $table->integer('downloads')->default(0);
            $table->integer('sort_order')->nullable();

            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_downloadable_links');
    }
}
