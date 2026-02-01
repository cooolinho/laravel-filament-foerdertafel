# 📚 Dokumentations-Übersicht

Willkommen zur Dokumentation des Laravel Filament Fördertafel Projekts!

## 📖 Inhaltsverzeichnis

### 1. Datenbank & Architecture
- **[DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)** - Vollständige Datenbank-Dokumentation
  - Entitäten: Location, Board, Field, Customer, Rental
  - Beziehungen und Foreign Keys
  - Beispiel-Anwendungsfälle
  - Migration-Übersicht

- **[MODEL_CONSTANTS.md](MODEL_CONSTANTS.md)** - Model-Konstanten Pattern
  - Warum Konstanten verwenden?
  - Übersicht aller Model-Konstanten
  - Verwendung in Forms, Tables, Migrations
  - Best Practices

### 2. Filament Resources
- **[FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md)** - Complete Resource Guide
  - LocationResource (Forms, Tables, Infolists)
  - BoardResource
  - FieldResource
  - CustomerResource
  - RentalResource
  - Alle Komponenten und Features

- **[NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md)** - Navigation & UI
  - Navigation-Gruppen
  - Icon-Auswahl und Bedeutung
  - Intelligente Badges
  - Farbcodierung
  - Tooltips

### 3. Demo-Daten
- **[SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md)** - Seeder Guide
  - LocationSeeder (5 Standorte)
  - BoardSeeder (6 Boards)
  - FieldSeeder (intelligente Field-Generierung)
  - CustomerSeeder (12 Kunden)
  - RentalSeeder (10 Szenarien)
  - Verwendung und Beispiele

### 4. Entwickler-Referenz
- **[COPILOT_PROMPTS.md](COPILOT_PROMPTS.md)** - AI Assistant Prompts
  - Hilfreiche Prompts für die Entwicklung
  - Best Practices
  - Code-Generierung

---

## 🚀 Quick Start Guide

### Für neue Entwickler:

1. **Start hier:** [../README.md](../README.md) - Laravel Application Übersicht
2. **Verstehe die Datenbank:** [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)
3. **Lerne das Pattern:** [MODEL_CONSTANTS.md](MODEL_CONSTANTS.md)
4. **Erkunde die UI:** [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md)
5. **Lade Demo-Daten:** [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md)

### Für Frontend-Entwicklung:

1. [NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md) - Navigation verstehen
2. [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md) - UI-Komponenten

### Für Backend-Entwicklung:

1. [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) - Schema verstehen
2. [MODEL_CONSTANTS.md](MODEL_CONSTANTS.md) - Code-Standards
3. [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md) - Test-Daten

---

## 📊 Projekt-Übersicht

### Entitäten

```
Location (Standort)
    └── Board (Spielfeld-Grid)
        └── Field (Einzelnes Feld)
            └── Rental (Vermietung)
                └── Customer (Kunde)
```

### Status-Übersicht

**Field Status:**
- 🟢 `available` - Verfügbar für Vermietung
- 🔴 `rented` - Aktuell vermietet
- 🟡 `reserved` - Reserviert (zukünftige Buchung)

**Rental Status:**
- 🟢 `active` - Aktive Vermietung
- ⚪ `completed` - Abgeschlossen
- 🔴 `cancelled` - Storniert

---

## 🎯 Häufig verwendete Szenarien

### Neues Field erstellen
→ Siehe [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md#beispiel-anwendungsfälle)

### Field vermieten
→ Siehe [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md#ein-feld-mieten)

### Resource anpassen
→ Siehe [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md)

### Navigation ändern
→ Siehe [NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md#anpassungsmöglichkeiten)

### Test-Daten generieren
→ Siehe [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md#verwendung)

---

## 🎨 Code-Style

### Model-Konstanten verwenden
```php
// ✅ Richtig
$field->update([Field::status => Field::STATUS_RENTED]);

// ❌ Falsch
$field->update(['status' => 'rented']);
```

### Forms strukturieren
```php
Section::make('Title')
    ->schema([
        TextInput::make(Model::property_name),
    ])
    ->columns(2);
```

Mehr Details: [MODEL_CONSTANTS.md](MODEL_CONSTANTS.md)

---

## 📈 Metriken & KPIs

Die Navigation zeigt wichtige KPIs als Badges:

- **Locations:** Gesamtzahl
- **Boards:** Gesamtzahl
- **Fields:** Verfügbar/Gesamt mit Ampel-System
- **Customers:** Gesamtzahl mit Wachstums-Indikator
- **Rentals:** Anzahl aktiver Vermietungen

Details: [NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md#badge-logik)

---

## 🔧 Wartung & Updates

### Dokumentation aktualisieren

Wenn du Änderungen machst, aktualisiere bitte die entsprechende Dokumentation:

- **Neue Entität:** [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) ergänzen
- **Neue Konstante:** [MODEL_CONSTANTS.md](MODEL_CONSTANTS.md) aktualisieren
- **Neue Resource:** [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md) erweitern
- **Neuer Seeder:** [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md) dokumentieren
- **UI-Änderung:** [NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md) anpassen

---

## 🐛 Troubleshooting

### Seeder-Fehler
→ Prüfe [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md#reihenfolge-beachten)

### Migration-Fehler
→ Prüfe [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md#migrationen-ausführen)

### Resource-Fehler
→ Prüfe [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md)

---

## 🤝 Beitragen

Beim Hinzufügen neuer Features:

1. ✅ Model-Konstanten definieren
2. ✅ Migration erstellen
3. ✅ Seeder hinzufügen
4. ✅ Resource konfigurieren
5. ✅ Navigation anpassen
6. ✅ Dokumentation aktualisieren

---

## 📞 Support

Bei Fragen zu:
- **Datenbank:** Siehe [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md)
- **Code-Pattern:** Siehe [MODEL_CONSTANTS.md](MODEL_CONSTANTS.md)
- **UI/UX:** Siehe [NAVIGATION_DOCUMENTATION.md](NAVIGATION_DOCUMENTATION.md)
- **Test-Daten:** Siehe [SEEDERS_DOCUMENTATION.md](SEEDERS_DOCUMENTATION.md)
- **Resources:** Siehe [FILAMENT_RESOURCES_OVERVIEW.md](FILAMENT_RESOURCES_OVERVIEW.md)

---

**Letzte Aktualisierung:** 2026-02-01

**Dokumentierte Version:** Laravel 12 + Filament 4

**Maintainer:** Entwicklerteam Fördertafel
