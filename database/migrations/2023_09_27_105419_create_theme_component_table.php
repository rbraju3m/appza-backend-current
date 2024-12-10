<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemeComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_theme_component', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id')->unsigned();
            $table->unsignedBigInteger('parent_id')->unsigned()->nullable();
            $table->unsignedBigInteger('component_parent_id')->unsigned()->nullable();
            $table->unsignedBigInteger('component_id')->unsigned();
            $table->unsignedBigInteger('theme_config_id')->unsigned()->nullable();
            $table->unsignedBigInteger('theme_page_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('theme_id')->references('id')->on('appfiy_theme')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('appfiy_theme_component')->onDelete('cascade');
            $table->foreign('component_parent_id')->references('id')->on('appfiy_component')->onDelete('cascade');
            $table->foreign('component_id')->references('id')->on('appfiy_component')->onDelete('cascade');
            $table->foreign('theme_config_id')->references('id')->on('appfiy_theme_config')->onDelete('cascade');
            $table->foreign('theme_page_id')->references('id')->on('appfiy_theme_page')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_theme_component');
    }
}
