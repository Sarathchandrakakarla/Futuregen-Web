<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 77);

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
        max-width: 1000px;
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
                <div class="p-2 col-lg-4 rounded">
                    <input type="date" class="form-control" name="Date" id="date" value="<?php if (isset($date)) {
                                                                                                echo $date;
                                                                                            } else {
                                                                                                echo date('Y-m-d');
                                                                                            } ?>" required>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="att_by" id="am" checked value="AM">
                        <label class="form-check-label" for="am">Morning</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="att_by" id="pm" value="PM">
                        <label class="form-check-label" for="pm">Afternoon</label>
                    </div>
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
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to send SMS"
                        <?php } ?>>
                        <button class="btn btn-success" name="send" id="send" onclick="return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Send</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Send SMS of Absentees</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead>
                <th>S.No</th>
                <th>Id No.</th>
                <th>Name</th>
                <th>Status</th>
                <th>Punch Time</th>
                <th>Punctuality</th>
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
                    $date = $_POST['Date'];
                    $att_by = $_POST['att_by'];

                    echo "<script>document.getElementById('date').value = '" . $date . "';</script>";
                    if ($att_by == "AM") {
                        echo "<script>document.getElementById('am').checked = true;</script>";
                    } else {
                        echo "<script>document.getElementById('pm').checked = true;</script>";
                    }

                    //Arrays
                    $ids = array();
                    $details = array();
                    $date = format_date($date);

                    //Queries
                    if ($att_by == "AM") {
                        $query1 = mysqli_query($link, "SELECT emd.Emp_Id, emd.Emp_First_Name, emd.Mobile, CASE WHEN ea.AM = 'A' THEN 'Absent' WHEN ea.AM = 'L' THEN 'Leave' WHEN ea.AM = 'P' THEN 'Present' ELSE 'Not submitted' END AS Attendance_Status, ea.AM_Punch_Time, CASE WHEN ea.AM = 'P' AND ea.AM_Punch_Time IS NOT NULL THEN CASE WHEN STR_TO_DATE(CONCAT(ea.Date, ' ', TRIM(REPLACE(ea.AM_Punch_Time, ' ', ''))), '%d-%m-%Y %h:%i:%s %p') <= STR_TO_DATE(CONCAT(ea.Date, ' 09:00:59 AM'), '%d-%m-%Y %h:%i:%s %p') THEN NULL ELSE TIMESTAMPDIFF(MINUTE, STR_TO_DATE(CONCAT(ea.Date, ' 09:00:00 AM'), '%d-%m-%Y %h:%i:%s %p'), STR_TO_DATE(CONCAT(ea.Date, ' ', TRIM(REPLACE(ea.AM_Punch_Time, ' ', ''))), '%d-%m-%Y %h:%i:%s %p')) END ELSE NULL END AS Late_By_Minutes FROM employee_master_data emd LEFT JOIN employee_attendance ea ON emd.Emp_Id = ea.Id_No AND ea.Date = '$date' WHERE emd.Status = 'Working'");
                    } else if ($att_by == "PM") {
                        $query1 = mysqli_query($link, "SELECT emd.Emp_Id, emd.Emp_First_Name, emd.Mobile, CASE WHEN ea.PM = 'A' THEN 'Absent' WHEN ea.PM = 'L' THEN 'Leave' WHEN ea.PM = 'P' THEN 'Present' ELSE 'Not submitted' END AS Attendance_Status, ea.PM_Punch_Time, CASE WHEN ea.PM = 'P' AND ea.PM_Punch_Time IS NOT NULL THEN CASE WHEN STR_TO_DATE(CONCAT(ea.Date, ' ', TRIM(REPLACE(ea.PM_Punch_Time, ' ', ''))), '%d-%m-%Y %h:%i:%s %p') <= STR_TO_DATE(CONCAT(ea.Date, ' 01:30:59 PM'), '%d-%m-%Y %h:%i:%s %p') THEN NULL ELSE TIMESTAMPDIFF(MINUTE, STR_TO_DATE(CONCAT(ea.Date, ' 01:30:00 PM'), '%d-%m-%Y %h:%i:%s %p'), STR_TO_DATE(CONCAT(ea.Date, ' ', TRIM(REPLACE(ea.PM_Punch_Time, ' ', ''))), '%d-%m-%Y %h:%i:%s %p')) END ELSE NULL END AS Late_By_Minutes FROM employee_master_data emd LEFT JOIN employee_attendance ea ON emd.Emp_Id = ea.Id_No AND ea.Date = '$date' WHERE emd.Status = 'Working'");
                    }
                    if ($query1) {
                        $i = 1;
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            echo '
                                <tr>
                                    <td>' . $i . '</td>
                                    <td>' . $row1['Emp_Id'] . '</td>
                                    <td>' . $row1['Emp_First_Name'] . '</td>
                                    <td>' . $row1['Attendance_Status'] . '</td>
                                    <td>' . $row1[$att_by . '_Punch_Time'] . '</td>';
                            if ($row1['Attendance_Status'] == "Present" && $row1[$att_by . '_Punch_Time']) {
                                if (is_numeric($row1['Late_By_Minutes'])) {
                                    echo '<td>' . $row1['Late_By_Minutes'] . ' mins</td>';
                                } else {
                                    echo '<td>On Time</td>';
                                }
                            } else {
                                echo '<td></td>';
                            }
                            $att_status = $row1['Attendance_Status'];
                            if ($att_status == "Not submitted") {
                                $att_status = 'Not Submitted attendance';
                            } else if ($att_status == "Leave") {
                                $att_status = 'On Leave';
                            }
                            if ($att_by == "AM") {
                                $att_status .= " for today's Morning";
                            } else if ($att_by == "PM") {
                                $att_status .= " for today's Afternoon";
                            }
                            $text = "Dear Employee " . $row1['Emp_First_Name'] . " of ID No :" . $row1['Emp_Id'] . " ,You are " . $att_status . " to school ";
                            if ($row1['Attendance_Status'] == "Present" && $row1[$att_by . '_Punch_Time']) {
                                if (is_numeric($row1['Late_By_Minutes'])) {
                                    $text .= "and late by " . $row1['Late_By_Minutes'] . " Minutes";
                                }
                            }
                            $text .= "-Principal,Victory Schools";
                            $mobile = $row1['Mobile'];
                            if (str_contains($mobile, ',')) {
                                $mobile = explode(',', $mobile, 2)[0];
                            } else if (str_contains($row2['Mobile'], ' ')) {
                                $mobile = explode(' ', $mobile, 2)[0];
                            }
                            $mobile = trim($mobile);

                            echo '<td>';
                            if (can('create', MENU_ID)) {
                                echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobile . '&message=' . $text . '&route=TRANS&TemplateID=1707175050055092681&format=JSON" class="sms_link">' . $mobile . '</a>';
                            } else {
                                echo '<a href="javascript:void(0)"
                                                    class="text-secondary disabled"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="You don\'t have permission to send SMS">
                                                    ' . $mobile . '
                                                </a>';
                            }
                            echo '</td>
                            <td>';

                            if (can("create", MENU_ID)) {
                                echo '<input type="checkbox" class="student form-check-input" id="student" name="student[' . $row1['Emp_Id'] . ']" value="' . $mobile . '">';
                            } else {
                                echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                        <input type="checkbox" class="form-check-input student disabled" disabled> 
                                    </span>';
                            }
                            echo '</td>
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

    <!-- Global Const Variable for can_send -->
    <script>
        const CAN_SEND = <?= can('create', MENU_ID) ? 'true' : 'false' ?>;
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

    <!-- Send SMS -->
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
        async function send(url) {
            response = await fetch(url);
        }
        $('#send').on('click', () => {
            if (!CAN_SEND) {
                alert("You do not have permission to Send SMS");
                return;
            }
            absentees = []
            $(".student:checked").each(function() {
                absentees.push($(this).parent().siblings().eq(6).children().attr('href'));
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
</body>

</html>