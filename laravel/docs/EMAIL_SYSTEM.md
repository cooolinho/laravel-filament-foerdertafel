# E-Mail-System Dokumentation

## Überblick

Das E-Mail-System ermöglicht das Versenden und Empfangen von E-Mails innerhalb der Fördertafel-Anwendung. Es unterstützt E-Mail-Vorlagen, automatische Variablen-Ersetzung und eine vollständige Verwaltung von Posteingang und Postausgang.

## Datenbankstruktur

### Tabelle: `emails`

Speichert alle ein- und ausgehenden E-Mails.

**Wichtige Felder:**
- `direction`: 'inbound' oder 'outbound'
- `status`: 'draft', 'sent', 'received', 'failed', 'read'
- `from_email`, `from_name`: Absenderinformationen
- `to_email`, `to_name`: Empfängerinformationen
- `cc`, `bcc`, `reply_to`: Zusätzliche E-Mail-Felder
- `subject`: Betreff
- `body_text`: Plain-Text Version
- `body_html`: HTML Version
- `attachments`: JSON-Array mit Anhängen
- `headers`: JSON-Array mit E-Mail-Headern
- `message_id`: Eindeutige Message-ID
- `in_reply_to`: Message-ID der E-Mail, auf die geantwortet wird
- `email_template_id`: Verknüpfung zur verwendeten Vorlage
- `customer_id`: Verknüpfung zum Kunden
- `rental_id`: Verknüpfung zur Vermietung
- `user_id`: Verknüpfung zum Benutzer
- `sent_at`, `received_at`, `read_at`: Zeitstempel
- `error_message`: Fehlermeldung bei fehlgeschlagenen E-Mails
- `metadata`: JSON für zusätzliche Daten

### Tabelle: `email_templates`

Speichert wiederverwendbare E-Mail-Vorlagen.

**Wichtige Felder:**
- `name`: Name der Vorlage
- `slug`: Eindeutiger Bezeichner
- `subject`: Betreff-Vorlage
- `body_text`: Plain-Text Vorlage
- `body_html`: HTML Vorlage
- `from_email`, `from_name`, `reply_to`: Standard-Absender
- `category`: Kategorisierung ('rental', 'inquiry', 'system', 'marketing')
- `available_variables`: JSON-Array mit dokumentierten Variablen
- `is_active`: Aktiv/Inaktiv Status

## Models

### Email Model (`App\Models\Email`)

**Konstanten:**
```php
// Directions
Email::DIRECTION_INBOUND = 'inbound'
Email::DIRECTION_OUTBOUND = 'outbound'

// Status
Email::STATUS_DRAFT = 'draft'
Email::STATUS_SENT = 'sent'
Email::STATUS_RECEIVED = 'received'
Email::STATUS_FAILED = 'failed'
Email::STATUS_READ = 'read'
```

**Scopes:**
```php
Email::inbound()    // Nur eingehende E-Mails
Email::outbound()   // Nur ausgehende E-Mails
Email::draft()      // Nur Entwürfe
Email::sent()       // Nur gesendete
Email::received()   // Nur empfangene
Email::unread()     // Nur ungelesene
Email::read()       // Nur gelesene
```

**Helper-Methoden:**
```php
$email->isInbound()     // Prüft ob eingehend
$email->isOutbound()    // Prüft ob ausgehend
$email->isDraft()       // Prüft ob Entwurf
$email->isSent()        // Prüft ob gesendet
$email->isRead()        // Prüft ob gelesen
$email->markAsRead()    // Markiert als gelesen
$email->markAsSent()    // Markiert als gesendet
```

**Beziehungen:**
```php
$email->emailTemplate  // E-Mail-Vorlage
$email->customer       // Kunde
$email->rental         // Vermietung
$email->user           // Benutzer
```

### EmailTemplate Model (`App\Models\EmailTemplate`)

**Konstanten:**
```php
EmailTemplate::CATEGORY_RENTAL = 'rental'
EmailTemplate::CATEGORY_INQUIRY = 'inquiry'
EmailTemplate::CATEGORY_SYSTEM = 'system'
EmailTemplate::CATEGORY_MARKETING = 'marketing'
```

**Scopes:**
```php
EmailTemplate::active()                    // Nur aktive Vorlagen
EmailTemplate::byCategory('rental')        // Nach Kategorie filtern
```

**Helper-Methoden:**
```php
$template->render(['customer_name' => 'Max Mustermann'])
// Gibt Array zurück:
// [
//     'subject' => 'Hallo Max Mustermann',
//     'body_text' => '...',
//     'body_html' => '...'
// ]
```

**Beziehungen:**
```php
$template->emails  // Alle E-Mails, die diese Vorlage verwendet haben
```

## Filament Resources

### EmailResource

- **Liste**: Zeigt alle E-Mails mit Filterung nach Richtung, Status, Kunde, etc.
- **Formular**: Umfassendes Formular zum Erstellen/Bearbeiten von E-Mails
- **Infolist**: Detaillierte Ansicht einer E-Mail
- **Badge**: Zeigt Anzahl ungelesener eingehender E-Mails

**Navigation:**
- Icon: `heroicon-o-envelope`
- Label: "E-Mails"
- Badge: Anzahl ungelesener E-Mails (rot)

### EmailTemplateResource

- **Liste**: Zeigt alle Vorlagen mit Filterung nach Kategorie und Status
- **Formular**: Erstellen/Bearbeiten von Vorlagen mit Platzhalter-Dokumentation
- **Infolist**: Detaillierte Vorschau der Vorlage
- **Actions**: Duplizieren-Funktion für Vorlagen

**Navigation:**
- Icon: `heroicon-o-document-text`
- Label: "E-Mail-Vorlagen"

