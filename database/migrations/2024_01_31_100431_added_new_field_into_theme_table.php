<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedNewFieldIntoThemeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_theme', function (Blueprint $table) {
            $table->string('background_color',20)->nullable();
            $table->string('font_family',20)->nullable();
            $table->string('text_color',20)->nullable();
            $table->decimal('font_size',10,2)->nullable();
            $table->string('transparent',20)->nullable();
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
