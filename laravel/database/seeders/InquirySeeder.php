<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Field;
use App\Models\Inquiry;
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
                $startDate = now()->addDays(rand(1, 30));
                $endDate = (clone $startDate)->addDays(rand(30, 90));

                // Randomly select 1-3 fields
                $selectedFields = $board->fields->random(rand(1, min(3, $board->fields->count())));

                $statuses = [
                    Inquiry::STATUS_PENDING,
                    Inquiry::STATUS_APPROVED,
                    Inquiry::STATUS_REJECTED,
                ];

                $inquiry = Inquiry::create([
                    Inquiry::board_id => $board->id,
                    Inquiry::customer_name => fake()->name(),
                    Inquiry::customer_email => fake()->unique()->safeEmail(),
                    Inquiry::customer_phone => fake()->phoneNumber(),
                    Inquiry::start_date => $startDate,
                    Inquiry::end_date => $endDate,
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
