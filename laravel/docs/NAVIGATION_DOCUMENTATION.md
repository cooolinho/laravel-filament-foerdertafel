# Filament Navigation & Resources - Dokumentation

Alle Resources wurden mit sinnvoller Navigation, Gruppierung, passenden Icons und Badges erweitert.

## 🎯 Navigation-Struktur

### Gruppe 1: Board Management
Verwaltung der physischen Boards und deren Standorte.

#### 1. Locations 📍
- **Icon:** MapPin (Standort-Pin)
- **Sort Order:** 1 (erstes in der Gruppe)
- **Badge:** Gesamtzahl der Locations
- **Badge Color:** Success (grün)
- **Beschreibung:** Verwaltung aller Standorte

#### 2. Boards 📊
- **Icon:** ViewColumns (Spalten/Grid)
- **Sort Order:** 2
- **Badge:** Gesamtzahl der Boards
- **Badge Color:** Info (blau)
- **Beschreibung:** Verwaltung aller Boards

#### 3. Fields 🎲
- **Icon:** Squares2x2 (Grid-Felder)
- **Sort Order:** 3 (letztes in der Gruppe)
- **Badge:** Verfügbar/Gesamt (z.B. "45/120")
- **Badge Color:** 
  - 🟢 Success (≥50% verfügbar)
  - 🟡 Warning (25-49% verfügbar)
  - 🔴 Danger (<25% verfügbar)
  - ⚪ Gray (keine Felder)
- **Tooltip:** "Available / Total Fields"
- **Beschreibung:** Intelligente Anzeige der Verfügbarkeit

---

### Gruppe 2: Customer Management
Verwaltung von Kunden und deren Vermietungen.

#### 1. Customers 👥
- **Icon:** UserGroup (Personen-Gruppe)
- **Sort Order:** 1
- **Badge:** Gesamtzahl der Kunden
- **Badge Color:**
  - 🟢 Success (≥20 Kunden)
  - 🔵 Info (10-19 Kunden)
  - 🟡 Warning (5-9 Kunden)
  - ⚪ Gray (<5 Kunden)
- **Beschreibung:** Dynamische Farbe basierend auf Kundenzahl

#### 2. Rentals 📄
- **Icon:** DocumentText (Dokument)
- **Sort Order:** 2
- **Badge:** Anzahl aktiver Vermietungen
- **Badge Color:** Success (grün)
- **Tooltip:** "Active Rentals"
- **Beschreibung:** Zeigt nur aktive Rentals

---

## 🎨 Icon-Auswahl Begründung

| Resource | Icon | Begründung |
|----------|------|------------|
| **Location** | MapPin | Klassisches Symbol für Standorte/Orte |
| **Board** | ViewColumns | Symbolisiert Grid/Spalten-Layout eines Boards |
| **Field** | Squares2x2 | Perfekt für einzelne Felder im Grid |
| **Customer** | UserGroup | Mehrere Personen für Kundenverwaltung |
| **Rental** | DocumentText | Vertrag/Dokument für Vermietungen |

---

## 📊 Badge-Logik

### Location Badge
```php
Badge: Gesamtzahl (z.B. "5")
Color: Success (grün)
```
Einfache Anzeige der Location-Anzahl.

---

### Board Badge
```php
Badge: Gesamtzahl (z.B. "6")
Color: Info (blau)
```
Zeigt alle verfügbaren Boards.

---

### Field Badge (Intelligent)
```php
Badge: "45/120" (verfügbar/gesamt)
Color: Dynamisch basierend auf Verfügbarkeit
Tooltip: "Available / Total Fields"
```

**Farblogik:**
- ≥50% verfügbar → 🟢 Grün (genug Kapazität)
- 25-49% verfügbar → 🟡 Gelb (mittlere Auslastung)
- <25% verfügbar → 🔴 Rot (hohe Auslastung)

Dies gibt sofort einen Überblick über die Board-Auslastung!

---

