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
    <?php load_theme_css(); ?>
    <?php load_dark_inline_style(); ?>
    <script>
    (function(){
        var d=localStorage.getItem('darkMode');
        var isDark=d==='true'||(d===null&&document.cookie.includes('darkMode=1'));
        if(isDark){document.documentElement.classList.add('dark');document.body.classList.add('dark');}
        if(!document.cookie.includes('darkMode=')){document.cookie='darkMode='+(isDark?'1':'0')+';path=/;max-age=31536000';}
        var t=localStorage.getItem('theme')||'default';
        var link=document.getElementById('theme-css');
        var themes=<?= json_encode(array_map(function($t){return $t['file'];},get_themes())) ?>;
        if(link&&themes[t])link.href=themes[t];
        if(!document.cookie.includes('theme=')){document.cookie='theme='+t+';path=/;max-age=31536000';}
    })();
    </script>
</head>
<body class="<?= ($_COOKIE['darkMode'] ?? '') === '1' ? 'dark' : '' ?>">
<body>
    <div class="container">
        <header>
            <h1><a href="index.php" style="text-decoration:none;color:inherit;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:8px;"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                <?= str('nav_title') ?>
            </a></h1>
            <div class="nav-links">
                <div class="lang-dropdown">
                    <button class="btn-lang" onclick="toggleLangDropdown()"><?= current_lang_flag() ?> <?= current_lang_name() ?> ▾</button>
                    <div class="lang-dropdown-menu" id="langMenu">
                        <?php foreach (get_languages() as $code => $meta): ?>
                            <a href="<?= lang_url(['lang' => $code]) ?>" class="<?= $code === current_lang() ? 'active' : '' ?>"><?= $meta['flag'] ?> <?= $meta['name'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
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

    <?php include 'footer.php'; ?>
</body>
</html>
