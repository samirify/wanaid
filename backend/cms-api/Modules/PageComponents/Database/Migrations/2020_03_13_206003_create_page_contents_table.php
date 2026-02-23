<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_sections_id');
            $table->foreign('page_sections_id')->references('id')->on('page_sections');
            $table->string('name', 255)->nullable();
            $table->string('code', 255);
            $table->unique(['code', 'page_sections_id']);
            $table->longText('value')->nullable();
            $table->string('cta_target', 20)->nullable();
            $table->string('cta_label', 255)->nullable();
            $table->unsignedBigInteger('cta_page_sections_id')->nullable();
            $table->foreign('cta_page_sections_id')->references('id')->on('page_sections');
            $table->longText('cta_url')->nullable();
            $table->integer('order')->default(1);
            $table->boolean('active')->default(0);
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
        Schema::dropIfExists('page_contents');
    }
}
