<?php
// rbac_helper.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensure student is logged in
 */
function requireLogin()
{
    if (!isset($_SESSION['Id_No'])) {
        echo "<script>
            alert('Session Expired! Please Login Again!');
            location.replace('/Futuregen/Student/student_login.php');
        </script>";
        exit;
    }
}

/**
 * Ensure menu access exists
 */
function requireMenuAccess(int $menuId)
{
    if (!isset($_SESSION['RBAC'][$menuId])) {
        echo "<script>
            alert('Access Denied!');
            location.replace('/Futuregen/Student/student_dashboard.php');
        </script>";
        exit;
    }
}

/**
 * Check a specific permission flag for current menu
 * Example flags: can_view, can_create, can_update, can_delete, can_print, can_export
 */
function can(string $permission, int $menuId): bool
{
    return (
        isset($_SESSION['RBAC'][$menuId][$permission]) &&
        $_SESSION['RBAC'][$menuId][$permission] == 1
    );
}
