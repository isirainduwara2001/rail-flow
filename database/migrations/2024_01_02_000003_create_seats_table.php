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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_id')->constrained('trains')->onDelete('cascade');
            $table->string('seat_number');
            $table->enum('class', ['economy', 'business', 'first'])->default('economy');
            $table->json('facilities')->nullable(); // {"ac": true, "plug": true, "meal": false}
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available');
            $table->timestamps();

            $table->unique(['train_id', 'seat_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
