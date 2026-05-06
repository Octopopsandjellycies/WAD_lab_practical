<?php
require_once 'session_bootstrap.php';

if (!isset($_SESSION['admin_id'], $_SESSION['admin_username'])) {
    header('Location: login.php');
    exit();
}
?>
