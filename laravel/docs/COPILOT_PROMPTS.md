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

ich will das kunden eine anfrage (InquiryPage) stellen können um felder zu mieten. Bei der Anfrage sollen die kunden angeben können welche felder (positionen im raster) sie mieten wollen, für welchen zeitraum (startdatum, enddatum) und ihre kontaktinformationen (name, email, telefon).
Aus dieser Anfrage wird dann später im Admin Bereich eine Vermietung (Rental) erstellt. Dies soll verhindern, dass kunden direkt im frontend felder mieten können ohne vorherige prüfung.
Die InquiryPage ist über das Board erreichbar. Dort wird das Board mit dem Raster der Felder angezeigt (wie in der BoardPage). Der Kunde kann dann die gewünschten Felder auswählen (auch mehrere nebeneinander) und im Anschluss das Formular mit den weiteren Angaben ausfüllen und absenden.
Passe mir das Inquiry Model, die Migration und die Resource im Admin Bereich entsprechend an.

Füge nun die zur InquiryPage ein Formular hinzu, in dem die Kunden ihre Kontaktinformationen (Name, E-Mail, Telefon), die gewünschten Felder (Positionen im Raster) und den gewünschten Zeitraum (Startdatum, Enddatum) angeben können.
Passe mir auch die Validierung an, sodass geprüft wird, dass mindestens ein Feld ausgewählt ist. Bei der Auswahl soll der Preis pro Monat der ausgewählten Felder angezeigt werden, sowie der Gesamtpreis für den gewählten Zeitraum.
Man gelangt über die BoardPage zur InquiryPage.

für jedes belegte feld in der InquiryPage soll der name des kunden angezeigt werden, der das feld aktuell gemietet hat. Die Felder die zur Auswahl stehen sollen also nicht mit grid-columns start und end arbeiten sondern dynamisch anhand der positionen der felder im Board gerendert werden.

beim absenden der Anfrage soll geprüft werden ob die ausgewählten Felder nebeneinander liegen. Falls nicht soll eine Fehlermeldung angezeigt werden.
Bei der auswahl der felder muss also immer ein rechteck gebildet werden können. wenn also die felder (1,1), (1,2) und (2,1) ausgewählt sind, dann fehlt das feld (2,2) um ein rechteck zu bilden und es soll eine fehlermeldung angezeigt werden.
Erstelle eine eigene Validierungsregel für diese Prüfung. Füge auch noch eine maximale anzahl an feldern hinzu die ausgewählt werden können (z.b. 10 felder). Auch hierfür eine eigene Validierungsregel.

passe mir meinen boardseeder und den fieldseeder an. ich will genau 1 board mit 17 rows und 16 columns haben. da es sich um ein fussball stadion handelt soll es vordefinierte felder geben die bestimmte bereiche des fussball feldes abdecken.
Es gib 4 ecken mit einem feld von 1x1 in jeder ecke.
Es gibt 2 Tore mit jeweils 5 feldern von 1x5. (startend bei (row=7, col=1) und (row=7, col=16))
Es gibt einen Mittelkreis mit 20 feldern von 4x5. (startend bei (row=7, col=7))

Nach dem der Kunde ein Anfrage abgesendet hat, soll dieser auf einer ConfirmationPage (InquiryCompletePage) weitergeleitet werden.
Auf dieser Seite soll eine Zusammenfassung der Anfrage angezeigt werden, inklusive der ausgewählten Felder,des Zeitraums und der Kontaktinformationen.
Passe mir die InquiryCompletePage und alle zugehörigen Dateien entsprechend an.

Die InquiryCompletePage kann nur geöffnet werden wenn eine Session Variable (inquiry_complete) gesetzt ist.
Passe mir die InquiryPage entsprechend an, sodass nach dem Absenden der Anfrage die Session Variable gesetzt wird und der Nutzer auf die InquiryCompletePage weitergeleitet wird.
Auf der InquiryCompletePage wird die Session Variable wieder gelöscht, sodass die Seite nicht erneut aufgerufen werden kann ohne eine neue Anfrage zu stellen. mit canView muss geprüft werden ob die Session Variable gesetzt ist, ansonsten wird man auf die Anfrage Seite weitergeleitet.

wenn ein Inquiry erstellt wird, sollen die Felder auf reserviert gesetzt werden, sodass diese nicht mehr in der Board Ansicht als verfügbar angezeigt werden.
Erstelle mir dazu ein Listeners und Subscriber System, welches auf das Erstellen eines Inquiry hört und die entsprechenden Felder auf reserviert setzt.

