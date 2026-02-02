# BoardPage - Kundenansicht

## Übersicht

Die BoardPage ist eine öffentliche/Frontend-Ansicht des Boards, die für Kunden gedacht ist. Sie zeigt das Board mit Hintergrundbild und allen Feldern in einer übersichtlichen Darstellung.

## Unterschied zum Admin FieldsWidget

| Feature | Admin (FieldsWidget) | Frontend (BoardPage) |
|---------|---------------------|----------------------|
| Hintergrundbild | ❌ Nein | ✅ Ja |
| Transparenz | Solide Farben | 20% Opacity + Backdrop Blur |
| Informationen | Viele Details | Fokus auf Verfügbarkeit |
| Interaktion | Edit/Create | Ansehen (später: Anfragen) |
| Zielgruppe | Administratoren | Kunden |

## Grid-Positionierung

### Im Frontend (BoardPage)

Das Hintergrundbild wird über den gesamten Container gestreckt (`background-size: 100% 100%`):

```blade
<div style="background-image: url('...');
            background-size: 100% 100%;
            min-height: 600px;">
    
    <div style="padding-left: {{ $offsetX }}px;
                padding-top: {{ $offsetY }}px;
                padding-right: {{ $offsetX }}px;
                padding-bottom: {{ $offsetY }}px;">
        
        <div class="grid" style="gap: {{ $gap }}px;">
            <!-- Felder -->
        </div>
    </div>
</div>
```

**Wichtig:**
- **Padding statt Margin**: Positioniert das Raster innerhalb des Backgrounds
- **Gleichmäßiges Padding**: Offset wird auf alle Seiten angewendet
- **100% 100% Size**: Bild füllt Container vollständig aus

### Im Admin (FieldsWidget)

Kein Hintergrundbild, nur der konfigurierbare Gap:

```blade
<div class="grid" style="gap: {{ $gap }}px;">
    <!-- Felder -->
</div>
```

## Neue Board-Eigenschaft: grid_gap

### Datenbank
```php
$table->integer(Board::grid_gap)->default(12);
```

### Model
```php
const string grid_gap = 'grid_gap';
```

### Zweck
- Bestimmt den Abstand zwischen den Feldern im Raster
- In Pixel (px)
- Default: 12px
- Range: 0-50px

## Feld-Darstellung

### Verfügbare Felder

```blade
<div class="bg-green-500/20 border-green-500">
    <svg><!-- Checkmark Icon --></svg>
    <div>Verfügbar</div>
    <div>150.00 €</div>
    <div>pro Monat</div>
</div>
```

**Features:**
- ✅ Grüner Border + 20% grüner Background
- ✅ Checkmark-Icon
- ✅ Preis prominent dargestellt
- ✅ Hover-Effekt (10% extra Opacity)
- ✅ Cursor: pointer

### Vermietete Felder

```blade
<div class="bg-yellow-500/20 border-yellow-500">
    <div>Vermietet an:</div>
    <div>Kunde Name</div>
    <div>Firma GmbH</div>
    <div>bis 31.12.2026</div>
    <div>150.00 €/Monat</div>
</div>
```

**Features:**
- 🟡 Gelber Border + 20% gelber Background
- 🏢 Kundenname + Firma
- 📅 Mietdauer bis-Datum
- 💰 Preis im Footer

### Mehrzeilige Felder (Same Customer)

Wenn ein Kunde mehrere aneinander liegende Felder mietet:

```php
// Backend: Separate Fields mit gleicher Rental
Field 1: [1,1] 2x2 → Rental #5 → Customer "ABC GmbH"
Field 2: [1,3] 1x1 → Rental #5 → Customer "ABC GmbH"
```

**Frontend-Darstellung:**
- Jedes Feld wird separat gerendert
- Beide zeigen "ABC GmbH" als Mieter
- Gleiche Optik/Farbgebung
- Visuell zusammenhängend durch Border-Farbe

**Hinweis:** Für zukünftige Erweiterung könnten Felder mit gleicher Rental gruppiert werden.

## Statistiken

Am unteren Rand der Seite werden drei Cards angezeigt:

```blade
<div class="grid grid-cols-3 gap-4">
    <div>{{ $totalFields }} Gesamt Felder</div>
    <div>{{ $availableFields }} Verfügbar</div>
    <div>{{ $rentedFields }} Vermietet</div>
</div>
```

## Workflow: Board konfigurieren

### 1. Hintergrundbild hochladen
1. Admin-Panel → Boards → Board bearbeiten
2. Section "Background & Positioning"
3. Bild hochladen (max. 5MB)

### 2. Grid-Offset einstellen
1. Öffne BoardPage im Frontend
2. Betrachte wo das Raster liegt
3. Zurück zum Admin
4. Trage Offset X/Y ein (in Pixel)
5. Speichern

### 3. Gap anpassen
1. Bestimme gewünschten Abstand zwischen Feldern
2. Trage Gap ein (0-50px)
3. Speichern

