# Database Schema

## Übersicht der Entitäten

### Location (Standort)
Repräsentiert den Standort des Boards
- **Felder:**
  - `id` - Primärschlüssel
  - `name` - Name des Standorts
  - `address` - Adresse
  - `description` - Beschreibung (optional)
  - `timestamps` - created_at, updated_at

- **Beziehungen:**
  - `hasMany` Boards

---

### Board (Tafel)
Repräsentiert das gesamte Board (z.B. Fußballfeld)
- **Felder:**
  - `id` - Primärschlüssel
  - `location_id` - Fremdschlüssel zu Location
  - `name` - Name des Boards
  - `rows` - Anzahl der Reihen
  - `columns` - Anzahl der Spalten
  - `description` - Beschreibung (optional)
  - `timestamps` - created_at, updated_at

- **Beziehungen:**
  - `belongsTo` Location
  - `hasMany` Fields

---

### Field (Feld)
Repräsentiert ein einzelnes Feld auf dem Board
- **Felder:**
  - `id` - Primärschlüssel
  - `board_id` - Fremdschlüssel zu Board
  - `name` - Name des Feldes (z.B. "Tor", "Strafraum", "Mittelkreis")
  - `row` - Reihe auf dem Board
  - `column` - Spalte auf dem Board
  - `width` - Breite des Feldes (Standard: 1)
  - `height` - Höhe des Feldes (Standard: 1)
  - `price_per_month` - Preis pro Monat
  - `status` - Status: 'available', 'rented', 'reserved'
  - `description` - Beschreibung (optional)
  - `timestamps` - created_at, updated_at

- **Beziehungen:**
  - `belongsTo` Board
  - `belongsToMany` Rentals (durch field_rental Pivot-Tabelle)

---

### Customer (Kunde)
Repräsentiert eine Person/Firma, die Felder mieten kann
- **Felder:**
  - `id` - Primärschlüssel
  - `name` - Name der Person
  - `company_name` - Firmenname (optional)
  - `email` - E-Mail (unique)
  - `phone` - Telefonnummer (optional)
  - `address` - Adresse (optional)
  - `payment_method` - Zahlungsmethode (optional)
  - `notes` - Notizen (optional)
  - `timestamps` - created_at, updated_at

- **Beziehungen:**
  - `hasMany` Rentals

---

### Rental (Vermietung)
Repräsentiert die Vermietung eines oder mehrerer Felder
- **Felder:**
  - `id` - Primärschlüssel
  - `customer_id` - Fremdschlüssel zu Customer
  - `start_date` - Startdatum
  - `end_date` - Enddatum (optional, für unbefristete Vermietung)
  - `total_price` - Gesamtpreis
  - `status` - Status: 'active', 'completed', 'cancelled'
  - `notes` - Notizen (optional)
  - `timestamps` - created_at, updated_at

- **Beziehungen:**
  - `belongsTo` Customer
  - `belongsToMany` Fields (durch field_rental Pivot-Tabelle)

---

### field_rental (Pivot-Tabelle)
Verbindet Felder mit Vermietungen (Many-to-Many)
- **Felder:**
  - `id` - Primärschlüssel
  - `field_id` - Fremdschlüssel zu Field
  - `rental_id` - Fremdschlüssel zu Rental
  - `timestamps` - created_at, updated_at

---

## Migrationen ausführen

Um die Datenbank-Tabellen zu erstellen, führe folgenden Befehl aus:

```bash
php artisan migrate
```

Um die Datenbank zurückzusetzen und neu zu erstellen:

```bash
php artisan migrate:fresh
```

## Model Features

### Field Model
- `isAvailable()` - Prüft, ob das Feld verfügbar ist
- `isRented()` - Prüft, ob das Feld vermietet ist

### Rental Model
- `isActive()` - Prüft, ob die Vermietung aktiv ist (Status active + Datum im Zeitraum)

## Beispiel-Anwendungsfälle

### Ein Feld mieten
```php
$rental = Rental::create([
    'customer_id' => $customer->id,
    'start_date' => now(),
    'end_date' => now()->addMonths(6),
    'total_price' => 0,
    'status' => 'active',
]);

// Felder zur Vermietung hinzufügen
$fields = Field::whereIn('id', [1, 2, 3])->get();
$rental->fields()->attach($fields);

// Gesamtpreis berechnen
$totalPrice = $fields->sum('price_per_month');
$rental->update(['total_price' => $totalPrice]);

// Status der Felder aktualisieren
$fields->each(function ($field) {
    $field->update(['status' => 'rented']);
});
```

### Verfügbare Felder eines Boards anzeigen
```php
$availableFields = $board->fields()->where('status', 'available')->get();
```

### Alle Vermietungen eines Kunden
```php
$rentals = $customer->rentals()->with('fields')->get();
```
