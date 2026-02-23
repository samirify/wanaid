<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_changes', function (Blueprint $table) {
            $table->id();
            
            $table->string('entity_name', 255);
            $table->string('entity_id', 255);
            $table->longText('message')->nullable();
            $table->unsignedBigInteger('status_from_id')->nullable();
            $table->foreign('status_from_id')->references('id')->on('application_code');
            $table->unsignedBigInteger('status_to_id')->nullable();
            $table->foreign('status_to_id')->references('id')->on('application_code');

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
        Schema::dropIfExists('status_changes');
    }
}
