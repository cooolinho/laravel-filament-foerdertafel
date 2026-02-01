# Fördertafel Laravel Application

## Idee des Projekts
Ich möchte mit diesem projekt ein digitales Board verwalten. Stell dir eine Art Schachbrettmuster vor. 
Dieses Board hat mehrere Felder (Field), die von Personen/Firmen (Customer) gemietet werden können. 
Jede Person/Firma kann eines oder mehrere aneinanderhängende Felder auf dem Board mieten.
Jedes Feld hat eine bestimmte Größe (z.B. 2x2, 3x1, etc.) und einen Preis pro Monat
Da es sich bei diesem Projekt um ein Fussball Projekt handelt, wird das Board wie ein Fußballfeld aussehen und Felder wie bspw. "Tor", "Strafraum", "Mittelkreis" sind preislich teuerer als andere Felder.

Erstelle mir eine erste Version indem du Models und Migrationen für die folgenden Entitäten anpasst (Dateien sind bereits im Projekt vorhanden):
- Location: Repräsentiert den Standort des Boards mit Attributen wie Name, Adresse und Beschreibung
- Board: Repräsentiert das gesamte Board mit einer bestimmten Anzahl von Reihen und Spalten
- Field: Repräsentiert ein einzelnes Feld auf dem Board mit Attributen wie Position (Reihe, Spalte), Größe (Breite, Höhe), Preis pro Monat und Status (verfügbar, vermietet)
- Customer: Repräsentiert eine Person/Firma, die Felder auf dem Board mieten kann, mit Attributen wie Name, Kontaktinformationen und Zahlungsmethode
- Rental: Repräsentiert die Vermietung eines oder mehrerer Felder an einen Kunden für einen bestimmten Zeitraum mit Attributen wie Startdatum, Enddatum und Gesamtpreis

Die Filament Dateien erstelle ich manuell nachdem die Models und Migrationen erstellt wurden.

Die Personen/Firmen können ihre gemieteten Felder mit Inhalten füllen, wie z.B. Text, Bilder oder Videos. 
Das System soll auch die Möglichkeit bieten, die Belegung der Felder zu verwalten, Zahlungen zu verfolgen und Berichte über die Nutzung des Boards zu erstellen. 
Bitte hilf mir, dieses Projekt zu planen und umzusetzen.

php artisan filament:resource --generate --record-title-attribute=name --view Board
php artisan filament:resource --generate --record-title-attribute=name --view Customer
php artisan filament:resource --generate --record-title-attribute=name --view Field
php artisan filament:resource --generate --record-title-attribute=name --view Location
php artisan filament:resource --generate --record-title-attribute=name --view Rental

Passe mir nun die Resource an. Erweitere die Form, Table und Infolist um die wichtigsten Attribute der Entitäten. nutzen die Konstanten aus den Models.

Erstelle mir nun für alle Models eine Seeder Datei, die jeweils mehrere Datensätze generiert. Lasse dir sinnvolle Demodaten einfallen, die ich dann als Testumgebung nutzen kann. Strukturiere es so, dass in DatabaseSeeder nur die Seeder aufgerufen werden müssen.
Alle anderen Seeder können seperat aufgerufen werden.

überarbeite die Resource Dateien noch mit einer sinnvollen Navigation und Gruppierung. Füge ein auch ein passendes Icon hinzu.

