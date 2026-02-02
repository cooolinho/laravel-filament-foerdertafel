# Quick Reference - Rental Events

## Schnellübersicht Events

### RentalCreated Event auslösen

```php
use App\Events\RentalCreated;
use App\Models\Rental;

// Wird automatisch ausgelöst bei:
$rental = Rental::create([...]);

// Oder manuell:
event(new RentalCreated($rental));
```

### RentalEnded Event auslösen

```php
use App\Events\RentalEnded;
use App\Models\Rental;

// Wird automatisch ausgelöst bei:
$rental->update(['status' => Rental::STATUS_COMPLETED]);
$rental->update(['status' => Rental::STATUS_CANCELLED]);

// Oder manuell:
event(new RentalEnded($rental));
```

## Listener verwenden

### E-Mail an Kunden senden (RentalCreated)

```php
// Automatisch durch Event ausgelöst
// Konfiguration in Settings:
// - email_notifications_enabled = true
// - E-Mail-Vorlage "rental-confirmation" muss existieren

// E-Mail wird in Queue gestellt und asynchron versendet
```

### Felder freigeben (RentalEnded)

```php
// Automatisch durch Event ausgelöst
// Alle Felder der beendeten Vermietung werden auf "available" gesetzt
```

## Eigenen Listener hinzufügen

### 1. Listener erstellen

```php
// app/Listeners/LogRentalActivity.php
namespace App\Listeners;

use App\Events\RentalCreated;
use Illuminate\Support\Facades\Log;

class LogRentalActivity
{
    public function handle(RentalCreated $event): void
    {
        Log::info("Neue Vermietung: #{$event->rental->id}");
    }
}
```

### 2. Listener registrieren

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    RentalCreated::class => [
        SendRentalConfirmationEmail::class,
        LogRentalActivity::class, // Neuer Listener
    ],
];
```

## E-Mail-Vorlagen Variablen

### Verfügbare Variablen in rental-confirmation Template

```
{{ customer_name }}     - Name des Kunden
{{ rental_id }}         - ID der Vermietung
{{ rental_start }}      - Startdatum (dd.mm.yyyy)
{{ rental_end }}        - Enddatum (dd.mm.yyyy)
{{ total_price }}       - Gesamtpreis (formatiert)
{{ field_count }}       - Anzahl der Felder
{{ field_names }}       - Namen der Felder (kommasepariert)
{{ board_name }}        - Name des Boards
{{ location_name }}     - Name des Standorts
```

### Eigene Variablen hinzufügen

```php
// app/Listeners/SendRentalConfirmationEmail.php
$variables = [
    'custom_variable' => 'Mein Wert',
    // ...
];
```

## Observer Methoden

### RentalObserver

```php
// app/Observers/RentalObserver.php

// Bei Erstellung
public function created(Rental $rental): void
{
    // Felder auf "rented" setzen
    // RentalCreated Event auslösen
}

// Bei Aktualisierung
public function updated(Rental $rental): void
{
    // Prüfe Status-Änderung
    // RentalEnded Event auslösen (bei completed/cancelled)
}

// Bei Löschung
public function deleted(Rental $rental): void
{
    // Felder freigeben
    // RentalEnded Event auslösen (wenn aktiv)
}
```

## Debugging

### Events testen

```bash
# Test-Kommando ausführen
php artisan test:rental-events
```

### Logs anzeigen

```bash
# Alle Event-Logs
tail -f storage/logs/laravel.log | grep -i "rental\|event"

# Nur E-Mail-Logs
tail -f storage/logs/laravel.log | grep -i "email"
```

### Event-Listener deaktivieren

```php
// Temporär alle Event-Listener deaktivieren
Event::fake();

// Nur bestimmte Events deaktivieren
Event::fake([
    RentalCreated::class,
]);
```

## Häufige Probleme

### E-Mail wird nicht versendet

1. **Settings prüfen:**
   ```php
   $settings = Setting::current();
   dd($settings->email_notifications_enabled);
   ```

2. **E-Mail-Vorlage prüfen:**
   ```php
   $template = EmailTemplate::where('slug', 'rental-confirmation')
       ->where('is_active', true)
       ->first();
   dd($template);
   ```

3. **Queue-Worker läuft?**
   ```bash
   php artisan queue:work
   ```

### Event wird nicht ausgelöst

1. **Observer registriert?**
   ```php
   // app/Providers/AppServiceProvider.php
   public function boot(): void
   {
       Rental::observe(RentalObserver::class);
   }
   ```

2. **Listener registriert?**
   ```php
   // app/Providers/EventServiceProvider.php
   protected $listen = [
       RentalCreated::class => [
           SendRentalConfirmationEmail::class,
       ],
   ];
   ```

3. **Cache löschen:**
   ```bash
   php artisan event:clear
   php artisan config:clear
   php artisan cache:clear
   ```

### Felder werden nicht freigegeben

1. **Listener prüfen:**
   ```php
   // Prüfe ob SetFieldsToAvailable ausgeführt wird
   Log::info('SetFieldsToAvailable wurde ausgeführt');
   ```

2. **Feld-Status manuell prüfen:**
   ```php
   $rental = Rental::find(1);
   foreach ($rental->fields as $field) {
       dd($field->status);
   }
   ```

## Best Practices

1. **Events für wichtige Geschäftslogik verwenden**
   - Nicht für einfache CRUD-Operationen
   - Für asynchrone Prozesse (E-Mails, Benachrichtigungen)

2. **Listener schlank halten**
   - Job-Klassen für aufwändige Operationen verwenden
   - Fehlerbehandlung implementieren

3. **Logging verwenden**
   - Info-Level für normale Operationen
   - Warning-Level für unerwartete Situationen
   - Error-Level für Fehler

4. **Queue verwenden**
   - E-Mail-Versand über Queue
   - Zeitintensive Operationen asynchron ausführen

## Siehe auch

- [Vollständige Dokumentation](RENTAL_EVENT_SYSTEM.md)
- [Email System](EMAIL_SYSTEM.md)
- [Settings System](SETTINGS_SYSTEM.md)
