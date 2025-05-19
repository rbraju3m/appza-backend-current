<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appza_request_logs', function (Blueprint $table) {
            $table->longText('response_data')->change();
        });
    }

    public function down()
    {
        Schema::table('appza_request_logs', function (Blueprint $table) {
            $table->json('response_data')->change(); // or text(), depending on original type
        });
    }
};
