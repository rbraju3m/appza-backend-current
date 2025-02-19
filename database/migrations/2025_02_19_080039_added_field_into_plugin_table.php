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
        Schema::table('appza_supports_plugin', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->longText('others')->nullable();
            $table->boolean('is_disable')->default(false);
            $table->string('image')->nullable();
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
