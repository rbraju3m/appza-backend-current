<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateThemePageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_theme_page', function (Blueprint $table) {
            $table->string('persistent_footer_buttons')->default(null)->nullable();
            $table->string('background_color',20)->default(null)->nullable();
            $table->string('border_color',20)->default(null)->nullable();
            $table->string('border_radius',20)->default(null)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('', function (Blueprint $table) {

        });
    }
}
