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
        Schema::create('payments', function (Blueprint $table) {
            $table->id("payment_id");
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone');
            $table->integer('payment_amount');
            $table->integer('payment_amount_cent');
            $table->string('payment_currency');
            $table->enum('payment_status', ['pending', 'approved', 'declined'])->default('pending');
            $table->string('order_id')->unique();
            $table->string('merchant_order_id');

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unsignedBigInteger('plan_id');
            $table->foreign("plan_id")->references('plan_id')->on('plans')->cascadeOnDelete()->cascadeOnUpdate();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
