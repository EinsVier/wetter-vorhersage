# Design-Optionen – Wetter Vorhersage v2.0

Seit Version 2.0 bietet das Wetter-Plugin umfassende Design-Anpassungen über die Einstellungsseite.

## Zugriff auf Design-Einstellungen

**WordPress-Admin → Einstellungen → Wetter → Design-Einstellungen**

---

## Verfügbare Optionen

### 🎨 Farbeinstellungen

#### Textfarbe
- **Standard:** `#333333` (Dunkelgrau)
- **Beschreibung:** Farbe für den gesamten Text in der Wetterkarte
- **Verwendung:** Haupttext, Temperaturen, Beschreibungen
- **Tipp:** Bei dunklen Hintergründen helle Farben verwenden (z.B. `#ffffff`)

#### Hintergrundfarbe
- **Standard:** `#ffffff` (Weiß)
- **Beschreibung:** Hintergrundfarbe der gesamten Wetterkarte
- **Verwendung:** Kartenoberfläche
- **Tipp:** Sollte zum Theme-Design passen

#### Akzentfarbe
- **Standard:** `#f7971e` (Orange)
- **Beschreibung:** Farbe für Hervorhebungen und Icons
- **Verwendung:**
  - Ortsname (großer Titel)
  - Wettersymbole
  - Links im Footer
- **Tipp:** Sollte kontrastreich zur Hintergrundfarbe sein

### 📏 Typografie

#### Schriftgröße
- **Standard:** Normal
- **Optionen:**
  - **Klein:** 0.875rem (14px) – Kompakt für Sidebars
  - **Normal:** 1rem (16px) – Ausgewogen für Hauptinhalte
  - **Groß:** 1.125rem (18px) – Gut lesbar, großzügig

**Anwendungsfall:**
- Klein → Sidebar-Widget
- Normal → Standard-Seiten
- Groß → Landingpages, Großanzeigen

### 📐 Layout

#### Kartenbreite
- **Standard:** Mittel (400px)
- **Optionen:**
  - **Automatisch:** 100% bis max. 600px – Passt sich Container an
  - **Klein:** 320px – Kompakt für schmale Sidebars
  - **Mittel:** 400px – Ausgewogen, Standard
  - **Groß:** 500px – Großzügig, für breite Spalten

**Responsive-Verhalten:**
Alle Breiten passen sich auf mobilen Geräten automatisch an (<500px = 100% Breite).

#### Icon-Helligkeit
- **Standard:** Normal
- **Optionen:**
  - **Hell:** Icons werden aufgehellt (Brightness 1.2)
  - **Normal:** Standard-Helligkeit
  - **Dunkel:** Icons werden abgedunkelt (Brightness 0.7)

**Verwendung:**
- Hell → Bei dunklen Hintergründen
- Normal → Bei weißen/hellen Hintergründen
- Dunkel → Für gedeckte, elegante Looks

---

## Layout-Struktur (v2.0)

Das neue Layout besteht aus zwei Hauptbereichen:

### 1️⃣ Today Card (Aktueller Tag)
Großer Block oben mit:
- **Ortsname:** Großgeschrieben, Akzentfarbe
- **Großes Icon:** Aktuelles Wettersymbol (120px)
- **Große Temperatur:** Aktuelle Temperatur (4.5× Schriftgröße)
- **Beschreibung:** Wetterbeschreibung (z.B. "Leichter Regen")
- **Metadaten:** Datum + Min/Max-Temperatur

### 2️⃣ Vorschau-Leiste (3-5 Tage)
Horizontales Grid darunter mit:
- **Wochentag:** Kurzform (Mo, Di, Mi, ...)
- **Datum:** Numerisch (1.2., 2.2., ...)
- **Kleines Icon:** Wettersymbol (45px)
- **Min/Max:** Temperaturbereich

**Anzahl Tage:** Über "Anzahl Vorschau-Tage" in Grundeinstellungen steuerbar (3-5 Tage).

---

## CSS-Technik

### CSS-Variablen
Das Plugin nutzt CSS Custom Properties für maximale Flexibilität:

```css
:root {
    --wetter-text-color: #333333;
    --wetter-bg-color: #ffffff;
    --wetter-accent-color: #f7971e;
    --wetter-card-width: 400px;
    --wetter-font-size: 1rem;
    --wetter-icon-brightness: 1;
}
```

### Externe Stylesheet
Alle Styles befinden sich in `assets/weather.css` (keine Inline-Styles mehr).

### Klassen-System
Die Wetterkarte erhält automatisch Klassen basierend auf Einstellungen:

```html
<div class="wetter-karte font-normal width-medium icons-normal">
    ...
</div>
```

---

## Beispiel-Konfigurationen

### Dark Mode
```
Textfarbe:        #ffffff
Hintergrundfarbe: #1a1a1a
Akzentfarbe:      #ffa500
Schriftgröße:     Normal
Kartenbreite:     Mittel
Icon-Helligkeit:  Hell
```

### Minimalistisch
```
Textfarbe:        #000000
Hintergrundfarbe: #f5f5f5
Akzentfarbe:      #000000
Schriftgröße:     Klein
Kartenbreite:     Klein
Icon-Helligkeit:  Dunkel
```

### Farbenfroher Sommer
```
Textfarbe:        #2c3e50
Hintergrundfarbe: #ecf0f1
Akzentfarbe:      #e74c3c
Schriftgröße:     Groß
Kartenbreite:     Groß
Icon-Helligkeit:  Normal
```

### Sidebar-Widget
```
Textfarbe:        #333333
Hintergrundfarbe: #ffffff
Akzentfarbe:      #0073aa (WordPress-Blau)
Schriftgröße:     Klein
Kartenbreite:     Klein
Icon-Helligkeit:  Normal
```

---

## Erweiterte Anpassungen

### Eigenes CSS überschreiben

Falls weitere Anpassungen nötig sind, können Sie in Ihrem Theme eigenes CSS hinzufügen:

```css
/* Im Theme: style.css oder Custom CSS */
.wetter-karte {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    border: 2px solid #ddd !important;
}

.wetter-ort-gross {
    font-family: 'Georgia', serif !important;
}
```

### Filter für Entwickler

```php
// CSS-Variablen programmatisch anpassen
add_filter('wetter_custom_css_vars', function($css) {
    return $css . "
        --wetter-border-radius: 8px;
    ";
});
```

---

## Migration von v1.0

**Automatische Migration:**
Bestehende Installationen behalten ihre Einstellungen. Neue Design-Optionen erhalten Standardwerte.

**Visuelle Änderungen:**
- Layout wurde von einspaltig zu zweigeteilt geändert (Today Card + Vorschau)
- Header-Gradient entfernt, durch Akzentfarbe ersetzt
- Icons sind jetzt größer und prominenter
- Responsive-Verhalten verbessert

**Breaking Changes:**
Keine – alle Shortcodes funktionieren weiterhin ohne Änderungen.

---

## Support & Feedback

Bei Fragen oder Problemen mit den Design-Optionen:
- Prüfen Sie die Farbkontraste (Text muss auf Hintergrund lesbar sein)
- Testen Sie verschiedene Bildschirmgrößen
- Cache löschen nach Design-Änderungen

**Plugin-Version:** 2.0.0
**WordPress-Kompatibilität:** 5.0+
