<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppfiyBuildHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_apk_build_history', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('version_id')->unsigned()->nullable();
            $table->foreign('version_id')->references('id')->on('appfiy_app_versions');

            $table->unsignedBigInteger('build_domain_id')->unsigned()->nullable();
            $table->foreign('build_domain_id')->references('id')->on('appfiy_build_domain');

            $table->integer('fluent_id')->nullable();
            $table->string('app_name', 255);
            $table->string('app_logo', 255);
            $table->string('app_splash_screen_image', 255);
            $table->string('build_version')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_build_history');
    }
}
