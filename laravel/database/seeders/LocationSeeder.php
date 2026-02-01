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
                Location::name => 'Sportplatz Berlin-Mitte',
                Location::address => 'Invalidenstraße 45, 10115 Berlin, Deutschland',
                Location::description => 'Hauptstandort in Berlin-Mitte mit modernster Ausstattung. Direkt am S-Bahnhof Nordbahnhof gelegen.',
            ],
            [
                Location::name => 'Sportarena Hamburg',
                Location::address => 'Reeperbahn 123, 20359 Hamburg, Deutschland',
                Location::description => 'Zentral gelegener Standort in Hamburg mit großem Parkplatz und guter Anbindung.',
            ],
            [
                Location::name => 'FC München Stadion',
                Location::address => 'Leopoldstraße 78, 80802 München, Deutschland',
                Location::description => 'Premium-Standort in München mit Blick auf die Alpen. Ideal für hochwertige Werbung.',
            ],
            [
                Location::name => 'Rhein-Ruhr Sportpark',
                Location::address => 'Königsallee 90, 40212 Düsseldorf, Deutschland',
                Location::description => 'Moderner Sportpark im Herzen der Rhein-Ruhr-Region.',
            ],
            [
                Location::name => 'Elbe-Stadion Dresden',
                Location::address => 'Prager Straße 15, 01069 Dresden, Deutschland',
                Location::description => 'Traditionsreicher Standort in Dresden mit historischem Charme.',
            ],
        ];

        foreach ($locations as $locationData) {
            Location::create($locationData);
        }
    }
}
