<?php

namespace Database\Seeders;

use Database\Seeders\System\EmailTemplateSeeder;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // Seed all board-related data in correct order
        $this->call([
            EmailTemplateSeeder::class,
        ]);

        $this->command->info('✓ E-Mail-Vorlagen erstellt');
    }
}

