<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLanguageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('language', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('countries_id');
            $table->foreign('countries_id')->references('id')->on('countries');
            $table->string('name', 255);
            $table->unsignedBigInteger('locales_id')->unique();
            $table->foreign('locales_id')->references('id')->on('locales');
            $table->string('direction', 3);
            $table->timestamp('last_published')->nullable();
            $table->boolean('default')->default(0);
            $table->boolean('active')->default(0);
            $table->boolean('available')->default(0);
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
        Schema::dropIfExists('language');
    }
}
