<?php

namespace App\Listeners;

use App\Events\RentalCreated;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SendRentalConfirmationEmail
{
    /**
     * Handle the event.
     */
    public function handle(RentalCreated $event): void
    {
        $rental = $event->rental;
        $customer = $rental->customer;

        // Prüfe ob E-Mail-Benachrichtigungen aktiviert sind
        $settings = Setting::current();
        if ($settings && !$settings->email_notifications_enabled) {
            Log::info("E-Mail-Benachrichtigungen sind deaktiviert. Keine Bestätigungs-E-Mail für Rental #{$rental->id} gesendet.");
            return;
        }

        // Hole die E-Mail-Vorlage für Mietbestätigungen
        $template = EmailTemplate::where(EmailTemplate::slug, 'rental-confirmation')
            ->where(EmailTemplate::is_active, true)
            ->first();

        if (!$template) {
            // Fallback: Verwende Standard-E-Mail-Vorlage aus Settings
            if ($settings && $settings->default_email_template_id) {
                $template = EmailTemplate::find($settings->default_email_template_id);
            }

            if (!$template) {
                Log::warning("Keine E-Mail-Vorlage für Mietbestätigung gefunden. Rental #{$rental->id}");
                return;
            }
        }

        // Bereite die Variablen für die E-Mail-Vorlage vor
        $fields = $rental->fields;
        $board = $fields->first()?->board;
        $location = $board?->location;

        $variables = [
            'customer_name' => $customer->name ?? $customer->company_name,
            'rental_id' => $rental->id,
            'rental_start' => $rental->start_date->format('d.m.Y'),
            'rental_end' => $rental->end_date->format('d.m.Y'),
            'total_price' => number_format($rental->total_price, 2, ',', '.'),
            'field_count' => $fields->count(),
            'field_names' => $fields->pluck('name')->join(', '),
            'board_name' => $board?->name ?? 'N/A',
            'location_name' => $location?->name ?? 'N/A',
        ];

        // Ersetze Variablen im Betreff und Body
        $subject = $this->replaceVariables($template->subject, $variables);
        $bodyHtml = $this->replaceVariables($template->body_html, $variables);
        $bodyText = $template->body_text ? $this->replaceVariables($template->body_text, $variables) : null;

        // Erstelle E-Mail-Datensatz
        $email = Email::create([
            Email::direction => Email::DIRECTION_OUTBOUND,
            Email::status => Email::STATUS_DRAFT,
            Email::from_email => $template->from_email ?? config('mail.from.address'),
            Email::from_name => $template->from_name ?? config('mail.from.name'),
            Email::to_email => $customer->email,
            Email::to_name => $customer->name ?? $customer->company_name,
            Email::reply_to => $template->reply_to,
            Email::subject => $subject,
            Email::body_html => $bodyHtml,
            Email::body_text => $bodyText,
            Email::email_template_id => $template->id,
            Email::customer_id => $customer->id,
            Email::rental_id => $rental->id,
            Email::metadata => [
                'event' => 'rental_created',
                'rental_id' => $rental->id,
            ],
        ]);

        // Versende E-Mail über Queue
        SendEmailJob::dispatch($email);

        Log::info("Mietbestätigungs-E-Mail für Rental #{$rental->id} an {$customer->email} erstellt und zur Warteschlange hinzugefügt.");
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
