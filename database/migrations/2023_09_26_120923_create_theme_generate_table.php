<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateThemeGenerateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_theme', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('image')->nullable();
            $table->unsignedBigInteger('appbar_id')->unsigned()->nullable();
            $table->unsignedBigInteger('navbar_id')->unsigned()->nullable();
            $table->unsignedBigInteger('drawer_id')->unsigned()->nullable();
            $table->json('appbar_navbar_drawer')->nullable();
            $table->boolean('is_default')->default(true);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('appbar_id')->references('id')->on('appfiy_global_config')->onDelete('cascade');
            $table->foreign('navbar_id')->references('id')->on('appfiy_global_config')->onDelete('cascade');
            $table->foreign('drawer_id')->references('id')->on('appfiy_global_config')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_theme');
    }
}
