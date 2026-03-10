<?php

namespace Database\Seeders;

use App\Models\Train;
use App\Models\SeatClass;
use App\Models\Seat;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TrainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create trains
        $trains = [
            [
                'name' => 'Express 101',
                'train_number' => 'TR001',
                'total_seats' => 48,
                'description' => 'Premium express train with excellent comfort'
            ],
            [
                'name' => 'Rapid 202',
                'train_number' => 'TR002',
                'total_seats' => 60,
                'description' => 'Fast comfortable train for daily commute'
            ],
            [
                'name' => 'Local 303',
                'train_number' => 'TR003',
                'total_seats' => 80,
                'description' => 'Local train with multiple stops'
            ],
            [
                'name' => 'Luxury 404',
                'train_number' => 'TR004',
                'total_seats' => 32,
                'description' => 'Premium luxury train with first-class facilities'
            ],
        ];

        foreach ($trains as $trainData) {
            $train = Train::create($trainData);

            // Create seat classes for each train
            $seatClasses = [
                ['class_name' => 'Economy', 'seat_count' => 20, 'price' => 500, 'description' => 'Standard seating'],
                ['class_name' => 'Business', 'seat_count' => 16, 'price' => 1000, 'description' => 'Extra legroom, meals included'],
                ['class_name' => 'First', 'seat_count' => 8, 'price' => 1500, 'description' => 'Premium seats with premium service'],
            ];

            foreach ($seatClasses as $classData) {
                SeatClass::create([
                    'train_id' => $train->id,
                    'class_name' => $classData['class_name'],
                    'seat_count' => $classData['seat_count'],
                    'price' => $classData['price'],
                    'description' => $classData['description'],
                ]);
            }

            // Create seats for train (4 seats per row, distributed across classes)
            $seatNumber = 1;
            $economySeats = 20;
            $businessSeats = 16;
            $firstSeats = 8;

            // Economy seats (rows 1-5)
            for ($i = 0; $i < $economySeats; $i++) {
                Seat::create([
                    'train_id' => $train->id,
                    'seat_number' => (string)$seatNumber++,
                    'class' => 'economy',
                    'facilities' => json_encode(['window' => $i % 4 < 2, 'aisle' => $i % 4 >= 2]),
                    'status' => 'available',
                ]);
            }

            // Business seats (rows 6-9)
            for ($i = 0; $i < $businessSeats; $i++) {
                Seat::create([
                    'train_id' => $train->id,
                    'seat_number' => (string)$seatNumber++,
                    'class' => 'business',
                    'facilities' => json_encode(['window' => $i % 4 < 2, 'aisle' => $i % 4 >= 2]),
                    'status' => 'available',
                ]);
            }

            // First Class seats (rows 10-12)
            for ($i = 0; $i < $firstSeats; $i++) {
                Seat::create([
                    'train_id' => $train->id,
                    'seat_number' => (string)$seatNumber++,
                    'class' => 'first',
                    'facilities' => json_encode(['window' => $i % 4 < 2, 'aisle' => $i % 4 >= 2]),
                    'status' => 'available',
                ]);
            }
        }

        // Create schedules for each train
        $routes = [
            ['from' => 'Colombo', 'to' => 'Kandy'],
            ['from' => 'Kandy', 'to' => 'Nuwara Eliya'],
            ['from' => 'Colombo', 'to' => 'Galle'],
            ['from' => 'Galle', 'to' => 'Matara'],
        ];

        $trains = Train::all();

        foreach ($trains as $train) {
            foreach ($routes as $route) {
                for ($day = 0; $day < 7; $day++) {
                    Schedule::create([
                        'train_id' => $train->id,
                        'from' => $route['from'],
                        'to' => $route['to'],
                        'departure' => Carbon::now()->addDays($day)->setTime(8 + ($train->id - 1) * 2, 0),
                        'arrival' => Carbon::now()->addDays($day)->setTime(12 + ($train->id - 1) * 2, 0),
                        'available_seats' => $train->total_seats,
                    ]);
                }
            }
        }
    }
}
