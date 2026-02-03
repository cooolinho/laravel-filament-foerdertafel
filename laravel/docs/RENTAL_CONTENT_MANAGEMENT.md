# Rental Content Management System

## Überblick

Das Rental Content Management System ermöglicht es Kunden, ihre gemieteten Felder mit Inhalten zu füllen. Sobald eine Miete (Rental) bezahlt wurde, erhält der Kunde einen einmaligen Zugangscode per E-Mail, mit dem er auf eine geschützte Seite zugreifen kann, um seine Felder zu verwalten.

## Datenbank-Schema

### Tabelle: `rental_contents`

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `id` | bigint | Primärschlüssel |
| `rental_id` | bigint | Foreign Key zu `rentals` |
| `access_code` | varchar(14) | Einmaliger Zugangscode (Format: XXXX-XXXX-XXXX) |
| `company_logo` | varchar | Pfad zum Firmenlogo (nur für Firmen) |
| `title` | varchar | Titel/Überschrift |
| `description` | text | Beschreibungstext |
| `website_url` | varchar | Website-URL |
| `contact_email` | varchar | Kontakt-E-Mail |
| `contact_phone` | varchar | Kontakt-Telefonnummer |
| `is_private_person` | boolean | Ist der Kunde eine Privatperson? |
| `is_published` | boolean | Sind die Inhalte veröffentlicht? |
| `last_accessed_at` | timestamp | Letzter Zugriffszeitpunkt |
| `created_at` | timestamp | Erstellungszeitpunkt |
| `updated_at` | timestamp | Änderungszeitpunkt |

**Indizes:**
- `access_code` - Unique Index für schnelle Suche
- `rental_id` - Foreign Key mit CASCADE DELETE

### Erweiterung der `rentals` Tabelle

Die bestehende `rentals` Tabelle wurde um folgende Felder erweitert:

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| `paid_at` | timestamp | Zeitpunkt der Bezahlung |

**Status-Erweiterung:**
- `pending` - Miete erstellt, aber noch nicht bezahlt
- `paid` - Miete bezahlt (Zugangscode wird versendet)
- `active` - Miete ist aktiv
- `completed` - Miete abgeschlossen
- `cancelled` - Miete storniert

## Models

### RentalContent Model

**Datei:** `app/Models/RentalContent.php`

#### Wichtige Methoden:

```php
// Statische Methoden
RentalContent::generateUniqueAccessCode(): string
RentalContent::findByAccessCode(string $accessCode): ?RentalContent

// Instance-Methoden
$rentalContent->updateLastAccessed(): void
$rentalContent->canUploadLogo(): bool
$rentalContent->isPublished(): bool
$rentalContent->isRecentlyAccessed(): bool
```

#### Beziehungen:
- `rental()` - BelongsTo: Zugehörige Miete

#### Automatische Generierung:
- Beim Erstellen eines neuen `RentalContent` wird automatisch ein einmaliger Zugangscode generiert

### Rental Model (Erweiterung)

**Datei:** `app/Models/Rental.php`

#### Neue Methoden:

```php
$rental->isPaid(): bool
$rental->content(): HasOne // Beziehung zu RentalContent
```

#### Neue Konstanten:
- `Rental::STATUS_PENDING`
- `Rental::STATUS_PAID`
- `Rental::paid_at`

### Customer Model (Erweiterung)

**Datei:** `app/Models/Customer.php`

#### Neue Methoden:

```php
$customer->isCompany(): bool
$customer->isPrivatePerson(): bool
```

## Events & Listeners

### Event: RentalPaid

**Datei:** `app/Events/RentalPaid.php`

Dieses Event wird ausgelöst, wenn eine Rental bezahlt wurde.

**Verwendung:**
```php
use App\Events\RentalPaid;

// Wenn Zahlung erfolgreich
$rental->paid_at = now();
$rental->status = Rental::STATUS_PAID;
$rental->save();

event(new RentalPaid($rental));
```

### Listener: SendAccessCodeEmail

**Datei:** `app/Listeners/SendAccessCodeEmail.php`

Dieser Listener reagiert auf das `RentalPaid` Event und:
1. Erstellt oder holt den `RentalContent` für die Rental
2. Lädt das Email-Template `rental-access-code`
3. Ersetzt die Platzhalter mit echten Daten
4. Erstellt einen Email-Eintrag
5. Queued den `SendEmailJob` zum Versenden

