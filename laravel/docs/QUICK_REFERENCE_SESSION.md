# Quick Reference: InquiryCompletePage Session-Kontrolle

## 🔑 Kern-Konzept
Die InquiryCompletePage ist nur einmalig nach dem Absenden einer Anfrage zugänglich.

## 📋 Implementierte Dateien

### 1. InquiryPage.php
**Zeile ~209-210:**
```php
session(['inquiry_complete' => $inquiry->id]);
$this->redirect(route('filament.app.pages.inquiry-complete-page'));
```

### 2. InquiryCompletePage.php
**Zeile ~25-28:**
```php
public static function canAccess(): bool
{
    return session()->has('inquiry_complete');
}
```

**Zeile ~30-51:**
```php
public function mount(): void
{
    if (!session()->has('inquiry_complete')) {
        $this->redirect(InquiryPage::getUrl());
        return;
    }
    
    $inquiryId = session('inquiry_complete');
    $this->inquiry = Inquiry::with(['board', 'fields'])->find($inquiryId);
    
    if (!$this->inquiry) {
        session()->forget('inquiry_complete');
        $this->redirect(InquiryPage::getUrl());
        return;
    }
    
    session()->forget('inquiry_complete'); // Einmaliger Zugriff
}
```

## ✅ Test-Checkliste

- [ ] Anfrage erstellen → Bestätigungsseite wird angezeigt
- [ ] F5 drücken → Weiterleitung zur InquiryPage
- [ ] Direkter URL-Zugriff → Weiterleitung zur InquiryPage
- [ ] Zurück-Button → Funktioniert normal
- [ ] Neuen Tab öffnen mit URL → Weiterleitung zur InquiryPage

## 🎯 Wichtigste Änderungen

| Aspekt | Vorher | Nachher |
|--------|--------|---------|
| **URL** | `/inquiry-complete?inquiry=123` | `/inquiry-complete` |
| **Zugriffskontrolle** | URL-Parameter | Session-Variable |
| **Mehrfacher Zugriff** | Möglich | Nicht möglich |
| **Sicherheit** | ID in URL sichtbar | Keine ID sichtbar |

## 🔍 Debug-Hilfe

### Session prüfen (in Tinker):
```php
php artisan tinker
>>> session()->all()
>>> session()->has('inquiry_complete')
>>> session()->get('inquiry_complete')
```

### Session manuell setzen (zum Testen):
```php
>>> session(['inquiry_complete' => 1])
```

### Session löschen:
```php
>>> session()->forget('inquiry_complete')
```

## 📚 Dokumentation
Vollständige Dokumentation: `docs/INQUIRY_COMPLETE_PAGE_SESSION.md`
