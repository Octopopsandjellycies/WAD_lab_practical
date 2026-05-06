<?php
$sessionLifetime = 60 * 60 * 24 * 7;
$cookiePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');

if ($cookiePath === '') {
    $cookiePath = '/';
}

ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', (string) $sessionLifetime);

if (session_status() === PHP_SESSION_NONE) {
    session_name('srs_admin_session');
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => $cookiePath,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

if (isset($_SESSION['last_regenerated']) && time() - (int) $_SESSION['last_regenerated'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regenerated'] = time();
} elseif (!isset($_SESSION['last_regenerated'])) {
    $_SESSION['last_regenerated'] = time();
}
?>
