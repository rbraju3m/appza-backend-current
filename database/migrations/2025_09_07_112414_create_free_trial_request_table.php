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
        Schema::create('appza_free_trial_request', function (Blueprint $table) {
            $table->id();
            $table->string('product_slug',50)->index('product_slug');
            $table->string('site_url')->index('site_url');
            $table->string('name');
            $table->string('email');
            $table->integer('product_id')->nullable();
            $table->integer('variation_id')->nullable();
            $table->string('license_key',50)->nullable()->index('license_key');
            $table->string('activation_hash',50)->nullable()->index('activation_hash');
            $table->string('product_title')->nullable();
            $table->string('variation_title')->default('Free');
            $table->integer('activation_limit')->default(1);
            $table->integer('activations_count')-> default(1);
            $table->date('expiration_date');
            $table->date('grace_period_date')->nullable();
            $table->string('status',20)->default("valid");
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('free_trial_request');
    }
};
