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
        Schema::create('appza_product_addons_versions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('addon_id')->unsigned()->index('addon_id');
            $table->string('version');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->foreign('addon_id')->references('id')->on('appza_product_addons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appza_product_addons_versions');
    }
};
