<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedFieldIntoGlobalConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_global_config', function (Blueprint $table) {
            $table->string('image_properties_padding_x',)->default(8.0);
            $table->string('image_properties_padding_y',)->default(8.0);
            $table->string('image_properties_margin_x')->default(0.0);
            $table->string('image_properties_margin_y')->default(0.0);

            $table->string('icon_properties_padding_x',)->default(8.0);
            $table->string('icon_properties_padding_y',)->default(8.0);
            $table->string('icon_properties_margin_x')->default(0.0);
            $table->string('icon_properties_margin_y')->default(0.0);
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
