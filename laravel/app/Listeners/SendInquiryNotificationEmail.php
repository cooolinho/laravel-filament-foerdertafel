<?php

namespace App\Listeners;

use App\Events\InquiryCreated;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Sendet bei jeder neuen Anfrage eine interne Benachrichtigungs-E-Mail
 * an alle in den Einstellungen hinterlegten Adressen (notification_email_addresses).
 */
class SendInquiryNotificationEmail extends BaseEmailNotificationListener
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
        'admin_url'      => 'Direktlink zur Anfrage im Admin-Bereich',
    ];

    /**
     * Handle the event.
     * Wird nach InquiryCreated ausgelöst und sendet Benachrichtigungen
     * an alle in den Einstellungen hinterlegten E-Mail-Adressen.
     */
    public function handle(InquiryCreated $event): void
    {
        $settings = app(GeneralSettings::class);

        if (!$this->notificationsEnabled($settings)) {
            return;
        }

        $recipients = $settings->notification_email_addresses ?? [];

        if (empty($recipients)) {
            Log::info('SendInquiryNotificationEmail: Keine Benachrichtigungs-E-Mail-Adressen konfiguriert – kein Versand.');
            return;
        }

        $inquiry = $event->inquiry;
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
            'rental_months'  => $inquiry->rental_months,
            'field_count'    => $fields->count(),
            'board_name'     => $board?->name ?? 'N/A',
            'location_name'  => $location?->name ?? 'N/A',
            'address'        => $address ?: 'N/A',
            'message'        => $inquiry->message ?? '',
            'admin_url'      => url('/admin/inquiries/' . $inquiry->id),
        ];

        foreach ($recipients as $recipientEmail) {
            $recipientEmail = trim($recipientEmail);

            if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
                Log::warning("SendInquiryNotificationEmail: Ungültige E-Mail-Adresse übersprungen: '{$recipientEmail}'");
                continue;
            }

            $email = $this->createEmail(
                templateSlug: 'inquiry-notification',
                toEmail:      $recipientEmail,
                toName:       $recipientEmail,
                variables:    $variables,
                extraData:    [
                    Email::metadata => [
                        'event'      => 'inquiry_created_notification',
                        'inquiry_id' => $inquiry->id,
                    ],
                ]
            );

            if ($email) {
                SendEmailJob::dispatch($email);
                Log::info("Anfrage-Benachrichtigungs-E-Mail für Anfrage #{$inquiry->id} an {$recipientEmail} erstellt (E-Mail-ID: {$email->id}).");
            }
        }
    }
}

