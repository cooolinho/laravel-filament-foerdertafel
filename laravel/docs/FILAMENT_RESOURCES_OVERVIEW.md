# Filament Resources - Übersicht der Implementierung

Alle Filament Resources wurden erfolgreich mit Forms, Tables und Infolists erweitert. Dabei werden durchgehend die Konstanten aus den Models verwendet.

## ✅ Implementierte Resources

### 1. Location Resource

#### **LocationForm**
- **Location Information Section**
  - Name (required, max 255)
  - Address (required, textarea)
  - Description (optional, textarea)

#### **LocationsTable**
- Spalten: Name, Address, Boards Count, Description, Created At, Updated At
- Sortierbar und durchsuchbar
- Boards Count zeigt Anzahl der zugehörigen Boards

#### **LocationInfolist**
- Zeigt alle Location-Informationen
- Statistik: Anzahl der Boards
- Timestamps in collapsible Section

---

### 2. Board Resource

#### **BoardForm**
- **Board Information Section**
  - Location (Select mit Relationship, createOptionForm für neue Locations)
  - Board Name (required, max 255)
- **Grid Configuration Section**
  - Rows (numeric, 1-100, default: 10)
  - Columns (numeric, 1-100, default: 10)
- **Additional Information Section** (collapsible)
  - Description (textarea)

#### **BoardsTable**
- Spalten: Name, Location, Rows, Columns, Fields Count, Description, Timestamps
- Filter: noch offen
- Fields Count zeigt Anzahl der Felder

#### **BoardInfolist**
- Board Information mit Location
- Grid Configuration (Rows, Columns, Total Fields)
- Timestamps (collapsible)

---

### 3. Field Resource

#### **FieldForm**
- **Field Information Section**
  - Board (Select mit Relationship)
  - Field Name (required, z.B. "Tor", "Strafraum")
  - Status (Select: Available, Rented, Reserved)
- **Position & Size Section**
  - Row, Column, Width, Height (numeric)
- **Pricing Section**
  - Price per Month (numeric mit € prefix)
- **Additional Information Section** (collapsible)
  - Description

#### **FieldsTable**
- Spalten: Name, Board, Row, Column, Width, Height, Price/Month, Status
- Status als Badge mit Farben:
  - Available = success (grün)
  - Rented = danger (rot)
  - Reserved = warning (gelb)
- Filter: Status
- Preis als Money-Format (EUR)

#### **FieldInfolist**
- Field Information mit Status Badge
- Position & Size (4 Spalten)
- Pricing
- Timestamps (collapsible)

---

### 4. Customer Resource

#### **CustomerForm**
- **Customer Information Section**
  - Name (required)
  - Company Name (optional)
- **Contact Information Section**
  - Email (required, unique, email validation)
  - Phone (optional, tel format)
  - Address (textarea, columnSpanFull)
- **Payment & Notes Section** (collapsible)
  - Payment Method (optional)
  - Notes (textarea)

#### **CustomersTable**
- Spalten: Name, Company, Email (copyable), Phone, Active Rentals Count, Payment Method, Created At
- Email ist kopierbar
- Rentals Count zeigt Anzahl der Vermietungen

#### **CustomerInfolist**
- Customer Information
- Contact Information (Email copyable)
- Payment & Notes
- Statistics: Total Rentals
- Timestamps (collapsible)

---

### 5. Rental Resource

#### **RentalForm**
- **Rental Information Section**
  - Customer (Select mit Relationship, createOptionForm für neue Kunden)
  - Status (Select: Active, Completed, Cancelled)
- **Rental Period Section**
  - Start Date (DatePicker, required)
  - End Date (DatePicker, optional für unbefristete Vermietung)
- **Fields Section**
  - Multiple Select für Felder (relationship)
  - Helper Text: "Select one or more adjacent fields"
- **Pricing Section**
  - Total Price per Month (numeric mit € prefix)
  - Helper Text: "Will be calculated from selected fields"
- **Additional Information Section** (collapsible)
  - Notes

#### **RentalsTable**
- Spalten: Customer, Company, Start Date, End Date, Price/Month, Fields Count, Status, Notes, Created At
- Status als Badge mit Farben:
  - Active = success (grün)
  - Completed = gray
  - Cancelled = danger (rot)
- Filter: Status
- Default Sort: Start Date (desc)
- End Date zeigt "Ongoing" wenn leer

#### **RentalInfolist**
- Rental Information mit Customer und Status Badge
- Rental Period
- Pricing und Fields Count
- **Rented Fields Section** mit RepeatableEntry:
  - Zeigt alle vermieteten Felder mit Name, Row, Column, Price
- Additional Information (Notes)
- Timestamps (collapsible)

---

## Verwendete Konstanten

Alle Forms, Tables und Infolists verwenden die Konstanten aus den Models:

```php
// Beispiele
Location::name
Board::rows
Field::status
Field::STATUS_AVAILABLE
Customer::email
Rental::start_date
Rental::STATUS_ACTIVE
```

## Features

### Badges mit Farben
- Field Status: success/danger/warning
- Rental Status: success/gray/danger

### Money Formatting
- Alle Preise mit `->money('EUR')`

### Relationships
- Select-Felder mit `->relationship()`
- `createOptionForm` für schnelles Erstellen neuer Datensätze
- RepeatableEntry für Rental Fields

### Counts
- `->counts('relationship')` für Anzahl-Anzeigen
- `fields_count`, `boards_count`, `rentals_count`

### Usability
- Copyable Email-Felder
- Collapsible Sections für optionale Informationen
- Placeholder für leere Felder (z.B. "Ongoing", "—")
- Helper Texts für Benutzerführung
- Toggleable Columns (isToggledHiddenByDefault)

### Validation
- Required fields
- Email validation
- Unique constraints
- Numeric min/max values
- Tel format für Telefonnummern

## Nächste Schritte

1. **Migrationen ausführen**:
   ```bash
   php artisan migrate
   ```

2. **Optional: Seeder erstellen** für Test-Daten

3. **Relations Pages** hinzufügen:
   - Bei Board: Fields-Relation anzeigen
   - Bei Customer: Rentals-Relation anzeigen
   - Bei Rental: Fields-Relation anzeigen

4. **Erweiterte Features**:
   - Automatische Preisberechnung im RentalForm
   - Field-Status automatisch bei Rental-Erstellung aktualisieren
   - Dashboard mit Statistiken
   - Visual Board-Grid anzeigen

5. **Filter erweitern**:
   - DateRange-Filter für Rentals
   - Board-Filter für Fields
   - Location-Filter für Boards
