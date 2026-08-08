<?php
/**
 * Theme management for ChatAdditions Gag List.
 *
 * get_themes() — scans themes/ directory, returns array of theme IDs
 * load_theme_css() — outputs <link> tag for default theme CSS (JS swaps via localStorage)
 * theme_name() — returns human-readable theme name
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

function load_theme_css() {
    $themes = get_themes();
    $theme = $_COOKIE['theme'] ?? 'default';
    if (!isset($themes[$theme])) $theme = 'default';
    echo '<link rel="stylesheet" href="' . htmlspecialchars($themes[$theme]['file']) . '" id="theme-css">';
}

function load_dark_inline_style() {
    $themes = get_themes();
    $theme = $_COOKIE['theme'] ?? 'default';
    if (!isset($themes[$theme])) $theme = 'default';
    $file = __DIR__ . '/' . $themes[$theme]['file'];
    if (!file_exists($file)) return;
    $css = file_get_contents($file);
    if (preg_match('/html\.dark[^{]*\{([^}]+)\}/s', $css, $m)) {
        echo '<style data-dark-inline>html.dark,html.dark body{' . $m[1] . '}</style>';
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
