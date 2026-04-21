<?php
include '../link.php';
$flag = "";

if (isset($_POST['Login'])) {

    function validate($data)
    {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $uname = validate($_POST['UserName']);
    $pass  = validate($_POST['Password']);

    // Fetch student + role (RBAC-ready, LEFT JOIN safety)
    $sql = "
        SELECT  s.Id_No,
                s.Stu_Hash,
                s.Status,
                s.Role,
                r.Role_Name,
                r.Active_Flag
        FROM student s
        LEFT JOIN roles r ON r.Role_Id = s.Role
        WHERE s.Id_No = '$uname'
    ";

    $result = mysqli_query($link, $sql);

    if (mysqli_num_rows($result) !== 1) {
        $flag = "username";
    } else {

        $row = mysqli_fetch_assoc($result);

        if (!password_verify($pass, $row['Stu_Hash'])) {
            $flag = "password";
        } else if ($row['Status'] === 'Disabled') {
            echo "<script>
                alert('Your Login has been Disabled. Contact Admin Office');
                location.replace('student_login.php');
            </script>";
            exit;
        } else if ($row['Role'] === null || $row['Role_Name'] === null) {
            echo "<script>
                alert('Your Role is Not Assigned. Contact Office Admin');
                location.replace('student_login.php');
            </script>";
            exit;
        } else if ((int)$row['Active_Flag'] !== 1) {
            echo "<script>
                alert('Your Role is Inactive. Contact Office Admin');
                location.replace('student_login.php');
            </script>";
            exit;
        } else {

            // Secure session
            session_regenerate_id(true);

            $_SESSION['Id_No']      = $row['Id_No'];
            $_SESSION['Role_Name'] = $row['Role_Name']; // UI only

            // Load RBAC
            $_SESSION['RBAC'] = [];
            $roleId = (int)$row['Role'];

            $permQuery = mysqli_query(
                $link,
                "SELECT Menu_Id,
                        can_view, can_create, can_update,
                        can_delete, can_print, can_export,
                        can_custom1, can_custom2, can_custom3, can_custom4
                 FROM role_menu_map
                 WHERE Role_Id = $roleId"
            );

            while ($p = mysqli_fetch_assoc($permQuery)) {
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

            // Load student master data (unchanged behavior)
            $id = $row['Id_No'];
            $sql_ = "SELECT * FROM student_master_data WHERE Id_No = '$id'";
            $result_ = mysqli_query($link, $sql_);

            if (mysqli_num_rows($result_) === 1) {
                $m = mysqli_fetch_assoc($result_);
                foreach ($m as $key => $value) {
                    $_SESSION[$key] = $value;
                }
            }

            header('Location: student_dashboard.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico">
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="../css/footer.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>

    <style>
        nav {
            background: #1b1b1b;
        }

        nav ul li .sub-menu {
            background: #1b1b1b;
        }

        body {
            background: #1abc9c;
        }

        #hide {
            position: absolute;
            margin: -3% 26%;
            font-size: 20px;
            color: #1abc9c;
            cursor: pointer;
        }

        @media (max-width:920px) {
            #hide {
                margin: -13% 74%;
            }
        }

        @media (max-width:500px) {
            footer {
                bottom: -10%;
            }
        }

        @media (max-height:600px) {
            footer {
                bottom: -200px;
            }
        }
    </style>
</head>

<body>

    <nav>
        <div class="logo">
            <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png" alt="..." width="70px" />
        </div>
        <div class="heading">
            <h3 style="<?php if($_SESSION['school_db']['school_code'] == 'FGS') echo 'font-size: medium;'; ?>"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h3>
        </div>
        <input type="checkbox" id="click" />
        <label for="click" class="menu-btn"><i class="fas fa-bars"></i></label>
        <ul>
            <li><a href="/Futuregen/index.php">Home</a></li>
            <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
            <!-- <li><a href="/Futuregen/Gallery/gallery.php">Gallery</a></li> -->
            <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/contact.php">Contact</a></li>
            <!-- <li><a href="/Futuregen/youtube.php" id="link">Our Stories</a></li>
            <li><a href="/Futuregen/blog/blog_index.php" id="link">Blog</a></li> -->
            <li>
                <a class="active" href="#">Login</a>
                <ul class="login-sub-menu sub-menu">
                    <li><a class="active" href="/Futuregen/Admin/admin_login.php">Admin Login</a></li>
                    <li><a href="/Futuregen/Student/student_login.php">Student Login</a></li>
                    <li><a href="/Futuregen/Faculty/faculty_login.php">Faculty Login</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <div class="container col-lg-4">
        <div class="wrapper">
            <div class="title p-2"><span>Student Login</span></div>

            <form method="post">
                <?php if ($flag === "password") { ?>
                    <div class="alert alert-danger">Incorrect Password</div>
                <?php } ?>
                <?php if ($flag === "username") { ?>
                    <div class="alert alert-danger">Incorrect Username</div>
                <?php } ?>

                <div class="row">
                    <i class="fas fa-user"></i>
                    <input type="text" name="UserName" placeholder="User Name"
                        value="<?= isset($uname) ? $uname : '' ?>"
                        oninput="this.value=this.value.toUpperCase()" required>
                </div>

                <div class="row">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="Password" id="password" placeholder="Password" required>
                </div>

                <span class="fas fa-eye" id="hide"></span>

                <div class="pass">
                    <a href="forgot_password.php">Forgot password?</a>
                </div>

                <div class="row button">
                    <input type="submit" name="Login" value="Login">
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?>, <a href="/"> <?= (isset($_SESSION['school_db']) && isset($_SESSION['school_db']['footer_msg'])) ? $_SESSION['school_db']['footer_msg'] : ''; ?> </a>. All Rights Reserved. </p>
            <p class="company-tag">
                Developed and Maintained by <u><a href="https://sarathtechgenics.netlify.app" target="_blank">Sarath Techgenics</a></u>
            </p>
        </div>
    </footer>

    <script>
        $('#hide').on('click', function() {
            $(this).toggleClass('fa-eye fa-eye-slash');
            $('#password').attr('type',
                $(this).hasClass('fa-eye-slash') ? 'text' : 'password'
            );
        });
    </script>

</body>

</html>yy