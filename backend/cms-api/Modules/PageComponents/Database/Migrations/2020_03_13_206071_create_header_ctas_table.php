<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHeaderCtasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('header_ctas', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('label', 255);
            $table->mediumText('url');
            $table->string('url_type', 32);
            $table->string('style', 32);
            $table->integer('order')->default(1);

            $table->unsignedBigInteger('pages_id');
            $table->foreign('pages_id')->references('id')->on('pages');

            // $table->unsignedBigInteger('ga_actions_id')->nullable();
            // $table->foreign('ga_actions_id')->references('id')->on('ga_actions');

            // $table->string('ga_label', 255)->nullable();
            $table->boolean('active')->default(0);

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
        Schema::dropIfExists('header_ctas');
    }
}
