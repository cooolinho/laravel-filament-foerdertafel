<?php

namespace Database\Seeders;

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

        // 1. Max Mustermann - Premium Tor-Position (langfristig)
        $customer1 = $customers->where(Customer::name, 'Max Mustermann')->first();
        if ($customer1) {
            $torFields = $fields->where(Field::status, Field::STATUS_RENTED)
                ->filter(fn($f) => str_contains($f->name, 'Tor'))
                ->take(2);

            if ($torFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer1->id,
                    Rental::start_date => now()->subMonths(6),
                    Rental::end_date => now()->addMonths(12),
                    Rental::total_price => $torFields->sum(Field::price_per_month),
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Jahresvertrag mit Option auf Verlängerung.',
                ]);
                $rental->fields()->attach($torFields->pluck('id'));
            }
        }

        // 2. Anna Schmidt - Mittelkreis (mittelfristig)
        $customer2 = $customers->where(Customer::name, 'Anna Schmidt')->first();
        if ($customer2) {
            $mittelkreisFields = $fields->where(Field::status, Field::STATUS_RENTED)
                ->filter(fn($f) => str_contains($f->name, 'Mittelkreis'))
                ->take(1);

            if ($mittelkreisFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer2->id,
                    Rental::start_date => now()->subMonths(3),
                    Rental::end_date => now()->addMonths(6),
                    Rental::total_price => $mittelkreisFields->sum(Field::price_per_month),
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Testphase, eventuell Verlängerung.',
                ]);
                $rental->fields()->attach($mittelkreisFields->pluck('id'));
            }
        }

        // 3. Thomas Müller - Strafraum + Mittelfeld (Premium Paket)
        $customer3 = $customers->where(Customer::name, 'Thomas Müller')->first();
        if ($customer3) {
            $premiumFields = $fields->where(Field::status, Field::STATUS_RENTED)
                ->filter(fn($f) => str_contains($f->name, 'Strafraum') || str_contains($f->name, 'Mittelfeld'))
                ->take(3);

            if ($premiumFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer3->id,
                    Rental::start_date => now()->subMonths(12),
                    Rental::end_date => now()->addMonths(18),
                    Rental::total_price => $premiumFields->sum(Field::price_per_month),
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'VIP-Paket mit mehreren Premium-Positionen.',
                ]);
                $rental->fields()->attach($premiumFields->pluck('id'));
            }
        }

        // 4. Sarah Weber - Mehrere Standard-Felder
        $customer4 = $customers->where(Customer::name, 'Sarah Weber')->first();
        if ($customer4) {
            $standardFields = $fields->where(Field::status, Field::STATUS_RENTED)
                ->whereNotIn('id', Rental::with('fields')->get()->pluck('fields')->flatten()->pluck('id'))
                ->take(4);

            if ($standardFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer4->id,
                    Rental::start_date => now()->subMonth(),
                    Rental::end_date => now()->addMonths(3),
                    Rental::total_price => $standardFields->sum(Field::price_per_month),
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Budget-freundliches Paket für Neukundin.',
                ]);
                $rental->fields()->attach($standardFields->pluck('id'));
            }
        }

        // 5. Michael Becker - Einzelnes günstiges Feld (kurzfristig)
        $customer5 = $customers->where(Customer::name, 'Michael Becker')->first();
        if ($customer5) {
            $cheapField = $fields->where(Field::status, Field::STATUS_AVAILABLE)
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
                ->take(2);

            if ($availableFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer6->id,
                    Rental::start_date => now()->subMonths(8),
                    Rental::end_date => now()->subMonth(),
                    Rental::total_price => $availableFields->sum(Field::price_per_month),
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
                ->skip(2)
                ->take(1);

            if ($availableFields->isNotEmpty()) {
                $rental = Rental::create([
                    Rental::customer_id => $customer7->id,
                    Rental::start_date => now()->subMonths(2),
                    Rental::end_date => now()->addMonth(),
                    Rental::total_price => $availableFields->sum(Field::price_per_month),
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
                    Rental::total_price => $midFields->sum(Field::price_per_month),
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
                    Rental::total_price => $futureFields->sum(Field::price_per_month),
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
                ->take(5);

            if ($smallFields->isNotEmpty()) {
                foreach ($smallFields as $field) {
                    $field->update([Field::status => Field::STATUS_RENTED]);
                }

                $rental = Rental::create([
                    Rental::customer_id => $customer10->id,
                    Rental::start_date => now()->subWeeks(2),
                    Rental::end_date => now()->addMonths(4),
                    Rental::total_price => $smallFields->sum(Field::price_per_month),
                    Rental::status => Rental::STATUS_ACTIVE,
                    Rental::notes => 'Event-Paket mit mehreren kleinen Flächen.',
                ]);
                $rental->fields()->attach($smallFields->pluck('id'));
            }
        }
    }
}
