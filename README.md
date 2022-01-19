![Alt text](docs/logo.svg?raw=true "logo")


# **Contao recruitee Bundle**
Mit dem Contao Recruitee Bundle der BROCKHAUS AG legst du über die recruitee API einen neuen
Bewerbungskandidaten an. Außerdem können alle aktuell veröffentlichten Jobs von recruitee auf
deiner Webseite geladen werden.


## **Wofür ist das Bundle?**
Das Contao Recruitee Bundle ermöglicht es, ein Bewerbungsformular an recruitee zu senden und
aktuell veröffentlichte Jobs auf deiner Webseite zu laden.</br>
Du möchten wissen, wie das geht? Unter dem Punkt "[Wie funktionier das?](#wie-funktioniert-das)"
haben wir alles Wissenswerte für dich zusammengefasst.


## **Wie kann ich das Bundle installieren und konfigurieren?**
1. Lade dir das Bundle mit dem Contao Manager in deiner Contao Umgebung oder per composer herunter.
2. Erstelle unter "contao/" einen Ordner mit dem Namen "settings".
3. In diesem Ordner erstellst du einen weiteren Ordner mit dem Namen "brockhaus-ag".
4. Anschließend erstellst du in dem Ordner "html/contao/brockhaus-ag" einen weiteren Ordner
   mit dem Namen "contao-recruitee-bundle".
5. In dem Ordner "settings" unseres Bundles findest du eine "example"-Datei. **Wichtig: Die Datei
   muss die Endung ".json" und nicht ".jsonc" aufweisen!**
6. In der Datei "config.json" gibst du die Standorte an, welche du bereits in recruitee angegeben hast.
   1. In dem Feld "name", gibst du den Namen deines Standortes an.
   2. Unter dem Punkt "bearerToken", gibst du den Token aus recruitee an, damit sich das Bundle über
      die API von recruitee einloggen kann.
   3. Der Punkt "companyIdentifier" kommt ebenfalls aus recruitee. Diese Company Id wird zur
      Identifizierung des Standortes benötigt.
   4. Unter dem Punkt "category" gibst du deinen Form Alias an, damit das Bundle das Formular erkennt.
7. Wenn du die Datei angepasst hast, kannst du sie in dem Ordner
   "html/contao/settings/brockhaus-ag/contao-recruitee-bundle/" ablegen.
