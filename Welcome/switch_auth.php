<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/Victory/db_router.php';

/* ==============================
   SWITCH CONTEXT CHECK
   ============================== */
if (!isset($_SESSION['switch_context'])) {
    header('Location: /Victory/Welcome/preindex.php');
    exit;
}

$username   = $_SESSION['switch_context']['username'];
$schoolCode = $_POST['school_code'];
$loginType  = $_POST['login_type'];
$password   = $_POST['password'];

/* ==============================
   1. Resolve School (CENTRAL DB)
   ============================== */
$stmt = mysqli_prepare(
    $central,
    "SELECT * FROM school_master
     WHERE school_code = ? AND active_flag = 1"
);
mysqli_stmt_bind_param($stmt, "s", $schoolCode);
mysqli_stmt_execute($stmt);
$school = mysqli_stmt_get_result($stmt)->fetch_assoc();

if (!$school) {
    die("Invalid school");
}

/* ==============================
   2. Connect School DB
   ============================== */
$link = connectSchoolDB($school);

/* ==============================
   ADMIN SWITCH LOGIN
   ============================== */
if ($loginType === 'Admin') {

    $sql = "
        SELECT a.Admin_Id_No, a.Admin_Hash,
               a.Role, r.Role_Name, r.Active_Flag
        FROM admin a
        JOIN roles r ON r.Role_Id = a.Role
        WHERE a.Admin_Id_No = ?
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$row || !password_verify($password, $row['Admin_Hash'])) {
        die("Incorrect username or password");
    }

    if ((int)$row['Active_Flag'] !== 1) {
        die("Your Role is Inactive");
    }

    session_regenerate_id(true);
    unset($_SESSION['switch_context']);

    $_SESSION['school_db']   = $school;
    $_SESSION['Admin_Id_No'] = $row['Admin_Id_No'];
    $_SESSION['Role_Name']   = $row['Role_Name'];

    loadRBAC($link, (int)$row['Role']);

    header("Location: /Victory/Admin/admin_dashboard.php");
    exit;
}

/* ==============================
   FACULTY SWITCH LOGIN
   ============================== */
if ($loginType === 'Faculty') {

    $sql = "
        SELECT f.Id_No, f.Fac_Hash, f.Status,
               f.Role, r.Role_Name, r.Active_Flag
        FROM faculty f
        LEFT JOIN roles r ON r.Role_Id = f.Role
        WHERE f.Id_No = ?
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$row || !password_verify($password, $row['Fac_Hash'])) {
        die("Incorrect username or password");
    }

    if ($row['Role_Name'] === null) {
        die("Invalid role mapping");
    }

    if ($row['Status'] === 'Disabled') {
        die("Your login has been disabled");
    }

    if ((int)$row['Active_Flag'] !== 1) {
        die("Your role is inactive");
    }

    session_regenerate_id(true);
    unset($_SESSION['switch_context']);

    $_SESSION['school_db'] = $school;
    $_SESSION['Id_No']     = $row['Id_No'];
    $_SESSION['Role_Name'] = $row['Role_Name'];

    loadRBAC($link, (int)$row['Role']);

    header("Location: /Victory/Faculty/faculty_dashboard.php");
    exit;
}

/* ==============================
   STUDENT SWITCH LOGIN
   ============================== */
if ($loginType === 'Student') {

    $sql = "
        SELECT s.Id_No, s.Stu_Hash, s.Status,
               s.Role, r.Role_Name, r.Active_Flag
        FROM student s
        LEFT JOIN roles r ON r.Role_Id = s.Role
        WHERE s.Id_No = ?
    ";

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$row || !password_verify($password, $row['Stu_Hash'])) {
        die("Incorrect username or password");
    }

    if ($row['Status'] === 'Disabled') {
        die("Your login has been disabled");
    }

    if ($row['Role'] === null || $row['Role_Name'] === null) {
        die("Role not assigned");
    }

    if ((int)$row['Active_Flag'] !== 1) {
        die("Your role is inactive");
    }

    session_regenerate_id(true);
    unset($_SESSION['switch_context']);

    $_SESSION['school_db'] = $school;
    $_SESSION['Id_No']     = $row['Id_No'];
    $_SESSION['Role_Name'] = $row['Role_Name'];

    loadRBAC($link, (int)$row['Role']);

    /* Load student master data */
    $id = $row['Id_No'];
    $res = mysqli_query($link, "SELECT * FROM student_master_data WHERE Id_No = '$id'");
    if (mysqli_num_rows($res) === 1) {
        foreach (mysqli_fetch_assoc($res) as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }

    header("Location: /Victory/Student/student_dashboard.php");
    exit;
}

die("Invalid login type");

/* ==============================
   RBAC LOADER (SHARED)
   ============================== */
function loadRBAC(mysqli $link, int $roleId): void
{
    $_SESSION['RBAC'] = [];

    $q = mysqli_query(
        $link,
        "SELECT Menu_Id,
                can_view, can_create, can_update,
                can_delete, can_print, can_export,
                can_custom1, can_custom2, can_custom3, can_custom4
         FROM role_menu_map
         WHERE Role_Id = $roleId"
    );

    while ($p = mysqli_fetch_assoc($q)) {
        $_SESSION['RBAC'][(int)$p['Menu_Id']] = [
            'view'    => (int)$p['can_view'],
            'create'  => (int)$p['can_create'],
            'update'  => (int)$p['can_update'],
            'delete'  => (int)$p['can_delete'],
            'print'   => (int)$p['can_print'],
            'export'  => (int)$p['can_export'],
            'custom1' => (int)$p['can_custom1'],
            'custom2' => (int)$p['can_custom2'],
            'custom3' => (int)$p['can_custom3'],
            'custom4' => (int)$p['can_custom4'],
        ];
    }
}
