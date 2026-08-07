<?php
/**
 * Localization for ChatAdditions Gag List.
 *
 * Usage: include lang.php after config.php. Call str('key') to get translated
 * string. Call lang_url($params) to build URL preserving current language.
 */

$translations = [
    'ru' => [
        // Navigation
        'nav_title'             => 'Gag <span>List</span>',
        'nav_theme_toggle'      => 'Переключить тему',
        'nav_add'               => '+ Добавить gag',
        'nav_logout'            => 'Выйти',
        'nav_login'             => 'Войти',
        'nav_back'              => '← Назад',
        'nav_language'          => 'EN',
        'nav_next'              => 'Далее →',

        // Index page
        'index_title'           => 'Gag List — CS 1.6',
        'index_search_placeholder' => 'Поиск по нику, Steam ID или IP...',
        'index_search_btn'      => 'Найти',
        'index_filter_all'      => 'Все',
        'index_filter_active'   => 'Активные',
        'index_stat_total'      => 'Всего: %d',
        'index_stat_active'     => 'Активных: %d',
        'index_stat_expired'    => 'Истёкших: %d',
        'index_th_num'          => '#',
        'index_th_player'       => 'Игрок',
        'index_th_steamid'      => 'Steam ID',
        'index_th_ip'           => 'IP',
        'index_th_reason'       => 'Причина',
        'index_th_admin'        => 'Админ',
        'index_th_term'         => 'Срок',
        'index_th_status'       => 'Статус',
        'index_th_actions'      => 'Действия',
        'index_empty'           => 'Наказания не найдены',
        'index_status_permanent' => 'Навсегда',
        'index_status_active'   => 'Активен',
        'index_status_expired'  => 'Истёк',
        'index_delete_confirm'  => 'Удалить этот gag?',
        'index_delete_tooltip'  => 'Удалить',
        'index_permanent_symbol' => '∞',
        'index_permanent_range' => '(%s — ∞)',

        // Duration
        'dur_year'              => '%d год',
        'dur_years'             => '%d года',
        'dur_years_many'        => '%d лет',
        'dur_month'             => '%d месяц',
        'dur_months'            => '%d месяца',
        'dur_months_many'       => '%d месяцев',
        'dur_week'              => '%d неделя',
        'dur_weeks'             => '%d недели',
        'dur_weeks_many'        => '%d недель',
        'dur_day'               => '%d день',
        'dur_days'              => '%d дня',
        'dur_days_many'         => '%d дней',
        'dur_hour'              => '%d ч.',
        'dur_min'               => '%d мин.',
        'dur_sec'               => '%d сек.',

        // Login page
        'login_title'           => 'Вход — Gag List',
        'login_heading'         => 'Вход в панель',
        'login_login'           => 'Логин',
        'login_password'        => 'Пароль',
        'login_submit'          => 'Войти',
        'login_error'           => 'Неверный логин или пароль',
        'login_rate_limit'      => 'Слишком много попыток. Попробуйте через 15 минут.',

        // Create page
        'create_title'          => 'Новый gag — Gag List',
        'create_heading'        => 'Новый gag',
        'create_field_name'     => 'Никнейм игрока *',
        'create_field_authid'   => 'Steam ID *',
        'create_field_ip'       => 'IP игрока',
        'create_field_flags'    => 'Флаги',
        'create_field_reason'   => 'Причина *',
        'create_field_admin_name' => 'Ник админа',
        'create_field_admin_authid' => 'Steam ID админа',
        'create_field_expire'   => 'Дата окончания (оставьте пустым для бессрочного)',
        'create_flag_a'         => 'a — Текстовый чат',
        'create_flag_b'         => 'b — Командный чат',
        'create_flag_c'         => 'c — Голосовой чат',
        'create_submit'         => 'Создать',
        'create_cancel'         => 'Отмена',
        'create_error_empty'    => 'Заполните обязательные поля',
        'create_error_date'     => 'Неверный формат даты',
        'create_error_db'       => 'Ошибка при создании: %s',

        // Edit page
        'edit_title'            => 'Редактирование — Gag List',
        'edit_heading'          => 'Редактирование gag #%d',
        'edit_field_name'       => 'Никнейм игрока *',
        'edit_field_authid'     => 'Steam ID *',
        'edit_field_ip'         => 'IP игрока',
        'edit_field_flags'      => 'Флаги',
        'edit_field_reason'     => 'Причина *',
        'edit_field_admin_name' => 'Ник админа',
        'edit_field_admin_authid' => 'Steam ID админа',
        'edit_field_expire'     => 'Дата окончания (оставьте пустым для бессрочного)',
        'edit_flag_a'           => 'a — Текстовый чат',
        'edit_flag_b'           => 'b — Командный чат',
        'edit_flag_c'           => 'c — Голосовой чат',
        'edit_submit'           => 'Сохранить',
        'edit_cancel'           => 'Отмена',
        'edit_error_empty'      => 'Заполните обязательные поля',
        'edit_error_date'       => 'Неверный формат даты',
        'edit_error_db'         => 'Ошибка при обновлении: %s',
        'edit_success'          => 'Gag успешно обновлён',

        // CSRF / errors
        'error_csrf'            => 'CSRF token mismatch',
        'error_db_connect'      => 'Ошибка подключения к БД: %s',
    ],

    'en' => [
        // Navigation
        'nav_title'             => 'Gag <span>List</span>',
        'nav_theme_toggle'      => 'Toggle theme',
        'nav_add'               => '+ Add gag',
        'nav_logout'            => 'Logout',
        'nav_login'             => 'Login',
        'nav_back'              => '← Back',
        'nav_language'          => 'RU',
        'nav_next'              => 'Next →',

        // Index page
        'index_title'           => 'Gag List — CS 1.6',
        'index_search_placeholder' => 'Search by name, Steam ID or IP...',
        'index_search_btn'      => 'Search',
        'index_filter_all'      => 'All',
        'index_filter_active'   => 'Active',
        'index_stat_total'      => 'Total: %d',
        'index_stat_active'     => 'Active: %d',
        'index_stat_expired'    => 'Expired: %d',
        'index_th_num'          => '#',
        'index_th_player'       => 'Player',
        'index_th_steamid'      => 'Steam ID',
        'index_th_ip'           => 'IP',
        'index_th_reason'       => 'Reason',
        'index_th_admin'        => 'Admin',
        'index_th_term'         => 'Term',
        'index_th_status'       => 'Status',
        'index_th_actions'      => 'Actions',
        'index_empty'           => 'No punishments found',
        'index_status_permanent' => 'Permanent',
        'index_status_active'   => 'Active',
        'index_status_expired'  => 'Expired',
        'index_delete_confirm'  => 'Delete this gag?',
        'index_delete_tooltip'  => 'Delete',
        'index_permanent_symbol' => '∞',
        'index_permanent_range' => '(%s — ∞)',

        // Duration
        'dur_year'              => '%d year',
        'dur_years'             => '%d years',
        'dur_years_many'        => '%d years',
        'dur_month'             => '%d month',
        'dur_months'            => '%d months',
        'dur_months_many'       => '%d months',
        'dur_week'              => '%d week',
        'dur_weeks'             => '%d weeks',
        'dur_weeks_many'        => '%d weeks',
        'dur_day'               => '%d day',
        'dur_days'              => '%d days',
        'dur_days_many'         => '%d days',
        'dur_hour'              => '%d hr.',
        'dur_min'               => '%d min.',
        'dur_sec'               => '%d sec.',

        // Login page
        'login_title'           => 'Login — Gag List',
        'login_heading'         => 'Panel Login',
        'login_login'           => 'Login',
        'login_password'        => 'Password',
        'login_submit'          => 'Login',
        'login_error'           => 'Invalid login or password',
        'login_rate_limit'      => 'Too many attempts. Try again in 15 minutes.',

        // Create page
        'create_title'          => 'New gag — Gag List',
        'create_heading'        => 'New gag',
        'create_field_name'     => 'Player name *',
        'create_field_authid'   => 'Steam ID *',
        'create_field_ip'       => 'Player IP',
        'create_field_flags'    => 'Flags',
        'create_field_reason'   => 'Reason *',
        'create_field_admin_name' => 'Admin name',
        'create_field_admin_authid' => 'Admin Steam ID',
        'create_field_expire'   => 'Expiry date (leave empty for permanent)',
        'create_flag_a'         => 'a — Text chat',
        'create_flag_b'         => 'b — Command chat',
        'create_flag_c'         => 'c — Voice chat',
        'create_submit'         => 'Create',
        'create_cancel'         => 'Cancel',
        'create_error_empty'    => 'Fill in required fields',
        'create_error_date'     => 'Invalid date format',
        'create_error_db'       => 'Creation error: %s',

        // Edit page
        'edit_title'            => 'Edit — Gag List',
        'edit_heading'          => 'Edit gag #%d',
        'edit_field_name'       => 'Player name *',
        'edit_field_authid'     => 'Steam ID *',
        'edit_field_ip'         => 'Player IP',
        'edit_field_flags'      => 'Flags',
        'edit_field_reason'     => 'Reason *',
        'edit_field_admin_name' => 'Admin name',
        'edit_field_admin_authid' => 'Admin Steam ID',
        'edit_field_expire'     => 'Expiry date (leave empty for permanent)',
        'edit_flag_a'           => 'a — Text chat',
        'edit_flag_b'           => 'b — Command chat',
        'edit_flag_c'           => 'c — Voice chat',
        'edit_submit'           => 'Save',
        'edit_cancel'           => 'Cancel',
        'edit_error_empty'      => 'Fill in required fields',
        'edit_error_date'       => 'Invalid date format',
        'edit_error_db'         => 'Update error: %s',
        'edit_success'          => 'Gag updated successfully',

        // CSRF / errors
        'error_csrf'            => 'CSRF token mismatch',
        'error_db_connect'      => 'Database connection error: %s',
    ],
];

// --- Language detection ---

function detect_language() {
    // 1. URL parameter
    if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $GLOBALS['translations'])) {
        $lang = $_GET['lang'];
        $_SESSION['lang'] = $lang;
        setcookie('lang', $lang, time() + 86400 * 365, '/');
        return $lang;
    }
    // 2. Session
    if (isset($_SESSION['lang']) && array_key_exists($_SESSION['lang'], $GLOBALS['translations'])) {
        return $_SESSION['lang'];
    }
    // 3. Cookie
    if (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], $GLOBALS['translations'])) {
        $_SESSION['lang'] = $_COOKIE['lang'];
        return $_COOKIE['lang'];
    }
    // 4. Browser
    $accept = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (preg_match('/^(en)/i', $accept)) {
        return 'en';
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
    global $translations, $CURRENT_LANG;
    $value = $translations[$CURRENT_LANG][$key] ?? $translations['ru'][$key] ?? $key;
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
        if ($overrides['lang'] === 'ru') {
            unset($params['lang']);
        }
    } else {
        $lang = current_lang();
        if ($lang !== 'ru') {
            $params['lang'] = $lang;
        }
    }
    return $params ? '?' . http_build_query($params) : '';
}
