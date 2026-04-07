<?php

namespace Database\Seeders\Demo;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Rental;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $fields = Field::all();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        if ($fields->isEmpty()) {
            $this->command->warn('No fields found. Please run FieldSeeder first.');
            return;
        }

        // Erstelle verschiedene Rental-Szenarien

        // 1. Max Mustermann - Beide Tore (Premium-Position, langfristig)
        $customer1 = $customers->where(Customer::name, 'Max Mustermann')->first();
        if ($customer1) {
            $torFields = $fields->filter(fn($f) => str_contains($f->name, 'Tor'));

            if ($torFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer1->id,
                    Rental::start_date => now()->subMonths(6),
                    Rental::end_date => now()->addMonths(12),
                    Rental::total_price => $torFields->sum(Field::price_per_month) * 6, // 6 Monate bereits vermietet
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Jahresvertrag für beide Tor-Positionen mit Option auf Verlängerung.',
                ]);
                $rental->fields()->attach($torFields->pluck('id'));
            }
        }

        // 2. Anna Schmidt - Mittelkreis (Premium-Position, mittelfristig)
        $customer2 = $customers->where(Customer::name, 'Anna Schmidt')->first();
        if ($customer2) {
            $mittelkreisFields = $fields->filter(fn($f) => str_contains($f->name, 'Mittelkreis'));

            if ($mittelkreisFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer2->id,
                    Rental::start_date => now()->subMonths(3),
                    Rental::end_date => now()->addMonths(6),
                    Rental::total_price => $mittelkreisFields->sum(Field::price_per_month) * 9, // 9 Monate Vertrag
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Testphase für Mittelkreis-Position, eventuell Verlängerung.',
                ]);
                $rental->fields()->attach($mittelkreisFields->pluck('id'));
            }
        }

        // 3. Thomas Müller - Alle 4 Ecken (Paket)
        $customer3 = $customers->where(Customer::name, 'Thomas Müller')->first();
        if ($customer3) {
            $eckenFields = $fields->filter(fn($f) => str_contains($f->name, 'Ecke'));

            if ($eckenFields->isNotEmpty()) {
                foreach ($eckenFields as $field) {
                    if ($field->status === Field::STATUS_AVAILABLE) {
                        $field->update([Field::status => Field::STATUS_RENTED]);
                    }
                }

                $rental = Rental::create([
                    Rental::customer_id => $customer3->id,
                    Rental::start_date => now()->subMonths(12),
                    Rental::end_date => now()->addMonths(18),
                    Rental::total_price => $eckenFields->sum(Field::price_per_month) * 30, // 30 Monate gesamt
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Langfristiges Paket mit allen 4 Eckpositionen.',
                ]);
                $rental->fields()->attach($eckenFields->pluck('id'));
            }
        }

        // 4. Sarah Weber - Mehrere Standard-Felder
        $customer4 = $customers->where(Customer::name, 'Sarah Weber')->first();
        if ($customer4) {
            // Hole bereits vermietete Field IDs
            $rentedFieldIds = Rental::with('fields')->get()->pluck('fields')->flatten()->pluck('id')->toArray();

            $standardFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->whereNotIn('id', $rentedFieldIds)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->take(4);

            if ($standardFields->isNotEmpty()) {
                foreach ($standardFields as $field) {
                    $field->update([Field::status => Field::STATUS_RENTED]);
                }

                $rental = Rental::create([
                    Rental::customer_id => $customer4->id,
                    Rental::start_date => now()->subMonth(),
                    Rental::end_date => now()->addMonths(3),
                    Rental::total_price => $standardFields->sum(Field::price_per_month) * 4, // 4 Monate
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Budget-freundliches Paket für Neukundin.',
                ]);
                $rental->fields()->attach($standardFields->pluck('id'));
            }
        }

        // 5. Michael Becker - Einzelnes Standard-Feld (kurzfristig)
        $customer5 = $customers->where(Customer::name, 'Michael Becker')->first();
        if ($customer5) {
            $cheapField = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->sortBy(Field::price_per_month)
                ->first();

            if ($cheapField) {
                $cheapField->update([Field::status => Field::STATUS_RENTED]);
                $rental = Rental::create([
                    Rental::customer_id => $customer5->id,
                    Rental::start_date => now(),
                    Rental::end_date => now()->addMonths(1),
                    Rental::total_price => $cheapField->price_per_month,
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Einmonatiger Test für Privatperson.',
                ]);
                $rental->fields()->attach([$cheapField->id]);
            }
        }

        // 6. Julia Fischer - Abgelaufene Vermietung (Completed)
        $customer6 = $customers->where(Customer::name, 'Julia Fischer')->first();
        if ($customer6) {
            $availableFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->take(2);

            if ($availableFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer6->id,
                    Rental::start_date => now()->subMonths(8),
                    Rental::end_date => now()->subMonth(),
                    Rental::total_price => $availableFields->sum(Field::price_per_month) * 7, // 7 Monate
                    Rental::status => Rental::STATUS_COMPLETED,
                    Rental::notes => 'Vertrag erfolgreich abgeschlossen.',
                ]);
                $rental->fields()->attach($availableFields->pluck('id'));
            }
        }

        // 7. Peter Hoffmann - Stornierte Vermietung
        $customer7 = $customers->where(Customer::name, 'Peter Hoffmann')->first();
        if ($customer7) {
            $availableFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->skip(2)
                ->take(1);

            if ($availableFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer7->id,
                    Rental::start_date => now()->subMonths(2),
                    Rental::end_date => now()->addMonth(),
                    Rental::total_price => $availableFields->sum(Field::price_per_month) * 3, // 3 Monate
                    Rental::status => Rental::STATUS_CANCELLED,
                    Rental::notes => 'Kunde hat aufgrund von Budget-Kürzungen storniert.',
                ]);
                $rental->fields()->attach($availableFields->pluck('id'));
            }
        }

        // 8. Lisa Schneider - Ongoing Rental (kein Enddatum)
        $customer8 = $customers->where(Customer::name, 'Lisa Schneider')->first();
        if ($customer8) {
            $midFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->skip(3)
                ->take(2);

            if ($midFields->isNotEmpty()) {
                foreach ($midFields as $field) {
                    $field->update([Field::status => Field::STATUS_RENTED]);
                }

                $rental = Rental::create([
                    Rental::customer_id => $customer8->id,
                    Rental::start_date => now()->subMonths(4),
                    Rental::end_date => null, // Unbefristetes Rental
                    Rental::total_price => $midFields->sum(Field::price_per_month) * 4, // 4 Monate bisher
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Unbefristete Vermietung mit monatlicher Kündigungsfrist.',
                ]);
                $rental->fields()->attach($midFields->pluck('id'));
            }
        }

        // 9. Daniel Koch - Zukünftige Vermietung
        $customer9 = $customers->where(Customer::name, 'Daniel Koch')->first();
        if ($customer9) {
            $futureFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->skip(5)
                ->take(2);

            if ($futureFields->isNotEmpty()) {
                foreach ($futureFields as $field) {
                    $field->update([Field::status => Field::STATUS_RESERVED]);
                }

                $rental = Rental::create([
                    Rental::customer_id => $customer9->id,
                    Rental::start_date => now()->addMonth(),
                    Rental::end_date => now()->addMonths(7),
                    Rental::total_price => $futureFields->sum(Field::price_per_month) * 6, // 6 Monate
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Vorab-Buchung für nächste Saison.',
                ]);
                $rental->fields()->attach($futureFields->pluck('id'));
            }
        }

        // 10. Sabine Wagner - Mehrere kleine Felder
        $customer10 = $customers->where(Customer::name, 'Sabine Wagner')->first();
        if ($customer10) {
            $smallFields = $fields->where(Field::status, Field::STATUS_AVAILABLE)
                ->where(Field::width, 1)
                ->where(Field::height, 1)
                ->skip(7)
                ->take(5);

            if ($smallFields->isNotEmpty()) {

                $rental = Rental::create([
                    Rental::customer_id => $customer10->id,
                    Rental::start_date => now()->startOfMonth(),
                    Rental::end_date => now()->addMonths(4),
                    Rental::total_price => $smallFields->sum(Field::price_per_month) * 4, // 4 Monate
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Event-Paket mit mehreren kleinen Flächen.',
                ]);
                $rental->fields()->attach($smallFields->pluck('id'));
            }
        }
    }
}
