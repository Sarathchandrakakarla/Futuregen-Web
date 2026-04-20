<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 121);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view this report');
        location.replace('/Victory/Faculty/faculty_dashboard.php')</script>";
    exit;
}

error_reporting(0);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/sidebar-style.css" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>
<style>
    body {
        background-image: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
    }

    .table-container {
        margin-left: 90px;
        max-width: 1400px;
        max-height: 700px;
        overflow-x: scroll;
        overflow-y: scroll;
    }

    .period {
        color: #fff;
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

<body>
    <?php include 'sidebar.php'; ?>
    <div class="container mt-3">
        <h1 style="text-align: center;font-family:'Times New Roman';">Time Table</h1>
    </div>
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>><i class="bx bx-printer"></i>Print</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" name="Refresh" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>><i class="bx bx-refresh"></i>Refresh</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="container table-container">
        <table class="table table-striped table-hover mt-5" id="table-container">
            <thead class="bg-warning">
                <th style="text-align:center;border-top: 2px solid black;border-bottom: 2px solid black;border-left: 2px solid black;border-right: 2px solid black;">Class</th>
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    echo "<th style='text-align:center;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;'>Period " . $i . "</th>";
                }
                echo "<th style='width:50px;text-align:center;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;'>Lunch Break</th>";
                for ($i = 5; $i <= 8; $i++) {
                    echo "<th style='text-align:center;border-right: 2px solid black;border-top: 2px solid black;border-bottom: 2px solid black;'>Period " . $i . "</th>";
                }
                ?>
            </thead>
            <tbody>
                <?php
                $classes = ['PreKG', 'LKG', 'UKG'];
                for ($i = 1; $i <= 10; $i++) {
                    array_push($classes, $i . ' CLASS');
                }
                $sections = ['A', 'B', 'C', 'D', 'E'];
                $final_classes = [];
                foreach ($classes as $class) {
                    $temp = [];
                    foreach ($sections as $section) {
                        if (mysqli_num_rows(mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'")) > 0) {
                            array_push($temp, $section);
                        }
                    }
                    $final_classes[$class] = $temp;
                }
                foreach (array_keys($final_classes) as $class) {
                    foreach ($final_classes[$class] as $section) {
                        echo "
                        <tr>
                            <td style='text-align:center;border-left: 2px solid black;border-right: 2px solid black;border-bottom: 2px solid black;'>" . $class . " " . $section . "</td>";
                        $time_table_sql = mysqli_query($link, "SELECT * FROM `time_table` WHERE Class = '$class' AND Section = '$section'");
                        if ($time_table_sql) {
                            if (mysqli_num_rows($time_table_sql) == 0) {
                                for ($i = 1; $i <= 4; $i++) {
                                    echo "<td class='period' id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                }
                                echo "<td class='period' name='Lunch' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                for ($i = 5; $i <= 8; $i++) {
                                    echo "<td class='period' id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                }
                            } else {
                                while ($time_table_row = mysqli_fetch_assoc($time_table_sql)) {
                                    for ($i = 1; $i <= 4; $i++) {
                                        if ($time_table_row['Period' . $i] != "" || $time_table_row['Period' . $i] != NULL) {
                                            $details = explode(',', $time_table_row['Period' . $i]);
                                            if (count($details) > 1) {
                                                $teacher_id = trim($details[0]);
                                                $subject = trim(end($details));
                                            } else {
                                                $teacher_id = trim($details[0]);
                                            }
                                            $name_sql = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$teacher_id'");
                                            while ($name_row = mysqli_fetch_assoc($name_sql)) {
                                                $teacher_name = $name_row['Emp_First_Name'];
                                            }
                                            //Checking Teacher is Present or Absent
                                            $date = date('d-m-Y');
                                            date_default_timezone_set("Asia/Kolkata");
                                            $am_pm = strtoupper(date('a', $timestamp));
                                            $teacher_status = true;
                                            $teacher_status_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Id_No = '$teacher_id' AND Date = '$date' AND $am_pm = 'A'");
                                            if (mysqli_num_rows($teacher_status_sql) == 0) {
                                                $teacher_status = true;
                                            } else {
                                                $teacher_status = false;
                                            }
                                            if ($teacher_status) {
                                                echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:green;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                            } else {
                                                echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:red;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                            }
                                        } else {
                                            echo "<td class='period'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                        }
                                    }
                                    echo "<td style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;font-weight:bold;'>Lunch Break</td>";
                                    for ($i = 5; $i <= 8; $i++) {
                                        if ($time_table_row['Period' . $i] != "" || $time_table_row['Period' . $i] != NULL) {
                                            $details = explode(',', $time_table_row['Period' . $i]);
                                            if (count($details) > 1) {
                                                $teacher_id = trim($details[0]);
                                                $subject = trim(end($details));
                                            } else {
                                                $teacher_id = trim($details[0]);
                                            }
                                            $name_sql = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$teacher_id'");
                                            while ($name_row = mysqli_fetch_assoc($name_sql)) {
                                                $teacher_name = $name_row['Emp_First_Name'];
                                            }
                                            //Checking Teacher is Present or Absent
                                            $date = date('d-m-Y');
                                            date_default_timezone_set("Asia/Kolkata");
                                            $am_pm = strtoupper(date('a', $timestamp));
                                            $teacher_status = true;
                                            $teacher_status_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Id_No = '$teacher_id' AND Date = '$date' AND $am_pm = 'A'");
                                            if (mysqli_num_rows($teacher_status_sql) == 0) {
                                                $teacher_status = true;
                                            } else {
                                                $teacher_status = false;
                                            }
                                            if ($teacher_status) {
                                                echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:green;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . " </td>";
                                            } else {
                                                echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:red;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . " </td>";
                                            }
                                        } else {
                                            echo "<td class='period'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                        }
                                    }
                                }
                            }
                        }
                        echo "</tr>";
                    }
                }
                ?>

                <?php

                if (isset($_POST['Refresh'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }       
                    $date = date('d-m-Y');
                    date_default_timezone_set("Asia/Kolkata");
                    $am_pm = strtoupper(date('a', $timestamp));
                    $absent_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Date = '$date' AND $am_pm = 'A'");
                    if (mysqli_num_rows($absent_sql) == 0) {
                        echo "<script>alert('There are No Absentees!');</script>";
                    } else {
                        $absentees = [];
                        while ($absent_row = mysqli_fetch_assoc($absent_sql)) {
                            array_push($absentees, $absent_row['Id_No']);
                        }
                        foreach ($absentees as $teacher_id) {
                            echo "<script>
                            const nodeList = document.querySelectorAll('." . $teacher_id . "');
                            for (let i = 0; i < nodeList.length; i++) {
                              nodeList[i].style.backgroundColor = 'red';
                            }
                            </script>";
                        }
                    }
                }

                ?>
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Time Table</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>