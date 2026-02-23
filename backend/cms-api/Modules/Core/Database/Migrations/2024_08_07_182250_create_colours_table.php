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
        Schema::create('colours', function (Blueprint $table) {
            $table->id();

            $table->string('name', 128)->nullable();

            $table->string('red', 5);
            $table->string('green', 5);
            $table->string('blue', 5);

            $table->string('hex', 16);

            $table->unique(['red', 'green', 'blue', 'hex']);

            $table->timestamps();
            createUserStampFields($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colours');
    }
};
