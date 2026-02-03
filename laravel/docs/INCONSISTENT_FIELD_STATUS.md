# Inkonsistente Field-Status Überwachung

## Übersicht

Dieses Feature hilft dabei, Felder zu identifizieren, die einen inkonsistenten Status haben. Ein Feld hat einen inkonsistenten Status, wenn:
- Es einem aktiven Rental zugeordnet ist
- Das Rental im aktuellen Zeitraum liegt (start_date <= heute <= end_date)
- Das Feld aber NICHT den Status `rented` hat

## Komponenten

### 1. Widget: InconsistentFieldStatusWidget

**Datei:** `app/Filament/Admin/Widgets/InconsistentFieldStatusWidget.php`

**Funktion:**
- Zeigt eine Tabelle mit allen Feldern, die einen inkonsistenten Status haben
- Wird im Dashboard angezeigt
- Spalten:
  - Feldname
  - Board
  - Aktueller Status (mit farbigem Badge)
  - Aktives Rental (mit ID und Zeitraum)
  - Kunde

**Query-Logik:**
```php
Field::query()
    ->whereHas('rentals', function (Builder $query) {
        $query->where(Rental::status, Rental::STATUS_ACTIVE)
            ->where(Rental::start_date, '<=', now())
            ->where(Rental::end_date, '>=', now());
    })
    ->where(Field::status, '!=', Field::STATUS_RENTED)
```

### 2. Filter: Inkonsistenter Status

**Datei:** `app/Filament/Admin/Resources/Fields/Tables/FieldsTable.php`

**Funktion:**
- TernaryFilter in der Fields-Tabelle
- Ermöglicht Filterung nach:
  - **Alle Felder** (Standard)
  - **Nur inkonsistente Felder** - Zeigt nur Felder mit inkonsistentem Status
  - **Nur konsistente Felder** - Zeigt nur Felder ohne Probleme

**Filter-Logik:**

**Inkonsistente Felder (true):**
```php
$query->whereHas('rentals', function (Builder $subQuery) {
    $subQuery->where(Rental::status, Rental::STATUS_ACTIVE)
        ->where(Rental::start_date, '<=', now())
        ->where(Rental::end_date, '>=', now());
})->where(Field::status, '!=', Field::STATUS_RENTED)
```

**Konsistente Felder (false):**
```php
$query->whereDoesntHave('rentals', function (Builder $subQuery) {
    $subQuery->where(Rental::status, Rental::STATUS_ACTIVE)
        ->where(Rental::start_date, '<=', now())
        ->where(Rental::end_date, '>=', now());
})->orWhere(Field::status, Field::STATUS_RENTED)
```

## Dashboard-Integration

Das Widget wurde dem Dashboard hinzugefügt in:
`app/Filament/Admin/Pages/Dashboard.php`

## Verwendung

### Widget ansehen
1. Navigiere zum Dashboard
2. Das Widget "Felder mit inkonsistentem Status" zeigt alle problematischen Felder an
3. Wenn keine Felder inkonsistent sind, ist die Tabelle leer

### Filter verwenden
1. Navigiere zur Fields-Liste
2. Klicke auf den Filter-Button
3. Wähle den Filter "Inkonsistenter Status"
4. Optionen:
   - **Alle Felder**: Zeigt alle Felder (Standard)
   - **Nur inkonsistente Felder**: Zeigt nur Felder, die einem aktiven Rental zugeordnet sind, aber nicht als "rented" markiert sind
   - **Nur konsistente Felder**: Zeigt nur Felder, die entweder keinem aktiven Rental zugeordnet sind oder korrekt als "rented" markiert sind

## Prävention

Obwohl Listener und Observer dieses Problem normalerweise verhindern sollten, dient dieses Feature als zusätzliche Sicherheitsmaßnahme:

### Bestehende Mechanismen
- **Field Status Observer** - Aktualisiert automatisch den Field-Status bei Rental-Änderungen
- **Rental Event Listener** - Reagiert auf Rental-Status-Änderungen

### Mögliche Ursachen für inkonsistente Status
1. Manuelle Datenbankänderungen
2. Fehler in Listeners/Observers
3. Race Conditions bei parallelen Updates
4. Import von externen Daten
5. Direkte SQL-Updates außerhalb von Eloquent

## Behebung von Problemen

Wenn inkonsistente Felder gefunden werden:

1. **Überprüfung**
   - Prüfe das zugeordnete Rental
   - Verifiziere, dass das Rental wirklich aktiv sein sollte
   
2. **Manuelle Korrektur**
   - Öffne das betroffene Field
   - Setze den Status manuell auf "rented"
   - Oder passe das Rental an, falls es nicht aktiv sein sollte

3. **Automatische Korrektur (empfohlen)**
   - Erstelle einen Artisan Command, der alle inkonsistenten Felder korrigiert
   ```bash
   php artisan fields:fix-inconsistent-status
   ```

## Technische Details

### Performance-Überlegungen
- Das Widget verwendet Eager Loading für bessere Performance
- Die Query ist optimiert mit `whereHas` für effiziente Subqueries
- Pagination ist aktiviert (10, 25, 50 Einträge pro Seite)

### Datenbank-Relationen
- Field -> Rental (many-to-many über `field_rental` Pivot-Tabelle)
- Rental -> Customer (belongs-to)
- Field -> Board (belongs-to)

## Wartung

### Regelmäßige Überprüfung
Empfohlene Intervalle:
- Täglich: Dashboard-Widget prüfen
- Wöchentlich: Filter verwenden für detaillierte Analyse
- Monatlich: Log-Analyse für wiederholte Probleme

### Monitoring
Erwäge das Hinzufügen von:
- Automatischen E-Mail-Benachrichtigungen bei inkonsistenten Feldern
- Logging von Status-Änderungen
- Metriken für Tracking der Häufigkeit
