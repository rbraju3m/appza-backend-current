<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalConfigComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_global_config_component', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('global_config_id')->unsigned()->nullable();
            $table->unsignedBigInteger('component_id')->unsigned()->nullable();
            $table->string('component_position',255)->nullable();
            $table->timestamps();
            $table->foreign('global_config_id')->references('id')->on('appfiy_global_config')->onDelete('cascade');
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
        Schema::dropIfExists('appfiy_global_config_component');
    }
}
