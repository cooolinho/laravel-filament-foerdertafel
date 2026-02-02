# Settings System

## Übersicht

Das Settings System ermöglicht die zentrale Verwaltung von Einstellungen für das gesamte Projekt über eine Filament Admin-Seite.

## Komponenten

### 1. Setting Model (`app/Models/Setting.php`)

Das Model verwaltet folgende Einstellungen:

- **default_payment_method**: Standard-Zahlungsmethode (Überweisung, Kreditkarte, PayPal, Bar)
- **default_rental_duration**: Standard-Mietdauer in Monaten
- **max_fields_per_customer**: Maximale Anzahl Felder pro Kunde
- **email_notifications_enabled**: E-Mail Benachrichtigungen aktivieren/deaktivieren
- **default_email_template_id**: Verknüpfung zur Standard E-Mail Vorlage

#### Verwendung im Code

```php
// Methode 1: Einzelne Einstellung abrufen (empfohlen, cached)
$maxFields = Setting::get('max_fields_per_customer', 10);
$paymentMethod = Setting::get('default_payment_method', 'bank_transfer');
$emailEnabled = Setting::get('email_notifications_enabled', true);

// Methode 2: Alle Einstellungen als Objekt (cached)
$settings = Setting::current();

// Zahlungsmethode prüfen
if ($settings->default_payment_method === Setting::PAYMENT_METHOD_BANK_TRANSFER) {
    // ...
}

// Maximale Felder prüfen
if ($customerFieldCount >= $settings->max_fields_per_customer) {
    // Kunde hat Maximum erreicht
}

// Verfügbare Zahlungsmethoden
$paymentMethods = Setting::getPaymentMethods();
```

#### Performance & Caching

Das Setting Model nutzt automatisches Caching für optimale Performance:

- **Cache-Dauer**: 1 Stunde (3600 Sekunden)
- **Cache-Key**: `app_settings`
- **Automatische Cache-Invalidierung**: Bei save/update/delete
- **Manuelles Cache-Leeren**: `Setting::clearCache()`

```php
// Cache wird automatisch verwaltet
$settings = Setting::current(); // Erste Abfrage: aus DB
$settings = Setting::current(); // Weitere Abfragen: aus Cache

// Manuelle Cache-Verwaltung (optional)
Setting::clearCache();
```

#### Konstanten

**Feld-Konstanten:**
- `Setting::default_payment_method`
- `Setting::default_rental_duration`
- `Setting::max_fields_per_customer`
- `Setting::email_notifications_enabled`
- `Setting::default_email_template_id`

**Zahlungsmethoden-Konstanten:**
- `Setting::PAYMENT_METHOD_BANK_TRANSFER` - 'bank_transfer'
- `Setting::PAYMENT_METHOD_CREDIT_CARD` - 'credit_card'
- `Setting::PAYMENT_METHOD_PAYPAL` - 'paypal'
- `Setting::PAYMENT_METHOD_CASH` - 'cash'

### 2. Migration (`database/migrations/2026_02_02_222624_create_settings_table.php`)

Die Migration erstellt die `settings` Tabelle mit folgenden Spalten:

- `id`: Primärschlüssel
- `default_payment_method`: String (Standard: 'bank_transfer')
- `default_rental_duration`: Integer (Standard: 1)
- `max_fields_per_customer`: Integer (Standard: 10)
- `email_notifications_enabled`: Boolean (Standard: true)
- `default_email_template_id`: Foreign Key zu email_templates (nullable)
- `created_at` & `updated_at`: Timestamps

Die Migration fügt automatisch einen Standarddatensatz ein.

#### Migration ausführen

```bash
php artisan migrate
```

### 3. SettingsPage (`app/Filament/Admin/Pages/SettingsPage.php`)

Eine Filament Admin-Seite mit Formular zur Verwaltung der Einstellungen.

#### Features

- **Singleton-Pattern**: Es gibt immer nur einen Settings-Datensatz
- **Formulare in Sektionen gruppiert**:
  - Allgemeine Einstellungen
  - E-Mail Einstellungen
- **Validierung**: Alle Felder werden validiert
- **Benachrichtigungen**: Erfolgs-Notification nach dem Speichern

#### Navigation

Die Seite erscheint im Admin-Panel unter:
- **Icon**: Zahnrad (cog-6-tooth)
- **Label**: "Einstellungen"

## Datenbank-Schema

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED PRIMARY KEY,
    default_payment_method VARCHAR(255) DEFAULT 'bank_transfer',
    default_rental_duration INT DEFAULT 1,
    max_fields_per_customer INT DEFAULT 10,
    email_notifications_enabled BOOLEAN DEFAULT true,
    default_email_template_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (default_email_template_id) REFERENCES email_templates(id) ON DELETE SET NULL
);
```

## Standardwerte

Bei der Installation werden folgende Standardwerte gesetzt:

| Einstellung | Standardwert |
|-------------|--------------|
| Zahlungsmethode | Überweisung (bank_transfer) |
| Mietdauer | 1 Monat |
| Max. Felder pro Kunde | 10 |
| E-Mail Benachrichtigungen | Aktiviert |
| E-Mail Vorlage | Keine (null) |

## Verwendungsbeispiele

### In einem Controller (empfohlen)

```php
use App\Models\Setting;

