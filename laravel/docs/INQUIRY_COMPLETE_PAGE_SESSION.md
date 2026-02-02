# InquiryCompletePage - Session-basierte Zugriffskontrolle

## Übersicht
Die InquiryCompletePage verwendet eine Session-Variable zur Zugriffskontrolle. Dies verhindert, dass Benutzer die Bestätigungsseite direkt aufrufen oder mehrfach aktualisieren können.

## Implementierung

### 1. Session-Variable setzen (InquiryPage)

Nach erfolgreicher Erstellung der Anfrage wird eine Session-Variable gesetzt:

```php
// In InquiryPage::submit()
session(['inquiry_complete' => $inquiry->id]);
$this->redirect(route('filament.app.pages.inquiry-complete-page'));
```

**Wichtige Punkte:**
- Die Session-Variable heißt `inquiry_complete`
- Der Wert ist die ID der neu erstellten Anfrage
- Die Weiterleitung erfolgt OHNE URL-Parameter

### 2. Zugriffskontrolle (InquiryCompletePage)

Die InquiryCompletePage implementiert zwei Schutzmechanismen:

#### a) Static canAccess() Methode
```php
public static function canAccess(): bool
{
    return session()->has('inquiry_complete');
}
```

Diese Methode wird von Filament automatisch aufgerufen, bevor die Seite geladen wird. Gibt sie `false` zurück, wird der Zugriff verweigert.

#### b) mount() Methode mit Session-Prüfung
```php
public function mount(): void
{
    // Check if session variable exists
    if (!session()->has('inquiry_complete')) {
        $this->redirect(InquiryPage::getUrl());
        return;
    }

    $inquiryId = session('inquiry_complete');

    // Load inquiry with relations
    $this->inquiry = Inquiry::with(['board', 'fields'])
        ->find($inquiryId);

    if (!$this->inquiry) {
        // Clear session and redirect if inquiry not found
        session()->forget('inquiry_complete');
        $this->redirect(InquiryPage::getUrl());
        return;
    }

    // Clear the session variable after loading to prevent page refresh
    session()->forget('inquiry_complete');
}
```

**Ablauf:**
1. Prüfung, ob Session-Variable existiert
2. Laden der Anfrage anhand der ID aus der Session
3. Falls Anfrage nicht gefunden: Session löschen und umleiten
4. **Wichtig:** Session-Variable wird nach dem Laden gelöscht

### 3. Session-Lebenszyklus

```
1. Kunde füllt Formular aus
   ↓
2. submit() wird aufgerufen
   ↓
3. Anfrage wird in DB gespeichert
   ↓
4. Session-Variable wird gesetzt: session(['inquiry_complete' => $inquiry->id])
   ↓
5. Redirect zu InquiryCompletePage
   ↓
6. canAccess() prüft Session-Variable → true
   ↓
7. mount() lädt Anfrage-Daten
   ↓
8. Session-Variable wird gelöscht: session()->forget('inquiry_complete')
   ↓
9. Seite wird angezeigt
   ↓
10. Bei Reload: canAccess() → false (Session-Variable existiert nicht mehr)
    ↓
11. Automatische Weiterleitung zur InquiryPage
```

## Sicherheitsaspekte

### 1. Einmaliger Zugriff
Die Session-Variable wird sofort nach dem ersten Laden gelöscht. Dies verhindert:
- Mehrfaches Aufrufen der Seite durch Reload
- Bookmarking der Bestätigungsseite
- Teilen des Links mit anderen

### 2. Keine URL-Parameter
Die Anfrage-ID wird NICHT in der URL übergeben. Vorteile:
- Keine sichtbare ID in der URL
- Kein direkter Zugriff durch Manipulation der URL
- Sauberere URLs

### 3. Server-seitige Validierung
Alle Prüfungen erfolgen server-seitig:
- `canAccess()` wird vor dem Laden der Seite ausgeführt
- `mount()` validiert die Daten zusätzlich
- Bei Problemen erfolgt eine sichere Weiterleitung

## Vorteile der Implementierung

