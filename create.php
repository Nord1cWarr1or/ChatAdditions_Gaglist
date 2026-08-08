<?php
require_once 'config.php';
require_auth();

$conn = db_connect();

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
        $error = str('create_error_empty');
    } elseif ($expire_at !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $expire_at)) {
        $error = str('create_error_date');
    } else {
        if ($expire_at === '') {
            $expire_at = '2286-11-20 17:46:39';
        }

        $stmt = $conn->prepare("INSERT INTO " . GAGS_TABLE . "
            (name, authid, ip, reason, admin_name, admin_authid, admin_ip, expire_at, flags, created_at)
            VALUES (?, ?, ?, ?, ?, ?, '0.0.0.0', ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
            name = VALUES(name), ip = VALUES(ip), reason = VALUES(reason),
            admin_name = VALUES(admin_name), admin_authid = VALUES(admin_authid),
            admin_ip = VALUES(admin_ip), expire_at = VALUES(expire_at),
            flags = VALUES(flags), created_at = NOW()");
        $stmt->bind_param('sssssssi', $name, $authid, $ip, $reason, $admin_name, $admin_authid, $expire_at, $flags);

        if ($stmt->execute()) {
            header('Location: index.php' . lang_url());
            exit;
        } else {
            $error = sprintf(str('create_error_db'), $conn->error);
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
    <title><?= str('create_title') ?></title>
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
                <a href="logout.php<?= lang_url() ?>" class="btn-logout"><?= str('nav_logout') ?></a>
            </div>
        </header>

        <div class="edit-wrapper">
            <div class="edit-box">
                <h2><?= str('create_heading') ?></h2>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="lang" value="<?= current_lang() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('create_field_name') ?></label>
                            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= str('create_field_authid') ?></label>
                            <input type="text" name="authid" value="<?= htmlspecialchars($_POST['authid'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('create_field_ip') ?></label>
                            <input type="text" name="ip" value="<?= htmlspecialchars($_POST['ip'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= str('create_field_flags') ?></label>
                            <div style="display:flex;gap:16px;margin-top:6px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_a" value="1" checked>
                                    <?= str('create_flag_a') ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_b" value="2" checked>
                                    <?= str('create_flag_b') ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_c" value="4" checked>
                                    <?= str('create_flag_c') ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= str('create_field_reason') ?></label>
                        <textarea name="reason" required><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('create_field_admin_name') ?></label>
                            <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= str('create_field_admin_authid') ?></label>
                            <input type="text" name="admin_authid" value="<?= htmlspecialchars($_POST['admin_authid'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= str('create_field_expire') ?></label>
                        <input type="datetime-local" name="expire_at" value="">
                    </div>

                    <div class="form-actions">
                        <a href="index.php<?= lang_url() ?>" class="btn-cancel"><?= str('create_cancel') ?></a>
                        <button type="submit" class="btn-save"><?= str('create_submit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
