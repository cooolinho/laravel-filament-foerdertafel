<?php

namespace App\Listeners;

use App\Events\RentalPaid;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\RentalContent;
use Illuminate\Support\Facades\Log;

class SendAccessCodeEmail extends BaseEmailNotificationListener
{
    public static array $defaultVariables = [
        'customer_name' => 'Kundenname',
        'access_code'   => 'Persönlicher Zugangscode für das Kundenportal',
        'rental_id'     => 'Reservierungs-Nummer (ID)',
        'start_date'    => 'Mietbeginn (TT.MM.JJJJ)',
        'end_date'      => 'Mietende (TT.MM.JJJJ)',
        'access_url'    => 'Direktlink zum Kundenportal mit eingebettetem Code',
        'fields_count'  => 'Anzahl gemieteter Felder',
        'fields_list'   => 'Namen der gemieteten Felder (kommagetrennt)',
    ];

    /**
     * Handle the event.
     * Sendet nach Zahlungseingang den Zugangscode für das Kunden-Portal.
     *
     * Dank $afterCommit = true läuft dieser Listener erst nach dem vollständigen
     * DB-Commit, sodass alle Beziehungen korrekt geladen werden können.
     */
    public function handle(RentalPaid $event): void
    {
        $rental   = $event->rental;
        $customer = $rental->customer;

        // RentalContent erstellen oder laden (generiert ggf. den Access-Code)
        $rentalContent = RentalContent::firstOrCreate(
            [RentalContent::rental_id => $rental->id],
            [
                RentalContent::is_private_person => empty($customer->company_name),
            ]
        );

        // Felder frisch laden
        $rental->loadMissing('fields');

        $variables = [
            'customer_name' => $customer->name,
            'access_code'   => $rentalContent->access_code,
            'rental_id'     => $rental->id,
            'start_date'    => $rental->start_date->format('d.m.Y'),
            'end_date'      => $rental->end_date->format('d.m.Y'),
            'access_url'    => route('rental.content.access', ['code' => $rentalContent->access_code]),
            'fields_count'  => $rental->fields->count(),
            'fields_list'   => $rental->fields->map(fn ($field) => $field->name)->join(', '),
        ];

        $email = $this->createEmail(
            templateSlug: 'rental-access-code',
            toEmail:      $customer->email,
            toName:       $customer->name,
            variables:    $variables,
            extraData:    [
                Email::customer_id => $customer->id,
                Email::rental_id   => $rental->id,
                Email::metadata    => [
                    'event'     => 'rental_paid',
                    'rental_id' => $rental->id,
                ],
            ]
        );

        if ($email) {
            Log::info("Zugangscode-E-Mail für Rental #{$rental->id} an {$customer->email} erstellt (ID: {$email->id}).");
        }

        // E-Mail in die Queue einreihen
        SendEmailJob::dispatch($email);
    }
}
