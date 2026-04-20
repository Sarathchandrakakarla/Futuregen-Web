<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 99);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<?php

if (isset($_POST['add'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert into this report');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    if ($_POST['Group']) {
        $group = $_POST['Group'];
        $group = str_replace(' ', '_', $group);

        $check_sql = mysqli_query($link, "SELECT * FROM `topics` WHERE Topic = '$group'");
        if (mysqli_num_rows($check_sql) > 0) {
            echo "<script>alert('Group Already Exists!!')</script>";
        } else {
            $sql = mysqli_query($link, "INSERT INTO `topics`(Topic) VALUES('$group')");

            if ($sql) {
                echo "<script>alert('New Group Inserted Successfully!!')</script>";
            } else {
                echo "<script>alert('New Group Insertion Failed!!')</script>";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 700px;
        max-height: 500px;
        overflow-x: scroll;
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
        }
    }

    @media print {
        * {
            display: none;
        }

        #table-container {
            display: block;
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

    #inp,
    #add-btn {
        position: relative;
    }

    @keyframes mymove {
        from {
            opacity: 0;
            left: -100px;
        }

        to {
            left: 0;
            opacity: 1;
        }
    }

    @keyframes myrevmove {
        from {
            opacity: 1;
            left: 0;
        }

        to {
            left: -100px;
            opacity: 0;
        }
    }

    .delete {
        cursor: pointer;
        font-size: 20px;
        color: red;
    }

    div[title],
    div[title] * {
        cursor: not-allowed !important;
    }

    .text-secondary {
        opacity: 0.6;
    }
</style>

<body class="bg-light">
    <?php
    include 'sidebar.php';
    ?>
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <label for=""><b>Add New Group</b></label>
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert youtube videos"
                        <?php } ?>>
                        <button class="btn btn-primary" style="border-radius: 50%;" id="plus" onclick="reveal();return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>> <i class="bx bx-plus" id="plus-icon"></i> </button>
                    </div>
                </div>
                <div class="col-lg-3">
                    <input type="text" class="form-control" id="inp" name="Group" placeholder="Enter Group Name" value="<?php if (isset($group)) {
                                                                                                                            echo $group;
                                                                                                                        } else {
                                                                                                                            echo '';
                                                                                                                        } ?>" style="opacity: 0;">
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert youtube videos"
                        <?php } ?>>
                        <button class="btn btn-warning" name="add" id="add-btn" style="opacity: 0;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="row justify-content-center mt-3">
        <div class="col-lg-4">
            <h3>Notifications Topics Report</h3>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover">
            <thead>
                <th>S No</th>
                <th>Topic</th>
                <th>Action</th>
            </thead>
            <tbody id="tbody">
                <?php
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $query1 = mysqli_query($link, "SELECT * FROM `topics`");
                    if (mysqli_num_rows($query1) == 0) {
                        echo '
                        <tr>
                            <td colspan="3" class="text-center">No Topics Found</td>
                        </tr>
                        ';
                    } else {
                        $i = 1;
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            echo '
                            <tr>
                                <td>' . $i . '</td>
                                <td>' . htmlspecialchars($row1['Topic']) . '</td>
                                <td>
                                    <div ' . (!can('delete', MENU_ID) ? 'title="You don\'t have permission to delete this topic"' : '') . '>
                                        <i class="bx bx-trash delete ' . (!can('delete', MENU_ID) ? 'text-secondary' : '') . '"
                                        ' . (!can('delete', MENU_ID) ? 'style="pointer-events:none;"' : 'onclick="deleteTopic(this)"') . '>
                                        </i>
                                    </div>
                                </td>
                            </tr>';
                            $i++;
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->

    <!-- Global Const Variables for can_delete -->
    <script>
        const CAN_DELETE = <?= can('delete', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Revealing Text Box -->
    <script type="text/javascript">
        function reveal() {
            button = document.getElementById('plus-icon');
            if (!button.classList.contains('open')) {
                button.style.transform = 'rotate(45deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "mymove 1s ease-in 1";
                txtinp.style.opacity = 1;

                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "mymove 1s ease-in 1";
                txtbtn.style.opacity = 1;
            } else {
                button.style.transform = 'rotate(90deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "myrevmove 1s ease-out 1";


                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "myrevmove 1s ease-out 1";

                txtinp.style.opacity = 0;
                txtbtn.style.opacity = 0;
            }
            button.classList.toggle('open');
        }
    </script>

    <!-- Delete Row -->
    <script type="text/javascript">
        $(".delete").click(function(e) {
            if (!CAN_DELETE) {
                e.preventDefault();
                alert("You do not have permission to delete video");
                return;
            }
            group = $(this).parent().siblings().eq(1).text();
            if (!confirm('Confirm to delete Group: ' + group + '?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        Group: group
                    },
                    success: function(data) {
                        alert('Group Deleted Successfully!! Refresh to get data updated!')
                    }
                });
            }
        });
    </script>
</body>

</html>