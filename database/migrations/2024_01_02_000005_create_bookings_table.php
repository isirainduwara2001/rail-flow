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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->foreignId('seat_id')->constrained('seats')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['confirmed', 'pending', 'cancelled', 'completed'])->default('confirmed');
            $table->string('booking_reference')->unique();
            $table->dateTime('booked_at');
            $table->timestamps();

            // Prevent double-booking: Unique constraint on schedule + seat combo
            $table->unique(['schedule_id', 'seat_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
