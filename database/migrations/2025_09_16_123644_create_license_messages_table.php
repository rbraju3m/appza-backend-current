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
        Schema::create('license_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('appza_fluent_informations')->nullOnDelete();
            $table->foreignId('addon_id')->nullable()->constrained('appza_product_addons')->nullOnDelete();
            $table->foreignId('license_logic_id')->constrained('license_logics')->cascadeOnDelete();
            $table->string('license_type'); // free_trial,premium
            $table->text('message_user')->nullable();
            $table->text('message_admin')->nullable();
            $table->text('message_special')->nullable();
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
        Schema::dropIfExists('license_messages');
    }
};
