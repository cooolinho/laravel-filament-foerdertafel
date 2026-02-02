<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();
        $templates = EmailTemplate::all();

        if ($customers->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Bitte zuerst Customer und User Seeder ausführen!');
            return;
        }

        // Beispiel ausgehende E-Mails (Outbound)
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $user = $users->random();
            $template = $templates->isNotEmpty() ? $templates->random() : null;

            Email::create([
                Email::direction => Email::DIRECTION_OUTBOUND,
                Email::status => $this->getRandomOutboundStatus(),
                Email::from_email => 'info@foerdertafel.de',
                Email::from_name => 'Fördertafel Team',
                Email::to_email => $customer->email,
                Email::to_name => $customer->name,
                Email::subject => $this->getRandomSubject(),
                Email::body_text => 'Dies ist eine Test-E-Mail im Text-Format.',
                Email::body_html => '<p>Dies ist eine <strong>Test-E-Mail</strong> im HTML-Format.</p>',
                Email::email_template_id => $template?->id,
                Email::customer_id => $customer->id,
                Email::user_id => $user->id,
                Email::sent_at => now()->subDays(rand(0, 30)),
                Email::metadata => [
                    'client' => 'Filament Admin',
                    'ip_address' => '127.0.0.1',
                ],
            ]);
        }

        // Beispiel eingehende E-Mails (Inbound)
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $isRead = rand(0, 1) === 1;

            Email::create([
                Email::direction => Email::DIRECTION_INBOUND,
                Email::status => Email::STATUS_RECEIVED,
                Email::from_email => $customer->email,
                Email::from_name => $customer->name,
                Email::to_email => 'info@foerdertafel.de',
                Email::to_name => 'Fördertafel Team',
                Email::subject => $this->getRandomInboundSubject(),
                Email::body_text => 'Dies ist eine eingehende Test-E-Mail von einem Kunden.',
                Email::body_html => '<p>Dies ist eine eingehende <strong>Test-E-Mail</strong> von einem Kunden.</p>',
                Email::customer_id => $customer->id,
                Email::received_at => now()->subDays(rand(0, 30)),
                Email::read_at => $isRead ? now()->subDays(rand(0, 15)) : null,
                Email::metadata => [
                    'spam_score' => 0.1,
                    'authenticated' => true,
                ],
            ]);
        }

        // Ein paar Entwürfe
        for ($i = 0; $i < 3; $i++) {
            $customer = $customers->random();
            $user = $users->random();

            Email::create([
                Email::direction => Email::DIRECTION_OUTBOUND,
                Email::status => Email::STATUS_DRAFT,
                Email::from_email => 'info@foerdertafel.de',
                Email::from_name => 'Fördertafel Team',
                Email::to_email => $customer->email,
                Email::to_name => $customer->name,
                Email::subject => 'Entwurf: ' . $this->getRandomSubject(),
                Email::body_text => 'Dies ist ein Entwurf...',
                Email::body_html => '<p>Dies ist ein <strong>Entwurf</strong>...</p>',
                Email::customer_id => $customer->id,
                Email::user_id => $user->id,
            ]);
        }

        // Eine fehlgeschlagene E-Mail
        $customer = $customers->random();
        $user = $users->random();

        Email::create([
            Email::direction => Email::DIRECTION_OUTBOUND,
            Email::status => Email::STATUS_FAILED,
            Email::from_email => 'info@foerdertafel.de',
            Email::from_name => 'Fördertafel Team',
            Email::to_email => 'invalid@example.com',
            Email::to_name => 'Test User',
            Email::subject => 'Test E-Mail',
            Email::body_text => 'Diese E-Mail konnte nicht zugestellt werden.',
            Email::body_html => '<p>Diese E-Mail konnte nicht zugestellt werden.</p>',
            Email::user_id => $user->id,
            Email::error_message => 'SMTP Error: Could not connect to mail server',
        ]);
    }

    private function getRandomOutboundStatus(): string
    {
        $statuses = [Email::STATUS_SENT, Email::STATUS_SENT, Email::STATUS_SENT, Email::STATUS_READ];
        return $statuses[array_rand($statuses)];
    }

    private function getRandomSubject(): string
    {
        $subjects = [
            'Ihre Buchungsbestätigung',
            'Wichtige Information zu Ihrer Reservierung',
            'Ihre Rechnung ist verfügbar',
            'Erinnerung: Mietzeit endet bald',
            'Vielen Dank für Ihre Buchung',
            'Aktualisierung zu Ihrer Anfrage',
            'Neues Angebot für Sie',
        ];

        return $subjects[array_rand($subjects)];
    }

    private function getRandomInboundSubject(): string
    {
        $subjects = [
            'Anfrage zur Verfügbarkeit',
            'Frage zu meiner Buchung',
            'Stornierungsanfrage',
            'Verlängerung der Mietzeit',
            'Problem mit der Tafel',
            'Allgemeine Frage',
            'Rückfrage zu Rechnung',
        ];

        return $subjects[array_rand($subjects)];
    }
}
