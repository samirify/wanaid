<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('code', 32)->unique();
            $table->string('entity_name', 255)->nullable();
            $table->string('entity_id', 255)->nullable();
            $table->float('amount');
            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('application_code');
            $table->unsignedBigInteger('status_id');
            $table->foreign('status_id')->references('id')->on('application_code');
            $table->timestamp('last_modified_at', 0)->nullable();

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
        Schema::dropIfExists('payments');
    }
}
