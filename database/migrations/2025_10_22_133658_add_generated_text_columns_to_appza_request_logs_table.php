<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add generated columns
        DB::statement("
            ALTER TABLE appza_request_logs
            ADD COLUMN request_text TEXT
                GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(request_data, '$'))) STORED,
            ADD COLUMN response_text TEXT
                GENERATED ALWAYS AS (JSON_UNQUOTE(JSON_EXTRACT(response_data, '$'))) STORED
        ");

        // Add FULLTEXT index
        DB::statement("
            ALTER TABLE appza_request_logs
            ADD FULLTEXT INDEX ft_request_response (request_text, response_text)
        ");
    }

    public function down(): void
    {
        // Drop the FULLTEXT index first
        DB::statement("ALTER TABLE appza_request_logs DROP INDEX ft_request_response");

        // Then drop the generated columns
        Schema::table('appza_request_logs', function (Blueprint $table) {
            $table->dropColumn(['request_text', 'response_text']);
        });
    }
};
