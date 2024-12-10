<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedNewFieldIntoGlobalConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_global_config', function (Blueprint $table) {
            $table->renameColumn('general_text_properties_color','text_properties_color');
            $table->string('icon_properties_size',255)->nullable();
            $table->string('icon_properties_color',255)->nullable();
            $table->string('icon_properties_shape_radius',255)->nullable();
            $table->string('icon_properties_background_color',255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
