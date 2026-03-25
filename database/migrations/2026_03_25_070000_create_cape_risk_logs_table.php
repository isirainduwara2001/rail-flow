<?php

// database/migrations/2026_03_25_070000_create_cape_risk_logs_table.php

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
        Schema::create('cape_risk_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('iot_data_id')->nullable();
            $table->json('context_json');
            $table->text('prompt_text');
            $table->enum('risk_level', ['Low', 'Medium', 'High']);
            $table->json('reasons_json');
            $table->json('actions_json');
            $table->text('prediction')->nullable();
            $table->string('source')->default('llm');
            $table->string('llm_model')->nullable();
            $table->integer('response_time_ms')->nullable();
            $table->string('prompt_version')->default('v1');
            $table->timestamps();

            $table->foreign('iot_data_id')
                  ->references('id')
                  ->on('iot_data')
                  ->onDelete('set null');

            $table->index('risk_level');
            $table->index('source');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cape_risk_logs');
    }
};
