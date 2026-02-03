# RentalResource - Actions & Features Dokumentation

## Übersicht

Die RentalResource wurde um umfangreiche Actions und Features erweitert, um den Content und Zugangscode direkt aus dem Filament Admin-Panel zu verwalten.

---

## 🎯 Neue Actions

### 1. **Als bezahlt markieren** (`markAsPaid`)
**Icon:** ✅ Check Circle (Grün)  
**Verfügbar:** Wenn Rental noch nicht bezahlt ist

**Funktionalität:**
- Markiert Rental als bezahlt (Status: `paid`, `paid_at` wird gesetzt)
- Erstellt automatisch RentalContent mit Zugangscode
- Optional: Versendet Zugangscode per E-Mail an Kunden
- Zeigt Zugangscode in Erfolgs-Notification

**Modal-Felder:**
- `paid_at` (DateTime) - Bezahlt am (default: jetzt)
- `send_email` (Checkbox) - Zugangscode per E-Mail senden (default: ja)

**Verwendung:**
```php
// In Tabelle als recordAction verfügbar
// In ViewRental als headerAction verfügbar
```

---

### 2. **Zugangscode anzeigen** (`viewAccessCode`)
**Icon:** 🔑 Key (Blau)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Zeigt Zugangscode in schönem Modal
- Kopier-Button für Code
- Kopier-Button für URL
- Direkt-Link zum Kundenportal
- Zeigt Status (Veröffentlicht/Entwurf)
- Zeigt letzten Zugriff

**Modal-View:**
- `filament/modals/access-code.blade.php`
- Gradient-Design
- Copy-to-Clipboard JavaScript

---

### 3. **Zugangscode neu generieren** (`regenerateAccessCode`)
**Icon:** 🔄 Arrow Path (Orange/Warning)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Generiert neuen, eindeutigen Zugangscode
- Alter Code wird ungültig
- Zeigt alten und neuen Code in Notification
- Requires Confirmation (Warnung)

**Achtung:**
- Kunde kann nicht mehr mit altem Code zugreifen
- Neuen Code manuell an Kunden senden

---

### 4. **Content verwalten** (`manageContent`)
**Icon:** 📄 Document Text (Blau)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Vollständiger Content-Editor im Modal
- Alle Felder bearbeitbar
- Logo-Upload (wenn Firma)
- Veröffentlichen Toggle
- Auto-Fill mit existierenden Daten

**Formular-Sections:**

#### Grundinformationen
- Titel (max 255 Zeichen)
- Beschreibung (max 2000 Zeichen, Textarea)

#### Kontaktinformationen
- Website URL (mit Validierung)
- Kontakt E-Mail (mit Validierung)
- Telefonnummer

#### Logo & Veröffentlichung
- Firmenlogo Upload (nur für Firmen)
  - Max 2MB
  - Formate: JPG, PNG, GIF, SVG
  - Gespeichert in `storage/app/public/rental-logos/`
- Veröffentlichen Toggle

---

### 5. **Zugangscode erneut senden** (`resendAccessCode`)
**Icon:** ✉️ Paper Airplane (Blau)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Versendet Zugangscode erneut per E-Mail
- Verwendet bestehendes Email-Template
- Requires Confirmation
- Error-Handling mit Log-Eintrag

---

### 6. **Content löschen** (`deleteContent`)
**Icon:** 🗑️ Trash (Rot)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Löscht gesamten Content
- Löscht Zugangscode (ungültig)
- Löscht Logo von Storage
- Requires Confirmation (Warnung)

**Achtung:**
- Kann nicht rückgängig gemacht werden
- Kunde verliert Zugriff
- Logo wird permanent gelöscht

---

### 7. **Content initialisieren** (`initializeContent`)
**Icon:** ➕ Plus Circle (Grün)  
**Verfügbar:** Wenn kein Content und Rental bezahlt

**Funktionalität:**
- Erstellt RentalContent für bezahlte Rental
- Generiert automatisch Zugangscode
- Setzt `is_private_person` basierend auf Customer
- Zeigt Zugangscode in Notification

**Verwendung:**
Für den Fall, dass Content manuell erstellt werden soll ohne Email zu versenden.

---

### 8. **Kundenportal öffnen** (`openCustomerPortal`)
**Icon:** 🔗 Arrow Top Right (Grau)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Öffnet Kundenportal in neuem Tab
- Direkter Link zur Verwaltungsseite
- Mit Zugangscode in URL

**URL-Format:**
```
/rental-content/manage/{access_code}
```

---

### 9. **Content-Status anzeigen** (`viewContentStatus`)
**Icon:** ℹ️ Information Circle (Blau)  
**Verfügbar:** Wenn Content vorhanden

**Funktionalität:**
- Zeigt umfassenden Content-Status
- Veröffentlichungs-Status
- Kontoart (Privat/Firma)
- Content-Vollständigkeit (Progress-Bar)
- Zugriffs-Informationen
- Miet-Informationen

