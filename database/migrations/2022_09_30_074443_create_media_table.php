<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('imageable');
            $table->string('type', 50)->default('image')->index();
            $table->string('file_name', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('size');
            $table->string('disk', 50);
            $table->string('collection_name', 50)->nullable();
            $table->json('conversions')->nullable();
            $table->unsignedInteger('sort_order')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
