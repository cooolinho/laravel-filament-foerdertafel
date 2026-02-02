<?php

namespace App\Observers;

use App\Events\RentalCreated;
use App\Events\RentalEnded;
use App\Models\Field;
use App\Models\Rental;

class RentalObserver
{
    /**
     * Handle the Rental "created" event.
     */
    public function created(Rental $rental): void
    {
        $this->updateFieldStatus($rental);

        // Löse RentalCreated Event aus
        event(new RentalCreated($rental));
    }

    /**
     * Handle the Rental "updated" event.
     */
    public function updated(Rental $rental): void
    {
        // Prüfe ob der Status auf "completed" oder "cancelled" geändert wurde
        if ($rental->isDirty(Rental::status)) {
            $oldStatus = $rental->getOriginal(Rental::status);
            $newStatus = $rental->status;

            // Wenn die Vermietung beendet oder abgebrochen wurde
            if (in_array($newStatus, [Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED])
                && $oldStatus === Rental::STATUS_ACTIVE) {

                // Löse RentalEnded Event aus
                event(new RentalEnded($rental));
            }
        }

        $this->updateFieldStatus($rental);
    }

    /**
     * Handle the Rental "deleted" event.
     */
    public function deleted(Rental $rental): void
    {
        // Wenn Rental gelöscht wird, setze alle Fields auf available
        // (falls sie nicht noch in anderen aktiven Rentals sind)
        $this->releaseFields($rental);

        // Löse RentalEnded Event aus wenn die Vermietung aktiv war
        if ($rental->status === Rental::STATUS_ACTIVE) {
            event(new RentalEnded($rental));
        }
    }

    /**
     * Update field status based on rental status
     */
    private function updateFieldStatus(Rental $rental): void
    {
        $fields = $rental->fields;

        foreach ($fields as $field) {
            $newStatus = match ($rental->status) {
                Rental::STATUS_ACTIVE => $this->determineActiveStatus($rental),
                Rental::STATUS_COMPLETED, Rental::STATUS_CANCELLED => Field::STATUS_AVAILABLE,
                default => Field::STATUS_AVAILABLE,
            };

            $field->update([Field::status => $newStatus]);
        }
    }

    /**
     * Determine status for active rentals based on dates
     */
    private function determineActiveStatus(Rental $rental): string
    {
        $now = now();
        $startDate = $rental->start_date;
        $endDate = $rental->end_date;

        // Wenn Start in der Zukunft -> RESERVED
        if ($startDate && $startDate > $now) {
            return Field::STATUS_RESERVED;
        }

        // Wenn bereits gestartet (und kein Enddatum oder Enddatum in der Zukunft) -> RENTED
        if (!$endDate || $endDate >= $now) {
            return Field::STATUS_RENTED;
        }

        // Wenn Enddatum in der Vergangenheit -> AVAILABLE
        return Field::STATUS_AVAILABLE;
    }

    /**
     * Release fields when rental is deleted
     */
    private function releaseFields(Rental $rental): void
    {
        $fields = $rental->fields;

        foreach ($fields as $field) {
            // Prüfe ob das Field noch in anderen aktiven Rentals ist
            $hasActiveRentals = $field->rentals()
                ->where('rentals.id', '!=', $rental->id)
                ->where(Rental::status, Rental::STATUS_ACTIVE)
                ->exists();

            // Nur auf AVAILABLE setzen, wenn keine anderen aktiven Rentals existieren
            if (!$hasActiveRentals) {
                $field->update([Field::status => Field::STATUS_AVAILABLE]);
            }
        }
    }
}
