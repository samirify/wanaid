<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReleaseIssuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('release_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('release_id')->nullable();
            $table->foreign('release_id')->references('id')->on('release');
            $table->string('type');
            $table->string('title');
            $table->string('description')->nullable();
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
        Schema::dropIfExists('release_issues');
    }
}
