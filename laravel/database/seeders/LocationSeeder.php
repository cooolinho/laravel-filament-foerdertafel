<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                Location::name => 'Stadion',
                Location::address => 'Musterstraße 1, 12345 Musterstadt',
                Location::description => 'Hauptstadion in der Innenstadt mit moderner Ausstattung.',
            ],
        ];

        foreach ($locations as $locationData) {
            Location::create($locationData);
        }
    }
}
