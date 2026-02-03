# Dashboard-Konfiguration für Benutzer

## Übersicht

Benutzer können nun ihr Dashboard individuell konfigurieren, indem sie auswählen:
- **Welche Widgets** angezeigt werden sollen (Sichtbarkeit)
- **In welcher Reihenfolge** die Widgets erscheinen (Drag & Drop Sortierung)

## Komponenten

### 1. Datenbank

**Tabelle:** `user_dashboard_settings`

Felder:
- `id` - Primary Key
- `user_id` - Foreign Key zu users Tabelle
- `visible_widgets` - JSON Array mit den sichtbaren Widget-Klassen
- `widget_order` - JSON Array für zukünftige Sortierung (noch nicht implementiert)
- `created_at`, `updated_at` - Timestamps

**Migration:** `2026_02_03_160000_create_user_dashboard_settings_table.php`

### 2. Model

**Model:** `App\Models\UserDashboardSetting`

Beziehungen:
- `user()` - BelongsTo Beziehung zu User Model

Das User Model wurde erweitert mit:
- `dashboardSettings()` - HasOne Beziehung zu UserDashboardSetting

### 3. Einstellungsseite

**Seite:** `App\Filament\Admin\Pages\UserDashboardSettingsPage`

Features:
- Checkbox-Liste aller verfügbaren Widgets (Sichtbarkeit)
- Sortierbare Widget-Reihenfolge mit Drag & Drop
- Speichern-Button zum Persistieren der Auswahl
- Zurücksetzen-Button zum Wiederherstellen der Standardeinstellungen
- Nicht in der Navigation sichtbar (`shouldRegisterNavigation = false`)
- Erreichbar über das Benutzermenü

Verfügbare Widgets:
- Statistik Übersicht (StatsOverviewWidget)
- Neueste Anfragen (RecentInquiriesWidget)
- Aktive Vermietungen (ActiveRentalsWidget)
- Auslaufende Vermietungen (ExpiringRentalsWidget)
- Feldstatus (FieldStatusWidget)
- Inkonsistente Felder (InconsistentFieldStatusWidget)
- Umsatz Chart (RevenueChartWidget)

### 4. Dashboard Integration

**Seite:** `App\Filament\Admin\Pages\Dashboard`

Die `getWidgets()` Methode wurde angepasst:
- Lädt die Dashboard-Einstellungen des aktuellen Benutzers
- Berücksichtigt die widget_order für die Anzeigereihenfolge
- Filtert die Widgets basierend auf den visible_widgets Einstellungen
- Zeigt alle Widgets in Standardreihenfolge, wenn keine Einstellungen vorhanden sind

### 5. Benutzermenü Integration

**Provider:** `App\Providers\Filament\AdminPanelProvider`

Im Panel wurde ein neuer Menüpunkt hinzugefügt:
- "Dashboard Einstellungen" im Benutzermenü
- Icon: `heroicon-o-cog-6-tooth`
- Verlinkt auf die UserDashboardSettingsPage

## Verwendung

### Als Benutzer

1. Klicke auf dein Benutzermenü (oben rechts)
2. Wähle "Dashboard Einstellungen"
3. **Sichtbarkeit:** Wähle die Widgets aus, die du sehen möchtest (mindestens 1)
4. **Reihenfolge:** Ziehe die Widgets in die gewünschte Reihenfolge (Drag & Drop)
   - Die Reihenfolge bestimmt, in welcher Reihenfolge die Widgets auf dem Dashboard angezeigt werden
   - Du kannst Widgets hinzufügen oder entfernen
   - Widgets lassen sich per Drag & Drop neu anordnen
5. Klicke auf "Einstellungen speichern"
6. Gehe zurück zum Dashboard - die Widgets werden in der gewählten Reihenfolge und Sichtbarkeit angezeigt

Um zu den Standardeinstellungen zurückzukehren:
1. Öffne "Dashboard Einstellungen"
2. Klicke auf "Zurücksetzen"

### Migration ausführen

```bash
php artisan migrate
```

## Technische Details

### Datenbankstruktur

```php
Schema::create('user_dashboard_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->json('visible_widgets')->nullable();
    $table->json('widget_order')->nullable();
    $table->timestamps();
    $table->unique('user_id');
});
```

### Widget-Filter-Logik

```php
public function getWidgets(): array
{
    $user = auth()->user();
    $settings = $user?->dashboardSettings;

    // If user has custom settings, use them
    if ($settings) {
        $visibleWidgets = $settings->visible_widgets ?? [];
        $widgetOrder = $settings->widget_order ?? [];

        // If we have a widget order, use it
        if (!empty($widgetOrder)) {
            // Filter by visible widgets and maintain order
            $orderedWidgets = [];
            foreach ($widgetOrder as $widgetClass) {
                // Only include if it's visible and exists in available widgets
                if (in_array($widgetClass, $visibleWidgets) && in_array($widgetClass, self::$defaultWidgets)) {
                    $orderedWidgets[] = $widgetClass;
                }
            }
            
            // Add any visible widgets that are not in the order
            foreach ($visibleWidgets as $widgetClass) {
                if (in_array($widgetClass, self::$defaultWidgets) && !in_array($widgetClass, $orderedWidgets)) {
                    $orderedWidgets[] = $widgetClass;
                }
            }
            
            return $orderedWidgets;
        }
        
        // If we only have visible widgets (no order), filter by visibility
        if (!empty($visibleWidgets)) {
            return array_filter(self::$defaultWidgets, function ($widget) use ($visibleWidgets) {
                return in_array($widget, $visibleWidgets);
            });
        }
    }

    return self::$defaultWidgets;
}
```

## Zukünftige Erweiterungen

Mögliche Erweiterungen:
- Widget-spezifische Einstellungen (z.B. Anzahl der angezeigten Einträge)
- Widget-Größenanpassung (full width, half width)
- Export/Import von Dashboard-Konfigurationen
- Vordefinierte Dashboard-Templates

## View-Datei

**Pfad:** `resources/views/filament/admin/pages/user-dashboard-settings-page.blade.php`

Einfache Blade-View, die das Filament-Formular rendert:

```blade
<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            {{ $this->getFormActions() }}
        </div>
    </form>
</x-filament-panels::page>
```
