<?php

/**
 * db_router.php
 * ----------------------------
 * Responsibility:
 * - Provide DB connections ONLY
 * - No business logic
 * - No school selection
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ==============================
   CENTRAL DB CONFIG
   ============================== */

define('CENTRAL_DB_HOST', 'localhost');
define('CENTRAL_DB_USER', 'root');
define('CENTRAL_DB_PASS', '');
define('CENTRAL_DB_NAME', 'central');

/* ==============================
   INTERNAL CONNECTION REGISTRY
   ============================== */

$GLOBALS['__DB_POOL__'] = [
    'central' => null,
    'school'  => null,
];

/* ==============================
   CONNECTORS
   ============================== */

function connectCentralDB(): mysqli
{
    if ($GLOBALS['__DB_POOL__']['central'] instanceof mysqli) {
        return $GLOBALS['__DB_POOL__']['central'];
    }

    $conn = mysqli_connect(
        CENTRAL_DB_HOST,
        CENTRAL_DB_USER,
        CENTRAL_DB_PASS,
        CENTRAL_DB_NAME
    );

    if ($conn === false) {
        error_log('Central DB connection failed: ' . mysqli_connect_error());
        die('System temporarily unavailable.');
    }

    $GLOBALS['__DB_POOL__']['central'] = $conn;
    return $conn;
}

function connectSchoolDB(array $school): mysqli
{
    if ($GLOBALS['__DB_POOL__']['school'] instanceof mysqli) {
        return $GLOBALS['__DB_POOL__']['school'];
    }

    $conn = mysqli_connect(
        $school['db_host'],
        $school['db_user'],
        $school['db_pass'],
        $school['db_name']
    );

    if ($conn === false) {
        error_log(
            'School DB connection failed [' . ($school['school_code'] ?? 'unknown') . ']: '
                . mysqli_connect_error()
        );
        die('School system unavailable.');
    }

    $GLOBALS['__DB_POOL__']['school'] = $conn;
    return $conn;
}

/* ==============================
   ROUTING
   ============================== */

/**
 * CENTRAL DB is ALWAYS available
 */
$central = connectCentralDB();

/**
 * $link resolution:
 * - If school context exists → school DB
 * - Else → central DB
 */
if (isset($_SESSION['school_db'])) {
    $link = connectSchoolDB($_SESSION['school_db']);
} else {
    $link = $central;
}