bei klick auf ein vermietetes feld im FieldsWidget oder in der BoardPage, soll sich ein modal öffnen, welches die details der vermietung anzeigt.
Dort sollen die wichtigsten Attribute der Vermietung angezeigt werden (Kunde, Startdatum, Enddatum, Gesamtpreis).
Passe mir das FieldsWidget und die BoardPage entsprechend an.

ich möchte in meinem projekt emails versenden und empfangen können. Ich habe dazu bereits die Models Email und EmailTemplate erstellt.
Füge alles was man für einen Email-Verkehr braucht hinzu. also passe die Migrations, Models, Resources, Seeder und alles was sonst noch nötig ist an.
anschließend passe mir die dazugehörigen filament resourcen an. erweitere die form, table und infolist.
lasse dir auch eine sinnvolle navigation einfallen und füge icons und badges hinzu.
ich will einen posteingang und einen postausgang als page haben.

ich habe ein settings model und eine migration und eine filament page erstellt.
ich möchte dort einstellungen für das gesamte projekt vornehmen können.
Passe mir die SettingsPage so an, dass ich dort die folgenden einstellungen vornehmen kann:
- Standard Zahlungsmethode für Kunden (z.B. Kreditkarte, PayPal, Überweisung)
- Standard Mietdauer (z.B. 1 Monat, 3 Monate, 6 Monate)
- Maximale Anzahl an Feldern, die ein Kunde mieten kann
- E-Mail Benachrichtigungen aktivieren/deaktivieren
- Standard Vorlage für E-Mails (Verknüpfung zu EmailTemplate)
Die default settings sollten über die migration gesetzt werden, sodass bei der Installation des Projekts bereits sinnvolle Standardwerte vorhanden sind.

Event System
Ich möchte ein Event System in meinem Laravel Projekt implementieren, um auf bestimmte Aktionen zu reagieren und entsprechende Prozesse auszulösen.
Erstelle mir dazu die folgenden Events, Listener und Subscriber:
- Event: RentalCreated
  - Beschreibung: Wird ausgelöst, wenn eine neue Vermietung (Rental) erstellt wird.
  - Listener: SendRentalConfirmationEmail
    - Beschreibung: Sendet eine Bestätigungs-E-Mail an den Kunden mit den Details der Vermietung.
- Event: RentalEnded
  - Beschreibung: Wird ausgelöst, wenn eine Vermietung endet.
  - Listener: SetFieldsToAvailable
    - Beschreibung: Setzt die Felder der beendeten Vermietung auf den Status "verfügbar".
      Passe mir die entsprechenden Models, Migrations, Resources und alle zugehörigen Dateien an, um dieses Event System zu integrieren.
      Stelle sicher, dass die Events korrekt ausgelöst werden und die Listener die gewünschten Aktionen ausführen.

ich möchte den kunden ein möglichkeit geben, ihre gemieteten felder mit inhalten zu füllen.
Erstelle mir dazu ein Content Model und eine Migration mit passen inhalten
Der zugang sollte immer einen einmaligen zugangscode haben, der bei der Erstellung generiert wird.
Soabald der Kunde sein Feld bezahlt hat, soll ihm der zugangscode per email zugeschickt werden.
Mit dem Zugangscode kann der Kunde auf eine Seite zugreifen und die dazugehörigen Felder sehen und verwalten.
Er kann ein Firmenlogo hochladen wenn er nicht als privatperson gekennzeichnet ist.
Die verwaltetetn Inhalte sind an die Rental gebunden.

ich möchte in meinem projekt Dokumente verwalten können. Ich habe dazu schon ein Document Model und eine Migration und die Filament Resource Dateien erstellt.
Die Dokumente sollen an verschiedene Entitäten gebunden werden können (Location, Board, Field, Customer, Rental).
Eigtl brauche ich die Dokumente nur für die Kunden. Den Kunden sollen Mietverträge und Rechnungen als PDF Dokumente hochgeladen und zugeordnet werden können.
Wenn Kunden per Lastschrift zahlen sollen die SEPA Mandate auch als Dokumente hinterlegt werden können. Diese werden dann bspw. per Email an den Kunden geschickt. Da sich diese Dokumente eventuell ändern können (neue Version des Mandats) sollen die Dokumente versioniert werden.
Passe mir die Document Resource so an, dass ich die Dokumente entsprechend verwalten kann.