**Modal-View:**
- `filament/modals/content-status.blade.php`
- Mehrere Cards mit Info-Blöcken
- Visueller Progress-Indicator
- Farbcodierte Status-Badges

**Angezeigte Daten:**
- Status (Veröffentlicht/Entwurf)
- Kontoart (Privatperson/Firma)
- Ausgefüllte Felder (x/y)
- Progress-Bar (Prozent)
- Zugangscode
- Letzter Zugriff
- Erstellungs-/Update-Datum
- Kunden-Info
- Anzahl Felder

---

## 📊 Neue Tabellen-Spalten

### In RentalsTable hinzugefügt:

1. **Bezahlt am** (`paid_at`)
   - DateTime-Spalte
   - Sortierbar
   - Placeholder: "Nicht bezahlt"
   - Standard: ausgeblendet (toggleable)

2. **Zugangscode** (`content.access_code`)
   - Monospace-Font
   - Kopierbar mit Message
   - Placeholder: "—"
   - Toggleable (standard: sichtbar)

3. **Veröffentlicht** (`content.is_published`)
   - Badge (Grün: Ja, Grau: Entwurf)
   - Placeholder: "Kein Content"
   - Toggleable

### Status-Spalte erweitert:
- ⚠️ `pending` (Orange) - Ausstehend
- 🔵 `paid` (Blau) - Bezahlt
- ✅ `active` (Grün) - Aktiv
- ⚫ `completed` (Grau) - Abgeschlossen
- 🔴 `cancelled` (Rot) - Storniert

---

## 🔍 Neue Filter

### Status-Filter erweitert:
- Ausstehend (`pending`)
- Bezahlt (`paid`)
- Aktiv (`active`)
- Abgeschlossen (`completed`)
- Storniert (`cancelled`)

### Content-Status Filter (NEU):
- **Mit Content** - Nur Rentals mit Content
- **Ohne Content** - Nur Rentals ohne Content
- **Veröffentlicht** - Nur veröffentlichter Content
- **Entwurf** - Nur Entwurf-Content

**Implementierung:**
```php
SelectFilter::make('has_content')
    ->options([...])
    ->query(function ($query, array $data) {
        // Verwendet whereHas/doesntHave
    })
```

---

## 📋 ViewRental Page - Header Actions

**Alle Actions verfügbar als Header-Buttons:**

1. ✏️ Edit (Standard)
2. ✅ Als bezahlt markieren
3. ➕ Content initialisieren
4. 🔑 Zugangscode anzeigen
5. 📄 Content verwalten
6. ℹ️ Content-Status anzeigen
7. 🔗 Kundenportal öffnen
8. ✉️ Zugangscode erneut senden
9. 🔄 Zugangscode neu generieren
10. 🗑️ Content löschen

**Automatische Sichtbarkeit:**
- Actions werden nur angezeigt wenn relevant
- `visible(fn (Rental $record) => ...)`
- Basierend auf Content-Existenz und Status

---

## 📝 Erweiterte Infolist

### Neue Section: "Customer Portal Content"

**Felder:**
1. **Access Code**
   - Monospace, Bold, Large
   - Kopierbar
   - Mit Copy-Message

2. **Published Status**
   - Badge (Grün/Grau)

3. **Title**
   - Content-Titel

4. **Website**
   - Als klickbarer Link
   - Öffnet in neuem Tab

5. **Contact Email**
   - Kopierbar

6. **Contact Phone**
   - Kopierbar

7. **Last Accessed**
   - DateTime mit "since" (z.B. "vor 2 Stunden")

8. **Logo**
   - Badge: "Uploaded" oder "No logo"
   - Grün/Grau

**Features:**
- 4-Spalten-Grid
- Nur sichtbar wenn Content existiert
- Collapsible Section
- `paid_at` zu Additional Information hinzugefügt

---

## 🎨 Modal-Views

### 1. `access-code.blade.php`

**Design:**
- Gradient-Hintergrund (Blau → Lila)
- Großer Code-Display (2xl, Monospace)
- Kopier-Buttons für Code & URL
- Status-Badge (Veröffentlicht/Entwurf)
- Info-Icons mit SVG
- Letzter Zugriff oder "Noch nicht zugegriffen"

**JavaScript:**
```javascript
function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
    // Alert mit Erfolgs-Message
}
```

---

### 2. `content-status.blade.php`

**Sections:**

#### Status Overview (2-Spalten Grid)
1. **Veröffentlicht-Status**
   - Icon: Eye (grün) oder Eye-Off (grau)
   - Farbe abhängig von Status

2. **Kontoart**
   - Icon: User (Privat) oder Building (Firma)
   - Blauer Hintergrund

#### Content-Vollständigkeit
- Gradient-Box (Lila → Pink)
- Progress-Bar animiert
- Liste aller Felder mit ✓ oder —
- Berechnung: `(filled / total) * 100`
- Dynamisch basierend auf `is_private_person`

