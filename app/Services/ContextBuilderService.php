<?php

// app/Services/ContextBuilderService.php — Step 1: Raw data → semantic labels

namespace App\Services;

class ContextBuilderService
{
    /**
     * Build human-readable context labels from raw sensor/weather/detection/disaster data.
     *
     * @param  array  $data  — matches the /api/iot/latest JSON structure
     * @return array
     */
    public function build(array $data): array
    {
        $latest    = $data['latest']    ?? [];
        $weather   = $data['weather']   ?? [];
        $detection = $data['latestDetection'] ?? null;
        $disaster  = $data['latestDisaster']  ?? null;

        return [
            'speed_status'     => $this->evaluateSpeed($latest),
            'light_condition'  => $this->evaluateLight($latest),
            'obstacle'         => $this->evaluateObstacle($detection),
            'flood_context'    => $this->evaluateFlood($disaster),
            'weather_context'  => $this->evaluateWeather($weather),
            'proximity_status' => $this->evaluateProximity($latest),
            'humidity_context' => $this->evaluateHumidity($latest),
            'rain_context'     => $this->evaluateRain($latest),
        ];
    }

    /**
     * Evaluate train speed against configured thresholds.
     */
    private function evaluateSpeed(array $latest): ?string
    {
        $speed = floatval($latest['speed'] ?? 0);

        if ($speed <= 0) {
            return 'Train stationary (0 km/h)';
        }

        $lowThreshold    = config('cape.speed.low');
        $mediumThreshold = config('cape.speed.medium');

        if ($speed < $lowThreshold) {
            return "Low speed — {$speed} km/h (safe braking distance)";
        }

        if ($speed <= $mediumThreshold) {
            return "Moderate speed — {$speed} km/h (standard operations)";
        }

        return "High speed — {$speed} km/h (extended braking distance required)";
    }

    /**
     * Evaluate ambient light condition from lux reading.
     */
    private function evaluateLight(array $latest): ?string
    {
        $lux = floatval($latest['lux'] ?? 0);

        $veryLowThreshold = config('cape.lux.very_low');
        $lowThreshold     = config('cape.lux.low');

        if ($lux < $veryLowThreshold) {
            return "Very low light ({$lux} lux, night conditions)";
        }

        if ($lux < $lowThreshold) {
            return "Low light ({$lux} lux, reduced visibility)";
        }

        return "Normal light ({$lux} lux, good visibility)";
    }

    /**
     * Evaluate obstacle from latest object detection.
     */
    private function evaluateObstacle(?array $detection): ?string
    {
        if (! $detection || empty($detection['type']) || strtolower($detection['type']) === 'none') {
            return null;
        }

        $type     = $detection['type'];
        $distance = floatval($detection['distance'] ?? 0);
        $size     = $detection['size'] ?? 'unknown';

        $dangerThreshold = config('cape.proximity_danger_threshold');

        if ($distance <= $dangerThreshold) {
            return "{$type} detected {$distance}m from track (size {$size}) — WITHIN DANGER ZONE";
        }

        return "{$type} detected {$distance}m from track (size {$size})";
    }

    /**
     * Evaluate flood risk from latest disaster report.
     */
    private function evaluateFlood(?array $disaster): ?string
    {
        if (! $disaster || empty($disaster['risk_level'])) {
            return null;
        }

        $riskLevel = $disaster['risk_level'];
        $city      = $disaster['city'] ?? 'Unknown area';

        return "{$riskLevel} flood risk — {$city}";
    }

    /**
     * Evaluate weather conditions from API data.
     */
    private function evaluateWeather(array $weather): ?string
    {
        if (empty($weather)) {
            return 'Weather data unavailable';
        }

        $description = $weather['weather'][0]['description'] ?? 'unknown';
        $windSpeed   = $weather['wind']['speed'] ?? 0;
        $visibility  = ($weather['visibility'] ?? 10000) / 1000;

        $parts = [];
        $parts[] = ucfirst($description);
        $parts[] = "wind {$windSpeed} m/s";

        if ($visibility < 5) {
            $parts[] = "LOW visibility {$visibility}km";
        } else {
            $parts[] = "visibility {$visibility}km";
        }

        return implode(', ', $parts);
    }

    /**
     * Evaluate proximity (frontal and lateral distances).
     */
    private function evaluateProximity(array $latest): ?string
    {
        $front = floatval($latest['sf_front_distance'] ?? 0);
        $side  = floatval($latest['sf_side_distance'] ?? 0);

        $dangerThreshold = config('cape.proximity_danger_threshold');

        $alerts = [];

        if ($front > 0 && $front < $dangerThreshold) {
            $alerts[] = "Frontal object at {$front}m";
        }

        if ($side > 0 && $side < $dangerThreshold) {
            $alerts[] = "Lateral object at {$side}m";
        }

        if (count($alerts) > 0) {
            return implode('; ', $alerts) . ' — PROXIMITY ALERT';
        }

        return "Clear corridor (front: {$front}m, side: {$side}m)";
    }

    /**
     * Evaluate humidity level.
     */
    private function evaluateHumidity(array $latest): ?string
    {
        $humidity = floatval($latest['humidity'] ?? 0);

        $normalThreshold = config('cape.humidity.normal');

        if ($humidity >= $normalThreshold) {
            return "High humidity ({$humidity}%) — potential fog/dew formation";
        }

        return "Normal humidity ({$humidity}%)";
    }

    /**
     * Evaluate rain conditions.
     */
    private function evaluateRain(array $latest): ?string
    {
        $rain = floatval($latest['rain_percentage'] ?? 0);

        $noneThreshold  = config('cape.rain.none');
        $lightThreshold = config('cape.rain.light');

        if ($rain <= $noneThreshold) {
            return 'No rain detected';
        }

        if ($rain < $lightThreshold) {
            return "Light rain ({$rain}%) — minor track moisture";
        }

        return "Heavy rain ({$rain}%) — reduced traction, flooding risk";
    }
}
