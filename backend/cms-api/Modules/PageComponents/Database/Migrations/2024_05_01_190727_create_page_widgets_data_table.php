<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_widgets_data', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('page_contents_id')->unique();
            $table->foreign('page_contents_id')->references('id')->on('page_contents')->cascadeOnDelete();

            $table->unsignedBigInteger('page_widgets_id');
            $table->foreign('page_widgets_id')->references('id')->on('page_widgets')->cascadeOnDelete();

            $table->unsignedBigInteger('module_id')->nullable();
            $table->foreign('module_id')->references('id')->on('client_modules')->cascadeOnDelete();

            $table->longText('data');

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
        Schema::dropIfExists('page_widgets_data');
    }
};
