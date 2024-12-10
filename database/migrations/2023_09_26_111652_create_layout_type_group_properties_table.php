<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLayoutTypeGroupPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_layout_type_group_style', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('layout_type_id')->unsigned()->nullable();
            $table->unsignedBigInteger('property_id')->unsigned()->nullable();
            $table->timestamps();
            $table->foreign('layout_type_id')->references('id')->on('appfiy_layout_type')->onDelete('cascade');
            $table->foreign('property_id')->references('id')->on('appfiy_layout_type_style_properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_layout_type_group_style');
    }
}
