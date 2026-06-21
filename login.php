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
        $error = 'Слишком много попыток. Попробуйте через 15 минут.';
    } else {
        $login = $_POST['login'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
            $_SESSION['logged_in'] = true;
            session_regenerate_id(true);
            if (file_exists($rate_file)) unlink($rate_file);
            header('Location: index.php');
            exit;
        } else {
            $attempts[] = $now;
            file_put_contents($rate_file, json_encode($attempts));
            $error = 'Неверный логин или пароль';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Gag List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><a href="index.php" style="text-decoration:none;color:inherit;">🔇 Gag <span>List</span></a></h1>
            <div class="nav-links">
                <button class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">☀️</button>
                <a href="index.php">← Назад</a>
            </div>
        </header>

        <div class="login-wrapper">
            <div class="login-box">
                <h2>Вход в панель</h2>
                
                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-group">
                        <label>Логин</label>
                        <input type="text" name="login" required autofocus>
                    </div>
                    <div class="form-group">
                        <label>Пароль</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn-submit">Войти</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    function getTheme() { return localStorage.getItem('theme') || 'light'; }
    function applyTheme(theme) {
        document.body.classList.toggle('dark', theme === 'dark');
        document.querySelector('.theme-toggle').textContent = theme === 'dark' ? '🌙' : '☀️';
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
