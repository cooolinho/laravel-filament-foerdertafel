# Dashboard Widgets

Diese Datei dokumentiert alle Dashboard-Widgets für das Filament Admin-Panel.

## Übersicht der Widgets

### 1. StatsOverviewWidget
**Typ:** Stats Overview Widget  
**Beschreibung:** Zeigt wichtige Kennzahlen auf einen Blick an.

**Angezeigt Metriken:**
- Verfügbare Felder (mit Gesamtzahl und Mini-Chart)
- Vermietete Felder (mit Auslastung in %)
- Aktive Vermietungen
- Offene Anfragen
- Monatlicher Umsatz
- Anzahl registrierter Kunden

**Position:** Ganz oben im Dashboard (Sort: 1)

---

### 2. RecentInquiriesWidget
**Typ:** Table Widget  
**Beschreibung:** Zeigt die 10 neuesten Kundenanfragen an.

**Spalten:**
- Kundenname
- E-Mail (kopierbar)
- Tafel
- Von-Datum
- Bis-Datum
- Status (als Badge mit Farben)
- Erstellungsdatum

**Aktionen:**
- Ansehen-Button (verlinkt zur Inquiry-Detailseite)

**Position:** Sort 2

---

### 3. ActiveRentalsWidget
**Typ:** Table Widget  
**Beschreibung:** Zeigt die aktuell aktiven Vermietungen an (max. 10).

**Spalten:**
- Kundenname
- E-Mail (kopierbar, standardmäßig ausgeblendet)
- Startdatum
- Enddatum (farbcodiert: rot=abgelaufen, gelb=läuft in 7 Tagen ab, grün=normal)
- Anzahl Felder
- Gesamtpreis (in EUR)
- Status (Badge)

**Aktionen:**
- Ansehen-Button (verlinkt zur Rental-Detailseite)

**Position:** Sort 3

---

### 4. ExpiringRentalsWidget
**Typ:** Table Widget  
**Beschreibung:** Zeigt Vermietungen an, die in den nächsten 30 Tagen ablaufen.

**Spalten:**
- Kundenname
- E-Mail (kopierbar)
- Telefon (ausklappbar)
- Enddatum (mit verbleibenden Tagen, farbcodiert)
- Anzahl Felder
- Gesamtpreis

**Aktionen:**
- Verlängern-Button (zur Bearbeitungsseite)
- Ansehen-Button

**Besonderheit:** Zeigt eine freundliche Empty State Message, wenn keine bald ablaufenden Vermietungen vorhanden sind.

**Position:** Sort 6

---

### 5. FieldStatusWidget
**Typ:** Chart Widget (Doughnut)  
**Beschreibung:** Visualisiert den Status aller Felder als Donut-Chart.

**Daten:**
- Verfügbare Felder (grün)
- Vermietete Felder (blau)
- Reservierte Felder (orange)

**Position:** Sort 4

---

### 6. RevenueChartWidget
**Typ:** Chart Widget (Line)  
**Beschreibung:** Zeigt die Umsatzentwicklung über die Zeit an.

**Filter:**
- 3 Monate
- 6 Monate (Standard)
- 12 Monate

**Berechnung:** Summiert alle Vermietungen (außer stornierte), die im jeweiligen Monat aktiv waren.

**Position:** Sort 5

---

## Dashboard-Konfiguration

Die Widgets werden in der `Dashboard.php` Page konfiguriert:

```php
public function getWidgets(): array
{
    return [
        StatsOverviewWidget::class,
        RecentInquiriesWidget::class,
        ActiveRentalsWidget::class,
        ExpiringRentalsWidget::class,
        FieldStatusWidget::class,
        RevenueChartWidget::class,
    ];
}
```

Das Dashboard nutzt ein 2-Spalten-Layout für die Widgets.

## Verwendete Models

- **Customer:** Kundendaten
- **Rental:** Vermietungsdaten
- **Inquiry:** Anfragedaten
- **Field:** Felddaten
- **Board:** Tafeldaten (über Relationen)

## Farbschema

- **Erfolg (grün):** Verfügbare Felder, abgeschlossene Aktionen
- **Info (blau):** Vermietete Felder, Informationen
- **Warnung (gelb/orange):** Bald ablaufende Fristen, ausstehende Aktionen
- **Gefahr (rot):** Abgelaufene Fristen, Fehler
- **Primär (lila):** Standard-Aktionen, Kundenzahlen

## Auto-Discovery

Die Widgets werden automatisch durch die `discoverWidgets()` Funktion im `AdminPanelProvider` erkannt und registriert.

## Anpassungen

Um ein Widget anzupassen:

1. Öffne die entsprechende Widget-Datei in `app/Filament/Admin/Widgets/`
2. Ändere die Datenabfragen in der `getData()` oder `table()` Methode
3. Passe die Sort-Reihenfolge mit `protected static ?int $sort` an
4. Ändere die Spaltenbreite mit `protected int | string | array $columnSpan`

## Performance-Hinweise

- Die Table Widgets sind auf 10 Einträge limitiert
- Das Revenue Chart Widget cached keine Daten (Echtzeit-Berechnung)
- Stats werden bei jedem Dashboard-Aufruf neu berechnet

## Empfohlene Erweiterungen

1. **Caching:** Implementiere Caching für die Stats mit 5-Minuten-TTL
2. **Polling:** Füge Auto-Refresh hinzu mit `protected static ?string $pollingInterval = '30s';`
3. **Export:** Ermögliche Export der Tabellen-Widgets als CSV/PDF
4. **Notifications:** Sende Benachrichtigungen bei kritischen Metriken
