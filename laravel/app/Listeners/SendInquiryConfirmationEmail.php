<?php

namespace App\Listeners;

use App\Events\InquiryCreated;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SendInquiryConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(InquiryCreated $event): void
    {
        $inquiry = $event->inquiry;

        // Prüfe ob E-Mail-Benachrichtigungen aktiviert sind
        $settings = Setting::current();
        if ($settings && !$settings->email_notifications_enabled) {
            Log::info("E-Mail-Benachrichtigungen sind deaktiviert. Keine Bestätigungs-E-Mail für Anfrage #{$inquiry->id} gesendet.");
            return;
        }

        // Hole die E-Mail-Vorlage für Anfrage-Bestätigungen
        $template = EmailTemplate::where(EmailTemplate::slug, 'inquiry-confirmation')
            ->where(EmailTemplate::is_active, true)
            ->first();

        if (!$template) {
            // Fallback: Verwende Standard-E-Mail-Vorlage aus Settings
            if ($settings && $settings->default_email_template_id) {
                $template = EmailTemplate::find($settings->default_email_template_id);
            }

            if (!$template) {
                Log::warning("Keine E-Mail-Vorlage für Anfrage-Bestätigung gefunden. Anfrage #{$inquiry->id}");
                return;
            }
        }

        // Bereite die Variablen für die E-Mail-Vorlage vor
        $board = $inquiry->board;
        $location = $board?->location;

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
            'customer_name'   => $customerName,
            'customer_email'  => $inquiry->customer_email,
            'customer_phone'  => $inquiry->customer_phone ?? 'N/A',
            'inquiry_id'      => $inquiry->id,
            'inquiry_date'    => $inquiry->created_at?->format('d.m.Y') ?? now()->format('d.m.Y'),
            'start_date'      => \Carbon\Carbon::parse($inquiry->start_date)->format('d.m.Y'),
            'end_date'        => \Carbon\Carbon::parse($inquiry->end_date)->format('d.m.Y'),
            'rental_months'   => $inquiry->rental_months,
            'field_count'     => $inquiry->fields()->count(),
            'board_name'      => $board?->name ?? 'N/A',
            'location_name'   => $location?->name ?? 'N/A',
            'address'         => $address ?: 'N/A',
            'message'         => $inquiry->message ?? '',
        ];

        // Ersetze Variablen im Betreff und Body
        $subject  = $this->replaceVariables($template->subject, $variables);
        $bodyHtml = $this->replaceVariables($template->body_html, $variables);
        $bodyText = $template->body_text ? $this->replaceVariables($template->body_text, $variables) : null;

        // Erstelle E-Mail-Datensatz
        $email = Email::create([
            Email::direction        => Email::DIRECTION_OUTBOUND,
            Email::status           => Email::STATUS_DRAFT,
            Email::from_email       => $template->from_email ?? config('mail.from.address'),
            Email::from_name        => $template->from_name ?? config('mail.from.name'),
            Email::to_email         => $inquiry->customer_email,
            Email::to_name          => $customerName,
            Email::reply_to         => $template->reply_to,
            Email::subject          => $subject,
            Email::body_html        => $bodyHtml,
            Email::body_text        => $bodyText,
            Email::email_template_id => $template->id,
            Email::metadata         => [
                'event'      => 'inquiry_created',
                'inquiry_id' => $inquiry->id,
            ],
        ]);

        // Anhänge aus der Template-Konfiguration verknüpfen
        $templateDocuments = $template->documents;
        if ($templateDocuments->isNotEmpty()) {
            $email->documents()->attach($templateDocuments->pluck('id')->toArray());
        }

        // Versende E-Mail über Queue
        SendEmailJob::dispatch($email);

        Log::info("Anfrage-Bestätigungs-E-Mail für Anfrage #{$inquiry->id} an {$inquiry->customer_email} erstellt und zur Warteschlange hinzugefügt.");
    }

    /**
     * Ersetze Variablen in einem Text durch ihre Werte.
     */
    private function replaceVariables(string $text, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
            $text = str_replace('{{ ' . $key . ' }}', $value, $text);
        }

        return $text;
    }
}

