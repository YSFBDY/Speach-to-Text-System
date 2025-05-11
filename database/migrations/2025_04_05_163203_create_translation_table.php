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
        Schema::create('translation', function (Blueprint $table) {
            $table->id("translation_id");
            $table->string('target_language');
            $table->string('target_language_code');
            $table->text('translated_text');

            $table->unsignedBigInteger('transcription_id');
            $table->foreign("transcription_id")->references('transcription_id')->on('transcription')->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation');
    }
};
