# Board Hintergrundbild & Grid-Positionierung

## Übersicht

Boards können jetzt ein Hintergrundbild erhalten (z.B. Luftaufnahme eines Stadions) und das Feld-Raster kann mit X/Y-Offsets präzise über dem Spielfeld positioniert werden.

## Anwendungsfall

**Beispiel:** Luftaufnahme eines Fußballstadions
- Das Spielfeld ist zentral im Bild
- Drumherum sind Zuschauerränge, Tribünen, Parkplätze, etc.
- Die Werbefelder sollen nur über dem eigentlichen Spielfeld liegen
- Mit Grid-Offset wird das Raster präzise positioniert

## Neue Board-Eigenschaften

### 1. Background Image (`background_image`)
- **Typ:** String (Dateipfad)
- **Format:** JPG, PNG
- **Max. Größe:** 5MB
- **Speicherort:** `storage/app/public/board-backgrounds/`
- **URL:** `Storage::url($board->background_image)`

### 2. Grid Offset X (`grid_offset_x`)
- **Typ:** Integer (Pixel)
- **Default:** 0
- **Beschreibung:** Horizontaler Versatz des Rasters vom linken Bildrand

### 3. Grid Offset Y (`grid_offset_y`)
- **Typ:** Integer (Pixel)
- **Default:** 0
- **Beschreibung:** Vertikaler Versatz des Rasters vom oberen Bildrand

## Datenbankschema

### Migration
```php
$table->string(Board::background_image)->nullable();
$table->integer(Board::grid_offset_x)->default(0);
$table->integer(Board::grid_offset_y)->default(0);
```

### Model-Konstanten
```php
const string background_image = 'background_image';
const string grid_offset_x = 'grid_offset_x';
const string grid_offset_y = 'grid_offset_y';
```

## Filament Form-Konfiguration

### Background & Positioning Section
```php
Section::make('Background & Positioning')
    ->description('Laden Sie ein Hintergrundbild hoch...')
    ->schema([
        FileUpload::make(Board::background_image)
            ->label('Hintergrundbild')
            ->image()
            ->imageEditor()
            ->maxSize(5120) // 5MB
            ->directory('board-backgrounds'),

        TextInput::make(Board::grid_offset_x)
            ->label('Grid Offset X (Pixel)')
            ->numeric()
            ->suffix('px'),

        TextInput::make(Board::grid_offset_y)
            ->label('Grid Offset Y (Pixel)')
            ->numeric()
            ->suffix('px'),
    ])
```

### Features
- **Image Editor:** Integrierter Bildeditor zum Zuschneiden/Anpassen
- **Aspect Ratios:** Voreingestellte Seitenverhältnisse (16:9, 4:3, 1:1)
- **Downloadable:** Bilder können heruntergeladen werden
- **Helper Text:** Hilfreiche Beschreibungen für Benutzer

## Widget-Darstellung

### HTML-Struktur
```blade
<div style="background-image: url('{{ Storage::url($backgroundImage) }}'); 
            background-size: cover;">
    <div class="grid" style="margin-left: {{ $offsetX }}px; 
                              margin-top: {{ $offsetY }}px;">
        <!-- Grid Fields -->
    </div>
</div>
```

### CSS-Eigenschaften
```css
.background-container {
    background-size: cover;      /* Bild auf volle Größe skalieren */
    background-position: center; /* Bild zentrieren */
    background-repeat: no-repeat; /* Keine Wiederholung */
}

.grid-container {
    margin-left: ${offsetX}px;   /* Horizontaler Offset */
    margin-top: ${offsetY}px;    /* Vertikaler Offset */
}
```

### Feld-Transparenz
Wenn ein Hintergrundbild vorhanden ist, werden die Felder halbtransparent:

```php
// Mit Hintergrundbild
'bg-green-500/30 backdrop-blur-sm'  // 30% Opacity + Blur-Effekt

// Ohne Hintergrundbild
'bg-green-50 dark:bg-green-950'     // Solide Farbe
```

**Transparenz-Stufen:**
- **Verfügbar:** Grün mit 30% Opacity
- **Vermietet:** Gelb mit 30% Opacity
- **Reserviert:** Blau mit 30% Opacity
- **Backdrop Blur:** Leichter Unschärfe-Effekt für bessere Lesbarkeit

## Workflow: Hintergrundbild einrichten

### Schritt 1: Board bearbeiten
1. Navigiere zu Board → Edit
2. Scrolle zu "Background & Positioning" Section

### Schritt 2: Bild hochladen
1. Klicke auf "Hintergrundbild" Upload-Feld
2. Wähle Luftaufnahme des Stadions (max. 5MB)
3. Optional: Nutze den Image Editor zum Zuschneiden
4. Speichere das Board

