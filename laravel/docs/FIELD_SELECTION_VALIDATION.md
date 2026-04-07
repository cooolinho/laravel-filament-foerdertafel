# Field Selection Validation Rules

## Überblick

Die InquiryPage verwendet zwei Custom Validation Rules, um sicherzustellen, dass die ausgewählten Felder bestimmte Kriterien erfüllen.

Die **Grenzwerte** (maximale Felder, Zeilen, Spalten) sowie die **physischen Maße** (Kachelbreite, Kachelhöhe, Abstand) werden zentral im `Setting`-Model gespeichert und können über die Admin-SettingsPage angepasst werden.

---

## Konfiguration in den Einstellungen (SettingsPage)

### Relevante Settings

| Feld | Konstante | Standardwert | Beschreibung |
|------|-----------|-------------|--------------|
| `max_fields_per_customer` | `Setting::max_fields_per_customer` | `10` | Maximale Gesamtanzahl auswählbarer Felder |
| `field_width_cm` | `Setting::field_width_cm` | `8.9` | Breite einer einzelnen Kachel in cm |
| `field_height_cm` | `Setting::field_height_cm` | `5.1` | Höhe einer einzelnen Kachel in cm |
| `field_gap_cm` | `Setting::field_gap_cm` | `1.2` | Abstand zwischen Kacheln in cm |

### Automatische Berechnung von Max-Zeilen und Max-Spalten

Aus `max_fields_per_customer` werden die erlaubten Dimensionen **automatisch** berechnet:

```php
// Max. Zeilen = floor(sqrt(max_fields))
Setting::getMaxSelectionRows();

// Max. Spalten = ceil(max_fields / max_rows)
Setting::getMaxSelectionCols();
```

#### Beispiele

| `max_fields_per_customer` | Max Zeilen | Max Spalten | Größtes Rechteck |
|--------------------------|-----------|------------|-----------------|
| 4 | 2 | 2 | 2×2 |
| 6 | 2 | 3 | 2×3 |
| 9 | 3 | 3 | 3×3 |
| 12 | 3 | 4 | 3×4 |
| 16 | 4 | 4 | 4×4 |

> **Wichtig:** Eine Auswahl von z. B. 9 Feldern als 1×9-Linie ist **nicht erlaubt**, da max. 3 Spalten (bei `max_fields=9`) gestattet sind. Die Auswahl muss stets ein möglichst quadratisches Rechteck bilden.

---

## Physische Maßberechnung

### Methoden im Setting-Model

```php
// Physische Breite für X Spalten (inkl. Gaps)
Setting::calculatePhysicalWidth(int $cols): float

// Physische Höhe für Y Zeilen (inkl. Gaps)
Setting::calculatePhysicalHeight(int $rows): float
```

### Formel

```
Breite = cols × field_width_cm  + (cols - 1) × field_gap_cm
Höhe   = rows × field_height_cm + (rows - 1) × field_gap_cm
```

### Beispiele (Standardwerte: 8,9 cm Breite, 5,1 cm Höhe, 1,2 cm Gap)

| Auswahl | Breite (cm) | Höhe (cm) |
|---------|------------|----------|
| 1×1 | 8,9 | 5,1 |
| 1×2 | 19,0 (8,9 + 1,2 + 8,9) | 5,1 |
| 1×3 | 29,1 | 5,1 |
| 2×1 | 8,9 | 11,4 (5,1 + 1,2 + 5,1) |
| 2×2 | 19,0 | 11,4 |
| 3×3 | 29,1 | 17,7 |

### Alle gültigen Konfigurationen abrufen

