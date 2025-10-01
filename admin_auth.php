<?php
session_start();
// Prevent session fixation
session_regenerate_id(true);

// Set secure session parameters
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
ini_set('session.use_strict_mode', 1);

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirectIfNotLoggedIn() {
    if (!isAdminLoggedIn()) {
        header("Location: admin_login.php");
        exit;
    }
}

function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}

function getAdminName() {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function getAdminUsername() {
    return $_SESSION['admin_username'] ?? '';
}

function adminLogout() {
    session_unset();
    session_destroy();
    header("Location: admin_login.php");
    exit;
}
?>