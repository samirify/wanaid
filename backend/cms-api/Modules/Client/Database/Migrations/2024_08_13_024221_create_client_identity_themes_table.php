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
        Schema::create('client_identity_themes', function (Blueprint $table) {
            $table->id();

            $table->foreign('client_identity_id')->references('id')->on('client_identity');
            $table->unsignedBigInteger('client_identity_id');

            $table->string('code', 255)->unique();
            $table->string('name', 255);

            $table->foreign('primary_colour_id')->references('id')->on('colours');
            $table->unsignedBigInteger('primary_colour_id');
            $table->foreign('secondary_colour_id')->references('id')->on('colours');
            $table->unsignedBigInteger('secondary_colour_id');

            $table->foreign('logo_coloured_light_id')->references('id')->on('media_store');
            $table->unsignedBigInteger('logo_coloured_light_id');

            $table->foreign('logo_coloured_dark_id')->references('id')->on('media_store');
            $table->unsignedBigInteger('logo_coloured_dark_id')->nullable();

            $table->foreign('logo_contrast_light_id')->references('id')->on('media_store');
            $table->unsignedBigInteger('logo_contrast_light_id')->nullable();

            $table->foreign('logo_contrast_dark_id')->references('id')->on('media_store');
            $table->unsignedBigInteger('logo_contrast_dark_id')->nullable();

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
        Schema::dropIfExists('client_identity_themes');
    }
};
