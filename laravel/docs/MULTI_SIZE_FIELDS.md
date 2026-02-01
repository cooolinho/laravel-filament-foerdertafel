# Multi-Size Fields Implementation

## Übersicht

Das Board-System unterstützt Felder mit verschiedenen Größen (z.B. 1x1, 2x2, 3x2). Felder können mehrere Grid-Positionen auf dem Board einnehmen.

## Technische Implementierung

### 1. Grid-Darstellung (`FieldsWidget`)

Die `getFieldsGrid()` Methode organisiert Felder in einer Grid-Struktur:

```php
// Erstelle ein leeres Grid
$grid = [];
for ($row = 1; $row <= $board->rows; $row++) {
    for ($col = 1; $col <= $board->columns; $col++) {
        $grid[$row][$col] = null;
    }
}

// Platziere Felder und markiere belegte Positionen
foreach ($fields as $field) {
    // Top-left Position = Field-Objekt
    $grid[$fieldRow][$fieldCol] = $field;
    
    // Alle anderen Positionen = 'occupied' (String-Marker)
    for ($r = $fieldRow; $r < $fieldRow + $fieldHeight; $r++) {
        for ($c = $fieldCol; $c < $fieldCol + $fieldWidth; $c++) {
            if ($r !== $fieldRow || $c !== $fieldCol) {
                $grid[$r][$c] = 'occupied';
            }
        }
    }
}
```

### 2. CSS Grid Layout

Im Blade-Template werden mehrzeilige Felder mit `grid-column: span X` und `grid-row: span Y` dargestellt:

```blade
@if($field === 'occupied')
    {{-- Skip - Teil eines größeren Feldes --}}
    @continue
@elseif($field)
    <div style="grid-column: span {{ $fieldWidth }}; grid-row: span {{ $fieldHeight }};">
        {{-- Feld-Inhalt --}}
    </div>
@else
    {{-- Leere Position --}}
@endif
```

### 3. Überlappungsprüfung

#### Im Widget (beim Erstellen neuer Felder):

```php
// Prüfe auf Überlappung
$rowOverlap = $newRow < $existingRow + $existingHeight && 
              $newRow + $newHeight > $existingRow;
$colOverlap = $newCol < $existingCol + $existingWidth && 
              $newCol + $newWidth > $existingCol;

return $rowOverlap && $colOverlap;
```

#### Im FieldSeeder:

Der Seeder verwendet die gleiche Logik in der `hasOverlap()` Methode:

```php
private function hasOverlap(int $newRow, int $newCol, int $newWidth, int $newHeight, array $existingFields): bool
{
    foreach ($existingFields as $existing) {
        $rowOverlap = $newRow < $existingRow + $existingHeight && 
                     $newRow + $newHeight > $existingRow;
        $colOverlap = $newCol < $existingCol + $existingWidth && 
                     $newCol + $newWidth > $existingCol;
        
        if ($rowOverlap && $colOverlap) {
            return true;
        }
    }
    return false;
}
```

## Beispiele

### Beispiel 1: 2x2 Feld

Ein 2x2 Feld an Position [1,1] belegt:
- [1,1] - Field-Objekt (top-left)
- [1,2] - 'occupied'
- [2,1] - 'occupied'
- [2,2] - 'occupied'

### Beispiel 2: 3x2 Feld

Ein 3x2 Feld an Position [2,3] belegt:
- [2,3] - Field-Objekt (top-left)
- [2,4] - 'occupied'
- [2,5] - 'occupied'
- [3,3] - 'occupied'
- [3,4] - 'occupied'
- [3,5] - 'occupied'

## Feldtypen im Seeder

Der FieldSeeder erstellt verschiedene Feldgrößen:

| Feldtyp | Größe | Preis | Beispiele |
|---------|-------|-------|-----------|
| Premium | 2x2 | 400-500€ | Tor, Elfmeterpunkt, Mittelkreis |
| Premium+ | 3x2 | 350€ | Strafraum Links/Rechts |
| Mittelfeld | 2x1 | 200€ | Mittellinie, Mittelfeld |
| Standard | 1x1 | 100-120€ | Außenlinie, Eckfahne, Standardfelder |

## Validierung

### Beim Erstellen eines Feldes:

1. **Board-Grenzen**: Prüfung ob `row + height <= board.rows` und `column + width <= board.columns`
2. **Überlappung**: Prüfung ob keine Position bereits von einem anderen Feld belegt ist
3. **Minimum**: Width und Height müssen mindestens 1 sein

### Fehlermeldungen:

- ❌ "Feld zu groß" - Feld passt nicht vollständig auf das Board
- ❌ "Position bereits belegt" - Feld überschneidet sich mit bestehendem Feld
- ✅ "Feld erstellt" - Erfolgreich erstellt

## UI/UX Features

### Visuelle Darstellung:
- Größere Felder nehmen mehr Platz im Grid ein
- Badge zeigt Größe an (z.B. "2x2", "3x2")
- Hover-Effekte funktionieren auf dem gesamten Feld
- Quick Actions sind am Top-Right positioniert

### Leere Positionen:
- Nur wirklich leere Positionen zeigen den "+" Button
- 'occupied' Positionen werden übersprungen
- Klick öffnet Modal mit vorausgefüllter Position

## Best Practices

1. **Größere Felder zuerst**: Beim Seeding werden größere Felder zuerst platziert
2. **Auffüllen mit 1x1**: Leere Positionen werden mit Standard 1x1 Feldern gefüllt
3. **Positionierung**: Row und Column sind 1-basiert (starten bei 1, nicht 0)
4. **CSS Grid**: Nutzt native CSS Grid Features für optimale Performance
