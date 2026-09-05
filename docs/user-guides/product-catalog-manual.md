# Management- & Anwender-Handbuch: Produkt-, Varianten- und Kategoriemanagement

Dieses Dokument beschreibt die Funktionen, Arbeitsabläufe, Schutzmechanismen und den geschäftlichen Nutzen des Produktkatalog-Systems für das Management, Product Owner und operative Anwender (E-Commerce Manager, Category Manager).

---

## 1. Executive Summary & Geschäftsnutzen

### 🎯 Warum dieses System?
Klassische E-Commerce-Systeme leiden häufig unter unvollständigen Produktdaten, Tippfehlern bei Artikelnummern (SKUs) und fehlerhaften Variantenkombinationen (z. B. versehentlich doppelt angelegte T-Shirts in gleicher Größe und Farbe). Dies führt zu Frust bei Kunden, Lagerdifferenzen und teuren manuellen Korrekturen.

### 💼 Messbarer Geschäftswert
* **Automatisierter Daten-Qualitätsfilter**: Ein Produkt kann erst dann live geschaltet werden, wenn alle Mindestanforderungen (Kategorie, mindestens 1 Variante, gültiger Preis) vollständig erfüllt sind.
* **Fehlerprävention im Lager**: Keine doppelten Variantenkombinationen und strikte Formate für Artikelnummern (SKUs).
* **Massive Zeitersparnis**:
  * **Inline-Editing**: Preise und Status direkt in der Übersichtstabelle anpassen – ohne mühsames Öffnen der einzelnen Detailseiten.
  * **Bulk-Aktionen**: Hunderte Produkte mit zwei Klicks gesammelt aktivieren oder archivieren.
  * **Sofortige Suche & Filter**: Volltextsuche über Produktnamen, Basis-SKUs und Varianten-SKUs.

---

## 2. Funktionsübersicht

| Funktion | Nutzen für das Unternehmen | Wo im System zu finden? |
| :--- | :--- | :--- |
| **Produktliste mit Livewire** | Schnelle, verzögerungsfreie Ansicht aller Katalogartikel mit automatischer Paginierung. | Menü: *Katalog > Produkte* |
| **Status-Filter (Tabs)** | Sofortige Übersicht über Entwürfe, aktive Artikel und archivierte Altbestände. | Oberhalb der Produkttabelle (*Alle, Entwürfe, Aktiv, Archiviert*) |
| **Inline-Statusänderung** | Status direkt per Dropdown in der Tabellenzeile umschalten. | Spalte *Status* in der Produkttabelle |
| **Inline-Preisanpassung** | Basispreis direkt in der Tabellenspalte überschreiben. | Spalte *Basispreis (€)* |
| **Varianten-Matrix (Größe & Farbe)** | Beliebig viele Varianten mit individueller SKU, Größe, Farbe, Hex-Code und abweichendem Preis. | Tab *Varianten* im Produktformular |
| **Bilder & Medienverwaltung** | Hinterlegen von Bild-URLs, Alt-Texten und Festlegen des Hauptbilds. | Tab *Medien* im Produktformular |
| **Kategoriemanagement** | Strukturierte Ordnung des Sortiments mit automatischer SEO-Slug-Generierung. | Menü: *Katalog > Kategorien* |
| **Massenaktionen (Bulk Actions)** | Sammelweises Aktivieren, Archivieren oder Löschen ausgewählter Produkte. | Checkboxen links in der Produkttabelle |

---

## 3. Schritt-für-Schritt Bedienungsanleitung

### 3.1 Neues Produkt anlegen
1. Navigieren Sie im linken Hauptmenü auf **Katalog > Produkte**.
2. Klicken Sie oben rechts auf die Schaltfläche **`Produkt erstellen`**.
3. Es öffnet sich das strukturierte Eingabeformular mit **3 Reitern (Tabs)**:

```
┌─────────────────┬─────────────────┬─────────────────┐
│ 1. Stammdaten   │ 2. Varianten    │ 3. Medien       │
└─────────────────┴─────────────────┴─────────────────┘
```

#### Tab 1: Stammdaten
* **Produktname**: Vollständiger Marketingname (z. B. *"Classic Unisex T-Shirt"*).
* **Basis-SKU**: Eindeutige Kennung für die Hauptserie (z. B. `TSHIRT-CLASSIC`). 
  > ℹ️ *Erlaubt sind Buchstaben, Ziffern, Bindestriche, Punkte und Unterstriche. Keine Leerzeichen.*
* **Kategorie**: Wählen Sie die passende Warengruppe aus dem Dropdown.
* **Status**: Standardmäßig auf **Entwurf** (`draft`).
* **Basispreis (€)**: Standard-Verkaufspreis (z. B. `29,99`).

#### Tab 2: Varianten
1. Klicken Sie auf **`Variante hinzufügen`**.
2. Geben Sie die Details der Ausprägung ein:
   * **Varianten-SKU**: Eindeutige Artikelnummer für diese Variante (z. B. `TSHIRT-CLASSIC-S-BLK`).
   * **Größe**: Konfektionsgröße (z. B. `S`, `M`, `L`, `XL`, `42`).
   * **Farbe**: Farbname (z. B. `Schwarz`, `Navy`, `Rot`).
   * **Farbcode (Hex)**: Optionaler Hex-Farbcode (z. B. `#000000`) zur visuellen Darstellung im Shop.
   * **Abweichender Preis (€)**: Optional. Bleibt das Feld leer, gilt automatisch der Basispreis des Produkts.

