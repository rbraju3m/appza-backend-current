<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemeConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_theme_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id')->unsigned()->nullable();
            $table->unsignedBigInteger('global_config_id')->unsigned()->nullable();
            $table->string('mode',255);
            $table->string('name',255);
            $table->string('slug',255);
            $table->string('background_color',255)->nullable();
            $table->string('layout',255)->nullable();
            $table->integer('icon_theme_size')->nullable();
            $table->string('icon_theme_color',255)->nullable();
            $table->double('shadow',10,2)->nullable();
            $table->string('icon',255)->nullable();
            $table->boolean('automatically_imply_leading')->default(false);
            $table->boolean('center_title')->default(false);
            $table->string('flexible_space',255)->nullable();
            $table->string('bottom',255)->nullable();
            $table->string('shape_type',255)->nullable();
            $table->integer('shape_border_radius')->nullable();
            $table->double('toolbar_opacity',10,2)->nullable();
            $table->string('actions_icon_theme_color',255)->nullable();
            $table->integer('actions_icon_theme_size')->nullable();
            $table->integer('title_spacing')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('theme_id')->references('id')->on('appfiy_theme')->onDelete('cascade');
            $table->foreign('global_config_id')->references('id')->on('appfiy_global_config')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_theme_config');
    }
}
