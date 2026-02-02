# Settings System - Quick Reference

## Sofortzugriff

### Einstellungen abrufen (cached)
```php
use App\Models\Setting;

// Alle Einstellungen als Objekt (cached)
$settings = Setting::current();

// Einzelne Einstellung abrufen (empfohlen)
$maxFields = Setting::get('max_fields_per_customer');

// Mit Fallback-Wert
$maxFields = Setting::get('max_fields_per_customer', 10);
```

### Cache-Management
```php
// Cache manuell leeren (wird automatisch bei save/delete geleert)
Setting::clearCache();

// Cache-Einstellungen
Setting::CACHE_KEY  // 'app_settings'
Setting::CACHE_TTL  // 3600 (1 Stunde)
```

### Verfügbare Eigenschaften
```php
$settings->default_payment_method        // 'bank_transfer', 'credit_card', 'paypal', 'cash'
$settings->default_rental_duration       // Integer (Monate)
$settings->max_fields_per_customer       // Integer
$settings->email_notifications_enabled   // Boolean
$settings->default_email_template_id     // Integer|null
$settings->defaultEmailTemplate          // EmailTemplate|null (Relation)
```

### Konstanten
```php
// Felder
Setting::default_payment_method
Setting::default_rental_duration
Setting::max_fields_per_customer
Setting::email_notifications_enabled
Setting::default_email_template_id

// Zahlungsmethoden
Setting::PAYMENT_METHOD_BANK_TRANSFER  // 'bank_transfer'
Setting::PAYMENT_METHOD_CREDIT_CARD    // 'credit_card'
Setting::PAYMENT_METHOD_PAYPAL         // 'paypal'
Setting::PAYMENT_METHOD_CASH           // 'cash'
```

### Methoden
```php
Setting::current()                  // Singleton: Aktuelle Einstellungen (cached)
Setting::get($key, $default)        // Einzelne Einstellung mit Fallback
Setting::getPaymentMethods()        // Array aller Zahlungsmethoden
Setting::clearCache()               // Cache manuell leeren
```

## Standardwerte (aus Migration)
- **Zahlungsmethode**: Überweisung (`bank_transfer`)
- **Mietdauer**: 1 Monat
- **Max. Felder**: 10
- **E-Mail Benachrichtigungen**: Aktiviert (`true`)
- **E-Mail Vorlage**: Keine (`null`)

## Admin-Zugriff
**URL**: `/admin/settings`  
**Navigation**: Admin-Panel → Einstellungen (⚙️ Icon)

## Dateien
- Model: `app/Models/Setting.php`
- Page: `app/Filament/Admin/Pages/SettingsPage.php`
- View: `resources/views/filament/admin/pages/settings-page.blade.php`
- Migration: `database/migrations/2026_02_02_222624_create_settings_table.php`
- Docs: `docs/SETTINGS_SYSTEM.md`

## Häufige Verwendungen

### Maximale Felder prüfen (empfohlen)
```php
$currentCount = $customer->fields()->count();
$maxFields = Setting::get('max_fields_per_customer', 10);

if ($currentCount >= $maxFields) {
    // Maximale Anzahl erreicht
}
```

### E-Mail-Benachrichtigung prüfen (empfohlen)
```php
if (Setting::get('email_notifications_enabled', true)) {
    // E-Mail senden
}
```

### Standard-Zahlungsmethode verwenden (empfohlen)
```php
Select::make('payment_method')
    ->options(Setting::getPaymentMethods())
    ->default(Setting::get('default_payment_method', 'bank_transfer'))
```

### Alternative: Vollständiges Objekt verwenden
```php
$settings = Setting::current();

if ($settings) {
    $maxFields = $settings->max_fields_per_customer;
    $paymentMethod = $settings->default_payment_method;
}
```

## Docker-Befehle

```bash
# Migration ausführen
docker exec -it foerdertafel php artisan migrate

# Settings anzeigen
docker exec -it foerdertafel php artisan tinker --execute="var_dump(App\Models\Setting::current());"

# Cache leeren
docker exec -it foerdertafel php artisan optimize:clear
```

## Status: ✅ Vollständig implementiert und getestet
