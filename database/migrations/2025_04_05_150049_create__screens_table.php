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
        Schema::create('screens', function (Blueprint $table) {
            $table->id("screen_id");
            $table->string('screen_name');
            $table->integer('feedback')->nullable();

            $table->unsignedBigInteger('user_id');
            $table->foreign("user_id")->references('user_id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
                                                                             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('screens');
    }
};
