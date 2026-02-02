# E-Mail-Versand - Schnellstart-Anleitung

## ✅ Was wurde erstellt?

- **SendEmailJob**: Queue-Job zum asynchronen E-Mail-Versand
- **Email->send()**: Helper-Methode im Model
- **Outbox-Actions**: "Senden" und "Erneut senden" Buttons
- **Fehlerbehandlung**: Automatische Retries und Logging

## 🚀 Schnellstart in 3 Schritten

### Schritt 1: Queue-Tabellen erstellen

```bash
cd D:\Projekte\laravel-filament-foerdertafel\laravel
php artisan queue:table
php artisan migrate
```

### Schritt 2: E-Mail-Provider konfigurieren

**Für Tests (empfohlen):**

1. Registrieren Sie sich bei [Mailtrap.io](https://mailtrap.io)
2. Öffnen Sie `.env` und fügen Sie hinzu:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=ihr_mailtrap_username
MAIL_PASSWORD=ihr_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@foerdertafel.de"
MAIL_FROM_NAME="Fördertafel"

QUEUE_CONNECTION=database
```

### Schritt 3: Queue-Worker starten

```bash
php artisan queue:work
```

**Fertig!** Sie können jetzt E-Mails versenden.

## 📧 E-Mail versenden

### Über Filament UI

1. Gehen Sie zu **Postausgang**
2. Klicken Sie auf **"Neue E-Mail"**
3. Füllen Sie das Formular aus
4. Speichern Sie als Entwurf
5. Klicken Sie auf **"Senden"** ⚡
6. Die E-Mail wird in die Warteschlange eingereiht

### Über Code

```php
use App\Models\Email;

// E-Mail erstellen
$email = Email::create([
    Email::direction => Email::DIRECTION_OUTBOUND,
    Email::status => Email::STATUS_DRAFT,
    Email::from_email => 'info@foerdertafel.de',
    Email::from_name => 'Fördertafel Team',
    Email::to_email => 'kunde@example.com',
    Email::to_name => 'Max Mustermann',
    Email::subject => 'Test-E-Mail',
    Email::body_html => '<p>Hallo <strong>Max</strong>!</p>',
    Email::body_text => 'Hallo Max!',
]);

// Senden
$email->send(); // 🚀 Dispatcht den SendEmailJob
```

### Mit E-Mail-Vorlage

```php
use App\Models\Email;
use App\Models\EmailTemplate;

$template = EmailTemplate::where('slug', 'rental-confirmation')->first();

$rendered = $template->render([
    'customer_name' => 'Max Mustermann',
    'rental_id' => '12345',
    // ... weitere Variablen
]);

$email = Email::create([
    Email::direction => Email::DIRECTION_OUTBOUND,
    Email::status => Email::STATUS_DRAFT,
    Email::from_email => 'info@foerdertafel.de',
    Email::to_email => 'kunde@example.com',
    Email::to_name => 'Max Mustermann',
    Email::subject => $rendered['subject'],
    Email::body_html => $rendered['body_html'],
    Email::body_text => $rendered['body_text'],
    Email::email_template_id => $template->id,
]);

$email->send();
```

## 🔍 E-Mails überwachen

### In Filament

- **Postausgang**: Alle ausgehenden E-Mails
- **Filter**: Status (Entwurf, Gesendet, Fehlgeschlagen)
- **Actions**: 
  - ✅ "Senden" für Entwürfe
  - 🔄 "Erneut senden" für fehlgeschlagene E-Mails
  - 📋 "Duplizieren" zum Wiederverwenden

### In Mailtrap

1. Öffnen Sie [Mailtrap.io](https://mailtrap.io)
2. Gehen Sie zu Ihrer Inbox
3. Alle gesendeten E-Mails erscheinen hier
4. Sie können HTML, Text-Version, Headers etc. prüfen

### In Logs

```bash
# Live-Ansicht der Logs
tail -f storage/logs/laravel.log

# Nach E-Mail-Logs suchen
grep "E-Mail" storage/logs/laravel.log
```

## 🛠️ Troubleshooting

### Problem: "Connection refused"

**Lösung**: Queue-Worker läuft nicht
```bash
php artisan queue:work
```

### Problem: E-Mails werden nicht versendet

**Prüfen Sie:**
1. Ist der Queue-Worker gestartet? → `ps aux | grep queue:work`
2. Sind die MAIL_* Variablen in `.env` korrekt?
3. Gibt es Fehler in `storage/logs/laravel.log`?

```bash
# Queue-Status prüfen
php artisan queue:monitor

# Failed Jobs anzeigen
php artisan queue:failed

# Failed Job erneut versuchen
php artisan queue:retry all
```

### Problem: E-Mails landen im Spam

**Für Produktion:**
- Konfigurieren Sie SPF, DKIM, DMARC Records
- Verwenden Sie einen professionellen Provider (SendGrid, Mailgun)
- Authentifizieren Sie Ihre Domain

## 🎯 Nächste Schritte

### Für Produktion

1. **Redis-Queue einrichten** (empfohlen):
```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

2. **Supervisor konfigurieren** (Worker läuft dauerhaft):
```bash
sudo apt-get install supervisor
```

Erstellen Sie `/etc/supervisor/conf.d/laravel-worker.conf`:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /pfad/zu/laravel/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/pfad/zu/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

3. **E-Mail-Provider wechseln**:
   - Mailtrap → SendGrid/Mailgun/SES
   - Siehe `.env.email.example` für Beispiele

4. **Monitoring einrichten**:
   - Laravel Horizon (für Redis Queue)
   - Logs überwachen
   - Alerts für fehlgeschlagene E-Mails

## 📚 Weitere Dokumentation

- **SEND_EMAIL_JOB.md**: Vollständige Job-Dokumentation
- **EMAIL_SYSTEM.md**: Komplette System-Übersicht
- **.env.email.example**: Beispiel-Konfigurationen für verschiedene Provider

## ✨ Features

- ✅ Asynchroner Versand über Queue
- ✅ Automatische Retries (3x)
- ✅ Fehlerbehandlung und Logging
- ✅ HTML und Text-Version
- ✅ CC, BCC, Reply-To Support
- ✅ Anhänge-Unterstützung
- ✅ E-Mail-Threading (Message-ID, In-Reply-To)
- ✅ E-Mail-Vorlagen mit Platzhaltern
- ✅ Status-Tracking
- ✅ Filament UI-Integration

## 🎉 Fertig!

Sie können jetzt E-Mails versenden! Testen Sie es:

1. Starten Sie den Queue-Worker: `php artisan queue:work`
2. Gehen Sie zu **Postausgang** → **Neue E-Mail**
3. Erstellen Sie eine Test-E-Mail
4. Klicken Sie auf **Senden**
5. Prüfen Sie Mailtrap für die empfangene E-Mail

Bei Fragen oder Problemen: Siehe **SEND_EMAIL_JOB.md** für Details!
