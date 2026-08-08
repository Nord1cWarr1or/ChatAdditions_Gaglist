<?php
require_once 'config.php';
require_auth();

$conn = db_connect();
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: index.php' . lang_url());
    exit;
}

$stmt = $conn->prepare("SELECT * FROM " . GAGS_TABLE . " WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$gag = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$gag) {
    header('Location: index.php' . lang_url());
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
        $error = str('edit_error_empty');
    } elseif ($expire_at !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $expire_at)) {
        $error = str('edit_error_date');
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
            $success = str('edit_success');
            $gag['name'] = $name;
            $gag['authid'] = $authid;
            $gag['ip'] = $ip;
            $gag['reason'] = $reason;
            $gag['admin_name'] = $admin_name;
            $gag['admin_authid'] = $admin_authid;
            $gag['expire_at'] = $expire_at;
            $gag['flags'] = $flags;
        } else {
            $error = sprintf(str('edit_error_db'), $conn->error);
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
    <title><?= str('edit_title') ?></title>
    <link rel="stylesheet" href="style.css">
    <?php load_theme_css(); ?>
</head>
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
                <h2><?= sprintf(str('edit_heading'), $id) ?></h2>

                <?php if ($success): ?>
                    <div class="error-msg" style="background:#d4edda;color:#155724;"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="lang" value="<?= current_lang() ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('edit_field_name') ?></label>
                            <input type="text" name="name" value="<?= htmlspecialchars(fix_encoding($gag['name'])) ?>" required>
                        </div>
                        <div class="form-group">
                            <label><?= str('edit_field_authid') ?></label>
                            <input type="text" name="authid" value="<?= htmlspecialchars($gag['authid']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('edit_field_ip') ?></label>
                            <input type="text" name="ip" value="<?= htmlspecialchars($gag['ip']) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= str('edit_field_flags') ?></label>
                            <div style="display:flex;gap:16px;margin-top:6px;">
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_a" value="1" <?= (intval($gag['flags']) & 1) ? 'checked' : '' ?>>
                                    <?= str('edit_flag_a') ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_b" value="2" <?= (intval($gag['flags']) & 2) ? 'checked' : '' ?>>
                                    <?= str('edit_flag_b') ?>
                                </label>
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:14px;">
                                    <input type="checkbox" name="flag_c" value="4" <?= (intval($gag['flags']) & 4) ? 'checked' : '' ?>>
                                    <?= str('edit_flag_c') ?>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= str('edit_field_reason') ?></label>
                        <textarea name="reason" required><?= htmlspecialchars(fix_encoding($gag['reason'])) ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label><?= str('edit_field_admin_name') ?></label>
                            <input type="text" name="admin_name" value="<?= htmlspecialchars(fix_encoding($gag['admin_name'])) ?>">
                        </div>
                        <div class="form-group">
                            <label><?= str('edit_field_admin_authid') ?></label>
                            <input type="text" name="admin_authid" value="<?= htmlspecialchars($gag['admin_authid']) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= str('edit_field_expire') ?></label>
                        <input type="datetime-local" name="expire_at"
                               value="<?= $gag['expire_at'] !== '2286-11-20 17:46:39' ? date('Y-m-d\TH:i', strtotime($gag['expire_at'])) : '' ?>">
                    </div>

                    <div class="form-actions">
                        <a href="index.php<?= lang_url() ?>" class="btn-cancel"><?= str('edit_cancel') ?></a>
                        <button type="submit" class="btn-save"><?= str('edit_submit') ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
