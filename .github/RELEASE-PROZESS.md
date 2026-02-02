# Release-Prozess

Anleitung zum Erstellen neuer Plugin-Versionen mit automatischer ZIP-Erstellung.

## Schnell-Anleitung

### 1. Version aktualisieren

**Dateien ändern:**
- `wetter-vorhersage.php` (Zeile 6): `Version: 2.0.1`
- `CHANGELOG.md`: Neue Änderungen dokumentieren

### 2. Commit & Tag erstellen

```bash
# Änderungen committen
git add .
git commit -m "Version 2.0.1 - Bugfix XYZ"

# Tag erstellen
git tag v2.0.1

# Pushen (mit Tags)
git push && git push --tags
```

### 3. GitHub Release erstellen

**Option A - Über GitHub CLI:**

```bash
gh release create v2.0.1 \
  --title "Version 2.0.1" \
  --notes "## Änderungen
- Bugfix: Problem XYZ behoben
- Verbesserung: Feature ABC optimiert"
```

**Option B - Über GitHub Website:**

1. https://github.com/EinsVier/wetter-vorhersage/releases/new
2. Tag auswählen: `v2.0.1`
3. Release-Titel: `Version 2.0.1`
4. Beschreibung aus CHANGELOG.md kopieren
5. "Publish release" klicken

### 4. Automatische ZIP-Erstellung

**GitHub Actions wird automatisch:**
1. Workflow starten (dauert ~1 Minute)
2. ZIP-Paket erstellen (`wetter-vorhersage-2.0.1.zip`)
3. Zum Release hochladen

**Fortschritt ansehen:**
https://github.com/EinsVier/wetter-vorhersage/actions

---

## Detaillierter Prozess

### Pre-Release Checkliste

Vor jedem Release prüfen:

- [ ] Version in `wetter-vorhersage.php` aktualisiert
- [ ] `CHANGELOG.md` mit allen Änderungen aktualisiert
- [ ] Code lokal getestet
- [ ] Keine offenen kritischen Issues
- [ ] Dokumentation aktualisiert (wenn nötig)
- [ ] Screenshots aktuell (wenn UI geändert)

### Versionierung (Semantic Versioning)

**Format:** `MAJOR.MINOR.PATCH`

- **MAJOR** (2.x.x): Breaking Changes, große Umbauten
  - Beispiel: `2.0.0` → `3.0.0`
  - Nutzer müssen evtl. Einstellungen anpassen

- **MINOR** (x.1.x): Neue Features, abwärtskompatibel
  - Beispiel: `2.0.0` → `2.1.0`
  - Neue Optionen, zusätzliche Funktionen

- **PATCH** (x.x.1): Bugfixes, kleine Verbesserungen
  - Beispiel: `2.0.0` → `2.0.1`
  - Fehlerbehebungen, Performance-Updates

### Release-Typen

**Stable Release:**
```bash
git tag v2.1.0
gh release create v2.1.0 --title "Version 2.1.0"
```

**Pre-Release (Beta/RC):**
```bash
git tag v2.1.0-beta.1
gh release create v2.1.0-beta.1 --prerelease \
  --title "Version 2.1.0 Beta 1" \
  --notes "⚠️ Dies ist eine Beta-Version für Tests"
```

### Changelog-Format

**CHANGELOG.md Eintrag:**

```markdown
## Version 2.0.1 (2026-02-05)

### 🐛 Bugfixes
- Cache-Fehler bei leeren API-Antworten behoben
- Darstellungsfehler auf iOS Safari korrigiert

### 🔧 Verbesserungen
- Performance bei großen Wetterdaten optimiert
- Fehlerbehandlung bei Netzwerk-Timeouts verbessert

### 📚 Dokumentation
- Beispiele für Custom CSS ergänzt
```

### Was passiert bei einem Release?

**1. Tag-Push → GitHub Actions Trigger**
```
git push --tags
  ↓
GitHub erkennt neuen Tag
  ↓
Workflow startet
```

