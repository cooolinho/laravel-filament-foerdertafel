<?php

namespace Database\Seeders;

use Database\Seeders\Demo\BoardSeeder;
use Database\Seeders\Demo\CustomerSeeder;
use Database\Seeders\Demo\EmailSeeder;
use Database\Seeders\Demo\FieldSeeder;
use Database\Seeders\Demo\InquirySeeder;
use Database\Seeders\Demo\LocationSeeder;
use Database\Seeders\Demo\RentalAccessCodeEmailTemplateSeeder;
use Database\Seeders\Demo\RentalSeeder;
use Database\Seeders\Demo\UserSeeder;
use Database\Seeders\System\EmailTemplateSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Alle verfügbaren Seeder mit Beschreibung.
     */
    protected array $availableSeeders = [
        'System (alle System Seeder auf einmal)' => SystemSeeder::class,
        'System - E-Mail-Vorlagen (Templates)'   => EmailTemplateSeeder::class,
        'Demo (alle Demo Seeder auf einmal)'     => DemoSeeder::class,
        'Demo - Benutzer (Users)'                => UserSeeder::class,
        'Demo - Standorte (Locations)'           => LocationSeeder::class,
        'Demo - Boards'                          => BoardSeeder::class,
        'Demo - Felder (Fields)'                 => FieldSeeder::class,
        'Demo - Kunden (Customers)'              => CustomerSeeder::class,
        'Demo - Vermietungen (Rentals)'          => RentalSeeder::class,
        'Demo - Anfragen (Inquiries)'            => InquirySeeder::class,
        'Demo - E-Mails (Emails)'                => EmailSeeder::class,
        'Demo - Zugangscode-E-Mail-Vorlage'      => RentalAccessCodeEmailTemplateSeeder::class,
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $choices = $this->command->choice(
            'Welche Seeder sollen ausgeführt werden? (Mehrfachauswahl mit Komma möglich, z.B. 0,1,2)',
            array_keys($this->availableSeeders),
            null,
            null,
            true // Mehrfachauswahl aktivieren
        );

        $selectedSeeders = [];

        foreach ((array) $choices as $choice) {
            $seederClass = $this->availableSeeders[$choice] ?? null;

            if ($seederClass === null) {
                $this->command->warn("Unbekannte Auswahl übersprungen: {$choice}");
                continue;
            }

            // DemoSeeder enthält bereits alle anderen → direkt aufrufen und abbrechen
            if ($seederClass === DemoSeeder::class) {
                $this->command->info('▶ Führe Demo-Seeder aus (alle Seeder)...');
                $this->call(DemoSeeder::class);
                return;
            }

            $selectedSeeders[$choice] = $seederClass;
        }

        if (empty($selectedSeeders)) {
            $this->command->warn('Keine Seeder ausgewählt. Abbruch.');
            return;
        }


        foreach ($selectedSeeders as $label => $seederClass) {
            $this->command->info("▶ Führe aus: {$label}");
            $this->call($seederClass);
        }

        $this->command->info('✓ Ausgewählte Seeder erfolgreich ausgeführt!');
    }
}
