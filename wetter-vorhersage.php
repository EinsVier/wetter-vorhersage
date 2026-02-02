<?php
/**
 * Plugin Name: Wetter Vorhersage
 * Plugin URI: https://example.com
 * Description: Zeigt das aktuelle Wetter und die Vorhersage für die nächsten 3 Tage mit OpenWeatherMap API
 * Version: 2.0.0
 * Author: Ihr Name
 * Author URI: https://example.com
 * License: GPL v2 or later
 * Text Domain: wetter-vorhersage
 */

// Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================================
 * ADMIN-EINSTELLUNGEN & OPTIONS API
 * ============================================================================
 *
 * Das Plugin nutzt die WordPress Options API für sichere Konfiguration.
 * Alle Einstellungen werden in der Datenbank gespeichert (wp_options).
 *
 * Funktionsweise:
 * 1. wetter_get_option() - Lädt Optionen mit Fallback auf Defaults
 * 2. register_setting() - Registriert Settings mit Validierung
 * 3. add_options_page() - Erstellt Menüeintrag unter "Einstellungen"
 * 4. Sanitize-Callback - Validiert & säubert Benutzereingaben
 */

/**
 * Standard-Einstellungen definieren
 *
 * @return array Default-Werte für alle Plugin-Optionen
 */
function wetter_get_default_options() {
    return array(
        // API & Standort
        'api_key' => '',
        'latitude' => '53.822',
        'longitude' => '12.788',
        'location_name' => '',
        'forecast_days' => 3,
        'cache_duration' => 30,

        // Styling-Optionen (neu in v2.0)
        'text_color' => '#333333',
        'bg_color' => '#ffffff',
        'accent_color' => '#f7971e',
        'font_size' => 'small',          // small, normal, large
        'card_width' => 'medium',         // auto, small, medium, large
        'icon_style' => 'normal'          // light, normal, dark
    );
}

/**
 * Plugin-Option mit Fallback auf Default-Werte abrufen
 *
 * @param string $key Option-Schlüssel
 * @return mixed Option-Wert oder Default
 */
function wetter_get_option($key) {
    $options = get_option('wetter_vorhersage_options', array());
    $defaults = wetter_get_default_options();

    if (isset($options[$key])) {
        return $options[$key];
    }

    return isset($defaults[$key]) ? $defaults[$key] : null;
}

/**
 * Admin-Menü registrieren
 */
function wetter_admin_menu() {
    add_options_page(
        'Wetter Vorhersage Einstellungen',  // Seitentitel
        'Wetter',                            // Menütitel
        'manage_options',                    // Capability
        'wetter-vorhersage',                 // Slug
        'wetter_render_settings_page'        // Callback
    );
}
add_action('admin_menu', 'wetter_admin_menu');

/**
 * Settings registrieren
 */
function wetter_register_settings() {
    register_setting(
        'wetter_vorhersage_options_group',   // Option Group
        'wetter_vorhersage_options',         // Option Name
        'wetter_sanitize_options'            // Sanitize Callback
    );
}
add_action('admin_init', 'wetter_register_settings');

/**
 * Eingaben validieren und sanitieren
 *
 * @param array $input Rohe Benutzereingaben
 * @return array Bereinigte und validierte Daten
 */
