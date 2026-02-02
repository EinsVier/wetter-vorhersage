# Plugin-Verteilung – Wetter Vorhersage

Anleitung zur Vorbereitung und Verteilung des Plugins.

---

## 📋 Checkliste vor der Verteilung

### 1. Plugin-Header aktualisieren

**Datei:** `wetter-vorhersage.php` (Zeilen 2-11)

Aktualisieren Sie folgende Felder:

```php
/**
 * Plugin Name: Wetter Vorhersage
 * Plugin URI: https://ihre-website.de/wetter-vorhersage  ← ÄNDERN
 * Description: Zeigt das aktuelle Wetter und die Vorhersage für die nächsten 3 Tage mit OpenWeatherMap API
 * Version: 2.0.0
 * Author: Ihr Name                                       ← ÄNDERN
 * Author URI: https://ihre-website.de                    ← ÄNDERN
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wetter-vorhersage
 * Requires at least: 5.0
 * Requires PHP: 7.0
 */
```

**Wichtig:**
- `Plugin URI` → URL zur Plugin-Website oder Repository
- `Author` → Ihr Name oder Firmenname
- `Author URI` → Ihre Website

---

### 2. README.txt erstellen (WordPress-Standard)

WordPress.org erfordert eine `readme.txt` im speziellen Format:

**Datei erstellen:** `readme.txt`

```
=== Wetter Vorhersage ===
Contributors: ihrwordpressusername
Tags: weather, forecast, openweathermap, widget, sidebar
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 2.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zeigt das aktuelle Wetter und die Vorhersage mit vollständig anpassbarem Design.

== Description ==

Wetter Vorhersage zeigt Echtzeitwetterdaten von OpenWeatherMap in einer wunderschön gestalteten Karte an. Mit Version 2.0 haben Sie vollständige Kontrolle über Farben, Layout und Typografie.

**Features:**
* Aktuelles Wetter + 3-5 Tage Vorhersage
* Vollständig anpassbares Design (Farben, Schriftgrößen, Layout)
* Responsive Design für alle Bildschirmgrößen
* Cache-System für optimale Performance
* Deutsche Lokalisierung
* Sidebar-tauglich

**Voraussetzungen:**
* Kostenloser OpenWeatherMap API-Key (1000 Aufrufe/Tag)

== Installation ==

1. Plugin hochladen nach `/wp-content/plugins/wetter-vorhersage/`
2. Plugin aktivieren unter "Plugins" im WordPress-Admin
3. Einstellungen öffnen: "Einstellungen → Wetter"
4. API-Key eintragen (von openweathermap.org)
5. Koordinaten eingeben
6. Shortcode `[wetter_vorhersage]` auf Seite/Beitrag einfügen

== Frequently Asked Questions ==

= Wo bekomme ich einen API-Key? =

Registrieren Sie sich kostenlos bei https://openweathermap.org/api
Der Free Plan bietet 1000 API-Aufrufe pro Tag.

= Wie finde ich meine Koordinaten? =

Nutzen Sie https://www.latlong.net/ oder Google Maps (Rechtsklick → Koordinaten kopieren).

= Kann ich mehrere Standorte anzeigen? =

Aktuell unterstützt das Plugin einen Standort. Für mehrere Standorte können Sie mehrere WordPress-Instanzen nutzen oder das Plugin anpassen.

= Ist das Plugin DSGVO-konform? =

Ja, das Plugin kommuniziert nur mit OpenWeatherMap (kein Tracking, keine Cookies). Prüfen Sie die Datenschutzrichtlinien von OpenWeatherMap.

== Screenshots ==

1. Wetterkarte im Standard-Design
2. Admin-Einstellungsseite - Standort & API
3. Design-Einstellungen - Farben & Layout
4. Responsive Darstellung auf Mobilgeräten
5. Dark Mode Beispiel

== Changelog ==

= 2.0.0 (2026-02-01) =
* Neues zweigeteiltes Layout (Today Card + Vorschau)
* Design-Einstellungen: Farben, Schriftgrößen, Kartenbreite
* Externes CSS mit CSS-Variablen
* Icon-Helligkeit anpassbar
* Verbesserte mobile Darstellung
* Vollständige Dokumentation

= 1.0.0 =
* Erste Veröffentlichung

== Upgrade Notice ==

= 2.0.0 =
Große Update mit neuen Design-Optionen. 100% rückwärtskompatibel - keine Änderungen an bestehenden Shortcodes nötig.
```

**Tool zum Validieren:** https://wordpress.org/plugins/developers/readme-validator/

---

### 3. Screenshots erstellen (optional, für WordPress.org)

**Speicherort:** `assets/` (außerhalb des Plugin-Ordners bei WordPress.org)

