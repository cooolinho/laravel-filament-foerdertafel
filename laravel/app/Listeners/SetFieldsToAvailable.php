<?php

namespace App\Listeners;

use App\Events\RentalEnded;
use App\Models\Field;
use Illuminate\Support\Facades\Log;

class SetFieldsToAvailable
{
    /**
     * Handle the event.
     */
    public function handle(RentalEnded $event): void
    {
        $rental = $event->rental;

        // Hole alle Felder der beendeten Vermietung
        $fields = $rental->fields;

        if ($fields->isEmpty()) {
            Log::info("Keine Felder für Rental #{$rental->id} gefunden.");
            return;
        }

        // Setze alle Felder auf "verfügbar"
        foreach ($fields as $field) {
            // Prüfe ob das Feld noch vermietet ist
            if ($field->status === Field::STATUS_RENTED) {
                $field->update([
                    Field::status => Field::STATUS_AVAILABLE,
                ]);

                Log::info("Feld #{$field->id} ({$field->name}) wurde auf 'verfügbar' gesetzt.");
            }
        }

        Log::info("Alle Felder von Rental #{$rental->id} wurden auf 'verfügbar' gesetzt.");
    }
}
