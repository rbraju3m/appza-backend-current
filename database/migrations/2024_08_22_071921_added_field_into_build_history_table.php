<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedFieldIntoBuildHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_apk_build_history', function (Blueprint $table) {
            $table->string('ios_issuer_id')->nullable();
            $table->string('ios_key_id')->nullable();
            $table->string('ios_p8_file_content')->nullable();
            $table->string('team_id')->nullable();
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
