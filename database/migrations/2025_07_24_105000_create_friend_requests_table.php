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
        Schema::create('friend_requests', function (Blueprint $table) {
            $table->id();

            // foreign key
            $table->unsignedBigInteger('sender_id')->index();   // who sent the request
            $table->unsignedBigInteger('receiver_id')->index(); // who received it

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');

            // prevent duplicate friend requests
            $table->unique(['sender_id', 'receiver_id']);

            // connetion
            $table->foreign('sender_id')
                ->references('id')->on('users')
                ->cascadeOnDelete()->restrictOnUpdate();

            $table->foreign('receiver_id')
                ->references('id')->on('users')
                ->cascadeOnDelete()->restrictOnUpdate();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friend_requests');
    }
};
