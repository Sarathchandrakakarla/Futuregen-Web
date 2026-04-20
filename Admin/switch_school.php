<?php
session_start();

/**
 * Preserve minimal identity for switch
 */
$_SESSION['switch_context'] = [
    'username'   => $_SESSION['Admin_Id_No'] ?? null,
    'parent_id'  => $_SESSION['school_db']['parent_org'] ?? null,
    'from_school' => $_SESSION['school_db']['school_code'] ?? null
];

/**
 * Remove ONLY auth + branch state
 */
unset(
    $_SESSION['school_db'],
    $_SESSION['RBAC'],
    $_SESSION['Role_Name'],
    $_SESSION['Admin_Id_No'],
    $_SESSION['Faculty_Id_No'],
    $_SESSION['Student_Id_No']
);

session_regenerate_id(true);

/**
 * Redirect to switch gateway
 */
header("Location: /Futuregen/Welcome/preindex.php?switch=1");
exit;
