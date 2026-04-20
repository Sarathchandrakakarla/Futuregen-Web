<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 78);

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

    #section {
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

    .tooltip-wrapper {
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .disabled {
        opacity: 0.5;
    }
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sms_type" id="class_wise" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class/Route Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="sms_type" id="all_students" value="All_Students">
                        <label class="form-check-label" for="all_students">All Students</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <label for="add_by" class="col-sm-2 col-form-label">Type of Fee: </label>
                <div class="p-2 col-lg-3 rounded">
                    <select class="form-select" name="Type" id="type" aria-label="Default select example">
                        <option value="selectfeetype" selected disabled>-- Select Fee Type --</option>
                        <option value="School Fee">School Fee</option>
                        <option value="Admission Fee">Admission Fee</option>
                        <option value="Computer Fee">Computer Fee</option>
                        <option value="Vehicle Fee">Vehicle Fee</option>
                        <option value="Examination Fee">Examination Fee</option>
                        <option value="Book Fee">Book Fee</option>
                        <?php
                        if ($_SESSION['school_db']['school_code'] == "FGS") {
                        ?>
                            <option value="Hostel Fee">Hostel Fee</option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center" id="class_row">
                <div class="row justify-content-center">
                    <div class="p-2 col-sm-2 rounded">
                        <label>Class: </label>
                    </div>
                    <div class="p-2 col-lg-3 rounded">
                        <select class="form-select" name="Class" id="class" aria-label="Default select example">
                            <option selected disabled>-- Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value='" . $i . " CLASS'>" . $i . " CLASS</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="p-2 col-sm-2 rounded">
                        <label>Section: </label>
                    </div>
                    <div class="p-2 col-lg-3 rounded">
                        <select class="form-select" name="Section" id="sec" aria-label="Default select example">
                            <option selected disabled>-- Select Section --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center" id="route_row" hidden>
                <div class="p-2 col-sm-2 rounded">
                    <label>Route: </label>
                </div>
                <div class="p-2 col-lg-3 rounded">
                    <select class="form-select" name="Route" id="route" aria-label="Default select example">
                        <option selected disabled>-- Select Route --</option>
                        <?php
                        $max_fee = array();
                        $route_query = mysqli_query($link, "SELECT v.Van_Route,f.Fee FROM van_route v,actual_fee f WHERE v.Van_Route=f.Route ORDER BY v.Van_Route");
                        while ($route_row = mysqli_fetch_assoc($route_query)) {
                            $max_fee[$route_row['Van_Route']] = $route_row['Fee'];
                            echo "<option value='" . $route_row['Van_Route'] . "'>" . $route_row['Van_Route'] . " - " . $route_row['Fee'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <label for="exam_name" class="col-lg-2 col-form-label">Amount: </label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" name="Amount" id="amount" required>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <label for="exam_name" class="col-lg-2 col-form-label">Due Date: </label>
                <div class="col-sm-3">
                    <input type="date" class="form-control" name="Date" id="date" required>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to send SMS"
                        <?php } ?>>
                        <button class="btn btn-success" name="send" id="send" onclick="return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Send</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('export', MENU_ID)) { ?>
                        title="You don't have permission to export this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="return false;" id="export" <?php echo !can('export', MENU_ID) ? 'disabled' : ''; ?>>Export To Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container" id="alert-container" style="display: none;">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                    </svg>
                    <div>
                        Now, You Can Send SMS
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="col-lg-3" style="color: red;">
            NOTE: Please Press Show before Sending SMS
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Send SMS of Fee Balances</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table hidden>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-size:30px;" colspan="4"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td>
            </tr>
            <tr>
                <td style="font-size:20px;color:red" id="label"></td>
                <td id="txt_label" style="font-size:20px;"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead>
                <th>S.No</th>
                <th>Id No.</th>
                <th>Name</th>
                <th id="label" hidden>Class</th>
                <th id="label2" hidden>Class</th>
                <th>Balance</th>
                <th>SMS Link</th>
                <th>
                    <?php if (can('create', MENU_ID)) { ?>
                        <input type="checkbox" class="form-check-input" id="select_all" onclick="toggle(this)">
                        <label for="select_all">Select All</label>
                    <?php } else { ?>
                        <input type="checkbox"
                            id="select_all"
                            class="form-check-input"
                            disabled
                            class="disabled"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="You don't have permission to select all and send SMS">
                        <label for="select_all">Select All</label>
                    <?php } ?>
                </th>
            </thead>
            <tbody id="tbody">
                <?php
                function format_date($date)
                {
                    $arr = explode('-', $date);
                    $t = $arr[0];
                    $arr[0] = $arr[2];
                    $arr[2] = $t;
                    $date = implode('-', $arr);
                    return $date;
                }
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    //Arrays
                    $ids = array();
                    $names = array();
                    $classes = array();
                    $mobiles = array();
                    $total = array();
                    $paid = array();
                    $balances = array();

                    $amount = $_POST['Amount'];
                    $date = $_POST['Date'];
                    $sms_type = $_POST['sms_type'];
                    echo "<script>
                                document.getElementById('" . strtolower($sms_type) . "').checked = true;
                                document.getElementById('amount').value = '" . $amount . "';
                                document.getElementById('date').value = '" . $date . "'</script>";
                    if ($_POST['Type']) {
                        $type = $_POST['Type'];
                        echo "<script>document.getElementById('type').value = '" . $type . "'</script>";
                        if ($type == "Vehicle Fee") {
                            echo "<script>
                                document.getElementById('class_row').hidden = 'hidden';
                                document.getElementById('route_row').hidden = '';
                                </script>";
                            if ($sms_type == "All_Students") {
                                echo "<script>
                                        document.getElementById('class_row').hidden = 'hidden';
                                        document.getElementById('route_row').hidden = 'hidden';
                                        document.getElementById('label').hidden = '';
                                        document.getElementById('label').innerHTML = 'Route';
                                        document.getElementById('label2').hidden = '';
                                    </script>";
                                $routes = [];
                                $sql = mysqli_query($link, "SELECT * FROM `van_route`");
                                while ($van_row = mysqli_fetch_assoc($sql)) {
                                    $routes[] = $van_row['Van_Route'];
                                }
                                $i = 1;
                                foreach ($routes as $route) {
                                    $ids = array();
                                    $names = array();
                                    $classes = array();
                                    $mobiles = array();
                                    $total = array();
                                    $paid = array();
                                    $balances = array();
                                    $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '% CLASS%' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG')");
                                    while ($row1 = mysqli_fetch_assoc($query1)) {
                                        array_push($ids, $row1['Id_No']);
                                        $names[$row1['Id_No']] = $row1['First_Name'];
                                        $classes[$row1['Id_No']] = $row1['Stu_Class'] . " " . $row1['Stu_Section'];
                                        if (str_contains($row1['Mobile'], ',')) {
                                            $mobiles[$row1['Id_No']] = explode(',', $row1['Mobile'], 2)[0];
                                        } else if (str_contains($row1['Mobile'], ' ')) {
                                            $mobiles[$row1['Id_No']] = explode(' ', $row1['Mobile'], 2)[0];
                                        } else {
                                            $mobiles[$row1['Id_No']] = $row1['Mobile'];
                                        }
                                        if ($row1['Route'] != '' && $row1['Route'] != NULL && $row1['Route'] != '0' && $row1['Route'] != 'Drop') {
                                            if (mysqli_num_rows(mysqli_query($link, "SELECT First_Name FROM `stu_fee_master_data` WHERE Id_No='" . $row1['Id_No'] . "' AND Type='Vehicle Fee'")) == 0) {
                                                echo "<script>alert('" . $id . " Not Available in Stu Fee Master Data! ')</script>";
                                            }
                                        }
                                    }
                                    foreach ($ids as $id) {
                                        //Fetching Committed Fees of Each Student
                                        $query2 = mysqli_query($link, "SELECT Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = 'Vehicle Fee'");
                                        if (mysqli_num_rows($query2) != 0) {
                                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                                $total[$id] = $row2['Total'];
                                            }
                                        }

                                        //Fetching Paid Fees of Each Student
                                        $query3 = mysqli_query($link, "SELECT SUM(Fee) AS Paid FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type' GROUP BY Id_No");
                                        if (mysqli_num_rows($query3) == 0) {
                                            $paid[$id] = '0';
                                        } else {
                                            while ($row3 = mysqli_fetch_assoc($query3)) {
                                                $paid[$id] = $row3['Paid'];
                                            }
                                        }

                                        //Calculating Balances of Each Student
                                        if ((int)$paid[$id] == 0) {
                                            $balances[$id] = (int)($total[$id]);
                                        } else {
                                            $balances[$id] = (int)($total[$id]) - (int)($paid[$id]);
                                        }
                                    }
                                    //Generating SMS Text for Each Student
                                    foreach ($ids as $id) {
                                        if ($balances[$id] != 0 && $balances[$id] >= $amount) {
                                            $text = "Dear sir/Madam,There is a balance of amount Rs" . $balances[$id] . "towards " . $type . " of your child " . $names[$id] . " studying " . $classes[$id] . " .Kindly pay before date " . format_date($date) . " .Principal,Victory highschool,kodur.";
                                            $mobiles[$id] = rtrim($mobiles[$id]);
                                            echo '
                                                <tr>
                                                <td>' . $i . '</td>
                                                <td>' . $id . '</td>
                                                <td>' . $names[$id] . '</td>
                                                <td>' . $route . '</td>
                                                <td style="white-space:nowrap;">' . $classes[$id] . '</td>
                                                <td>' . $balances[$id] . '</td>
                                                <td>';
                                            if (can('create', MENU_ID)) {
                                                echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobiles[$id] . '&message=' . $text . '&route=TRANS&TemplateID=1707164915284267071&format=JSON" class="sms_link">' . $mobiles[$id] . '</a>';
                                            } else {
                                                echo '<a href="javascript:void(0)"
                                                    class="text-secondary disabled"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="You don\'t have permission to send SMS">
                                                    ' . $mobiles[$id] . '
                                                </a>';
                                            }
                                            echo '</td>
                                                <td>';
                                            if (can("create", MENU_ID)) {
                                                echo '<input type="checkbox" class="form-check-input student" id="student" name="student[' . $id . ']" value="' . $details[$id][1] . '">';
                                            } else {
                                                echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                                            <input type="checkbox" class="form-check-input student disabled" disabled> 
                                                        </span>';
                                            }

                                            echo '</td>
                                                </tr>
                                                ';
                                            $i++;
                                        }
                                    }
                                }
                                echo "<script>document.getElementById('alert-container').style.display = 'block';</script>";
                            } else {
                                if ($_POST['Route']) {
                                    $route = $_POST['Route'];
                                    echo "<script>document.getElementById('route').value = '" . $route . "';
                                        document.getElementById('label2').hidden = ''
                                        </script>";
                                    $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '% CLASS%' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG')");
                                    while ($row1 = mysqli_fetch_assoc($query1)) {
                                        array_push($ids, $row1['Id_No']);
                                        $names[$row1['Id_No']] = $row1['First_Name'];
                                        $classes[$row1['Id_No']] = $row1['Stu_Class'] . " " . $row1['Stu_Section'];
                                        if (str_contains($row1['Mobile'], ',')) {
                                            $mobiles[$row1['Id_No']] = explode(',', $row1['Mobile'], 2)[0];
                                        } else if (str_contains($row1['Mobile'], ' ')) {
                                            $mobiles[$row1['Id_No']] = explode(' ', $row1['Mobile'], 2)[0];
                                        } else {
                                            $mobiles[$row1['Id_No']] = $row1['Mobile'];
                                        }
                                        if ($row1['Route'] != '' && $row1['Route'] != NULL && $row1['Route'] != '0' && $row1['Route'] != 'Drop') {
                                            if (mysqli_num_rows(mysqli_query($link, "SELECT First_Name FROM `stu_fee_master_data` WHERE Id_No='" . $row1['Id_No'] . "' AND Type='Vehicle Fee'")) == 0) {
                                                echo "<script>alert('" . $id . " Not Available in Stu Fee Master Data! ')</script>";
                                            }
                                        }
                                    }
                                    foreach ($ids as $id) {
                                        //Fetching Committed Fees of Each Student
                                        $query2 = mysqli_query($link, "SELECT Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = 'Vehicle Fee'");
                                        if (mysqli_num_rows($query2) != 0) {
                                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                                $total[$id] = $row2['Total'];
                                            }
                                        }

                                        //Fetching Paid Fees of Each Student
                                        $query3 = mysqli_query($link, "SELECT SUM(Fee) AS Paid FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type' GROUP BY Id_No");
                                        if (mysqli_num_rows($query3) == 0) {
                                            $paid[$id] = '0';
                                        } else {
                                            while ($row3 = mysqli_fetch_assoc($query3)) {
                                                $paid[$id] = $row3['Paid'];
                                            }
                                        }

                                        //Calculating Balances of Each Student
                                        if ((int)$paid[$id] == 0) {
                                            $balances[$id] = (int)($total[$id]);
                                        } else {
                                            $balances[$id] = (int)($total[$id]) - (int)($paid[$id]);
                                        }
                                    }
                                    //Generating SMS Text for Each Student
                                    $i = 1;
                                    foreach ($ids as $id) {
                                        if ($balances[$id] != 0 && $balances[$id] >= $amount) {
                                            $text = "Dear sir/Madam,There is a balance of amount Rs" . $balances[$id] . "towards " . $type . " of your child " . $names[$id] . " studying " . $classes[$id] . " .Kindly pay before date " . format_date($date) . " .Principal,Victory highschool,kodur.";
                                            $mobiles[$id] = rtrim($mobiles[$id]);
                                            echo '
                                                <tr>
                                                <td>' . $i . '</td>
                                                <td>' . $id . '</td>
                                                <td>' . $names[$id] . '</td>
                                                <td>' . $classes[$id] . '</td>
                                                <td>' . $balances[$id] . '</td>
                                                <td>';
                                            if (can('create', MENU_ID)) {
                                                echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobiles[$id] . '&message=' . $text . '&route=TRANS&TemplateID=1707164915284267071&format=JSON" class="sms_link">' . $mobiles[$id] . '</a>';
                                            } else {
                                                echo '<a href="javascript:void(0)"
                                                    class="text-secondary disabled"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="You don\'t have permission to send SMS">
                                                    ' . $mobiles[$id] . '
                                                </a>';
                                            }
                                            echo '</td>
                                                <td>';
                                            if (can("create", MENU_ID)) {
                                                echo '<input type="checkbox" class="form-check-input student" id="student" name="student[' . $id . ']" value="' . $details[$id][1] . '">';
                                            } else {
                                                echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                                            <input type="checkbox" class="form-check-input student disabled" disabled> 
                                                        </span>';
                                            }
                                            echo '</td>
                                                </tr>
                                                ';
                                            $i++;
                                        }
                                    }
                                    echo "<script>document.getElementById('alert-container').style.display = 'block';</script>";
                                } else {
                                    echo "<script>alert('Please Select Route!')</script>";
                                }
                            }
                        } else {
                            echo "<script>
                                document.getElementById('class_row').hidden = '';
                                document.getElementById('route_row').hidden = 'hidden';
                                </script>";
                            if ($sms_type == "All_Students") {
                                echo "<script>
                                    document.getElementById('class_row').hidden = 'hidden';
                                    document.getElementById('route_row').hidden = 'hidden';
                                    document.getElementById('label').hidden = '';
                                    document.getElementById('label').innerHTML = 'Class';
                                    document.getElementById('label2').hidden = 'hiddden';
                                    </script>";
                                $classes = ['PreKG', 'LKG', 'UKG'];
                                for ($j = 1; $j <= 10; $j++) {
                                    $classes[] = $j . " CLASS";
                                }
                                $sections = ['A', 'B', 'C', 'D', 'E'];
                                $i = 1;
                                foreach ($classes as $class) {
                                    foreach ($sections as $section) {
                                        //Arrays
                                        $ids = array();
                                        $names = array();
                                        $mobiles = array();
                                        $total = array();
                                        $paid = array();
                                        $balances = array();
                                        //Fetching Id Nos of Students of that Class and Section
                                        $ids_query = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                                        while ($row1 = mysqli_fetch_assoc($ids_query)) {
                                            array_push($ids, $row1['Id_No']);
                                            $names[$row1['Id_No']] = $row1['First_Name'];
                                            if (str_contains($row1['Mobile'], ',')) {
                                                $mobiles[$row1['Id_No']] = explode(',', $row1['Mobile'], 2)[0];
                                            } else if (str_contains($row1['Mobile'], ' ')) {
                                                $mobiles[$row1['Id_No']] = explode(' ', $row1['Mobile'], 2)[0];
                                            } else {
                                                $mobiles[$row1['Id_No']] = $row1['Mobile'];
                                            }
                                        }
                                        foreach ($ids as $id) {
                                            //Fetching Committed Fees of Each Student
                                            $query2 = mysqli_query($link, "SELECT Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                            if (mysqli_num_rows($query2) == 0) {
                                                if ($type != "Admission Fee") {
                                                    echo "<script>alert('" . $id . " Not Available in Stu Fee Master Data! ')</script>";
                                                }
                                            } else {
                                                while ($row2 = mysqli_fetch_assoc($query2)) {
                                                    $total[$id] = $row2['Total'];
                                                }
                                            }

                                            //Fetching Paid Fees of Each Student
                                            $query3 = mysqli_query($link, "SELECT SUM(Fee) AS Paid FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type' GROUP BY Id_No");
                                            if (mysqli_num_rows($query3) == 0) {
                                                $paid[$id] = '0';
                                            } else {
                                                while ($row3 = mysqli_fetch_assoc($query3)) {
                                                    $paid[$id] = $row3['Paid'];
                                                }
                                            }

                                            //Calculating Balances of Each Student
                                            if ((int)$paid[$id] == 0) {
                                                $balances[$id] = (int)($total[$id]);
                                            } else {
                                                $balances[$id] = (int)($total[$id]) - (int)($paid[$id]);
                                            }
                                        }
                                        //Generating SMS Text for Each Student
                                        foreach ($ids as $id) {
                                            if ($balances[$id] != 0 && $balances[$id] >= $amount) {
                                                $text = "Dear sir/Madam,There is a balance of amount Rs" . $balances[$id] . "towards " . $type . " of your child " . $names[$id] . " studying " . $class . " " . $section . " .Kindly pay before date " . format_date($date) . " .Principal,Victory highschool,kodur.";
                                                $mobiles[$id] = rtrim($mobiles[$id]);
                                                echo '
                                                    <tr>
                                                    <td>' . $i . '</td>
                                                    <td>' . $id . '</td>
                                                    <td>' . $names[$id] . '</td>
                                                    <td>' . $class . ' ' . $section . '</td>
                                                    <td>' . $balances[$id] . '</td>
                                                    <td>';
                                                if (can('create', MENU_ID)) {
                                                    echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobiles[$id] . '&message=' . $text . '&route=TRANS&TemplateID=1707164915284267071&format=JSON" class="sms_link">' . $mobiles[$id] . '</a>';
                                                } else {
                                                    echo '<a href="javascript:void(0)"
                                                            class="text-secondary disabled"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="You don\'t have permission to send SMS">
                                                            ' . $mobiles[$id] . '
                                                            </a>';
                                                }
                                                echo '</td>
                                                    <td>';
                                                if (can("create", MENU_ID)) {
                                                    echo '<input type="checkbox" class="form-check-input student" id="student" name="student[' . $id . ']" value="' . $details[$id][1] . '">';
                                                } else {
                                                    echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                                                <input type="checkbox" class="form-check-input student disabled" disabled> 
                                                            </span>';
                                                }
                                                echo '</td>
                                                    </tr>
                                                    ';
                                                $i++;
                                            }
                                        }
                                    }
                                }
                                echo "<script>document.getElementById('alert-container').style.display = 'block';</script>";
                            } else {
                                if ($_POST['Class']) {
                                    $class = $_POST['Class'];
                                    echo "<script>document.getElementById('class').value = '" . $class . "';
                                        document.getElementById('label2').hidden = 'hiddden';
                                        </script>";
                                    if ($_POST['Section']) {
                                        $section = $_POST['Section'];
                                        echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                                        //Fetching Id Nos of Students of that Class and Section
                                        $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                                        while ($row1 = mysqli_fetch_assoc($query1)) {
                                            array_push($ids, $row1['Id_No']);
                                            $names[$row1['Id_No']] = $row1['First_Name'];
                                            if (str_contains($row1['Mobile'], ',')) {
                                                $mobiles[$row1['Id_No']] = explode(',', $row1['Mobile'], 2)[0];
                                            } else if (str_contains($row1['Mobile'], ' ')) {
                                                $mobiles[$row1['Id_No']] = explode(' ', $row1['Mobile'], 2)[0];
                                            } else {
                                                $mobiles[$row1['Id_No']] = $row1['Mobile'];
                                            }
                                        }
                                        foreach ($ids as $id) {
                                            //Fetching Committed Fees of Each Student
                                            $query2 = mysqli_query($link, "SELECT Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                            if (mysqli_num_rows($query2) == 0) {
                                                if ($type != "Admission Fee") {
                                                    echo "<script>alert('" . $id . " Not Available in Stu Fee Master Data! ')</script>";
                                                }
                                            } else {
                                                while ($row2 = mysqli_fetch_assoc($query2)) {
                                                    $total[$id] = $row2['Total'];
                                                }
                                            }

                                            //Fetching Paid Fees of Each Student
                                            $query3 = mysqli_query($link, "SELECT SUM(Fee) AS Paid FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type' GROUP BY Id_No");
                                            if (mysqli_num_rows($query3) == 0) {
                                                $paid[$id] = '0';
                                            } else {
                                                while ($row3 = mysqli_fetch_assoc($query3)) {
                                                    $paid[$id] = $row3['Paid'];
                                                }
                                            }

                                            //Calculating Balances of Each Student
                                            if ((int)$paid[$id] == 0) {
                                                $balances[$id] = (int)($total[$id]);
                                            } else {
                                                $balances[$id] = (int)($total[$id]) - (int)($paid[$id]);
                                            }
                                        }
                                        //Generating SMS Text for Each Student
                                        $i = 1;
                                        foreach ($ids as $id) {
                                            if ($balances[$id] != 0 && $balances[$id] >= $amount) {
                                                $text = "Dear sir/Madam,There is a balance of amount Rs" . $balances[$id] . "towards " . $type . " of your child " . $names[$id] . " studying " . $class . " " . $section . " .Kindly pay before date " . format_date($date) . " .Principal,Victory highschool,kodur.";
                                                $mobiles[$id] = rtrim($mobiles[$id]);
                                                echo '
                                                    <tr>
                                                    <td>' . $i . '</td>
                                                    <td>' . $id . '</td>
                                                    <td>' . $names[$id] . '</td>
                                                    <td>' . $balances[$id] . '</td>
                                                    <td>';
                                                if (can('create', MENU_ID)) {
                                                    echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobiles[$id] . '&message=' . $text . '&route=TRANS&TemplateID=1707164915284267071&format=JSON" class="sms_link">' . $mobiles[$id] . '</a>';
                                                } else {
                                                    echo '<a href="javascript:void(0)"
                                                            class="text-secondary disabled"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-placement="top"
                                                            title="You don\'t have permission to send SMS">
                                                            ' . $mobiles[$id] . '
                                                            </a>';
                                                }
                                                echo '</td>
                                                    <td>';
                                                if (can("create", MENU_ID)) {
                                                    echo '<input type="checkbox" class="form-check-input student" id="student" name="student[' . $id . ']" value="' . $details[$id][1] . '">';
                                                } else {
                                                    echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                                                <input type="checkbox" class="form-check-input student disabled" disabled> 
                                                            </span>';
                                                }
                                                echo '</td>
                                                    </tr>
                                                    ';
                                                $i++;
                                            }
                                        }
                                        echo "<script>document.getElementById('alert-container').style.display = 'block';</script>";
                                    } else {
                                        echo "<script>alert('Please Select Section!')</script>";
                                    }
                                } else {
                                    echo "<script>alert('Please Select Class!')</script>";
                                }
                            }
                        }
                    } else {
                        echo "<script>alert('Please Select Fee Type!')</script>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>


    <!-- Scripts -->

    <!-- Global Const Variables for can_update,can_allocate -->
    <script>
        const CAN_SEND = <?= can('create', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Change Labels -->
    <script type="text/javascript">
        let route_row = document.getElementById('route_row');
        let cls_row = document.getElementById('class_row');
        document.getElementById('type').addEventListener('change', function(e) {
            type = this.value
            if (type == "Vehicle Fee") {
                if (!cls_row.hidden) {
                    cls_row.hidden = 'hidden';
                }
                if (!all_students.checked && route_row.hidden) {
                    route_row.hidden = '';
                }
            } else {
                if (!all_students.checked && cls_row.hidden) {
                    cls_row.hidden = '';
                }
                if (!route_row.hidden) {
                    route_row.hidden = 'hidden';
                }
            }
        });

        document.addEventListener('change', (e) => {
            var row = e.target.id;
            var type = document.getElementById('type').value;
            if (row == "all_students") {
                if (!cls_row.hidden) {
                    cls_row.hidden = 'hidden';
                }
                if (!route_row.hidden) {
                    route_row.hidden = 'hidden';
                }
            } else if (row == "class_wise") {

                if (cls_row.hidden && type != "Vehicle Fee") {
                    cls_row.hidden = '';
                }
                if (route_row.hidden && type == "Vehicle Fee") {
                    route_row.hidden = '';
                }
            }
        })
    </script>

    <!-- Checkbox Select All -->
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByClassName('student');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        $('.student').on('click', function() {
            if ($('.student').not(':checked').length == 0) {
                document.getElementById('select_all').checked = true;
            } else {
                document.getElementById('select_all').checked = false;
            }
        });
    </script>
    <!--
    <script>
        $('#send').on('click', () => {
            absentees = []
            $(".student:checked").each(function() {
                absentees.push($(this).parent().siblings().eq(4).children().attr('href'));
                //mywin = window.open($(this).parent().siblings().eq(4).children().attr('href'), '_blank')
            });
            if (absentees.length > 0) {
                absentees.forEach((stu) => {
                    mywin = window.open(stu, '_blank')
                })
            }
            /*
            $('.sms_link').each(function() {
                mywin = window.open($(this).attr('href'), '_blank')
            });
            */
        });
    </script>
    -->
    <script>
        function send(url) {
            fetchResponse = fetch(url)
        }
        $('#send').on('click', () => {
            if (!CAN_SEND) {
                alert("You do not have permission to Send SMS");
                return;
            }
            absentees = []
            $(".student:checked").each(function() {
                if (!all_students.checked) {
                    if (type.value == "Vehicle Fee") {
                        absentees.push($(this).parent().siblings().eq(5).children().attr('href'));
                    } else {
                        absentees.push($(this).parent().siblings().eq(4).children().attr('href'));
                    }
                } else {
                    if (type.value == "Vehicle Fee") {
                        absentees.push($(this).parent().siblings().eq(6).children().attr('href'));
                    } else {
                        absentees.push($(this).parent().siblings().eq(5).children().attr('href'));
                    }
                }
                //mywin = window.open($(this).parent().siblings().eq(4).children().attr('href'), '_blank')
            });
            if (absentees.length > 0) {
                absentees.forEach((stu) => {
                    //console.log(stu)
                    send(stu)
                    //mywin = window.open(stu, '_blank')
                })
                alert('All SMS Sent Successfully!')
            } else {
                alert('No Student Selected!')
            }
            /*
            $('.sms_link').each(function() {
                mywin = window.open($(this).attr('href'), '_blank')
            });
            */
        });
    </script>
    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = "Fee Balances List"
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById('table-container');
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            // Specify file name
            filename = filename ? filename + '.xls' : 'excel_data.xls';

            // Create download link element
            downloadLink = document.createElement("a");

            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                // Create a link to the file
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

                // Setting the file name
                downloadLink.download = filename;

                //triggering the function
                downloadLink.click();
            }
        });
    </script>
</body>

</html>