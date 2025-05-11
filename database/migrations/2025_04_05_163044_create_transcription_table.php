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
        Schema::create('transcription', function (Blueprint $table) {
            $table->id("transcription_id");
            $table->string('audio_path');
            $table->text('text_content');
           

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('screen_id');
            $table->foreign("screen_id")->references('screen_id')->on('screens')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcription');
    }
};
