<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_theme_page', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id')->unsigned()->nullable();
            $table->unsignedBigInteger('page_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('theme_id')->references('id')->on('appfiy_theme')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('appfiy_page')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_theme_page');
    }
}
