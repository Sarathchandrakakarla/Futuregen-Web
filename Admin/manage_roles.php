<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 106);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view this report');
        location.replace('/Futuregen/Admin/admin_dashboard.php')</script>";
    exit;
}

error_reporting(0);
?>

<?php
$admin = $_SESSION['Admin_Id_No'];
$errors = [];
$success = '';
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

/* ---------------- CREATE ROLE ---------------- */
if (isset($_POST['CreateRole'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to create role');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $login_type = trim($_POST['Login_Type'] ?? '');
    $role_name = trim($_POST['Role_Name']);
    $role_desc = trim($_POST['Role_Desc']);

    if ($login_type === '') {
        $errors[] = "Login Type is required";
    }

    if ($role_name === '') {
        $errors[] = "Role Name is required";
    }

    if (empty($errors)) {

        $chk = mysqli_query(
            $link,
            "SELECT 1 FROM roles WHERE Role_Name = '$role_name'"
        );

        if (mysqli_num_rows($chk) > 0) {
            $errors[] = "Role already exists";
        } else {
            mysqli_query(
                $link,
                "INSERT INTO roles (Role_Name, Role_Desc, Login_Type, Created_By)
                 VALUES ('$role_name', '$role_desc', '$login_type', '$admin')"
            );
            $success = "Role created successfully";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
    }
}

/* ---------------- UPDATE ROLE ---------------- */
if (isset($_POST['UpdateRole'])) {
    if (!can('update', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to update role');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $role_id   = (int)$_POST['Role_Id'];
    $role_name = trim($_POST['Role_Name']);
    $role_desc = trim($_POST['Role_Desc']);

    if ($role_id <= 0) {
        $errors[] = "Invalid Role";
    }

    if ($role_name === '') {
        $errors[] = "Role Name is required";
    }

    if (empty($errors)) {

        $chk = mysqli_query(
            $link,
            "SELECT 1 FROM roles 
             WHERE Role_Name = '$role_name' 
               AND Role_Id <> $role_id"
        );

        if (mysqli_num_rows($chk) > 0) {
            $errors[] = "Role name already exists";
        } else {

            mysqli_query(
                $link,
                "UPDATE roles
                 SET Role_Name = '$role_name',
                     Role_Desc = '$role_desc',
                     Updated_By = '$admin',
                     Updated_On = NOW()
                 WHERE Role_Id = $role_id"
            );

            $success = "Role updated successfully";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

/* ---------------- TOGGLE ACTIVE ---------------- */
if (isset($_POST['ToggleRole'])) {
    if (!can('delete', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to deactivate/activate role');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $role_id = (int)$_POST['Role_Id'];
    $status  = (int)$_POST['Active_Flag'];

    mysqli_query(
        $link,
        "UPDATE roles
         SET Active_Flag = $status,
             Updated_By = '$admin',
             Updated_On = NOW()
         WHERE Role_Id = $role_id"
    );
    $_SESSION['success_msg'] = "Role " . ($status ? 'Activated' : 'Inactivated');
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ---------------- HARD DELETE ROLE ---------------- */
if (isset($_POST['DeleteRole'])) {
    if (!can('custom1', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to hard delete role');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $role_id = (int)$_POST['Role_Id'];

    if ($role_id <= 0) {
        $_SESSION['error_msgs'][] = "Invalid Role";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    mysqli_begin_transaction($link);

    try {

        // Ensure role is inactive
        $chk = mysqli_query(
            $link,
            "SELECT Active_Flag FROM roles WHERE Role_Id = $role_id FOR UPDATE"
        );
        $r = mysqli_fetch_assoc($chk);

        if (!$r || (int)$r['Active_Flag'] === 1) {
            throw new Exception("Only inactive roles can be deleted");
        }

        /* // Unassign users
        mysqli_query(
            $link,
            "UPDATE users SET Role_Id = NULL WHERE Role_Id = $role_id"
        ); */

        // Delete role-menu mappings
        mysqli_query(
            $link,
            "DELETE FROM role_menu_map WHERE Role_Id = $role_id"
        );

        // Delete role
        mysqli_query(
            $link,
            "DELETE FROM roles WHERE Role_Id = $role_id"
        );

        mysqli_commit($link);
        $_SESSION['success_msg'] = "Role deleted permanently";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } catch (Exception $e) {
        mysqli_rollback($link);
        $_SESSION['error_msgs'][] = $e->getMessage();
    }
}

/* ---------------- FETCH ROLES ---------------- */
$roles = mysqli_query(
    $link,
    "SELECT * FROM roles ORDER BY Role_Id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
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

    td span[title],
    td span[title] * {
        cursor: not-allowed !important;
    }

    button:disabled {
        opacity: 0.6;
    }
</style>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container mt-5" style="margin-left: 8%;">

        <div class="d-flex justify-content-between mb-3">
            <h4>Manage Roles</h4>
            <div class="btn-wrapper"
                <?php if (!can('create', MENU_ID)) { ?>
                title="You don't have permission to create role"
                <?php } ?>>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roleModal" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>
                    <i class="bx bx-plus"></i> Add Role
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Roles Table -->
    <div class="container table-container" id="table-container">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Role Name</th>
                    <th>Description</th>
                    <th>Login Type</th>
                    <th>Status</th>
                    <th>Created Date</th>
                    <th>Created By</th>
                    <th>Updated Date</th>
                    <th>Updated By</th>
                    <th width="120">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1;
                while ($r = mysqli_fetch_assoc($roles)): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= htmlspecialchars($r['Role_Name']) ?></td>
                        <td><?= htmlspecialchars($r['Role_Desc']) ?></td>
                        <td><?= htmlspecialchars($r['Login_Type']) ?></td>
                        <td>
                            <?= $r['Active_Flag'] ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-secondary">Inactive</span>' ?>
                        </td>
                        <td>
                            <?= htmlspecialchars(
                                date('d-m-Y h:i:s A', strtotime($r['Created_On']))
                            ) ?>
                        </td>
                        <td><?= htmlspecialchars($r['Created_By']) ?></td>
                        <td>
                            <?= $r['Updated_On'] ? htmlspecialchars(
                                date('d-m-Y h:i:s A', strtotime($r['Updated_On']))
                            ) : '' ?>
                        </td>
                        <td><?= htmlspecialchars($r['Updated_By']) ?></td>
                        <td>

                            <!-- ===== EDIT ===== -->
                            <span
                                <?php if (!$r['Active_Flag']) { ?>
                                title="Inactive roles cannot be edited"
                                <?php } elseif (!can('update', MENU_ID)) { ?>
                                title="You don't have permission to edit roles"
                                <?php } ?>>
                                <button type="button"
                                    class="btn btn-sm btn-info mb-1 w-100"
                                    data-bs-toggle="modal"
                                    data-bs-target="#roleModal"
                                    data-id="<?= $r['Role_Id'] ?>"
                                    data-name="<?= htmlspecialchars($r['Role_Name']) ?>"
                                    data-desc="<?= htmlspecialchars($r['Role_Desc']) ?>"
                                    data-logintype="<?= htmlspecialchars($r['Login_Type']) ?>"
                                    <?= (!$r['Active_Flag'] || !can('update', MENU_ID)) ? 'disabled' : '' ?>>
                                    Edit
                                </button>
                            </span>

                            <!-- ===== ACTIVATE / DEACTIVATE ===== -->
                            <?php if (!in_array($r['Role_Name'], ['System Admin'])): ?>
                                <span
                                    <?php if (!can('delete', MENU_ID)) { ?>
                                    title="You don't have permission to change role status"
                                    <?php } ?>>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="Role_Id" value="<?= $r['Role_Id'] ?>">
                                        <input type="hidden" name="Active_Flag" value="<?= $r['Active_Flag'] ? 0 : 1 ?>">
                                        <button name="ToggleRole"
                                            class="btn btn-sm w-100 <?= $r['Active_Flag'] ? 'btn-warning' : 'btn-success' ?>"
                                            <?= !can('delete', MENU_ID) ? 'disabled' : '' ?>
                                            onclick="return confirm('Confirm to <?= $r['Active_Flag'] ? 'Deactivate' : 'Activate' ?>?')">
                                            <?= $r['Active_Flag'] ? 'Deactivate' : 'Activate' ?>
                                        </button>
                                    </form>
                                </span>
                            <?php endif; ?>

                            <!-- ===== DELETE (ONLY WHEN INACTIVE) ===== -->
                            <?php if (!$r['Active_Flag']): ?>
                                <span
                                    <?php if (!can('custom1', MENU_ID)) { ?>
                                    title="You don't have permission to delete roles"
                                    <?php } ?>>
                                    <button type="button"
                                        class="btn btn-sm btn-danger w-100 mt-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteRoleModal"
                                        data-id="<?= $r['Role_Id'] ?>"
                                        data-name="<?= htmlspecialchars($r['Role_Name']) ?>"
                                        <?= !can('custom1', MENU_ID) ? 'disabled' : '' ?>>
                                        Delete
                                    </button>
                                </span>
                            <?php endif; ?>

                        </td>

                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- CREATE/EDIT ROLE MODAL -->
    <div class="modal fade" id="roleModal">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <input type="hidden" name="Role_Id" id="Role_Id">
                <input type="hidden" name="Form_Mode" id="Form_Mode" value="CREATE">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Create Role</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3" id="loginTypeBlock">
                        <label>Login Type *</label>
                        <select name="Login_Type" id="login_type" class="form-control" required>
                            <option value="">-- Select Login Type --</option>
                            <option value="Admin">Admin</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Role Name *</label>
                        <input type="text" name="Role_Name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="Role_Desc" class="form-control"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" id="modalSubmitBtn" name="CreateRole" class="btn btn-primary">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="deleteRoleModal">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <input type="hidden" name="Role_Id" id="Delete_Role_Id">

                <div class="modal-header">
                    <h5 class="modal-title text-danger">Delete Role Permanently</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>
                        You are about to permanently delete role:
                        <strong id="Delete_Role_Name"></strong>
                    </p>

                    <div class="form-check">
                        <input class="form-check-input confirm-check" type="checkbox">
                        <label class="form-check-label">
                            I understand this action is irreversible
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input confirm-check" type="checkbox">
                        <label class="form-check-label">
                            Delete all role-menu permission mappings
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input confirm-check" type="checkbox">
                        <label class="form-check-label">
                            Unassign this role from all users
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="btn-wrapper"
                        <?php if (!can('custom1', MENU_ID)) { ?>
                        title="You don't have permission to delete role "
                        <?php } ?>>
                        <button type="submit"
                            name="DeleteRole"
                            id="DeleteRoleBtn"
                            class="btn btn-danger"
                            disabled>
                            Delete Permanently
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->

    <!-- Global Const Variables for can_insert,can_update,can_soft_delete,can_hard_delete -->
    <script>
        const CAN_INSERT = <?= can('create', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_UPDATE = <?= can('update', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_HARD_DELETE = <?= can('custom1', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <script>
        const roleModal = document.getElementById('roleModal');

        roleModal.addEventListener('show.bs.modal', function(event) {

            const button = event.relatedTarget;

            const roleId = button.getAttribute('data-id');
            const roleName = button.getAttribute('data-name');
            const roleDesc = button.getAttribute('data-desc');
            const logintype = button.getAttribute('data-logintype');

            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('modalSubmitBtn');

            if (roleId) {
                if (!CAN_UPDATE) {
                    event.preventDefault();
                    alert("You do not have permission to update role");
                    return;
                }

                // EDIT MODE
                title.innerText = 'Edit Role';
                document.getElementById('login_type').disabled = true;
                document.getElementById('login_type').value = logintype;
                document.getElementById('Role_Id').value = roleId;
                document.querySelector('[name="Role_Name"]').value = roleName;
                document.querySelector('[name="Role_Desc"]').value = roleDesc;

                submitBtn.name = 'UpdateRole';
                submitBtn.innerText = 'Update';
            } else {
                if (!CAN_INSERT) {
                    event.preventDefault();
                    alert("You do not have permission to create role");
                    return;
                }
                // CREATE MODE
                title.innerText = 'Create Role';
                document.getElementById('login_type').disabled = false;
                document.getElementById('login_type').value = '';
                document.getElementById('loginTypeBlock').style.display = 'block';
                document.getElementById('Role_Id').value = '';
                document.querySelector('[name="Role_Name"]').value = '';
                document.querySelector('[name="Role_Desc"]').value = '';

                submitBtn.name = 'CreateRole';
                submitBtn.innerText = 'Save';
            }
        });

        const deleteModal = document.getElementById('deleteRoleModal');

        deleteModal.addEventListener('show.bs.modal', function(event) {
            if (!CAN_HARD_DELETE) {
                event.preventDefault();
                alert("You do not have permission to hard delete role");
                return;
            }
            const button = event.relatedTarget;
            document.getElementById('Delete_Role_Id').value =
                button.getAttribute('data-id');
            document.getElementById('Delete_Role_Name').innerText =
                button.getAttribute('data-name');

            document.querySelectorAll('.confirm-check').forEach(c => c.checked = false);
            document.getElementById('DeleteRoleBtn').disabled = true;
        });

        document.querySelectorAll('.confirm-check').forEach(cb => {
            if (!CAN_HARD_DELETE) {
                document.getElementById('DeleteRoleBtn').disabled = true;
                return;
            }
            cb.addEventListener('change', () => {
                const allChecked = [...document.querySelectorAll('.confirm-check')]
                    .every(c => c.checked);
                document.getElementById('DeleteRoleBtn').disabled = !allChecked;
            });
        });
    </script>

</body>

</html>