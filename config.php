<?php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'pass');
define('DB_NAME', 'db_name');

define('ADMIN_LOGIN', 'admin');
define('ADMIN_PASSWORD', 'changeme');

header('Content-Type: text/html; charset=utf-8');
session_start();
date_default_timezone_set('Europe/Moscow');

function db_connect() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('Ошибка подключения к БД: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
        $conn->query("SET NAMES utf8mb4");
    }
    return $conn;
}

function is_auth() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function fix_encoding($str) {
    if (!mb_check_encoding($str, 'UTF-8')) {
        return $str;
    }
    $fixed = mb_convert_encoding($str, 'Windows-1252', 'UTF-8');
    return $fixed !== false ? $fixed : $str;
}

function require_auth() {
    if (!is_auth()) {
        header('Location: login.php');
        exit;
    }
}

function plural($n, $one, $few, $many) {
    $mod10 = $n % 10;
    $mod100 = $n % 100;
    if ($mod100 >= 11 && $mod100 <= 19) return $many;
    if ($mod10 === 1) return $one;
    if ($mod10 >= 2 && $mod10 <= 4) return $few;
    return $many;
}

function double_encode($str) {
    $result = '';
    $bytes = $str;
    for ($i = 0; $i < strlen($bytes); $i++) {
        $b = ord($bytes[$i]);
        $result .= mb_convert_encoding(chr($b), 'UTF-8', 'Windows-1252');
    }
    return $result;
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf() {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        die('CSRF token mismatch');
    }
}
