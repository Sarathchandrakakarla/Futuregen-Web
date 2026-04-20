<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce school selection
 */
if (!isset($_SESSION['school_db'])) {
    echo "<script>alert('Branch Context Missing!');</script>";
    header('Location: /Futuregen/Welcome/preindex.php');
    exit;
}

/**
 * DB routing
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Futuregen/db_router.php';

/**
 * Extra safety: block central DB usage here
 */
if ($link === $central) {
    error_log('SECURITY: Protected page resolved to central DB');
    header('Location: /Futuregen/Welcome/preindex.php');
    exit;
}
