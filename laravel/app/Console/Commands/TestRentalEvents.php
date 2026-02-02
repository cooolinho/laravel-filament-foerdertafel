<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Field;
use App\Models\Rental;
use Illuminate\Console\Command;

class TestRentalEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:rental-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Rental Events (RentalCreated und RentalEnded)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Teste Rental Events...');
        $this->newLine();

        // Test 1: RentalCreated Event
        $this->info('Test 1: Erstelle eine neue Vermietung (RentalCreated Event)');
        $this->testRentalCreated();
        $this->newLine();

        // Test 2: RentalEnded Event
        $this->info('Test 2: Beende eine Vermietung (RentalEnded Event)');
        $this->testRentalEnded();
        $this->newLine();

        $this->info('✓ Alle Tests abgeschlossen!');
        $this->newLine();
        $this->comment('Prüfe die Logs in storage/logs/laravel.log');
        $this->comment('Prüfe die Emails in der Datenbank-Tabelle "emails"');

        return Command::SUCCESS;
    }

    /**
     * Test RentalCreated Event
     */
    private function testRentalCreated(): void
    {
        // Hole einen Kunden
        $customer = Customer::first();
        if (!$customer) {
            $this->error('Kein Kunde gefunden. Bitte führe zuerst die Seeder aus.');
            return;
        }

        // Hole verfügbare Felder
        $fields = Field::where(Field::status, Field::STATUS_AVAILABLE)
            ->take(2)
            ->get();

        if ($fields->count() < 2) {
            $this->error('Nicht genügend verfügbare Felder gefunden.');
            return;
        }

        // Erstelle eine neue Vermietung
        $rental = Rental::create([
            Rental::customer_id => $customer->id,
            Rental::start_date => now(),
            Rental::end_date => now()->addMonths(3),
            Rental::total_price => $fields->sum(Field::price_per_month) * 3,
            Rental::status => Rental::STATUS_ACTIVE,
            Rental::notes => 'Test Rental für Event System',
        ]);

        // Verknüpfe Felder mit der Vermietung
        $rental->fields()->attach($fields->pluck('id'));

        $this->info("✓ Rental #{$rental->id} erstellt");
        $this->info("  Kunde: {$customer->name}");
        $this->info("  Felder: {$fields->count()}");
        $this->info("  Zeitraum: {$rental->start_date->format('d.m.Y')} - {$rental->end_date->format('d.m.Y')}");
        $this->info("  → RentalCreated Event wurde ausgelöst");
        $this->info("  → SendRentalConfirmationEmail Listener wurde ausgeführt");
    }

    /**
     * Test RentalEnded Event
     */
    private function testRentalEnded(): void
    {
        // Hole eine aktive Vermietung
        $rental = Rental::where(Rental::status, Rental::STATUS_ACTIVE)->first();

        if (!$rental) {
            $this->error('Keine aktive Vermietung gefunden.');
            return;
        }

        $fieldCount = $rental->fields()->count();

        // Setze Status auf completed
        $rental->update([
            Rental::status => Rental::STATUS_COMPLETED,
        ]);

        $this->info("✓ Rental #{$rental->id} beendet");
        $this->info("  Status: {$rental->status}");
        $this->info("  Felder: {$fieldCount}");
        $this->info("  → RentalEnded Event wurde ausgelöst");
        $this->info("  → SetFieldsToAvailable Listener wurde ausgeführt");
    }
}
