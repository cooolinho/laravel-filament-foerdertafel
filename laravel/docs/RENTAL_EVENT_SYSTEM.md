# Event System - Rental Events

## Übersicht

Das Event System wurde implementiert, um auf bestimmte Aktionen im Zusammenhang mit Vermietungen (Rentals) zu reagieren und automatisierte Prozesse auszulösen.

## Events

### 1. RentalCreated

**Beschreibung:** Wird ausgelöst, wenn eine neue Vermietung (Rental) erstellt wird.

**Datei:** `app/Events/RentalCreated.php`

**Auslöser:** 
- Wird automatisch durch den `RentalObserver` ausgelöst, wenn ein neues Rental erstellt wird
- Im Observer in der `created()` Methode

**Payload:**
- `$rental` - Das erstellte Rental Model

### 2. RentalEnded

**Beschreibung:** Wird ausgelöst, wenn eine Vermietung endet.

**Datei:** `app/Events/RentalEnded.php`

**Auslöser:**
- Wird automatisch durch den `RentalObserver` ausgelöst, wenn:
  - Der Status einer Vermietung von `active` auf `completed` oder `cancelled` geändert wird
  - Eine aktive Vermietung gelöscht wird

**Payload:**
- `$rental` - Das beendete Rental Model

## Listener

### 1. SendRentalConfirmationEmail

**Beschreibung:** Sendet eine Bestätigungs-E-Mail an den Kunden mit den Details der Vermietung.

**Datei:** `app/Listeners/SendRentalConfirmationEmail.php`

**Reagiert auf:** `RentalCreated` Event

**Funktionalität:**
1. Prüft, ob E-Mail-Benachrichtigungen in den Settings aktiviert sind
2. Lädt die E-Mail-Vorlage mit dem Slug `rental-confirmation`
3. Ersetzt Variablen in der Vorlage mit Rental-Daten
4. Erstellt einen E-Mail-Datensatz in der Datenbank
5. Versendet die E-Mail über die Queue mit dem `SendEmailJob`

**Verfügbare Variablen in der E-Mail-Vorlage:**
- `{{ customer_name }}` - Name des Kunden
- `{{ rental_id }}` - ID der Vermietung
- `{{ rental_start }}` - Startdatum der Vermietung (dd.mm.yyyy)
- `{{ rental_end }}` - Enddatum der Vermietung (dd.mm.yyyy)
- `{{ total_price }}` - Gesamtpreis der Vermietung (formatiert)
- `{{ field_count }}` - Anzahl der gemieteten Felder
- `{{ field_names }}` - Namen der gemieteten Felder (kommasepariert)
- `{{ board_name }}` - Name des Boards
- `{{ location_name }}` - Name des Standorts

### 2. SetFieldsToAvailable

**Beschreibung:** Setzt die Felder der beendeten Vermietung auf den Status "verfügbar".

**Datei:** `app/Listeners/SetFieldsToAvailable.php`

**Reagiert auf:** `RentalEnded` Event

**Funktionalität:**
1. Lädt alle Felder der beendeten Vermietung
2. Prüft für jedes Feld, ob es noch vermietet ist
3. Setzt den Status auf `available`, wenn das Feld vermietet war
4. Loggt die Änderungen

## Observer

### RentalObserver

**Beschreibung:** Überwacht Änderungen am Rental Model und löst entsprechende Events aus.

**Datei:** `app/Observers/RentalObserver.php`

**Registrierung:** In `app/Providers/AppServiceProvider.php` im `boot()` Method

**Überwachte Ereignisse:**

#### created()
- Aktualisiert den Status der zugeordneten Felder
- **Löst aus:** `RentalCreated` Event

#### updated()
- Prüft, ob der Status auf `completed` oder `cancelled` geändert wurde
- **Löst aus:** `RentalEnded` Event (wenn Status von `active` zu `completed`/`cancelled` wechselt)
- Aktualisiert den Status der zugeordneten Felder

#### deleted()
- Gibt die Felder frei, wenn keine anderen aktiven Vermietungen existieren
- **Löst aus:** `RentalEnded` Event (wenn die Vermietung aktiv war)

## Event Service Provider

**Datei:** `app/Providers/EventServiceProvider.php`

**Registrierte Events und Listener:**

```php
protected $listen = [
    RentalCreated::class => [
        SendRentalConfirmationEmail::class,
    ],
    RentalEnded::class => [
        SetFieldsToAvailable::class,
    ],
];
```

## Workflow

### Neue Vermietung erstellen