Erstellen Sie Screenshots:
- `screenshot-1.png` - Wetterkarte im Standard-Design (1200x900px)
- `screenshot-2.png` - Admin-Einstellungsseite
- `screenshot-3.png` - Design-Einstellungen
- `screenshot-4.png` - Mobile Ansicht
- `screenshot-5.png` - Dark Mode Beispiel

**Format:**
- PNG oder JPG
- Empfohlen: 1200x900px oder 1280x960px
- Max. 1MB pro Bild

---

### 4. Lizenz-Datei hinzufügen

**Datei erstellen:** `LICENSE.txt`

```
GNU GENERAL PUBLIC LICENSE
Version 2, June 1991

Copyright (C) 2026 Ihr Name

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
```

Vollständiger Text: https://www.gnu.org/licenses/gpl-2.0.txt

---

### 5. Code-Qualität prüfen

#### Sicherheits-Checkliste

✅ **Aktueller Stand (bereits implementiert):**
- Nonces bei Admin-Formularen (WordPress Settings API)
- Capability-Checks (`manage_options`)
- Input-Sanitization (`sanitize_text_field`, Regex-Validierung)
- Output-Escaping (`esc_html`, `esc_attr`, `esc_url`)
- SQL-Injection-Schutz (keine direkten Queries)
- XSS-Schutz (alle User-Inputs escaped)
- API-Key-Schutz (Passwort-Feld, nie im Klartext)

#### WordPress Coding Standards

**Tool installieren:**
```bash
composer require --dev wp-coding-standards/wpcs
vendor/bin/phpcs --config-set installed_paths vendor/wp-coding-standards/wpcs
```

**Code prüfen:**
```bash
vendor/bin/phpcs --standard=WordPress wetter-vorhersage.php
```

**Auto-Fix (wo möglich):**
```bash
vendor/bin/phpcbf --standard=WordPress wetter-vorhersage.php
```

---

### 6. Testen auf verschiedenen Umgebungen

**WordPress-Versionen:**
- WordPress 5.0 (Mindestanforderung)
- WordPress 6.0
- WordPress 6.4 (aktuell)

**PHP-Versionen:**
- PHP 7.0 (Mindestanforderung)
- PHP 7.4
- PHP 8.0
- PHP 8.1
- PHP 8.2

**Themes testen:**
- Twenty Twenty-Four
- Twenty Twenty-Three
- Beliebte Third-Party-Themes (Astra, GeneratePress, etc.)

**Browser:**
- Chrome/Edge
- Firefox
- Safari
- Mobile Browser (iOS Safari, Chrome Mobile)

---

## 📦 Verteilungsoptionen

### Option 1: WordPress.org Plugin Repository (empfohlen)

**Vorteile:**
- Höchste Reichweite
- Automatische Updates für Nutzer
- Vertrauenswürdig
- Kostenlos

**Prozess:**

1. **Account erstellen:**
   - https://wordpress.org/support/register.php

2. **Plugin einreichen:**
   - https://wordpress.org/plugins/developers/add/
   - ZIP-Datei hochladen
   - Wartezeit: 1-14 Tage Review

3. **SVN-Repository einrichten:**
   ```bash
   svn co https://plugins.svn.wordpress.org/wetter-vorhersage
   cd wetter-vorhersage

   # Dateien in trunk/ kopieren
   cp -r /pfad/zum/plugin/* trunk/

   # Assets (Screenshots) in assets/
   cp screenshots/*.png assets/

   # Committen
   svn add trunk/* assets/*
   svn ci -m "Initial release 2.0.0"

   # Tag erstellen
   svn cp trunk tags/2.0.0
   svn ci -m "Tagging version 2.0.0"
   ```

4. **Updates veröffentlichen:**
   - Änderungen in `trunk/` committen
   - Neue Tags erstellen: `svn cp trunk tags/2.0.1`
   - Automatische Update-Benachrichtigung an Nutzer

**Dokumentation:**
https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/

---

### Option 2: GitHub Releases

**Vorteile:**
- Volle Kontrolle
- Issue-Tracking
- Community-Beiträge möglich
- Kostenlos

**Prozess:**

1. **Repository erstellen:**
   ```bash
   cd /pfad/zum/plugin
   git init
   git add .
   git commit -m "Initial commit v2.0.0"
   ```

2. **Zu GitHub pushen:**
   ```bash
   git remote add origin https://github.com/username/wetter-vorhersage.git
   git branch -M main
   git push -u origin main
   ```

3. **Release erstellen:**
   - GitHub → Releases → "Create a new release"
   - Tag: `v2.0.0`
   - Title: `Version 2.0.0`
   - Description: Changelog kopieren
   - ZIP-Datei anhängen

4. **Updates:**
   ```bash
   # Version aktualisieren in wetter-vorhersage.php
   git add .
   git commit -m "Version 2.0.1"
   git tag v2.0.1
   git push && git push --tags
   ```

