# Inquiry System - Implementation Summary

## ✅ Implementierte Komponenten

### 1. Datenbank

#### Migrationen
- ✅ `2026_02_02_032110_create_inquiries_table.php` - Haupttabelle für Anfragen
  - board_id (Foreign Key)
  - Kundendaten (name, email, phone)
  - Zeitraum (start_date, end_date)
  - requested_fields (JSON Array mit Field IDs)
  - Status (pending, approved, rejected, converted)
  - message & admin_notes
  - rental_id (Optional, nach Konvertierung)

- ✅ `2026_02_02_032115_create_inquiry_field_table.php` - Pivot-Tabelle
  - inquiry_id
  - field_id
  - timestamps

### 2. Models

#### Inquiry Model (app/Models/Inquiry.php)
- ✅ Alle Konstanten für Feldnamen
- ✅ Status-Konstanten (PENDING, APPROVED, REJECTED, CONVERTED)
- ✅ Fillable-Felder
- ✅ Casts (dates, JSON array)
- ✅ Beziehungen:
  - belongsTo(Board)
  - belongsTo(Rental)
  - belongsToMany(Field) via inquiry_field
- ✅ Helper-Methoden:
  - isPending()
  - isApproved()
  - isConverted()
  - getStatusLabel()

#### Aktualisierte Models
- ✅ Board: hasMany(Inquiry)
- ✅ Rental: hasMany(Inquiry)
- ✅ Field: belongsToMany(Inquiry)

### 3. Filament Admin Resources

#### InquiryResource (app/Filament/Admin/Resources/Inquiries/)
- ✅ Navigation-Label: "Anfragen"
- ✅ Navigation-Icon: RectangleStack
- ✅ Navigation-Sort: 3
- ✅ Model-Labels (deutsch)

#### InquiryForm (Schemas/InquiryForm.php)
- ✅ Board-Auswahl (reactive)
- ✅ Feld-Auswahl (dynamisch basierend auf Board)
- ✅ Kundendaten (Name, E-Mail, Telefon)
- ✅ Datums-Picker (Start/End mit Validierung)
- ✅ Status-Auswahl
- ✅ Message & Admin-Notizen (Textarea)
- ✅ Rental-Verknüpfung (optional)

#### InquiryInfolist (Schemas/InquiryInfolist.php)
- ✅ Status-Badge mit Farben
- ✅ Board-Info
- ✅ Zeitraum-Anzeige
- ✅ Liste der angeforderten Felder
- ✅ Kundendaten mit Copy-Funktion
- ✅ Nachrichten-Sektion
- ✅ Rental-Verknüpfung (conditional)

#### InquiriesTable (Tables/InquiriesTable.php)
- ✅ Spalten: ID, Board, Kundenname, E-Mail, Daten, Status
- ✅ Status-Badge mit Farben
- ✅ Filter: Status, Board
- ✅ Actions: View, Edit
- ✅ Bulk-Actions: Delete
- ✅ Default-Sort: created_at DESC

#### Pages
- ✅ CreateInquiry: afterCreate Hook für Field-Sync
- ✅ EditInquiry: 
  - mutateFormDataBeforeFill für Field-Loading
  - afterSave Hook für Field-Sync
- ✅ ViewInquiry:
  - Convert-to-Rental Action
  - Customer-Erstellung/Zuordnung
  - Automatic Rental-Erstellung
  - Field-Attachment
  - Price-Calculation
  - Status-Update
  - Redirect zu Rental

### 4. Seeders

#### InquirySeeder (database/seeders/InquirySeeder.php)
- ✅ 5 Anfragen pro Board
- ✅ Zufällige Feld-Auswahl (1-3 Felder)
- ✅ Realistische Daten (Faker)
- ✅ Verschiedene Status
- ✅ Optional: Message & Admin-Notes
- ✅ Field-Attachment via Pivot

#### DatabaseSeeder
- ✅ InquirySeeder hinzugefügt
- ✅ Info-Message aktualisiert

### 5. Dokumentation

