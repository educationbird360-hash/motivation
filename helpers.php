<?php

// Start or resume the session with safer defaults
function start_secure_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

function require_auth() {
    if (!isset($_SESSION['username']) || !isset($_SESSION['role'])) {
        header('Location: index.php');
        exit();
    }
}

function require_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: index.php');
        exit();
    }
}

function require_member() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
        header('Location: index.php');
        exit();
    }
}

function redirect_if_logged_in() {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: admin_dashboard.php');
            exit();
        } elseif ($_SESSION['role'] === 'member') {
            header('Location: member_dashboard.php');
            exit();
        }
    }
}

function sanitize_text($value) {
    return trim(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS));
}

function sanitize_email($email) {
    return trim(filter_var($email, FILTER_SANITIZE_EMAIL));
}

function show_alert($message, $type = 'danger') {
    return '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show mt-3" role="alert">'
         . htmlspecialchars($message)
         . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
         . '<span aria-hidden="true">&times;</span>'
         . '</button>'
         . '</div>';
}

function escape($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
