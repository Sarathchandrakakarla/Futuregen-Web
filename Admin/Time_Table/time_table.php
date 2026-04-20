<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 42);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view this report');
        location.replace('/Victory/Admin/admin_dashboard.php')</script>";
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
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />

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

    .leisure-container {
        max-width: 1400px;
        max-height: 500px;
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
    <?php include '../sidebar.php'; ?>
    <div class="container mt-3">
        <h1 style="text-align: center;font-family:'Times New Roman';">Time Table</h1>
    </div>
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
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
                    <div class="btn-wrapper"
                        <?php if (!can('delete', MENU_ID)) { ?>
                        title="You don't have permission to reset this report"
                        <?php } ?>>
                        <button class="btn btn-primary" name="Reset" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>><i class="bx bx-reset"></i>Reset Time Table</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update time table"
                        <?php } ?>>
                        <button class="btn btn-primary edit" onclick="edit(<?= !can('update', MENU_ID) ? 'false' : 'true' ?>);return false;" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>><i class="bx bx-edit"></i>Edit</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update time table"
                        <?php } ?>>
                        <button class="btn btn-primary save <?= !can('update', MENU_ID) ? 'save-disabled' : '' ?>" name="Save" onclick="return false;" disabled><i class="bx bx-save"></i>Save</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('view', 53)) { ?>
                        title="You don't have permission to view employee list"
                        <?php } ?>>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emplist" <?php echo !can('view', 53) ? 'disabled' : ''; ?>>
                            Employee List
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container table-container mt-5">
            <table class="table table-striped table-hover" id="table-container">
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
                                            $time_table_temp_sql = mysqli_query($link, "SELECT * FROM `time_table_temp` WHERE Class = '$class' AND Section = '$section' AND Period = 'Period$i'");
                                            if (mysqli_num_rows($time_table_temp_sql) != 0) {
                                                while ($time_table_temp_row =  mysqli_fetch_assoc($time_table_temp_sql)) {
                                                    $teacher_id = $time_table_temp_row['Faculty'];

                                                    $name_sql = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$teacher_id'");
                                                    while ($name_row = mysqli_fetch_assoc($name_sql)) {
                                                        $teacher_name = $name_row['Emp_First_Name'];
                                                    }
                                                    echo "<td class='period " . $teacher_id . " allocated'  id='" . $class . "_" . $section . "_period_" . $i . "' style='color:black;text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:blue;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . "</td>";
                                                }
                                            } else {
                                                if ($time_table_row['Period' . $i] != "" && $time_table_row['Period' . $i] != NULL) {
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
                                                    $teacher_status_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Id_No = '$teacher_id' AND Date = '$date' AND (AM = 'A' OR AM = 'L')");
                                                    if (mysqli_num_rows($teacher_status_sql) == 0) {
                                                        $teacher_status = true;
                                                    } else {
                                                        $teacher_status = false;
                                                    }
                                                    if ($teacher_status) {
                                                        echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:green;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                                    } else {
                                                        echo "<td class='period absent " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:#BA0021;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                                    }
                                                } else {
                                                    echo "<td class='period'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                                }
                                            }
                                        }
                                        echo "<td style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;font-weight:bold;'>Lunch Break</td>";
                                        for ($i = 5; $i <= 8; $i++) {
                                            $time_table_temp_sql = mysqli_query($link, "SELECT * FROM `time_table_temp` WHERE Class = '$class' AND Section = '$section' AND Period = 'Period$i'");
                                            if (mysqli_num_rows($time_table_temp_sql) != 0) {
                                                while ($time_table_temp_row =  mysqli_fetch_assoc($time_table_temp_sql)) {
                                                    $teacher_id = $time_table_temp_row['Faculty'];

                                                    $name_sql = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$teacher_id'");
                                                    while ($name_row = mysqli_fetch_assoc($name_sql)) {
                                                        $teacher_name = $name_row['Emp_First_Name'];
                                                    }
                                                    echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:blue;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . "</td>";
                                                }
                                            } else {
                                                if ($time_table_row['Period' . $i] != "" && $time_table_row['Period' . $i] != NULL) {
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
                                                    $teacher_status_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Id_No = '$teacher_id' AND Date = '$date' AND (PM = 'A' OR PM = 'L')");
                                                    if (mysqli_num_rows($teacher_status_sql) == 0) {
                                                        $teacher_status = true;
                                                    } else {
                                                        $teacher_status = false;
                                                    }
                                                    if ($teacher_status) {
                                                        echo "<td class='period " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:green;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                                    } else {
                                                        echo "<td class='period absent " . $teacher_id . "'  id='" . $class . "_" . $section . "_period_" . $i . "' style='text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;background-color:#BA0021;font-weight:bold;'>" . $teacher_id . " <br> " . $teacher_name . " <br> " . $subject . "</td>";
                                                    }
                                                } else {
                                                    echo "<td class='period'  id='" . $class . "_" . $section . "_period_" . $i . "' style='color:black;text-align:center;border-right: 2px solid black;border-bottom: 2px solid black;'></td>";
                                                }
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
                        $absent_sql = mysqli_query($link, "SELECT * FROM `employee_attendance` WHERE Date = '$date' AND ($am_pm = 'A' OR $am_pm = 'L')");
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
                              nodeList[i].style.backgroundColor = '#BA0021';
                            }
                            </script>";
                            }
                        }
                    }
                    ?>

                    <?php
                    if (isset($_POST['Reset'])) {
                        if (!can('delete', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to reset this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $reset_query = mysqli_query($link, "TRUNCATE TABLE `time_table_temp`");
                        if ($reset_query) {
                            echo "<script>alert('Time Table Reset Succesful! Refresh to get Updated Data! ')</script>";
                        } else {
                            echo "<script>alert('Time Table Reset Failed!')</script>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- Employee List Modal -->
    <div class="modal fade" id="emplist" tabindex="-1" aria-labelledby="empListLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="empListLabel">Employee List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <th class="border border-dark">S No</th>
                            <th class="border border-dark">Id No.</th>
                            <th class="border border-dark">Name</th>
                        </thead>
                        <tbody>
                            <?php
                            if (can('view', 53)) {
                                $query4 = mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Status = 'Working' ORDER BY Emp_Id");
                                $i = 1;
                                while ($row4 = mysqli_fetch_assoc($query4)) {
                                    echo "
                                <tr>
                                    <td class='border border-dark'>" . $i . "</td>
                                    <td class='border border-dark'>" . $row4['Emp_Id'] . "</td>
                                    <td class='border border-dark'>" . $row4['Emp_First_Name'] . "</td>
                                </tr>
                                ";
                                    $i++;
                                }
                            } else {
                                echo "
                                <tr>
                                    <td class='border border-dark text-center' colspan='3'>You don't have permission to view employee list</td>
                                </tr>
                                ";
                            }
                            ?>
                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </div>

    <div class="leisure-container table-container mt-5">
        <table class="table table-hover">
            <thead class="bg-secondary text-white">
                <tr>
                    <th class="text-center" colspan="5">Additional Faculty</th>
                </tr>
                <tr>
                    <th>S No.</th>
                    <th>Id No.</th>
                    <th>Name</th>
                    <th>Period</th>
                    <th>Allocate</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //Arrays
                $periods = [];
                for ($i = 1; $i <= 8; $i++) {
                    array_push($periods, 'Period' . $i);
                }
                $classes = ['PreKG', 'LKG', 'UKG'];
                for ($i = 1; $i <= 10; $i++) {
                    array_push($classes, $i . ' CLASS');
                }
                $sections = ['A', 'B', 'C', 'D'];
                $period_faculties = [];
                $overall_faculties = [];
                $leisure_faculties = [];

                //Getting Faculties from Time Table Period Wise
                foreach ($periods as $period) {
                    $period_faculties[$period] = [];
                    foreach ($classes as $class) {
                        foreach ($sections as $section) {
                            $query1 = mysqli_query($link, "SELECT * FROM `time_table` WHERE Class = '$class' AND Section = '$section'");
                            if (mysqli_num_rows($query1) > 0) {
                                while ($row1 = mysqli_fetch_assoc($query1)) {
                                    if ($row1[$period] != NULL) {
                                        array_push($period_faculties[$period], explode(',', $row1[$period])[0]);
                                        array_push($overall_faculties, explode(',', $row1[$period])[0]);
                                    }
                                }
                            }

                            $query2 = mysqli_query($link, "SELECT * FROM `time_table_temp` WHERE Class = '$class' AND Section = '$section' AND Period = '$period'");
                            if (mysqli_num_rows($query2) > 0) {
                                while ($row2 = mysqli_fetch_assoc($query2)) {
                                    array_push($period_faculties[$period], $row2['Faculty']);
                                }
                            }
                        }
                    }
                }
                //Overall Faculties from Time Table
                $overall_faculties = array_unique($overall_faculties);
                //Checking Which Period is Leisure for each faculty
                foreach ($overall_faculties as $faculty) {
                    $temp = [];
                    foreach ($periods as $period) {
                        if (!in_array($faculty, $period_faculties[$period])) {
                            array_push($temp, $period);
                        }
                    }
                    if (count($temp) > 0) {
                        $leisure_faculties[$faculty] = $temp;
                    }
                }

                $i = 1;
                $can_allocate = can('custom1', MENU_ID);
                foreach (array_keys($leisure_faculties) as $id) {
                    $query2 = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$id'");
                    if (mysqli_num_rows($query2) > 0) {
                        while ($row2 = mysqli_fetch_assoc($query2)) {

                            foreach ($leisure_faculties[$id] as $period) {
                                echo '
                            <tr>
                                <td>' . $i . '</td>
                                <td>' . $id . '</td>
                                <td>' . $row2['Emp_First_Name'] . '</td>
                                <td>' . $period . '</td>
                                <td>
                                    <input type="checkbox" id="allocate_' . $id . '_' . $period . '"  onclick = "disp_class(this)" ' . (!$can_allocate ? 'disabled' : '') . ' />
                                    <label for="allocate_' . $id . '_' . $period . '">Allocate</label>
                                </td>
                            </tr>
                            ';
                                $i++;
                            }
                        }
                    }
                }

                //Head Master and Nagaraju Sir
                $additional_ids = ['VHEM006'];
                foreach ($additional_ids as $id) {
                    $status = true;
                    foreach ($periods as $period) {
                        if (in_array($id, $period_faculties[$period])) {
                            $status = false;
                            break;
                        }
                    }
                    if ($status) {
                        $query3 = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$id'");
                        if (mysqli_num_rows($query3) > 0) {
                            while ($row3 = mysqli_fetch_assoc($query3)) {
                                echo '
                            <tr>
                                <td>' . $i . '</td>
                                <td>' . $id . '</td>
                                <td>' . $row3['Emp_First_Name'] . '</td>
                                <td>Any</td>
                                <td>
                                    <input type="checkbox" class="any" id="allocate_' . $id . '_' . $period . '" onclick = "disp_class(this)" ' . (!$can_allocate ? 'disabled' : '') . '/>
                                    <label for="allocate_' . $id . '_' . $period . '">Allocate</label>
                                </td>
                            </tr>
                            ';
                                $i++;
                            }
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Global Const Variables for can_update,can_allocate -->
    <script>
        const CAN_UPDATE_MAIN = <?= can('update', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_ALLOCATE = <?= can('custom1', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Display Classes -->
    <script type="text/javascript">
        function disp_class(ele) {
            // 🚨 RBAC HARD STOP
            if (!CAN_ALLOCATE) {
                ele.checked = false;

                // Prevent duplicate alerts
                if (!ele.dataset.blocked) {
                    alert("You do not have permission to allocate faculty.");
                    ele.dataset.blocked = "1";
                }

                return false;
            }

            // Clear block flag when allowed
            delete ele.dataset.blocked;
            if (ele.checked) {
                let absent_classes = []
                let absent_sections = []
                //Get All Period td elements
                var period_list = document.querySelectorAll('.absent')
                Array.from(period_list).forEach((element) => {
                    var arr = element.id.split('_')
                    absent_classes.push(arr[0]);
                    absent_sections.push(arr[1]);
                })
                absent_classes = new Set(absent_classes)
                absent_sections = new Set(absent_sections)
                text = "<select class='form-control' id='" + ele.id + "_class'><option value='' selected>-- Select Class --</option>"
                absent_classes.forEach((cls) => {
                    text += "<option value='" + cls + "'>" + cls + "</option>"
                })
                //text += "<option value='1 CLASS'>1 CLASS</option>"
                text += "</select>"
                text += "<select class='form-control' id='" + ele.id + "_section'><option value='' selected>-- Select Section --</option>"
                absent_sections.forEach((sec) => {
                    text += "<option value='" + sec + "' id='" + ele.id + "_section'>" + sec + "</option>"
                })
                //text += "<option value='A'>A</option>"
                text += "</select>"
                if (ele.classList.contains('any')) {
                    text += "<select class='form-control' id='" + ele.id + "_period'><option value='' selected>-- Select Period --</option>"
                    for (var i = 1; i <= 8; i++) {
                        text += "<option value='Period" + i + "'>Period" + i + "</option>"
                    }
                    text += "</select>"
                }
                text += "<button class='btn btn-success' id='" + ele.id + "_allocate' onclick='allocate(this)' >Allocate</button>"
                $(ele).parent('td').append(text)
            } else {
                $('#' + ele.id + '_class').remove()
                $('#' + ele.id + '_section').remove()
                $('#' + ele.id + '_allocate').remove()
                $('#' + ele.id + '_period').remove()
            }
        }
    </script>

    <!-- Allocate -->
    <script type="text/javascript">
        function allocate(ele) {

            if (!CAN_ALLOCATE) {
                alert("You do not have permission to allocate faculty.");
                return;
            }

            let cls = $(ele).parent().children().eq(2).val();
            if (!cls) {
                alert("Please Select Class!");
                return;
            }

            let sec = $(ele).parent().children().eq(3).val();
            if (!sec) {
                alert("Please Select Section!");
                return;
            }

            let period = $(ele).parent().siblings().eq(3).text().trim();

            if (period === "Any") {
                period = $(ele).parent().children().eq(4).val();
                if (!period) {
                    alert("Please Select Period!");
                    return;
                }
            }

            let fac_id = $(ele).parent().siblings().eq(1).text().trim();
            let period_num = period.replace('Period', '');

            let cellId = `${cls}_${sec}_period_${period_num}`;
            let cell = document.getElementById(cellId);

            if (!cell) {
                alert("Invalid period selection!");
                return;
            }

            if (!cell.classList.contains('absent')) {
                alert('Given Period is Already Allocated!');
                return;
            }

            // ✅ UI UPDATE (CRITICAL)
            cell.innerHTML = fac_id;
            cell.classList.remove('absent');
            cell.classList.add('allocated');
            cell.style.backgroundColor = 'blue';
            cell.style.color = 'black';
            cell.style.fontWeight = 'bold';

            alert('Faculty allocated temporarily. Click Save to persist changes.');
        }
    </script>

    <!-- Edit -->
    <script type="text/javascript">
        function edit(canUpdate) {
            if (canUpdate) {
                var periodList = document.querySelectorAll('.period');
                periodList.forEach((period) => {
                    $(period).attr('contenteditable', 'true');
                });
                $('.save').prop('disabled', !canUpdate);
            }
        }
    </script>

    <!-- Save -->
    <script type="text/javascript">
        $('.save').on('click', () => {

            if ($('.save').hasClass('save-disabled')) {
                alert("You don't have permission to update time table");
                return;
            }

            let classes = ['PreKG', 'LKG', 'UKG'];
            for (let i = 1; i <= 10; i++) {
                classes.push(i + ' CLASS');
            }

            let sections = ['A', 'B', 'C', 'D'];
            let text = "";
            let allocated_text = "";

            classes.forEach((cls) => {
                sections.forEach((section) => {
                    for (let period = 1; period <= 8; period++) {

                        let elm = document.getElementById(`${cls}_${section}_period_${period}`);
                        if (!elm) continue;

                        let value = elm.innerHTML.replace(/&nbsp;/g, '').trim();
                        if (value === "") continue;

                        if (elm.classList.contains('allocated')) {
                            if (CAN_ALLOCATE) {
                                allocated_text += `${cls}_${section}_period_${period}=${value}&`;
                            }
                        } else {
                            if (CAN_UPDATE_MAIN) {
                                text += `${cls}_${section}_period_${period}=${value}&`;
                            }
                        }
                    }
                });
            });
            let payload = {};

            if (CAN_UPDATE_MAIN && text !== "") {
                payload.Time_Table = text;
            }

            if (CAN_ALLOCATE && allocated_text !== "") {
                payload.Allocated = allocated_text;
            }

            if (Object.keys(payload).length === 0) {
                alert("You don't have permission to save any changes.");
                return;
            }


            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: payload,
                success: function(data) {
                    console.log(data);

                    // Normalize response
                    let parts = data.split(',').filter(v => v.trim() !== '');

                    // 🔐 Permission handling
                    // 🔐 Permission handling (separate)
                    if (parts.includes('permission_update') && parts.includes('permission_allocate')) {
                        alert("You don't have permission to update the timetable or allocate faculty.");
                        return;
                    }

                    if (parts.includes('permission_update')) {
                        alert("You don't have permission to update the timetable.");
                        return;
                    }

                    if (parts.includes('permission_allocate')) {
                        alert("You don't have permission to allocate faculty.");
                        return;
                    }

                    // ❌ Operation failure (SQL / logic)
                    if (parts.includes('failure')) {
                        alert('Time Table Updation Failed!');
                        return;
                    }

                    // ✅ Success handling
                    if (parts.includes('success')) {
                        if (parts.length > 1) {
                            alert('Time Table and Allocations Updated Successfully!');
                        } else {
                            alert('Time Table Updated Successfully!');
                        }
                        return;
                    }

                    // 🟡 Fallback
                    alert('Internal Error!');
                }

            });
        });
    </script>

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