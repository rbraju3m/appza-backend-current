<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponentParentChildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_component_parent_child', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->unsigned();
            $table->unsignedBigInteger('child_id')->unsigned();
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('appfiy_component')->onDelete('cascade');
            $table->foreign('child_id')->references('id')->on('appfiy_component')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_component_parent_child');
    }
}
