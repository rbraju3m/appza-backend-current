<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComponentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appfiy_component', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->unsigned()->nullable();
            $table->unsignedBigInteger('layout_type_id')->unsigned()->nullable();
            $table->string('name',255);
            $table->string('slug',255);
            $table->string('label',255)->nullable();
            $table->string('icon_code',100)->nullable();
            $table->string('event',255)->nullable();
            $table->json('scope')->nullable();
            $table->string('class_type',255)->nullable();
            $table->longText('app_icon')->nullable();
            $table->longText('web_icon')->nullable();
            $table->boolean('is_multiple')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('layout_type_id')->references('id')->on('appfiy_layout_type')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('appfiy_component')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appfiy_component');
    }
}
