<?php
/**
 * Theme management for ChatAdditions Gag List.
 *
 * get_themes() — scans themes/ directory, returns array of theme IDs
 * current_theme() — reads $_GET['theme'] > $_COOKIE['theme'] > default 'default'
 * theme_url($overrides) — builds URL preserving current theme + language
 * load_theme_css() — outputs <link> tag for current theme CSS
 */

function get_themes() {
    static $themes = null;
    if ($themes !== null) return $themes;

    $themes = [];
    $dir = __DIR__ . '/themes';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.css') as $file) {
            $id = basename($file, '.css');
            $themes[$id] = [
                'name' => ucfirst($id),
                'file' => 'themes/' . basename($file),
            ];
        }
    }
    if (empty($themes)) {
        $themes['default'] = ['name' => 'Default', 'file' => 'style.css'];
    }
    // Ensure default is first
    if (isset($themes['default'])) {
        $default = $themes['default'];
        unset($themes['default']);
        ksort($themes);
        $themes = ['default' => $default] + $themes;
    }
    return $themes;
}

function current_theme() {
    if (isset($_GET['theme'])) {
        return $_GET['theme'];
    }
    if (isset($_SESSION['theme'])) return $_SESSION['theme'];
    if (isset($_COOKIE['theme'])) {
        $_SESSION['theme'] = $_COOKIE['theme'];
        return $_COOKIE['theme'];
    }
    return 'default';
}

function init_theme() {
    if (isset($_GET['theme'])) {
        $theme = $_GET['theme'];
        $_SESSION['theme'] = $theme;
        if (headers_sent()) {
            // Can't set cookie, store in session only
        } else {
            setcookie('theme', $theme, time() + 86400 * 365, '/');
        }
    }
}

function theme_url($overrides = []) {
    $params = $_GET;
    unset($params['theme']);
    $theme_explicit = false;
    foreach ($overrides as $k => $v) {
        if ($v === false) {
            unset($params[$k]);
            unset($overrides[$k]);
            if ($k === 'theme') $theme_explicit = true;
        }
    }
    $params = array_merge($params, $overrides);
    if (isset($overrides['theme'])) {
        $params['theme'] = $overrides['theme'];
    } elseif (!$theme_explicit) {
        $theme = current_theme();
        if ($theme !== 'default') $params['theme'] = $theme;
    }
    // Preserve lang
    if (function_exists('current_lang')) {
        $lang = current_lang();
        if ($lang !== 'ru') $params['lang'] = $lang;
    }
    return $params ? '?' . http_build_query($params) : '';
}

function load_theme_css() {
    $themes = get_themes();
    $current = current_theme();
    if (isset($themes[$current])) {
        echo '<link rel="stylesheet" href="' . htmlspecialchars($themes[$current]['file']) . '">';
    }
}

function theme_name($id) {
    $names = [
        'default' => 'Default',
        'forest' => 'Forest',
        'sunset' => 'Sunset',
        'royal' => 'Royal',
        'rose' => 'Rose',
        'ocean' => 'Ocean',
        'crimson' => 'Crimson',
        'lavender' => 'Lavender',
        'amber' => 'Amber',
        'mint' => 'Mint',
    ];
    return $names[$id] ?? ucfirst($id);
}
