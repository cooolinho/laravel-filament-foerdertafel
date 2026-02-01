# Database Seeders - Dokumentation

Alle Seeder wurden erstellt und können einzeln oder zusammen ausgeführt werden.

## 🎯 Übersicht der Seeder

### 1. LocationSeeder
**Erstellt:** 5 Locations in verschiedenen deutschen Städten

- **Sportplatz Berlin-Mitte** - Hauptstandort in Berlin
- **Sportarena Hamburg** - Zentrale Location in Hamburg  
- **FC München Stadion** - Premium-Standort in München
- **Rhein-Ruhr Sportpark** - Moderner Sportpark in Düsseldorf
- **Elbe-Stadion Dresden** - Traditioneller Standort in Dresden

Jede Location hat vollständige Adresse und Beschreibung.

---

### 2. BoardSeeder
**Erstellt:** 6 Boards verteilt auf alle Locations

- **Berlin:** 2 Boards (Hauptfeld Nord 12x8, Trainingsfeld Süd 8x6)
- **Hamburg:** 1 Board (Arena Hauptfeld 10x10)
- **München:** 1 Board (Premium Stadionfeld 15x10)
- **Düsseldorf:** 1 Board (Sportpark Feld 1 10x8)
- **Dresden:** 1 Board (Elbe-Feld Classic 9x7)

Boards haben unterschiedliche Größen für verschiedene Szenarien.

---

### 3. FieldSeeder
**Erstellt:** Intelligente Feld-Generierung pro Board

#### Feldtypen mit unterschiedlichen Preisen:

**Premium-Felder (teuer):**
- Tor Links/Rechts: 500€/Monat (2x2 Felder)
- Elfmeterpunkt/Mittelkreis: 400€/Monat (2x2 Felder)
- Strafraum Links/Rechts: 350€/Monat (3x2 Felder)

**Mittelfeld (mittel):**
- Mittellinie Mitte/Links/Rechts: 200€/Monat (2x1 Felder)

**Standard-Felder (günstig):**
- Außenlinie/Eckfahne: 100€/Monat (1x1 Felder)
- Standard-Felder: 120€/Monat (1x1 Felder)

#### Features:
- Automatische Positionsberechnung (row/column)
- Realistische Beschreibungen basierend auf Position
- Status-Zuweisung (Premium meist vermietet, Standard meist verfügbar)
- Füllt Board komplett mit Feldern (max. 30 pro Board)
- Prüft Überschneidungen

---

### 4. CustomerSeeder
**Erstellt:** 12 realistische Kunden

#### Kundenprofile:

**Firmenkunden (10):**
1. Max Mustermann - Mustermann GmbH (Berlin) - Langzeitkunde
2. Anna Schmidt - Schmidt Sport Marketing (Hamburg) - Premium-Interessent
3. Thomas Müller - Müller & Söhne AG (München) - VIP-Kunde
4. Sarah Weber - Weber Sportswear (Düsseldorf) - Neue Kundin
5. Julia Fischer - Fischer Consulting (Berlin) - Mehrfachbucher
6. Peter Hoffmann - Hoffmann Automobil GmbH (Hamburg) - Langfrist-Partner
7. Lisa Schneider - Schneider Fitness Center (München) - Trainingsfeld-Interessent
8. Daniel Koch - Koch Immobilien (Düsseldorf) - Zentrale Positionen
9. Sabine Wagner - Wagner Events (Dresden) - Event-Organisatorin
10. Frank Zimmermann - Zimmermann Tech Solutions (Berlin) - Tech-Firma

**Privatkunden (2):**
11. Michael Becker (Dresden) - Kleines Budget
12. Claudia Meyer (Hamburg) - Stammkundin

Alle mit vollständigen Kontaktdaten, verschiedenen Zahlungsmethoden und Notizen.

---

### 5. RentalSeeder
**Erstellt:** 10 verschiedene Rental-Szenarien

#### Rental-Typen:

1. **Langfristige Premium-Vermietung** (Max Mustermann)
   - Tor-Positionen (2 Felder)
   - 6 Monate laufend, 12 Monate verbleibend
   - Status: Active

2. **Mittelfristige Vermietung** (Anna Schmidt)
   - Mittelkreis (1 Feld)
   - 3 Monate laufend, 6 Monate verbleibend
   - Testphase

3. **Premium-Paket** (Thomas Müller)
   - Strafraum + Mittelfeld (3 Felder)
   - 12 Monate laufend, 18 Monate verbleibend
   - VIP-Kunde

4. **Standard-Paket** (Sarah Weber)
   - Mehrere Standard-Felder (4 Felder)
   - Neu, 1 Monat laufend, 3 Monate verbleibend
   - Budget-freundlich

