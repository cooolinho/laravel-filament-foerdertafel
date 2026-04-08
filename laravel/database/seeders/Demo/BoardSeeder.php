<?php

namespace Database\Seeders\Demo;

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

        // Fussball Stadion: 1 Board with 17 rows and 16 columns
        $stadion = $locations->where(Location::name, 'Fussball Stadion')->first();
        if ($stadion) {
            Board::create([
                Board::location_id => $stadion->id,
                Board::name => 'Fördertafel Fussballstadion',
                Board::rows => 17,
                Board::columns => 18,
                Board::grid_gap => 1,
                Board::description => 'Fördertafel für das Fussballstadion mit vordefinierten Feldern für Ecken, Tore und Mittelkreis.',
            ]);
        }
    }
}
