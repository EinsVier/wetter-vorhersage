# Wetter Vorhersage Plugin

Ein WordPress-Plugin zur Anzeige von Wettervorhersagen über die OpenWeatherMap API in einer modernen, vertikalen Karten-Darstellung.

## Installation

1. Laden Sie den Ordner `wetter-vorhersage` in Ihr WordPress-Verzeichnis unter `wp-content/plugins/`
2. Aktivieren Sie das Plugin im WordPress-Backend unter "Plugins"
3. Konfigurieren Sie das Plugin (siehe unten)

## Konfiguration

### 1. API-Key eintragen

Öffnen Sie die Datei `wetter-vorhersage.php` und tragen Sie Ihren OpenWeatherMap API-Key ein:

```php
define('WETTER_API_KEY', 'IHR_API_KEY_HIER');
```

**Kostenlosen API-Key erhalten:**
- Registrieren Sie sich auf https://openweathermap.org/api
- Wählen Sie den "Free Plan" (1000 Aufrufe/Tag kostenlos)
- Kopieren Sie Ihren API-Key

### 2. Standort konfigurieren

Tragen Sie die Koordinaten Ihres gewünschten Standorts ein:

```php
define('WETTER_LATITUDE', '53.822');  // Breitengrad (Beispiel: Neukalen)
define('WETTER_LONGITUDE', '12.788'); // Längengrad (Beispiel: Neukalen)
```

**Koordinaten finden:**
- Google Maps: Rechtsklick auf Ort → "Koordinaten" kopieren
- Oder: https://www.latlong.net/

### 3. Cache-Dauer anpassen (optional)

Die Standard-Cache-Dauer beträgt 30 Minuten (1800 Sekunden):

```php
define('WETTER_CACHE_TIME', 1800); // in Sekunden
```

## Verwendung

### Shortcode in Seiten/Beiträgen

Fügen Sie folgenden Shortcode in jeden Beitrag oder jede Seite ein:

```
[wetter_vorhersage]
```

### Shortcode in Templates

In Theme-Dateien (z.B. Sidebar):

```php
<?php echo do_shortcode('[wetter_vorhersage]'); ?>
```

### In WordPress-Widgets

Das Plugin funktioniert auch in Text-Widgets:
1. Design → Widgets
2. Text-Widget hinzufügen
3. `[wetter_vorhersage]` einfügen

## Layout-Struktur

Das Plugin zeigt eine vertikale Wetterkarte mit folgendem Aufbau:

### 1. Kopfbereich (Header)
- **Ortsname** in Großbuchstaben (wird automatisch von der API geladen)
- Farbiger Hintergrund (Orange-Gelb-Gradient)

### 2. Hauptbereich (Aktuelles Wetter)
- Großes Wettersymbol
- Aktuelle Temperatur (große Anzeige)
- Deutsche Wetterbeschreibung

### 3. Detailzeile für Heute
- Aktuelles Datum (ausgeschrieben)
- Min/Max-Temperatur für den Tag

### 4. Vorhersage-Leiste (Horizontal)
- 3 gleich breite Spalten für die nächsten 3 Tage
- Pro Tag: Wochentag, Datum, Symbol, Min/Max-Temperatur

### 5. Footer
- Credits: "Daten von OpenWeatherMap"
- Letzte Aktualisierung (Uhrzeit)

## Features

- ✅ **Vertikales Karten-Design** - Modern und kompakt
- ✅ **Aktuelles Wetter** - Temperatur, Min/Max, Beschreibung
- ✅ **3-Tage-Vorhersage** - Übersichtlich in horizontaler Leiste
- ✅ **Deutsche Beschreibungen** - Vollständig lokalisiert
- ✅ **SVG-Wettersymbole** - Sonne, Wolken, Regen, Schnee, Gewitter, Nebel
- ✅ **Automatischer Ortsname** - Wird von der API geladen
- ✅ **Responsive Design** - Funktioniert auf allen Geräten
- ✅ **Sidebar-geeignet** - Max. 400px Breite
- ✅ **Automatisches Caching** - Schont API-Limit
- ✅ **Sichere Ausgabe** - XSS-Schutz

## Caching

Das Plugin nutzt WordPress Transients für intelligentes Caching:

- **Cache-Schlüssel**: `wetter_forecast_data`
- **Standard-Dauer**: 30 Minuten
- **Vorteil**: Reduziert API-Aufrufe und verbessert Ladezeiten

### Cache manuell löschen

Fügen Sie folgende URL im Browser auf (als Administrator eingeloggt):

```
https://ihre-website.de/wp-admin/admin-post.php?action=wetter_clear_cache
```

**Oder** entkommentieren Sie in der `wetter-vorhersage.php` die letzte Zeile:

```php
add_action('admin_post_wetter_clear_cache', 'wetter_clear_cache');
```

## Technische Details

- **API**: OpenWeatherMap Forecast API (kostenloser Plan)
- **HTTP-Client**: WordPress HTTP API (`wp_remote_get`)
- **Caching**: WordPress Transients API
- **Sicherheit**: Alle Ausgaben mit `esc_html()` gesichert
- **Icons**: Inline-SVG (keine externen Requests)
- **CSS**: Inline-Styles (nur geladen wenn Shortcode aktiv)
- **Max. Breite**: 400px (perfekt für Sidebars)
- **Responsive**: Funktioniert ab 320px Breite

## Anpassungen

### Farben ändern

Passen Sie in `wetter_get_inline_css()` die Farben an:

```css
/* Header-Farbe ändern */
.wetter-header {
    background: linear-gradient(135deg, #IHR_FARBCODE 0%, #IHR_FARBCODE 100%);
}

/* Icon-Farbe ändern */
.wetter-icon-gross {
    color: #IHR_FARBCODE;
}
```

### Maximale Breite ändern

```css
.wetter-karte {
    max-width: 500px; /* Statt 400px */
}
```

## Fehlerbehebung

### "Bitte konfigurieren Sie den OpenWeatherMap API-Key"
→ API-Key in Zeile 22 der `wetter-vorhersage.php` eintragen

### "Fehler beim Laden der Wetterdaten"
→ Prüfen Sie Ihre Internetverbindung und ob der API-Key gültig ist

### Keine Daten sichtbar
→ Prüfen Sie die Koordinaten und aktivieren Sie WordPress Debug-Modus

### Alte Daten werden angezeigt
→ Cache manuell löschen (siehe oben)

## API-Limits

**Kostenloser Plan:**
- 1.000 API-Aufrufe pro Tag
- 60 Aufrufe pro Minute

**Mit 30-Minuten-Cache:**
- Bei ca. 100 Seitenaufrufen/Tag: ~48 API-Aufrufe
- Weit innerhalb des kostenlosen Limits

## Support

Bei Fragen oder Problemen:
- Prüfen Sie die Fehlerbehebung oben
- Aktivieren Sie WordPress Debug-Modus für Details
- Kontaktieren Sie den Plugin-Autor

## Lizenz

GPL v2 or later

---

**Entwickelt für WordPress 5.0+** | **Getestet bis WordPress 6.4**
