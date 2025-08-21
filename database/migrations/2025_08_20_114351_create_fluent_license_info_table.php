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
        Schema::create('appza_fluent_license_info', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('build_domain_id')->unsigned()->index('build_domain_id');
            $table->string('site_url')->index('site_url');
            $table->integer('product_id')->index('product_id');
            $table->integer('variation_id')->index('variation_id');
            $table->string('license_key')->index('license_key');
            $table->string('activation_hash')->index('activation_hash');
            $table->string('product_title')->nullable();
            $table->string('variation_title')->nullable();
            $table->integer('activation_limit')->default(0);
            $table->integer('activations_count')-> default(0);
            $table->string('expiration_date')->nullable();
            $table->timestamps();
            $table->foreign('build_domain_id')->references('id')->on('appfiy_build_domain')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluent_license_info');
    }
};