```php
// Gibt alle validen R×C-Kombinationen zurück inkl. physischer Maße
Setting::getValidRectangles();

// Rückgabe-Struktur (Beispiel bei max_fields=9):
[
    ['rows' => 1, 'cols' => 1, 'total' => 1,  'width_cm' => 8.9,  'height_cm' => 5.1],
    ['rows' => 1, 'cols' => 2, 'total' => 2,  'width_cm' => 19.0, 'height_cm' => 5.1],
    ['rows' => 1, 'cols' => 3, 'total' => 3,  'width_cm' => 29.1, 'height_cm' => 5.1],
    ['rows' => 2, 'cols' => 1, 'total' => 2,  'width_cm' => 8.9,  'height_cm' => 11.4],
    ['rows' => 2, 'cols' => 2, 'total' => 4,  'width_cm' => 19.0, 'height_cm' => 11.4],
    ['rows' => 2, 'cols' => 3, 'total' => 6,  'width_cm' => 29.1, 'height_cm' => 11.4],
    ['rows' => 3, 'cols' => 1, 'total' => 3,  'width_cm' => 8.9,  'height_cm' => 17.7],
    ['rows' => 3, 'cols' => 2, 'total' => 6,  'width_cm' => 19.0, 'height_cm' => 17.7],
    ['rows' => 3, 'cols' => 3, 'total' => 9,  'width_cm' => 29.1, 'height_cm' => 17.7],
]
```

---

## 1. FieldsFormRectangle Rule

**Datei**: `app/Rules/FieldsFormRectangle.php`

### Zweck
Prüft, ob die ausgewählten Felder:
1. ein **zusammenhängendes Rechteck** bilden
2. die erlaubten **Zeilen- und Spalten-Grenzen** nicht überschreiten (aus Settings)

### Validierungsschritte

```
1. Felder laden & Grid-Positionen extrahieren (inkl. multi-cell)
2. Rechteck-Prüfung: alle Positionen innerhalb der Bounding-Box vorhanden?
3. Zeilen-Check:  selectionRows ≤ Setting::getMaxSelectionRows()
4. Spalten-Check: selectionCols ≤ Setting::getMaxSelectionCols()
```

### Rechteck-Prüfung

#### ✅ Gültige Beispiele

```
1×1 (einzelnes Feld):
┌───┐
│ X │  → ✓
└───┘

1×3 (horizontale Linie bei max_cols ≥ 3):
┌───┬───┬───┐
│ X │ X │ X │  → ✓
└───┴───┴───┘

2×2:
┌───┬───┐
│ X │ X │  → ✓
├───┼───┤
│ X │ X │
└───┴───┘
```

#### ❌ Ungültige Beispiele

```
L-Form:
┌───┬───┐
│ X │ X │
├───┼───┤
│ X │   │  ← (2,2) fehlt  → ✗
└───┴───┘

1×9-Linie bei max_fields=9 (max_cols=3):
┌───┬───┬───┬───┬───┬───┬───┬───┬───┐
│ X │ X │ X │ X │ X │ X │ X │ X │ X │
└───┴───┴───┴───┴───┴───┴───┴───┴───┘
→ ✗  (9 Spalten > max_cols 3)
```

### Code-Logik

```php
// 1. Grid-Positionen extrahieren (multi-cell-fähig)
foreach ($value as $fieldId) {
    $field = $fields->get($fieldId);
    for ($row = $field->row; $row < $field->row + $field->height; $row++) {
        for ($col = $field->column; $col < $field->column + $field->width; $col++) {
            $positions[] = ['row' => $row, 'col' => $col];
        }
    }
}

// 2. Rechteck-Prüfung
$minRow = min(array_column($positions, 'row'));
$maxRow = max(array_column($positions, 'row'));
$minCol = min(array_column($positions, 'col'));
$maxCol = max(array_column($positions, 'col'));

$expectedCount = ($maxRow - $minRow + 1) * ($maxCol - $minCol + 1);
if (count($positions) !== $expectedCount) {
    $fail('Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden.');
    return;
}

// 3. Zeilen/Spalten-Grenzen prüfen
$selectionRows = $maxRow - $minRow + 1;
$selectionCols = $maxCol - $minCol + 1;

if ($selectionRows > Setting::getMaxSelectionRows()) {
    $fail("Die Auswahl darf maximal X Zeile(n) umfassen.");
    return;
}
if ($selectionCols > Setting::getMaxSelectionCols()) {
    $fail("Die Auswahl darf maximal X Spalte(n) umfassen.");
    return;
}
```

### Fehlermeldungen

| Situation | Meldung |
|-----------|---------|
| Kein Rechteck | `"Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden."` |
| Zu viele Zeilen | `"Die Auswahl darf maximal X Zeile(n) umfassen (aktuell: Y)."` |
| Zu viele Spalten | `"Die Auswahl darf maximal X Spalte(n) umfassen (aktuell: Y)."` |

