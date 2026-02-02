# Inquiry System Documentation

## Überblick

Das Inquiry-System ermöglicht es Kunden, Anfragen für Feldvermietungen zu stellen, ohne direkt im Frontend buchen zu können. Admins können diese Anfragen im Admin-Bereich prüfen und in Vermietungen umwandeln.

## Features

### Kundenansicht (Frontend)
- Kunden können Anfragen über die InquiryPage stellen
- Auswahl mehrerer Felder im Board-Raster
- Angabe von Zeitraum (Start- und Enddatum)
- Eingabe von Kontaktinformationen (Name, E-Mail, Telefon)
- Optional: Nachricht an Admin

### Admin-Bereich
- Übersicht aller Anfragen mit Filteroptionen
- Status-Management (Ausstehend, Genehmigt, Abgelehnt, Vermietung erstellt)
- Detailansicht mit allen Anfrageinformationen
- Umwandlung von Anfragen in Vermietungen per Klick
- Automatische Kundenerstellung oder -zuordnung

## Datenbank-Schema

### inquiries Tabelle
- `id`: Primary Key
- `board_id`: Foreign Key zu boards
- `customer_name`: Kundenname
- `customer_email`: E-Mail-Adresse
- `customer_phone`: Telefonnummer (optional)
- `start_date`: Gewünschtes Startdatum
- `end_date`: Gewünschtes Enddatum
- `requested_fields`: JSON-Array mit Field-IDs
- `status`: Status (pending, approved, rejected, converted)
- `message`: Nachricht vom Kunden (optional)
- `admin_notes`: Interne Admin-Notizen (optional)
- `rental_id`: Foreign Key zu rentals (nach Konvertierung)
- `created_at`, `updated_at`: Timestamps

### inquiry_field Pivot-Tabelle
- `id`: Primary Key
- `inquiry_id`: Foreign Key zu inquiries
- `field_id`: Foreign Key zu fields
- `created_at`, `updated_at`: Timestamps

## Model-Beziehungen

### Inquiry Model
- `belongsTo(Board::class)`: Zugehöriges Board
- `belongsTo(Rental::class)`: Zugehörige Vermietung (optional)
- `belongsToMany(Field::class)`: Angeforderte Felder

### Board Model
- `hasMany(Inquiry::class)`: Alle Anfragen für dieses Board

### Field Model
- `belongsToMany(Inquiry::class)`: Anfragen für dieses Feld

### Rental Model
- `hasMany(Inquiry::class)`: Anfragen, die zu dieser Vermietung konvertiert wurden

## Status-Workflow

1. **pending (Ausstehend)**: Neue Anfrage von Kunde
2. **approved (Genehmigt)**: Admin hat Anfrage genehmigt
3. **rejected (Abgelehnt)**: Admin hat Anfrage abgelehnt
4. **converted (Vermietung erstellt)**: Anfrage wurde in Vermietung umgewandelt

## Konvertierung zu Vermietung

### Prozess
1. Admin öffnet Anfrage-Detailansicht
2. Klick auf "In Vermietung umwandeln"
3. System prüft, ob Kunde bereits existiert (anhand E-Mail)
4. Falls nicht: Neuer Kunde wird erstellt
5. Neue Vermietung wird erstellt mit:
   - Zugehörigem Kunden
   - Start- und Enddatum aus Anfrage
   - Zugehörigen Feldern aus Anfrage
   - Automatisch berechneter Gesamtpreis
6. Anfrage-Status wird auf "converted" gesetzt
7. `rental_id` wird in Anfrage gespeichert
8. Admin wird zu Vermietungs-Detailansicht weitergeleitet

## Admin-Funktionen

### Inquiry Resource (InquiryResource)
- **Liste**: Alle Anfragen mit Filteroptionen nach Status und Board
- **Erstellen**: Manuelle Erstellung von Anfragen (z.B. telefonische Anfragen)
- **Bearbeiten**: Bearbeitung von Anfragendaten und Admin-Notizen
- **Ansehen**: Detailansicht mit Konvertierungs-Action