**Verfügbare Template-Variablen:**
- `{{customer_name}}` - Name des Kunden
- `{{access_code}}` - Generierter Zugangscode
- `{{rental_id}}` - ID der Miete
- `{{start_date}}` - Startdatum (Format: dd.mm.yyyy)
- `{{end_date}}` - Enddatum (Format: dd.mm.yyyy)
- `{{access_url}}` - Direkte URL zur Content-Verwaltung
- `{{fields_count}}` - Anzahl der gemieteten Felder
- `{{fields_list}}` - Kommagetrennte Liste der Feldnamen

## Controller

### RentalContentController

**Datei:** `app/Http/Controllers/RentalContentController.php`

#### Routen & Methoden:

| Route | Methode | Beschreibung |
|-------|---------|--------------|
| `GET /rental-content/access` | `showAccessForm()` | Zeigt Zugangscode-Eingabeformular |
| `POST /rental-content/access` | `accessWithCode()` | Verarbeitet Zugangscode |
| `GET /rental-content/access/{code}` | `accessWithCode()` | Direkter Zugriff mit Code in URL |
| `GET /rental-content/manage/{code}` | `manage()` | Zeigt Content-Verwaltungsseite |
| `POST /rental-content/manage/{code}` | `update()` | Aktualisiert Content |
| `DELETE /rental-content/manage/{code}/logo` | `deleteLogo()` | Löscht Firmenlogo |

#### Validierungsregeln:

```php
[
    'title' => 'nullable|string|max:255',
    'description' => 'nullable|string|max:2000',
    'website_url' => 'nullable|url|max:255',
    'contact_email' => 'nullable|email|max:255',
    'contact_phone' => 'nullable|string|max:50',
    'is_published' => 'boolean',
    'company_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // nur für Firmen
]
```

#### Logo-Upload:
- Nur verfügbar wenn `is_private_person = false`
- Gespeichert in: `storage/app/public/rental-logos/`
- Maximale Größe: 2MB
- Erlaubte Formate: jpeg, png, jpg, gif, svg

## Routen

**Datei:** `routes/web.php`

Alle Routen sind öffentlich zugänglich (keine Authentifizierung erforderlich).

**Named Routes:**
- `rental.content.access-form` - Zugangscode-Formular
- `rental.content.access` - POST: Zugangscode verarbeiten
- `rental.content.access.code` - GET: Direkter Zugriff mit Code
- `rental.content.manage` - Content-Verwaltungsseite
- `rental.content.update` - POST: Content aktualisieren
- `rental.content.delete-logo` - DELETE: Logo löschen

## Email-Template

### Seeder: RentalAccessCodeEmailTemplateSeeder

**Datei:** `database/seeders/RentalAccessCodeEmailTemplateSeeder.php`

Erstellt das Email-Template mit dem Slug `rental-access-code`.

**Template enthält:**
- HTML-Version mit schönem Design
- Text-Version für reine Text-Clients
- Alle notwendigen Platzhalter
- Anleitung was der Kunde tun kann
- Direkt-Link zur Content-Verwaltung

**Seeder ausführen:**
```bash
php artisan db:seed --class=RentalAccessCodeEmailTemplateSeeder
```

## Migrations

### 2026_02_03_120000_create_rental_contents_table.php
Erstellt die `rental_contents` Tabelle mit allen Feldern und Indizes.

### 2026_02_03_120001_add_paid_at_to_rentals_table.php
Fügt `paid_at` zur `rentals` Tabelle hinzu und erweitert den Status-ENUM.

**Migrations ausführen:**
```bash
php artisan migrate
```

## Workflow

### 1. Rental-Erstellung
```php
$rental = Rental::create([
    'customer_id' => $customerId,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'total_price' => $totalPrice,
    'status' => Rental::STATUS_PENDING,
]);
```

### 2. Zahlung bestätigen
```php
$rental->paid_at = now();
$rental->status = Rental::STATUS_PAID;
$rental->save();

// Event auslösen
event(new RentalPaid($rental));
```

### 3. System reagiert automatisch
- `SendAccessCodeEmail` Listener wird aufgerufen
- `RentalContent` wird erstellt (falls nicht vorhanden)
- Zugangscode wird generiert
- Email wird versendet

