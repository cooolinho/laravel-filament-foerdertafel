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

Passe mir nun die Resource an. Erweitere die Form, Table und Infolist um die wichtigsten Attribute der Entitäten. nutzen die Konstanten aus den Models.

Erstelle mir nun für alle Models eine Seeder Datei, die jeweils mehrere Datensätze generiert. Lasse dir sinnvolle Demodaten einfallen, die ich dann als Testumgebung nutzen kann. Strukturiere es so, dass in DatabaseSeeder nur die Seeder aufgerufen werden müssen.
Alle anderen Seeder können seperat aufgerufen werden.

überarbeite die Resource Dateien noch mit einer sinnvollen Navigation und Gruppierung. Füge ein auch ein passendes Icon hinzu.
