# Changelog – Wetter Vorhersage Plugin

## Version 2.0.0 (2026-02-01)

### 🎨 Neue Features

#### Design-Einstellungen
- **Farbkonfiguration:** Textfarbe, Hintergrundfarbe, Akzentfarbe per Farbwähler
- **Typografie:** Schriftgrößen-Auswahl (Klein, Normal, Groß)
- **Layout-Kontrolle:** Kartenbreite konfigurierbar (Auto, Klein, Mittel, Groß)
- **Icon-Styling:** Helligkeitsanpassung für Icons (Hell, Normal, Dunkel)

#### Neues Layout
- **Today Card:** Großer Block für aktuelles Wetter
  - Großer Ortsname in Akzentfarbe
  - 120px Wettericon
  - Große Temperaturanzeige (4.5× Schriftgröße)
  - Prominente Wetterbeschreibung
  - Metadaten: Datum + Min/Max

- **Vorschau-Leiste:** Horizontales Grid mit 3-5 Tagen
  - Kompakte Tagesansicht
  - 45px Icons
  - Min/Max-Temperatur
  - Responsive Grid-Layout

### 🔧 Technische Verbesserungen

#### CSS-Architektur
- **Externes Stylesheet:** `assets/weather.css` statt Inline-CSS
- **CSS-Variablen:** Vollständige Nutzung von Custom Properties
- **Klassen-System:** Dynamische CSS-Klassen basierend auf Einstellungen
- **Modular:** Einfache Erweiterbarkeit für zukünftige Features

#### Code-Qualität
- **Trennung von Logik und Design:** HTML-Markup sauber, kein Inline-Styling
- **Erweiterte Sanitization:** Hex-Farbcode-Validierung, Enum-Validierung
- **Bessere Performance:** Externes CSS wird gecacht
- **Vorbereitet für:** Wetterwarnungen, alternative Layouts, Widget-Support

### 📚 Dokumentation

Neue Dateien:
- `DESIGN-OPTIONEN.md` – Vollständige Anleitung zu Design-Features
- `CHANGELOG.md` – Versionshistorie
- Aktualisierte `CLAUDE.md` – Entwickler-Dokumentation

### 🔄 Migration

**Automatisch:**
- Bestehende Installationen erhalten Standardwerte für neue Optionen
- Keine manuellen Schritte erforderlich
- Alle v1.0-Shortcodes funktionieren weiterhin

**Breaking Changes:**
- Keine – 100% rückwärtskompatibel

### 🐛 Bugfixes
- Verbesserte mobile Darstellung bei sehr schmalen Viewports (<380px)
- Farbkontrast-Optimierung für bessere Lesbarkeit
- Grid-Layout stabiler bei dynamischer Anzahl von Tagen

---

## Version 1.0.0

### Initial Release
- OpenWeatherMap API-Integration
- Shortcode `[wetter_vorhersage]`
- Admin-Einstellungsseite
- Cache-System mit Transients
- Responsive Vertikalkarten-Layout
- SVG-Icons für Wetterbedingungen
- Deutsche Lokalisierung
