<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDatatypeForDecimalValueIntoGlobalConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_global_config', function (Blueprint $table) {
            $table->double('icon_theme_size',10,2)->nullable()->change();
            $table->double('shape_border_radius',10,2)->nullable()->change();
            $table->double('actions_icon_theme_size',10,2)->nullable()->change();
            $table->double('title_spacing',10,2)->nullable()->change();
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
