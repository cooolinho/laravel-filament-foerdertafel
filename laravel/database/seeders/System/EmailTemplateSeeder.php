<?php

namespace Database\Seeders\System;

use App\Listeners\SendAccessCodeEmail;
use App\Listeners\SendInquiryConfirmationEmail;
use App\Listeners\SendInquiryNotificationEmail;
use App\Listeners\SendRentalConfirmationEmail;
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
                EmailTemplate::available_variables => SendRentalConfirmationEmail::$defaultVariables,
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Anfrage-Bestätigung',
                EmailTemplate::slug => 'inquiry-confirmation',
                EmailTemplate::subject => 'Ihre Anfrage ist bei uns eingegangen - Anfrage #{{ inquiry_id }}',
                EmailTemplate::body_html => $this->getInquiryConfirmationHtml(),
                EmailTemplate::body_text => $this->getInquiryConfirmationText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_INQUIRY,
                EmailTemplate::description => 'Bestätigung für eingegangene Anfragen mit allen Anfragedetails',
                EmailTemplate::available_variables => SendInquiryConfirmationEmail::$defaultVariables,
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Zugangscode',
                EmailTemplate::slug => 'rental-access-code',
                EmailTemplate::subject => 'Ihr Zugangscode - Reservierung #{{ rental_id }}',
                EmailTemplate::body_html => $this->getRentalAccessCodeHtml(),
                EmailTemplate::body_text => $this->getRentalAccessCodeText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
                EmailTemplate::description => 'Wird nach Zahlungseingang mit dem Zugangscode für den Kundenbereich versendet',
                EmailTemplate::available_variables => SendAccessCodeEmail::$defaultVariables,
                EmailTemplate::is_active => true,
            ],
            [
                EmailTemplate::name => 'Anfrage-Benachrichtigung (intern)',
                EmailTemplate::slug => 'inquiry-notification',
                EmailTemplate::subject => 'Neue Anfrage eingegangen - #{{ inquiry_id }} von {{ customer_name }}',
                EmailTemplate::body_html => $this->getInquiryNotificationHtml(),
                EmailTemplate::body_text => $this->getInquiryNotificationText(),
                EmailTemplate::category => EmailTemplate::CATEGORY_INQUIRY,
                EmailTemplate::description => 'Interne Benachrichtigung bei neuer Anfrage – wird an die in den Einstellungen hinterlegten Admin-E-Mail-Adressen gesendet',
                EmailTemplate::available_variables => SendInquiryNotificationEmail::$defaultVariables,
                EmailTemplate::is_active => true,
            ],

            // Unbenutzt - kommt später zum Einsatz
//            [
//                EmailTemplate::name => 'Mietende Erinnerung',
//                EmailTemplate::slug => 'rental-ending-reminder',
//                EmailTemplate::subject => 'Ihre Mietzeit endet bald - Reservierung {{ rental_id }}',
//                EmailTemplate::body_html => $this->getRentalEndingReminderHtml(),
//                EmailTemplate::body_text => $this->getRentalEndingReminderText(),
//                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
//                EmailTemplate::description => 'Erinnerung an bevorstehendes Mietende',
//                EmailTemplate::available_variables => [
//                    'customer_name', 'rental_id', 'rental_end', 'board_name', 'location_name'
//                ],
//                EmailTemplate::is_active => true,
//            ],
//            [
//                EmailTemplate::name => 'Rechnung',
//                EmailTemplate::slug => 'invoice',
//                EmailTemplate::subject => 'Ihre Rechnung - Reservierung {{ rental_id }}',
//                EmailTemplate::body_html => $this->getInvoiceHtml(),
//                EmailTemplate::body_text => $this->getInvoiceText(),
//                EmailTemplate::category => EmailTemplate::CATEGORY_RENTAL,
//                EmailTemplate::description => 'Rechnung für abgeschlossene Vermietungen',
//                EmailTemplate::available_variables => [
//                    'customer_name', 'rental_id', 'total_price', 'rental_start', 'rental_end'
//                ],
//                EmailTemplate::is_active => true,
//            ],
//            [
//                EmailTemplate::name => 'Willkommens-E-Mail',
//                EmailTemplate::slug => 'welcome',
//                EmailTemplate::subject => 'Willkommen bei {{ company_name }}',
//                EmailTemplate::body_html => $this->getWelcomeHtml(),
//                EmailTemplate::body_text => $this->getWelcomeText(),
//                EmailTemplate::category => EmailTemplate::CATEGORY_SYSTEM,
//                EmailTemplate::description => 'Willkommensnachricht für neue Kunden',
//                EmailTemplate::available_variables => [
//                    'customer_name', 'company_name', 'company_email', 'company_phone'
//                ],
//                EmailTemplate::is_active => true,
//            ],
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

    private function getInquiryConfirmationHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Anfrage-Bestätigung</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Ihre Anfrage ist eingegangen</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>vielen Dank für Ihre Anfrage! Wir haben diese erhalten und werden uns schnellstmöglich bei Ihnen melden.</p>

        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Ihre Anfragedetails</h3>
            <p><strong>Anfrage-Nr.:</strong> {{ inquiry_id }}</p>
            <p><strong>Datum:</strong> {{ inquiry_date }}</p>
            <p><strong>Gewünschter Zeitraum:</strong> {{ start_date }} bis {{ end_date }} ({{ rental_months }} Monat(e))</p>
            <p><strong>Anzahl Felder:</strong> {{ field_count }}</p>
            <p><strong>Gesamtpreis:</strong> {{ total_price }} €</p>
            <p><strong>Tafel:</strong> {{ board_name }}</p>
            <p><strong>Standort:</strong> {{ location_name }}</p>
        </div>

        <div style="background-color: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Ihre Kontaktdaten</h3>
            <p><strong>Name:</strong> {{ customer_name }}</p>
            <p><strong>E-Mail:</strong> {{ customer_email }}</p>
            <p><strong>Telefon:</strong> {{ customer_phone }}</p>
            <p><strong>Adresse:</strong> {{ address }}</p>
        </div>

        {{ message }}

        <p>In der Regel antworten wir innerhalb von 24 Stunden auf Ihre Anfrage.</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getInquiryConfirmationText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

