<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSearchFieldCommonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('search_field_common', function (Blueprint $table) {
            $table->id();
            $table->string('fill_color',20);
            $table->string('page_title_text',255);
            $table->string('page_title_color',20);
            $table->double('page_title_font_size',10,2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('search_field_common');
    }
}
