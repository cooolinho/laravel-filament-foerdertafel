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
        $berlin = $locations->where(Location::name, 'Sportplatz Berlin-Mitte')->first();
        if ($berlin) {
            Board::create([
                Board::location_id => $berlin->id,
                Board::name => 'Hauptfeld Nord',
                Board::rows => 12,
                Board::columns => 8,
                Board::description => 'Hauptspielfeld mit Premium-Positionen wie Tor und Strafraum.',
            ]);

            Board::create([
                Board::location_id => $berlin->id,
                Board::name => 'Trainingsfeld Süd',
                Board::rows => 8,
                Board::columns => 6,
                Board::description => 'Kleineres Trainingsfeld für günstigere Werbeflächen.',
            ]);
        }

        // Hamburg: 1 Board
        $hamburg = $locations->where(Location::name, 'Sportarena Hamburg')->first();
        if ($hamburg) {
            Board::create([
                Board::location_id => $hamburg->id,
                Board::name => 'Arena Hauptfeld',
                Board::rows => 10,
                Board::columns => 10,
                Board::description => 'Großes quadratisches Feld mit symmetrischer Aufteilung.',
            ]);
        }

        // München: 1 Board
        $munich = $locations->where(Location::name, 'FC München Stadion')->first();
        if ($munich) {
            Board::create([
                Board::location_id => $munich->id,
                Board::name => 'Premium Stadionfeld',
                Board::rows => 15,
                Board::columns => 10,
                Board::description => 'Exklusives Stadionfeld mit höchster Sichtbarkeit.',
            ]);
        }

        // Düsseldorf: 1 Board
        $duesseldorf = $locations->where(Location::name, 'Rhein-Ruhr Sportpark')->first();
        if ($duesseldorf) {
            Board::create([
                Board::location_id => $duesseldorf->id,
                Board::name => 'Sportpark Feld 1',
                Board::rows => 10,
                Board::columns => 8,
                Board::description => 'Modernes Feld mit digitaler Anzeigetafel.',
            ]);
        }

        // Dresden: 1 Board
        $dresden = $locations->where(Location::name, 'Elbe-Stadion Dresden')->first();
        if ($dresden) {
            Board::create([
                Board::location_id => $dresden->id,
                Board::name => 'Elbe-Feld Classic',
                Board::rows => 9,
                Board::columns => 7,
                Board::description => 'Klassisches Feld mit traditioneller Aufteilung.',
            ]);
        }
    }
}
