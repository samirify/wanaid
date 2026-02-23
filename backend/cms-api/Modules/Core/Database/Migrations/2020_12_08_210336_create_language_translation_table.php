<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language_translation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('language')->cascadeOnDelete();
            $table->unsignedBigInteger('language_code_id');
            $table->foreign('language_code_id')->references('id')->on('language_code')->cascadeOnDelete();
            $table->longText('text')->nullable();
            $table->unique(['language_id', 'language_code_id']);
            $table->timestamps();
            createUserStampFields($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('language_translation');
    }
}
