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
        Schema::create('seat_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained()->onDelete('cascade');
            $table->string('class_name', 100); // e.g., "Window Seat", "2nd Class"
            $table->integer('seat_count'); // Number of seats in this class
            $table->decimal('price', 10, 2); // Price per ticket
            $table->text('description')->nullable(); // e.g., "AC with window view"
            $table->timestamps();

            $table->unique(['train_id', 'class_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seat_classes');
    }
};
