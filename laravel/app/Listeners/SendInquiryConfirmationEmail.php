<?php

namespace App\Listeners;

use App\Events\InquiryCreated;
use App\Models\Email;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendInquiryConfirmationEmail extends BaseEmailNotificationListener
{
    public static array $defaultVariables = [
        'customer_name'  => 'Kundenname (oder Firmenname)',
        'customer_email' => 'E-Mail-Adresse des Kunden',
        'customer_phone' => 'Telefonnummer des Kunden',
        'inquiry_id'     => 'Anfrage-Nummer (ID)',
        'inquiry_date'   => 'Datum der Anfrage (TT.MM.JJJJ)',
        'start_date'     => 'Gewünschter Mietbeginn (TT.MM.JJJJ)',
        'end_date'       => 'Gewünschtes Mietende (TT.MM.JJJJ)',
        'rental_months'  => 'Mietdauer in Monaten',
        'field_count'    => 'Anzahl der angefragten Felder',
        'board_name'     => 'Name der Fördertafel',
        'location_name'  => 'Standortname der Tafel',
        'address'        => 'Postanschrift des Kunden',
        'message'        => 'Optionale Nachricht des Kunden',
    ];

    /**
     * Handle the event.
     * Wird sowohl für InquiryCreated (automatisch) als auch für
     * InquiryConfirmationEmailRequested (manueller Wiederversand) verwendet.
     *
     * Dank $afterCommit = true läuft dieser Listener erst nach dem vollständigen
     * DB-Commit, sodass inquiry->fields korrekt geladen werden können.
     */
    public function handle(InquiryCreated $event): void
    {
        $inquiry = $event->inquiry;

        // Beziehungen frisch laden – stellt sicher, dass fields nach dem Commit verfügbar sind
        $inquiry->loadMissing(['board.location', 'fields']);

        $board    = $inquiry->board;
        $location = $board?->location;
        $fields   = $inquiry->fields;

        $customerName = $inquiry->is_company && $inquiry->company_name
            ? $inquiry->company_name
            : $inquiry->customer_name;

        $address = collect([
            $inquiry->street && $inquiry->street_nr
                ? "{$inquiry->street} {$inquiry->street_nr}"
                : ($inquiry->street ?? null),
            $inquiry->zip && $inquiry->city
                ? "{$inquiry->zip} {$inquiry->city}"
                : ($inquiry->city ?? null),
        ])->filter()->join(', ');

        $variables = [
            'customer_name'  => $customerName,
            'customer_email' => $inquiry->customer_email,
            'customer_phone' => $inquiry->customer_phone ?? 'N/A',
            'inquiry_id'     => $inquiry->id,
            'inquiry_date'   => $inquiry->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'start_date'     => Carbon::parse($inquiry->start_date)->format('d.m.Y'),
            'end_date'       => Carbon::parse($inquiry->end_date)->format('d.m.Y'),
            'total_price'    => number_format($inquiry->getTotalPrice(), 2, ',', '.'),
            'rental_months'  => $inquiry->rental_months,
            'field_count'    => $fields->count(),
            'board_name'     => $board?->name ?? 'N/A',
            'location_name'  => $location?->name ?? 'N/A',
            'address'        => $address ?: 'N/A',
            'message'        => $inquiry->message ?? '',
        ];

        $email = $this->createAndDispatchEmail(
            templateSlug: 'inquiry-confirmation',
            toEmail:      $inquiry->customer_email,
            toName:       $customerName,
            variables:    $variables,
            extraData:    [
                Email::metadata => [
                    'event'      => 'inquiry_created',
                    'inquiry_id' => $inquiry->id,
                ],
            ]
        );

        if ($email) {
            Log::info("Anfrage-Bestätigungs-E-Mail für Anfrage #{$inquiry->id} an {$inquiry->customer_email} erstellt (ID: {$email->id}).");
        }
    }
}

