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
        Schema::table('appza_free_trial_request', function (Blueprint $table) {
            $table->boolean('is_fluent_license_check')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
