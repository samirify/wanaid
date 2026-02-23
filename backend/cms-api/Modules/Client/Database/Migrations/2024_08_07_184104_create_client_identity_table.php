<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_identity', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);

            $table->string('business_name', 255);
            $table->string('business_slogan', 255)->nullable();
            $table->string('business_short_description', 255)->nullable();

            $table->boolean('active')->default(0);
            $table->boolean('default')->default(0);

            $table->timestamps();
            createUserStampFields($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_identity');
    }
};
