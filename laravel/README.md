# Fördertafel Laravel Application

## 🎯 Projektidee

Dieses Projekt ist ein digitales Board-Management-System für Werbeflächen auf Fußballfeldern.

### Konzept
Stell dir ein Schachbrettmuster vor - ein Fußballfeld, das in einzelne Felder unterteilt ist. Jedes Feld kann von Personen oder Firmen (Customers) gemietet werden. 

**Besonderheiten:**
- Jeder Customer kann eines oder mehrere **aneinanderhängende** Felder mieten
- Felder haben verschiedene Größen (z.B. 2x2, 3x1, etc.)
- Premium-Positionen wie **"Tor"**, **"Strafraum"** oder **"Mittelkreis"** haben höhere Preise
- Standard-Felder an Außenlinien sind günstiger

### Preis-Beispiele
- 🥇 **Tor:** 500€/Monat (höchste Aufmerksamkeit)
- 🥈 **Strafraum:** 350€/Monat (Action-Zone)
- 🥉 **Mittelkreis:** 400€/Monat (zentrale Position)
- 🟢 **Standard-Felder:** 100-120€/Monat

---

## 📚 Dokumentation

Die vollständige Dokumentation findest du im **`docs/`** Verzeichnis:

### 🗂️ Datenbank & Models
- **[DATABASE_SCHEMA.md](docs/DATABASE_SCHEMA.md)**
  - Vollständiges Datenbank-Schema
  - Alle Entitäten und deren Beziehungen
  - Beispiel-Anwendungsfälle
  - Migration-Struktur

- **[MODEL_CONSTANTS.md](docs/MODEL_CONSTANTS.md)**
  - Model-Konstanten Pattern
  - Verwendung in Forms, Tables und Migrations
  - Best Practices für Type Safety
  - Code-Beispiele

### 🎨 Filament Resources
- **[FILAMENT_RESOURCES_OVERVIEW.md](docs/FILAMENT_RESOURCES_OVERVIEW.md)**
  - Übersicht aller Resources (Location, Board, Field, Customer, Rental)
  - Form-Komponenten und Validierung
  - Table-Konfiguration mit Filtern
  - Infolist-Aufbau
  - Status-Badges und Money-Formatting

- **[NAVIGATION_DOCUMENTATION.md](docs/NAVIGATION_DOCUMENTATION.md)**
  - Navigation-Gruppen und Sortierung
  - Icon-Auswahl (MapPin, ViewColumns, Squares2x2, etc.)
  - Intelligente Badges (Verfügbarkeit, Status)
  - Tooltips und Farbcodierung

### 🌱 Demo-Daten
- **[SEEDERS_DOCUMENTATION.md](docs/SEEDERS_DOCUMENTATION.md)**
  - Übersicht aller Seeder
  - 5 Locations in deutschen Städten
  - 6 Boards mit verschiedenen Größen
  - ~200 Fields mit realistischen Preisen
  - 12 Customers (Firmen + Privat)
  - 10 Rentals (verschiedene Szenarien)
  - Verwendung und Ausführung

---

## 🚀 Quick Start

### 1. Datenbank migrieren
```bash
php artisan migrate
```

### 2. Demo-Daten laden
```bash
php artisan db:seed
```

Oder alles auf einmal:
```bash
php artisan migrate:fresh --seed
```

### 3. Admin-Panel öffnen
```
URL:      http://localhost/admin/login
E-Mail:   admin@example.com
Password: secret
```

---

## 🛠️ Entwicklung

### Neue Resource erstellen
```bash
php artisan filament:resource --generate --record-title-attribute=name --view ResourceName
```

### Neue Migration erstellen
```bash
php artisan make:migration create_table_name_table
```

### Neuen Seeder erstellen
```bash
php artisan make:seeder TableNameSeeder
```

### Tests ausführen
```bash
php artisan test
```

### Code-Style prüfen
```bash
./vendor/bin/pint
```

---

## 📊 Projekt-Statistiken

Nach dem Seeding:
- ✅ **5 Locations** - Standorte in deutschen Großstädten
- ✅ **6 Boards** - Verschiedene Größen (8x6 bis 15x10)
- ✅ **~200 Fields** - Premium bis Standard-Positionen
- ✅ **12 Customers** - Mix aus Firmen (10) und Privat (2)
- ✅ **10 Rentals** - Active, Completed, Cancelled

