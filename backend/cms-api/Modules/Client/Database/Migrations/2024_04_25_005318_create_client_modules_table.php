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
        Schema::create('client_modules', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('code', 32)->unique();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('client_module_categories');
            $table->boolean('active')->default(0);
            $table->unsignedBigInteger('pages_id')->nullable();
            $table->foreign('pages_id')->references('id')->on('pages');
            $table->unsignedBigInteger('records_template_page_id')->nullable();
            $table->foreign('records_template_page_id')->references('id')->on('pages');

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
        Schema::dropIfExists('client_modules');
    }
};
