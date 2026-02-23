<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team', function (Blueprint $table) {
            $table->id();

            $table->string('unique_title', 255)->unique();

            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts');

            $table->unsignedBigInteger('departments_id')->nullable();
            $table->foreign('departments_id')->references('id')->on('departments');

            $table->string('position', 255);
            $table->mediumText('short_description');
            $table->longText('description')->nullable();
            $table->boolean('show_on_web')->default(1);
            $table->integer('order')->default(1);

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
        Schema::dropIfExists('team');
    }
}