### Schritt 3: Raster positionieren
1. Öffne die Board-Detailansicht
2. Betrachte das FieldsWidget mit Hintergrundbild
3. Notiere, wie weit das Raster verschoben werden muss
4. Bearbeite das Board erneut
5. Trage Grid Offset X und Y ein (in Pixel)
6. Speichere und überprüfe die Positionierung

### Schritt 4: Feintuning
Wiederhole Schritt 3 bis das Raster perfekt über dem Spielfeld liegt.

## Beispiel-Konfiguration

### Fußballstadion Luftaufnahme
```
Bildgröße: 1920x1080px
Spielfeld-Position: 
  - Starts bei X: 240px, Y: 150px
  - Endet bei X: 1680px, Y: 930px

Grid-Konfiguration:
  - Rows: 10
  - Columns: 15
  - Grid Offset X: 240px
  - Grid Offset Y: 150px
```

### Berechnung der Offsets
```
offset_x = (Bildbreite - Spielfeldbreite) / 2 + Rand_links
offset_y = (Bildhöhe - Spielfeldhöhe) / 2 + Rand_oben
```

## Visuelle Darstellung

### Ohne Offset (Standard)
```
┌─────────────────────────────────┐
│ [Grid startet hier]             │
│  ╔═══╗╔═══╗╔═══╗                │
│  ║ 1 ║║ 2 ║║ 3 ║  Spielfeld     │
│  ╚═══╝╚═══╝╚═══╝                │
│                                  │
│      (Zuschauerränge)            │
└─────────────────────────────────┘
```

### Mit Offset (Korrekt positioniert)
```
┌─────────────────────────────────┐
│   (Zuschauerränge)               │
│                                  │
│         ┌───────────┐            │
│         │╔═══╗╔═══╗│            │
│ Offset→ │║ 1 ║║ 2 ║│ Spielfeld  │
│         │╚═══╝╚═══╝│            │
│         └───────────┘            │
└─────────────────────────────────┘
```

## Storage-Konfiguration

### Symlink erstellen
```bash
php artisan storage:link
```

Dies erstellt einen symbolischen Link von `public/storage` zu `storage/app/public`.

### Bildpfad-Zugriff
```php
// Speichern
$board->background_image = $request->file('background_image')
    ->store('board-backgrounds', 'public');

// Abrufen
$url = Storage::url($board->background_image);
// Output: /storage/board-backgrounds/filename.jpg
```

## Best Practices

### Bildoptimierung
1. **Format:** JPG für Fotos, PNG für Grafiken
2. **Auflösung:** Maximal 1920x1080px (Full HD)
3. **Dateigröße:** Unter 2MB für schnellere Ladezeiten
4. **Kompression:** 80-90% Qualität ist ausreichend

### Grid-Positionierung
1. **Screenshot:** Mache einen Screenshot der Widget-Ansicht
2. **Bildbearbeitung:** Öffne in Photoshop/GIMP
3. **Messen:** Miss die Pixel-Abstände zum Spielfeld
4. **Eintragen:** Übertrage die Werte ins Form
5. **Testen:** Überprüfe die Positionierung

### Performance
- Verwende optimierte Bilder (WebP, komprimiertes JPG)
- Lazy Loading bei vielen Boards
- CDN für große Bilddateien
- Cache die Storage-URLs

## Fehlerbehebung

### Bild wird nicht angezeigt
1. ✓ Prüfe ob `php artisan storage:link` ausgeführt wurde
2. ✓ Prüfe Dateiberechtigungen in `storage/app/public`
3. ✓ Prüfe ob Pfad in Datenbank korrekt ist
4. ✓ Prüfe Browser-Konsole auf 404-Fehler

### Raster ist falsch positioniert
1. ✓ Überprüfe Offset-Werte (positiv/negativ)
2. ✓ Prüfe ob Bild die richtige Größe hat
3. ✓ Beachte Zoom-Level des Browsers
4. ✓ Teste mit verschiedenen Bildschirmgrößen

### Felder sind nicht sichtbar
1. ✓ Erhöhe Transparenz (30% → 50%)
2. ✓ Füge stärkeren Border hinzu (border-3)
3. ✓ Verwende dunkleres Hintergrundbild
4. ✓ Aktiviere backdrop-blur für besseren Kontrast

## Zukünftige Erweiterungen

### Mögliche Features
- [ ] Zoom/Pan-Funktionalität für das Hintergrundbild
- [ ] Drag & Drop für Grid-Positionierung
- [ ] Mehrere Hintergrundbilder pro Board (Tag/Nacht)
- [ ] Automatische Spielfeld-Erkennung per AI
- [ ] Grid-Rotation für schräge Perspektiven
- [ ] Responsive Offsets für verschiedene Bildschirmgrößen
