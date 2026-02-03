<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class RentalAccessCodeEmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        EmailTemplate::updateOrCreate(
            [
                EmailTemplate::slug => 'rental-access-code',
            ],
            [
                EmailTemplate::name => 'Zugangscode für Feldverwaltung',
                EmailTemplate::description => 'Diese E-Mail wird an Kunden gesendet, wenn ihre Miete bezahlt wurde und sie Zugriff auf die Feldverwaltung erhalten.',
                EmailTemplate::subject => 'Ihr Zugangscode für die Feldverwaltung - Vermietung #{{rental_id}}',
                EmailTemplate::body_html => $this->getHtmlBody(),
                EmailTemplate::body_text => $this->getTextBody(),
                EmailTemplate::is_active => true,
            ]
        );
    }

    /**
     * Get the HTML body for the email template.
     */
    private function getHtmlBody(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ihr Zugangscode</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .access-code {
            background-color: #fff;
            border: 2px dashed #4CAF50;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #4CAF50;
        }
        .button {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background-color: #e8f5e9;
            border-left: 4px solid #4CAF50;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Ihre Zahlung ist eingegangen!</h1>
    </div>
    
    <div class="content">
        <p>Hallo {{customer_name}},</p>
        
        <p>vielen Dank für Ihre Zahlung! Ihre Miete wurde erfolgreich bestätigt.</p>
        
        <div class="info-box">
            <strong>Mietdetails:</strong><br>
            Vermietungs-ID: #{{rental_id}}<br>
            Zeitraum: {{start_date}} bis {{end_date}}<br>
            Anzahl Felder: {{fields_count}}<br>
            Felder: {{fields_list}}
        </div>
        
        <h2>Ihr persönlicher Zugangscode</h2>
        
        <p>Mit diesem Code können Sie Ihre gemieteten Felder verwalten und mit Inhalten füllen:</p>
        
        <div class="access-code">
            {{access_code}}
        </div>
        
        <p style="text-align: center;">
            <a href="{{access_url}}" class="button">Jetzt Felder verwalten</a>
        </p>
        
        <h3>Was können Sie tun?</h3>
        <ul>
            <li>Titel und Beschreibung für Ihre Felder eingeben</li>
            <li>Kontaktinformationen hinterlegen</li>
            <li>Website-URL angeben</li>
            <li>Firmenlogo hochladen (falls Sie als Firma gemeldet sind)</li>
            <li>Inhalte veröffentlichen oder als Entwurf speichern</li>
        </ul>
        
        <p><strong>Wichtig:</strong> Bewahren Sie diesen Zugangscode gut auf! Sie benötigen ihn, um Ihre Felder zu verwalten.</p>
        
        <p>Bei Fragen stehen wir Ihnen gerne zur Verfügung.</p>
        
        <p>Mit freundlichen Grüßen<br>
        Ihr Team</p>
    </div>
    
    <div class="footer">
        <p>Diese E-Mail wurde automatisch generiert. Bitte antworten Sie nicht direkt auf diese E-Mail.</p>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Get the plain text body for the email template.
     */
    private function getTextBody(): string
    {
        return <<<'TEXT'
Hallo {{customer_name}},

vielen Dank für Ihre Zahlung! Ihre Miete wurde erfolgreich bestätigt.

MIETDETAILS:
Vermietungs-ID: #{{rental_id}}
Zeitraum: {{start_date}} bis {{end_date}}
Anzahl Felder: {{fields_count}}
Felder: {{fields_list}}

IHR PERSÖNLICHER ZUGANGSCODE:
{{access_code}}

Mit diesem Code können Sie Ihre gemieteten Felder verwalten:
{{access_url}}

WAS KÖNNEN SIE TUN?
- Titel und Beschreibung für Ihre Felder eingeben
- Kontaktinformationen hinterlegen
- Website-URL angeben
- Firmenlogo hochladen (falls Sie als Firma gemeldet sind)
- Inhalte veröffentlichen oder als Entwurf speichern

WICHTIG: Bewahren Sie diesen Zugangscode gut auf! Sie benötigen ihn, um Ihre Felder zu verwalten.

Bei Fragen stehen wir Ihnen gerne zur Verfügung.

Mit freundlichen Grüßen
Ihr Team

---
Diese E-Mail wurde automatisch generiert.
TEXT;
    }
}
