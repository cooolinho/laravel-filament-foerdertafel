# SendEmailJob Dokumentation

## Überblick

Der `SendEmailJob` ist ein Queue-Job, der für das asynchrone Versenden von E-Mails zuständig ist. Er verarbeitet `Email`-Model-Instanzen und sendet diese über Laravel's Mail-System.

## Features

### ✅ Implementierte Funktionen

- **Asynchrones Versenden**: E-Mails werden über die Queue versendet
- **Retry-Mechanismus**: 3 Versuche mit 60 Sekunden Wartezeit zwischen Versuchen
- **Fehlerbehandlung**: Automatisches Logging und Status-Update bei Fehlern
- **Vollständige E-Mail-Unterstützung**:
  - From, To, CC, BCC
  - Reply-To
  - HTML und Text-Body
  - Custom Headers
  - Anhänge
  - Message-ID für E-Mail-Threading
  - In-Reply-To und References für Konversationen

### 🔧 Konfiguration

**Job-Eigenschaften:**
- `$tries = 3`: Maximale Anzahl der Versendeversuche
- `$backoff = 60`: Wartezeit zwischen Versuchen in Sekunden

## Verwendung

### Option 1: Job direkt dispatchen

```php
use App\Jobs\SendEmailJob;
use App\Models\Email;

$email = Email::find(1);
SendEmailJob::dispatch($email);
```

### Option 2: Model-Helper-Methode

```php
use App\Models\Email;

$email = Email::find(1);
$email->send(); // Dispatcht automatisch den SendEmailJob
```

### Option 3: Sofort versenden (ohne Queue)

```php
use App\Jobs\SendEmailJob;
use App\Models\Email;

$email = Email::find(1);
SendEmailJob::dispatchSync($email); // Blockierend, für Tests
```

### Option 4: Mit Verzögerung versenden

```php
use App\Jobs\SendEmailJob;
use App\Models\Email;

$email = Email::find(1);
SendEmailJob::dispatch($email)
    ->delay(now()->addMinutes(10)); // In 10 Minuten versenden
```

## Workflow

### 1. E-Mail wird in die Queue eingereiht

```php
// Status wird auf SENT gesetzt (in Warteschlange)
$email->update([Email::status => Email::STATUS_SENT]);
SendEmailJob::dispatch($email);
```

### 2. Job wird ausgeführt

Der Job:
1. Prüft, ob E-Mail bereits gesendet wurde
2. Baut die Mail-Message mit allen Details auf
3. Sendet die E-Mail über Laravel Mail
4. Aktualisiert den Status und `sent_at` Zeitstempel
5. Generiert und speichert eine Message-ID

### 3. Bei Erfolg

```php
$email->status = Email::STATUS_SENT;
$email->sent_at = now();
$email->message_id = '<generated-id@domain.com>';
$email->error_message = null;
```

### 4. Bei Fehler

**Erste Versuche (1-2):**
- Exception wird geloggt
- Status bleibt SENT (oder wird auf FAILED gesetzt)
- Job wird nach 60 Sekunden erneut versucht

**Nach 3 Versuchen:**
- `failed()` Methode wird aufgerufen
- Status wird auf FAILED gesetzt
- Fehlermeldung wird gespeichert
- Admin wird benachrichtigt (via Log)

## E-Mail-Provider konfigurieren

### Schritt 1: `.env` konfigurieren

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # Für Tests
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@foerdertafel.de
MAIL_FROM_NAME="${APP_NAME}"
```

### Schritt 2: Provider-spezifische Konfiguration

#### Option A: Mailtrap (für Tests)
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

#### Option B: SendGrid
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

#### Option C: Mailgun
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.foerdertafel.de
MAILGUN_SECRET=your_mailgun_secret
MAILGUN_ENDPOINT=api.eu.mailgun.net  # Oder api.mailgun.net für US
```

#### Option D: Amazon SES
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_DEFAULT_REGION=eu-central-1
```

## Queue-Setup

### Schritt 1: Queue-Treiber konfigurieren

**Für Entwicklung (`.env`):**
```env
QUEUE_CONNECTION=database
```

**Für Produktion (empfohlen):**
```env
QUEUE_CONNECTION=redis
```

### Schritt 2: Queue-Tabellen erstellen (für database driver)

```bash
php artisan queue:table
php artisan migrate
```

### Schritt 3: Queue-Worker starten

**Für Entwicklung:**
```bash
php artisan queue:work --tries=3 --timeout=90
```

**Für Produktion (mit Supervisor):**
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /pfad/zu/laravel/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/pfad/zu/logs/worker.log
stopwaitsecs=3600
```

## Logging

Der Job loggt alle wichtigen Events:

**Erfolgreicher Versand:**
```
INFO: E-Mail {id} erfolgreich gesendet an {email}
```

**Fehler beim Versenden:**
```
ERROR: Fehler beim Versenden von E-Mail {id}: {message}
Context: {exception, email_id, to_email}
```

