# Model Constants - Übersicht

Alle Models und Migrationen wurden überarbeitet und verwenden jetzt Konstanten für Properties, analog zum Board Model.

## ✅ Aktualisierte Models

### Location Model
```php
const string name = 'name';
const string address = 'address';
const string description = 'description';
```

### Board Model (bereits vorhanden)
```php
const string location_id = 'location_id';
const string name = 'name';
const string rows = 'rows';
const string columns = 'columns';
const string description = 'description';
```

### Field Model
```php
const string board_id = 'board_id';
const string name = 'name';
const string row = 'row';
const string column = 'column';
const string width = 'width';
const string height = 'height';
const string price_per_month = 'price_per_month';
const string status = 'status';
const string description = 'description';

// Status Konstanten
const string STATUS_AVAILABLE = 'available';
const string STATUS_RENTED = 'rented';
const string STATUS_RESERVED = 'reserved';
```

### Customer Model
```php
const string name = 'name';
const string company_name = 'company_name';
const string email = 'email';
const string phone = 'phone';
const string address = 'address';
const string payment_method = 'payment_method';
const string notes = 'notes';
```

### Rental Model
```php
const string customer_id = 'customer_id';
const string start_date = 'start_date';
const string end_date = 'end_date';
const string total_price = 'total_price';
const string status = 'status';
const string notes = 'notes';

// Status Konstanten
const string STATUS_ACTIVE = 'active';
const string STATUS_COMPLETED = 'completed';
const string STATUS_CANCELLED = 'cancelled';
```

## ✅ Aktualisierte Migrationen

Alle Migrationen verwenden jetzt die Model-Konstanten:

### Beispiel (Locations Migration)
```php
use App\Models\Location;

Schema::create('locations', function (Blueprint $table) {
    $table->id();
    $table->string(Location::name);
    $table->text(Location::address);
    $table->text(Location::description)->nullable();
    $table->timestamps();
});
```

### Beispiel (Fields Migration mit Status-Enum)
```php
use App\Models\Field;

$table->enum(Field::status, [
    Field::STATUS_AVAILABLE, 
    Field::STATUS_RENTED, 
    Field::STATUS_RESERVED
])->default(Field::STATUS_AVAILABLE);
```

## Vorteile dieses Ansatzes

1. **Type Safety**: Konstanten bieten bessere Autovervollständigung in der IDE
2. **Refactoring**: Beim Umbenennen einer Spalte muss nur die Konstante geändert werden
3. **Consistency**: Gleiche Spaltennamen in Models, Migrationen und Code
4. **Fehlerprävention**: Tippfehler werden zur Compile-Zeit erkannt
5. **Dokumentation**: Alle verfügbaren Properties sind auf einen Blick sichtbar

## Verwendung in Filament Resources

Bei der Erstellung der Filament Resources kannst du die Konstanten verwenden:

```php
use App\Models\Field;

Forms\Components\TextInput::make(Field::name)
    ->required(),

Forms\Components\Select::make(Field::status)
    ->options([
        Field::STATUS_AVAILABLE => 'Verfügbar',
        Field::STATUS_RENTED => 'Vermietet',
        Field::STATUS_RESERVED => 'Reserviert',
    ])
    ->default(Field::STATUS_AVAILABLE),
```

## Nächste Schritte

Die Datenbank-Struktur ist jetzt vollständig vorbereitet. Du kannst:

1. `php artisan migrate` ausführen um die Tabellen zu erstellen
2. Die Filament Resources erstellen
3. Optional: Seeder erstellen für Test-Daten
