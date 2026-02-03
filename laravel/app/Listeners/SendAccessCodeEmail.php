<?php

namespace App\Listeners;

use App\Events\RentalPaid;
use App\Jobs\SendEmailJob;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\RentalContent;
use Illuminate\Support\Facades\Log;

class SendAccessCodeEmail
{
    /**
     * Handle the event.
     */
    public function handle(RentalPaid $event): void
    {
        $rental = $event->rental;
        $customer = $rental->customer;

        try {
            // Erstelle oder hole den RentalContent für diese Rental
            $rentalContent = RentalContent::firstOrCreate(
                [RentalContent::rental_id => $rental->id],
                [
                    RentalContent::is_private_person => empty($customer->company_name),
                ]
            );

            // Hole das Email-Template für Zugangscode
            $template = EmailTemplate::where('slug', 'rental-access-code')->first();

            if (!$template) {
                Log::error("Email-Template 'rental-access-code' nicht gefunden");
                return;
            }

            // Bereite die Template-Variablen vor
            $variables = [
                'customer_name' => $customer->name,
                'access_code' => $rentalContent->access_code,
                'rental_id' => $rental->id,
                'start_date' => $rental->start_date->format('d.m.Y'),
                'end_date' => $rental->end_date->format('d.m.Y'),
                'access_url' => route('rental.content.access', ['code' => $rentalContent->access_code]),
                'fields_count' => $rental->fields->count(),
                'fields_list' => $rental->fields->map(fn($field) => $field->name)->join(', '),
            ];

            // Ersetze Platzhalter im Subject und Body
            $subject = $this->replacePlaceholders($template->subject, $variables);
            $bodyHtml = $this->replacePlaceholders($template->body_html, $variables);
            $bodyText = $this->replacePlaceholders($template->body_text ?? '', $variables);

            // Erstelle Email-Eintrag
            $email = Email::create([
                Email::from_email => config('mail.from.address'),
                Email::from_name => config('mail.from.name'),
                Email::to_email => $customer->email,
                Email::to_name => $customer->name,
                Email::subject => $subject,
                Email::body_html => $bodyHtml,
                Email::body_text => $bodyText,
                Email::email_template_id => $template->id,
                Email::status => Email::STATUS_DRAFT,
                Email::direction => Email::DIRECTION_OUTBOUND,
            ]);

            // Versende Email
            SendEmailJob::dispatch($email);

            Log::info("Zugangscode-Email für Rental #{$rental->id} erstellt und in Queue eingereiht");

        } catch (\Exception $e) {
            Log::error("Fehler beim Versenden der Zugangscode-Email: " . $e->getMessage());
        }
    }

    /**
     * Ersetze Platzhalter im Template
     */
    private function replacePlaceholders(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{{" . $key . "}}", $value, $content);
        }
        return $content;
    }
}
