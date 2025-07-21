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
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venue_id')->nullable()->constrained()->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->date('start_date');
            $table->time('start_time');
            $table->date('end_date');
            $table->time('end_time');

            $table->string('time_zone')->default('UTC');
            $table->boolean('all_day_event')->default(false);

            $table->string('banner');
            $table->json('tags')->nullable();

            $table->enum('status', ['going_live', 'pending', 'postponed', 'cancelled', 'completed'])->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