### 4. Testen
1. Öffne BoardPage erneut
2. Prüfe Positionierung
3. Bei Bedarf Offset/Gap anpassen

## Zukünftige Features

### Geplante Erweiterungen
- [ ] **Board-Auswahl**: Dropdown zum Wechseln zwischen Boards
- [ ] **Anfrage-Prozess**: Kunden können Felder anfragen/mieten
- [ ] **Multi-Field-Auswahl**: Mehrere Felder auf einmal auswählen
- [ ] **Upload-Funktion**: Kunden können Content hochladen
- [ ] **Preis-Rechner**: Automatische Berechnung bei Multi-Field-Auswahl
- [ ] **Filter**: Nur verfügbare/vermietete Felder anzeigen
- [ ] **Zoom/Pan**: Bei großen Boards

### Anfrage-Prozess (Konzept)

```
1. Kunde wählt Feld(er) aus
2. Anfrage-Formular öffnet sich
3. Kunde gibt Daten ein:
   - Name, Firma
   - Email, Telefon
   - Gewünschte Mietdauer
   - Upload: Logo/Content
4. Anfrage wird gespeichert
5. Admin prüft Anfrage
6. Admin erstellt Rental
7. Kunde erhält Bestätigung
```

## Technische Details

### Routes
```php
// Automatisch durch Filament generiert
/app/board?board={id}
```

### URL-Parameter
- `?board=1` - Zeigt Board mit ID 1
- Ohne Parameter: Erstes verfügbares Board

### Performance-Optimierung

**Eager Loading:**
```php
$board = Board::with([
    'fields.rentals' => function ($query) {
        $query->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    },
    'fields.rentals.customer'
])->find($id);
```

**Caching (Optional):**
```php
$board = Cache::remember("board.$id", 300, function () use ($id) {
    return Board::with(...)->find($id);
});
```

### Responsive Design

Das Grid passt sich automatisch an:

```css
/* Desktop: Full Grid */
grid-template-columns: repeat(15, minmax(100px, 1fr));

/* Tablet: Horizontal Scroll */
overflow-x: auto;

/* Mobile: Einzelne Spalten-Ansicht möglich */
@media (max-width: 768px) {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
}
```

## Styling-Anpassungen

### Farben anpassen
```php
// Im Blade-Template
$borderColor = match($fieldStatus) {
    'available' => 'border-green-500 bg-green-500/20',
    'rented' => 'border-yellow-500 bg-yellow-500/20',
    'reserved' => 'border-blue-500 bg-blue-500/20',
};
```

### Transparenz anpassen
```blade
{{-- 20% Opacity --}}
bg-green-500/20

{{-- Mehr Transparenz: 30% --}}
bg-green-500/30

{{-- Weniger Transparenz: 10% --}}
bg-green-500/10
```

### Background-Stretch anpassen
```css
/* Aktuell: Vollständig gestreckt */
background-size: 100% 100%;

/* Alternative: Cover (behält Seitenverhältnis) */
background-size: cover;

/* Alternative: Contain (zeigt gesamtes Bild) */
background-size: contain;
```

## Troubleshooting

### Felder sind nicht sichtbar
**Problem:** Felder verschmelzen mit Hintergrund
**Lösung:** 
- Erhöhe Border-Width: `border-3` → `border-4`
- Erhöhe Opacity: `/20` → `/40`
- Füge Text-Shadow hinzu: `text-shadow: 0 0 4px black;`

### Grid ist falsch positioniert
**Problem:** Raster liegt nicht über Spielfeld
**Lösung:**
- Prüfe Offset X/Y Werte
- Beachte: Padding wird auf alle Seiten angewendet
- Teste mit `background-size: contain` für bessere Übersicht

### Bild wird verzerrt
**Problem:** Hintergrundbild ist gestaucht/gestreckt
**Lösung:**
- Verwende `background-size: cover` statt `100% 100%`
- Lade Bild im passenden Seitenverhältnis hoch
- Nutze Image Editor zum Zuschneiden

### Performance-Probleme
**Problem:** Seite lädt langsam
**Lösung:**
- Komprimiere Hintergrundbild (< 1MB)
- Aktiviere Caching für Board-Daten
- Lazy-Load für Bilder: `loading="lazy"`

## Best Practices

1. **Bildoptimierung**
   - Format: WebP oder JPG
   - Auflösung: 1920x1080px
   - Größe: < 1MB
   - Qualität: 80%

2. **Grid-Konfiguration**
   - Rows/Columns: 8-15 für optimale Darstellung
   - Gap: 8-16px für gute Balance
   - Offset: In 10px-Schritten anpassen

3. **Testing**
   - Teste auf verschiedenen Bildschirmgrößen
   - Prüfe mit/ohne Hintergrundbild
   - Teste mit vielen/wenigen Feldern
   - Browser: Chrome, Firefox, Safari

4. **Accessibility**
   - Kontrast: Mindestens 4.5:1
   - Schriftgröße: Mindestens 14px
   - Focus-States für Keyboard-Navigation
   - Alt-Texte für Icons