8. Sobald alle Schritte abgeschlossen sind, kannst du mit dem nächsten Schritt "[Ich bin fertig, wie kann
   ich das Bundle nutzen?](#ich-bin-fertig-wie-kann-ich-das-bundle-nutzen)" weiter machen.


## **Ich bin fertig, wie kann ich das Bundle nutzen?**
Das Bundle hat unterschiedliche Funktionen:
1. "/recruitee/reload-jobs"
   1. Diese Funktion dient dazu, alle Jobs, die über recruitee veröffentlicht wurden, neu zu laden.
   2. Du hast dir bestimmt schon die Frage gestellt, ob du nun immer, wenn du einen neuen Job
      veröffentlichst, die Jobs aktualisieren musst. Natürlich nicht. Schau einfach unter dem Punkt
      "[Wie können meine Jobs automatisch aktualisiert werden?](#wie-knnen-meine-jobs-automatisch-aktualisiert-werden)"
      nach.
2. "/recruitee/load-jobs"
   1. Das ist dein Bereich. Wir haben eine weitere Seite entwickelt, damit du die zurzeit geladenen
      Jobs formatiert ansehen kannst. Außerdem sind das Datum und die Uhrzeit aufgeführt, damit du
      sehen kannst, wann deine Jobs zuletzt geladen wurden.
3. "/recruitee/load-json-jobs"
   1. Im Gegensatz zu Punkt zwei, handelt es sich um eine JSON formatierte Ausgabe, welche dazu
      dient, deine veröffentlichten Jobs auf der Webseite anzuzeigen.


## **Wie können meine Jobs automatisch aktualisiert werden?**
Deine Jobs können ganz einfach über einen Cron Job bei deinem Server Anbieter aktualisiert werden.
Rufe dazu im Cron Job die URL "https://{ihre-url}.{tld}/recruitee/reload-jobs" auf. </br>
</br>
~ Demnächst soll es auch möglich sein Cron Jobs direkt über das Contao Backend zu erstellen.


## **Wie funktioniert das?**
Jetzt steht nur noch eine Frage im Raum: Wie funktioniert das?

### **Neuen Bewerbungskandidaten anlegen:**
Um einen neuen Bewerbungskandidaten anzulegen, benötigst du zunächst ein Formular auf deiner Webseite,
das den Form Alias "bewerbung-" aufweist. Dieses Formular muss die Formularfelder "Anrede",
"Vorname", "Nachname", "E-Mail" und "Nachricht" enthalten. Alle anderen Optionen sind sekundär. Du
findest genauere Informationen zum Thema "Formular" unter dem Punkt "[Backend-API -> Bewerbungskandidaten
senden](#bewerbungskandidaten-senden)". </br>
Wenn eine Bewerbung über dein Formular versandt wird, empfängt ein Contao-Hook die Bewerbung. Diese
Bewerbungsdaten werden dann automatisch aufgeteilt und an die API von recruitee weitergeleitet.

### **Veröffentlichte Jobs laden:**
Wenn du Jobs bereits über recruitee veröffentlicht hast, werden diese über eine API-Anfrage geladen.
Diese Jobs werden dann mit einem Zeitstempel (der letzten Aktualisierung) in einer JSON Datei gespeichert
werden. Wenn du jetzt beispielsweise die Seite "https://{ihre-url}.{tld}/recruitee/load-jobs" aufrufst,
wird die JSON Datei geladen und die Jobs in deinem Browser ausgegeben. </br>
Wie du auf deiner Webseite die Jobs anzeigen lassen kannst bzw. wie die API ausgespielt wird, findest du
unter dem Punkt "[Backend-API -> Jobs laden](#jobs-laden)".


## **Backend-API**
#### **Bewerbungskandidaten senden:**
```json5
{
    // Formular Alias: bewerbung-experts
    "alias": "",
    // die Job Id, auf den sich der Bewerber bewirbt
    "jobID": "",
    // Anrede: Herr, Frau, Divers
    "bw_anrede": "",
    // Titel: Dr., Dipl., etc. 
    "bw_titel": "",
    // Vorname: Max
    "bw_vorname": "",
    // Nachname: Mustermann
    "bw_name": "",
    // E-Mail: max.mustermann@brockhaus-ag.de
    "bw_email": "",
    // 
    "profil_sonstiges": "",
    // https://github.com/BROCKHAUS-AG
    "github": "",
    // https://de.linkedin.com/company/brockhaus-ag
    "linkedin": "",
    // https://www.xing.com/pages/brockhausag
    "xing": "",
    //
    "bw_quelle": "",
    // Bewerbungsanschreiben
    "anschreiben": [

    ],
    // Lebenslauf
    "lebenslauf": [

    ],
    // Zeugnisse
    "zeugnisse": [

    ],
    // Foto von Bewerber
    "foto": [

    ]
}
```
#### **Jobs laden:**
```json5
[
   {
      // Stellenname des Jobs
      "stellenname": "",
      // Id aus recruitee
      "id": "",
      // Einsatzort (Stadt)
      "einsatzort": "",
      // Job Url zu recruitee
      "_url": "",
      // Kategorie (Alias für unterschiedliche Seiten)
      "kategorie": "",
      // Bild Alias
      "bildUrl": "",
      // Stellenbeschreibung mit HTML
      "stellenbeschreibung": "",
      "tags": [],
   },
   {
      "stellenname": "",
      "id": "",
      "einsatzort": "",
      "_url": "",
      "kategorie": "",
      "bildUrl": "",
      "stellenbeschreibung": "",
      "tags": [],
   }
]
```