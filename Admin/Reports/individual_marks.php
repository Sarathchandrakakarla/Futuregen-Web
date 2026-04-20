<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 21);

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
        max-width: 700px;
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
    <form action="" method="POST" autocomplete="off">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <label for="Id" class="col-sm-2 col-form-label"><b>Student Id No</b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="Id" id="id" oninput="this.value = this.value.toUpperCase()" required>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Exam_Type" id="all_exams" onchange="examType()" checked value="All_Exams">
                        <label class="form-check-label" for="all_exams">All Exams</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Exam_Type" id="single_exam" onchange="examType()" value="Single_Exam">
                        <label class="form-check-label" for="single_exam">Single Exam</label>
                    </div>
                </div>
                <div class="col-lg-3" id="exam_row" hidden>
                    <select class="form-select" name="Exam" id="exam" aria-label="Default select example">
                        <option selected disabled>-- Select Exam --</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mark_type" id="normal" checked value="Normal">
                        <label class="form-check-label" for="normal">Normal</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="mark_type" id="gpa" value="GPA">
                        <label class="form-check-label" for="gpa">GPA</label>
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
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-6">
                <h3><b>Student Marks Details Report</b></h3>
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['show'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $id = $_POST['Id'];
        echo "<script>document.getElementById('id').value = '" . $id . "'</script>";
        $examtype = $_POST['Exam_Type'];
        $mark_type = $_POST['mark_type'];
        if ($mark_type == "GPA") {
            echo "<script>document.getElementById('gpa').checked = true;</script>";
        } else {
            echo "<script>document.getElementById('normal').checked = true;</script>";
        }
        $cls_sql = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id'");
        if (mysqli_num_rows($cls_sql) == 0) {
            echo "<script>alert('No Student Found!')</script>";
        } else {
            while ($row1 = mysqli_fetch_assoc($cls_sql)) {
                $name = $row1['First_Name'];
                $class = $row1['Stu_Class'];
                $section = $row1['Stu_Section'];
            }
            if (str_contains($class, "Others") || str_contains($class, "Drop") || str_contains($class, "others") || str_contains($class, "drop")) {
                echo "<script>alert('Student Passedout/Drop')</script>";
            } else {
                echo '<div class="container">
                    <table class="table" border="1">
                        <thead id="thead">
                        <th>' . $id . '</th>
                        <th>' . $name . '</th>
                        <th>' . $class . '</th>
                        <th>' . $section . '</th>
                        </thead>
                    </table>
                    </div>';
                //If Exam Type is Single Exam
                if ($examtype == "Single_Exam") {
                    if ($_POST['Exam']) {
                        $exam = $_POST['Exam'];
                        $subs = array();
                        $marks = array();
                        $sub_max = array();

                        $query1 = mysqli_query($link, "SELECT Subjects,Max_Marks FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                        $max_tot = 0;
                        while ($row = mysqli_fetch_assoc($query1)) {
                            array_push($subs, $row['Subjects']);
                            $sub_max[$row['Subjects']] = $row['Max_Marks'];
                            $max_tot += (int)$row['Max_Marks'];
                        }
                        $sub_max['Total'] = $max_tot;

                        $sub_count = count($subs);

                        $query2 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
                        $tot = 0;
                        while ($row5 = mysqli_fetch_assoc($query2)) {
                            for ($i = 1; $i <= $sub_count; $i++) {
                                array_push($marks, $row5['sub' . $i]);
                                $tot += (int)$row5['sub' . $i];
                            }
                            array_push($marks, $tot);
                        }
                        echo '<div class="container table-container" id="table-container">
                            <table class="table table-hover" border="1">
                            <tbody id="tbody">
                            <tr>';
                        echo '<td class="bg-secondary text-light" colspan="2" style="width:1000px;text-align:center;"><b>' . $exam . '</b></td></tr>';
                        $c = 0;
                        $tot_max = 0;
                        foreach ($subs as $sub) {
                            if ($marks[$c] != 'A') {
                                echo '<tr>
                                    <tr>
                                    <th style="height:30px;">' . $sub . '</th>
                                    <td style="text-align:center">' . $marks[$c] . '/' . $sub_max[$sub] . '</td>
                                    </tr>
                                    </tr>';
                            } else {
                                echo '<tr>
                                        <tr>
                                        <th style="height:30px;">' . $sub . '</th>
                                        <td style="text-align:center">' . $marks[$c] . '</td>
                                        </tr>
                                        </tr>';
                            }
                            $c++;
                        }
                        echo '<tr>
                            <tr>
                            <th style="height:30px;">Total</th>
                            <td style="text-align:center">' . $marks[$c] . '/' . $max_tot . '</td>
                            </tr>
                            </tr>';
                        if ($mark_type == "Normal") {
                            $percentage = round(((int)$marks[$c] / (int)$max_tot) * 100, 2);
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
                            echo '<tr>
                                <tr>
                                    <th style="height:30px;">Percentage</th>
                                    <td style="text-align:center">' . $percentage . '</td>
                                </tr>
                                </tr>
                                <tr>
                                <tr>
                                    <th style="height:30px;">Grade</th>
                                    <td style="text-align:center">' . $grade . '</td>
                                </tr>
                                </tr>';
                        } else {
                            $grades = array();

                            //Calculating Subject Wise Grades
                            for ($sub = 1; $sub <= $sub_count; $sub++) {
                                $mark = ((int)$marks[$sub - 1] / (int)$sub_max[$subs[$sub - 1]]) * 100;
                                if ($mark >= 91 && $mark <= 100) {
                                    $grades['sub' . $sub] = array("A1", 10);
                                } else if ($mark >= 81 && $mark <= 90) {
                                    $grades['sub' . $sub] = array("A2", 9);
                                } else if ($mark >= 71 && $mark <= 80) {
                                    $grades['sub' . $sub] = array("B1", 8);
                                } else if ($mark >= 61 && $mark <= 70) {
                                    $grades['sub' . $sub] = array("B2", 7);
                                } else if ($mark >= 51 && $mark <= 60) {
                                    $grades['sub' . $sub] = array("C1", 6);
                                } else if ($mark >= 41 && $mark <= 50) {
                                    $grades['sub' . $sub] = array("C2", 5);
                                } else if ($mark >= 35 && $mark <= 40) {
                                    $grades['sub' . $sub] = array("D1", 4);
                                } else if ($mark >= 0 && $mark <= 34) {
                                    $grades['sub' . $sub] = array("E", 3);
                                }
                            }
                            //Calculating Average of grade points
                            $sum = 0;
                            for ($sub = 1; $sub <= $sub_count; $sub++) {
                                $sum += $grades['sub' . $sub][1];
                            }
                            $avg = round($sum / $sub_count, 1);
                            if ($avg == 10) {
                                $grade = "A1";
                            } else if ($avg >= 9 && $avg < 10) {
                                $grade = "A2";
                            } else if ($avg >= 8 && $avg < 9) {
                                $grade = "B1";
                            } else if ($avg >= 7 && $avg < 8) {
                                $grade = "B2";
                            } else if ($avg >= 6 && $avg < 7) {
                                $grade = "C1";
                            } else if ($avg >= 5 && $avg < 6) {
                                $grade = "C2";
                            } else if ($avg >= 4 && $avg < 5) {
                                $grade = "D1";
                            } else if ($avg >= 3 && $avg < 4) {
                                $grade = "D2";
                            } else if ($avg >= 0 && $avg < 3) {
                                $grade = "E1";
                            }
                            echo '<tr>
                                <tr>
                                    <th style="height:30px;">Grade</th>
                                    <td style="text-align:center">' . $grade . '</td>
                                </tr>
                                </tr>
                                <tr>
                                <tr>
                                    <th style="height:30px;">GPA</th>
                                    <td style="text-align:center">' . $avg . '</td>
                                </tr>
                                </tr>';
                        }
                    } else {
                        echo "<script>alert('Please Select Exam!')</script>";
                    }
                } else if ($examtype == "All_Exams") {
                    //Arrays
                    $exams = array();
                    $subs = array();
                    $max = array();
                    $marks = array();
                    $sub_count = array();
                    $full_marks = array();
                    $exm_sql = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class'");
                    while ($row2 = mysqli_fetch_assoc($exm_sql)) {
                        array_push($exams, $row2['Exam']);
                    }
                    foreach ($exams as $exam) {
                        $sub_sql = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                        $sub_count[$exam] = mysqli_num_rows($sub_sql);
                        $temp = array();
                        while ($row3 = mysqli_fetch_assoc($sub_sql)) {
                            array_push($temp, $row3['Subjects']);
                            $max[$exam][$row3['Subjects']] = $row3['Max_Marks'];
                        }
                        $subs[$exam] = $temp;
                        $temp = array();
                    }

                    foreach ($exams as $exam) {
                        $marks_sql = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
                        $temp1 = array();
                        while ($row4 = mysqli_fetch_assoc($marks_sql)) {
                            $i = 1;
                            foreach ($subs[$exam] as $sub) {
                                $temp1[$sub] = $row4['sub' . $i];
                                $i++;
                            }
                            $i = 1;
                            $full_marks[$exam] = $temp1;
                        }
                    }

                    echo '<div class="container table-container" id="table-container">
                        <table class="table table-hover" border="1">
                        <tbody id="tbody">
                        <tr>';

                    foreach ($exams as $exam) {
                        foreach (array_keys($full_marks) as $exm) {
                            if ($exam == $exm) {
                                if ($sub_count[$exam] != 0) {
                                    echo '<td class="bg-secondary text-light" colspan="2" style="width:1000px;text-align:center;"><b>' . $exam . '</b></td>';
                                    echo '</tr>
                                        <tr>';
                                    $tot = 0;
                                    $max_tot = 0;
                                    foreach ($subs[$exam] as $sub) {
                                        if ($full_marks[$exam][$sub] != 'A') {
                                            echo '<tr>
                                                <th style="height:30px;">' . $sub . '</th>
                                                <td style="padding-left:10px;">' . $full_marks[$exam][$sub] . '/' . $max[$exam][$sub] . '</td>
                                            </tr>';
                                        } else {
                                            echo '<tr>
                                                    <th style="height:30px;">' . $sub . '</th>
                                                    <td style="padding-left:20px;">' . $full_marks[$exam][$sub] . '</td>
                                                </tr>';
                                        }
                                        $tot += (int)$full_marks[$exam][$sub];
                                        $max_tot += $max[$exam][$sub];
                                    }
                                    echo '<tr>
                                        <th>Total</th>
                                        <td style="padding-left:10px;">' . $tot . '/' . $max_tot . '</td>
                                        </tr>';
                                    if ($mark_type == "Normal") {
                                        $percentage = round(((int)$tot / $max_tot) * 100, 2);
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
                                        echo '<tr>
                                            <th>Percentage</th>
                                            <td style="padding-left:10px;">' . $percentage . '</td>
                                            </tr>
                                            <tr>
                                            <th>Grade</th>
                                            <td style="padding-left:10px;">' . $grade . '</td>
                                            </tr>';
                                    } else {
                                        $grades = array();

                                        //Calculating Subject Wise Grades
                                        foreach ($subs[$exam] as $sub) {
                                            $mark = ((int)$full_marks[$exam][$sub] / (int)$max[$exam][$sub]) * 100;
                                            if ($mark >= 91 && $mark <= 100) {
                                                $grades[$sub] = array("A1", 10);
                                            } else if ($mark >= 81 && $mark <= 90) {
                                                $grades[$sub] = array("A2", 9);
                                            } else if ($mark >= 71 && $mark <= 80) {
                                                $grades[$sub] = array("B1", 8);
                                            } else if ($mark >= 61 && $mark <= 70) {
                                                $grades[$sub] = array("B2", 7);
                                            } else if ($mark >= 51 && $mark <= 60) {
                                                $grades[$sub] = array("C1", 6);
                                            } else if ($mark >= 41 && $mark <= 50) {
                                                $grades[$sub] = array("C2", 5);
                                            } else if ($mark >= 35 && $mark <= 40) {
                                                $grades[$sub] = array("D1", 4);
                                            } else if ($mark >= 0 && $mark <= 34) {
                                                $grades[$sub] = array("E", 3);
                                            }

                                            //Calculating Average of grade points
                                            $sum = 0;
                                            foreach ($subs[$exam] as $sub) {
                                                $sum += $grades[$sub][1];
                                            }
                                            $avg = round($sum / $sub_count[$exam], 1);
                                            if ($avg == 10) {
                                                $grade = "A1";
                                            } else if ($avg >= 9 && $avg < 10) {
                                                $grade = "A2";
                                            } else if ($avg >= 8 && $avg < 9) {
                                                $grade = "B1";
                                            } else if ($avg >= 7 && $avg < 8) {
                                                $grade = "B2";
                                            } else if ($avg >= 6 && $avg < 7) {
                                                $grade = "C1";
                                            } else if ($avg >= 5 && $avg < 6) {
                                                $grade = "C2";
                                            } else if ($avg >= 4 && $avg < 5) {
                                                $grade = "D1";
                                            } else if ($avg >= 3 && $avg < 4) {
                                                $grade = "D2";
                                            } else if ($avg >= 0 && $avg < 3) {
                                                $grade = "E1";
                                            }
                                        }
                                        echo '<tr>
                                                <th style="height:30px;">Grade</th>
                                                <td style="padding-left:10px;">' . $grade . '</td>
                                            </tr>
                                            <tr>
                                            <tr>
                                                <th style="height:30px;">GPA</th>
                                                <td style="padding-left:10px;">' . $avg . '</td>
                                            </tr>';
                                    }

                                    echo '</tr>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    ?>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        function examType() {
            exam_row = document.getElementById('exam_row');
            if (document.getElementById('all_exams').checked) {
                p = document.getElementById('all_exams').value;
                exam_row.hidden = 'hidden';
            } else if (document.getElementById('single_exam').checked) {
                p = document.getElementById('single_exam').value;
                exam_row.hidden = '';
                id = document.getElementById('id').value;
                $('#exam').html('');
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        Id: id
                    },
                    success: function(data) {
                        $('#exam').html(data);
                    }
                })
            }
        }
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<div class='container' style='display:flex;margin-left:30px;'><img src='<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png' alt='...' width='70px' style='margin-bottom:20px;'><h2 style='margin-left:150px;'>Student Marks Details Report</h2>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table><tr><th>Id No:</th><td><?php echo $id; ?></td></tr></table></div>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table><tr><th>Name:</th><td><?php echo $name; ?></td></tr></table></div>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table><tr><th>Name of Examination:<td><?php if ($examtype == "All_Exams") {
                                                                                                                                            echo "All Exams";
                                                                                                                                        } else {
                                                                                                                                            echo $exam;
                                                                                                                                        } ?>";
            window.frames["print_frame"].document.body.innerHTML += "</td> </tr> </table> </div><br>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>