function wetter_sanitize_options($input) {
    $sanitized = array();
    $defaults = wetter_get_default_options();

    // API-Key: Nur speichern wenn nicht leer oder Platzhalter
    if (isset($input['api_key'])) {
        $api_key = sanitize_text_field($input['api_key']);
        // Wenn Passwort-Platzhalter, alte Werte behalten
        if ($api_key !== '' && !preg_match('/^\*+$/', $api_key)) {
            $sanitized['api_key'] = $api_key;
        } else {
            // Alten Wert beibehalten
            $old_options = get_option('wetter_vorhersage_options', array());
            $sanitized['api_key'] = isset($old_options['api_key']) ? $old_options['api_key'] : '';
        }
    }

    // Latitude: Float zwischen -90 und 90
    if (isset($input['latitude'])) {
        $lat = floatval($input['latitude']);
        if ($lat >= -90 && $lat <= 90) {
            $sanitized['latitude'] = $lat;
        } else {
            add_settings_error(
                'wetter_vorhersage_options',
                'invalid_latitude',
                'Breitengrad muss zwischen -90 und 90 liegen.',
                'error'
            );
            $sanitized['latitude'] = $defaults['latitude'];
        }
    }

    // Longitude: Float zwischen -180 und 180
    if (isset($input['longitude'])) {
        $lon = floatval($input['longitude']);
        if ($lon >= -180 && $lon <= 180) {
            $sanitized['longitude'] = $lon;
        } else {
            add_settings_error(
                'wetter_vorhersage_options',
                'invalid_longitude',
                'Längengrad muss zwischen -180 und 180 liegen.',
                'error'
            );
            $sanitized['longitude'] = $defaults['longitude'];
        }
    }

    // Ortsname: Optional, freier Text
    if (isset($input['location_name'])) {
        $sanitized['location_name'] = sanitize_text_field($input['location_name']);
    }

    // Anzahl Vorschau-Tage: Integer zwischen 1 und 5
    if (isset($input['forecast_days'])) {
        $days = intval($input['forecast_days']);
        if ($days >= 1 && $days <= 5) {
            $sanitized['forecast_days'] = $days;
        } else {
            add_settings_error(
                'wetter_vorhersage_options',
                'invalid_forecast_days',
                'Anzahl Vorschau-Tage muss zwischen 1 und 5 liegen.',
                'error'
            );
            $sanitized['forecast_days'] = $defaults['forecast_days'];
        }
    }

    // Cache-Dauer: Integer zwischen 5 und 1440 Minuten (max. 24 Stunden)
    if (isset($input['cache_duration'])) {
        $duration = intval($input['cache_duration']);
        if ($duration >= 5 && $duration <= 1440) {
            $sanitized['cache_duration'] = $duration;
        } else {
            add_settings_error(
                'wetter_vorhersage_options',
                'invalid_cache_duration',
                'Cache-Dauer muss zwischen 5 und 1440 Minuten liegen.',
                'error'
            );
            $sanitized['cache_duration'] = $defaults['cache_duration'];
        }
    }

    // ========== STYLING-OPTIONEN (NEU IN V2.0) ==========

    // Textfarbe: Hex-Farbcode
    if (isset($input['text_color'])) {
        $color = sanitize_text_field($input['text_color']);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $sanitized['text_color'] = $color;
        } else {
            $sanitized['text_color'] = $defaults['text_color'];
        }
    }

    // Hintergrundfarbe: Hex-Farbcode
    if (isset($input['bg_color'])) {
        $color = sanitize_text_field($input['bg_color']);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $sanitized['bg_color'] = $color;
        } else {
            $sanitized['bg_color'] = $defaults['bg_color'];
        }
    }

    // Akzentfarbe: Hex-Farbcode
    if (isset($input['accent_color'])) {
        $color = sanitize_text_field($input['accent_color']);
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $sanitized['accent_color'] = $color;
        } else {
            $sanitized['accent_color'] = $defaults['accent_color'];
        }
    }

    // Schriftgröße: small, normal, large
    if (isset($input['font_size'])) {
        $size = sanitize_text_field($input['font_size']);
        if (in_array($size, array('small', 'normal', 'large'), true)) {
            $sanitized['font_size'] = $size;
        } else {
            $sanitized['font_size'] = $defaults['font_size'];
        }
    }

    // Kartenbreite: auto, small, medium, large
    if (isset($input['card_width'])) {
        $width = sanitize_text_field($input['card_width']);
        if (in_array($width, array('auto', 'small', 'medium', 'large'), true)) {
            $sanitized['card_width'] = $width;
        } else {
            $sanitized['card_width'] = $defaults['card_width'];
        }
    }

    // Icon-Style: light, normal, dark
    if (isset($input['icon_style'])) {
        $style = sanitize_text_field($input['icon_style']);
        if (in_array($style, array('light', 'normal', 'dark'), true)) {
            $sanitized['icon_style'] = $style;
        } else {
            $sanitized['icon_style'] = $defaults['icon_style'];
        }
    }

    // Erfolgs-Meldung
    add_settings_error(
        'wetter_vorhersage_options',
        'settings_updated',
        'Einstellungen erfolgreich gespeichert.',
        'success'
    );

    return $sanitized;
}

/**
 * Einstellungsseite rendern
 */