#### Tab 3: Medien
1. Klicken Sie auf **`Bild hinzufügen`**.
2. Tragen Sie die **Bild-URL** und einen **Alt-Text** für Screenreader und Barrierefreiheit ein.
3. Aktivieren Sie den Schalter **Hauptbild** beim primären Titelbild.

4. Klicken Sie unten auf **`Erstellen`**. Das Produkt ist nun als Entwurf angelegt.

---

### 3.2 Produkt freigeben (Aktivierung)
Ein Produkt kann über **drei Wege** aktiviert werden:
* **Weg A (Ein-Klick-Aktion)**: In der Tabellenzeile auf die grüne Schaltfläche **`Aktivieren`** klicken.
* **Weg B (Inline-Dropdown)**: In der Tabellenspalte *Status* den Wert direkt auf **Aktiv** setzen.
* **Weg C (Bulk-Aktion)**: Mehrere Produkte per Checkbox markieren und oben links unter *Massenaktionen* auf **`Ausgewählte aktivieren`** klicken.

---

### 3.3 Produkt archivieren
Wenn ein Artikel aus dem Sortiment genommen wird:
* Klicken Sie in der Zeile auf **`Archivieren`** oder markieren Sie mehrere Zeilen und wählen Sie **`Ausgewählte archivieren`**.
* Das Produkt wechselt in den Tab **Archiviert** und ist für Endkunden im Shop nicht mehr kaufbar.

---

## 4. Geschäftsregeln & Schutzmechanismen (Business Invariants)

Das System verfügt über integrierte Sicherheitsprüfungen, die menschliche Fehler aktiv verhindern:

```
                  ┌─────────────────────────────────────┐
                  │    Aktivierung eines Produkts       │
                  └──────────────────┬──────────────────┘
                                     │
           Ist eine Kategorie zugewiesen?
           ├── NEIN ──► ❌ FEHLER: "Kategorie erforderlich"
           │
           Existiert mindestens 1 Variante?
           ├── NEIN ──► ❌ FEHLER: "Mindestens 1 Variante erforderlich"
           │
           Ist der Basispreis > 0,00 €?
           ├── NEIN ──► ❌ FEHLER: "Preis muss größer 0 sein"
           │
           └── JA ────► ✅ FREIGABE ERTEILT (Status = Aktiv)
```

1. **Aktivierungssperre**: Ein unfertiges Produkt kann nicht versehentlich im Shop landen.
2. **Duplikatschutz**: Es ist unmöglich, versehentlich zwei Varianten mit identischer Kombination aus *Größe* und *Farbe* anzulegen.
3. **Archivschutz**: Ein archiviertes Produkt kann nicht mehr unbemerkt verändert werden. Soll es reaktiviert werden, muss der Status zunächst bewusst zurückgesetzt werden.
4. **SKU-Formatprüfung**: Verhindert Datenübertragungsfehler an Warenwirtschaftssysteme (ERPs) und Versanddienstleister.

---

## 5. Effizienz-Features für das Tagesgeschäft

* **Blitzschnelle Suche**:
  * Tippen Sie in das Suchfeld oben rechts einen Produktnamen, eine Basis-SKU oder sogar eine spezifische Varianten-SKU ein – die Tabelle filtert in Echtzeit.
* **Direktes Inline-Editing**:
  * Wenn Preise steigen oder sinken, klicken Sie in der Tabelle einfach in das Preisfeld des Produkts, tippen den neuen Betrag ein und drücken `Enter`. Kein zeitraubendes Wechseln in die Bearbeitungsmaske erforderlich.
* **Kategorie-Filter**:
  * Filtern Sie über das Dropdown-Menü *Kategorie* gezielt nach bestimmten Warengruppen (z. B. alle *"Schuhe"*).

---

## 6. Häufige Fragen & Problemlösungen (FAQ)

### ❓ Warum erhalte ich beim Klick auf "Aktivieren" eine Fehlermeldung?
* **Antwort**: Das Produkt erfüllt noch nicht alle Mindestanforderungen. Prüfen Sie:
  1. Ist dem Produkt eine Kategorie zugewiesen?
  2. Wurde im Tab *Varianten* mindestens eine Variante angelegt?
  3. Ist der Basispreis höher als 0,00 €?

### ❓ Was passiert, wenn eine Variante keinen eigenen Preis hat?
* **Antwort**: Das System verwendet in diesem Fall automatisch den Basispreis des Hauptprodukts. Ein eigener Variantenpreis ist nur dann nötig, wenn bestimmte Größen (z. B. Übergrößen) oder Farben teurer sind.

### ❓ Kann ein archiviertes Produkt wieder reaktiviert werden?
* **Antwort**: Ja. Wechseln Sie in den Reiter **Archiviert** und klicken Sie auf **Aktivieren** oder wählen Sie im Status-Dropdown **Aktiv**.
