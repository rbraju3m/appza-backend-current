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
        Schema::create('appza_fluent_informations', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug',50)->unique()->index();
            $table->string('api_url',255);
            $table->integer('item_id');
            $table->string('item_name',255)->nullable();
            $table->string('item_description',255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appza_fluent_informations');
    }
};