5. **Kurzfristige Einzelvermietung** (Michael Becker)
   - Einzelnes günstiges Feld
   - Gerade gestartet, 1 Monat Laufzeit
   - Privatkunde-Test

6. **Abgeschlossene Vermietung** (Julia Fischer)
   - 2 Felder, vor 1 Monat beendet
   - Status: Completed

7. **Stornierte Vermietung** (Peter Hoffmann)
   - 1 Feld, Budget-Kürzung
   - Status: Cancelled

8. **Unbefristete Vermietung** (Lisa Schneider)
   - 2 Felder, kein Enddatum
   - 4 Monate laufend
   - Monatliche Kündigungsfrist

9. **Zukünftige Buchung** (Daniel Koch)
   - 2 Felder, startet in 1 Monat
   - Vorab-Buchung, Status: Reserved
   - 6 Monate Laufzeit

10. **Event-Paket** (Sabine Wagner)
    - 5 kleine Felder (1x1)
    - 2 Wochen alt, 4 Monate Laufzeit
    - Event-Package

#### Features:
- Realistische Zeiträume (vergangen, laufend, zukünftig)
- Verschiedene Status (Active, Completed, Cancelled)
- Automatische Preisberechnung aus Feldern
- Field-Status wird automatisch aktualisiert
- Sinnvolle Notizen zu jedem Rental

---

## 🚀 Verwendung

### Alle Seeder ausführen:
```bash
php artisan db:seed
```

### Einzelne Seeder ausführen:
```bash
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=BoardSeeder
php artisan db:seed --class=FieldSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=RentalSeeder
```

### Datenbank zurücksetzen und neu seeden:
```bash
php artisan migrate:fresh --seed
```

### Nur bestimmte Seeder nach Fresh:
```bash
php artisan migrate:fresh
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=BoardSeeder
```

---

## 📊 Generierte Daten-Statistik

Nach dem Seeding hast du:

- ✅ **1 Admin-User** (admin@example.com)
- ✅ **5 Locations** (Berlin, Hamburg, München, Düsseldorf, Dresden)
- ✅ **6 Boards** (verschiedene Größen: 8x6 bis 15x10)
- ✅ **~180-300 Fields** (je nach Board-Größe)
- ✅ **12 Customers** (10 Firmen, 2 Privat)
- ✅ **10 Rentals** (verschiedene Status und Zeiträume)

---

## 🎨 Test-Szenarien

Die Seeder erstellen folgende Test-Szenarien:

### Filament Dashboard
- Verschiedene Locations zum Filtern
- Boards mit unterschiedlichen Auslastungen
- Felder in verschiedenen Status (Available/Rented/Reserved)
- Kunden mit/ohne Firmennamen
- Aktive, abgeschlossene und stornierte Vermietungen

### Status-Verteilung
- **Fields:** ~30% vermietet, ~60% verfügbar, ~10% reserviert
- **Rentals:** ~70% aktiv, ~20% abgeschlossen, ~10% storniert

### Preis-Spannen
- Günstigste Felder: 100€/Monat
- Standard-Felder: 120€/Monat
- Mittelfeld: 200€/Monat
- Premium-Felder: 350-400€/Monat
- Top-Felder (Tor): 500€/Monat

### Zeiträume
- Abgelaufene Verträge (Completed)
- Laufende Verträge (verschiedene Restlaufzeiten)
- Zukünftige Buchungen (Reserved)
- Unbefristete Verträge (kein end_date)

---

## 🔄 Reihenfolge beachten!

Die Seeder müssen in dieser Reihenfolge ausgeführt werden:

1. **LocationSeeder** (zuerst)
2. **BoardSeeder** (benötigt Locations)
3. **FieldSeeder** (benötigt Boards)
4. **CustomerSeeder** (unabhängig)
5. **RentalSeeder** (benötigt Fields + Customers)

Der `DatabaseSeeder` führt sie automatisch in der richtigen Reihenfolge aus!

---

## ⚠️ Hinweise

- Bei erneutem Seeding werden Daten dupliziert (falls keine `migrate:fresh` verwendet wird)
- Field-Status werden vom RentalSeeder aktualisiert
- Einige Rentals haben bewusst kein Enddatum (unbefristet)
- Premium-Felder sind meist schon vermietet
- Beschreibungen sind kontextabhängig und realistisch

---

## 🎯 Nächste Schritte

Nach dem Seeding kannst du:

1. Filament Admin-Panel öffnen: `php artisan serve`
2. Login mit: admin@example.com
3. Alle Resources durchstöbern
4. Filter und Sortierungen testen
5. Neue Datensätze erstellen
6. Relationships überprüfen
7. Status-Badges testen
8. Infolists anschauen

Viel Erfolg beim Testen! 🚀
