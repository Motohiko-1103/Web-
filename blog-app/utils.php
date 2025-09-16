<?php
session_start();

function h($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sent = $_POST['csrf_token'] ?? '';
        if (!$sent || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
            http_response_code(400);
            die('Bad Request: invalid CSRF token');
        }
    }
}

function redirect($url) {
    header("Location: {$url}");
    exit;
}

function ensure_dir($path) {
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
    }
}

function parse_new_tags($input) {
    $names = array_filter(array_map('trim', explode(',', $input ?? '')));
    $names = array_map(function($n){ return mb_strtolower($n, 'UTF-8'); }, $names);
    $names = array_unique($names);
    return $names;
}
?>