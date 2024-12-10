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
        Schema::create('build_orders', function (Blueprint $table) {
            $table->id();
            $table->string('package_name');
            $table->string('app_name');
            $table->string('domain');
            $table->string('base_suffix');
            $table->string('base_url');
            $table->string('build_number');
            $table->string('icon_url');
            $table->char('build_target', 10);

            // Android Only Properties
            $table->string('jks_url')->nullable();
            $table->json('key_properties_url')->nullable();

            // iOS Only Properties
            $table->string('issuer_id')->nullable();
            $table->string('key_id')->nullable();
            $table->string('api_key_url')->nullable();
            $table->string('team_id')->nullable();
            $table->string('app_identifier')->nullable();

            $table->char('status', 10)->default(\App\Enums\BuildStatus::Pending->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('build_orders');
    }
};
