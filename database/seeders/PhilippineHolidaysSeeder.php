<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhilippineHolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;
        
        $holidays = [
            // REGULAR HOLIDAYS
            [
                'name' => 'New Year\'s Day',
                'month' => 1,
                'day' => 1,
                'type' => 'Regular Holiday',
                'description' => 'First day of the year',
                'color' => 'danger',
            ],
            [
                'name' => 'Maundy Thursday',
                'month' => 3,
                'day' => 28, // Movable - 2025 date, update annually
                'type' => 'Regular Holiday',
                'description' => 'Commemorates the Last Supper of Jesus Christ (movable date)',
                'color' => 'purple',
            ],
            [
                'name' => 'Good Friday',
                'month' => 3,
                'day' => 29, // Movable - 2025 date, update annually
                'type' => 'Regular Holiday',
                'description' => 'Commemorates the crucifixion of Jesus Christ (movable date)',
                'color' => 'purple',
            ],
            [
                'name' => 'Araw ng Kagitingan (Day of Valor)',
                'month' => 4,
                'day' => 9,
                'type' => 'Regular Holiday',
                'description' => 'Commemorates the fall of Bataan during World War II',
                'color' => 'info',
            ],
            [
                'name' => 'Labor Day',
                'month' => 5,
                'day' => 1,
                'type' => 'Regular Holiday',
                'description' => 'Celebrates the economic and social achievements of workers',
                'color' => 'warning',
            ],
            [
                'name' => 'Independence Day',
                'month' => 6,
                'day' => 12,
                'type' => 'Regular Holiday',
                'description' => 'Celebrates Philippine independence from Spain',
                'color' => 'primary',
            ],
            [
                'name' => 'Eid al-Adha (Feast of Sacrifice)',
                'month' => 6,
                'day' => 7, // Movable - 2025 date, update annually
                'type' => 'Regular Holiday',
                'description' => 'Islamic holiday commemorating Abraham\'s willingness to sacrifice (movable date)',
                'color' => 'success',
            ],
            [
                'name' => 'Ninoy Aquino Day',
                'month' => 8,
                'day' => 21,
                'type' => 'Regular Holiday',
                'description' => 'Commemorates the assassination of Senator Benigno Aquino Jr.',
                'color' => 'warning',
            ],
            [
                'name' => 'National Heroes Day',
                'month' => 8,
                'day' => 25, // Last Monday of August
                'type' => 'Regular Holiday',
                'description' => 'Honors all Filipino heroes who fought for independence',
                'color' => 'info',
            ],
            [
                'name' => 'All Saints\' Day',
                'month' => 11,
                'day' => 1,
                'type' => 'Regular Holiday',
                'description' => 'Christian holiday honoring all saints',
                'color' => 'purple',
            ],
            [
                'name' => 'Bonifacio Day',
                'month' => 11,
                'day' => 30,
                'type' => 'Regular Holiday',
                'description' => 'Celebrates the birth of Andres Bonifacio, a Filipino revolutionary',
                'color' => 'danger',
            ],
            [
                'name' => 'Christmas Day',
                'month' => 12,
                'day' => 25,
                'type' => 'Regular Holiday',
                'description' => 'Celebrates the birth of Jesus Christ',
                'color' => 'danger',
            ],
            [
                'name' => 'Rizal Day',
                'month' => 12,
                'day' => 30,
                'type' => 'Regular Holiday',
                'description' => 'Commemorates the execution of Dr. Jose Rizal',
                'color' => 'primary',
            ],
            
            // SPECIAL NON-WORKING HOLIDAYS
            [
                'name' => 'Chinese New Year',
                'month' => 1,
                'day' => 29, // Movable - 2025 date, update annually
                'type' => 'Special Non-Working Holiday',
                'description' => 'Celebrates the beginning of a new year on the Chinese calendar (movable date)',
                'color' => 'danger',
            ],
            [
                'name' => 'EDSA People Power Revolution Anniversary',
                'month' => 2,
                'day' => 25,
                'type' => 'Special Non-Working Holiday',
                'description' => 'Commemorates the peaceful revolution that overthrew Ferdinand Marcos',
                'color' => 'warning',
            ],
            [
                'name' => 'Black Saturday',
                'month' => 3,
                'day' => 30, // Movable - 2025 date, update annually
                'type' => 'Special Non-Working Holiday',
                'description' => 'Day between Good Friday and Easter Sunday (movable date)',
                'color' => 'info',
            ],
            [
                'name' => 'Eid al-Fitr (End of Ramadan)',
                'month' => 3,
                'day' => 31, // Movable - 2025 date, update annually
                'type' => 'Special Non-Working Holiday',
                'description' => 'Islamic holiday marking the end of Ramadan (movable date)',
                'color' => 'success',
            ],
            [
                'name' => 'All Souls\' Day',
                'month' => 11,
                'day' => 2,
                'type' => 'Special Non-Working Holiday',
                'description' => 'Day of prayer for the souls of the dead',
                'color' => 'purple',
            ],
            [
                'name' => 'Feast of the Immaculate Conception of Mary',
                'month' => 12,
                'day' => 8,
                'type' => 'Special Non-Working Holiday',
                'description' => 'Catholic feast day celebrating the conception of the Virgin Mary',
                'color' => 'primary',
            ],
            [
                'name' => 'Christmas Eve',
                'month' => 12,
                'day' => 24,
                'type' => 'Special Non-Working Holiday',
                'description' => 'Day before Christmas',
                'color' => 'danger',
            ],
            [
                'name' => 'Last Day of the Year',
                'month' => 12,
                'day' => 31,
                'type' => 'Special Non-Working Holiday',
                'description' => 'New Year\'s Eve',
                'color' => 'danger',
            ],
        ];
        
        foreach ($holidays as $holiday) {
            $startDate = Carbon::create($currentYear, $holiday['month'], $holiday['day']);
            
            Holiday::updateOrCreate(
                [
                    'name' => $holiday['name'],
                ],
                [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $startDate->format('Y-m-d'),
                    'type' => $holiday['type'],
                    'description' => $holiday['description'],
                    'color' => $holiday['color'],
                    'is_annual' => true,
                ]
            );
        }
        
        $this->command->info('Philippine holidays have been seeded successfully!');
        $this->command->info('Total: ' . count($holidays) . ' holidays added');
        $this->command->info('Note: Some holidays have movable dates (Maundy Thursday, Good Friday, etc.) and should be updated annually.');
    }
}
