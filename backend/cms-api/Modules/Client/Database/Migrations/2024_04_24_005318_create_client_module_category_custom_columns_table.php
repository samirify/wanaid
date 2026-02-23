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
        Schema::create('client_module_category_custom_columns', function (Blueprint $table) {
            $table->id();

            $table->foreign('client_module_categories_id', 'client_module_categories_idx')->references('id')->on('client_module_categories');
            $table->unsignedBigInteger('client_module_categories_id');

            $table->string('name', 255);
            $table->string('type', 255);
            $table->string('foreign_table', 255)->nullable();
            $table->string('foreign_column', 255)->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('unique')->default(false);
            $table->longText('options')->nullable();

            $table->timestamps();
            createUserStampFields($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_module_category_custom_columns');
    }
};
