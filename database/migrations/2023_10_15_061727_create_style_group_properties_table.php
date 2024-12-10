<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStyleGroupPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_style_group_properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('style_group_id')->unsigned()->nullable();
            $table->unsignedBigInteger('style_property_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('style_group_id')->references('id')->on('appfiy_style_group')->onDelete('cascade');
            $table->foreign('style_property_id')->references('id')->on('appfiy_style_properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_style_group_properties');
    }
}
