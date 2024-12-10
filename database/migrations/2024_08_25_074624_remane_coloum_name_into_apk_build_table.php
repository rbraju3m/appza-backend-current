<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemaneColoumNameIntoApkBuildTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_apk_build_history', function (Blueprint $table) {
            $table->renameColumn('team_id', 'ios_team_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appfiy_apk_build_history', function (Blueprint $table) {
            $table->renameColumn('team_id', 'ios_team_id');
        });
    }
}