class RentalController extends Controller
{
    public function store(Request $request)
    {
        // Empfohlen: Einzelne Settings mit get() abrufen
        $duration = $request->input('duration') ?? Setting::get('default_rental_duration', 1);
        
        // Maximale Felder prüfen
        $customerRentals = $customer->rentals()->count();
        $maxFields = Setting::get('max_fields_per_customer', 10);
        
        if ($customerRentals >= $maxFields) {
            return back()->withErrors('Maximale Anzahl von ' . $maxFields . ' Feldern erreicht');
        }
        
        // ...
    }
}
```

### Alternative: Mit vollständigem Settings-Objekt

```php
use App\Models\Setting;

class RentalController extends Controller
{
    public function store(Request $request)
    {
        $settings = Setting::current();
        
        // Standard-Mietdauer verwenden
        $duration = $request->input('duration', $settings->default_rental_duration);
        
        // Maximale Felder prüfen
        $customerRentals = $customer->rentals()->count();
        if ($customerRentals >= $settings->max_fields_per_customer) {
            return back()->withErrors('Maximale Anzahl erreicht');
        }
        
        // ...
    }
}
```

### In einem Service

```php
use App\Models\Setting;

class EmailService
{
    public function shouldSendEmail(): bool
    {
        // Empfohlen: get() mit Fallback
        return Setting::get('email_notifications_enabled', false);
    }
    
    public function getDefaultTemplate(): ?EmailTemplate
    {
        $templateId = Setting::get('default_email_template_id');
        return $templateId ? EmailTemplate::find($templateId) : null;
    }
}
```

### In einer Filament Resource

```php
use App\Models\Setting;
use Filament\Forms\Components\Select;

Select::make('payment_method')
    ->options(Setting::getPaymentMethods())
    ->default(Setting::get('default_payment_method', 'bank_transfer'))
```

## View-Datei

Die View-Datei befindet sich unter:
`resources/views/filament/admin/pages/settings-page.blade.php`

Sie enthält ein Formular mit Wire-Binding zur SettingsPage-Komponente.

## Beziehungen

### EmailTemplate

Die Settings haben eine optionale Beziehung zur EmailTemplate-Tabelle:

```php
$settings = Setting::current();
$template = $settings->defaultEmailTemplate; // BelongsTo Beziehung
```

## Best Practices

1. **Singleton-Pattern verwenden**: Verwenden Sie immer `Setting::current()` statt direkter Queries
2. **Konstanten verwenden**: Nutzen Sie die definierten Konstanten statt Magic Strings
3. **Null-Safety**: Prüfen Sie immer, ob Settings existieren (z.B. mit `?->` Operator)
4. **Caching**: Für häufige Zugriffe könnte Caching hinzugefügt werden
5. **Default-Werte**: Verwenden Sie immer Fallback-Werte: `$settings?->max_fields_per_customer ?? 10`

## Erweiterungen

### Neue Einstellung hinzufügen

1. Migration erstellen:
```bash
php artisan make:migration add_new_setting_to_settings_table
```

2. In der Migration die Spalte hinzufügen:
```php
Schema::table('settings', function (Blueprint $table) {
    $table->string('new_setting')->default('default_value');
});
```

3. Im Setting Model hinzufügen:
```php
// Konstante
const string new_setting = 'new_setting';

// Fillable
protected $fillable = [
    // ...
    self::new_setting,
];

// Optional: Cast
protected $casts = [
    // ...
    self::new_setting => 'boolean', // oder 'integer', etc.
];
```

4. In SettingsPage das Formular erweitern:
```php
TextInput::make(Setting::new_setting)
    ->label('Neue Einstellung')
    ->required()
```

## Testing

```php
use App\Models\Setting;

test('settings can be retrieved', function () {
    $settings = Setting::current();
    expect($settings)->not->toBeNull();
    expect($settings->default_payment_method)->toBe('bank_transfer');
});

test('settings can be updated', function () {
    $settings = Setting::current();
    $settings->update(['max_fields_per_customer' => 20]);
    
    expect($settings->fresh()->max_fields_per_customer)->toBe(20);
});
```

## Troubleshooting

### Settings nicht vorhanden

Falls keine Settings existieren, führen Sie die Migration erneut aus:
```bash
php artisan migrate:fresh
```

Oder fügen Sie manuell einen Datensatz hinzu:
```php
Setting::create([
    'default_payment_method' => 'bank_transfer',
    'default_rental_duration' => 1,
    'max_fields_per_customer' => 10,
    'email_notifications_enabled' => true,
]);
```

### EmailTemplate Fehler

Stellen Sie sicher, dass die email_templates Tabelle vor der settings Tabelle migriert wird.
