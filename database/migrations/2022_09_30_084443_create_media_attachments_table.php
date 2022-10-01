<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TahirRasheed\MediaLibrary\Models\Media;

class CreateMediaAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Media::class)->constrained()->cascadeOnDelete();
            $table->morphs('imageable');
            $table->string('type', 50)->default('image')->index();
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
        Schema::dropIfExists('media_attachments');
    }
};