---

## 🗂️ Entitäten-Übersicht

### Location
Standorte der Boards (z.B. "Sportplatz Berlin-Mitte")
- Name, Adresse, Beschreibung
- Hat mehrere Boards

### Board
Das eigentliche Spielfeld-Grid
- Gehört zu einer Location
- Rows × Columns Konfiguration
- Hat viele Fields

### Field
Einzelnes Feld auf dem Board
- Position (Row, Column)
- Größe (Width, Height)
- Preis pro Monat
- Status: Available, Rented, Reserved
- Premium-Kategorien (Tor, Strafraum, etc.)

### Customer
Kunde (Person oder Firma)
- Name, Firma, E-Mail, Telefon
- Adresse, Zahlungsmethode
- Hat mehrere Rentals

### Rental
Vermietung von Feldern
- Customer-Zuordnung
- Start- und Enddatum
- Mehrere Fields pro Rental
- Gesamtpreis
- Status: Active, Completed, Cancelled

---

## 🎨 Navigation-Struktur

### Board Management
1. 📍 **Locations** - Standortverwaltung
2. 📊 **Boards** - Board-Verwaltung
3. 🎲 **Fields** - Feld-Verwaltung mit Verfügbarkeits-Badge

### Customer Management
1. 👥 **Customers** - Kundenverwaltung
2. 📄 **Rentals** - Vermietungen (nur aktive im Badge)

---

## 🔑 Model-Konstanten Beispiel

Alle Models verwenden Konstanten für Properties:

```php
// Field Model
const string board_id = 'board_id';
const string name = 'name';
const string status = 'status';
const string STATUS_AVAILABLE = 'available';
const string STATUS_RENTED = 'rented';

// Verwendung
$field->update([Field::status => Field::STATUS_RENTED]);
```

**Vorteile:**
- ✅ Type Safety & Autocomplete
- ✅ Refactoring-sicher
- ✅ Keine Tippfehler
- ✅ Einheitliche Verwendung

---

## 🧪 Test-Szenarien

Die Demo-Daten enthalten realistische Szenarien:

- ✅ **Langfristige Premium-Vermietungen** (VIP-Kunden)
- ✅ **Kurzfristige Tests** (1 Monat)
- ✅ **Unbefristete Rentals** (monatlich kündbar)
- ✅ **Zukünftige Buchungen** (Reserved Status)
- ✅ **Abgeschlossene Verträge** (Completed)
- ✅ **Stornierungen** (Cancelled)

---

## 📈 Nächste Schritte

### Mögliche Erweiterungen
- [ ] Dashboard mit Statistiken (Umsatz, Auslastung)
- [ ] Visuelle Board-Darstellung (Grid-View)
- [ ] Automatische Preisberechnung bei Rental-Erstellung
- [ ] Automatisches Status-Update der Fields
- [ ] PDF-Export für Verträge
- [ ] Email-Benachrichtigungen
- [ ] Multi-Tenancy für mehrere Organisationen
- [ ] API für externe Integration

### Performance-Optimierung
- [ ] Eager Loading für Relationships
- [ ] Query-Caching
- [ ] Index-Optimierung

---

## 🔗 Hilfreiche Links

- [Filament 4 Documentation](https://filamentadmin.com/)
- [Laravel 12 Documentation](https://laravel.com/docs)
- [Heroicons (Icons)](https://heroicons.com/)
- [Tailwind CSS](https://tailwindcss.com/)

---

## 💡 Tipps

### Filament Forms
Alle Forms verwenden Model-Konstanten und sind in Sections unterteilt.

### Tables
Tables haben Filter, Sortierung und intelligente Badges mit Farbcodierung.

### Infolists
Infolists zeigen detaillierte Informationen mit RepeatableEntries für Relations.

### Seeder
Seeder können einzeln oder alle zusammen ausgeführt werden. Siehe [SEEDERS_DOCUMENTATION.md](docs/SEEDERS_DOCUMENTATION.md).

---

**Viel Erfolg beim Entwickeln! 🚀**