### 1. Benutzerfreundlichkeit
- Klarer, linearer Ablauf
- Keine verwirrenden URL-Parameter
- Automatische Umleitung bei ungültigen Zugriffen

### 2. Sicherheit
- Schutz vor direktem Zugriff
- Schutz vor wiederholtem Zugriff
- Keine Exposition sensibler IDs in URLs

### 3. Wartbarkeit
- Einfache Logik
- Klare Zuständigkeiten
- Leicht testbar

## Mögliche Erweiterungen

### 1. Zeitlimit
Session-Variable mit Ablaufzeit versehen:

```php
session([
    'inquiry_complete' => [
        'id' => $inquiry->id,
        'expires_at' => now()->addMinutes(5)
    ]
]);
```

### 2. Token-basierter Zugriff
Zusätzliche Sicherheitsebene mit eindeutigem Token:

```php
$token = Str::random(32);
session(['inquiry_complete' => [
    'id' => $inquiry->id,
    'token' => $token
]]);
```

### 3. Mehrfacher Zugriff erlauben
Falls gewünscht, Session-Variable NICHT löschen:

```php
// In mount() - NICHT session()->forget() aufrufen
// Stattdessen Counter oder Timestamp verwenden
session([
    'inquiry_complete' => [
        'id' => $inquiry->id,
        'views' => session('inquiry_complete.views', 0) + 1,
        'last_viewed' => now()
    ]
]);
```

## Fehlerbehandlung

### Scenario 1: Session-Variable fehlt
```php
// In canAccess()
if (!session()->has('inquiry_complete')) {
    // Filament leitet automatisch um oder zeigt 403
    return false;
}
```

### Scenario 2: Anfrage nicht gefunden
```php
// In mount()
if (!$this->inquiry) {
    session()->forget('inquiry_complete');
    $this->redirect(InquiryPage::getUrl());
    return;
}
```

### Scenario 3: Seite neu laden
```php
// Session-Variable wurde bereits gelöscht
// canAccess() gibt false zurück
// Automatische Umleitung zur InquiryPage
```

## Testing

### Unit Tests
```php
public function test_inquiry_complete_page_requires_session()
{
    $response = $this->get(route('filament.app.pages.inquiry-complete-page'));
    $response->assertRedirect(route('filament.app.pages.inquiry-page'));
}

public function test_inquiry_complete_page_with_valid_session()
{
    $inquiry = Inquiry::factory()->create();
    session(['inquiry_complete' => $inquiry->id]);
    
    $response = $this->get(route('filament.app.pages.inquiry-complete-page'));
    $response->assertOk();
    
    // Session should be cleared after first visit
    $this->assertFalse(session()->has('inquiry_complete'));
}

public function test_inquiry_complete_page_clears_session_after_view()
{
    $inquiry = Inquiry::factory()->create();
    session(['inquiry_complete' => $inquiry->id]);
    
    // First visit
    $this->get(route('filament.app.pages.inquiry-complete-page'));
    
    // Second visit should redirect
    $response = $this->get(route('filament.app.pages.inquiry-complete-page'));
    $response->assertRedirect(route('filament.app.pages.inquiry-page'));
}
```

### Manuelle Tests
1. ✅ Normale Anfrage erstellen → Bestätigungsseite wird angezeigt
2. ✅ Seite neu laden → Weiterleitung zur InquiryPage
3. ✅ Direkter Zugriff via URL → Weiterleitung zur InquiryPage
4. ✅ Session manuell löschen → Weiterleitung zur InquiryPage
5. ✅ Ungültige Anfrage-ID in Session → Weiterleitung zur InquiryPage

## Zusammenfassung

Die session-basierte Zugriffskontrolle bietet eine sichere und benutzerfreundliche Lösung für die InquiryCompletePage:

- ✅ Einmaliger Zugriff
- ✅ Keine URL-Parameter
- ✅ Server-seitige Validierung
- ✅ Automatische Bereinigung
- ✅ Klare Fehlerbehandlung
- ✅ Einfache Wartung

Die Implementierung folgt Best Practices für Web-Anwendungen und bietet eine solide Grundlage für zukünftige Erweiterungen.
