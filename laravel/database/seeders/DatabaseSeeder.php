<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // Seed all board-related data in correct order
        $this->call([
            LocationSeeder::class,
            BoardSeeder::class,
            FieldSeeder::class,
            CustomerSeeder::class,
            RentalSeeder::class,
            InquirySeeder::class,
            EmailTemplateSeeder::class,
            EmailSeeder::class,
        ]);

        $this->command->info('✓ Database seeded successfully!');
        $this->command->info('✓ 1 Locations created');
        $this->command->info('✓ 1 Boards created');
        $this->command->info('✓ Multiple Fields created per Board');
        $this->command->info('✓ 12 Customers created');
        $this->command->info('✓ 10 Rentals created (Active, Completed, Cancelled)');
        $this->command->info('✓ 5 Inquiries created per Board');
        $this->command->info('✓ Email Templates created');
        $this->command->info('✓ Sample Emails created (Inbound & Outbound)');
    }
}
