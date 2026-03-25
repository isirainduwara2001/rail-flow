<?php

// app/Services/EventMemoryService.php — Rolling event memory for trend detection

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class EventMemoryService
{
    /**
     * Push a sensor snapshot into the rolling event memory cache.
     *
     * Keeps only the last 10 entries. TTL from config.
     */
    public function remember(array $iotData): void
    {
        $cacheKey = config('cape.cache_key');
        $ttl      = config('cape.cache_ttl');

        $entry = [
            'speed'            => floatval($iotData['speed'] ?? 0),
            'lux'              => floatval($iotData['lux'] ?? 0),
            'rain_percentage'  => floatval($iotData['rain_percentage'] ?? 0),
            'humidity'         => floatval($iotData['humidity'] ?? 0),
            'obstacle'         => $iotData['obstacle'] ?? null,
            'timestamp'        => now()->toIso8601String(),
        ];

        $history = Cache::get($cacheKey, []);
        $history[] = $entry;

        // Keep only the last 10 entries
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        Cache::put($cacheKey, $history, $ttl);
    }

    /**
     * Analyse the cached event history and return a trend summary.
     *
     * @return string  Plain English summary of detected trends.
     */
    public function getSummary(): string
    {
        $cacheKey = config('cape.cache_key');
        $history  = Cache::get($cacheKey, []);

        if (count($history) < 2) {
            return 'No significant trends detected (insufficient history).';
        }

        $trends = [];

        // Analyse the last 3 entries (or fewer if history is short)
        $recent = array_slice($history, -3);

        // --- Rain trend ---
        if (count($recent) >= 3) {
            $rainValues = array_column($recent, 'rain_percentage');
            if ($rainValues[2] > $rainValues[1] && $rainValues[1] > $rainValues[0]) {
                $trends[] = 'Rain increasing trend detected';
            }
        }

        // --- Obstacle recurrence ---
        $obstacleCount = 0;
        foreach ($recent as $entry) {
            if (! empty($entry['obstacle'])) {
                $obstacleCount++;
            }
        }
        if ($obstacleCount >= 2) {
            $trends[] = 'Repeated obstacle detection in recent readings';
        }

        // --- Light level trend ---
        if (count($recent) >= 3) {
            $luxValues = array_column($recent, 'lux');
            if ($luxValues[2] < $luxValues[1] && $luxValues[1] < $luxValues[0]) {
                $trends[] = 'Light level dropping (approaching nightfall or tunnel)';
            }
        }

        // --- Humidity trend ---
        if (count($recent) >= 3) {
            $humidityValues = array_column($recent, 'humidity');
            if ($humidityValues[2] > $humidityValues[1] && $humidityValues[1] > $humidityValues[0]) {
                $trends[] = 'Humidity increasing trend';
            }
        }

        // --- Speed changes ---
        if (count($recent) >= 2) {
            $lastSpeed = end($recent)['speed'];
            $prevSpeed = $recent[count($recent) - 2]['speed'];
            $speedDiff = $lastSpeed - $prevSpeed;

            if ($speedDiff > 10) {
                $trends[] = 'Speed increasing rapidly';
            } elseif ($speedDiff < -10) {
                $trends[] = 'Speed decreasing rapidly (possible emergency braking)';
            }
        }

        if (empty($trends)) {
            return 'No significant trends detected.';
        }

        return implode('. ', $trends) . '.';
    }
}
