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
        Schema::create('system_messages', function (Blueprint $table) {
            $table->id();

            $table->string('code', 32)->unique();
            $table->longText('message');
            $table->string('entity_name', 255)->nullable();
            $table->string('entity_id', 255)->nullable();
            $table->unsignedBigInteger('message_type_id');
            $table->foreign('message_type_id')->references('id')->on('application_code');
            $table->unsignedBigInteger('severity_id');
            $table->foreign('severity_id')->references('id')->on('application_code');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('application_code');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');

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
        Schema::dropIfExists('system_messages');
    }
};