### Filament-Komponenten

#### InquiryForm
- Board-Auswahl mit dynamischer Feld-Liste
- Kundendaten-Eingabe
- Datums-Auswahl mit Validierung
- Status-Auswahl
- Nachricht und Admin-Notizen

#### InquiryInfolist
- Strukturierte Anzeige aller Anfrageinformationen
- Badge für Status mit farblicher Kennzeichnung
- Auflistung aller angeforderten Felder
- Kundendaten mit Kopier-Funktion
- Verlinkung zur zugehörigen Vermietung

#### InquiriesTable
- Spalten: ID, Board, Kundenname, E-Mail, Start-/Enddatum, Status
- Filter: Status, Board
- Sortierung: Standard nach Erstelldatum (neueste zuerst)
- Actions: Ansehen, Bearbeiten, Löschen

## Validierung

### Frontend (InquiryPage)
- Mindestens ein Feld muss ausgewählt sein
- Start- und Enddatum sind Pflichtfelder
- Enddatum muss nach oder gleich Startdatum sein
- Name und E-Mail sind Pflichtfelder
- E-Mail muss gültig sein

### Backend (Admin)
- Board ist Pflichtfeld
- Mindestens ein Feld in `requested_fields`
- Gültiges Datums-Format
- Status muss einer der definierten Werte sein

## Frontend-Integration (TODO)

### InquiryPage
Die InquiryPage muss noch implementiert werden mit:
- Board-Darstellung wie in BoardPage
- Feld-Auswahl (Mehrfachauswahl, auch nebeneinander)
- Formular für Kontaktdaten und Zeitraum
- Absenden der Anfrage
- Bestätigungsnachricht nach Absenden

### Routing
- Route: `/board/{board}/inquiry`
- Verlinkung von BoardPage zur InquiryPage
- Nach Absenden: Bestätigungsseite oder Weiterleitung

## Best Practices

### Admin-Workflow
1. Regelmäßige Überprüfung neuer Anfragen
2. Status-Update nach Prüfung (genehmigt/abgelehnt)
3. Bei Genehmigung: Umwandlung in Vermietung
4. Admin-Notizen für interne Kommunikation nutzen

### Kunden-Kommunikation
- E-Mail-Benachrichtigungen bei Status-Änderungen (TODO)
- Kopie der Anfrage an Kunden-E-Mail (TODO)
- Bestätigung nach Konvertierung in Vermietung (TODO)

## Erweiterungsmöglichkeiten

1. **E-Mail-Benachrichtigungen**
   - Bei neuer Anfrage an Admin
   - Bei Status-Änderung an Kunde
   - Bei Konvertierung in Vermietung

2. **Preis-Kalkulation in Anfrage**
   - Automatische Berechnung des Preises basierend auf ausgewählten Feldern
   - Anzeige in Anfrage-Details

3. **Verfügbarkeits-Prüfung**
   - Automatische Prüfung, ob Felder im gewünschten Zeitraum verfügbar sind
   - Warnung bei Konflikten

4. **Anfrage-Kommentare**
   - Thread-basierte Kommunikation zwischen Admin und Kunde
   - Historie aller Interaktionen

5. **Bulk-Actions**
   - Mehrere Anfragen gleichzeitig genehmigen/ablehnen
   - Massenbearbeitung von Status

## Technische Details

### Model-Konstanten
Alle Feldnamen sind als Konstanten definiert für bessere Wartbarkeit:
```php
Inquiry::board_id
Inquiry::customer_name
Inquiry::customer_email
// etc.
```

### Status-Konstanten
```php
Inquiry::STATUS_PENDING
Inquiry::STATUS_APPROVED
Inquiry::STATUS_REJECTED
Inquiry::STATUS_CONVERTED
```

### Helper-Methoden
```php
$inquiry->isPending()     // Prüft ob Status pending
$inquiry->isApproved()    // Prüft ob Status approved
$inquiry->isConverted()   // Prüft ob Status converted
$inquiry->getStatusLabel() // Gibt deutschen Status-Label zurück
```