### Customer Badge
```php
Badge: Gesamtzahl (z.B. "12")
Color: Dynamisch basierend auf Kundenzahl
```

**Farblogik:**
- ≥20 Kunden → 🟢 Grün (gesunder Kundenstamm)
- 10-19 Kunden → 🔵 Blau (wachsend)
- 5-9 Kunden → 🟡 Gelb (Aufbauphase)
- <5 Kunden → ⚪ Grau (Start)

Zeigt den Wachstumsstatus des Geschäfts.

---

### Rental Badge
```php
Badge: Anzahl aktiver Rentals (z.B. "8")
Color: Success (grün)
Tooltip: "Active Rentals"
```

Fokus auf **aktive** Vermietungen, nicht auf abgeschlossene oder stornierte.

---

## 🗂️ Gruppierung & Sortierung

### Board Management (Sort: 1-3)
Logische Reihenfolge von groß nach klein:
1. **Locations** (Standorte) → Wo?
2. **Boards** (Boards) → Was?
3. **Fields** (Felder) → Details

### Customer Management (Sort: 1-2)
1. **Customers** (Kunden) → Wer?
2. **Rentals** (Vermietungen) → Was mieten sie?

---

## 💡 Vorteile der Struktur

### Übersichtlichkeit
- ✅ Klare Trennung: Board-Verwaltung vs. Kunden-Verwaltung
- ✅ Logische Hierarchie innerhalb der Gruppen
- ✅ Intuitive Icons für schnelle Orientierung

### Informationsgehalt
- ✅ Badges zeigen wichtige KPIs auf einen Blick
- ✅ Dynamische Farben für Status-Ampel
- ✅ Tooltips für zusätzliche Erklärungen

### Benutzerfreundlichkeit
- ✅ Konsistente Benennung (Plural-Form)
- ✅ Sortierung von allgemein zu spezifisch
- ✅ Farbcodierung nach Dringlichkeit/Status

---

## 🎭 Navigation Preview

So sieht die Navigation aus:

```
📂 Board Management
   📍 Locations [5]
   📊 Boards [6]
   🎲 Fields [45/120]

📂 Customer Management
   👥 Customers [12]
   📄 Rentals [8]
```

---

## 🔧 Anpassungsmöglichkeiten

### Icons ändern
Icons können einfach geändert werden:
```php
protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewIcon;
```

Alle verfügbaren Icons: [Heroicons](https://heroicons.com/)

### Badge-Logik anpassen
```php
public static function getNavigationBadge(): ?string
{
    // Eigene Logik hier
    return 'Custom';
}

public static function getNavigationBadgeColor(): ?string
{
    return 'primary'; // primary, success, warning, danger, info, gray
}
```

### Sortierung ändern
```php
protected static ?int $navigationSort = 10; // Höhere Zahl = weiter unten
```

### Gruppierung ändern
```php
protected static ?string $navigationGroup = 'Neue Gruppe';
```

---

## 📈 Zukünftige Erweiterungen

### Mögliche zusätzliche Badges:
- **Board:** Auslastung in % anzeigen
- **Customer:** Anzahl aktiver Kunden (mit Rentals)
- **Rental:** Umsatz des aktuellen Monats
- **Field:** Durchschnittspreis der verfügbaren Felder

### Mögliche neue Gruppen:
- **Reports & Analytics** (Berichte)
- **Settings** (Einstellungen)
- **System** (System-Verwaltung)

---

## ✅ Status

Alle 5 Resources wurden erfolgreich konfiguriert:
- ✅ LocationResource - MapPin Icon, Success Badge
- ✅ BoardResource - ViewColumns Icon, Info Badge  
- ✅ FieldResource - Squares2x2 Icon, Dynamic Availability Badge
- ✅ CustomerResource - UserGroup Icon, Dynamic Growth Badge
- ✅ RentalResource - DocumentText Icon, Active Badge

Die Navigation ist jetzt professionell strukturiert und bietet auf einen Blick wichtige Geschäftsinformationen! 🎉
