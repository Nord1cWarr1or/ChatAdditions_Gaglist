<?php
require_once 'config.php';
require_auth();
verify_csrf();

$conn = db_connect();
$id = intval($_POST['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM " . GAGS_TABLE . " WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

header('Location: index.php');
exit;
