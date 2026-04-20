<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 76);

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
                <th>Class</th>
                <th>SMS Link</th>
                <th>
                    <?php if (can('create', MENU_ID)) { ?>
                        <input type="checkbox"
                            class="form-check-input"
                            id="select_all"
                            onclick="toggle(this)">
                        <label for="select_all">Select All</label>

                    <?php } else { ?>
                        <span class="tooltip-wrapper"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="You don't have permission to select all and send SMS">

                            <input type="checkbox"
                                class="form-check-input disabled"
                                id="select_all"
                                disabled>

                            <label for="select_all">Select All</label>
                        </span>

                    <?php } ?>
                </th>
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
                            $query1 = mysqli_query($link, "SELECT Id_No FROM `attendance_daily` WHERE Date = '$date' AND AM IN ('A','L')");
                        } else if ($att_by == "PM") {
                            $query1 = mysqli_query($link, "SELECT Id_No FROM `attendance_daily` WHERE Date = '$date' AND PM IN ('A','L')");
                        }
                        if ($query1) {
                            if (mysqli_num_rows($query1) != 0) {
                                while ($row1  = mysqli_fetch_assoc($query1)) {
                                    array_push($ids, $row1['Id_No']);    //Fetching Id Nos of Absentees Students on that Date

                                    $query2 = mysqli_query($link, "SELECT First_Name,Mobile,Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '" . $row1['Id_No'] . "'");
                                    if ($query2) {
                                        if (mysqli_num_rows($query2) != 0) {
                                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                                //Fetching Mobile Nos of Absentees Students on that Date
                                                if (str_contains($row2['Mobile'], ',')) {
                                                    $details[$row1['Id_No']] = array($row2['First_Name'], explode(',', $row2['Mobile'], 2)[0], $row2['Stu_Class'] . ' ' . $row2['Stu_Section']);
                                                } else if (str_contains($row2['Mobile'], ' ')) {
                                                    $details[$row1['Id_No']] = array($row2['First_Name'], explode(' ', $row2['Mobile'], 2)[0], $row2['Stu_Class'] . ' ' . $row2['Stu_Section']);
                                                } else {
                                                    $details[$row1['Id_No']] = array($row2['First_Name'], $row2['Mobile'], $row2['Stu_Class'] . ' ' . $row2['Stu_Section']);
                                                }
                                            }
                                        } else {
                                            echo "<script>alert('No Student Found with " . $row1['Id_No'] . "')</script>";
                                        }
                                    } else {
                                        echo "<script>alert('Mobile Number Query Error!')</script>";
                                    }
                                }
                                if ($query2) {
                                    $i = 1;
                                    foreach ($ids as $id) {
                                        $text = "Dear Sir/Madam,Your Child " . $details[$id][0] . " is absent to school today's ";
                                        if ($att_by == "AM") {
                                            $text .= "Morning";
                                        } else {
                                            $text .= "Afternoon";
                                        }
                                        $text .= " Principal,Victory HS,KDR";
                                        $text = urlencode($text);
                                        echo '<tr>
                                        <td>' . $i . '</td>
                                        <td>' . $id . '</td>
                                        <td>' . $details[$id][0] . '</td>
                                        <td>' . $details[$id][2] . '</td>
                                        <td>';
                                        if (can('create', MENU_ID)) {
                                            echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $details[$id][1] . '&message=' . $text . '&route=TRANS&TemplateID=1707162010221207287&format=JSON" class="sms_link">' . $details[$id][1] . '</a>';
                                        } else {
                                            echo '<a href="javascript:void(0)"
                                                    class="text-secondary disabled"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="You don\'t have permission to send SMS">
                                                    ' . $details[$id][1] . '
                                                </a>';
                                        }
                                        echo '</td>
                                            <td>';

                                        if (can("create", MENU_ID)) {
                                            echo '<input type="checkbox" class="form-check-input student" name="student[' . $id . ']" value="' . $details[$id][1] . '">';
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
                            } else {
                                echo "<script>alert('No Absentees on " . $date . "!!')</script>";
                            }
                        } else {
                            echo "<script>alert('Attendance Query Error!')</script>";
                        }
                    }
                    ?>
                </tr>
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
                absentees.push($(this).parent().siblings().eq(4).children().attr('href'));
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