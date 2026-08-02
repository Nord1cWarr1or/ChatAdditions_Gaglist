<?php
require_once 'config.php';
require_auth();

$conn = db_connect();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM " . GAGS_TABLE . " WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$gag = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$gag) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $authid = trim($_POST['authid'] ?? '');
    $ip = trim($_POST['ip'] ?? '');
    $reason = trim($_POST['reason'] ?? '');
    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_authid = trim($_POST['admin_authid'] ?? '');
    $expire_at = trim($_POST['expire_at'] ?? '');
    $flags = 0;
    if (!empty($_POST['flag_a'])) $flags |= 1;
    if (!empty($_POST['flag_b'])) $flags |= 2;
    if (!empty($_POST['flag_c'])) $flags |= 4;
    
    if ($name === '' || $authid === '' || $reason === '') {
        $error = 'Заполните обязательные поля';
    } elseif ($expire_at !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $expire_at)) {
        $error = 'Неверный формат даты';
    } else {
        if ($expire_at === '') {
            $expire_at = '2286-11-20 17:46:39';
        }

        $stmt = $conn->prepare("UPDATE " . GAGS_TABLE . " SET
            name = ?, authid = ?, ip = ?, reason = ?,
            admin_name = ?, admin_authid = ?, expire_at = ?, flags = ?
            WHERE id = ?");
        $stmt->bind_param('sssssssii', $name, $authid, $ip, $reason, $admin_name, $admin_authid, $expire_at, $flags, $id);

        if ($stmt->execute()) {
            $success = 'Gag успешно обновлён';
            $gag['name'] = $name;
            $gag['authid'] = $authid;
            $gag['ip'] = $ip;
            $gag['reason'] = $reason;
            $gag['admin_name'] = $admin_name;
            $gag['admin_authid'] = $admin_authid;
            $gag['expire_at'] = $expire_at;
            $gag['flags'] = $flags;
        } else {
            $error = 'Ошибка при обновлении: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование — Gag List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1><a href="index.php" style="text-decoration:none;color:inherit;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:8px;"><path d="M3 11l18-5v12L3 13v-2z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                Gag <span>List</span>
            </a></h1>
            <div class="nav-links">
                <button class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">
                    <svg class="icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <a href="index.php">← Назад</a>
                <a href="logout.php" class="btn-logout">Выйти</a>
            </div>
        </header>

        <div class="edit-wrapper">
            <div class="edit-box">
                <h2>Редактирование gag #<?= $id ?></h2>
                
                <?php if ($success): ?>
                    <div class="error-msg" style="background:#d4edda;color:#155724;"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Никнейм игрока *</label>
                            <input type="text" name="name" value="<?= htmlspecialchars(fix_encoding($gag['name'])) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Steam ID *</label>
                            <input type="text" name="authid" value="<?= htmlspecialchars($gag['authid']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>IP игрока</label>
                            <input type="text" name="ip" value="<?= htmlspecialchars($gag['ip']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Флаги</label>
                            <div style="display:flex;gap:16px;margin-top:6px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_a" value="1" <?= (intval($gag['flags']) & 1) ? 'checked' : '' ?>>
                                    a — Текстовый чат
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_b" value="2" <?= (intval($gag['flags']) & 2) ? 'checked' : '' ?>>
                                    b — Командный чат
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_c" value="4" <?= (intval($gag['flags']) & 4) ? 'checked' : '' ?>>
                                    c — Голосовой чат
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Причина *</label>
                        <textarea name="reason" required><?= htmlspecialchars(fix_encoding($gag['reason'])) ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Ник админа</label>
                            <input type="text" name="admin_name" value="<?= htmlspecialchars(fix_encoding($gag['admin_name'])) ?>">
                        </div>
                        <div class="form-group">
                            <label>Steam ID админа</label>
                            <input type="text" name="admin_authid" value="<?= htmlspecialchars($gag['admin_authid']) ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Дата окончания (оставьте пустым для бессрочного)</label>
                        <input type="datetime-local" name="expire_at" 
                               value="<?= $gag['expire_at'] !== '2286-11-20 17:46:39' ? date('Y-m-d\TH:i', strtotime($gag['expire_at'])) : '' ?>">
                    </div>
                    
                    <div class="form-actions">
                        <a href="index.php" class="btn-cancel">Отмена</a>
                        <button type="submit" class="btn-save">Сохранить</button>
                    </div>
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
