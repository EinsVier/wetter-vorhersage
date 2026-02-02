# Admin-Einstellungsseite - Dokumentation

## ✅ Was wurde implementiert

Das Wetter-Plugin verfügt jetzt über eine vollständige Admin-Einstellungsseite im WordPress-Backend.

### Zugriff
**Einstellungen → Wetter**

---

## 🔧 Implementierte Funktionen

### 1. WordPress Options API Integration

**Alle Einstellungen werden sicher in der Datenbank gespeichert:**
- Option-Name: `wetter_vorhersage_options`
- Speicherort: `wp_options` Tabelle
- Keine fest codierten Werte mehr im Code

### 2. Konfigurierbare Optionen

| Option | Typ | Beschreibung | Validierung |
|--------|-----|--------------|-------------|
| **API-Key** | Password | OpenWeatherMap API-Key | Niemals im Klartext angezeigt |
| **Latitude** | Float | Breitengrad | -90 bis 90 |
| **Longitude** | Float | Längengrad | -180 bis 180 |
| **Ortsname** | Text | Benutzerdefinierter Name | Optional, frei |
| **Vorschau-Tage** | Integer | Anzahl Vorhersage-Tage | 1 bis 5 |
| **Cache-Dauer** | Integer | Minuten | 5 bis 1440 |

### 3. Sicherheits-Features

✅ **API-Key Schutz:**
- Wird als Passwort-Feld angezeigt
- Niemals im Klartext ausgegeben
- Bei Anzeige: `********************`
- Status-Anzeige: ✓ API-Key ist gesetzt

✅ **Escaping & Sanitization:**
- Alle Eingaben mit `sanitize_text_field()` bereinigt
- Ausgaben mit `esc_attr()` und `esc_html()` gesichert
- Numerische Felder validiert

✅ **Berechtigungen:**
- Nur für Admins (`manage_options`)
- Automatische WordPress-Nonce-Prüfung

### 4. Validierung

**Automatische Fehlerprüfung:**
- Latitude: Muss zwischen -90 und 90 liegen
- Longitude: Muss zwischen -180 und 180 liegen
- Vorschau-Tage: 1-5
- Cache-Dauer: 5-1440 Minuten

**Fehlerbehandlung:**
- Fehlermeldungen werden angezeigt
- Ungültige Werte werden auf Defaults zurückgesetzt
- Erfolgs-Meldung bei korrektem Speichern

### 5. UI-Features

✅ **WordPress-konformes Design:**
- `.wrap` Container
- `.form-table` Layout
- Standard WordPress Buttons
- Notice-System für Meldungen

✅ **Hilfetexte:**
- Beschreibungen unter jedem Feld
- Links zu Hilfe-Ressourcen
- API-Limit-Informationen

✅ **Warnungen:**
- Prominente Warnung wenn kein API-Key gesetzt
- Link zur API-Registrierung

✅ **Zusatz-Informationen:**
- Plugin-Verwendung (Shortcodes)
- Cache-Lösch-Button
- API-Limit-Statistiken

---

## 🔄 Technische Details

### Funktionen-Übersicht

```php
// Options API
wetter_get_default_options()    // Default-Werte definieren
wetter_get_option($key)         // Option mit Fallback laden

// Admin-Hooks
wetter_admin_menu()             // Menü registrieren (add_options_page)
wetter_register_settings()      // Settings registrieren (register_setting)
wetter_sanitize_options($input) // Validierung & Sanitization

// Rendering
wetter_render_settings_page()   // Einstellungsseite HTML
wetter_admin_notices()          // Success/Error Notices

// Cache-Verwaltung
wetter_admin_clear_cache()      // Admin-Action für Cache löschen
wetter_clear_cache()            // Cache löschen
```

### Datenfluss

```
Benutzer füllt Formular aus
        ↓
WordPress validiert Nonce
        ↓
wetter_sanitize_options() wird aufgerufen
        ↓
Eingaben werden validiert & gesäubert
        ↓
Bei Fehler: add_settings_error()
        ↓
Daten in wp_options gespeichert
        ↓
Erfolgs-Meldung angezeigt
```

### Default-Werte

```php
array(
    'api_key' => '',              // Leer (muss gesetzt werden)
    'latitude' => '53.822',       // Neukalen
    'longitude' => '12.788',      // Neukalen
    'location_name' => '',        // Leer = von API
    'forecast_days' => 3,         // 3 Tage Vorhersage
    'cache_duration' => 30        // 30 Minuten
)
```

---

## 🚀 Verwendung

### Plugin aktivieren
1. WordPress-Backend → Plugins
2. "Wetter Vorhersage" aktivieren
3. **Automatisch:** Default-Werte werden gesetzt