- ✅ INQUIRY_SYSTEM.md - Vollständige Dokumentation
  - Überblick & Features
  - Datenbank-Schema
  - Model-Beziehungen
  - Status-Workflow
  - Konvertierungs-Prozess
  - Admin-Funktionen
  - Validierung
  - Frontend-Integration (TODO)
  - Best Practices
  - Erweiterungsmöglichkeiten

- ✅ README.md aktualisiert mit Inquiry-Link

## 🎯 Hauptfunktionalität

### Admin kann:
1. ✅ Alle Anfragen ansehen und filtern
2. ✅ Anfragen manuell erstellen/bearbeiten
3. ✅ Status ändern (pending → approved/rejected)
4. ✅ Admin-Notizen hinzufügen
5. ✅ Anfragen in Vermietungen umwandeln:
   - Automatische Kunden-Erstellung/-Zuordnung
   - Rental mit allen Feldern erstellen
   - Automatische Preis-Berechnung
   - Status auf "converted" setzen
   - Weiterleitung zur Vermietung

### Konvertierungs-Prozess (Convert to Rental)
1. ✅ Prüft ob Kunde existiert (via E-Mail)
2. ✅ Erstellt neuen Kunden oder verwendet bestehenden
3. ✅ Erstellt Rental mit korrekten Daten
4. ✅ Verknüpft alle angeforderten Felder
5. ✅ Berechnet Gesamtpreis
6. ✅ Aktualisiert Inquiry-Status
7. ✅ Verknüpft Rental mit Inquiry
8. ✅ Success-Notification
9. ✅ Redirect zu Rental-Detailansicht

## 📋 Nächste Schritte (Frontend)

### TODO: InquiryPage Frontend-Implementation
Die InquiryPage muss noch im Frontend implementiert werden:

1. **Board-Darstellung**
   - Board-Raster wie in BoardPage anzeigen
   - Felder anklickbar machen
   - Mehrfachauswahl ermöglichen
   - Visuelle Hervorhebung ausgewählter Felder

2. **Formular**
   - Kontaktdaten (Name, E-Mail, Telefon)
   - Datums-Auswahl (Start/End)
   - Optional: Nachricht
   - Submit-Button

3. **Validierung**
   - Frontend-Validierung
   - API-Endpunkt für Anfragen-Erstellung
   - Fehlerbehandlung

4. **User Experience**
   - Bestätigungsseite nach Submit
   - E-Mail-Bestätigung (optional)
   - Verlinkung von BoardPage

5. **Route & Controller**
   - Route: `/board/{board}/inquiry`
   - Controller für API-Endpunkt
   - Validation Request

## 🔧 Verwendung

### Migrationen ausführen
```bash
cd laravel
php artisan migrate
```

### Seeders ausführen
```bash
php artisan db:seed
# Oder nur Inquiry-Seeder:
php artisan db:seed --class=InquirySeeder
```

### Admin-Bereich
- Navigiere zu "Anfragen" im Admin-Panel
- Neue Anfrage erstellen oder bestehende ansehen
- Anfrage in Vermietung umwandeln

## 📊 Status-Übersicht

| Status | Farbe | Bedeutung |
|--------|-------|-----------|
| Pending | Warning (Gelb) | Neu, wartet auf Bearbeitung |
| Approved | Success (Grün) | Genehmigt, kann konvertiert werden |
| Rejected | Danger (Rot) | Abgelehnt |
| Converted | Info (Blau) | In Vermietung umgewandelt |

## ✨ Features

- ✅ Vollständiges CRUD für Anfragen
- ✅ One-Click Konvertierung zu Rental
- ✅ Automatische Kunden-Verwaltung
- ✅ Field-Synchronisation via Pivot
- ✅ Status-Workflow
- ✅ Filament-Integration
- ✅ Deutsche Labels
- ✅ Responsive Design
- ✅ Notifications
- ✅ Test-Daten via Seeder

## 🎨 UI/UX Highlights

- Farbcodierte Status-Badges
- Copy-to-Clipboard für Kontaktdaten
- Dynamische Feld-Auswahl basierend auf Board
- Strukturierte Info-Layouts
- Hilfe-Texte und Tooltips
- Bestätigungs-Dialoge
- Success-Notifications
- Automatische Weiterleitungen
