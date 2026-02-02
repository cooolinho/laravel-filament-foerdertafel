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

passe mir mein FieldsWidget und das template an. Ich möchte das alle Felder eines Boards in einer übersichtlichen Ansicht dargestellt werden. 
Jedes Feld soll seinen Status (verfügbar, vermietet) anzeigen und bei Klick auf ein Feld sollen die Details des Feldes angezeigt werden, 
inklusive der Möglichkeit, das Feld zu bearbeiten oder eine neue Vermietung zu starten.
Die Anordnung ist so, dass die Felder in einem Raster dargestellt werden, das der Anzahl der Reihen und Spalten des Boards entspricht.

passe das widget noch folgender maßen an. 
ich möchte an den stellen im raster wo noch kein feld hinterlegt eine möglichkeit besteht, ein neues feld zu erstellen.
Klickt man auf diese stelle, öffnet sich ein modal in dem die wichtigsten attribute des feldes eingegeben werden können (position, größe, preis pro monat, status).

es gibt eine besonderheit beim raster. ein feld kann größer als 1x1 sein.
Das bedeutet, dass ein feld mehrere positionen im raster einnehmen kann.
Passe das widget so an, dass diese größen berücksichtigt werden und die felder entsprechend im raster dargestellt werden.
passe auch den seeder (FieldSeeder) an sodass keine Felder mit überlappenden positionen erstellt werden.

nochmal zum verständins. wenn ein field (bspw. row=1 und col=1) im raster eine width von 2 hat, dann ist auch automatisch das feld an position (row=1, col=2) belegt. im widget wird das field dann über 2 rows dargestellt und ersetzt die position (row=1, col=2). es soll also kein leeres feld an dieser position geben.
das gleich gilt für height. wenn ein field eine height von 2 hat, dann ist auch automatisch die position (row=2, col=1) belegt.

Ein Board soll ein Hintergrundbild zugeordnet werden können indem man eines hochlädt.
Das Hintergrundbild wird im FieldsWidget als Hintergrund des Rasters angezeigt. 
Gehen wird davon aus ich lade eine Fussball Stadion als Lufaufnahme hoch. Das Fussball Feld (welches als Raster hintergrund dient) ist weiter mittig im Bild. Die Felder sollen also passend zum Hintergrundbild positioniert werden können. Das ist nötig wenn die um das Fussball Feld noch eine Art Rahmen ist. Dann würde bspw. Das Feld (1, 1) nicht an der oberen linken eckfahne sein sondern irgendwo in den zuschauerrängen und das soll ja nicht.
Ich muss also pro hintergrundbild am Board auch ein versatz für das raster einstellen können (x und y achse). Passe mir das Board Model, die Migration und die Resource entsprechend an.

Ich möchte das Hintergrundbild nicht im Admin Bereich anzeigen lassen, da das Widget FieldsWidget zu viele Informationen hat und somit das Bild nicht passend dargestellt werden kann.
Stattdessen möchte ich das Hintergrundbild im Frontend anzeigen lassen, wenn ich mir das Board ansehe. Ich habe eine BoardPage erstellt. Hier soll ein Board mit dessen Feldern angezeigt werden. Fast genau die gleiche darstellung wie im FieldsWidget, nur das es hier um die Ansicht für Kunden geht.
Die Kunden sollen das Raster mit den Feldern sehen. Wenn eine Kachel frei ist, soll diese als verfügbar angezeigt werden. Wenn ein Feld vermietet ist, soll der Name des Kunden der zurzeit das Feld gemietet hat angezeigt werden. Hat der Kunde mehrere Felder nebeneinander gemietet, sollen diese auch zusammenhängend dargestellt werden.
Zur Info: Im späteren Verlauf (Beim Anfrageprozess der noch folgt) sollen die Kunden dann auch die Möglichkeit haben, Felder zu mieten und Inhalte hochzuladen. Kunden haben die Möglichkeit ein einzelndes oder mehrere Felder nebeneinander zu mieten.
Am Board muss grid_offset_x und grid_offset_y durch padding angepasst werden, damit das Raster passend zum Hintergrundbild positioniert ist. Das Hintergrundbild streckt (horizontal und vertikal) sich dann über das gesamte Board und innerhalb des Boards wird dann das Raster mit den padding Angaben positioniert.
Zusätzlich soll noch ein gap bestimmt werden können, also der abstand zwischen den feldern im raster.
Passe mir die BoardPage und alle zugehörigen Dateien entsprechend an.