### Einstellungen konfigurieren
1. **Einstellungen → Wetter** öffnen
2. **API-Key eintragen** (zwingend erforderlich)
3. Koordinaten anpassen (falls gewünscht)
4. Optional: Benutzerdefinierten Ortsnamen eintragen
5. **Speichern**

### Cache verwalten
- **Automatisch:** Cache läuft nach konfigurierten Minuten ab
- **Manuell:** Button "Cache jetzt löschen" auf der Einstellungsseite

---

## 📋 Code-Integration

### Angepasste Funktionen

**Alle Funktionen verwenden jetzt die Options API:**

```php
// Vorher (Konstanten)
define('WETTER_API_KEY', '...');
$api_key = WETTER_API_KEY;

// Nachher (Options)
$api_key = wetter_get_option('api_key');
```

**Betroffene Funktionen:**
- `wetter_get_forecast_data()` - Verwendet API-Key, Koordinaten, Cache-Dauer
- `wetter_get_next_days()` - Verwendet forecast_days
- `wetter_get_location_name()` - Verwendet location_name (falls gesetzt)
- `wetter_vorhersage_shortcode()` - Prüft API-Key via Options

### Aktivierungs-Hook

```php
register_activation_hook(__FILE__, 'wetter_activate_plugin');
```

**Beim ersten Aktivieren:**
- Prüft ob Optionen bereits existieren
- Setzt Default-Werte falls nicht vorhanden
- Kein Überschreiben bei Re-Aktivierung

---

## 🛡️ Sicherheit

### Implementierte Maßnahmen

1. **Capability-Prüfung:** Nur Admins (`manage_options`)
2. **Nonce-Prüfung:** Automatisch via `settings_fields()`
3. **Sanitization:** Alle Eingaben werden bereinigt
4. **Escaping:** Alle Ausgaben werden escaped
5. **Validierung:** Numerische Bereiche werden geprüft
6. **API-Key:** Niemals im Klartext angezeigt

### Password-Feld-Logik

```php
// Bei Anzeige
value="<?php echo $has_api_key ? '********************' : ''; ?>"

// Bei Speicherung
if (Passwort-Platzhalter) {
    // Alten Wert behalten
} else {
    // Neuen Wert speichern
}
```

---

## 🎨 UI-Elemente

### Formular-Struktur

```html
<div class="wrap">
    <h1>Wetter Vorhersage Einstellungen</h1>

    <!-- Warnungen -->
    <div class="notice notice-warning">...</div>

    <!-- Formular -->
    <form method="post" action="options.php">
        <table class="form-table">
            <tr>
                <th><label>...</label></th>
                <td>
                    <input ... />
                    <p class="description">...</p>
                </td>
            </tr>
        </table>
        <submit-button>
    </form>

    <!-- Zusatz-Infos -->
    <h2>Plugin-Verwendung</h2>
    <table class="widefat">...</table>
</div>
```

### Notice-Typen

- **Warning:** Kein API-Key gesetzt
- **Error:** Validierungs-Fehler
- **Success:** Einstellungen gespeichert / Cache gelöscht

---

## 📦 Datei-Struktur

```
wp-content/plugins/wetter-vorhersage/
├── wetter-vorhersage.php    (1041 Zeilen)
│   ├── Plugin-Header
│   ├── Admin-Funktionen (neu)
│   ├── Core-Funktionen (angepasst)
│   ├── Shortcode
│   ├── CSS
│   └── Hooks
├── README.md
└── ADMIN-ANLEITUNG.md (diese Datei)
```

---

## ✨ Vorteile

### Für Benutzer
✅ Keine Code-Bearbeitung mehr nötig
✅ Intuitive Benutzeroberfläche
✅ Sofortiges Feedback bei Fehlern
✅ Hilfreiche Beschreibungen

### Für Entwickler
✅ WordPress-Standards eingehalten
✅ Options API korrekt implementiert
✅ Saubere Validierung
✅ Erweiterbar für neue Optionen
✅ Revisions-sicher

### Für Sicherheit
✅ API-Key wird nie im Klartext angezeigt
✅ Alle Eingaben validiert
✅ XSS-Schutz durch Escaping
✅ Berechtigungen geprüft

---

## 🔮 Erweiterungsmöglichkeiten

Die Struktur erlaubt einfaches Hinzufügen neuer Optionen:

1. In `wetter_get_default_options()` hinzufügen
2. In `wetter_sanitize_options()` validieren
3. In `wetter_render_settings_page()` Feld hinzufügen
4. In relevanten Funktionen via `wetter_get_option()` verwenden

---

**Version:** 1.0.0
**Stand:** 2026-02-01
**WordPress-Kompatibilität:** 5.0+