vielen Dank für Ihre Anfrage! Wir haben diese erhalten und werden uns schnellstmöglich bei Ihnen melden.

Ihre Anfragedetails:
- Anfrage-Nr.: {{ inquiry_id }}
- Datum: {{ inquiry_date }}
- Gewünschter Zeitraum: {{ start_date }} bis {{ end_date }} ({{ rental_months }} Monat(e))
- Anzahl Felder: {{ field_count }}
- Gesamtpreis: {{ total_price }}
- Tafel: {{ board_name }}
- Standort: {{ location_name }}

Ihre Kontaktdaten:
- Name: {{ customer_name }}
- E-Mail: {{ customer_email }}
- Telefon: {{ customer_phone }}
- Adresse: {{ address }}

{{ message }}

In der Regel antworten wir innerhalb von 24 Stunden auf Ihre Anfrage.

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

    private function getRentalAccessCodeHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ihr Zugangscode</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Ihr Zugangscode ist bereit</h2>
        <p>Hallo {{ customer_name }},</p>
        <p>vielen Dank für Ihre Zahlung! Ihr Zugangscode für den Kundenbereich steht Ihnen nun zur Verfügung.</p>

        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <p style="margin: 0 0 8px; font-size: 14px; color: #6b7280;">Ihr persönlicher Zugangscode</p>
            <p style="margin: 0; font-size: 32px; font-weight: bold; letter-spacing: 4px; color: #1d4ed8;">{{ access_code }}</p>
        </div>

        <div style="background-color: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Ihre Mietdetails</h3>
            <p><strong>Reservierungs-Nr.:</strong> {{ rental_id }}</p>
            <p><strong>Mietzeitraum:</strong> {{ start_date }} bis {{ end_date }}</p>
            <p><strong>Anzahl Felder:</strong> {{ fields_count }}</p>
            <p><strong>Felder:</strong> {{ fields_list }}</p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ access_url }}"
               style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                Zum Kundenbereich
            </a>
        </p>

        <p>Oder rufen Sie diesen Link direkt auf:<br>
            <a href="{{ access_url }}">{{ access_url }}</a>
        </p>

        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        <p>Mit freundlichen Grüßen,<br>Ihr Team</p>
    </div>
</body>
</html>
HTML;
    }

    private function getRentalAccessCodeText(): string
    {
        return <<<TEXT
Hallo {{ customer_name }},

vielen Dank für Ihre Zahlung! Ihr Zugangscode für den Kundenbereich steht Ihnen nun zur Verfügung.

Ihr Zugangscode: {{ access_code }}

Ihre Mietdetails:
- Reservierungs-Nr.: {{ rental_id }}
- Mietzeitraum: {{ start_date }} bis {{ end_date }}
- Anzahl Felder: {{ fields_count }}
- Felder: {{ fields_list }}

Zum Kundenbereich: {{ access_url }}

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

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

    private function getInquiryNotificationHtml(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Neue Anfrage eingegangen</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #2563eb;">Neue Anfrage eingegangen</h2>
        <p>Es ist eine neue Anfrage über das Kundenportal eingegangen.</p>

        <div style="background-color: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Antragsteller</h3>
            <p><strong>Name:</strong> {{ customer_name }}</p>
            <p><strong>E-Mail:</strong> {{ customer_email }}</p>
            <p><strong>Telefon:</strong> {{ customer_phone }}</p>
            <p><strong>Adresse:</strong> {{ address }}</p>
        </div>

        <div style="background-color: #eff6ff; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Anfragedetails</h3>
            <p><strong>Anfrage-Nr.:</strong> {{ inquiry_id }}</p>
            <p><strong>Datum:</strong> {{ inquiry_date }}</p>
            <p><strong>Zeitraum:</strong> {{ start_date }} – {{ end_date }} ({{ rental_months }} Monat(e))</p>
            <p><strong>Anzahl Felder:</strong> {{ field_count }}</p>
            <p><strong>Tafel:</strong> {{ board_name }}</p>
            <p><strong>Standort:</strong> {{ location_name }}</p>
        </div>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ admin_url }}"
               style="background-color: #2563eb; color: #ffffff; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold;">
                Anfrage im Admin-Bereich öffnen
            </a>
        </p>

        <p style="font-size: 13px; color: #6b7280;">
            Oder direkt über diesen Link:<br>
            <a href="{{ admin_url }}">{{ admin_url }}</a>
        </p>
    </div>
</body>
</html>
HTML;
    }

    private function getInquiryNotificationText(): string
    {
        return <<<TEXT
Neue Anfrage eingegangen

Es ist eine neue Anfrage über das Kundenportal eingegangen.

Antragsteller:
- Name: {{ customer_name }}
- E-Mail: {{ customer_email }}
- Telefon: {{ customer_phone }}
- Adresse: {{ address }}

Anfragedetails:
- Anfrage-Nr.: {{ inquiry_id }}
- Datum: {{ inquiry_date }}
- Zeitraum: {{ start_date }} – {{ end_date }} ({{ rental_months }} Monat(e))
- Anzahl Felder: {{ field_count }}
- Tafel: {{ board_name }}
- Standort: {{ location_name }}

Anfrage im Admin-Bereich öffnen:
{{ admin_url }}
TEXT;
    }
}
