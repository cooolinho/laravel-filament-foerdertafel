<?php

namespace Database\Seeders\Demo;

use App\Models\Board;
use App\Models\Inquiry;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boards = Board::with('fields')->get();

        if ($boards->isEmpty()) {
            $this->command->warn('No boards found. Please seed boards and fields first.');
            return;
        }

        foreach ($boards as $board) {
            if ($board->fields->isEmpty()) {
                continue;
            }

            // Create 5 sample inquiries per board
            for ($i = 1; $i <= 5; $i++) {

                // Randomly select 1-3 fields
                $selectedFields = $board->fields->random(rand(1, min(3, $board->fields->count())));

                $statuses = [
                    Inquiry::STATUS_PENDING,
                    Inquiry::STATUS_APPROVED,
                    Inquiry::STATUS_REJECTED,
                ];

                $isCompany = fake()->boolean(40);
                $rentalMonths = max(1, (int) Setting::get(Setting::default_rental_duration, 1));
                $startDate = now()->addDays(rand(1, 30))->startOfMonth();
                $endDate = (clone $startDate)->addMonths($rentalMonths)->subDay();

                $inquiry = Inquiry::create([
                    Inquiry::board_id => $board->id,
                    Inquiry::customer_name => fake()->name(),
                    Inquiry::customer_email => fake()->unique()->safeEmail(),
                    Inquiry::customer_phone => fake()->phoneNumber(),
                    Inquiry::is_company => $isCompany,
                    Inquiry::company_name => $isCompany ? fake()->company() : null,
                    Inquiry::street => fake()->streetName(),
                    Inquiry::street_nr => fake()->buildingNumber(),
                    Inquiry::zip => fake()->postcode(),
                    Inquiry::city => fake()->city(),
                    Inquiry::start_date => $startDate,
                    Inquiry::end_date => $endDate,
                    Inquiry::rental_months => $rentalMonths,
                    Inquiry::requested_fields => $selectedFields->pluck('id')->toArray(),
                    Inquiry::status => $statuses[array_rand($statuses)],
                    Inquiry::message => fake()->boolean(70) ? fake()->paragraph() : null,
                    Inquiry::admin_notes => fake()->boolean(30) ? fake()->sentence() : null,
                ]);

                // Attach fields to inquiry via pivot table
                $inquiry->fields()->attach($selectedFields->pluck('id'));

                $this->command->info("Created inquiry #{$inquiry->id} for board '{$board->name}' with " . $selectedFields->count() . " field(s)");
            }
        }
    }
}
