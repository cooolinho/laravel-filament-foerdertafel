<?php

namespace App\Console\Commands;

use App\Models\Field;
use App\Models\Rental;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class FixInconsistentFieldStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fields:fix-inconsistent-status
                            {--dry-run : Run without making any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix fields with inconsistent status (fields assigned to active rentals but not marked as rented)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');
        $isVerbose = $this->option('verbose');

        $this->info('Suche nach Feldern mit inkonsistentem Status...');
        $this->newLine();

        // Finde alle Felder mit inkonsistentem Status
        $inconsistentFields = $this->getInconsistentFields();

        $count = $inconsistentFields->count();

        if ($count === 0) {
            $this->info('✓ Keine inkonsistenten Felder gefunden!');
            return self::SUCCESS;
        }

        $this->warn("⚠ {$count} inkonsistente(s) Feld(er) gefunden:");
        $this->newLine();

        $tableData = [];
        $fixedCount = 0;

        foreach ($inconsistentFields as $field) {
            $activeRental = $field->rentals()
                ->where(Rental::status, Rental::STATUS_ACTIVE)
                ->where(Rental::start_date, '<=', now())
                ->where(Rental::end_date, '>=', now())
                ->with('customer')
                ->first();

            $tableData[] = [
                'ID' => $field->id,
                'Name' => $field->name,
                'Status' => $field->status,
                'Rental ID' => $activeRental?->id ?? '-',
                'Kunde' => $activeRental?->customer?->name ?? '-',
            ];

            if (!$isDryRun) {
                $field->status = Field::STATUS_RENTED;
                $field->save();
                $fixedCount++;

                if ($isVerbose) {
                    $this->info("  ✓ Feld #{$field->id} ({$field->name}) auf 'rented' gesetzt");
                }
            }
        }

        $this->table(
            ['ID', 'Name', 'Aktueller Status', 'Rental ID', 'Kunde'],
            $tableData
        );

        $this->newLine();

        if ($isDryRun) {
            $this->warn("DRY RUN: Keine Änderungen vorgenommen.");
            $this->info("Führe den Befehl ohne --dry-run aus, um die Änderungen zu speichern.");
        } else {
            $this->info("✓ {$fixedCount} Feld(er) wurden korrigiert!");

            // Log der Änderungen
            \Log::info("Fixed {$fixedCount} inconsistent field statuses", [
                'fields' => $inconsistentFields->pluck('id')->toArray(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }

        return self::SUCCESS;
    }

    /**
     * Get fields with inconsistent status.
     */
    protected function getInconsistentFields()
    {
        return Field::query()
            ->whereHas('rentals', function (Builder $query) {
                $query->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::start_date, '<=', now())
                    ->where(Rental::end_date, '>=', now());
            })
            ->where(Field::status, '!=', Field::STATUS_RENTED)
            ->with(['board', 'rentals' => function ($query) {
                $query->where(Rental::status, Rental::STATUS_ACTIVE)
                    ->where(Rental::start_date, '<=', now())
                    ->where(Rental::end_date, '>=', now())
                    ->with('customer');
            }])
            ->get();
    }
}
