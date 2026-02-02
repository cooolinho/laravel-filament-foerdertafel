# Field Selection Validation Rules

## Überblick

Die InquiryPage verwendet zwei Custom Validation Rules, um sicherzustellen, dass die ausgewählten Felder bestimmte Kriterien erfüllen.

---

## 1. FieldsFormRectangle Rule

**Datei**: `app/Rules/FieldsFormRectangle.php`

### Zweck
Prüft, ob die ausgewählten Felder ein zusammenhängendes Rechteck bilden.

### Funktionsweise

1. **Positions-Extraktion**
   - Lädt alle ausgewählten Felder aus der Datenbank
   - Extrahiert alle Grid-Positionen, die diese Felder einnehmen
   - Berücksichtigt dabei multi-cell Felder (width × height)

2. **Rechteck-Prüfung**
   - Findet minimale und maximale Row/Column
   - Berechnet erwartete Anzahl an Positionen im Rechteck
   - Prüft ob alle Positionen innerhalb des Rechtecks vorhanden sind

### Beispiele

#### ✅ Gültiges Rechteck
```
Felder: (1,1), (1,2), (2,1), (2,2)

┌───┬───┐
│ X │ X │
├───┼───┤
│ X │ X │
└───┴───┘

→ Bildet 2×2 Rechteck ✓
```

#### ❌ Ungültiges Rechteck
```
Felder: (1,1), (1,2), (2,1)

┌───┬───┐
│ X │ X │
├───┼───┤
│ X │   │  ← Feld (2,2) fehlt!
└───┴───┘

→ Kein vollständiges Rechteck ✗
```

#### ✅ L-Form wird abgelehnt
```
Felder: (1,1), (1,2), (2,1), (3,1)

┌───┬───┐
│ X │ X │
├───┼───┤
│ X │   │
├───┼───┤
│ X │   │
└───┴───┘

→ Kein Rechteck ✗
```

### Code-Logik

```php
// 1. Min/Max Positionen finden
$minRow = min(array_column($positions, 'row'));
$maxRow = max(array_column($positions, 'row'));
$minCol = min(array_column($positions, 'col'));
$maxCol = max(array_column($positions, 'col'));

// 2. Erwartete Anzahl berechnen
$expectedCount = ($maxRow - $minRow + 1) * ($maxCol - $minCol + 1);

// 3. Tatsächliche vs. Erwartete Anzahl vergleichen
if (count($positions) !== $expectedCount) {
    return false;
}

// 4. Alle Positionen im Rechteck prüfen
for ($row = $minRow; $row <= $maxRow; $row++) {
    for ($col = $minCol; $col <= $maxCol; $col++) {
        if (!isset($positionSet[$row . ',' . $col])) {
            return false; // Position fehlt
        }
    }
}
```

### Multi-Cell Felder

Die Regel berücksichtigt korrekt Felder, die mehrere Grid-Zellen einnehmen:

```php
// Feld mit width=2, height=1 an Position (1,1)
// Belegt Positionen: (1,1) und (1,2)

for ($row = $field->row; $row < $field->row + $field->height; $row++) {
    for ($col = $field->column; $col < $field->column + $field->width; $col++) {
        $positions[] = ['row' => $row, 'col' => $col];
    }
}
```

### Fehlermeldung

```
"Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden."
```

---

## 2. MaxFieldsCount Rule

**Datei**: `app/Rules/MaxFieldsCount.php`

### Zweck
Begrenzt die Anzahl der auswählbaren Felder auf ein Maximum.

### Parameter
- `$maxFields`: Maximale Anzahl (Standard: 10)

### Verwendung

```php
new MaxFieldsCount(10) // Max 10 Felder
new MaxFieldsCount(5)  // Max 5 Felder
```

### Funktionsweise

```php
if (count($value) > $this->maxFields) {
    $fail("Sie können maximal {$this->maxFields} Felder auswählen.");
}
```

### Fehlermeldung

```
"Sie können maximal 10 Felder auswählen."
```

---

## Integration in InquiryPage

### 1. Import der Rules

```php
use App\Rules\FieldsFormRectangle;
use App\Rules\MaxFieldsCount;
use Illuminate\Support\Facades\Validator;
```

### 2. Validierung im submit()

```php
$validator = Validator::make(
    ['selected_fields' => $this->selectedFields],
    [
        'selected_fields' => [
            'required',
            'array',
            'min:1',
            new MaxFieldsCount(10),
            new FieldsFormRectangle(),
        ],
    ],
    [
        'selected_fields.required' => 'Bitte wählen Sie mindestens ein Feld aus.',
        'selected_fields.min' => 'Bitte wählen Sie mindestens ein Feld aus.',
    ]
);

if ($validator->fails()) {
    Notification::make()
        ->title('Ungültige Feld-Auswahl')
        ->body($validator->errors()->first('selected_fields'))
        ->danger()
        ->send();
    return;
}
```

### 3. Live-Feedback in toggleField()