**Installations-Anleitung für Nutzer:**
```
1. Release-ZIP herunterladen
2. WordPress-Admin → Plugins → Installieren → Plugin hochladen
3. ZIP auswählen → Jetzt installieren → Aktivieren
```

---

### Option 3: Eigene Website / Shop

**Für kommerzielle Versionen mit Premium-Features:**

1. **ZIP-Paket erstellen:**
   ```bash
   cd /pfad/zu/plugins
   zip -r wetter-vorhersage-2.0.0.zip wetter-vorhersage/ \
       -x "*.git*" "*.DS_Store" "*node_modules*"
   ```

2. **Update-System einrichten:**
   - Plugin Update Checker Library nutzen
   - Eigenen Update-Server aufsetzen
   - Lizenzschlüssel-Validierung

**Tools:**
- **EDD (Easy Digital Downloads)** - für Verkauf
- **Freemius** - Update-System + Monetarisierung
- **Plugin Update Checker** - Selbst-gehostete Updates

---

## 📝 Plugin-Paket erstellen

### Einfaches ZIP (für manuelle Distribution)

```bash
cd /mnt/c/workspace/WordPress/wordpress-neukalen/wp-content/plugins

# Sauberes Paket ohne Entwicklungs-Dateien
zip -r wetter-vorhersage-2.0.0.zip wetter-vorhersage/ \
    -x "*.git*" \
    -x "*.DS_Store" \
    -x "*node_modules*" \
    -x "*.idea*" \
    -x "*composer.json*" \
    -x "*package.json*"
```

**Inhalt prüfen:**
```bash
unzip -l wetter-vorhersage-2.0.0.zip
```

**Sollte enthalten:**
```
wetter-vorhersage/
├── wetter-vorhersage.php
├── assets/
│   └── weather.css
├── README.md
├── ADMIN-ANLEITUNG.md
├── DESIGN-OPTIONEN.md
├── CHANGELOG.md
├── LICENSE.txt
└── readme.txt (für WordPress.org)
```

---

## 🔄 Versionierung

**Semantic Versioning verwenden:**
- `2.0.0` → Große Änderungen, Breaking Changes
- `2.1.0` → Neue Features, abwärtskompatibel
- `2.1.1` → Bugfixes

**Bei jedem Update ändern:**
1. Plugin-Header (`Version: 2.1.0`)
2. `readme.txt` (`Stable tag: 2.1.0`)
3. `CHANGELOG.md` aktualisieren
4. Git-Tag erstellen (`git tag v2.1.0`)

---

## ⚖️ Rechtliches

### Lizenzierung

**GPL v2 or later:**
- ✅ Kostenlose Weitergabe erlaubt
- ✅ Modifikationen erlaubt
- ✅ Kommerzieller Einsatz erlaubt
- ⚠️ Abgeleitete Werke müssen auch GPL sein
- ⚠️ Keine Garantie/Haftung

**Wichtig:**
- OpenWeatherMap API hat eigene Terms of Service
- Nutzer müssen eigenen API-Key besorgen
- Keine API-Keys im Plugin-Code verteilen

### Copyright

```php
/**
 * @copyright 2026 Ihr Name
 * @license GPL-2.0-or-later
 */
```

---

## 📢 Marketing (optional)

Wenn Sie Nutzer gewinnen möchten:

1. **WordPress.org:**
   - Gute Screenshots
   - Ausführliche Beschreibung
   - FAQ mit häufigen Fragen
   - Regelmäßige Updates

2. **Social Media:**
   - Plugin auf Twitter/X ankündigen
   - Reddit (r/WordPress, r/ProWordPress)
   - Facebook WordPress-Gruppen

3. **Website:**
   - Eigene Plugin-Seite mit Demo
   - Video-Tutorial
   - Ausführliche Dokumentation

4. **Support:**
   - WordPress.org Support-Forum nutzen
   - GitHub Issues aktivieren
   - Schnelle Antworten auf Fragen

---

## ✅ Finale Checkliste

Vor der Veröffentlichung:

- [ ] Plugin-Header aktualisiert (Name, URL, Autor)
- [ ] `readme.txt` erstellt (WordPress-Format)
- [ ] `LICENSE.txt` hinzugefügt
- [ ] Screenshots erstellt (5 Stück)
- [ ] Code-Qualität geprüft (PHPCS)
- [ ] Sicherheit geprüft (Escaping, Sanitization)
- [ ] Auf WordPress 5.0+ getestet
- [ ] Auf PHP 7.0+ getestet
- [ ] Mobile/Responsive getestet
- [ ] Verschiedene Themes getestet
- [ ] Dokumentation vollständig
- [ ] CHANGELOG.md aktuell
- [ ] ZIP-Paket erstellt
- [ ] Verteilungskanal gewählt

**Bereit zur Veröffentlichung! 🚀**
