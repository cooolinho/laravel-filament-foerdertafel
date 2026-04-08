<?php

namespace App\Listeners;

use App\Events\RentalCreated;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use Illuminate\Support\Facades\Log;

class SendRentalConfirmationEmail extends BaseEmailNotificationListener
{
    public static array $defaultVariables = [
        'customer_name'  => 'Kundenname (oder Firmenname)',
        'rental_id'      => 'Reservierungs-Nummer (ID)',
        'rental_start'   => 'Mietbeginn (TT.MM.JJJJ)',
        'rental_end'     => 'Mietende (TT.MM.JJJJ)',
        'total_price'    => 'Gesamtpreis (z. B. 12,50)',
        'field_count'    => 'Anzahl gemieteter Felder',
        'field_names'    => 'Namen der gemieteten Felder (kommagetrennt)',
        'board_name'     => 'Name der Fördertafel',
        'location_name'  => 'Standortname der Tafel',
    ];

    /**
     * Handle the event.
     * Wird sowohl für RentalCreated (automatisch via Observer) als auch für
     * RentalConfirmationEmailRequested (manueller Wiederversand) verwendet.
     *
     * Dank $afterCommit = true läuft dieser Listener erst nach dem vollständigen
     * DB-Commit, sodass rental->fields korrekt geladen werden können.
     */
    public function handle(RentalCreated $event): void
    {
        $rental   = $event->rental;
        $customer = $rental->customer;

        // Beziehungen frisch laden um sicherzustellen, dass alle nach dem Commit verfügbar sind
        $rental->loadMissing('fields.board.location');

        $fields   = $rental->fields;
        $board    = $fields->first()?->board;
        $location = $board?->location;

        $customerName = $customer->name ?? $customer->company_name;

        $variables = [
            'customer_name'  => $customerName,
            'rental_id'      => $rental->id,
            'rental_start'   => $rental->start_date->format('d.m.Y'),
            'rental_end'     => $rental->end_date->format('d.m.Y'),
            'total_price'    => number_format($rental->total_price * $rental->rental_months, 2, ',', '.'),
            'field_count'    => $fields->count(),
            'field_names'    => $fields->pluck('name')->join(', '),
            'board_name'     => $board?->name ?? 'N/A',
            'location_name'  => $location?->name ?? 'N/A',
        ];

        $email = $this->createEmail(
            templateSlug: 'rental-confirmation',
            toEmail:      $customer->email,
            toName:       $customerName,
            variables:    $variables,
            extraData:    [
                Email::customer_id => $customer->id,
                Email::rental_id   => $rental->id,
                Email::metadata    => [
                    'event'     => 'rental_created',
                    'rental_id' => $rental->id,
                ],
            ]
        );

        if ($email) {
            Log::info("Mietbestätigungs-E-Mail für Rental #{$rental->id} an {$customer->email} erstellt (ID: {$email->id}).");
        }

        // E-Mail in die Queue einreihen
        SendEmailJob::dispatch($email);
    }
}
