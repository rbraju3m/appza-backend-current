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
        Schema::create('appza_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('method');
            $table->string('url',500);
            $table->json('headers')->nullable();
            $table->json('request_data')->nullable();
            $table->integer('response_status')->nullable();
            $table->json('response_data')->nullable();
            $table->string('ip_address');
            $table->string('user_agent',500)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->float('execution_time')->nullable();
            $table->timestamps();

            $table->index(['method', 'url']);
            $table->index('created_at');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appza_request_logs');
    }
};
