<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 98);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/sidebar-style.css" />
    <link rel="stylesheet" href="../css/style.css">
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        background: #E4E9F7;
    }

    .container {
        max-width: 650px;
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    @media screen and (max-width:700px) {
        .container {
            margin-left: 70px;
            max-width: 340px;
        }

        .wrapper .title {
            height: 150px;
        }
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>
    <div class="container">
        <div class="wrapper">
            <div class="title"><span>Change Student/Faculty Password</span></div>
            <form action="" method="post" autocomplete="off">
                <div class="row">
                    <i class="fas fa-lock"></i>
                    <select name="User_Type" id="user_type" class="form-control" style="padding-left: 50px;">
                        <option value="" selected disabled>-- Select User Type --</option>
                        <option value="Student">Student</option>
                        <option value="Faculty">Faculty</option>
                    </select>
                </div>
                <div class="row">
                    <i class="fas fa-user"></i>
                    <input type="text" placeholder="User Name" id="username" name="UserName" oninput="this.value = this.value.toUpperCase()" required>
                </div>
                <div class="row">
                    <button class="btn btn-primary" style="margin-left:30%;max-width: max-content;" onclick="generate();return false;">Generate Password</button>
                </div>
                <div class="row" style="margin-top: 30px;">
                    <div class="col-lg-4">
                        <label for="password_head" id="password_head"><b>New Password:</b></label>
                    </div>
                    <div class="col-lg-6">
                        <input type="text" id="password" name="Password" required>
                    </div>
                </div>
                <div class="row button">
                    <div class="btn-wrapper <?php echo !can('update', MENU_ID) ? 'rbac-disabled' : ''; ?>"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update student/faculty password"
                        <?php } ?>>
                        <input type="submit" name="change" class="btn" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    include '../link.php';
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    if (isset($_POST['change'])) {
        if (!can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to update student/faculty password');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $id = validate($_POST['UserName']);
        $new = validate($_POST['Password']);
        echo "<script>document.getElementById('username').value = '" . $id . "';
        document.getElementById('password').value = '" . $new . "';</script>";
        $npasshash = password_hash($new, PASSWORD_DEFAULT);
        if ($_POST['User_Type']) {
            $user_type = $_POST['User_Type'];
            echo "<script>document.getElementById('user_type').value = '" . $user_type . "' </script>";
            if ($user_type == "Student") {
                if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM student WHERE Id_No = '$id'")) == 0) {
                    echo "<script>alert('No Student Found in Login Table!!')</script>";
                } else {
                    if (mysqli_query($link, "UPDATE student SET Stu_Hash = '$npasshash', Stu_Password = '$new' WHERE Id_No = '$id'")) {
                        echo "<script>alert('Student Password Updated Successfully')</script>";
                    } else {
                        echo "<script>alert('Student Password Updation Failed due to Internal Error')</script>";
                    }
                }
            } else if ($user_type == "Faculty") {
                if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM faculty WHERE Id_No = '$id'")) == 0) {
                    echo "<script>alert('No Faculty Found in Login Table!!')</script>";
                } else {
                    if (mysqli_query($link, "UPDATE faculty SET Fac_Hash = '$npasshash', Password = '$new' WHERE Id_No = '$id'")) {
                        echo "<script>alert('Faculty Password Updated Successfully')</script>";
                    } else {
                        echo "<script>alert('Faculty Password Updation Failed due to Internal Error')</script>";
                    }
                }
            }
        } else {
            echo "<script>alert('Please Select User Type!')</script>";
        }
    }
    ?>
    <!-- Scripts -->

    <!-- Generate Password -->
    <script type="text/javascript">
        function generate() {
            type = document.getElementById('user_type').value;
            if (type == "Student") {
                var password = 'VHST';
            } else if (type == "Faculty") {
                var password = 'VICEM';
            } else {
                return false
            }
            pass = document.getElementById('password');
            var char = Math.floor(Math.random() * 9000 + 1000);
            password += char
            pass.value = password;
        }
    </script>
</body>

</html>