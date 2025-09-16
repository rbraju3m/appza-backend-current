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
        Schema::create('license_logics', function (Blueprint $table) {
            $table->id();
            $table->string('name',100);
            $table->string('slug',100)->unique();
            $table->enum('event', ['expiration','grace','invalid']);
            $table->enum('direction', ['before','equal','after'])->nullable(); // null for invalid
            $table->unsignedInteger('from_days')->nullable();
            $table->unsignedInteger('to_days')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_logics');
    }
};
