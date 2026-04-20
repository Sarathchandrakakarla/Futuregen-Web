<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 12);

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

    .table-container {
        max-width: 1350px;
        max-height: 500px;
        margin-left: 8%;
        overflow-x: scroll;
    }

    th,
    td {
        text-align: center;
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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Route Wise Strength Particulars</b></h3>
            </div>
        </div>
        <form action="" method="post">
            <div class="row justify-content-center mt-4 p-1">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                    </div>
                </div>
        </form>
    </div>

    <div class="container table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-white">
                <th>Route</th>
                <th>PreKG</th>
                <th>LKG</th>
                <th>UKG</th>
                <?php for ($i = 1; $i <= 10; $i++) {
                    echo '<th>' . $i . ' CLASS</th>';
                } ?>
                <th>Total</th>
            </thead>

            <?php

            if (isset($_POST['show'])) {
                if (!can('view', MENU_ID)) {
                    echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                    exit;
                }
                //Arrays
                $class = array("PreKG", "LKG", "UKG", "1 CLASS", "2 CLASS", "3 CLASS", "4 CLASS", "5 CLASS", "6 CLASS", "7 CLASS", "8 CLASS", "9 CLASS", "10 CLASS");
                $routes = array();
                $strength = array();

                $res = mysqli_query($link, "SELECT * FROM `van_route` ORDER BY Van_Route");

                while ($row = mysqli_fetch_assoc($res)) {
                    array_push($routes, $row['Van_Route']);
                }

                foreach ($class as $cls) {
                    $total = 0;
                    foreach ($routes as $route) {
                        $no_stu = mysqli_num_rows(mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$cls' AND Van_Route = '$route'"));
                        $strength[$cls][$route] = $no_stu;
                        $total += $no_stu;
                    }
                    $strength[$cls]['Total'] = $total;
                }

                $overall_total = 0;
                foreach ($routes as $route) {
                    $route_total = 0;
                    echo '<tr>
                    <td>' . $route . '</td>';
                    foreach ($class as $cls) {
                        $route_total += $strength[$cls][$route];
                        echo '<td style="text-align:center;">' . $strength[$cls][$route] . '</td>';
                    }
                    $overall_total += $route_total;
                    echo '<td style="text-align:center;"><b>' . $route_total . '</b></td>';
                    echo '</tr>';
                }
                echo '<tr>
                <td><b>Total</b></td>';
                foreach ($class as $cls) {
                    echo '<td style="text-align:center;"><b>' . $strength[$cls]['Total'] . '</b></td>';
                }
                echo '<td style="text-align:center;"><b>' . $overall_total . '</b></td>';
                echo '</tr>';
            }
            ?>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h1 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h1>";
            window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Route Wise Strength Particulars</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>