<?php
declare(strict_types=1);

session_start();

$configFile = __DIR__ . '/config.local.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    die(
        'Admin is not set up yet on this server. Copy admin/config.sample.php to ' .
        'admin/config.local.php and set a real password — see the comments in that file.'
    );
}
require_once $configFile;

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}

function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function admin_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function admin_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(admin_csrf_token()) . '">';
}

function admin_check_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', is_string($token) ? $token : '')) {
        http_response_code(403);
        die('Security check failed (your session may have expired) — go back and try again.');
    }
}

// Handle login submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
    $password = $_POST['password'] ?? '';
    if (is_string($password) && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_authenticated'] = true;
        header('Location: index.php');
        exit;
    }
    $_SESSION['login_error'] = 'Incorrect password.';
    header('Location: index.php');
    exit;
}

// Handle logout.
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
