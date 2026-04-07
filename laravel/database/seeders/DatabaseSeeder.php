<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Alle verfügbaren Seeder mit Beschreibung.
     */
    protected array $availableSeeders = [
        'Demo (alle Seeder auf einmal)'   => DemoSeeder::class,
        'Benutzer (Users)'                => UserSeeder::class,
        'Standorte (Locations)'           => LocationSeeder::class,
        'Boards'                          => BoardSeeder::class,
        'Felder (Fields)'                 => FieldSeeder::class,
        'Kunden (Customers)'              => CustomerSeeder::class,
        'Vermietungen (Rentals)'          => RentalSeeder::class,
        'Anfragen (Inquiries)'            => InquirySeeder::class,
        'E-Mail-Vorlagen (Templates)'     => EmailTemplateSeeder::class,
        'E-Mails (Emails)'                => EmailSeeder::class,
        'Zugangscode-E-Mail-Vorlage'      => RentalAccessCodeEmailTemplateSeeder::class,
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
