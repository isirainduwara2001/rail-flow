<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations
     */
    public function up(): void
    {
        Schema::create('iot_data', function (Blueprint $table) {
            $table->id();
            $table->decimal('sf_front_distance', 8, 2)->nullable();
            $table->decimal('sf_side_distance', 8, 2)->nullable();
            $table->decimal('t_front_distance', 8, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('lux', 8, 2)->nullable();
            $table->decimal('rain_percentage', 5, 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_data');
    }
};
