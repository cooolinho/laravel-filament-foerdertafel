# Field Status Management mit RentalObserver

## Übersicht

Der Field-Status wird jetzt automatisch durch einen **RentalObserver** verwaltet. Felder werden initial mit `STATUS_AVAILABLE` erstellt und ändern ihren Status automatisch basierend auf den zugehörigen Rentals.

## Implementierte Komponenten

### 1. RentalObserver (`app/Observers/RentalObserver.php`)

Der Observer lauscht auf folgende Rental-Events:

#### **created** - Rental wurde erstellt
- Setzt den Status aller zugeordneten Fields basierend auf Rental-Status und Datum

#### **updated** - Rental wurde aktualisiert
- Aktualisiert den Status aller zugeordneten Fields
- Wichtig bei Statusänderungen (z.B. von ACTIVE zu COMPLETED)

#### **deleted** - Rental wurde gelöscht
- Setzt Fields auf `AVAILABLE` zurück
- Prüft vorher, ob das Field noch in anderen aktiven Rentals ist

### 2. Status-Logik

Der Observer bestimmt den Field-Status basierend auf:

| Rental Status | Zeitpunkt | Field Status |
|---------------|-----------|--------------|
| **ACTIVE** | Start-Datum in der Zukunft | `RESERVED` |
| **ACTIVE** | Start-Datum in Vergangenheit/heute, kein End-Datum | `RENTED` |
| **ACTIVE** | Start-Datum in Vergangenheit/heute, End-Datum in Zukunft | `RENTED` |
| **ACTIVE** | End-Datum in der Vergangenheit | `AVAILABLE` |
| **COMPLETED** | - | `AVAILABLE` |
| **CANCELLED** | - | `AVAILABLE` |

### 3. Wichtige Methoden

#### `updateFieldStatus(Rental $rental)`
- Hauptmethode zur Status-Aktualisierung
- Wird bei `created` und `updated` Events aufgerufen

#### `determineActiveStatus(Rental $rental): string`
- Logik für ACTIVE Rentals
- Berücksichtigt Start- und End-Datum
- Unterscheidet zwischen RESERVED (zukünftig) und RENTED (aktuell)

#### `releaseFields(Rental $rental)`
- Wird beim Löschen eines Rentals aufgerufen
- Prüft ob Field noch in anderen aktiven Rentals ist
- Setzt nur auf AVAILABLE wenn keine anderen Rentals existieren

## Änderungen an bestehenden Dateien

### FieldSeeder.php
**Vorher:**
```php
Field::status => Field::STATUS_RENTED // Manuell gesetzt
```

**Jetzt:**
```php
Field::status => Field::STATUS_AVAILABLE // Immer initial AVAILABLE
```

Alle Felder werden mit `STATUS_AVAILABLE` erstellt.

### RentalSeeder.php
**Vorher:**
```php
foreach ($fields as $field) {
    $field->update([Field::status => Field::STATUS_RENTED]);
}
$rental = Rental::create([...]); // Danach Rental erstellen
```

**Jetzt:**
```php
$rental = Rental::create([...]); // Rental erstellen
$rental->fields()->attach($fieldIds); // Observer übernimmt Status-Update
```

Alle manuellen Status-Updates wurden entfernt.

### AppServiceProvider.php
```php
public function boot(): void
{
    Rental::observe(RentalObserver::class);
}
```

Observer wird beim Booten der Application registriert.

## Vorteile der neuen Implementierung

### ✅ **Automatisierung**
- Kein manuelles Setzen von Field-Status mehr nötig
- Status wird automatisch bei Rental-Änderungen aktualisiert

### ✅ **Konsistenz**
- Zentrale Logik an einem Ort
- Keine verstreuten Status-Updates im Code

### ✅ **Datumsbasiert**
- Status wird basierend auf Start- und End-Datum bestimmt
- RESERVED für zukünftige Rentals
- RENTED für aktuelle Rentals
- AVAILABLE für abgelaufene/stornierte Rentals

### ✅ **Mehrfachvermietung-Schutz**
- Prüft beim Löschen ob Field noch in anderen Rentals ist
- Verhindert versehentliches Freigeben von gemieteten Fields

### ✅ **Wartbarkeit**
- Änderungen an der Status-Logik nur an einem Ort nötig
- Testbar durch isolierte Observer-Klasse

## Verwendung

### Neues Rental erstellen
```php
$rental = Rental::create([
    Rental::customer_id => $customer->id,
    Rental::start_date => now(),
    Rental::end_date => now()->addMonths(6),
    Rental::status => Rental::STATUS_ACTIVE,
    // ...
]);

// Fields zuordnen - Observer aktualisiert automatisch den Status
$rental->fields()->attach([1, 2, 3]);
```

### Rental aktualisieren
```php
$rental->update([
    Rental::status => Rental::STATUS_COMPLETED,
]);
// Observer setzt automatisch alle zugehörigen Fields auf AVAILABLE
```

### Rental löschen
```php
$rental->delete();
// Observer prüft andere Rentals und setzt Fields ggf. auf AVAILABLE
```

## Zukünftige Erweiterungen

### Cronjob für abgelaufene Rentals
Es könnte ein Cronjob implementiert werden, der täglich prüft:
```php
// Setze abgelaufene Rentals auf COMPLETED
Rental::where(Rental::status, Rental::STATUS_ACTIVE)
    ->where(Rental::end_date, '<', now())
    ->update([Rental::status => Rental::STATUS_COMPLETED]);
// Observer aktualisiert automatisch die Field-Status
```

### Event-Broadcasting
Bei Bedarf könnte der Observer Events broadcasten:
```php
event(new FieldStatusChanged($field, $oldStatus, $newStatus));
```

### Logging
Status-Änderungen könnten geloggt werden:
```php
Log::info("Field {$field->id} status changed to {$newStatus} due to Rental {$rental->id}");
```

## Testing

### Unit Test Beispiel
```php
public function test_field_status_changes_when_rental_created()
{
    $field = Field::factory()->create([
        Field::status => Field::STATUS_AVAILABLE
    ]);
    
    $rental = Rental::factory()->create([
        Rental::status => Rental::STATUS_ACTIVE,
        Rental::start_date => now(),
        Rental::end_date => now()->addMonth(),
    ]);
    
    $rental->fields()->attach($field->id);
    
    $this->assertEquals(Field::STATUS_RENTED, $field->fresh()->status);
}
```

## Zusammenfassung

Das neue System mit dem RentalObserver sorgt für:
- ✅ Automatische Status-Verwaltung
- ✅ Konsistente Datenintegrität
- ✅ Weniger fehleranfälliger Code
- ✅ Bessere Wartbarkeit
- ✅ Zentrale Logik

Alle Status-Änderungen werden jetzt zentral vom Observer gesteuert, basierend auf dem Rental-Status und den Datumswerten.
