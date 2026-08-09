<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 23);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<?php
function month($date)
{
    $arr = explode('-', $date);
    $temp = array();
    switch ($arr[1]) {
        case '01':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "January";
            break;
        case '02':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "February";
            break;
        case '03':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "March";
            break;
        case '04':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "April";
            break;
        case '05':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "May";
            break;
        case '06':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "June";
            break;
        case '07':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "July";
            break;
        case '08':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "August";
            break;
        case '09':
            array_push($temp, str_split($arr[1])[1]);
            $arr[1] = "September";
            break;
        case '10':
            array_push($temp, $arr[1]);
            $arr[1] = "October";
            break;
        case '11':
            array_push($temp, $arr[1]);
            $arr[1] = "November";
            break;
        case '12':
            array_push($temp, $arr[1]);
            $arr[1] = "December";
            break;
    }
    array_push($temp, $arr[1]);
    return $temp;
}
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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="stu_type" id="class_wise" onchange="stuType()" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="stu_type" id="single" onchange="stuType()" value="Single">
                        <label class="form-check-label" for="single">Single</label>
                    </div>
                </div>
                <div class="col-lg-3" id="id_row" hidden>
                    <input type="text" class="form-control" placeholder="Enter Id No." oninput="this.value = this.value.toUpperCase()" onchange="fetchExam()" name="Id_No" id="id_no">
                </div>
            </div>
            <div class="row justify-content-center mt-5" id="class_row">
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam()" aria-label="Default select example">
                        <option selected disabled>-- Select Class --</option>
                        <option value="PreKG">PreKG</option>
                        <option value="LKG">LKG</option>
                        <option value="UKG">UKG</option>
                        <?php
                        for ($i = 1; $i <= 8; $i++) {
                            echo '<option value="' . $i . ' CLASS">' . $i . ' CLASS</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="p-2 col-lg-4 rounded">
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
            <div class="row justify-content-center mt-3">
                <label for="exam_name" class="col-lg-2 col-form-label">Examination Name</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Exam" id="exam">
                        <option value="selectexam">--Select Exam--</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <button class="btn btn-primary" type="submit" name="Ok">OK</button>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <button class="btn btn-success" onclick="printDiv();return false;">Print</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Mark List</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="tbody">
        <?php

        if (isset($_POST['Ok'])) {
            $months = array(
                'June',
                'July',
                'August',
                'September',
                'October',
                'November',
                'December',
                'January',
                'February',
                'March',
                'April'
            );
            //Arrays
            $working_days = array();
            $days = array();
            $working_months = array();

            foreach ($months as $month) {
                $working_query = mysqli_query($link, "SELECT Working_Days AS Days FROM `working_days` WHERE Month = '$month'");
                if (mysqli_num_rows($working_query) == 0) {
                    //echo "<script>alert('" . $month . " Not Available!!')</script>";
                    $working_days[$month] = '';
                } else {
                    while ($working_row = mysqli_fetch_assoc($working_query)) {
                        if ((int)$working_row['Days'] != '') {
                            $working_days[$month] = (int)$working_row['Days'];
                        }
                    }
                }
            }

            $mon_arr = array();
            foreach (array_keys($working_days) as $mon) {
                array_push($mon_arr, $mon);
            }
            $stu_type = $_POST['stu_type'];
            if ($stu_type == "Single") {
                echo "<script>
                document.getElementById('single').checked = true;
                document.getElementById('id_row').hidden = '';
                document.getElementById('class_row').hidden = 'hidden';
                </script>";

                //Arrays
                $subs = array();
                $marks = array();


                if ($_POST['Id_No']) {
                    $id = $_POST['Id_No'];
                    echo "<script>document.getElementById('id_no').value = '" . $id . "'</script>";
                    if ($_POST['Exam']) {
                        $exam = $_POST['Exam'];
                        $class_sql = mysqli_query($link, "SELECT First_Name,Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '$id'");
                        while ($class_row = mysqli_fetch_assoc($class_sql)) {
                            $name = $class_row['First_Name'];
                            $class = $class_row['Stu_Class'];
                            $section = $class_row['Stu_Section'];
                        }

                        $query1 = mysqli_query($link, "SELECT Subjects,Max_Marks FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            array_push($subs, array($row1['Subjects'], $row1['Max_Marks']));
                        }

                        $total_max = 0;
                        foreach ($subs as $sub) {
                            $total_max += $sub[1];
                        }

                        foreach (array_keys($working_days) as $month) {
                            $days_query = mysqli_query($link, "SELECT $month FROM `stu_att_master` WHERE Id_No = '$id'");
                            if (mysqli_num_rows($days_query) == 0) {
                                $days[$month]['Present'] = '';
                            } else {
                                while ($days_row = mysqli_fetch_assoc($days_query)) {
                                    if ($days_row[$month] != '0') {
                                        $days[$month]['Present'] = $days_row[$month];
                                    } else {
                                        $days[$month]['Present'] = '';
                                    }
                                }
                            }
                        }

                        $query2 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");

                        $query3 = mysqli_query($link, "SELECT Max_Marks FROM `class_wise_examination` WHERE Class = '$class' AND Exam = '$exam'");
                        $max_marks = mysqli_fetch_row($query3)[0];

                        $temp = array();
                        while ($row2 = mysqli_fetch_assoc($query2)) {
                            for ($i = 1; $i <= count($subs); $i++) {
                                array_push($temp, $row2['sub' . $i]);
                            }
                            $temp['Total'] = $row2['Total'];
                            $percentage = round(($row2['Total'] / $total_max) * 100, 1);
                            if ($percentage >= 80 && $percentage <= 100) {
                                $grade = "Excellent";
                            } else if ($percentage >= 70 && $percentage < 80) {
                                $grade = "Good";
                            } else if ($percentage >= 60 && $percentage < 70) {
                                $grade = "Satisfactory";
                            } else if ($percentage >= 50 && $percentage < 60) {
                                $grade = "Above Average";
                            } else if ($percentage >= 35 && $percentage < 50) {
                                $grade = "Average";
                            } else if ($percentage > 0 && $percentage < 35) {
                                $grade = "Below Average";
                            } else {
                                $grade = "";
                            }
                            $temp['Percentage'] = $percentage;
                            $temp['Grade'] = $grade;
                        }
                        $marks[$id] = $temp;


                        echo '
                            <div class="container" style="border:2px dashed black; margin-bottom:3%;">
                                <table style="width:100%;">
                                    <tr>
                                        <td>
                                            <img src="' . $_SESSION['school_db']['Media_Root_Dir'] . "/Victory Logo.png" . '" width="60px"/>
                                        </td>
                                        <td style="width:75%;height:100%;font-size:20px;font-weight:bold;text-align:center;">FUTUREGEN ENGLISH MEDIUM SCHOOL<br>RLY KODUR</td>
                                        <td>Phone No:9558494484</td>
                                    </tr>
                                </table>
                                <table style="width:100%;border:2px solid black;">
                                    <tr>
                                        <td style="margin-top:20px;padding-left:10px;width:65%;"><b>Student ID:         ' . $id . '</b></td>
                                        <td style="margin-top:20px;"><b>Name:         ' . $name . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="margin-top:20px;padding-left:10px;width:65%;"><b>Name of the Examination:         ' . $exam . '</b></td>
                                        <td style="margin-top:20px;padding-left:10px;"><b>Class:         ' . $class . ' ' . $section . '</b></td>
                                    </tr>
                                </table>
                                <div class="main-container" style="margin-top:20px;display:flex;gap:150px;">
                                    <div class="container" style="border:2px solid black;width:40%;height:200px;">
                                    <table>
                                        <thead>
                                            <th style="border-bottom:2px solid black;">Subject</th>
                                            <th style="border-bottom:2px solid black;padding-left:50px;">Max Marks</th>
                                            <th style="border-bottom:2px solid black;padding-left:50px;">Obtained</th>
                                        </thead>
                                        <tbody>
                                    ';
                        $count = 0;
                        foreach ($subs as $sub) {
                            echo '
                                        <tr>
                                            <td style = "font-size:13px;font-weight:bold;font-family:' . 'Arial' . '">' . $sub[0] . '</td>
                                            <td style = "font-size:13px;padding-left:55px;font-weight:bold;font-family:' . 'Arial' . '">' . $sub[1] . '</td>
                                            <td style = "font-size:13px;padding-left:55px;font-weight:bold;font-family:' . 'Arial' . '">' . $marks[$id][$count] . '</td>
                                        <tr>';
                            $count++;
                        }
                        echo '</tbody>
                                </table>
                                    </div>
                                    <div class="container" style="border:2px solid black;width:30%;height:100%;">
                                        <table>
                                        <thead>
                                            <tr>
                                                <td colspan="3" style="text-align:center;border-bottom:2px solid black;">Days</td>
                                            </tr>
                                            <tr>
                                                <th style="border-bottom:2px solid black;border-right:2px solid black;">Month</th>
                                                <th style="border-bottom:2px solid black;border-right:2px solid black;">Working</th>
                                                <th style="border-bottom:2px solid black;">Present</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                        foreach ($mon_arr as $mon) {
                            echo '
                                        <tr>
                                            <td style = "width:250px;font-size:12px;font-family:' . 'Arial' . '">' . $mon . '</td>
                                            <td style = "font-size:10px;padding-left:10px;border-left:2px solid black;font-weight:bold;font-family:' . 'Arial' . '">' . $working_days[$mon] . '</td>
                                            <td style = "font-size:10px;padding-left:30px;border-left:2px solid black;font-weight:bold;font-family:' . 'Arial' . '">' . $days[$id][$mon]['Present'] . '</td>
                                        <tr>';

                            $count++;
                            $i++;
                        }
                        echo '</body>
                                        </table>
                                    </div>
                                </div>
                                <table style="padding-left:50px;margin-top:-50px;">
                                    <tr>
                                        <td><b style="padding-right:75px;">Total</b>' . $marks[$id]['Total'] . '</td>
                                    </tr>
                                    <tr>
                                        <td><b style="padding-right:35px;">Percentage</b>' . $marks[$id]['Percentage'] . '</td>
                                    </tr>
                                    <tr>
                                        <td><b style="padding-right:65px;">Grade</b>' . $marks[$id]['Grade'] . '</td>
                                    </tr>
                                </table>
                                <table style="padding-top:40px;">
                                    <tr>
                                        <td style="width:40%;">Class Teacher</td>
                                        <td style="width:70%;">
                                        <img src="/Viswateja/Images/sign.png" width="70px" style="bottom:30px;left:80px;position:relative;" />
                                        Head Master</td>
                                        <td>Parent/Guardian</td>
                                    </tr>
                                </table>
                            </div>
                            ';
                    } else {
                        echo "<script>alert('Please Select Exam!!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Enter Id_No')</script>";
                }
            } else {
                if ($_POST['Class']) {
                    $class = $_POST['Class'];
                    echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                    $s = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class'");
                    echo "<script>document.getElementById('exam').innerHTML = '';</script>";
                    if (mysqli_num_rows($s) > 0) {
                        echo "<script>$('#exam').html('<option value=" . 'selectexam' . " disabled selected>--Select Exam--</option>');</script>";
                        while ($r = mysqli_fetch_assoc($s)) {
                            echo "<script>$('#exam').append('<option value=' + '" . $r['Exam'] . "' + '>" . $r['Exam'] . "</option>');</script>";
                        }
                    } else {
                        echo "<script>$('#exam').html('<option selected disabled>No Exam Found</option>');</script>";
                    }
                    if ($_POST['Section']) {
                        $section = $_POST['Section'];
                        echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                        if ($_POST['Exam']) {
                            $exam = $_POST['Exam'];
                            echo "<script>document.getElementById('exam').value = '" . $exam . "'</script>";

                            //Arrays
                            $ids = array();
                            $names = array();
                            $subs = array();
                            $marks = array();

                            //Queries
                            $query1 = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                            $query2 = mysqli_query($link, "SELECT Subjects,Max_Marks FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");

                            while ($row1 = mysqli_fetch_assoc($query1)) {
                                array_push($ids, $row1['Id_No']);
                                $names[$row1['Id_No']] = $row1['First_Name'];
                            }
                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                array_push($subs, array($row2['Subjects'], $row2['Max_Marks']));
                            }

                            $total_max = 0;
                            foreach ($subs as $sub) {
                                $total_max += $sub[1];
                            }

                            foreach ($ids as $id) {
                                foreach (array_keys($working_days) as $month) {
                                    $days_query = mysqli_query($link, "SELECT $month FROM `stu_att_master` WHERE Id_No = '$id'");
                                    if (mysqli_num_rows($days_query) == 0) {
                                        $days[$id][$month]['Present'] = '';
                                    } else {
                                        while ($days_row = mysqli_fetch_assoc($days_query)) {
                                            if ($days_row[$month] != '0') {
                                                $days[$id][$month]['Present'] = $days_row[$month];
                                            } else {
                                                $days[$id][$month]['Present'] = '';
                                            }
                                        }
                                    }
                                }
                            }

                            foreach ($ids as $id) {
                                $query3 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");

                                $temp = array();
                                while ($row3 = mysqli_fetch_assoc($query3)) {
                                    for ($i = 1; $i <= count($subs); $i++) {
                                        array_push($temp, $row3['sub' . $i]);
                                    }
                                    $temp['Total'] = $row3['Total'];
                                    $percentage = round(($row3['Total'] / $total_max) * 100, 1);
                                    if ($percentage >= 80 && $percentage <= 100) {
                                        $grade = "Excellent";
                                    } else if ($percentage >= 70 && $percentage < 80) {
                                        $grade = "Good";
                                    } else if ($percentage >= 60 && $percentage < 70) {
                                        $grade = "Satisfactory";
                                    } else if ($percentage >= 50 && $percentage < 60) {
                                        $grade = "Above Average";
                                    } else if ($percentage >= 35 && $percentage < 50) {
                                        $grade = "Average";
                                    } else if ($percentage > 0 && $percentage < 35) {
                                        $grade = "Below Average";
                                    } else {
                                        $grade = "";
                                    }
                                    $temp['Percentage'] = $percentage;
                                    $temp['Grade'] = $grade;
                                }
                                $marks[$id] = $temp;
                            }

                            foreach ($ids as $id) {
                                echo '
                            <div class="container" style="border:2px dashed black; margin-bottom:3%;">
                                <table style="width:100%;">
                                    <tr>
                                        <td>
                                            <img src="' . $_SESSION['school_db']['Media_Root_Dir'] . "/Victory Logo.png" . '" width="60px"/>
                                        </td>
                                        <td style="width:75%;height:100%;font-size:20px;font-weight:bold;text-align:center;">FUTUREGEN ENGLISH MEDIUM SCHOOL<br>RLY KODUR</td>
                                        <td>Phone No:9558494484</td>
                                    </tr>
                                </table>
                                <table style="width:100%;border:2px solid black;">
                                    <tr>
                                        <td style="margin-top:20px;padding-left:10px;width:65%;"><b>Student ID:         ' . $id . '</b></td>
                                        <td style="margin-top:20px;"><b>Name:         ' . $names[$id] . '</b></td>
                                    </tr>
                                    <tr>
                                        <td style="margin-top:20px;padding-left:10px;width:65%;"><b>Name of the Examination:         ' . $exam . '</b></td>
                                        <td style="margin-top:20px;padding-left:10px;"><b>Class:         ' . $class . ' ' . $section . '</b></td>
                                    </tr>
                                </table>
                                <div class="main-container" style="margin-top:20px;display:flex;gap:150px;">
                                    <div class="container" style="border:2px solid black;width:40%;height:200px;">
                                    <table>
                                        <thead>
                                            <th style="border-bottom:2px solid black;">Subject</th>
                                            <th style="border-bottom:2px solid black;padding-left:50px;">Max Marks</th>
                                            <th style="border-bottom:2px solid black;padding-left:50px;">Obtained</th>
                                        </thead>
                                        <tbody>
                                    ';
                                $count = 0;
                                foreach ($subs as $sub) {
                                    echo '
                                        <tr>
                                            <td style = "font-size:13px;font-weight:bold;font-family:' . 'Arial' . '">' . $sub[0] . '</td>
                                            <td style = "font-size:13px;padding-left:55px;font-weight:bold;font-family:' . 'Arial' . '">' . $sub[1] . '</td>
                                            <td style = "font-size:13px;padding-left:55px;font-weight:bold;font-family:' . 'Arial' . '">' . $marks[$id][$count] . '</td>
                                        <tr>';
                                    $count++;
                                }
                                echo '</tbody>
                                </table>
                                    </div>
                                    <div class="container" style="border:2px solid black;width:30%;height:100%;">
                                        <table>
                                        <thead>
                                            <tr>
                                                <td colspan="3" style="text-align:center;border-bottom:2px solid black;">Days</td>
                                            </tr>
                                            <tr>
                                                <th style="border-bottom:2px solid black;border-right:2px solid black;">Month</th>
                                                <th style="border-bottom:2px solid black;border-right:2px solid black;">Working</th>
                                                <th style="border-bottom:2px solid black;">Present</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                                foreach ($mon_arr as $mon) {
                                    echo '
                                        <tr>
                                            <td style = "width:250px;font-size:12px;font-family:' . 'Arial' . '">' . $mon . '</td>
                                            <td style = "font-size:10px;padding-left:10px;border-left:2px solid black;font-weight:bold;font-family:' . 'Arial' . '">' . $working_days[$mon] . '</td>
                                            <td style = "font-size:10px;padding-left:30px;border-left:2px solid black;font-weight:bold;font-family:' . 'Arial' . '">' . $days[$ids[0]][$mon]['Present'] . '</td>
                                        <tr>';

                                    $count++;
                                    $i++;
                                }
                                echo '</body>
                                        </table>
                                    </div>
                                </div>
                                <table style="padding-left:50px;margin-top:-50px;">
                                    <tr>
                                        <td><b style="padding-right:75px;">Total</b>' . $marks[$id]['Total'] . '</td>
                                    </tr>
                                    <tr>
                                        <td><b style="padding-right:35px;">Percentage</b>' . $marks[$id]['Percentage'] . '</td>
                                    </tr>
                                    <tr>
                                        <td><b style="padding-right:65px;">Grade</b>' . $marks[$id]['Grade'] . '</td>
                                    </tr>
                                </table>
                                <table style="padding-top:40px;">
                                    <tr>
                                        <td style="width:40%;">Class Teacher</td>
                                        <td style="width:70%;">Correspondent/Principal</td>
                                        <td>Parent/Guardian</td>
                                    </tr>
                                </table>
                            </div>
                            ';
                            }
                        } else {
                            echo "<script>alert('Please Select Exam!!')</script>";
                        }
                    } else {
                        echo "<script>alert('Please Select Section!!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class!!')</script>";
                }
            }
        }

        ?>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>




    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        function stuType() {
            id_row = document.getElementById('id_row');
            class_row = document.getElementById('class_row');
            if (document.getElementById('class_wise').checked) {
                id_row.hidden = 'hidden';
                class_row.hidden = '';
            } else if (document.getElementById('single').checked) {
                id_row.hidden = '';
                class_row.hidden = 'hidden';
            }
        }
    </script>

    <!-- Fetch Exam -->
    <script type="text/javascript">
        function fetchExam() {
            if (document.getElementById('class_wise').checked) {
                cls = $('#class').val();
                $('#exam').html('');
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        class: cls
                    },
                    success: function(data) {
                        $("#exam").html(data);
                    }
                })
            } else if (document.getElementById('single').checked) {
                id = $('#id_no').val();
                console.log(id);
                $('#exam').html('');
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        Id_No: id
                    },
                    success: function(data) {
                        $("#exam").html(data);
                    }
                })
            }
        }
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>