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

$count_sql = "SELECT COUNT(*) as cnt FROM chatadditions_gags $where";
$stmt = $conn->prepare($count_sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['cnt'];
$stmt->close();

$total_pages = max(1, ceil($total / $per_page));
$page = min($page, $total_pages);
$offset = ($page - 1) * $per_page;

$sql = "SELECT id, name, authid, ip, reason, admin_name, admin_authid, created_at, expire_at, flags 
        FROM chatadditions_gags $where 
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

$count_active = $conn->query("SELECT COUNT(*) as cnt FROM chatadditions_gags WHERE expire_at > NOW() OR expire_at = '2286-11-20 17:46:39'")->fetch_assoc()['cnt'];
$count_expired = $conn->query("SELECT COUNT(*) as cnt FROM chatadditions_gags WHERE expire_at <= NOW() AND expire_at != '2286-11-20 17:46:39'")->fetch_assoc()['cnt'];

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
            <h1><a href="index.php" style="text-decoration:none;color:inherit;">🔇 Gag <span>List</span></a></h1>
            <div class="nav-links">
                <button class="theme-toggle" onclick="toggleTheme()" title="Переключить тему">☀️</button>
                <?php if (is_auth()): ?>
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
                <button type="submit" class="filter-btn">🔍 Найти</button>
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
                                    <a href="edit.php?id=<?= $gag['id'] ?>" class="btn btn-edit">✏️</a>
                                    <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Удалить этот gag?')">
                                        <input type="hidden" name="id" value="<?= $gag['id'] ?>">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-delete">🗑️</button>
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
