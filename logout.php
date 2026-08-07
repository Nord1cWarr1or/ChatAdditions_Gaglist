<?php
require_once 'config.php';

$lang = current_lang();
session_destroy();
// Restore language preference after session destroy
$_SESSION['lang'] = $lang;
header('Location: index.php' . lang_url());
exit;
