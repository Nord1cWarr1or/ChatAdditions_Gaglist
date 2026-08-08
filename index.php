<?php
require_once 'config.php';

$conn = db_connect();

$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 25;
$offset = ($page - 1) * $per_page;
$search = trim($_GET['q'] ?? '');
$active_only = isset($_GET['active']) && $_GET['active'] == '1';

$where_parts = [];
$params = [];
$types = '';

if ($search !== '') {
    $where_parts[] = "(name LIKE ? OR name LIKE ? OR authid LIKE ? OR ip LIKE ?)";
    $search_escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
    $like = '%' . $search_escaped . '%';
    $like_double = '%' . double_encode($search_escaped) . '%';
    $params = array_merge($params, [$like, $like_double, $like, $like]);
    $types .= 'ssss';
}

if ($active_only) {
    $where_parts[] = "(expire_at > NOW() OR expire_at = '2286-11-20 17:46:39')";
}

$where = '';
if ($where_parts) {
    $where = 'WHERE ' . implode(' AND ', $where_parts);
}

$count_sql = "SELECT COUNT(*) as cnt FROM " . GAGS_TABLE . " $where";
$stmt = $conn->prepare($count_sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_filtered = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$total = $conn->query("SELECT COUNT(*) as cnt FROM " . GAGS_TABLE)->fetch_assoc()['cnt'];
$total_pages = max(1, ceil($total_filtered / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "SELECT id, name, authid, ip, reason, admin_name, admin_authid, created_at, expire_at, flags
        FROM " . GAGS_TABLE . " $where
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$limit_types = $types . 'ii';
$limit_params = array_merge($params, [$per_page, $offset]);
$stmt->bind_param($limit_types, ...$limit_params);
$stmt->execute();
$result = $stmt->get_result();
$gags = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$count_active = $conn->query("SELECT COUNT(*) as cnt FROM " . GAGS_TABLE . " WHERE expire_at > NOW() OR expire_at = '2286-11-20 17:46:39'")->fetch_assoc()['cnt'];
$count_expired = $conn->query("SELECT COUNT(*) as cnt FROM " . GAGS_TABLE . " WHERE expire_at <= NOW() AND expire_at != '2286-11-20 17:46:39'")->fetch_assoc()['cnt'];

$now = date('Y-m-d H:i:s');

/**
 * Format duration between two timestamps using localized pluralization.
 */
function format_duration($created, $expire) {
    $diff = strtotime($expire) - strtotime($created);
    if ($diff >= 31536000) {
        $n = floor($diff / 31536000);
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 19) $key = 'dur_years_many';
        elseif ($mod10 === 1) $key = 'dur_year';
        elseif ($mod10 >= 2 && $mod10 <= 4) $key = 'dur_years';
        else $key = 'dur_years_many';
        return sprintf(str($key), $n);
    }
    if ($diff >= 2592000) {
        $n = floor($diff / 2592000);
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 19) $key = 'dur_months_many';
        elseif ($mod10 === 1) $key = 'dur_month';
        elseif ($mod10 >= 2 && $mod10 <= 4) $key = 'dur_months';
        else $key = 'dur_months_many';
        return sprintf(str($key), $n);
    }
    if ($diff >= 604800) {
        $n = floor($diff / 604800);
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 19) $key = 'dur_weeks_many';
        elseif ($mod10 === 1) $key = 'dur_week';
        elseif ($mod10 >= 2 && $mod10 <= 4) $key = 'dur_weeks';
        else $key = 'dur_weeks_many';
        return sprintf(str($key), $n);
    }
    if ($diff >= 86400) {
        $n = floor($diff / 86400);
        $mod10 = $n % 10;
        $mod100 = $n % 100;
        if ($mod100 >= 11 && $mod100 <= 19) $key = 'dur_days_many';
        elseif ($mod10 === 1) $key = 'dur_day';
        elseif ($mod10 >= 2 && $mod10 <= 4) $key = 'dur_days';
        else $key = 'dur_days_many';
        return sprintf(str($key), $n);
    }
    if ($diff >= 3600) return sprintf(str('dur_hour'), floor($diff / 3600));
    if ($diff >= 60) return sprintf(str('dur_min'), floor($diff / 60));
    return sprintf(str('dur_sec'), $diff);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= str('index_title') ?></title>
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
                <div class="lang-dropdown">
                    <button class="btn-lang" onclick="toggleLangDropdown()"><?= current_lang_flag() ?> <?= current_lang_name() ?> ▾</button>
                    <div class="lang-dropdown-menu" id="langMenu">
                        <?php foreach (get_languages() as $code => $meta): ?>
                            <a href="<?= lang_url(['lang' => $code]) ?>" class="<?= $code === current_lang() ? 'active' : '' ?>"><?= $meta['flag'] ?> <?= $meta['name'] ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php if (is_auth()): ?>
                    <a href="create.php<?= lang_url() ?>" class="btn-add"><?= str('nav_add') ?></a>
                    <a href="logout.php<?= lang_url() ?>" class="btn-logout"><?= str('nav_logout') ?></a>
                <?php else: ?>
                    <a href="login.php<?= lang_url() ?>" class="btn-login"><?= str('nav_login') ?></a>
                <?php endif; ?>
            </div>
        </header>

        <div class="toolbar">
            <?php
            $build_url = function($overrides = []) use ($search, $active_only) {
                $params = array_filter($_GET, fn($k) => $k !== 'lang', ARRAY_FILTER_USE_KEY);
                $q = isset($overrides['q']) ? $overrides['q'] : $search;
                if ($q !== '') $params['q'] = $q;
                if (array_key_exists('active', $overrides)) {
                    if ($overrides['active']) $params['active'] = '1';
                    else unset($params['active']);
                } elseif ($active_only) {
                    $params['active'] = '1';
                }
                // Preserve language
                $lang = current_lang();
                if ($lang !== 'ru') $params['lang'] = $lang;
                return $params ? '?' . http_build_query($params) : '';
            };
            ?>
            <form method="get" action="index.php" class="search-form" style="flex:1; min-width:250px; display:flex; gap:8px; align-items:center;">
                <?php if (current_lang() !== 'ru'): ?><input type="hidden" name="lang" value="<?= current_lang() ?>"><?php endif; ?>
                <input type="text" name="q" class="search-box" placeholder="<?= str('index_search_placeholder') ?>" value="<?= htmlspecialchars($search) ?>">
                <?php if ($active_only): ?>
                    <input type="hidden" name="active" value="1">
                <?php endif; ?>
                <button type="submit" class="filter-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <?= str('index_search_btn') ?>
                </button>
                <?php if ($search !== ''): ?>
                    <a href="<?= $build_url(['q' => '']) ?>" class="filter-btn">✕</a>
                <?php endif; ?>
            </form>
            <a href="<?= $build_url(['active' => false]) ?: 'index.php' ?>" class="filter-btn <?= !$active_only ? 'active' : '' ?>"><?= str('index_filter_all') ?></a>
            <a href="<?= $build_url(['active' => 1]) ?>" class="filter-btn <?= $active_only ? 'active' : '' ?>"><?= str('index_filter_active') ?></a>
            <div class="stats">
                <span class="stat-badge stat-total"><?= str('index_stat_total', $total) ?></span>
                <span class="stat-badge stat-active"><?= str('index_stat_active', $count_active) ?></span>
                <span class="stat-badge stat-expired"><?= str('index_stat_expired', $count_expired) ?></span>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if (empty($gags)): ?>
                <div class="empty-state">
                    <p><?= str('index_empty') ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th><?= str('index_th_player') ?></th>
                            <th class="col-steamid"><?= str('index_th_steamid') ?></th>
                            <?php if (is_auth()): ?>
                                <th class="col-ip"><?= str('index_th_ip') ?></th>
                            <?php endif; ?>
                            <th class="col-reason"><?= str('index_th_reason') ?></th>
                            <th class="col-admin"><?= str('index_th_admin') ?></th>
                            <th><?= str('index_th_term') ?></th>
                            <th><?= str('index_th_status') ?></th>
                            <?php if (is_auth()): ?>
                                <th><?= str('index_th_actions') ?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gags as $i => $gag):
                            $is_permanent = ($gag['expire_at'] === '2286-11-20 17:46:39');
                            $is_active = $is_permanent || (strtotime($gag['expire_at']) > strtotime($now));
                        ?>
                        <tr>
                            <td><?= $offset + $i + 1 ?></td>
                            <td class="player-name" title="<?= htmlspecialchars(fix_encoding($gag['name'])) ?>"><?= htmlspecialchars(fix_encoding($gag['name'])) ?></td>
                            <td class="col-steamid"><span class="steam-id"><?= htmlspecialchars($gag['authid']) ?></span></td>
                            <?php if (is_auth()): ?>
                                <td class="ip-address col-ip"><?= htmlspecialchars($gag['ip']) ?></td>
                            <?php endif; ?>
                            <td class="reason-text col-reason" title="<?= htmlspecialchars(fix_encoding($gag['reason'])) ?>"><?= htmlspecialchars(fix_encoding($gag['reason'])) ?></td>
                            <td class="admin-name col-admin" title="<?= htmlspecialchars(fix_encoding($gag['admin_name'])) ?>"><?= htmlspecialchars(fix_encoding($gag['admin_name'])) ?></td>
                            <td class="date-cell">
                                <?php if ($is_permanent): ?>
                                    <?= str('index_permanent_symbol') ?> <span class="date-range">(<?= date('d.m.Y H:i', strtotime($gag['created_at'])) ?> — <?= str('index_permanent_symbol') ?>)</span>
                                <?php else:
                                    $dur = format_duration($gag['created_at'], $gag['expire_at']);
                                    ?>
                                    <span class="date-duration"><?= $dur ?></span>
                                    <span class="date-range">(<?= date('d.m.Y H:i', strtotime($gag['created_at'])) ?> — <?= date('d.m.Y H:i', strtotime($gag['expire_at'])) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_permanent): ?>
                                    <span class="status-badge status-permanent"><?= str('index_status_permanent') ?></span>
                                <?php elseif ($is_active): ?>
                                    <span class="status-badge status-active"><?= str('index_status_active') ?></span>
                                <?php else: ?>
                                    <span class="status-badge status-expired"><?= str('index_status_expired') ?></span>
                                <?php endif; ?>
                            </td>
                            <?php if (is_auth()): ?>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?= $gag['id'] ?>&amp;<?= ltrim(lang_url(), '?') ?>" class="btn btn-edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('<?= str('index_delete_confirm') ?>')">
                                        <input type="hidden" name="id" value="<?= $gag['id'] ?>">
                                        <?= csrf_field() ?>
                                        <?php if (current_lang() !== 'ru'): ?><input type="hidden" name="lang" value="<?= current_lang() ?>"><?php endif; ?>
                                        <button type="submit" class="btn btn-delete" title="<?= str('index_delete_tooltip') ?>">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?><?= current_lang() !== 'ru' ? '&lang=' . current_lang() : '' ?>">← <?= str('nav_back') ?></a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);

            if ($start > 1): ?>
                <a href="?page=1<?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?><?= current_lang() !== 'ru' ? '&lang=' . current_lang() : '' ?>">1</a>
                <?php if ($start > 2): ?><span>...</span><?php endif; ?>
            <?php endif;

            for ($p = $start; $p <= $end; $p++):
                if ($p == $page): ?>
                    <span class="current"><?= $p ?></span>
                <?php else: ?>
                    <a href="?page=<?= $p ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?><?= current_lang() !== 'ru' ? '&lang=' . current_lang() : '' ?>"><?= $p ?></a>
                <?php endif;
            endfor; ?>

            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span>...</span><?php endif; ?>
                <a href="?page=<?= $total_pages ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?><?= current_lang() !== 'ru' ? '&lang=' . current_lang() : '' ?>"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?><?= current_lang() !== 'ru' ? '&lang=' . current_lang() : '' ?>"><?= str('nav_next') ?></a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    function getTheme() {
        return localStorage.getItem('theme') || 'light';
    }
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
    function toggleLangDropdown() {
        document.getElementById('langMenu').classList.toggle('open');
    }
    document.addEventListener('click', function(e) {
        var dd = document.querySelector('.lang-dropdown');
        if (dd && !dd.contains(e.target)) {
            document.getElementById('langMenu').classList.remove('open');
        }
    });
    </script>
</body>
</html>
