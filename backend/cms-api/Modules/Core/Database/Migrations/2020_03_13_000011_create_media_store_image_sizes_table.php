<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateMediaStoreImageSizesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_store_image_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('media_store_id');
            $table->foreign('media_store_id')->references('id')->on('media_store')->cascadeOnDelete();
            $table->string('width', 32);
            $table->string('height', 32);
            $table->binary('content');
            $table->timestamps();
            createUserStampFields($table);
        });
        DB::statement("ALTER TABLE media_store_image_sizes MODIFY content MEDIUMBLOB");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_store_image_sizes');
    }
}
