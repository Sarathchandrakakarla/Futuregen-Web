<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 29);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />

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
        max-width: 1100px;
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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Homework Analytics</b></h3>
            </div>
        </div>
    </div>
    <div class="container" id="report_container" hidden>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-3">
                <span id="class_label"></span>
            </div>
        </div>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-2">
                Viewed: <span id="viewed"></span>
            </div>
            <div class="col-lg-2">
                Not Viewed: <span id="not_viewed"></span>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Id No.</th>
                    <th style="padding:5px;">Student Name</th>
                    <th style="padding:5px;">Viewed Status</th>
                    <th style="padding:5px;">First Viewed Time</th>
                    <th style="padding:5px;">Latest Viewed Time</th>
                    <th style="padding:5px;">Response</th>
                    <th style="padding:5px;">Response Time</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr>
                    <?php
                    if (isset($_GET['Action']) && $_GET['Action'] == "show") {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $date = $_GET['Date'];
                        $class = $_GET['Class'];
                        $section = $_GET['Section'];
                        $subject = $_GET['Subject'];
                        $report = ['Viewed' => 0, 'Not Viewed Yet' => 0];
                        echo "
                        <script>
                            report_container.hidden = '';
                            class_label.innerHTML = '" . $class . " " . $section . " - " . $date . " - " . $subject . "';
                        </script>
                        ";
                        $query1 = mysqli_query($link, "SELECT smd.Id_No, smd.First_Name, CASE WHEN sh.Id_No IS NULL THEN 'Not Viewed Yet' ELSE 'Viewed' END AS View_Status, CASE WHEN sh.Id_No IS NULL THEN NULL ELSE sh.First_View END AS First_View, CASE WHEN sh.Id_No IS NULL THEN NULL ELSE sh.Latest_View END AS Latest_View, CASE WHEN sh.Id_No IS NULL THEN NULL ELSE sh.Response_Time END AS Response_Time,Image,Text FROM student_master_data smd LEFT JOIN student_homework sh ON smd.Id_No = sh.Id_No AND sh.Date = '$date' AND sh.Subject = '$subject' WHERE smd.Stu_Class = '$class' AND smd.Stu_Section = '$section'");
                        $i = 1;
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            echo '
                            <tr>
                                <td>' . $i . '</td>
                                <td>' . $row1['Id_No'] . '</td>
                                <td>' . $row1['First_Name'] . '</td>
                                <td style="white-space:nowrap;">' . $row1['View_Status'] . '</td>
                                <td>' . $row1['First_View'] . '</td>
                                <td>' . $row1['Latest_View'] . '</td>';
                            if ($row1['Image'] || $row1['Text']) {
                                echo '<td><a href="' . $_SESSION['school_db']['Root_Dir'] . '/Files/Homework/Student Homework/' . $date . '/' . $row1['Id_No'] . '-' . $subject . '.pdf" target="_blank" class="btn btn-warning"><i class="fas fa-eye"></i> View</a></td>
                                <td>' . $row1['Response_Time'] . '</td>
                                ';
                            } else {
                                echo '
                                <td></td>
                                <td></td>
                                ';
                            }
                            echo '</tr>
                            ';
                            $report[$row1['View_Status']]++;
                            $i++;
                        }
                        echo "
                        <script>
                            viewed.innerHTML = '" . $report['Viewed'] . "';
                            not_viewed.innerHTML = '" . $report['Not Viewed Yet'] . "';
                        </script>
                        ";
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

</body>

</html>