**Endgültiges Fehlschlagen:**
```
ERROR: E-Mail {id} endgültig fehlgeschlagen
Context: {exception, email_id, attempts}
```

## Beispiel: Automatischer E-Mail-Versand

### Observer für automatische E-Mails

```php
// app/Observers/RentalObserver.php
class RentalObserver
{
    public function created(Rental $rental)
    {
        $template = EmailTemplate::where('slug', 'rental-confirmation')->first();
        
        if (!$template) {
            return;
        }
        
        $rendered = $template->render([
            'customer_name' => $rental->customer->name,
            'rental_id' => $rental->id,
            'rental_start' => $rental->start_date->format('d.m.Y'),
            'rental_end' => $rental->end_date->format('d.m.Y'),
            'board_name' => $rental->field->board->name,
            'location_name' => $rental->field->board->location->name,
            'total_price' => number_format($rental->total_price, 2, ',', '.'),
        ]);
        
        $email = Email::create([
            Email::direction => Email::DIRECTION_OUTBOUND,
            Email::status => Email::STATUS_DRAFT,
            Email::from_email => 'info@foerdertafel.de',
            Email::from_name => 'Fördertafel Team',
            Email::to_email => $rental->customer->email,
            Email::to_name => $rental->customer->name,
            Email::subject => $rendered['subject'],
            Email::body_html => $rendered['body_html'],
            Email::body_text => $rendered['body_text'],
            Email::email_template_id => $template->id,
            Email::customer_id => $rental->customer_id,
            Email::rental_id => $rental->id,
            Email::user_id => auth()->id(),
        ]);
        
        // E-Mail versenden
        $email->send();
    }
}
```

## Testing

### Test mit Mailtrap

1. Registrieren Sie sich bei [Mailtrap.io](https://mailtrap.io)
2. Erstellen Sie ein Inbox
3. Kopieren Sie die SMTP-Credentials in Ihre `.env`
4. Alle E-Mails werden in Mailtrap abgefangen

### Unit Test

```php
// tests/Unit/SendEmailJobTest.php
use App\Jobs\SendEmailJob;
use App\Models\Email;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendEmailJobTest extends TestCase
{
    public function test_email_is_sent()
    {
        Mail::fake();
        
        $email = Email::factory()->create([
            'status' => Email::STATUS_DRAFT,
        ]);
        
        $job = new SendEmailJob($email);
        $job->handle();
        
        $this->assertEquals(Email::STATUS_SENT, $email->fresh()->status);
        $this->assertNotNull($email->fresh()->sent_at);
    }
}
```

## Monitoring

### Queue-Status überwachen

```bash
# Anzahl der Jobs in der Queue
php artisan queue:monitor

# Failed Jobs anzeigen
php artisan queue:failed

# Failed Job erneut versuchen
php artisan queue:retry {job-id}

# Alle Failed Jobs erneut versuchen
php artisan queue:retry all
```

### Filament-Integration

Im Dashboard können Sie E-Mails mit Status `FAILED` filtern und erneut versenden:

```php
// In Outbox.php oder EmailResource
Action::make('resend')
    ->label('Erneut senden')
    ->icon('heroicon-o-arrow-path')
    ->visible(fn (Email $record) => $record->status === Email::STATUS_FAILED)
    ->action(function (Email $record) {
        $record->update([
            Email::status => Email::STATUS_DRAFT,
            Email::error_message => null,
        ]);
        $record->send();
    })
    ->successNotificationTitle('E-Mail wird erneut gesendet');
```

## Tipps & Best Practices

1. **Verwenden Sie immer die Queue**: Synchrones Senden blockiert die Anwendung
2. **Testen Sie mit Mailtrap**: Vermeiden Sie versehentliches Versenden an echte E-Mail-Adressen
3. **Überwachen Sie Failed Jobs**: Richten Sie Alerts für fehlgeschlagene E-Mails ein
4. **Rate Limiting beachten**: Einige Provider limitieren E-Mails pro Stunde
5. **Backup Queue Driver**: Verwenden Sie Redis für Produktion
6. **Supervisor einrichten**: Stellen Sie sicher, dass Queue-Worker automatisch neu gestartet werden
7. **Logs überwachen**: Prüfen Sie regelmäßig auf E-Mail-Fehler

## Troubleshooting

### Problem: E-Mails werden nicht versendet

**Lösung:**
```bash
# Prüfen Sie, ob Queue-Worker läuft
ps aux | grep "queue:work"

# Starten Sie den Worker
php artisan queue:work
```

### Problem: E-Mails landen im Spam

**Lösung:**
- SPF-Record konfigurieren
- DKIM-Signatur einrichten
- DMARC-Policy setzen
- Authentifizierte SMTP-Verbindung verwenden

### Problem: Timeouts bei großen Anhängen

**Lösung:**
```php
// Erhöhen Sie das Job-Timeout
public $timeout = 300; // 5 Minuten
```

### Problem: Memory-Probleme bei vielen E-Mails

**Lösung:**
```bash
# Worker mit memory limit starten
php artisan queue:work --memory=512
```
