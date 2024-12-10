<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedFieldIntoBuildDomainTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appfiy_build_domain', function (Blueprint $table) {
            $table->string('license_key');
            $table->unsignedBigInteger('version_id')->unsigned()->nullable();
            $table->foreign('version_id')->references('id')->on('appfiy_app_versions');

            $table->integer('fluent_id')->nullable();
            $table->string('app_name', 255)->nullable();
            $table->string('app_logo', 255)->nullable();
            $table->string('app_splash_screen_image', 255)->nullable();
            $table->string('build_version')->nullable();
            $table->string('ios_issuer_id')->nullable();
            $table->string('ios_key_id')->nullable();
            $table->string('ios_p8_file_content')->nullable();
            $table->string('team_id')->nullable();
            $table->boolean('is_android')->default(true);
            $table->boolean('is_ios')->default(false);
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
