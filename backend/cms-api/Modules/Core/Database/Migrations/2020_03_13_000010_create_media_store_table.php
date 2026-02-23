<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateMediaStoreTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_store', function (Blueprint $table) {
            $table->id();
            $table->string('mime_type');
            $table->string('file_name', 255);
            $table->string('file_size', 32);
            $table->string('file_extension', 32);
            $table->string('entity_name', 255);
            $table->string('entity_id', 255);
            $table->binary('content');
            $table->string('width', 32)->nullable();
            $table->string('height', 32)->nullable();
            $table->string('dpi', 32)->nullable();
            $table->string('temp_token', 36)->nullable(); // UUID V4
            $table->boolean('to_delete')->default(0);
            $table->string('batch_id', 32)->nullable();
            $table->timestamps();
            createUserStampFields($table);
        });

        DB::statement("ALTER TABLE media_store MODIFY content MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_store');
    }
}