---

## 2. MaxFieldsCount Rule

**Datei**: `app/Rules/MaxFieldsCount.php`

### Zweck
Begrenzt die **Gesamtanzahl** der auswählbaren Felder auf `max_fields_per_customer`.

### Verwendung

```php
new MaxFieldsCount(Setting::get(Setting::max_fields_per_customer, 4))
```

### Fehlermeldung

```
"Sie können maximal {$maxFields} Felder auswählen."
```

---

## Integration in InquiryPage

### Validierung im `submit()`

```php
$validator = Validator::make(
    ['selected_fields' => $this->selectedFields],
    [
        'selected_fields' => [
            'required',
            'array',
            'min:1',
            new MaxFieldsCount(Setting::get(Setting::max_fields_per_customer, 4)),
            new FieldsFormRectangle(),  // prüft Rechteck + Zeilen/Spalten-Limits
        ],
    ],
    [
        'selected_fields.required' => 'Bitte wählen Sie mindestens ein Feld aus.',
        'selected_fields.min'      => 'Bitte wählen Sie mindestens ein Feld aus.',
    ]
);
```

### Live-Feedback in `toggleField()`

Der Max-Wert kommt ebenfalls aus den Settings (nicht mehr hardcodiert):

```php
public function toggleField(int $fieldId): void
{
    $maxFields = (int) Setting::get(Setting::max_fields_per_customer, 4);

    if (in_array($fieldId, $this->selectedFields)) {
        $this->selectedFields = array_values(array_diff($this->selectedFields, [$fieldId]));
    } else {
        if (count($this->selectedFields) >= $maxFields) {
            Notification::make()
                ->title('Maximale Anzahl erreicht')
                ->body("Sie können maximal {$maxFields} Felder auswählen.")
                ->warning()
                ->send();
            return;
        }
        $this->selectedFields[] = $fieldId;
    }

    $this->dispatch('fields-updated');
}
```

### Maßanzeige nach Auswahl (`getSelectedFieldDimensions()`)

Sobald Felder ausgewählt sind, berechnet die InquiryPage die physischen Abmessungen:

```php
public function getSelectedFieldDimensions(): ?array
{
    // Bounding-Box der ausgewählten Felder ermitteln
    $minRow = $fields->min(Field::row);
    $maxRowEnd = $fields->map(fn($f) => $f->row + $f->height - 1)->max();
    $minCol = $fields->min(Field::column);
    $maxColEnd = $fields->map(fn($f) => $f->column + $f->width - 1)->max();

    $selectionRows = $maxRowEnd - $minRow + 1;
    $selectionCols = $maxColEnd - $minCol + 1;

    return [
        'rows'      => $selectionRows,
        'cols'      => $selectionCols,
        'width_cm'  => Setting::calculatePhysicalWidth($selectionCols),
        'height_cm' => Setting::calculatePhysicalHeight($selectionRows),
    ];
}
```

Das Ergebnis wird in der InquiryPage-Blade als **grüne Info-Box** angezeigt:

```
┌─────────────────────────────────────────┐
│ 📐 Maße Ihrer Auswahl (2 × 3 Felder)   │
│                                         │
│  Breite: 29,1 cm   │  Höhe: 11,4 cm   │
│  3×8,9 + 2×1,2     │  2×5,1 + 1×1,2   │
└─────────────────────────────────────────┘
```

---

## FieldInformationPage

**Datei**: `app/Filament/App/Pages/FieldInformationPage.php`  
**View**: `resources/views/filament/app/pages/field-information-page.blade.php`  
**Navigation**: „Feldgrößen" (Sort: 3)

### Inhalte der Seite

1. **SVG-Diagramm** – Visualisierung des maximalen Rasters (max_rows × max_cols) mit:
   - Einzelmaße pro Kachel oben/links (z. B. 8,9 | 1,2 | 8,9 | ...)
   - Kumulative Maße für 2 bis max Kacheln unten/rechts (z. B. 19,0, 29,1)

2. **Konfigurationstabelle** – Alle gültigen R×C-Kombinationen mit Breite, Höhe und Berechnungsformel

3. **Referenz-Box** – Einzelkachelmaße auf einen Blick

### Methoden

