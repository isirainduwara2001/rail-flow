<?php

namespace App\Helpers;

class StationHelper
{
    /**
     * List of stations from Colombo to Kandy with coordinates.
     */
    public static function getStations()
    {
        return [
            ['name' => 'Colombo Fort', 'latitude' => 6.9344, 'longitude' => 79.8501],
            ['name' => 'Maradana', 'latitude' => 6.9272, 'longitude' => 79.8653],
            ['name' => 'Ragama', 'latitude' => 7.0317, 'longitude' => 79.9234],
            ['name' => 'Gampaha', 'latitude' => 7.0905, 'longitude' => 79.9950],
            ['name' => 'Veyangoda', 'latitude' => 7.1569, 'longitude' => 80.0592],
            ['name' => 'Polgahawela', 'latitude' => 7.3323, 'longitude' => 80.2974],
            ['name' => 'Rambukkana', 'latitude' => 7.3315, 'longitude' => 80.3951],
            ['name' => 'Peradeniya Junction', 'latitude' => 7.2714, 'longitude' => 80.5956],
            ['name' => 'Kandy', 'latitude' => 7.2896, 'longitude' => 80.6333],
        ];
    }

    /**
     * Calculate distance between two points using Haversine formula (in KM).
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radius of the Earth in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Find the nearest station within a limit (default 10km).
     */
    public static function getNearestStation($lat, $lng, $limitKm = 10)
    {
        if (! $lat || ! $lng) {
            return null;
        }

        $stations = self::getStations();
        $nearestStation = null;
        $minDistance = $limitKm;

        foreach ($stations as $station) {
            $distance = self::calculateDistance($lat, $lng, $station['latitude'], $station['longitude']);
            if ($distance <= $minDistance) {
                $minDistance = $distance;
                $nearestStation = $station;
            }
        }

        if ($nearestStation) {
            $indexStation = array_search($nearestStation, $stations);

            if (isset($stations[$indexStation + 1])) {
                return $stations[$indexStation + 1];
            }
        }

        return $nearestStation;
    }
}
