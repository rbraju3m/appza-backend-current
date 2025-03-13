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
        Schema::table('appfiy_page', function (Blueprint $table) {
            $table->integer('component_limit')->nullable()->default(null)->change(); // Change default value
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appfiy_page', function (Blueprint $table) {
            $table->integer('component_limit')->default(0)->nullable(false)->change(); // Revert to old default value
        });
    }
};
