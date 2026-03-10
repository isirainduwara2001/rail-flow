<?php

namespace App\Enums;

enum TrainType: string
{
    case EXPRESS = 'Express';
    case RAJDHANI = 'Rajdhani';
    case SHATABDI = 'Shatabdi';
    case GARIB_RATH = 'Garib-Rath';
    case LOCAL = 'Local';

    /**
     * Get all train types as an array for select options.
     */
    public static function toArray(): array
    {
        return array_map(fn(TrainType $type) => $type->value, TrainType::cases());
    }

    /**
     * Get all train types with their labels.
     */
    public static function withLabels(): array
    {
        return [
            self::EXPRESS->value => 'Express',
            self::RAJDHANI->value => 'Rajdhani',
            self::SHATABDI->value => 'Shatabdi',
            self::GARIB_RATH->value => 'Garib-Rath',
            self::LOCAL->value => 'Local',
        ];
    }

    /**
     * Get the icon for this train type.
     */
    public function icon(): string
    {
        return match($this) {
            self::EXPRESS => 'speed',
            self::RAJDHANI => 'train',
            self::SHATABDI => 'flight',
            self::GARIB_RATH => 'people',
            self::LOCAL => 'directions_bus',
        };
    }

    /**
     * Get the color badge for this train type.
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::EXPRESS => 'danger',      // Red
            self::RAJDHANI => 'primary',    // Blue
            self::SHATABDI => 'success',    // Green
            self::GARIB_RATH => 'warning',  // Yellow
            self::LOCAL => 'info',          // Cyan
        };
    }
}
