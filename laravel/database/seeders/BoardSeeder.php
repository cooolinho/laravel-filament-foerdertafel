<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Location;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = Location::all();

        if ($locations->isEmpty()) {
            $this->command->warn('No locations found. Please run LocationSeeder first.');
            return;
        }

        // Berlin-Mitte: 2 Boards
        $berlin = $locations->where(Location::name, 'Stadion')->first();
        if ($berlin) {
            Board::create([
                Board::location_id => $berlin->id,
                Board::name => 'Fördertafel',
                Board::rows => 16,
                Board::columns => 17,
                Board::grid_gap => 1,
                Board::description => 'Jugendfördertafel für den Sportplatz in Berlin-Mitte.',
            ]);
        }
    }
}