### 4. Kunde erhält Email
- Kunde bekommt Email mit Zugangscode
- Email enthält Direktlink zur Verwaltungsseite

### 5. Kunde verwaltet Content
- Kunde gibt Zugangscode ein oder klickt auf Link
- System zeigt Content-Verwaltungsseite
- Kunde kann Inhalte eingeben und speichern

## Sicherheit

### Zugangscode
- 12 Zeichen + 2 Bindestriche (Format: XXXX-XXXX-XXXX)
- Alphanumerisch, nur Großbuchstaben
- Eindeutig (Unique Constraint)
- Keine zeitliche Begrenzung (kann später hinzugefügt werden)

### Validierung
- Alle Eingaben werden validiert
- URL-Validierung für Website
- Email-Validierung für Kontakt-Email
- Logo-Upload nur für Firmen
- Dateigröße und Typ-Prüfung für Logos

### Access Control
- Kein Login erforderlich
- Zugriff nur mit gültigem Zugangscode
- Jeder Zugriff wird protokolliert (`last_accessed_at`)

## Views (TODO)

Die folgenden Views müssen noch erstellt werden:

### resources/views/rental-content/access-form.blade.php
- Formular zur Eingabe des Zugangscodes
- Anzeige von Fehlermeldungen
- Link zu Hilfe/Support

### resources/views/rental-content/manage.blade.php
- Anzeige der gemieteten Felder
- Formular zur Eingabe/Bearbeitung der Inhalte
- Logo-Upload (conditional für Firmen)
- Vorschau der Inhalte
- Veröffentlichen/Entwurf-Button
- Success/Error-Messages

## Erweiterungsmöglichkeiten

### Zukünftige Features:
1. **Zeitlich begrenzte Zugangscodes**
   - Ablaufdatum für Zugangscodes
   - Automatische E-Mail bei Ablauf

2. **Multi-Language Support**
   - Template-Übersetzungen
   - Content in mehreren Sprachen

3. **Content-Vorschau**
   - Live-Vorschau wie Content auf dem Board aussieht
   - Responsive Preview

4. **Versions-Historie**
   - Änderungen protokollieren
   - Frühere Versionen wiederherstellen

5. **Admin-Benachrichtigungen**
   - Admin wird benachrichtigt wenn Kunde Content veröffentlicht
   - Moderations-System

6. **Erweiterte Medien**
   - Mehrere Bilder/Videos
   - Galerie-Funktion
   - QR-Code Generator

7. **Analytics**
   - Tracking wie oft Content angesehen wurde
   - Zugriffs-Statistiken

## Testdaten

Um das System zu testen, können Sie einen Seeder erstellen:

```php
// Test-Rental mit bezahltem Status
$rental = Rental::factory()->create([
    'status' => Rental::STATUS_PAID,
    'paid_at' => now(),
]);

// RentalContent automatisch erstellen
$rentalContent = RentalContent::create([
    'rental_id' => $rental->id,
    'is_private_person' => false,
]);

// Zugangscode ausgeben
echo "Access Code: " . $rentalContent->access_code;
```

## Troubleshooting

### Email wird nicht versendet
- Prüfen Sie Queue-Worker: `php artisan queue:work`
- Prüfen Sie Email-Template: Slug muss `rental-access-code` sein
- Prüfen Sie Logs: `storage/logs/laravel.log`

### Zugangscode funktioniert nicht
- Prüfen Sie ob `RentalContent` Eintrag existiert
- Prüfen Sie `access_code` Wert in Datenbank
- Prüfen Sie Route-Namen

### Logo-Upload schlägt fehl
- Prüfen Sie `storage/app/public/rental-logos` Ordner existiert
- Prüfen Sie Schreibrechte
- Erstellen Sie Symlink: `php artisan storage:link`
- Prüfen Sie `is_private_person` Flag

## Nützliche Artisan-Befehle

```bash
# Migrations ausführen
php artisan migrate

# Email-Template Seeder ausführen
php artisan db:seed --class=RentalAccessCodeEmailTemplateSeeder

# Storage-Link erstellen (für Logo-Uploads)
php artisan storage:link

# Queue-Worker starten
php artisan queue:work

# Event-Listeners anzeigen
php artisan event:list
```
