<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 10);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view this report');
            location.replace('/Futuregen/Admin/admin_dashboard.php')</script>";
    exit;
}

error_reporting(0);
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
        max-width: 1200px;
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
            <div class="col-lg-6">
                <h3><b>Class Wise Strength Particulars</b></h3>
            </div>
        </div>
        <div class="row justify-content-center mt-4 p-1">
            <div class="col-lg-2">
                <div class="btn-wrapper"
                    <?php if (!can('print', MENU_ID)) { ?>
                    title="You don't have permission to print this report"
                    <?php } ?>>
                    <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    $sql = array();
    $class = array("PreKG", "LKG", "UKG", "1 CLASS", "2 CLASS", "3 CLASS", "4 CLASS", "5 CLASS", "6 CLASS", "7 CLASS", "8 CLASS", "9 CLASS", "10 CLASS",);
    $caste = array("OC", "BC", "SC", "ST", "Mi");
    $gen = array("Boy", "Girl");
    foreach ($class as $cls) {
        foreach ($caste as $cat) {
            foreach ($gen as $g) {
                array_push($sql, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$cls' AND Gender = '$g' AND Category = '$cat'");
            }
        }
    }
    $strength = array();
    for ($j = 0; $j < count($sql); $j++) {
        array_push($strength, mysqli_num_rows(mysqli_query($link, $sql[$j])));
    }

    $total = array();
    $k = 10;
    $b = 0;
    for ($a = 1; $a <= 13; $a++) {
        $sum = 0;
        for ($b; $b < $k; $b++) {
            $sum += $strength[$b];
        }
        array_push($total, $sum);
        $k += 10;
        $sum = 0;
    }
    ?>

    <div class="container table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-white">
                <th>Class</th>
                <th colspan="2" style="width: 100px;">OC</th>
                <th colspan="2" style="width: 100px;">BC</th>
                <th colspan="2" style="width: 100px;">SC</th>
                <th colspan="2" style="width: 100px;">ST</th>
                <th colspan="2" style="width: 100px;">Mi</th>
                <th>Total</th>
            </thead>
            <thead>
                <th></th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
                <th>Male</th>
                <th>Female</th>
            </thead>
            <tr style="height: 50px;" style="height: 50px;">
                <td>Pre KG</td>
                <?php
                $c = 0;
                for ($c; $c < 10; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[0]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>LKG</td>
                <?php
                for ($c; $c < 20; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[1]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>UKG</td>
                <?php
                for ($c; $c < 30; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[2]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>1 CLASS</td>
                <?php
                for ($c; $c < 40; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[3]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>2 CLASS</td>
                <?php
                for ($c; $c < 50; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[4]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>3 CLASS</td>
                <?php
                for ($c; $c < 60; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[5]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>4 CLASS</td>
                <?php
                for ($c; $c < 70; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[6]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>5 CLASS</td>
                <?php
                for ($c; $c < 80; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[7]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>6 CLASS</td>
                <?php
                for ($c; $c < 90; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[8]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>7 CLASS</td>
                <?php
                for ($c; $c < 100; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[9]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>8 CLASS</td>
                <?php
                for ($c; $c < 110; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td style='text-align:center;'>$total[10]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>9 CLASS</td>
                <?php
                for ($c; $c < 120; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td>$total[11]</td>";
                ?>
            </tr>
            <tr style="height: 50px;">
                <td>10 CLASS</td>
                <?php
                for ($c; $c < 130; $c++) {
                    echo "<td style='text-align:center;'>$strength[$c]</td>";
                }
                echo "<td>$total[12]</td>";
                ?>
            </tr>
            <tr>
                <td colspan="10"></td>
                <td>Total</td>
                <td><?php
                    $tot = 0;
                    foreach ($strength as $str) {
                        $tot += $str;
                    }
                    echo $tot;
                    ?></td>
            </tr>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h1 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h1>";
            window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Strength Particulars</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>