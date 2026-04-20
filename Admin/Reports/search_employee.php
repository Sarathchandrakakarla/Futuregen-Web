<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 52);

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
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    label {
        font-weight: bold;
    }

    .table-container {
        max-width: 1000px;
        max-height: 500px;
        margin-left: 8%;
        overflow-x: scroll;
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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST" autocomplete="off">
        <div class="container mt-3">
            <div class="row justify-content-center mt-4">
                <label for="Search_By" class="col-sm-2 col-form-label">Employee Name</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="txtinp" id="txtinp" required>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Search Employee Details Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container">
        <table class="table table-striped table-hover">
            <thead class="bg-secondary text-light">
                <tr>
                    <th>S.No</th>
                    <th>Emp Id No.</th>
                    <th>Name</th>
                    <th>SurName</th>
                    <th>Father Name</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $txtinp = $_POST['txtinp'];
                        echo "<script>document.getElementById('txtinp').value = '" . $txtinp . "'</script>";
                        $sql = "SELECT * FROM `employee_master_data` WHERE Emp_First_Name LIKE '%$txtinp%'";
                        $result = mysqli_query($link, $sql);
                        $i = 1;
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo '<tr>
                                <td>' . $i . '</td>
                                <td>' . $row['Emp_Id'] . '</td>
                                <td>' . $row['Emp_First_Name'] . '</td>
                                <td>' . $row['Emp_Sur_Name'] . '</td>
                                <td>' . $row['Father_Name'] . '</td>
                                </tr>';
                                $i++;
                            }
                        } else {
                            echo "<script>
                                    alert('No Employee Found');
                                    </script>";
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>