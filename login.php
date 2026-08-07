<?php
require_once 'config.php';

$error = '';
$rate_limit_max = 5;
$rate_limit_window = 900;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'];
    $rate_file = sys_get_temp_dir() . '/login_rate_' . md5($ip);
    $attempts = [];
    if (file_exists($rate_file)) {
        $attempts = json_decode(file_get_contents($rate_file), true) ?: [];
    }
    $now = time();
    $attempts = array_filter($attempts, fn($t) => $t > $now - $rate_limit_window);

    if (count($attempts) >= $rate_limit_max) {
        $error = str('login_rate_limit');
    } else {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
            $_SESSION['logged_in'] = true;
            session_regenerate_id(true);
            if (file_exists($rate_file)) unlink($rate_file);
            header('Location: index.php' . lang_url());
            exit;
        } else {
            $attempts[] = $now;
            file_put_contents($rate_file, json_encode($attempts));
            $error = str('login_error');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= str('login_title') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><a href="index.php" style="text-decoration:none;color:inherit;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:8px;"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                <?= str('nav_title') ?>
            </a></h1>
            <div class="nav-links">
                <button class="theme-toggle" onclick="toggleTheme()" title="<?= str('nav_theme_toggle') ?>">
                    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <a href="<?= '?' . http_build_query(array_merge(array_filter($_GET, fn($k) => $k !== 'lang', ARRAY_FILTER_USE_KEY), ['lang' => current_lang() === 'ru' ? 'en' : 'ru'])) ?>" class="btn-lang" title="<?= str('nav_language') ?>"><?= str('nav_language') ?></a>
                <a href="index.php<?= lang_url() ?>"><?= str('nav_back') ?></a>
            </div>
        </header>

        <div class="login-wrapper">
            <div class="login-box">
                <h2><?= str('login_heading') ?></h2>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <input type="hidden" name="lang" value="<?= current_lang() ?>">
                    <div class="form-group">
                        <label><?= str('login_login') ?></label>
                        <input type="text" name="login" required autofocus>
                    </div>
                    <div class="form-group">
                        <label><?= str('login_password') ?></label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-submit"><?= str('login_submit') ?></button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function getTheme() { return localStorage.getItem('theme') || 'light'; }
    function applyTheme(theme) {
        document.body.classList.toggle('dark', theme === 'dark');
        const btn = document.querySelector('.theme-toggle');
        if (btn) {
            const sun = btn.querySelector('.icon-sun');
            const moon = btn.querySelector('.icon-moon');
            if (theme === 'dark') {
                if (sun) sun.style.display = 'none';
                if (moon) moon.style.display = 'block';
            } else {
                if (sun) sun.style.display = 'block';
                if (moon) moon.style.display = 'none';
            }
        }
    }
    function toggleTheme() {
        const next = getTheme() === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme', next);
        applyTheme(next);
    }
    applyTheme(getTheme());
    </script>
</body>
</html>