function wetter_render_settings_page() {
    // Berechtigungen prüfen
    if (!current_user_can('manage_options')) {
        wp_die('Sie haben keine ausreichenden Berechtigungen für diese Seite.');
    }

    $options = get_option('wetter_vorhersage_options', wetter_get_default_options());
    $api_key = isset($options['api_key']) ? $options['api_key'] : '';
    $has_api_key = !empty($api_key);
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <?php
        // Warnung anzeigen, wenn kein API-Key gesetzt ist
        if (!$has_api_key) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <strong>⚠️ Kein API-Key konfiguriert!</strong><br>
                    Das Plugin benötigt einen gültigen OpenWeatherMap API-Key, um zu funktionieren.<br>
                    <a href="https://openweathermap.org/api" target="_blank">Hier kostenlos registrieren</a>
                    und API-Key unten eintragen.
                </p>
            </div>
            <?php
        }
        ?>

        <?php settings_errors('wetter_vorhersage_options'); ?>

        <form method="post" action="options.php">
            <?php
            settings_fields('wetter_vorhersage_options_group');
            ?>

            <table class="form-table" role="presentation">
                <!-- API-Key -->
                <tr>
                    <th scope="row">
                        <label for="wetter_api_key">
                            OpenWeatherMap API-Key
                            <span style="color: red;">*</span>
                        </label>
                    </th>
                    <td>
                        <input
                            type="password"
                            id="wetter_api_key"
                            name="wetter_vorhersage_options[api_key]"
                            value="<?php echo $has_api_key ? '********************' : ''; ?>"
                            class="regular-text"
                            placeholder="Ihren API-Key hier eintragen"
                            autocomplete="off"
                        />
                        <p class="description">
                            Ihr API-Key wird verschlüsselt gespeichert und niemals im Klartext angezeigt.<br>
                            <a href="https://openweathermap.org/api" target="_blank">Kostenlosen API-Key hier erhalten</a>
                            (Free Plan: 1000 Aufrufe/Tag)
                            <?php if ($has_api_key): ?>
                                <br><span style="color: green;">✓ API-Key ist gesetzt</span>
                            <?php endif; ?>
                        </p>
                    </td>
                </tr>

                <!-- Latitude -->
                <tr>
                    <th scope="row">
                        <label for="wetter_latitude">
                            Breitengrad (Latitude)
                            <span style="color: red;">*</span>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="wetter_latitude"
                            name="wetter_vorhersage_options[latitude]"
                            value="<?php echo esc_attr($options['latitude']); ?>"
                            class="regular-text"
                            placeholder="z.B. 53.822"
                            step="any"
                        />
                        <p class="description">
                            Breitengrad zwischen -90 und 90.
                            <a href="https://www.latlong.net/" target="_blank">Koordinaten finden</a>
                            oder Google Maps nutzen (Rechtsklick → Koordinaten kopieren)
                        </p>
                    </td>
                </tr>

                <!-- Longitude -->
                <tr>
                    <th scope="row">
                        <label for="wetter_longitude">
                            Längengrad (Longitude)
                            <span style="color: red;">*</span>
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="wetter_longitude"
                            name="wetter_vorhersage_options[longitude]"
                            value="<?php echo esc_attr($options['longitude']); ?>"
                            class="regular-text"
                            placeholder="z.B. 12.788"
                            step="any"
                        />
                        <p class="description">
                            Längengrad zwischen -180 und 180.
                        </p>
                    </td>
                </tr>

                <!-- Ortsname (optional) -->
                <tr>
                    <th scope="row">
                        <label for="wetter_location_name">
                            Ortsname (optional)
                        </label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="wetter_location_name"
                            name="wetter_vorhersage_options[location_name]"
                            value="<?php echo esc_attr($options['location_name']); ?>"
                            class="regular-text"
                            placeholder="z.B. Peenestadt Neukalen"
                        />
                        <p class="description">
                            Benutzerdefinierter Ortsname für die Anzeige.<br>
                            Wenn leer, wird der Ortsname von OpenWeatherMap verwendet.
                        </p>
                    </td>
                </tr>

                <!-- Anzahl Vorschau-Tage -->
                <tr>
                    <th scope="row">
                        <label for="wetter_forecast_days">
                            Anzahl Vorschau-Tage
                        </label>
                    </th>
                    <td>
                        <input
                            type="number"
                            id="wetter_forecast_days"
                            name="wetter_vorhersage_options[forecast_days]"
                            value="<?php echo esc_attr($options['forecast_days']); ?>"
                            min="1"
                            max="5"
                            step="1"
                            class="small-text"
                        />
                        <p class="description">
                            Anzahl der Tage für die Vorhersage (zusätzlich zu heute). Standard: 3
                        </p>
                    </td>
                </tr>

                <!-- Cache-Dauer -->
                <tr>
                    <th scope="row">
                        <label for="wetter_cache_duration">
                            Cache-Dauer (Minuten)
                        </label>
                    </th>
                    <td>
                        <input
                            type="number"
                            id="wetter_cache_duration"
                            name="wetter_vorhersage_options[cache_duration]"
                            value="<?php echo esc_attr($options['cache_duration']); ?>"
                            min="5"
                            max="1440"
                            step="5"
                            class="small-text"
                        />
                        <p class="description">
                            Wie lange Wetterdaten zwischengespeichert werden (5-1440 Minuten). Standard: 30<br>
                            Längere Cache-Zeiten = weniger API-Aufrufe = schnellere Ladezeiten
                        </p>
                    </td>
                </tr>
            </table>

            <hr style="margin: 30px 0;">

            <!-- ========== DESIGN-EINSTELLUNGEN (NEU IN V2.0) ========== -->
            <h2>🎨 Design-Einstellungen</h2>
            <table class="form-table" role="presentation">
                <!-- Textfarbe -->
                <tr>
                    <th scope="row">
                        <label for="wetter_text_color">
                            Textfarbe
                        </label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="wetter_text_color"
                            name="wetter_vorhersage_options[text_color]"
                            value="<?php echo esc_attr($options['text_color']); ?>"
                            class="wetter-color-picker"
                        />
                        <input
                            type="text"
                            value="<?php echo esc_attr($options['text_color']); ?>"
                            class="regular-text wetter-color-text"
                            readonly
                        />
                        <p class="description">
                            Farbe für den Haupttext in der Wetterkarte.
                        </p>
                    </td>
                </tr>

                <!-- Hintergrundfarbe -->
                <tr>
                    <th scope="row">
                        <label for="wetter_bg_color">
                            Hintergrundfarbe
                        </label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="wetter_bg_color"
                            name="wetter_vorhersage_options[bg_color]"
                            value="<?php echo esc_attr($options['bg_color']); ?>"
                            class="wetter-color-picker"
                        />
                        <input
                            type="text"
                            value="<?php echo esc_attr($options['bg_color']); ?>"
                            class="regular-text wetter-color-text"
                            readonly
                        />
                        <p class="description">
                            Hintergrundfarbe der Wetterkarte.
                        </p>
                    </td>
                </tr>

                <!-- Akzentfarbe -->
                <tr>
                    <th scope="row">
                        <label for="wetter_accent_color">
                            Akzentfarbe
                        </label>
                    </th>
                    <td>
                        <input
                            type="color"
                            id="wetter_accent_color"
                            name="wetter_vorhersage_options[accent_color]"
                            value="<?php echo esc_attr($options['accent_color']); ?>"
                            class="wetter-color-picker"
                        />
                        <input
                            type="text"
                            value="<?php echo esc_attr($options['accent_color']); ?>"
                            class="regular-text wetter-color-text"
                            readonly
                        />
                        <p class="description">
                            Farbe für Ortsname, Icons und Links. Standard: Orange (#f7971e)
                        </p>
                    </td>
                </tr>

                <!-- Schriftgröße -->
                <tr>
                    <th scope="row">
                        <label for="wetter_font_size">
                            Schriftgröße
                        </label>
                    </th>
                    <td>
                        <select
                            id="wetter_font_size"
                            name="wetter_vorhersage_options[font_size]"
                            class="regular-text"
                        >
                            <option value="small" <?php selected($options['font_size'], 'small'); ?>>Klein</option>
                            <option value="normal" <?php selected($options['font_size'], 'normal'); ?>>Normal</option>
                            <option value="large" <?php selected($options['font_size'], 'large'); ?>>Groß</option>
                        </select>
                        <p class="description">
                            Grundschriftgröße für die gesamte Wetterkarte.
                        </p>
                    </td>
                </tr>

                <!-- Kartenbreite -->
                <tr>
                    <th scope="row">
                        <label for="wetter_card_width">
                            Kartenbreite
                        </label>
                    </th>
                    <td>
                        <select
                            id="wetter_card_width"
                            name="wetter_vorhersage_options[card_width]"
                            class="regular-text"
                        >
                            <option value="auto" <?php selected($options['card_width'], 'auto'); ?>>Automatisch (100% bis max. 600px)</option>
                            <option value="small" <?php selected($options['card_width'], 'small'); ?>>Klein (320px)</option>
                            <option value="medium" <?php selected($options['card_width'], 'medium'); ?>>Mittel (400px)</option>
                            <option value="large" <?php selected($options['card_width'], 'large'); ?>>Groß (500px)</option>
                        </select>
                        <p class="description">
                            Maximale Breite der Wetterkarte.
                        </p>
                    </td>
                </tr>

                <!-- Icon-Style -->
                <tr>
                    <th scope="row">
                        <label for="wetter_icon_style">
                            Icon-Helligkeit
                        </label>
                    </th>
                    <td>
                        <select
                            id="wetter_icon_style"
                            name="wetter_vorhersage_options[icon_style]"
                            class="regular-text"
                        >
                            <option value="light" <?php selected($options['icon_style'], 'light'); ?>>Hell (aufgehellt)</option>
                            <option value="normal" <?php selected($options['icon_style'], 'normal'); ?>>Normal</option>
                            <option value="dark" <?php selected($options['icon_style'], 'dark'); ?>>Dunkel (abgedunkelt)</option>
                        </select>
                        <p class="description">
                            Helligkeit der Wettersymbole. Nützlich bei dunklen Hintergründen.
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Einstellungen speichern'); ?>
        </form>

        <!-- Zusätzliche Informationen -->
        <hr>

        <h2>📋 Plugin-Verwendung</h2>
        <table class="widefat" style="max-width: 800px;">
            <tbody>
                <tr>
                    <td style="padding: 15px;">
                        <strong>Shortcode in Seiten/Beiträgen:</strong><br>
                        <code>[wetter_vorhersage]</code>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px; background: #f9f9f9;">
                        <strong>Shortcode in Templates (PHP):</strong><br>
                        <code>&lt;?php echo do_shortcode('[wetter_vorhersage]'); ?&gt;</code>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 15px;">
                        <strong>Cache manuell löschen:</strong><br>
                        <a href="<?php echo esc_url(admin_url('admin-post.php?action=wetter_clear_cache')); ?>"
                           class="button">
                            🗑️ Cache jetzt löschen
                        </a>
                        <span class="description">Löscht zwischengespeicherte Wetterdaten und erzwingt neue API-Abfrage</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h2>ℹ️ Weitere Informationen</h2>
        <ul>
            <li><strong>API-Limit (Free Plan):</strong> 1.000 Aufrufe pro Tag</li>
            <li><strong>Geschätzte Aufrufe:</strong> Bei 30 Min. Cache und 100 Seitenaufrufen/Tag ≈ 48 API-Aufrufe</li>
            <li><strong>Plugin-Version:</strong> 1.0.0</li>
            <li><strong>Datenquelle:</strong> <a href="https://openweathermap.org" target="_blank">OpenWeatherMap</a></li>
        </ul>
    </div>

    <style>
        .form-table th {
            width: 220px;
        }
        .notice.notice-success {
            border-left-color: #46b450;
        }
        .wetter-color-picker {
            width: 80px;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            vertical-align: middle;
            margin-right: 10px;
        }
        .wetter-color-text {
            max-width: 150px;
            font-family: monospace;
            vertical-align: middle;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Farb-Sync zwischen Color-Picker und Text-Input
            document.querySelectorAll('.wetter-color-picker').forEach(function(picker) {
                var textInput = picker.nextElementSibling;
                picker.addEventListener('input', function() {
                    if (textInput && textInput.classList.contains('wetter-color-text')) {
                        textInput.value = picker.value.toUpperCase();
                    }
                });
            });
        });
    </script>
    <?php
}