| Methode | Rückgabe | Beschreibung |
|---------|---------|-------------|
| `getFieldWidth()` | `float` | Kachelbreite aus Settings |
| `getFieldHeight()` | `float` | Kachelhöhe aus Settings |
| `getFieldGap()` | `float` | Gap aus Settings |
| `getMaxRows()` | `int` | `Setting::getMaxSelectionRows()` |
| `getMaxCols()` | `int` | `Setting::getMaxSelectionCols()` |
| `getValidRectangles()` | `array` | `Setting::getValidRectangles()` |

---

## User Experience

### Validierungs-Flow

```
Kunde klickt auf Feld
    │
    ├─ Anzahl bereits = max_fields? → ⚠️ Warning-Notification, Feld nicht ausgewählt
    └─ Sonst: Feld zur Auswahl hinzufügen
           │
           └─ Maß-Info-Box aktualisiert sich (Breite/Höhe live)

Kunde klickt „Absenden"
    │
    ├─ Kein Feld ausgewählt? → ❌ Danger-Notification
    ├─ > max_fields Felder? → ❌ Danger-Notification
    ├─ Kein Rechteck? → ❌ Danger-Notification
    ├─ Zeilen > max_rows? → ❌ Danger-Notification
    ├─ Spalten > max_cols? → ❌ Danger-Notification
    └─ Alles OK → ✅ Anfrage wird erstellt
```

### Notifications

| Typ | Titel | Nachricht | Wann |
|-----|-------|-----------|------|
| Warning | Maximale Anzahl erreicht | Sie können maximal N Felder auswählen. | Bei Klick auf (N+1). Feld |
| Danger | Fehler | Bitte wählen Sie mindestens ein Feld aus. | Submit ohne Auswahl |
| Danger | Ungültige Feld-Auswahl | Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden. | Kein Rechteck |
| Danger | Ungültige Feld-Auswahl | Die Auswahl darf maximal X Zeile(n) umfassen. | Zu viele Zeilen |
| Danger | Ungültige Feld-Auswahl | Die Auswahl darf maximal X Spalte(n) umfassen. | Zu viele Spalten |

---

## Edge Cases

### Multi-Cell Felder

```
Feld A (width=2, height=1) an (1,1): belegt (1,1) und (1,2)
Feld B (width=1, height=1) an (2,1): belegt (2,1)

Grid:
┌───────┐
│   A   │  (1,1)+(1,2)
├───┬───┤
│ B │   │  (2,1) – (2,2) fehlt!
└───┴───┘

→ ✗ Kein vollständiges Rechteck
```

Die Bounding-Box berücksichtigt immer die gesamte Ausdehnung eines Multi-Cell-Felds.

### Nicht-quadratische `max_fields`-Werte

Bei `max_fields = 7`:
- `max_rows = floor(sqrt(7)) = 2`
- `max_cols = ceil(7 / 2) = 4`
- Effektiv erreichbar: 2×3 = 6 Felder (da 2×4=8 > 7 von MaxFieldsCount abgefangen wird)

---

## Datenbank-Migration

Die Feld-Dimensionen werden in der `settings`-Tabelle gespeichert:

```
Migration: 2026_04_07_000000_add_field_dimensions_to_settings_table.php
```

```php
Schema::table('settings', function (Blueprint $table) {
    $table->decimal('field_width_cm', 5, 2)->default(8.9);
    $table->decimal('field_height_cm', 5, 2)->default(5.1);
    $table->decimal('field_gap_cm', 5, 2)->default(1.2);
});
```

---

## Zusammenfassung

✅ **Mindestens 1 Feld** muss ausgewählt sein  
✅ **Maximal N Felder** (aus Settings konfigurierbar, kein Hardcoding)  
✅ **Rechteck-Form** muss gebildet werden  
✅ **Max. Zeilen** automatisch aus `floor(sqrt(max_fields))`  
✅ **Max. Spalten** automatisch aus `ceil(max_fields / max_rows)`  
✅ **Live-Maßanzeige** (Breite/Höhe in cm) nach Feldauswahl  
✅ **FieldInformationPage** mit SVG-Diagramm und Konfigurationstabelle  
✅ **Multi-Cell Felder** werden korrekt berücksichtigt  
✅ **Alle Grenzwerte** zentral in der SettingsPage pflegbar
