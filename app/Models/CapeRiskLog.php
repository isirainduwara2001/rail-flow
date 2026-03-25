<?php

// app/Models/CapeRiskLog.php — Eloquent model for CAPE risk assessment logs

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapeRiskLog extends Model
{
    protected $table = 'cape_risk_logs';

    protected $fillable = [
        'iot_data_id',
        'context_json',
        'prompt_text',
        'risk_level',
        'reasons_json',
        'actions_json',
        'prediction',
        'source',
        'llm_model',
        'response_time_ms',
        'prompt_version',
    ];

    protected $casts = [
        'context_json' => 'array',
        'reasons_json' => 'array',
        'actions_json' => 'array',
    ];

    /**
     * Relationship: belongs to IoT data record.
     */
    public function iotData(): BelongsTo
    {
        return $this->belongsTo(IotData::class, 'iot_data_id');
    }

    /**
     * Get the most recent CAPE risk assessment.
     */
    public static function latestRisk(): ?self
    {
        return static::latest()->first();
    }
}
