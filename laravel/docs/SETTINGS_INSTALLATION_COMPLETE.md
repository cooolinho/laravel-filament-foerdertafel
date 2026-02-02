# Settings System - Installation Abgeschlossen ✅

## Was wurde implementiert

### ✅ 1. Database Migration
- **Datei**: `database/migrations/2026_02_02_222624_create_settings_table.php`
- **Status**: Erfolgreich migriert
- **Standardwerte**: Automatisch eingefügt

### ✅ 2. Setting Model
- **Datei**: `app/Models/Setting.php`
- **Features**:
  - Konstanten für alle Felder
  - Zahlungsmethoden-Konstanten
  - `current()` Methode für Singleton-Pattern
  - `getPaymentMethods()` für verfügbare Zahlungsmethoden
  - BelongsTo-Beziehung zu EmailTemplate

### ✅ 3. SettingsPage (Filament Admin)
- **Datei**: `app/Filament/Admin/Pages/SettingsPage.php`
- **Features**:
  - Formular mit zwei Sektionen (Allgemein & E-Mail)
  - Automatische Validierung
  - Erfolgs-Notifications
  - Navigation-Icon: Zahnrad

### ✅ 4. View-Datei
- **Datei**: `resources/views/filament/admin/pages/settings-page.blade.php`
- Enthält Formular mit Wire-Binding

### ✅ 5. Dokumentation
- **Datei**: `docs/SETTINGS_SYSTEM.md`
- Vollständige Dokumentation mit Beispielen

## Verfügbare Einstellungen

| Einstellung | Feld | Typ | Standard | Beschreibung |
|-------------|------|-----|----------|--------------|
| Zahlungsmethode | `default_payment_method` | String | `bank_transfer` | Überweisung, Kreditkarte, PayPal, Bar |
| Mietdauer | `default_rental_duration` | Integer | `1` | In Monaten (1-12) |
| Max. Felder | `max_fields_per_customer` | Integer | `10` | Maximale Felder pro Kunde (1-100) |
| E-Mail Benachrichtigungen | `email_notifications_enabled` | Boolean | `true` | Ein-/Ausschalten |
| E-Mail Vorlage | `default_email_template_id` | Foreign Key | `null` | Verknüpfung zu EmailTemplate |

## Verwendung

### Im Code
```php
use App\Models\Setting;

// Einstellungen abrufen
$settings = Setting::current();

// Werte verwenden
$paymentMethod = $settings->default_payment_method;
$maxFields = $settings->max_fields_per_customer;

// Zahlungsmethoden-Liste
$methods = Setting::getPaymentMethods();
```

### Im Admin-Panel
1. Navigiere zu **Admin** → **Einstellungen**
2. Bearbeite die gewünschten Werte
3. Klicke auf **Speichern**

## Nächste Schritte

### Optional: E-Mail Template erstellen
Wenn Sie die Standard-E-Mail-Vorlage setzen möchten, erstellen Sie zuerst ein EmailTemplate:

```bash
docker exec -it foerdertafel php artisan tinker
```

```php
$template = App\Models\EmailTemplate::create([
    'name' => 'Standard Template',
    'slug' => 'default-template',
    'subject' => 'Ihre Anfrage',
    'body_html' => '<p>Standard E-Mail</p>',
    'is_active' => true,
    'category' => 'general'
]);
```

### Integration in bestehenden Code

#### 1. In Rental-Validierung
```php
// app/Http/Controllers/RentalController.php
$settings = Setting::current();
$customerRentals = $customer->rentals()->count();

if ($customerRentals >= $settings->max_fields_per_customer) {
    throw ValidationException::withMessages([
        'fields' => 'Maximale Anzahl von ' . $settings->max_fields_per_customer . ' Feldern erreicht'
    ]);
}
```

#### 2. In E-Mail Service
```php
// app/Services/EmailService.php
public function sendEmail($to, $subject, $body)
{
    $settings = Setting::current();
    
    if (!$settings->email_notifications_enabled) {
        return; // E-Mails deaktiviert
    }
    
    // E-Mail senden mit Standard-Template
    $template = $settings->defaultEmailTemplate;
    // ...
}
```

#### 3. In Inquiry-Formular
```php
// app/Filament/App/Pages/InquiryPage.php
use App\Models\Setting;

Select::make('payment_method')
    ->label('Zahlungsmethode')
    ->options(Setting::getPaymentMethods())
    ->default(fn() => Setting::current()?->default_payment_method)
    ->required()
```

## Test-Befehle

### Settings prüfen
```bash
docker exec -it foerdertafel php artisan tinker --execute="var_dump(App\Models\Setting::current());"
```

### Zahlungsmethoden anzeigen
```bash
docker exec -it foerdertafel php artisan tinker --execute="print_r(App\Models\Setting::getPaymentMethods());"
```

### Settings aktualisieren
```bash
docker exec -it foerdertafel php artisan tinker --execute="App\Models\Setting::current()->update(['max_fields_per_customer' => 20]);"
```

## Troubleshooting

### Problem: Settings-Seite nicht sichtbar
**Lösung**: Cache leeren
```bash
docker exec -it foerdertafel php artisan optimize:clear
```

### Problem: Formular speichert nicht
**Lösung**: Browser-Cache leeren und neu laden

### Problem: Keine Settings in DB
**Lösung**: Migration erneut ausführen
```bash
docker exec -it foerdertafel php artisan migrate:fresh
```

## Verifikation

✅ Migration ausgeführt
✅ Settings-Datensatz erstellt  
✅ Model funktioniert
✅ Zahlungsmethoden verfügbar
✅ Admin-Seite registriert

**Status**: Installation erfolgreich abgeschlossen! 🎉

Die Settings-Seite ist jetzt im Admin-Panel unter dem Zahnrad-Icon verfügbar.
