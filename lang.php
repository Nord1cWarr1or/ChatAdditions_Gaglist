<?php
/**
 * Localization for ChatAdditions Gag List.
 *
 * Loads translations from lang.json. Usage: str('key') for translated string.
 * lang_url($params) builds URL preserving current language.
 */

/**
 * Load and cache all translations from lang.json.
 * Returns [meta => [...], 'ru' => [...], 'en' => [...], ...]
 */
function load_translations() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    $json_path = __DIR__ . '/lang.json';
    if (!file_exists($json_path)) {
        // Fallback: minimal Russian translations
        $cache = [
            'meta' => ['ru' => ['name' => 'Русский', 'flag' => '🇷🇺']],
            'ru' => ['nav_title' => 'Gag <span>List</span>'],
        ];
        return $cache;
    }

    $json = file_get_contents($json_path);
    $data = json_decode($json, true);
    if (!is_array($data) || !isset($data['meta'])) {
        $cache = [
            'meta' => ['ru' => ['name' => 'Русский', 'flag' => '🇷🇺']],
            'ru' => ['nav_title' => 'Gag <span>List</span>'],
        ];
        return $cache;
    }

    $cache = $data;
    return $cache;
}

/**
 * Get available languages with metadata.
 * Returns ['ru' => ['name' => 'Русский', 'flag' => '🇷🇺'], ...]
 */
function get_languages() {
    $data = load_translations();
    return $data['meta'] ?? [];
}

// --- Language detection ---

function detect_language() {
    $data = load_translations();
    $available = array_keys($data['meta'] ?? []);

    // 1. URL parameter
    if (isset($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $lang = $_GET['lang'];
        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, time() + 86400 * 365, '/');
        return $lang;
    }
    // 2. Session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], $available, true)) {
        return $_SESSION['lang'];
    }
    // 3. Cookie
    if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $available, true)) {
        $_SESSION['lang'] = $_COOKIE['lang'];
        return $_COOKIE['lang'];
    }
    // 4. Browser Accept-Language — find first match from available languages
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if ($accept) {
        // Parse Accept-Language: "en-US,en;q=0.9,ru;q=0.8"
        $ranges = explode(',', $accept);
        foreach ($ranges as $range) {
            $parts = explode(';', trim($range));
            $lang_code = strtolower(substr(trim($parts[0]), 0, 2));
            if (in_array($lang_code, $available, true)) {
                return $lang_code;
            }
        }
    }
    // 5. Default
    return 'ru';
}

$CURRENT_LANG = detect_language();

/**
 * Get translated string by key. Supports sprintf formatting.
 * Usage: str('key') or str('key', $arg1, $arg2)
 */
function str($key, ...$args) {
    $data = load_translations();
    $lang = current_lang();
    $value = $data[$lang][$key] ?? $data['ru'][$key] ?? $key;
    if ($args) {
        $value = vsprintf($value, $args);
    }
    return $value;
}

/**
 * Get current language code.
 */
function current_lang() {
    global $CURRENT_LANG;
    return $CURRENT_LANG;
}

/**
 * Get current language name (from meta).
 */
function current_lang_name() {
    $data = load_translations();
    $meta = $data['meta'][current_lang()] ?? null;
    return $meta['name'] ?? current_lang();
}

/**
 * Get current language flag emoji.
 */
function current_lang_flag() {
    $data = load_translations();
    $meta = $data['meta'][current_lang()] ?? null;
    return $meta['flag'] ?? '🌐';
}

/**
 * Build URL preserving current language and other query params.
 * Usage: lang_url(['page' => 2]) => "?lang=en&page=2"
 */
function lang_url($overrides = []) {
    $params = $_GET;
    unset($params['lang']);
    foreach ($overrides as $k => $v) {
        if ($v === false) {
            unset($params[$k]);
            unset($overrides[$k]);
        }
    }
    $params = array_merge($params, $overrides);
    // If lang was explicitly passed in overrides, use it; otherwise preserve current
    if (isset($overrides['lang'])) {
        $params['lang'] = $overrides['lang'];
    } else {
        $lang = current_lang();
        if ($lang !== 'ru') {
            $params['lang'] = $lang;
        }
    }
    return $params ? '?' . http_build_query($params) : '';
}
