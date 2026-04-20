<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 93);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/sidebar-style.css" />
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
</head>

<style>
    body {
        background: #E4E9F7;
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    #hide {
        position: absolute;
        margin: -3% 26%;
        font-size: 25px;
        cursor: pointer;
        color: #1abc9c;
    }

    @media screen and (max-width:920px) {
        #hide {
            position: absolute;
            text-align: right;
            margin: -13% 74%;
            font-size: 20px;
        }
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>
    <div class="container col-lg-4">
        <div class="wrapper">
            <div class="title"><span>Add Admin User</span></div>
            <form action="" method="post" autocomplete="off">
                <div class="row" id="alert-row" style="display: none;">
                    <div class="alert alert-danger d-flex align-items-center" role="alert" id="alert-parent">
                        <div id="alert">
                            Alert
                        </div>
                    </div>
                </div>
                <div class="row">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="User Name" name="UserName" id="username" oninput="this.value = this.value.toUpperCase()" required>
                </div>
                <div class="row">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="Full Name" name="Full_Name" id="full_name" required>
                </div>
                <div class="row">
                    <i class="fas fa-phone"></i>
                    <input type="text" placeholder="Mobile Number" minlength="10" maxlength="10" name="Mobile" id="mobile" required>
                </div>
                <div class="row">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Password" id="password" name="Password" required>
                </div>
                <span class="fas fa-eye" id="hide"></span>
                <div class="row">
                    <i class="fas fa-lock"></i>
                    <input type="password" placeholder="Confirm Password" id="c_password" name="C_Password" required>
                </div>
                <div class="row">
                    <i class="fas fa-users"></i>
                    <select class="form-select" name="Role" id="role" style="padding-left: 60px;">
                        <option value="selectrole" selected disabled>-- Select Role --</option>
                        <?php
                        $roles_query = mysqli_query($link, "SELECT Role_Id, Role_Name FROM `roles` WHERE Login_Type = 'Admin'");
                        while ($roles_row = mysqli_fetch_assoc($roles_query)) {
                            echo '<option value="' . $roles_row['Role_Id'] . '">' . $roles_row['Role_Name'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="row button">
                    <div class="btn-wrapper <?php echo !can('create', MENU_ID) ? 'rbac-disabled' : ''; ?>"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to add admin user"
                        <?php } ?>>
                        <button type="submit"
                            class="btn"
                            name="Add"
                            id="add"
                            onclick="return confirm('Confirm to Add New Admin?')"
                            <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>
                            Add User
                        </button>
                    </div>
                </div>

                <div class="row button">
                    <div class="btn-wrapper <?php echo !can('update', MENU_ID) ? 'rbac-disabled' : ''; ?>"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update admin user"
                        <?php } ?>>
                        <button type="submit"
                            class="btn"
                            name="Update"
                            id="update"
                            onclick="return confirm('Confirm to Update Admin User?')"
                            <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>
                            Update User
                        </button>
                    </div>
                </div>

                <div class="row button">
                    <div class="btn-wrapper <?php echo !can('delete', MENU_ID) ? 'rbac-disabled' : ''; ?>"
                        <?php if (!can('delete', MENU_ID)) { ?>
                        title="You don't have permission to delete admin user"
                        <?php } ?>>
                        <button type="submit"
                            class="btn"
                            name="Delete"
                            id="delete"
                            onclick="return confirm('Confirm to Delete Admin User?')"
                            <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>
                            Delete User
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <?php
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    if (isset($_POST['Add'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert admin user');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $uid = validate($_POST['UserName']);
        $name = validate($_POST['Full_Name']);
        $mobile = validate($_POST['Mobile']);
        $password = validate($_POST['Password']);
        $c_password = validate($_POST['C_Password']);
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<script>document.getElementById('username').value = '" . $uid . "';
            document.getElementById('full_name').value = '" . $name . "';
            document.getElementById('mobile').value = '" . $mobile . "';
            document.getElementById('password').value = '" . $password . "';
            document.getElementById('c_password').value = '" . $c_password . "';</script>";
        if ($_POST['Role']) {
            $role = $_POST['Role'];
            echo "<script>document.getElementById('role').value = '" . $role . "';</script>";
            //Check if User Already Exists
            $check_sql = mysqli_query($link, "SELECT * FROM `admin` WHERE Admin_Id_No = '$uid'");
            if ($check_sql) {
                if (mysqli_num_rows($check_sql) != 0) {
                    echo "<script>alert('User Already Exists!!')</script>";
                } else {
                    $sql = "INSERT INTO `admin` VALUES('','$uid','$name','$mobile','$password','$pass_hash','$role')";
                    if (mysqli_query($link, $sql)) {
                        echo "<script>
                                alert_row = document.getElementById('alert-row');
                                alert = document.getElementById('alert');
                                alert_parent = document.getElementById('alert-parent');
                                alert_row.style.display = 'block';
                                alert_parent.classList.remove('alert-danger');
                                alert_parent.classList.add('alert-success');
                                alert.innerHTML = 'Successfully Added Admin User!!';
                            </script>";
                    } else {
                        echo "<script>
                                alert_row = document.getElementById('alert-row');
                                alert = document.getElementById('alert');
                                alert_parent = document.getElementById('alert-parent');
                                alert_row.style.display = 'block';
                                alert_parent.classList.remove('alert-success');
                                alert_parent.classList.add('alert-danger');
                                alert.innerHTML = 'User Insertion Failed due to SQL Error!!';
                            </script>";
                    }
                }
            } else {
                echo "<script>alert('User Checking Query Error!!')</script>";
            }
        } else {
            echo "<script>alert('Please Select Role!!')</script>";
        }
    }

    if (isset($_POST['Update'])) {
        if (!can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to update admin user');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $id = $_POST['UserName'];
        $name = validate($_POST['Full_Name']);
        $mobile = validate($_POST['Mobile']);
        $password = validate($_POST['Password']);
        $c_password = validate($_POST['C_Password']);
        $role = $_POST['Role'];
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "<script>document.getElementById('username').value = '" . $id . "';
        document.getElementById('full_name').value = '" . $name . "';
        document.getElementById('mobile').value = '" . $mobile . "';
        document.getElementById('password').value = '" . $password . "';
        document.getElementById('c_password').value = '" . $c_password . "';
        document.getElementById('role').value = '" . $role . "';</script>";

        //Check if User Already Exists
        $check_sql = mysqli_query($link, "SELECT * FROM `admin` WHERE Admin_Id_No = '$id' ");
        if ($check_sql) {
            if (mysqli_num_rows($check_sql) == 0) {
                echo "<script>alert('User Not Found!!')</script>";
            } else {
                $upd_sql = mysqli_query($link, "UPDATE `admin` SET Admin_Name = '$name',Mobile = '$mobile',Admin_Password = '$password',Admin_Hash = '$pass_hash',Role = '$role' WHERE Admin_Id_No = '$id'");
                if ($upd_sql) {
                    echo "<script>alert('User Updated Successfully!!')</script>";
                } else {
                    echo "<script>alert('User Updation Failed!!')</script>";
                }
            }
        }
    }

    if (isset($_POST['Delete'])) {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to delete admin user');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $id = $_POST['UserName'];

        //Check if User Already Exists
        $check_sql = mysqli_query($link, "SELECT * FROM `admin` WHERE Admin_Id_No = '$id' ");
        if ($check_sql) {
            if (mysqli_num_rows($check_sql) == 0) {
                echo "<script>alert('User Not Found!!')</script>";
            } else {
                $del_sql = mysqli_query($link, "DELETE FROM `admin` WHERE Admin_Id_No = '$id' ");
                if ($del_sql) {
                    echo "<script>alert('User Deleted Successfully!!')</script>";
                } else {
                    echo "<script>alert('User Deletion Failed!!')</script>";
                }
            }
        }
    }
    ?>
    <!-- Scripts -->

    <!-- Get Full Name of User -->
    <script type="text/javascript">
        $('#username').on('change', () => {
            id = $('#username').val();
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    Id_No: id
                },
                success: function(data) {
                    if (data != '0') {
                        arr = data.split(",");
                        $('#full_name').val(arr[0]);
                        $('#mobile').val(arr[1]);
                        $('#password').val(arr[2]);
                        $('#c_password').val(arr[3]);
                        document.getElementById('role').value = arr[4];
                    } else {
                        $('#full_name').val('');
                        $('#mobile').val('');
                        $('#password').val('');
                        $('#c_password').val('');
                        document.getElementById('role').value = 'selectrole';
                    }
                }
            })
        });
    </script>

    <!-- Show/Hide Password -->
    <script type="text/javascript">
        $('#hide').on('click', function() {
            $(this).toggleClass('fa-eye');
            $(this).toggleClass('fa-eye-slash');
            if ($(this).hasClass('fa-eye-slash')) {
                $('#password').attr('type', 'text')
                $('#c_password').attr('type', 'text')
            } else {
                $('#password').attr('type', 'password')
                $('#c_password').attr('type', 'password')
            }
        });
    </script>

    <!-- Validation of From and Display Alert -->
    <script>
        pass = document.getElementById('password');
        c_pass = document.getElementById('c_password');
        alert_row = document.getElementById('alert-row');
        alert = document.getElementById('alert');
        let flag = false;

        function checkPassword() {
            if (pass.value != c_pass.value) {
                alert_row.style.display = 'block';
                alert.innerHTML = 'Passwords are not Same';
                flag = false;
            } else {
                flag = true;
                alert_row.style.display = 'none';
            }
        }
        pass.addEventListener('input', () => {
            if (c_pass.value.length > 0) {
                checkPassword();
            }
        })
        c_pass.addEventListener('input', checkPassword);
    </script>

</html>