#### Zugriffs-Informationen
- Zugangscode (Monospace)
- Letzter Zugriff (mit "Vor X")
- Erstellt am
- Aktualisiert am (wenn unterschiedlich)

#### Mietinformationen
- Kundenname
- Firma (wenn vorhanden)
- Anzahl Felder
- Miet-Status (Badge)

**Design:**
- Cards mit Borders
- Farb-kodierte Status
- Grid-Layouts
- Responsive
- Icons per SVG

---

## 🔧 Technische Details

### Actions-Klasse

**Datei:** `app/Filament/Admin/Resources/Rentals/Actions/RentalActions.php`

**Struktur:**
```php
class RentalActions {
    public static function markAsPaid(): Action { ... }
    public static function viewAccessCode(): Action { ... }
    public static function regenerateAccessCode(): Action { ... }
    // ... etc
}
```

**Verwendung:**
```php
use App\Filament\Admin\Resources\Rentals\Actions\RentalActions;

// In Table
->recordActions([
    RentalActions::markAsPaid(),
    RentalActions::viewAccessCode(),
])

// In Page
protected function getHeaderActions(): array {
    return [
        RentalActions::markAsPaid(),
        // ...
    ];
}
```

---

## 🎯 Workflow-Beispiele

### Neue Rental → Content → Email

1. **Admin erstellt Rental** (Status: `pending`)
2. **Zahlung geht ein**
3. **Admin klickt "Als bezahlt markieren"**
   - Wählt Datum
   - Checkbox "E-Mail senden" aktiviert
   - Bestätigt
4. **System:**
   - Setzt `paid_at` und Status auf `paid`
   - Erstellt `RentalContent` mit Code
   - Versendet Email via Event
5. **Admin sieht Notification** mit Code
6. **Kunde erhält Email** mit Code und Link

---

### Content manuell bearbeiten

1. **Admin öffnet Rental**
2. **Klickt "Content verwalten"**
3. **Modal öffnet sich** mit allen Feldern
4. **Admin bearbeitet:**
   - Titel
   - Beschreibung
   - Kontakte
   - Lädt Logo hoch (wenn Firma)
   - Aktiviert "Veröffentlichen"
5. **Speichert**
6. **Success-Notification**

---

### Code neu generieren

1. **Admin klickt "Zugangscode neu generieren"**
2. **Warnung erscheint** (alter Code ungültig)
3. **Bestätigt**
4. **Notification zeigt:**
   - Alter Code: ABCD-1234-EFGH
   - Neuer Code: WXYZ-5678-IJKL
5. **Admin muss** neuen Code an Kunden senden

---

## 📱 Features im Überblick

### Admin kann:
✅ Rentals als bezahlt markieren  
✅ Zugangscodes anzeigen & kopieren  
✅ Content direkt bearbeiten  
✅ Logo hochladen/verwalten  
✅ Content veröffentlichen/zurückziehen  
✅ Zugangscodes neu generieren  
✅ Emails erneut versenden  
✅ Content-Status überwachen  
✅ Content-Vollständigkeit sehen  
✅ Kundenportal direkt öffnen  
✅ Content löschen  
✅ Nach Content-Status filtern  

### Automatisch:
✅ Zugangscode-Generierung  
✅ Email-Versand nach Bezahlung  
✅ Content-Initialisierung  
✅ Logo-Storage-Management  

---

## 🎨 UI/UX Highlights

- ✅ Farbkodierte Status-Badges
- ✅ Konditionelle Action-Sichtbarkeit
- ✅ Kopier-Funktionen für Codes & URLs
- ✅ Moderne Modal-Designs
- ✅ Progress-Bars für Vollständigkeit
- ✅ Responsive Layouts
- ✅ Icons für alle Actions
- ✅ Tooltips & Helper-Texte
- ✅ Confirmation-Dialoge für kritische Actions
- ✅ Persistent Notifications für wichtige Info

---

## 🔒 Sicherheit

- ✅ Confirmation für kritische Actions
- ✅ Validierung in Forms
- ✅ File-Upload-Beschränkungen
- ✅ Error-Handling mit Logs
- ✅ Try-Catch für Email-Versand

---

## 📚 Weitere Infos

**Siehe auch:**
- `docs/RENTAL_CONTENT_MANAGEMENT.md` - System-Übersicht
- `docs/CUSTOMER_PORTAL_VIEWS.md` - Kundenportal
- `QUICK_START_CUSTOMER_PORTAL.md` - Quick Start

**Dateien:**
- Actions: `app/Filament/Admin/Resources/Rentals/Actions/RentalActions.php`
- Table: `app/Filament/Admin/Resources/Rentals/Tables/RentalsTable.php`
- Infolist: `app/Filament/Admin/Resources/Rentals/Schemas/RentalInfolist.php`
- ViewPage: `app/Filament/Admin/Resources/Rentals/Pages/ViewRental.php`
- Modals: `resources/views/filament/modals/*.blade.php`
