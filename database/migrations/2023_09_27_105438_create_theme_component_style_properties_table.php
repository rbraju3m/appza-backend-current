<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemeComponentStylePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_theme_component_style', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('theme_id')->unsigned();
            $table->unsignedBigInteger('theme_component_id')->unsigned();
            $table->string('name',255);
            $table->string('input_type',255);
            $table->string('value',255);
            $table->longText('default_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('theme_id')->references('id')->on('appfiy_theme')->onDelete('cascade');
            $table->foreign('theme_component_id')->references('id')->on('appfiy_theme_component')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_theme_component_style');
    }
}