```php
public function toggleField(int $fieldId): void
{
    if (in_array($fieldId, $this->selectedFields)) {
        // Remove field
        $this->selectedFields = array_values(array_diff($this->selectedFields, [$fieldId]));
    } else {
        // Check max limit
        if (count($this->selectedFields) >= 10) {
            Notification::make()
                ->title('Maximale Anzahl erreicht')
                ->body('Sie können maximal 10 Felder auswählen.')
                ->warning()
                ->send();
            return;
        }
        
        $this->selectedFields[] = $fieldId;
    }
}
```

---

## User Experience

### Validierungs-Flow

1. **Während Auswahl**:
   - Bei Klick auf 11. Feld → Warning-Notification: "Maximale Anzahl erreicht"
   - Feld wird nicht ausgewählt

2. **Beim Submit**:
   - Prüfung auf mindestens 1 Feld
   - Prüfung auf max. 10 Felder
   - Prüfung auf Rechteck-Form
   - Bei Fehler → Danger-Notification mit spezifischer Fehlermeldung

### Notifications

| Typ | Titel | Nachricht | Wann |
|-----|-------|-----------|------|
| Warning | Maximale Anzahl erreicht | Sie können maximal 10 Felder auswählen. | Bei Klick auf 11. Feld |
| Danger | Fehler | Bitte wählen Sie mindestens ein Feld aus. | Submit ohne Auswahl |
| Danger | Ungültige Feld-Auswahl | Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden. | Submit mit ungültigem Rechteck |
| Danger | Ungültige Feld-Auswahl | Sie können maximal 10 Felder auswählen. | Submit mit >10 Feldern |
| Success | Anfrage erfolgreich gesendet! | Wir werden uns in Kürze bei Ihnen melden. | Erfolgreicher Submit |

---

## Testing

### Test-Szenarien

#### 1. Rechteck-Validierung

**Test 1: Gültiges 2×2 Rechteck**
```
Input: Felder (1,1), (1,2), (2,1), (2,2)
Expected: ✅ Valid
```

**Test 2: Ungültiges L-Form**
```
Input: Felder (1,1), (1,2), (2,1)
Expected: ❌ "Die ausgewählten Felder müssen ein zusammenhängendes Rechteck bilden."
```

**Test 3: Einzelnes Feld**
```
Input: Feld (1,1)
Expected: ✅ Valid (1×1 Rechteck)
```

**Test 4: Horizontale Linie**
```
Input: Felder (1,1), (1,2), (1,3)
Expected: ✅ Valid (1×3 Rechteck)
```

**Test 5: Vertikale Linie**
```
Input: Felder (1,1), (2,1), (3,1)
Expected: ✅ Valid (3×1 Rechteck)
```

**Test 6: Diagonal**
```
Input: Felder (1,1), (2,2), (3,3)
Expected: ❌ Invalid (kein Rechteck)
```

#### 2. Max-Fields-Validierung

**Test 1: Genau 10 Felder**
```
Input: 10 Felder als Rechteck
Expected: ✅ Valid
```

**Test 2: 11 Felder**
```
Input: 11 Felder
Expected: ❌ "Sie können maximal 10 Felder auswählen."
```

**Test 3: Live-Limit**
```
Action: Klick auf 11. Feld (während Auswahl)
Expected: ⚠️ Warning-Notification, Feld wird nicht ausgewählt
```

---

## Edge Cases

### Multi-Cell Felder

**Scenario**: Feld A (2×1) an (1,1) und Feld B (1×1) an (2,1)

```
┌───────┬───┐
│   A   │   │  A belegt (1,1) und (1,2)
├───┬───┼───┤
│ B │   │   │  B belegt (2,1)
└───┴───┴───┘

Felder: [A, B]
Positionen: [(1,1), (1,2), (2,1)]

→ Ungültig, da (2,2) fehlt
```

### Leere Auswahl
- Wird bereits vor Validator-Aufruf abgefangen
- Frühe Rückkehr mit spezifischer Fehlermeldung

### Ungültige Field-IDs
- Validator ignoriert fehlende Felder
- Andere Validierung sollte Existenz-Check durchführen

---

## Konfiguration

### Max-Fields ändern

In `InquiryPage.php`:

```php
// Aktuell: Max 10 Felder
new MaxFieldsCount(10)

// Ändern auf 15 Felder:
new MaxFieldsCount(15)
```

Vergiss nicht, auch die Live-Validierung anzupassen:

```php
if (count($this->selectedFields) >= 15) {
    // ...
}
```

### Custom Fehlermeldungen

```php
$validator = Validator::make(
    ['selected_fields' => $this->selectedFields],
    [
        'selected_fields' => [
            new MaxFieldsCount(10),
            new FieldsFormRectangle(),
        ],
    ],
    [
        // Custom Messages hier
    ]
);
```

---

## Zusammenfassung

Die Validierungsregeln stellen sicher:

✅ **Mindestens 1 Feld** muss ausgewählt sein
✅ **Maximal 10 Felder** können ausgewählt werden
✅ **Rechteck-Form** muss gebildet werden
✅ **Live-Feedback** für bessere UX
✅ **Multi-Cell Felder** werden korrekt berücksichtigt

Die Implementierung ist **robust** und **produktionsreif**! 🚀
