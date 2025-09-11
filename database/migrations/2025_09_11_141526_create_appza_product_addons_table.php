<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appza_product_addons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id')->unsigned()->index('product_id');
            $table->string('addon_name',100);
            $table->string('addon_slug',100);
            $table->json('addon_json_info');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->foreign('product_id')->references('id')->on('appza_fluent_informations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appza_product_addons');
    }
};
