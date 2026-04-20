<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 107);

requireLogin();
requireMenuAccess(MENU_ID);

//error_reporting(0);
?>
<?php
$success = '';
$errors  = [];

// Flash success
if (isset($_SESSION['success_msg'])) {
    $success = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

// Flash errors (optional)
if (isset($_SESSION['error_msgs'])) {
    $errors = $_SESSION['error_msgs'];
    unset($_SESSION['error_msgs']);
}

/* ---------------- READ: INITIALIZE ---------------- */
$roles      = [];
$menus      = [];
$roleMaps   = [];
$selectedRoleId = null;
$login_type = null;
$platform_type = null;

/* ---------------- COPY ROLE–MENU MAPPINGS ---------------- */
if (isset($_POST['CopyMappings'])) {

    if (!can('custom1', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to copy mappings');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $sourceRole = (int)$_POST['Source_Role_Id'];
    $targetRole = (int)$_POST['Target_Role_Id'];
    $admin      = $_SESSION['Admin_Id_No'];

    /* ---------- BASIC VALIDATION ---------- */
    if ($sourceRole === $targetRole) {
        $_SESSION['error_msgs'][] = 'Source and Target roles must be different.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $loginType = $_POST['Login_Type'] ?? '';
    $platformType = $_POST['Platform_Type'] ?? '';

    $chk = mysqli_query($link, "
        SELECT COUNT(*) AS cnt
        FROM roles
        WHERE Role_Id IN ($sourceRole, $targetRole)
        AND Login_Type = '$loginType'
    ");


    $row = mysqli_fetch_assoc($chk);
    if ((int)$row['cnt'] !== 2) {
        $_SESSION['error_msgs'][] =
            'Source and Target roles must belong to the same Login Type.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    /* ---------- VALIDATE ROLES ---------- */
    $srcQ = mysqli_query($link, "
        SELECT Role_Id
        FROM roles
        WHERE Role_Id = $sourceRole
    ");

    $tgtQ = mysqli_query($link, "
        SELECT Role_Id, Active_Flag
        FROM roles
        WHERE Role_Id = $targetRole
    ");

    if (mysqli_num_rows($srcQ) !== 1) {
        $_SESSION['error_msgs'][] = 'Invalid source role selected.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $tgt = mysqli_fetch_assoc($tgtQ);
    if (!$tgt || (int)$tgt['Active_Flag'] !== 1) {
        $_SESSION['error_msgs'][] = 'Target role must be active.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    mysqli_begin_transaction($link);

    try {

        /* ---------- FETCH SOURCE MAPPINGS ---------- */
        $sourceMaps = [];
        $srcMapQ = mysqli_query($link, "
            SELECT rm.*
            FROM role_menu_map rm
            JOIN menus m ON m.Menu_Id = rm.Menu_Id
            WHERE rm.Role_Id = $sourceRole
              AND m.Login_Type = '$loginType'
              AND m.Platform_Type = '$platformType'
        ");
        while ($row = mysqli_fetch_assoc($srcMapQ)) {
            $sourceMaps[] = $row;
        }

        /* ---------- CLEAR TARGET MAPPINGS ---------- */
        mysqli_query($link, "
            DELETE rm
            FROM role_menu_map rm
            JOIN menus m ON m.Menu_Id = rm.Menu_Id
            WHERE rm.Role_Id = $targetRole
              AND m.Login_Type = '$loginType'
              AND m.Platform_Type = '$platformType'
        ");

        /* ---------- FETCH ACTIVE PARENT MENUS ---------- */
        $parentIds = [];
        $pQ = mysqli_query($link, "
            SELECT Menu_Id
            FROM menus
            WHERE Parent_Flag = 1
              AND Active_Flag = 1
              AND Login_Type = '$loginType'
              AND Platform_Type = '$platformType'
        ");
        while ($p = mysqli_fetch_assoc($pQ)) {
            $parentIds[] = (int)$p['Menu_Id'];
        }

        /* ---------- INSERT PARENTS (FLAGS = 0) ---------- */
        if ($platformType !== 'App') {
            foreach ($parentIds as $pid) {
                mysqli_query($link, "
                    INSERT INTO role_menu_map
                    (Role_Id, Menu_Id, Created_By, Created_On)
                    VALUES
                    ($targetRole, $pid, '$admin', NOW())
                ");
            }
        }

        /* ---------- INSERT CHILD MAPPINGS ---------- */
        foreach ($sourceMaps as $row) {

            $menuId = (int)$row['Menu_Id'];

            // Skip parents (already inserted)
            if ($platformType !== 'App' && in_array($menuId, $parentIds, true)) {
                continue;
            }

            $cols = ['Role_Id', 'Menu_Id', 'Created_By', 'Created_On'];
            $vals = [$targetRole, $menuId, "'$admin'", 'NOW()'];

            foreach ($row as $col => $val) {
                if (strpos($col, 'can_') === 0 && (int)$val === 1) {
                    $cols[] = $col;
                    $vals[] = 1;
                }
            }

            mysqli_query($link, "
                INSERT INTO role_menu_map
                (" . implode(',', $cols) . ")
                VALUES
                (" . implode(',', $vals) . ")
            ");
        }

        mysqli_commit($link);

        if ($platformType === 'App') {
            mysqli_query($link, "
                            UPDATE roles
                            SET Permission_Version = Permission_Version + 1
                            WHERE Role_Id = $targetRole
                        ");
        }

        $roleNames = [];

        $nameQ = mysqli_query($link, "
            SELECT Role_Id, Role_Name
            FROM roles
            WHERE Role_Id IN ($sourceRole, $targetRole)
        ");

        while ($r = mysqli_fetch_assoc($nameQ)) {
            $roleNames[$r['Role_Id']] = $r['Role_Name'];
        }

        $sourceRoleName = $roleNames[$sourceRole] ?? "Role ($sourceRole)";
        $targetRoleName = $roleNames[$targetRole] ?? "Role ($targetRole)";


        $_SESSION['success_msg'] =
            "Permissions copied successfully from "
            . htmlspecialchars($sourceRoleName)
            . " → "
            . htmlspecialchars($targetRoleName) . ".";

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {

        mysqli_rollback($link);
        $_SESSION['error_msgs'][] = 'Failed to copy role permissions.';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

/* ---------------- READ: FETCH ROLES ---------------- */
if (isset($_POST['ShowRoles'])) {
    if (!can('view', 106)) {
        echo "<script>alert('You don\'t have permission to view roles');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $login_type = $_POST['Login_Type'];
    $platform_type = $_POST['Platform_Type'];
    $status = $_POST['Role_Status'];

    $where = "";
    if ($status === 'A') $where = "WHERE Active_Flag = 1";
    if ($status === 'I') $where = "WHERE Active_Flag = 0";

    $roles = mysqli_query($link, "
        SELECT Role_Id, Role_Name, Active_Flag
        FROM roles
        $where AND Login_Type = '$login_type'
        ORDER BY Role_Name
    ");
}

/* ---------------- READ: FETCH MENUS (ACTIVE ONLY) ---------------- */
if (isset($_POST['ShowMappings'])) {

    if (!can('view', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to view mappings');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $selectedRole = null;
    $selectedRoleId = (int)$_POST['Role_Id'];
    $login_type = $_POST['Login_Type'];
    $platformType = $_POST['Platform_Type'];
    $platform_type = $platformType;
    $isApp = ($platformType === 'App');

    $srQ = mysqli_query($link, "
        SELECT Role_Id, Role_Name, Active_Flag
        FROM roles
        WHERE Role_Id = $selectedRoleId
    ");

    if ($sr = mysqli_fetch_assoc($srQ)) {
        $selectedRole = $sr;
    }

    if (!$selectedRole) {
        $_SESSION['error_msgs'][] = "Selected role does not exist.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $parentMenus = mysqli_query($link, "
        SELECT Menu_Id, Menu_Name,Display_Name, Sequence_Id, Route
        FROM menus
        WHERE Active_Flag = 1
        AND Parent_Flag = 1
        AND Login_Type = '$login_type'
        AND Platform_Type = '$platformType'
        ORDER BY Sequence_Id
    ");


    $mapQ = mysqli_query($link, "
        SELECT *
        FROM role_menu_map
        WHERE Role_Id = $selectedRoleId
    ");

    while ($m = mysqli_fetch_assoc($mapQ)) {
        $roleMaps[$m['Menu_Id']] = $m;
    }
    $isRoleActive = 1;

    $roleStatusQ = mysqli_query($link, "
        SELECT Active_Flag
        FROM roles
        WHERE Role_Id = $selectedRoleId
    ");

    if ($row = mysqli_fetch_assoc($roleStatusQ)) {
        $isRoleActive = (int)$row['Active_Flag'];
    }
}

/* ---------------- SAVE ROLE–MENU MAPPINGS (FINAL) ---------------- */
if (isset($_POST['SaveMappings'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to save mappings');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $roleId = (int)$_POST['Role_Id'];
    $admin  = $_SESSION['Admin_Id_No'];
    $platformType = $_POST['Platform_Type'];
    $isApp = ($platformType === 'App');

    /* ---------- Role Active Check ---------- */
    $rQ = mysqli_query($link, "
        SELECT Role_Name,Active_Flag,Login_Type
        FROM roles
        WHERE Role_Id = $roleId
    ");
    $r = mysqli_fetch_assoc($rQ);
    $roleName = $r['Role_Name'];
    $loginType = $r['Login_Type'];

    if (!$r || (int)$r['Active_Flag'] !== 1) {
        $_SESSION['error_msgs'][] = "Inactive role cannot be modified";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    $postedPerms = $_POST['perm'] ?? [];

    mysqli_begin_transaction($link);

    try {

        /* ---------- Counters ---------- */
        $insertCount = 0;
        $updateCount = 0;
        $deleteCount = 0;

        /* ---------- Fetch Menu Metadata ---------- */
        $menuMeta = [];
        $metaQ = mysqli_query($link, "
            SELECT Menu_Id, Parent_Flag, Route, Par_Menu_Id
            FROM menus
            WHERE Active_Flag = 1
              AND Login_Type = '$loginType'
              AND Platform_Type = '$platformType'
        ");
        while ($meta = mysqli_fetch_assoc($metaQ)) {
            $menuMeta[(int)$meta['Menu_Id']] = $meta;
        }

        $parentIds = [];
        foreach ($menuMeta as $menuId => $meta) {
            if ((int)$meta['Parent_Flag'] === 1) {
                $parentIds[] = (int)$menuId;
            }
        }

        /* ---------- Fetch Existing Mappings ---------- */
        $existing = [];
        $exQ = mysqli_query($link, "
            SELECT *
            FROM role_menu_map
            WHERE Role_Id = $roleId
        ");
        while ($row = mysqli_fetch_assoc($exQ)) {
            $existing[(int)$row['Menu_Id']] = $row;
        }

        /* ---------- Ensure Parent Rows Exist (Flags = 0) ---------- */
        if (!$isApp) {
            foreach ($parentIds as $pid) {
                if (!isset($existing[$pid])) {
                    mysqli_query($link, "
                        INSERT INTO role_menu_map
                        (Role_Id, Menu_Id, Created_By, Created_On)
                        VALUES
                        ($roleId, $pid, '$admin', NOW())
                    ");
                    $existing[$pid] = ['Menu_Id' => $pid];
                    $insertCount++;
                }
            }
        }

        /* ---------- Process Submitted Permissions ---------- */
        foreach ($postedPerms as $menuId => $flags) {

            $menuId = (int)$menuId;

            if (!isset($menuMeta[$menuId])) {
                continue;
            }

            $meta = $menuMeta[$menuId];
            $isParent = ((int)$meta['Parent_Flag'] == 1);
            $isStandaloneParent = ($isParent && !empty($meta['Route']));
            $isParentWithChildren = ($isParent && empty($meta['Route']));
            $isChild = !$isParent;

            if ($isApp && $isParentWithChildren) {
                continue;
            }

            if (!$isApp && $isParent) {
                continue;
            }

            // Normalize flags
            $cols = [];
            foreach ($flags as $flag => $v) {
                $cols[$flag] = 1;
            }

            /* ----- UPDATE ----- */
            if (isset($existing[$menuId])) {

                // No flags selected → delete child mapping
                if (empty($cols)) {
                    mysqli_query($link, "
                        DELETE FROM role_menu_map
                        WHERE Role_Id = $roleId
                          AND Menu_Id = $menuId
                    ");
                    $deleteCount++;
                    continue;
                }

                $set = [];

                // Set checked flags = 1
                foreach ($cols as $c => $v) {
                    $set[] = "$c = 1";
                }

                // Unset unchecked flags = 0
                foreach ($existing[$menuId] as $c => $v) {
                    if (strpos($c, 'can_') === 0 && !isset($cols[$c])) {
                        $set[] = "$c = 0";
                    }
                }

                mysqli_query($link, "
                    UPDATE role_menu_map
                    SET " . implode(',', $set) . ",
                        Updated_By = '$admin',
                        Updated_On = NOW()
                    WHERE Role_Id = $roleId
                      AND Menu_Id = $menuId
                ");
                $updateCount++;
            } else {

                // No flags selected → nothing to insert
                if (empty($cols)) {
                    continue;
                }

                /* ----- INSERT ----- */
                $columns = ['Role_Id', 'Menu_Id', 'Created_By', 'Created_On'];
                $values  = [$roleId, $menuId, "'$admin'", 'NOW()'];

                foreach ($cols as $c => $v) {
                    $columns[] = $c;
                    $values[]  = 1;
                }

                mysqli_query($link, "
                    INSERT INTO role_menu_map
                    (" . implode(',', $columns) . ")
                    VALUES
                    (" . implode(',', $values) . ")
                ");
                $insertCount++;
            }

            if ($isApp && $isChild) {
                $parentId = (int)$meta['Par_Menu_Id'];
                if ($parentId > 0 && isset($menuMeta[$parentId])) {
                    $parentMeta = $menuMeta[$parentId];
                    $isParentWithChildrenParent = ((int)$parentMeta['Parent_Flag'] == 1 && empty($parentMeta['Route']));

                    if ($isParentWithChildrenParent) {
                        $parentMapQ = mysqli_query($link, "
                            SELECT COUNT(*) AS cnt
                            FROM role_menu_map
                            WHERE Role_Id = $roleId
                              AND Menu_Id = $parentId
                        ");
                        $parentMapCnt = (int)mysqli_fetch_assoc($parentMapQ)['cnt'];
                        if ($parentMapCnt === 0) {
                            mysqli_query($link, "
                                INSERT INTO role_menu_map
                                (Role_Id, Menu_Id, Created_By, Created_On)
                                VALUES
                                ($roleId, $parentId, '$admin', NOW())
                            ");
                            $existing[$parentId] = ['Menu_Id' => $parentId];
                            $insertCount++;
                        }
                    }
                }
            }
        }

        /* ---------- Delete Removed Mappings ---------- */
        foreach ($existing as $menuId => $row) {

            if (!isset($menuMeta[$menuId])) {
                continue;
            }

            $meta = $menuMeta[$menuId];
            $isParent = ((int)$meta['Parent_Flag'] == 1);
            $isParentWithChildren = ($isParent && empty($meta['Route']));

            if (!$isApp && $isParent) {
                continue;
            }

            if ($isApp && $isParentWithChildren) {
                continue;
            }

            if (!isset($postedPerms[$menuId])) {
                mysqli_query($link, "
                    DELETE FROM role_menu_map
                    WHERE Role_Id = $roleId
                      AND Menu_Id = $menuId
                ");
                $deleteCount++;
            }
        }

        if ($isApp) {
            foreach ($menuMeta as $parentId => $meta) {
                $isParentWithChildren = ((int)$meta['Parent_Flag'] == 1 && empty($meta['Route']));
                if (!$isParentWithChildren) {
                    continue;
                }

                $childCountQ = mysqli_query($link, "
                    SELECT COUNT(*) AS cnt
                    FROM role_menu_map rm
                    JOIN menus m ON rm.Menu_Id = m.Menu_Id
                    WHERE rm.Role_Id = $roleId
                    AND m.Par_Menu_Id = $parentId
                    AND m.Platform_Type = 'App'
                ");
                $childCount = (int)mysqli_fetch_assoc($childCountQ)['cnt'];

                if ($childCount === 0) {
                    mysqli_query($link, "
                        DELETE FROM role_menu_map
                        WHERE Role_Id = $roleId
                          AND Menu_Id = $parentId
                    ");
                    $deleteCount++;
                }
            }
        }

        mysqli_commit($link);

        /* ---------- Increment Permission Version (App Only) ---------- */
        if ($isApp && ($insertCount > 0 || $updateCount > 0 || $deleteCount > 0)) {

            mysqli_query($link, "
                        UPDATE roles
                        SET Permission_Version = Permission_Version + 1
                        WHERE Role_Id = $roleId
                        ");
        }

        $_SESSION['success_msg'] =
            "Permissions saved successfully for role <b>$roleName</b>. "
            . "Inserted: $insertCount, "
            . "Updated: $updateCount, "
            . "Deleted: $deleteCount.";


        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {

        mysqli_rollback($link);
        $_SESSION['error_msgs'][] = "Failed to update permissions";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Boxiocns CDN Link -->
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 1350px;
        max-height: 500px;
        margin-left: 8%;
        overflow-x: scroll;
    }

    @media print {
        * {
            display: none;
        }

        #table-container {
            display: block;
        }
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
        }
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    span[title],
    span[title] *,
    div[title],
    div[title] *,
    .btn-wrapper[title],
    .btn-wrapper[title] * {
        cursor: not-allowed !important;
    }

    input:disabled,
    button:disabled {
        opacity: 0.6;
    }

    .legend {
        display: flex;
        flex-direction: column;
    }

    .table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #212529;
    }
</style>

<body>
    <?php include 'sidebar.php'; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success mt-3" style="margin-left:8%">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger mt-3" style="margin-left:8%">
            <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
    <?php endif; ?>


    <div class="container mt-5" style="margin-left: 8%;">

        <form method="post" class="mb-3">
            <div class="row justify-content-center">
                <div class="col-md-3">
                    <select name="Platform_Type" id="platform_type" class="form-select" required>
                        <option value="" selected disabled>-- Select Platform Type --</option>
                        <option value="Web" <?= isset($platform_type) && $platform_type == 'Web' ? 'selected' : '' ?>>Web</option>
                        <option value="App" <?= isset($platform_type) && $platform_type == 'App' ? 'selected' : '' ?>>App</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="Login_Type" id="login_type" class="form-select" required>
                        <option value="" selected disabled>-- Select Login Type --</option>
                        <option value="Admin" <?= isset($login_type) && $login_type == 'Admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="Faculty" <?= isset($login_type) && $login_type == 'Faculty' ? 'selected' : '' ?>>Faculty</option>
                        <option value="Student" <?= isset($login_type) && $login_type == 'Student' ? 'selected' : '' ?>>Student</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="Role_Status" id="role_status" class="form-select" required>
                        <option value="A" <?= isset($status) && $status == 'A' ? 'selected' : '' ?>>Active</option>
                        <option value="I" <?= isset($status) && $status == 'I' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', 106)) { ?>
                        title="You don't have permission to view roles"
                        <?php } ?>>
                        <button name="ShowRoles" class="btn btn-primary" <?php echo !can('view', 106) ? 'disabled' : ''; ?>>
                            Show Roles
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php if (!empty($roles)): ?>

        <form method="post">
            <input type="hidden" name="Role_Status"
                value="<?= htmlspecialchars($_POST['Role_Status'] ?? 'A') ?>">
            <input type="hidden" name="Login_Type"
                value="<?= $login_type ?>">
            <input type="hidden" name="Platform_Type"
                value="<?= htmlspecialchars($_POST['Platform_Type'] ?? '') ?>">
            <div class="container table-container" id="table-container">

                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Select</th>
                            <th>Role Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php while ($r = mysqli_fetch_assoc($roles)): ?>
                            <tr class="<?= ($selectedRoleId == $r['Role_Id']) ? 'table-primary' : '' ?>">
                                <td class="text-center">
                                    <input type="radio"
                                        class="form-check-input"
                                        name="Role_Id"
                                        value="<?= $r['Role_Id'] ?>"
                                        <?= ($selectedRoleId == $r['Role_Id']) ? 'checked' : '' ?>
                                        required>
                                </td>
                                <td><?= htmlspecialchars($r['Role_Name']) ?></td>
                                <td>
                                    <?= $r['Active_Flag']
                                        ? '<span class="badge bg-success">Active</span>'
                                        : '<span class="badge bg-secondary">Inactive</span>' ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <div class="btn-wrapper"
                            <?php if (!can('view', MENU_ID)) { ?>
                            title="You don't have permission to view mappings"
                            <?php } ?>>
                            <button name="ShowMappings" class="btn btn-success" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>
                                Show Mappings
                            </button>
                        </div>
                        <?php if (!empty($roles)): ?>
                            <div class="btn-wrapper"
                                <?php if (!can('custom1', MENU_ID)) { ?>
                                title="You don't have permission to copy mappings"
                                <?php } ?>>
                                <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#copyRoleModal"
                                    <?php echo !can('custom1', MENU_ID) ? 'disabled' : ''; ?>>
                                    Copy Mappings
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <?php if (!empty($selectedRole)): ?>
        <div class="container">
            <div class="alert alert-info mt-3" style="margin-left: 8%;">
                <strong>Managing permissions for:</strong>
                <?= htmlspecialchars($selectedRole['Role_Name']) ?>
                <?= !$selectedRole['Active_Flag'] ? '(Inactive - Read Only)' : '' ?>
            </div>
        </div>
        <div class="container">
            <div class="alert alert-info mt-3" style="margin-left: 8%;">
                <strong>Permissions Legend:</strong><br>
                <div class="legend">
                    <span>V : View</span>
                    <span>C : Create/Insert</span>
                    <span>U : Update</span>
                    <span>D : Delete</span>
                    <span>P : Print</span>
                    <span>E : Export Excel</span>
                </div>
            </div>
        </div>

    <?php endif; ?>

    <!-- Mappings Table -->
    <?php if (isset($parentMenus) && mysqli_num_rows($parentMenus) > 0): ?>
        <?php if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view mappings');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        ?>
        <?php
        $flags = [
            'can_view'   => 'V',
            'can_create' => 'C',
            'can_update' => 'U',
            'can_delete' => 'D',
            'can_print'  => 'P',
            'can_export' => 'E',
            'can_custom1' => 'C1',
            'can_custom2' => 'C2',
            'can_custom3' => 'C3',
            'can_custom4' => 'C4',
        ];
        ?>

        <form method="post">
            <input type="hidden" name="Role_Id" value="<?= $selectedRoleId ?>">
            <input type="hidden" name="Role_Status"
                value="<?= htmlspecialchars($_POST['Role_Status']) ?>">
            <input type="hidden" name="Platform_Type"
                value="<?= htmlspecialchars($_POST['Platform_Type'] ?? '') ?>">

            <div class="container table-container mt-4">

                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>
                                <span <?= !can('create', MENU_ID) ? 'title="You don\'t have permission to update mappings"' : '' ?>>
                                    <input type="checkbox" class="form-check-input" id="sa-global" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="ro-global" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>RO</button>
                                    <button type="button" class="btn btn-sm btn-outline-success" id="fa-global" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>FA</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="cc-global" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>CC</button>
                                </span>
                                &nbsp;Menu
                            </th>
                            <?php foreach ($flags as $f): ?>
                                <th><?= $f ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($parentMenus): ?>
                            <?php while ($p = mysqli_fetch_assoc($parentMenus)): ?>
                                <?php
                                $isStandaloneParent = ($isApp && !empty($p['Route']));
                                $isParentWithChildren = ($isApp && empty($p['Route']));
                                ?>
                                <?php if (!$isStandaloneParent): ?>
                                    <tr class="table-secondary">
                                        <td>
                                            <span <?= !can('create', MENU_ID) ? 'title="You don\'t have permission to update mappings"' : '' ?>>
                                                <input type="checkbox" class="form-check-input sa-parent" data-parent="<?= $p['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>

                                                <button type="button" class="btn btn-sm btn-outline-secondary ro-parent" data-parent="<?= $p['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>RO</button>

                                                <button type="button" class="btn btn-sm btn-outline-success fa-parent" data-parent="<?= $p['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>FA</button>

                                                <button type="button" class="btn btn-sm btn-outline-danger cc-parent" data-parent="<?= $p['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>CC</button>
                                            </span>

                                            <b><?= htmlspecialchars($p['Display_Name']) ?></b>
                                        </td>

                                        <?php foreach ($flags as $f): ?>
                                            <td class="text-center">—</td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endif; ?>
                                <?php
                                if ($isStandaloneParent) {
                                    $childMenus = mysqli_query($link, "
                                        SELECT Menu_Id, Menu_Name,Display_Name
                                        FROM menus
                                        WHERE Active_Flag = 1
                                        AND Menu_Id = {$p['Menu_Id']}
                                        AND Login_Type = '$login_type'
                                        AND Platform_Type = '$platform_type'
                                        ORDER BY Sequence_Id
                                    ");
                                } else {
                                    $childMenus = mysqli_query($link, "
                                        SELECT Menu_Id, Menu_Name,Display_Name
                                        FROM menus
                                        WHERE Active_Flag = 1
                                        AND Parent_Flag = 0
                                        AND Par_Menu_Id = {$p['Menu_Id']}
                                        AND Login_Type = '$login_type'
                                        AND Platform_Type = '$platform_type'
                                        ORDER BY Sequence_Id
                                    ");
                                }
                                ?>

                                <?php while ($c = mysqli_fetch_assoc($childMenus)): ?>
                                    <?php $map = $roleMaps[$c['Menu_Id']] ?? []; ?>

                                    <tr <?php if ($isStandaloneParent) {
                                            echo 'class="table-secondary"';
                                        } ?>>
                                        <td>
                                            <?php if (!$isStandaloneParent) {
                                                echo '&nbsp;&nbsp;&nbsp;';
                                            } ?>
                                            <span <?= !can('create', MENU_ID) ? 'title="You don\'t have permission to update mappings"' : '' ?>>
                                                <input type="checkbox" class="form-check-input sa-child" data-parent="<?= $p['Menu_Id'] ?>" data-menu="<?= $c['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>

                                                <button type="button" class="btn btn-sm btn-outline-secondary ro-child" data-menu="<?= $c['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>RO</button>

                                                <button type="button" class="btn btn-sm btn-outline-success fa-child" data-menu="<?= $c['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>FA</button>

                                                <button type="button" class="btn btn-sm btn-outline-danger cc-child" data-menu="<?= $c['Menu_Id'] ?>" <?= !can('create', MENU_ID) ? 'disabled' : '' ?>>CC</button>
                                            </span>

                                            <?= htmlspecialchars($c['Display_Name']) ?>
                                        </td>

                                        <?php foreach ($flags as $k => $lbl): ?>
                                            <td class="text-center">
                                                <?php if (!in_array($lbl, ['C1', 'C2', 'C3', 'C4'])): ?>

                                                    <!-- CORE FLAGS -->
                                                    <span
                                                        <?= !(can('create', MENU_ID) && $isRoleActive)
                                                            ? 'title="You don\'t have permission to update this permission"' : '' ?>>
                                                        <input type="checkbox"
                                                            class="form-check-input perm-checkbox perm-core"
                                                            data-menu="<?= $c['Menu_Id'] ?>"
                                                            data-parent="<?= $p['Menu_Id'] ?>"
                                                            data-flag="<?= $k ?>"
                                                            name="perm[<?= $c['Menu_Id'] ?>][<?= $k ?>]"
                                                            <?= !empty($map[$k]) ? 'checked' : '' ?>
                                                            <?= (can('create', MENU_ID) && $isRoleActive) ? '' : 'disabled' ?>>
                                                    </span>

                                                <?php else: ?>

                                                    <!-- CUSTOM FLAGS -->
                                                    <span
                                                        <?= !(can('create', MENU_ID) && $isRoleActive && $_SESSION['Role_Name'] === 'System Admin')
                                                            ? 'title="Custom permissions can be edited only by System Admin"' : '' ?>>
                                                        <input type="checkbox"
                                                            class="form-check-input perm-checkbox perm-custom"
                                                            data-menu="<?= $c['Menu_Id'] ?>"
                                                            data-parent="<?= $p['Menu_Id'] ?>"
                                                            data-flag="<?= $k ?>"
                                                            name="perm[<?= $c['Menu_Id'] ?>][<?= $k ?>]"
                                                            <?= !empty($map[$k]) ? 'checked' : '' ?>
                                                            <?= (can('create', MENU_ID) && $isRoleActive && $_SESSION['Role_Name'] === 'System Admin')
                                                                ? '' : 'disabled' ?>>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endwhile; ?>

                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php
                        if (!$isRoleActive) {
                            echo 'title="Inactive roles cannot be modified"';
                        } elseif (!can('create', MENU_ID)) {
                            echo 'title="You don\'t have permission to save mappings"';
                        }
                        ?>>
                        <button name="SaveMappings"
                            class="btn btn-success mt-3"
                            <?php
                            if (!$isRoleActive || !can('create', MENU_ID)) {
                                echo 'disabled';
                            }
                            ?>>
                            Save Mappings
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>

    <!-- ================= COPY ROLE MAPPINGS MODAL ================= -->
    <div class="modal fade" id="copyRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="post" id="copyRoleForm">

                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title">Copy Role Mappings</h5>
                        <button type="button" class="btn-close"
                            data-bs-dismiss="modal"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">

                        <div class="alert alert-warning">
                            <strong>Warning:</strong>
                            This will <u>replace all permissions</u> of the target role.
                        </div>

                        <input type="hidden" name="Login_Type"
                            value="<?= htmlspecialchars($login_type) ?>">
                        <input type="hidden" name="Platform_Type"
                            value="<?= htmlspecialchars($platform_type) ?>">

                        <!-- Source Role -->
                        <div class="mb-3">
                            <label class="form-label">Source Role</label>
                            <select name="Source_Role_Id" class="form-select" required>
                                <option value="">-- Select Source Role --</option>

                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['Role_Id'] ?>">
                                        <?= htmlspecialchars($r['Role_Name']) ?>
                                        <?= !$r['Active_Flag'] ? ' (Inactive)' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Can be Active or Inactive
                            </small>
                        </div>

                        <!-- Target Role -->
                        <div class="mb-3">
                            <label class="form-label">Target Role</label>
                            <select name="Target_Role_Id" class="form-select" required>
                                <option value="">-- Select Target Role --</option>

                                <?php foreach ($roles as $r): ?>
                                    <?php if ((int)$r['Active_Flag'] === 1): ?>
                                        <option value="<?= $r['Role_Id'] ?>">
                                            <?= htmlspecialchars($r['Role_Name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Must be Active
                            </small>
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <div class="btn-wrapper"
                            <?php if (!can('custom1', MENU_ID)) { ?>
                            title="You don't have permission to copy mappings"
                            <?php } ?>>
                            <button type="submit"
                                name="CopyMappings"
                                class="btn btn-primary" <?php echo !can('custom1', MENU_ID) ? 'disabled' : ''; ?>>
                                Copy
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->

    <!-- Global Const Variables for can_insert,can_update,can_soft_delete,can_hard_delete -->
    <script>
        const CAN_COPY = <?= can('custom1', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Handle Select All (all scopes) -->
    <script>
        /* ===============================
            GLOBAL SELECT-ALL (HORIZONTAL)
        =============================== */
        document.getElementById('sa-global')?.addEventListener('change', function() {
            const checked = this.checked;

            document.querySelectorAll('.perm-core').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = checked;
                }
            });

            // sync parent + child row checkboxes
            document.querySelectorAll('.sa-parent, .sa-child').forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = checked;
                }
            });
        });

        /* ===============================
           PARENT-LEVEL SELECT-ALL
           =============================== */
        document.querySelectorAll('.sa-parent').forEach(parentCb => {

            parentCb.addEventListener('change', function() {
                const parentId = this.dataset.parent;
                const checked = this.checked;

                // toggle all children core permissions under this parent
                document.querySelectorAll(
                    `.perm-core[data-parent="${parentId}"]`
                ).forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = checked;
                    }
                });

                // sync child row selectors
                document.querySelectorAll(
                    `.sa-child[data-parent="${parentId}"]`
                ).forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = checked;
                    }
                });
            });

        });

        /* ===============================
           CHILD-LEVEL SELECT-ALL
           =============================== */
        document.querySelectorAll('.sa-child').forEach(childCb => {

            childCb.addEventListener('change', function() {
                const menuId = this.dataset.menu;
                const checked = this.checked;

                // toggle all core permissions in this row
                document.querySelectorAll(
                    `.perm-core[data-menu="${menuId}"]`
                ).forEach(cb => {
                    if (!cb.disabled) {
                        cb.checked = checked;
                    }
                });
            });

        });

        /* ===============================
           AUTO-SYNC SELECT-ALL STATES
           =============================== */
        document.querySelectorAll('.perm-core').forEach(cb => {

            cb.addEventListener('change', function() {

                const menuId = this.dataset.menu;
                const parentId = this.dataset.parent;

                // ---- sync child-level checkbox ----
                const rowPerms = document.querySelectorAll(
                    `.perm-core[data-menu="${menuId}"]:not(:disabled)`
                );
                const rowChecked = document.querySelectorAll(
                    `.perm-core[data-menu="${menuId}"]:checked:not(:disabled)`
                );

                const childSA = document.querySelector(
                    `.sa-child[data-menu="${menuId}"]`
                );
                if (childSA) {
                    childSA.checked = rowPerms.length === rowChecked.length;
                }

                // ---- sync parent-level checkbox ----
                const parentPerms = document.querySelectorAll(
                    `.perm-core[data-parent="${parentId}"]:not(:disabled)`
                );
                const parentChecked = document.querySelectorAll(
                    `.perm-core[data-parent="${parentId}"]:checked:not(:disabled)`
                );

                const parentSA = document.querySelector(
                    `.sa-parent[data-parent="${parentId}"]`
                );
                if (parentSA) {
                    parentSA.checked = parentPerms.length === parentChecked.length;
                }

                // ---- sync global checkbox ----
                const allPerms = document.querySelectorAll(
                    `.perm-core:not(:disabled)`
                );
                const allChecked = document.querySelectorAll(
                    `.perm-core:checked:not(:disabled)`
                );

                const globalSA = document.getElementById('sa-global');
                if (globalSA) {
                    globalSA.checked = allPerms.length === allChecked.length;
                }
            });

        });
    </script>

    <!-- Handle RO,FC,CC Buttons (all scopes) -->
    <script>
        /* ===============================
            FLAG DEFINITIONS (MUST BE FIRST)
        =============================== */
        const READ_ONLY_FLAGS = ['can_view', 'can_print', 'can_export'];
        const FULL_ACCESS_FLAGS = [
            'can_view',
            'can_create',
            'can_update',
            'can_delete',
            'can_print',
            'can_export'
        ];

        /* ===============================
           HELPER FUNCTIONS
           =============================== */
        function setFlags(selector, flags, checked) {
            document.querySelectorAll(selector).forEach(cb => {
                if (!cb.disabled && flags.includes(cb.dataset.flag)) {
                    cb.checked = checked;
                }
            });
        }

        function clearCore(selector) {
            document.querySelectorAll(selector).forEach(cb => {
                if (!cb.disabled && cb.classList.contains('perm-core')) {
                    cb.checked = false;
                }
            });
        }

        function clearCustom(selector) {
            document.querySelectorAll(selector).forEach(cb => {
                if (!cb.disabled && cb.classList.contains('perm-custom')) {
                    cb.checked = false;
                }
            });
        }

        /* ===============================
           GLOBAL BUTTONS
           =============================== */
        document.getElementById('ro-global')?.addEventListener('click', () => {
            clearCore('.perm-core');
            setFlags('.perm-core', READ_ONLY_FLAGS, true);
        });


        document.getElementById('fa-global')?.addEventListener('click', () => {
            setFlags('.perm-core', FULL_ACCESS_FLAGS, true);
        });

        document.getElementById('cc-global')?.addEventListener('click', () => {
            clearCustom('.perm-custom');
        });

        /* ===============================
           PARENT-LEVEL BUTTONS
           =============================== */
        document.querySelectorAll('.ro-parent').forEach(btn => {
            btn.addEventListener('click', () => {
                const pid = btn.dataset.parent;
                clearCore(`.perm-core[data-parent="${pid}"]`);
                setFlags(`.perm-core[data-parent="${pid}"]`, READ_ONLY_FLAGS, true);
            });
        });


        document.querySelectorAll('.fa-parent').forEach(btn => {
            btn.addEventListener('click', () => {
                const pid = btn.dataset.parent;
                setFlags(`.perm-core[data-parent="${pid}"]`, FULL_ACCESS_FLAGS, true);
            });
        });

        document.querySelectorAll('.cc-parent').forEach(btn => {
            btn.addEventListener('click', () => {
                const pid = btn.dataset.parent;
                clearCustom(`.perm-custom[data-parent="${pid}"]`);
            });
        });

        /* ===============================
           CHILD-LEVEL BUTTONS
           =============================== */
        document.querySelectorAll('.ro-child').forEach(btn => {
            btn.addEventListener('click', () => {
                const mid = btn.dataset.menu;
                clearCore(`.perm-core[data-menu="${mid}"]`);
                setFlags(`.perm-core[data-menu="${mid}"]`, READ_ONLY_FLAGS, true);
            });
        });

        document.querySelectorAll('.fa-child').forEach(btn => {
            btn.addEventListener('click', () => {
                const mid = btn.dataset.menu;
                setFlags(`.perm-core[data-menu="${mid}"]`, FULL_ACCESS_FLAGS, true);
            });
        });

        document.querySelectorAll('.cc-child').forEach(btn => {
            btn.addEventListener('click', () => {
                const mid = btn.dataset.menu;
                clearCustom(`.perm-custom[data-menu="${mid}"]`);
            });
        });
    </script>

    <!-- Handle Copy Validations -->
    <script>
        document.getElementById('copyRoleForm').addEventListener('submit', function(e) {
            if (!CAN_COPY) {
                e.preventDefault();
                alert("You do not have permission to copy mappings");
                return;
            }

            const sourceSelect = this.querySelector('[name="Source_Role_Id"]');
            const targetSelect = this.querySelector('[name="Target_Role_Id"]');

            const source = sourceSelect.value;
            const target = targetSelect.value;

            // Remove old error
            const oldError = this.querySelector('.copy-error');
            if (oldError) oldError.remove();

            // Required validation
            if (!source || !target) {
                e.preventDefault();
                showCopyError(this, 'Please select both Source and Target roles.');
                return;
            }

            // Source ≠ Target validation
            if (source === target) {
                e.preventDefault();
                showCopyError(this, 'Source role and Target role must be different.');
                return;
            }

            // Final confirmation
            const sourceName = sourceSelect.options[sourceSelect.selectedIndex].text;
            const targetName = targetSelect.options[targetSelect.selectedIndex].text;

            const ok = confirm(
                `You are about to replace ALL permissions of:\n\n` +
                `Target Role: ${targetName}\n\n` +
                `Using permissions from:\n` +
                `Source Role: ${sourceName}\n\n` +
                `Do you want to continue?`
            );

            if (!ok) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            return;

            // If OK → form submits normally
        });

        function showCopyError(form, message) {
            const div = document.createElement('div');
            div.className = 'alert alert-danger mt-2 copy-error';
            div.innerText = message;
            form.querySelector('.modal-body').appendChild(div);
        }
    </script>
</body>

</html>