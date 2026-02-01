# CSS Grid Layout für Multi-Size Felder

## Das Problem

Wenn ein Feld eine `width=2` und `height=2` hat und an Position `[1,1]` startet, belegt es:
- Spalten: 1 und 2 (width)
- Reihen: 1 und 2 (height)

Das bedeutet, die Positionen `[1,1]`, `[1,2]`, `[2,1]`, `[2,2]` sind alle belegt.

## Die Lösung

### 1. Backend (PHP Widget)

```php
$grid = [];
foreach ($fields as $field) {
    // Top-left Position = Field-Objekt
    $grid[$field->row][$field->column] = $field;
    
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

**Ergebnis für ein 2x2 Feld an [1,1]:**
```
Grid[1][1] = Field Object
Grid[1][2] = 'occupied'
Grid[2][1] = 'occupied'
Grid[2][2] = 'occupied'
```

### 2. Frontend (Blade Template)

#### CSS Grid Setup
```blade
<div class="grid" style="
    grid-template-columns: repeat(5, minmax(120px, 1fr));
    grid-template-rows: repeat(5, minmax(120px, auto));
">
```

Dies erstellt ein explizites 5×5 Grid.

#### Rendering-Logik

```blade
@foreach($grid as $row => $columns)
    @foreach($columns as $col => $field)
        @if($field === 'occupied')
            {{-- Unsichtbarer Platzhalter --}}
            <div style="grid-column: {{ $col }}; grid-row: {{ $row }};" class="pointer-events-none"></div>
            
        @elseif($field)
            {{-- Echtes Feld mit Span --}}
            <div style="grid-column: {{ $col }} / span {{ $width }}; grid-row: {{ $row }} / span {{ $height }};">
                <!-- Feld-Inhalt -->
            </div>
            
        @else
            {{-- Leere Position --}}
            <button style="grid-column: {{ $col }}; grid-row: {{ $row }};">
                <!-- + Button -->
            </button>
        @endif
    @endforeach
@endforeach
```

### 3. Warum funktioniert das?

#### CSS Grid Positionierung

**Syntax:** `grid-column: start / span count`

Beispiel für ein 2x2 Feld an Position [1,1]:
```css
grid-column: 1 / span 2;  /* Startet bei Spalte 1, belegt 2 Spalten */
grid-row: 1 / span 2;     /* Startet bei Reihe 1, belegt 2 Reihen */
```

Dies platziert das Element:
- **Horizontal:** Von Spalte 1 bis Spalte 3 (1 + 2)
- **Vertikal:** Von Reihe 1 bis Reihe 3 (1 + 2)

#### Die 'occupied' Platzhalter

Ohne Platzhalter würde CSS Grid die Elemente falsch positionieren, da es nicht weiß, dass manche Positionen "übersprungen" werden sollen.

**Mit Platzhaltern:**
```
[Field 2x2] [occupied] [Empty] [Empty] [Empty]
[occupied]  [occupied] [Empty] [Empty] [Empty]
[Empty]     [Empty]    [Empty] [Empty] [Empty]
```

Das 2x2 Feld "überschreibt" visuell die 'occupied' Platzhalter durch `z-index` und absolute Positionierung.

## Beispiel: 5×5 Board mit verschiedenen Feldgrößen

### Board Layout
```
+-------+-------+-------+-------+-------+
|  Tor  |  Tor  | Empty | Empty | Empty |  Reihe 1
|  2x2  | (occ) |       |       |       |
+-------+-------+-------+-------+-------+
| (occ) | (occ) | Strafraum 3x2 | (occ) |  Reihe 2
|       |       +-------+-------+-------+
+-------+-------+ (occ) | (occ) | (occ) |  Reihe 3
| 1x1   | 1x1   |       |       |       |
+-------+-------+-------+-------+-------+
| Empty | Empty | Empty | Empty | Empty |  Reihe 4
+-------+-------+-------+-------+-------+
| Empty | Empty | Empty | Empty | Empty |  Reihe 5
+-------+-------+-------+-------+-------+
```

### Backend Grid-Array
```php
[1][1] = Field(Tor, 2x2)
[1][2] = 'occupied'
[1][3] = null
[1][4] = null
[1][5] = null

[2][1] = 'occupied'
[2][2] = 'occupied'
[2][3] = Field(Strafraum, 3x2)
[2][4] = 'occupied'
[2][5] = 'occupied'

[3][1] = Field(1x1)
[3][2] = Field(1x1)
[3][3] = 'occupied'
[3][4] = 'occupied'
[3][5] = 'occupied'

[4][1] = null
[4][2] = null
// ... etc
```

### Gerenderte HTML-Struktur
```html
<!-- Row 1 -->
<div style="grid-column: 1 / span 2; grid-row: 1 / span 2;">Tor (2x2)</div>
<div style="grid-column: 2; grid-row: 1;" class="pointer-events-none"></div>
<button style="grid-column: 3; grid-row: 1;">+ Empty</button>
<button style="grid-column: 4; grid-row: 1;">+ Empty</button>
<button style="grid-column: 5; grid-row: 1;">+ Empty</button>

<!-- Row 2 -->
<div style="grid-column: 1; grid-row: 2;" class="pointer-events-none"></div>
<div style="grid-column: 2; grid-row: 2;" class="pointer-events-none"></div>
<div style="grid-column: 3 / span 3; grid-row: 2 / span 2;">Strafraum (3x2)</div>
<div style="grid-column: 4; grid-row: 2;" class="pointer-events-none"></div>
<div style="grid-column: 5; grid-row: 2;" class="pointer-events-none"></div>

<!-- Row 3 -->
<div style="grid-column: 1; grid-row: 3;">Field 1x1</div>
<div style="grid-column: 2; grid-row: 3;">Field 1x1</div>
<div style="grid-column: 3; grid-row: 3;" class="pointer-events-none"></div>
<div style="grid-column: 4; grid-row: 3;" class="pointer-events-none"></div>
<div style="grid-column: 5; grid-row: 3;" class="pointer-events-none"></div>
```

## Key Points

1. **Jede Grid-Position wird gerendert** - entweder als Feld, 'occupied' Platzhalter, oder leerer Button
2. **Größere Felder verwenden `span`** - um mehrere Grid-Zellen zu belegen
3. **'occupied' Platzhalter sind unsichtbar** - `pointer-events-none` verhindert Interaktion
4. **Explizite Grid-Positionierung** - `grid-column: {{ $col }}; grid-row: {{ $row }};`
5. **Z-Index** - Größere Felder haben `z-10` bei Hover-Actions

## Wichtige CSS-Eigenschaften

```css
.grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(120px, 1fr));  /* 5 explizite Spalten */
    grid-template-rows: repeat(5, minmax(120px, auto));    /* 5 explizite Reihen */
    gap: 12px;                                              /* Abstand zwischen Zellen */
}

.field-2x2 {
    grid-column: 1 / span 2;   /* Belegt 2 Spalten */
    grid-row: 1 / span 2;      /* Belegt 2 Reihen */
}

.occupied-placeholder {
    pointer-events: none;       /* Keine Interaktion möglich */
    opacity: 0;                 /* Unsichtbar */
}
```

## Debugging-Tipps

Um zu sehen, welche Positionen belegt sind, kann man temporär die 'occupied' Platzhalter sichtbar machen:

```blade
@if($field === 'occupied')
    <div style="grid-column: {{ $col }}; grid-row: {{ $row }}; background: red; opacity: 0.3;">
        occupied
    </div>
@endif
```

Dies zeigt rote Bereiche an, die von größeren Feldern belegt werden.
