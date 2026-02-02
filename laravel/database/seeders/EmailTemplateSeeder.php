<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                EmailTemplate::name => 'Buchungsbestätigung',
                EmailTemplate::slug => 'rental-confirmation',
                EmailTemplate::subject => 'Ihre Buchung wurde bestätigt - Reservierung {{ rental_id }}',
                EmailTemplate::body_html => $this->getRentalConfirmationHtml(),
                EmailTemplate::body_text => $this->getRentalConfirmationText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
                EmailTemplate::description => 'Wird nach erfolgreicher Buchung an den Kunden gesendet',
                EmailTemplate::available_variables => [
                    'customer_name', 'rental_id', 'rental_start', 'rental_end',
                    'board_name', 'location_name', 'total_price'
                ],
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Anfrageeingang bestätigen',
                EmailTemplate::slug => 'inquiry-received',
                EmailTemplate::subject => 'Ihre Anfrage ist bei uns eingegangen',
                EmailTemplate::body_html => $this->getInquiryReceivedHtml(),
                EmailTemplate::body_text => $this->getInquiryReceivedText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_INQUIRY,
                EmailTemplate::description => 'Bestätigung für eingegangene Anfragen',
                EmailTemplate::available_variables => [
                    'customer_name', 'customer_email', 'inquiry_date'
                ],
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Mietende Erinnerung',
                EmailTemplate::slug => 'rental-ending-reminder',
                EmailTemplate::subject => 'Ihre Mietzeit endet bald - Reservierung {{ rental_id }}',
                EmailTemplate::body_html => $this->getRentalEndingReminderHtml(),
                EmailTemplate::body_text => $this->getRentalEndingReminderText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
                EmailTemplate::description => 'Erinnerung an bevorstehendes Mietende',
                EmailTemplate::available_variables => [
                    'customer_name', 'rental_id', 'rental_end', 'board_name', 'location_name'
                ],
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Rechnung',
                EmailTemplate::slug => 'invoice',
                EmailTemplate::subject => 'Ihre Rechnung - Reservierung {{ rental_id }}',
                EmailTemplate::body_html => $this->getInvoiceHtml(),
                EmailTemplate::body_text => $this->getInvoiceText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
                EmailTemplate::description => 'Rechnung für abgeschlossene Vermietungen',
                EmailTemplate::available_variables => [
                    'customer_name', 'rental_id', 'total_price', 'rental_start', 'rental_end'
                ],
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Willkommens-E-Mail',
                EmailTemplate::slug => 'welcome',
                EmailTemplate::subject => 'Willkommen bei {{ company_name }}',
                EmailTemplate::body_html => $this->getWelcomeHtml(),
                EmailTemplate::body_text => $this->getWelcomeText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_SYSTEM,
                EmailTemplate::description => 'Willkommensnachricht für neue Kunden',
                EmailTemplate::available_variables => [
                    'customer_name', 'company_name', 'company_email', 'company_phone'
                ],
                EmailTemplate::is_active => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::create($template);
        }
    }

    private function getRentalConfirmationHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Buchungsbestätigung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Buchungsbestätigung</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>vielen Dank für Ihre Buchung! Wir freuen uns, Ihnen mitteilen zu können, dass Ihre Reservierung erfolgreich bestätigt wurde.</p>
        
        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Buchungsdetails</h3>
            <p><strong>Reservierungs-Nr.:</strong> {{ rental_id }}</p>
            <p><strong>Tafel:</strong> {{ board_name }}</p>
            <p><strong>Standort:</strong> {{ location_name }}</p>
            <p><strong>Mietzeitraum:</strong> {{ rental_start }} bis {{ rental_end }}</p>
            <p><strong>Gesamtpreis:</strong> {{ total_price }} €</p>
        </div>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getRentalConfirmationText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

vielen Dank für Ihre Buchung! Wir freuen uns, Ihnen mitteilen zu können, dass Ihre Reservierung erfolgreich bestätigt wurde.

Buchungsdetails:
- Reservierungs-Nr.: {{ rental_id }}
- Tafel: {{ board_name }}
- Standort: {{ location_name }}
- Mietzeitraum: {{ rental_start }} bis {{ rental_end }}
- Gesamtpreis: {{ total_price }} €

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen,
Ihr Team
TEXT;
    }

    private function getInquiryReceivedHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Ihre Anfrage ist eingegangen</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>vielen Dank für Ihre Anfrage! Wir haben diese erhalten und werden uns schnellstmöglich bei Ihnen melden.</p>
        <p>In der Regel antworten wir innerhalb von 24 Stunden.</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getInquiryReceivedText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

vielen Dank für Ihre Anfrage! Wir haben diese erhalten und werden uns schnellstmöglich bei Ihnen melden.

In der Regel antworten wir innerhalb von 24 Stunden.

Mit freundlichen Grüßen,
Ihr Team
TEXT;
    }

    private function getRentalEndingReminderHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Ihre Mietzeit endet bald</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>wir möchten Sie daran erinnern, dass Ihre Mietzeit für die Tafel <strong>{{ board_name }}</strong> am Standort <strong>{{ location_name }}</strong> bald endet.</p>
        <p><strong>Mietende:</strong> {{ rental_end }}</p>
        <p>Falls Sie eine Verlängerung wünschen, kontaktieren Sie uns bitte rechtzeitig.</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getRentalEndingReminderText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

wir möchten Sie daran erinnern, dass Ihre Mietzeit für die Tafel {{ board_name }} am Standort {{ location_name }} bald endet.

Mietende: {{ rental_end }}

Falls Sie eine Verlängerung wünschen, kontaktieren Sie uns bitte rechtzeitig.

Mit freundlichen Grüßen,
Ihr Team
TEXT;
    }

    private function getInvoiceHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Ihre Rechnung</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>anbei erhalten Sie Ihre Rechnung für die Reservierung {{ rental_id }}.</p>
        
        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p><strong>Mietzeitraum:</strong> {{ rental_start }} bis {{ rental_end }}</p>
            <p><strong>Gesamtbetrag:</strong> {{ total_price }} €</p>
        </div>
        
        <p>Vielen Dank für Ihr Vertrauen!</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getInvoiceText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

anbei erhalten Sie Ihre Rechnung für die Reservierung {{ rental_id }}.

Mietzeitraum: {{ rental_start }} bis {{ rental_end }}
Gesamtbetrag: {{ total_price }} €

Vielen Dank für Ihr Vertrauen!

Mit freundlichen Grüßen,
Ihr Team
TEXT;
    }

    private function getWelcomeHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Willkommen bei {{ company_name }}!</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>herzlich willkommen! Wir freuen uns, Sie als Kunden begrüßen zu dürfen.</p>
        <p>Bei Fragen erreichen Sie uns unter:</p>
        <ul>
            <li>E-Mail: {{ company_email }}</li>
            <li>Telefon: {{ company_phone }}</li>
        </ul>
        <p>Wir freuen uns auf eine gute Zusammenarbeit!</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team von {{ company_name }}</p>
    </div>
</body>
</html>
HTML;
    }

    private function getWelcomeText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

herzlich willkommen! Wir freuen uns, Sie als Kunden begrüßen zu dürfen.

Bei Fragen erreichen Sie uns unter:
- E-Mail: {{ company_email }}
- Telefon: {{ company_phone }}

Wir freuen uns auf eine gute Zusammenarbeit!

Mit freundlichen Grüßen,
Ihr Team von {{ company_name }}
TEXT;
    }
}
