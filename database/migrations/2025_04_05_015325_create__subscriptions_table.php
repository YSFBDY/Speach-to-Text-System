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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id("subscription_id");
            $table->enum('subscription_status', ['active', 'inactive']);
            $table->integer('remain_transcription_limit');
            $table->integer('remain_translation_limit');

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('plan_id');
            $table->foreign("plan_id")->references('plan_id')->on('plans')->cascadeOnDelete()->cascadeOnUpdate();

            $table->date('start_date');
            $table->date('end_date');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
