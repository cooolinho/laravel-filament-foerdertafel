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
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // Seed all board-related data in correct order
        $this->call([
            UserSeeder::class,
            LocationSeeder::class,
            BoardSeeder::class,
            FieldSeeder::class,
            CustomerSeeder::class,
            RentalSeeder::class,
            InquirySeeder::class,
            EmailSeeder::class,
            RentalAccessCodeEmailTemplateSeeder::class,
        ]);

        $this->command->info('✓ Demo-Datenbank erfolgreich befüllt!');
        $this->command->info('✓ 1 Standort erstellt');
        $this->command->info('✓ 1 Board erstellt');
        $this->command->info('✓ Mehrere Felder pro Board erstellt');
        $this->command->info('✓ 12 Kunden erstellt');
        $this->command->info('✓ 10 Vermietungen erstellt (Aktiv, Abgeschlossen, Storniert)');
        $this->command->info('✓ 5 Anfragen pro Board erstellt');
        $this->command->info('✓ Beispiel-E-Mails erstellt (Eingehend & Ausgehend)');
        $this->command->info('✓ Zugangscode-E-Mail-Vorlage erstellt');
    }
}