1. Ein neues Rental wird in der Datenbank erstellt
2. `RentalObserver::created()` wird ausgelöst
3. Der Status der zugeordneten Felder wird aktualisiert
4. `RentalCreated` Event wird dispatched
5. `SendRentalConfirmationEmail` Listener wird ausgeführt:
   - Prüft Settings für E-Mail-Benachrichtigungen
   - Lädt E-Mail-Vorlage
   - Ersetzt Variablen
   - Erstellt E-Mail-Datensatz
   - Fügt E-Mail zur Queue hinzu

### Vermietung beenden

1. Der Status eines Rentals wird auf `completed` oder `cancelled` geändert
2. `RentalObserver::updated()` wird ausgelöst
3. Prüfung, ob der Status von `active` geändert wurde
4. `RentalEnded` Event wird dispatched
5. `SetFieldsToAvailable` Listener wird ausgeführt:
   - Lädt alle Felder der Vermietung
   - Setzt Felder auf `available`
6. Der Status der zugeordneten Felder wird durch den Observer aktualisiert

## E-Mail-Vorlage

Die E-Mail-Vorlage für Mietbestätigungen wird über den `EmailTemplateSeeder` erstellt:

**Slug:** `rental-confirmation`  
**Kategorie:** `rental`  
**Status:** Aktiv

Die Vorlage kann im Admin-Bereich bearbeitet werden.

## Logging

Alle Events und Listener loggen ihre Aktionen für Debugging-Zwecke:

- Info-Level: Erfolgreiche Aktionen
- Warning-Level: Fehlende Vorlagen oder Konfigurationen
- Error-Level: Fehler beim Versenden von E-Mails

## Abhängigkeiten

- **Models:** `Rental`, `Field`, `Customer`, `Email`, `EmailTemplate`, `Settings`
- **Jobs:** `SendEmailJob`
- **Seeder:** `EmailTemplateSeeder`

## Konfiguration

### E-Mail-Benachrichtigungen aktivieren/deaktivieren

In der Settings-Seite im Admin-Bereich kann die Option "E-Mail-Benachrichtigungen aktivieren/deaktivieren" konfiguriert werden.

Wenn deaktiviert, werden keine E-Mails versendet, aber die Events werden trotzdem ausgelöst (für andere Listener).

## Erweiterungen

Um weitere Listener hinzuzufügen:

1. Erstelle einen neuen Listener in `app/Listeners/`
2. Registriere den Listener im `EventServiceProvider`
3. Der Listener erhält automatisch das Event mit dem Rental-Objekt

**Beispiel:**

```php
// app/Listeners/LogRentalCreation.php
class LogRentalCreation
{
    public function handle(RentalCreated $event): void
    {
        Log::info("Neue Vermietung erstellt: #{$event->rental->id}");
    }
}

// app/Providers/EventServiceProvider.php
protected $listen = [
    RentalCreated::class => [
        SendRentalConfirmationEmail::class,
        LogRentalCreation::class, // Neuer Listener
    ],
];
```

## Testing

Um das Event System zu testen:

### Manuelles Testen

1. **Im Admin-Bereich eine neue Vermietung erstellen:**
   - Navigiere zu "Rentals" → "Create"
   - Wähle einen Kunden aus
   - Wähle Felder aus
   - Setze Start- und Enddatum
   - Speichere die Vermietung
   - → RentalCreated Event wird ausgelöst
   - → E-Mail wird erstellt und zur Queue hinzugefügt

2. **Eine Vermietung beenden:**
   - Navigiere zu einer aktiven Vermietung
   - Ändere den Status auf "Completed" oder "Cancelled"
   - Speichere die Änderung
   - → RentalEnded Event wird ausgelöst
   - → Felder werden auf "verfügbar" gesetzt

### Automatisches Testen mit Artisan-Kommando

```bash
# Führe das Test-Kommando aus
php artisan test:rental-events
```

Das Kommando testet:
- Erstellung einer neuen Vermietung (RentalCreated)
- Beendigung einer Vermietung (RentalEnded)

### Logs überprüfen

```bash
# Prüfe die Logs
tail -f storage/logs/laravel.log

# Oder unter Windows
Get-Content storage/logs/laravel.log -Tail 50 -Wait
```

### Datenbank überprüfen

```sql
-- Prüfe erstellte E-Mails
SELECT * FROM emails ORDER BY created_at DESC LIMIT 10;

-- Prüfe Feld-Status
SELECT id, name, status FROM fields;

-- Prüfe Vermietungen
SELECT id, customer_id, status, start_date, end_date FROM rentals;
```

## Siehe auch

- [Email System Documentation](EMAIL_SYSTEM.md)
- [Settings System Documentation](SETTINGS_SYSTEM.md)
- [Field Status Observer Documentation](FIELD_STATUS_OBSERVER.md)