## Custom Pages

### Inbox (Posteingang)

Spezielle Seite für eingehende E-Mails.

**Features:**
- Automatische Filterung auf `direction = 'inbound'`
- Ungelesene E-Mails werden hervorgehoben (fett)
- Badge zeigt Anzahl ungelesener E-Mails
- Actions: Ansehen, Als gelesen markieren, Antworten
- Bulk-Actions: Als gelesen markieren, Löschen
- Auto-Refresh alle 30 Sekunden

**Navigation:**
- Icon: `heroicon-o-inbox`
- Label: "Posteingang"
- Badge: Anzahl ungelesener E-Mails (rot)

### Outbox (Postausgang)

Spezielle Seite für ausgehende E-Mails.

**Features:**
- Automatische Filterung auf `direction = 'outbound'`
- Status-Badges (Entwurf, Gesendet, Fehlgeschlagen)
- Actions: Ansehen, Bearbeiten (nur Entwürfe), Senden, Duplizieren
- Empty-State mit "E-Mail erstellen" Button
- Badge zeigt Anzahl der Entwürfe

**Navigation:**
- Icon: `heroicon-o-paper-airplane`
- Label: "Postausgang"
- Badge: Anzahl Entwürfe (orange)

## E-Mail-Vorlagen

### Verfügbare Standard-Vorlagen

1. **Buchungsbestätigung** (`rental-confirmation`)
   - Kategorie: Vermietung
   - Verwendung: Nach erfolgreicher Buchung

2. **Anfrageeingang bestätigen** (`inquiry-received`)
   - Kategorie: Anfrage
   - Verwendung: Bestätigung eingegangener Anfragen

3. **Mietende Erinnerung** (`rental-ending-reminder`)
   - Kategorie: Vermietung
   - Verwendung: Erinnerung an bevorstehendes Mietende

4. **Rechnung** (`invoice`)
   - Kategorie: Vermietung
   - Verwendung: Rechnung für abgeschlossene Vermietungen

5. **Willkommens-E-Mail** (`welcome`)
   - Kategorie: System
   - Verwendung: Willkommensnachricht für neue Kunden

### Verfügbare Variablen

E-Mail-Vorlagen unterstützen folgende Platzhalter:

```
{{ customer_name }}     - Name des Kunden
{{ customer_email }}    - E-Mail des Kunden
{{ rental_id }}         - Vermietungs-ID
{{ rental_start }}      - Mietbeginn
{{ rental_end }}        - Mietende
{{ board_name }}        - Tafel-Name
{{ location_name }}     - Standort-Name
{{ total_price }}       - Gesamtpreis
{{ company_name }}      - Firmenname
{{ company_email }}     - Firmen-E-Mail
{{ company_phone }}     - Firmen-Telefon
```

**Verwendung in Vorlagen:**
```html
<p>Hallo {{ customer_name }},</p>
<p>Ihre Buchung für {{ board_name }} wurde bestätigt.</p>
```

## Seeder

### EmailTemplateSeeder

Erstellt 5 Standard-E-Mail-Vorlagen mit deutschen Texten.

### EmailSeeder

Erstellt Beispiel-E-Mails:
- 15 ausgehende E-Mails (verschiedene Status)
- 10 eingehende E-Mails (einige gelesen, einige ungelesen)
- 3 Entwürfe
- 1 fehlgeschlagene E-Mail

## Workflow

### E-Mail senden

1. Neue E-Mail erstellen über "E-Mails" → "Neu"
2. Optional: E-Mail-Vorlage auswählen
3. Empfänger auswählen (optional über Kunde)
4. Betreff und Nachricht eingeben
5. Als Entwurf speichern ODER
6. Status auf "Gesendet" setzen und `sent_at` Zeitstempel setzen

### E-Mail empfangen

1. Neue E-Mail mit `direction = 'inbound'` erstellen
2. Status auf "Empfangen" setzen
3. `received_at` Zeitstempel setzen
4. E-Mail erscheint im Posteingang als ungelesen
5. Beim Öffnen wird `read_at` automatisch gesetzt

### E-Mail-Vorlage verwenden

```php
$template = EmailTemplate::where('slug', 'rental-confirmation')->first();

$rendered = $template->render([
    'customer_name' => $rental->customer->name,
    'rental_id' => $rental->id,
    'board_name' => $rental->field->board->name,
    // ...
]);

Email::create([
    Email::direction => Email::DIRECTION_OUTBOUND,
    Email::status => Email::STATUS_DRAFT,
    Email::email_template_id => $template->id,
    Email::subject => $rendered['subject'],
    Email::body_html => $rendered['body_html'],
    Email::body_text => $rendered['body_text'],
    // ...
]);
```

## Zukünftige Erweiterungen

- [ ] Integration mit echtem E-Mail-Provider (z.B. SendGrid, Mailgun)
- [ ] Dateianhänge hochladen und anhängen
- [ ] WYSIWYG-Editor für E-Mail-Vorlagen
- [ ] Automatische E-Mails bei bestimmten Events
- [ ] E-Mail-Signaturen
- [ ] Thread-Ansicht für E-Mail-Konversationen
- [ ] Spam-Filter und -Markierung
- [ ] E-Mail-Import von externen Postfächern
- [ ] Versandstatistiken und Reports
- [ ] A/B-Testing für E-Mail-Vorlagen

## Hinweise

- Alle E-Mails verwenden Soft Deletes
- Die `message_id` sollte eindeutig sein für E-Mail-Threading
- `in_reply_to` und `references` werden für E-Mail-Konversationen verwendet
- Metadaten können beliebige zusätzliche Informationen als JSON speichern
- E-Mail-Vorlagen unterstützen automatisches Slug-Generierung basierend auf dem Namen