**2. Build-Prozess**
```
- Checkout Code
- Version aus Tag extrahieren (v2.0.1 → 2.0.1)
- Plugin-Verzeichnis erstellen
- Dateien kopieren (ohne .git, .github, etc.)
- ZIP erstellen: wetter-vorhersage-2.0.1.zip
```

**3. Upload**
```
- ZIP zum Release hochladen
- Als Artifact speichern (30 Tage)
- Download-Link verfügbar
```

**4. Ergebnis**
```
✓ Release verfügbar unter:
  https://github.com/EinsVier/wetter-vorhersage/releases

✓ ZIP-Download:
  https://github.com/EinsVier/wetter-vorhersage/releases/download/v2.0.1/wetter-vorhersage-2.0.1.zip
```

### Troubleshooting

**Problem: Workflow schlägt fehl**

1. Workflow-Log anschauen:
   ```
   https://github.com/EinsVier/wetter-vorhersage/actions
   ```

2. Häufige Fehler:
   - Fehlende Dateien → Prüfen ob alle Dateien committed sind
   - Berechtigungsfehler → GITHUB_TOKEN sollte automatisch gesetzt sein
   - Syntax-Fehler → PHP-Code lokal testen

**Problem: ZIP ist zu groß**

- Workflow-Datei anpassen (`release.yml`)
- Weitere Dateien zu `--exclude` hinzufügen

**Problem: Falsche Dateien im ZIP**

1. Lokal testen:
   ```bash
   rsync -av --exclude='.git*' --exclude='.github' ./ test-build/
   cd test-build && ls -la
   ```

2. Workflow-Exclude-Liste anpassen

### Rollback

**Falls ein Release fehlerhaft ist:**

```bash
# Release löschen
gh release delete v2.0.1 --yes

# Tag löschen (lokal und remote)
git tag -d v2.0.1
git push origin :refs/tags/v2.0.1

# Korrektur durchführen
# ... fixes ...

# Neu taggen und releasen
git tag v2.0.1
git push --tags
gh release create v2.0.1 ...
```

### Best Practices

1. **Regelmäßige Releases:**
   - Kleine, häufige Updates besser als große seltene
   - Bugfixes schnell veröffentlichen

2. **Kommunikation:**
   - Klare Release-Notes schreiben
   - Breaking Changes hervorheben
   - Migration-Anleitungen bei großen Updates

3. **Testing:**
   - Jede Version lokal testen
   - Auf verschiedenen PHP-Versionen prüfen
   - Beta-Versionen für große Updates

4. **Dokumentation:**
   - Changelog immer aktuell halten
   - README bei Feature-Änderungen anpassen
   - Screenshots bei UI-Änderungen erneuern

---

## Beispiel-Workflow

**Szenario: Bugfix veröffentlichen**

```bash
# 1. Branch erstellen (optional)
git checkout -b fix/cache-error

# 2. Bugfix implementieren
# ... Code ändern ...

# 3. Version bumpen
# wetter-vorhersage.php: Version: 2.0.0 → 2.0.1

# 4. Changelog aktualisieren
# CHANGELOG.md: Neuer Eintrag für 2.0.1

# 5. Committen
git add .
git commit -m "Fix cache error on empty API responses

- Handle empty API responses gracefully
- Add fallback for missing cache data
- Improve error messages

Fixes #12"

# 6. Mergen (falls Branch verwendet)
git checkout main
git merge fix/cache-error

# 7. Taggen
git tag v2.0.1 -m "Version 2.0.1 - Cache Bugfix"

# 8. Pushen
git push && git push --tags

# 9. Release erstellen
gh release create v2.0.1 \
  --title "Version 2.0.1 - Cache Bugfix" \
  --notes-file CHANGELOG.md

# 10. Fertig! ZIP wird automatisch erstellt
```

**Ergebnis:**
- ✅ Code auf GitHub
- ✅ Release mit Notes
- ✅ ZIP automatisch erstellt und hochgeladen
- ✅ Nutzer können sofort downloaden

---

## Weitere Informationen

- **GitHub Actions Dokumentation:** https://docs.github.com/en/actions
- **WordPress Plugin Handbook:** https://developer.wordpress.org/plugins/
- **Semantic Versioning:** https://semver.org/lang/de/
