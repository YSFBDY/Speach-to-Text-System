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
        Schema::create('plans', function (Blueprint $table) {
            $table->id("plan_id");
            $table->string('plan_name');
            $table->integer('plan_price');
            $table->integer('plan_price_cents');
            $table->string('plan_description');
            $table->string('plan_period');
            $table->integer('plan_transcription_limit');
            $table->integer('plan_translation_limit');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
