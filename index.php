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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gag List — CS 1.6</title>
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
                <?php if (is_auth()): ?>
                    <a href="create.php" class="btn-add">+ Добавить gag</a>
                    <a href="logout.php" class="btn-logout">Выйти</a>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Войти</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="toolbar">
            <?php
            $build_url = function($overrides = []) use ($search, $active_only) {
                $params = [];
                $q = isset($overrides['q']) ? $overrides['q'] : $search;
                if ($q !== '') $params['q'] = $q;
                if (isset($overrides['active'])) {
                    if ($overrides['active']) $params['active'] = '1';
                } elseif ($active_only) {
                    $params['active'] = '1';
                }
                return $params ? '?' . http_build_query($params) : 'index.php';
            };
            ?>
            <form method="get" action="index.php" class="search-form" style="flex:1; min-width:250px; display:flex; gap:8px; align-items:center;">
                <input type="text" name="q" class="search-box" placeholder="Поиск по нику, Steam ID или IP..." value="<?= htmlspecialchars($search) ?>">
                <?php if ($active_only): ?>
                    <input type="hidden" name="active" value="1">
                <?php endif; ?>
                <button type="submit" class="filter-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:4px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    Найти
                </button>
                <?php if ($search !== ''): ?>
                    <a href="<?= $build_url(['q' => '']) ?>" class="filter-btn">✕</a>
                <?php endif; ?>
            </form>
            <a href="<?= $build_url(['active' => 0]) ?>" class="filter-btn <?= !$active_only ? 'active' : '' ?>">Все</a>
            <a href="<?= $build_url(['active' => 1]) ?>" class="filter-btn <?= $active_only ? 'active' : '' ?>">Активные</a>
            <div class="stats">
                <span class="stat-badge stat-total">Всего: <?= $total ?></span>
                <span class="stat-badge stat-active">Активных: <?= $count_active ?></span>
                <span class="stat-badge stat-expired">Истёкших: <?= $count_expired ?></span>
            </div>
        </div>

        <div class="table-wrapper">
            <?php if (empty($gags)): ?>
                <div class="empty-state">
                    <p>Наказания не найдены</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Игрок</th>
                            <th class="col-steamid">Steam ID</th>
                            <?php if (is_auth()): ?>
                                <th class="col-ip">IP</th>
                            <?php endif; ?>
                            <th class="col-reason">Причина</th>
                            <th class="col-admin">Админ</th>
                            <th>Срок</th>
                            <th>Статус</th>
                            <?php if (is_auth()): ?>
                                <th>Действия</th>
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
                                    ∞ <span class="date-range">(<?= date('d.m.Y H:i', strtotime($gag['created_at'])) ?> — ∞)</span>
                                <?php else:
                                    $diff = strtotime($gag['expire_at']) - strtotime($gag['created_at']);
                                    $dur = '';
                                    if ($diff >= 31536000) { $n = floor($diff / 31536000); $dur = "$n " . plural($n, 'год', 'года', 'лет');
                                    } elseif ($diff >= 2592000) { $n = floor($diff / 2592000); $dur = "$n " . plural($n, 'месяц', 'месяца', 'месяцев');
                                    } elseif ($diff >= 604800) { $n = floor($diff / 604800); $dur = "$n " . plural($n, 'неделя', 'недели', 'недель');
                                    } elseif ($diff >= 86400) { $n = floor($diff / 86400); $dur = "$n " . plural($n, 'день', 'дня', 'дней');
                                    } elseif ($diff >= 3600) { $n = floor($diff / 3600); $dur = "$n ч.";
                                    } elseif ($diff >= 60) { $n = floor($diff / 60); $dur = "$n мин.";
                                    } else { $dur = "$diff сек."; }
                                    ?>
                                    <span class="date-duration"><?= $dur ?></span>
                                    <span class="date-range">(<?= date('d.m.Y H:i', strtotime($gag['created_at'])) ?> — <?= date('d.m.Y H:i', strtotime($gag['expire_at'])) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_permanent): ?>
                                    <span class="status-badge status-permanent">Навсегда</span>
                                <?php elseif ($is_active): ?>
                                    <span class="status-badge status-active">Активен</span>
                                <?php else: ?>
                                    <span class="status-badge status-expired">Истёк</span>
                                <?php endif; ?>
                            </td>
                            <?php if (is_auth()): ?>
                            <td>
                                <div class="actions">
                                    <a href="edit.php?id=<?= $gag['id'] ?>" class="btn btn-edit">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Удалить этот gag?')">
                                        <input type="hidden" name="id" value="<?= $gag['id'] ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-delete">
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
                <a href="?page=<?= $page - 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?>">← Назад</a>
            <?php endif; ?>
            
            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            
            if ($start > 1): ?>
                <a href="?page=1<?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?>">1</a>
                <?php if ($start > 2): ?><span>...</span><?php endif; ?>
            <?php endif;
            
            for ($p = $start; $p <= $end; $p++):
                if ($p == $page): ?>
                    <span class="current"><?= $p ?></span>
                <?php else: ?>
                    <a href="?page=<?= $p ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?>"><?= $p ?></a>
                <?php endif;
            endfor; ?>
            
            <?php if ($end < $total_pages): ?>
                <?php if ($end < $total_pages - 1): ?><span>...</span><?php endif; ?>
                <a href="?page=<?= $total_pages ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?>"><?= $total_pages ?></a>
            <?php endif; ?>
            
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $active_only ? '&active=1' : '' ?>">Далее →</a>
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
    </script>
</body>
</html>
