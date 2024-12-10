<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedPaddingMarginIntoGlobalConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_global_config', function (Blueprint $table) {
            $table->string('padding_x',)->default(8);
            $table->string('padding_y')->default(16);
            $table->string('margin_x')->default(0);
            $table->string('margin_y')->default(0);
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
