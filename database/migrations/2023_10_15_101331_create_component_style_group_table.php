<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponentStyleGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_component_style_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('component_id')->unsigned()->nullable();
            $table->unsignedBigInteger('style_group_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('style_group_id')->references('id')->on('appfiy_style_group')->onDelete('cascade');
            $table->foreign('component_id')->references('id')->on('appfiy_component')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_component_style_group');
    }
}
