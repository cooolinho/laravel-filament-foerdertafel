# Quick Start: Inkonsistente Field-Status Überwachung

## Was wurde implementiert?

### ✅ 1. Widget im Dashboard
Ein neues Widget zeigt alle Felder mit inkonsistentem Status in einer Tabelle an.

**Ort:** Dashboard → "Felder mit inkonsistentem Status"

### ✅ 2. Filter in der Fields-Tabelle
Ein neuer Filter ermöglicht das Filtern nach inkonsistenten Feldern.

**Ort:** Fields → Filter Button → "Inkonsistenter Status"

### ✅ 3. Artisan Command zur automatischen Korrektur
Ein Command zum Fixen von inkonsistenten Status.

## Schnellstart

### Widget ansehen
```
1. Öffne das Admin-Dashboard
2. Scrolle zum Widget "Felder mit inkonsistentem Status"
3. Wenn Felder angezeigt werden → Aktion erforderlich!
```

### Filter verwenden
```
1. Navigiere zu "Fields"
2. Klicke auf den Filter-Button (Trichter-Icon)
3. Wähle Filter "Inkonsistenter Status"
4. Optionen:
   - "Nur inkonsistente Felder" → Zeigt problematische Felder
   - "Nur konsistente Felder" → Zeigt korrekte Felder
   - "Alle Felder" → Zeigt alle (Standard)
```

### Command ausführen

**Testlauf (ohne Änderungen):**
```bash
php artisan fields:fix-inconsistent-status --dry-run
```

**Mit detaillierter Ausgabe:**
```bash
php artisan fields:fix-inconsistent-status --dry-run --verbose
```

**Änderungen durchführen:**
```bash
php artisan fields:fix-inconsistent-status
```

## Was bedeutet "inkonsistenter Status"?

Ein Feld hat einen inkonsistenten Status wenn:
- ✓ Es einem Rental zugeordnet ist
- ✓ Das Rental den Status "active" hat
- ✓ Das aktuelle Datum liegt zwischen start_date und end_date
- ✗ ABER: Das Feld hat NICHT den Status "rented"

## Beispiel

```
Feld: "A1"
Status: "available" ❌ (sollte "rented" sein)
Rental: #123 (active, 01.01.2026 - 31.12.2026)
Kunde: Mustermann GmbH
```

→ Dieses Feld wird im Widget und Filter angezeigt!

## Wichtige Dateien

```
app/Filament/Admin/Widgets/InconsistentFieldStatusWidget.php
app/Filament/Admin/Resources/Fields/Tables/FieldsTable.php
app/Console/Commands/FixInconsistentFieldStatus.php
docs/INCONSISTENT_FIELD_STATUS.md (Vollständige Dokumentation)
```

## Nächste Schritte

1. **Teste das Widget:**
   - Öffne das Dashboard
   - Prüfe, ob Felder angezeigt werden

2. **Teste den Filter:**
   - Öffne die Fields-Liste
   - Aktiviere den Filter "Nur inkonsistente Felder"

3. **Teste den Command (optional):**
   ```bash
   cd laravel
   php artisan fields:fix-inconsistent-status --dry-run --verbose
   ```

## Fehlerbehebung

**Problem:** Widget zeigt keine Daten
- ✓ Das ist gut! Bedeutet keine inkonsistenten Felder vorhanden

**Problem:** Filter funktioniert nicht
- Prüfe, ob aktive Rentals existieren
- Prüfe Datenbankverbindung

**Problem:** Command findet keine Felder
- Das ist normal, wenn keine Inkonsistenzen vorliegen
- Teste mit Testdaten

## Automatisierung (Optional)

Füge den Command zu einem Scheduler hinzu:

**Datei:** `app/Console/Kernel.php`
```php
protected function schedule(Schedule $schedule)
{
    // Täglich um 2:00 Uhr inkonsistente Felder prüfen und fixen
    $schedule->command('fields:fix-inconsistent-status')
        ->dailyAt('02:00')
        ->emailOutputOnFailure('admin@example.com');
}
```
