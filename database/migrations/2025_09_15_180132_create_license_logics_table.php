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
            $table->string('type',20);
            $table->enum('direction', ['before', 'after', 'equal']);
            $table->unsignedInteger('from_days');
            $table->unsignedInteger('to_days');
            $table->timestamps();

            // Composite unique constraint
            $table->unique(['type', 'direction', 'from_days', 'to_days'], 'unique_logic_rule');
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