/**
 * Admin-Action zum manuellen Cache löschen
 */
function wetter_admin_clear_cache() {
    // Berechtigungen prüfen
    if (!current_user_can('manage_options')) {
        wp_die('Keine Berechtigung.');
    }

    // Cache löschen
    wetter_clear_cache();

    // Zurück zur Einstellungsseite mit Erfolgs-Nachricht
    wp_redirect(add_query_arg(
        array(
            'page' => 'wetter-vorhersage',
            'cache_cleared' => '1'
        ),
        admin_url('options-general.php')
    ));
    exit;
}
add_action('admin_post_wetter_clear_cache', 'wetter_admin_clear_cache');

/**
 * Erfolgs-Nachricht nach Cache-Löschung anzeigen
 */
function wetter_admin_notices() {
    if (isset($_GET['cache_cleared']) && $_GET['cache_cleared'] === '1') {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✓ Cache erfolgreich gelöscht!</strong> Die Wetterdaten werden beim nächsten Aufruf neu geladen.</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'wetter_admin_notices');

/**
 * Wetterdaten von OpenWeatherMap abrufen
 *
 * @return array|WP_Error Wetterdaten oder Fehler
 */
function wetter_get_forecast_data() {
    // Optionen laden
    $api_key = wetter_get_option('api_key');
    $latitude = wetter_get_option('latitude');
    $longitude = wetter_get_option('longitude');
    $cache_duration = wetter_get_option('cache_duration');

    // Cache-Dauer in Sekunden umrechnen
    $cache_seconds = intval($cache_duration) * 60;

    // Cache-Key für die Transient API
    $transient_key = 'wetter_forecast_data';

    // Versuche gecachte Daten zu laden
    $cached_data = get_transient($transient_key);
    if ($cached_data !== false) {
        return $cached_data;
    }

    // API-URL zusammenstellen
    $api_url = sprintf(
        'https://api.openweathermap.org/data/2.5/forecast?lat=%s&lon=%s&units=metric&lang=de&appid=%s',
        $latitude,
        $longitude,
        $api_key
    );

    // API-Anfrage mit WordPress HTTP API
    $response = wp_remote_get($api_url, array(
        'timeout' => 15,
        'sslverify' => true
    ));

    // Fehlerbehandlung
    if (is_wp_error($response)) {
        return new WP_Error('api_error', 'Verbindung zur Wetter-API fehlgeschlagen: ' . $response->get_error_message());
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        return new WP_Error('api_error', 'API-Fehler: HTTP ' . $response_code);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !isset($data['list'])) {
        return new WP_Error('parse_error', 'Ungültige API-Antwort');
    }

    // Daten im Cache speichern
    set_transient($transient_key, $data, $cache_seconds);

    return $data;
}

/**
 * Aktuelles Wetter extrahieren (erste Einträge = heute)
 *
 * @param array $forecast_data Rohdaten von der API
 * @return array|null Aktuelle Wetterdaten
 */
function wetter_get_current($forecast_data) {
    if (!isset($forecast_data['list']) || empty($forecast_data['list'])) {
        return null;
    }

    $first_item = $forecast_data['list'][0];

    // Min/Max für heute aus allen heutigen Einträgen ermitteln
    $today_date = date('Y-m-d');
    $today_temps = array();

    foreach ($forecast_data['list'] as $item) {
        if (date('Y-m-d', $item['dt']) === $today_date) {
            $today_temps[] = $item['main']['temp_min'];
            $today_temps[] = $item['main']['temp_max'];
        }
    }

    return array(
        'temp' => $first_item['main']['temp'],
        'temp_min' => !empty($today_temps) ? min($today_temps) : $first_item['main']['temp_min'],
        'temp_max' => !empty($today_temps) ? max($today_temps) : $first_item['main']['temp_max'],
        'description' => $first_item['weather'][0]['description'],
        'main' => $first_item['weather'][0]['main'],
        'icon' => $first_item['weather'][0]['icon'],
        'dt' => $first_item['dt']
    );
}

/**
 * Wetterdaten für die nächsten Tage gruppieren (ohne heute)
 *
 * @param array $forecast_data Rohdaten von der API
 * @return array Gruppierte Wetterdaten nach Tagen
 */
function wetter_get_next_days($forecast_data) {
    if (!isset($forecast_data['list'])) {
        return array();
    }

    // Anzahl Vorschau-Tage aus Optionen laden
    $forecast_days = intval(wetter_get_option('forecast_days'));

    $grouped = array();
    $today_date = date('Y-m-d');

    foreach ($forecast_data['list'] as $item) {
        $date = date('Y-m-d', $item['dt']);

        // Nur zukünftige Tage (nicht heute), maximal konfigurierte Anzahl
        if ($date > $today_date && !isset($grouped[$date]) && count($grouped) < $forecast_days) {
            $grouped[$date] = array(
                'date' => $item['dt'],
                'temps' => array($item['main']['temp']),
                'temp_min' => $item['main']['temp_min'],
                'temp_max' => $item['main']['temp_max'],
                'description' => $item['weather'][0]['description'],
                'icon' => $item['weather'][0]['icon'],
                'main' => $item['weather'][0]['main']
            );
        } elseif (isset($grouped[$date])) {
            // Min/Max-Temperaturen aktualisieren
            $grouped[$date]['temps'][] = $item['main']['temp'];
            $grouped[$date]['temp_min'] = min($grouped[$date]['temp_min'], $item['main']['temp_min']);
            $grouped[$date]['temp_max'] = max($grouped[$date]['temp_max'], $item['main']['temp_max']);
        }
    }

    return $grouped;
}

/**
 * Ortsnamen aus API-Daten oder Optionen extrahieren
 *
 * @param array $forecast_data Rohdaten von der API
 * @return string Ortsname
 */
function wetter_get_location_name($forecast_data) {
    // Benutzerdefinierten Ortsnamen prüfen
    $custom_name = wetter_get_option('location_name');
    if (!empty($custom_name)) {
        return $custom_name;
    }

    // Andernfalls von API verwenden
    if (isset($forecast_data['city']['name'])) {
        return $forecast_data['city']['name'];
    }

    return 'Unbekannter Ort';
}

/**
 * SVG-Icon für Wetterbedingungen erstellen
 *
 * @param string $weather_main Hauptwetterbedingung (Clear, Clouds, Rain, etc.)
 * @param string $icon OpenWeatherMap Icon-Code
 * @return string SVG-Code
 */
function wetter_get_icon_svg($weather_main, $icon) {
    $is_night = strpos($icon, 'n') !== false;

    switch ($weather_main) {
        case 'Clear':
            if ($is_night) {
                // Mond
                return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>';
            } else {
                // Sonne
                return '<svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="0.5"><circle cx="12" cy="12" r="4"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>';
            }
        case 'Clouds':
            // Wolken
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>';
        case 'Rain':
        case 'Drizzle':
            // Regen
            return '<svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path fill="currentColor" stroke="none" d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/><line x1="8" y1="19" x2="8" y2="21"/><line x1="11" y1="19" x2="11" y2="21"/><line x1="14" y1="19" x2="14" y2="21"/></svg>';
        case 'Snow':
            // Schnee
            return '<svg viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1"><path fill="currentColor" stroke="none" d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/><circle cx="8" cy="19" r="0.5"/><circle cx="11" cy="19" r="0.5"/><circle cx="14" cy="19" r="0.5"/><circle cx="9.5" cy="21" r="0.5"/><circle cx="12.5" cy="21" r="0.5"/></svg>';
        case 'Thunderstorm':
            // Gewitter
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/><path fill="#FFD700" d="M14 13h-3l2 5v-3h2l-2-5v3z"/></svg>';
        case 'Mist':
        case 'Fog':
        case 'Haze':
        case 'Smoke':
        case 'Dust':
            // Nebel
            return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="13" x2="21" y2="13"/><line x1="3" y1="17" x2="21" y2="17"/></svg>';
        default:
            // Standard: Wolke
            return '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04A7.49 7.49 0 0 0 12 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 0 0 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96z"/></svg>';
    }
}

/**
 * Shortcode-Handler für Wettervorhersage
 *
 * @param array $atts Shortcode-Attribute
 * @return string HTML-Ausgabe
 */
function wetter_vorhersage_shortcode($atts) {
    // API-Key prüfen
    $api_key = wetter_get_option('api_key');
    if (empty($api_key)) {
        return '<div class="wetter-error">
            Bitte konfigurieren Sie den OpenWeatherMap API-Key.<br>
            <a href="' . esc_url(admin_url('options-general.php?page=wetter-vorhersage')) . '">Zu den Einstellungen</a>
        </div>';
    }

    // Wetterdaten abrufen
    $forecast_data = wetter_get_forecast_data();

    if (is_wp_error($forecast_data)) {
        return '<div class="wetter-error">Fehler beim Laden der Wetterdaten: ' . esc_html($forecast_data->get_error_message()) . '</div>';
    }

    // Aktuelles Wetter und Ortsnamen extrahieren
    $current = wetter_get_current($forecast_data);
    $next_days = wetter_get_next_days($forecast_data);
    $location = wetter_get_location_name($forecast_data);

    if (!$current) {
        return '<div class="wetter-error">Keine Wetterdaten verfügbar.</div>';
    }

    // Styling-Optionen abrufen
    $font_size = wetter_get_option('font_size');
    $card_width = wetter_get_option('card_width');
    $icon_style = wetter_get_option('icon_style');

    // CSS-Klassen zusammenstellen
    $css_classes = array(
        'wetter-karte',
        'font-' . esc_attr($font_size),
        'width-' . esc_attr($card_width)
    );

    if ($icon_style !== 'normal') {
        $css_classes[] = 'icons-' . esc_attr($icon_style);
    }

    // HTML-Ausgabe erstellen
    ob_start();
    ?>
    <div class="<?php echo esc_attr(implode(' ', $css_classes)); ?>">
        <!-- TODAY CARD - Aktueller Tag (großer Block) -->
        <div class="wetter-heute-card">
            <h2 class="wetter-ort-gross"><?php echo esc_html(strtoupper($location)); ?></h2>

            <div class="wetter-heute-content">
                <div class="wetter-icon-heute">
                    <?php echo wetter_get_icon_svg($current['main'], $current['icon']); ?>
                </div>

                <div class="wetter-temp-heute">
                    <?php echo esc_html(round($current['temp'])); ?><span class="grad">°C</span>
                </div>

                <div class="wetter-beschreibung-heute">
                    <?php echo esc_html(ucfirst($current['description'])); ?>
                </div>
            </div>

            <div class="wetter-heute-meta">
                <div class="meta-datum">
                    <?php echo esc_html(date_i18n('l, j. F', $current['dt'])); ?>
                </div>
                <div class="meta-minmax">
                    <span class="temp-min"><?php echo esc_html(round($current['temp_min'])); ?>°</span>
                    <span class="separator">/</span>
                    <span class="temp-max"><?php echo esc_html(round($current['temp_max'])); ?>°</span>
                </div>
            </div>
        </div>

        <!-- VORSCHAU - Horizontale Leiste mit 3-5 Tagen -->
        <?php if (!empty($next_days)): ?>
            <div class="wetter-vorschau-leiste">
                <?php foreach ($next_days as $date => $day): ?>
                    <div class="vorschau-tag">
                        <div class="tag-label">
                            <?php echo esc_html(date_i18n('D', $day['date'])); ?>
                        </div>
                        <div class="tag-datum">
                            <?php echo esc_html(date_i18n('j.n.', $day['date'])); ?>
                        </div>
                        <div class="tag-icon">
                            <?php echo wetter_get_icon_svg($day['main'], $day['icon']); ?>
                        </div>
                        <div class="tag-temp">
                            <?php echo esc_html(round($day['temp_min'])); ?>° / <?php echo esc_html(round($day['temp_max'])); ?>°
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- FOOTER -->
        <div class="wetter-footer">
            <small>
                Daten von <a href="https://openweathermap.org/" target="_blank" rel="noopener">OpenWeatherMap</a>
                • Aktualisiert: <?php echo esc_html(date_i18n('H:i', current_time('timestamp'))); ?> Uhr
            </small>
        </div>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('wetter_vorhersage', 'wetter_vorhersage_shortcode');

/**
 * CSS-Styles für das Plugin laden
 */
function wetter_enqueue_styles() {
    global $post;

    // Prüfen ob Shortcode vorhanden ist
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wetter_vorhersage')) {
        // Externes CSS laden
        wp_enqueue_style(
            'wetter-vorhersage-styles',
            plugins_url('assets/weather.css', __FILE__),
            array(),
            '2.1.0'  // Sidebar-optimierte Version
        );

        // CSS-Variablen für Farben dynamisch generieren
        $custom_css = wetter_get_custom_css_vars();
        wp_add_inline_style('wetter-vorhersage-styles', $custom_css);
    }
}
add_action('wp_enqueue_scripts', 'wetter_enqueue_styles', 999);

/**
 * CSS-Variablen für Farben dynamisch generieren
 *
 * @return string CSS-Code mit Custom Properties
 */
function wetter_get_custom_css_vars() {
    $text_color = wetter_get_option('text_color');
    $bg_color = wetter_get_option('bg_color');
    $accent_color = wetter_get_option('accent_color');

    return "
        :root {
            --wetter-text-color: {$text_color};
            --wetter-bg-color: {$bg_color};
            --wetter-accent-color: {$accent_color};
        }
    ";
}

/**
 * Cache manuell löschen
 */
function wetter_clear_cache() {
    delete_transient('wetter_forecast_data');
}

/**
 * Plugin-Aktivierung: Standardwerte setzen, falls noch nicht vorhanden
 */
function wetter_activate_plugin() {
    $existing_options = get_option('wetter_vorhersage_options', false);

    // Nur setzen, wenn noch keine Optionen vorhanden
    if ($existing_options === false) {
        $defaults = wetter_get_default_options();

        // Wenn alte Konstanten vorhanden sind, diese migrieren
        if (defined('WETTER_API_KEY') && WETTER_API_KEY !== 'IHR_API_KEY_HIER') {
            $defaults['api_key'] = WETTER_API_KEY;
        }
        if (defined('WETTER_LATITUDE')) {
            $defaults['latitude'] = WETTER_LATITUDE;
        }
        if (defined('WETTER_LONGITUDE')) {
            $defaults['longitude'] = WETTER_LONGITUDE;
        }

        add_option('wetter_vorhersage_options', $defaults);
    }
}
register_activation_hook(__FILE__, 'wetter_activate_plugin');
