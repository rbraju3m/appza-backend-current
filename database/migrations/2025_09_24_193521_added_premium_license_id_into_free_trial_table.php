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
            // Make column nullable
            $table->unsignedBigInteger('premium_license_id')->nullable()->index();

            // Foreign key with ON DELETE SET NULL
            $table->foreign('premium_license_id')
                ->references('id')
                ->on('appza_fluent_license_info')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
