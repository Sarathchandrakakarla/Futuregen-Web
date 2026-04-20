<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 39);

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
        max-width: 900px;
        max-height: 500px;
        overflow-x: scroll;
    }

    .delete {
        color: red;
        cursor: pointer;
        font-size: 20px;
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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <form action="" method="post">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-1">
                    <label for=""><b>Date:</b></label>
                </div>
                <div class="col-lg-4">
                    <input type="date" class="form-control" value="<?php if (isset($date)) {
                                                                        echo $date;
                                                                    } else {
                                                                        echo date('Y-m-d');
                                                                    } ?>" name="Date" id="date" required>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="att_type" id="am" checked value="AM">
                        <label class="form-check-label" for="am">Morning</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="att_type" id="pm" value="PM">
                        <label class="form-check-label" for="pm">Afternoon</label>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row justify-content-center mt-4">
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
    </div>
    <div class="container" id="report_container" hidden>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-2" style="font-weight:bold;">
                Not Submitted: <span id="Not Submitted"></span>
            </div>
            <div class="col-lg-2" style="font-weight:bold;">
                Present: <span id="Present"></span>
            </div>
            <div class="col-lg-2" style="font-weight:bold;">
                Absent: <span id="Absent"></span>
            </div>
            <div class="col-lg-2" style="font-weight:bold;">
                Leave: <span id="Leave"></span>
            </div>
        </div>
    </div>
    <form action="" method="POST">
        <div class="container table-container">
            <table class="table table-striped">
                <thead>
                    <th>S.No</th>
                    <th>Id No.</th>
                    <th>Name</th>
                    <th>Attendance Status</th>
                    <th>Punch Time</th>
                </thead>
                <tbody id="tbody">
                    <tr>
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
                            $date = $_POST['Date'];
                            $type = $_POST['att_type'];
                            echo "<script>document.getElementById('date').value = '" . $date . "';
                            document.getElementById('" . strtolower($type) . "').checked = true</script>";
                            $report = ["Not Submitted" => 0, "Present" => 0, "Absent" => 0, "Leave" => 0];

                            //Queries
                            $query1 = mysqli_query($link, "SELECT emd.Emp_Id, emd.Emp_First_Name, COALESCE(CASE WHEN ea." . $type . " = 'A' THEN 'Absent' WHEN ea." . $type . " = 'L' THEN 'Leave' WHEN ea." . $type . " = 'P' THEN 'Present' WHEN ea." . $type . " IS NULL THEN 'Not Submitted' ELSE 'Not Submitted' END, 'Not Submitted') AS " . $type . "_Status, COALESCE(ea." . $type . "_Punch_Time, '') AS " . $type . "_Punch_Time FROM employee_master_data emd LEFT JOIN employee_attendance ea ON emd.Emp_Id = ea.Id_No AND ea.Date = '" . format_date($date) . "' WHERE emd.Status = 'Working' ORDER BY FIELD(" . $type . "_Status,'Not Submitted','Absent','Leave','Present'),emd.Emp_Id");

                            if ($query1) {
                                if (mysqli_num_rows($query1) == 0) {
                                    echo "<script>alert('No Employees Found!!')</script>";
                                } else {
                                    $i = 1;
                                    while ($row1 = mysqli_fetch_assoc($query1)) {
                                        echo '
                                        <tr>
                                            <td>' . $i . '</td>
                                            <td>' . $row1['Emp_Id'] . '</td>
                                            <td>' . $row1['Emp_First_Name'] . '</td>
                                            <td>' . $row1[$type . '_Status'] . '</td>
                                            <td>' . $row1[$type . '_Punch_Time'] . '</td>
                                        </tr>
                                        ';
                                        $report[$row1[$type . '_Status']]++;
                                        $i++;
                                    }
                                    echo '
                                    <script>
                                        report_container.hidden = "";
                                        document.getElementById("Not Submitted").innerHTML = ' . $report['Not Submitted'] . ';
                                        document.getElementById("Present").innerHTML = ' . $report['Present'] . ';
                                        document.getElementById("Absent").innerHTML = ' . $report['Absent'] . ';
                                        document.getElementById("Leave").innerHTML = ' . $report['Leave'] . ';
                                    </script>
                                    ';
                                }
                            } else {
                                echo "<script>alert('Error in Fetching Id Nos!')</script>";
                            }
                        }

                        ?>
                </tbody>
            </table>
        </div>
    </form>

</body>

</html>