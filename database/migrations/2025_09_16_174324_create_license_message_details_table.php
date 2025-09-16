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
        Schema::create('license_message_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->nullable()->constrained('license_messages')->nullOnDelete();
            $table->string('type')->nullable(); //user, admin, special
            $table->text('message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_show')->default(false);
            $table->boolean('is_feature')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('license_message_details');
    }
};
