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
        Schema::table('appfiy_scope', function (Blueprint $table) {
            $table->unsignedBigInteger('page_id')->unsigned()->nullable();
            $table->foreign('page_id')->references('id')->on('appfiy_page');
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
