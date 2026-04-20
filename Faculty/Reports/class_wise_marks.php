<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 118);

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
        margin-left: 72px;
        max-width: 1400px;
        max-height: 500px;
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    #max {
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
        <div class="container form-container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="class_type" id="section_wise" checked value="Section_Wise">
                        <label class="form-check-label" for="section_wise">Section Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="class_type" id="class_wise" value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <label for="class_name" class="col-lg-2 col-form-label">Class</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam(this.value)">
                        <option value="selectclass" selected disabled>--Select Class--</option>
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
            <div class="row justify-content-center mt-3" id="section_row">
                <label for="class_name" class="col-lg-2 col-form-label">Section</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Section" id="sec">
                        <option value="selectsection" selected disabled>--Select Section--</option>
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
            <div class="container">
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-4">
                        <div class="col-lg-4">
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
                            <div class="btn-wrapper"
                                <?php if (!can('export', MENU_ID)) { ?>
                                title="You don't have permission to export this report"
                                <?php } ?>>
                                <button class="btn btn-success" onclick="return false;" id="export" <?php echo !can('export', MENU_ID) ? 'disabled' : ''; ?>>Export To Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Class Wise Marks Report</b></h3>
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
                <td style="font-size:20px;color:red">Name of Class:</td>
                <td id="class_label" style="font-size:20px;"></td>
            </tr>
            <tr>
                <td style="font-size:20px;color:red">Name of Exam:</td>
                <td id="exam_label" style="font-size:20px;"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th>S.No</th>
                    <th>Id No.</th>
                    <th style="text-align: center;">Name</th>
                    <th>Max Marks</th>
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $class_type = $_POST['class_type'];
                        if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                            if ($class_type == "Section_Wise") {
                                echo "<script>document.getElementById('section_wise').checked = 'true';</script>";
                                if ($_POST['Section']) {
                                    $section = $_POST['Section'];
                                    echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                                    echo "<script>document.getElementById('class_label').innerHTML = '" . $class . " " . $section . "'</script>";
                                } else {
                                    echo "<script>alert('Section Not Selected')</script>";
                                }
                            } else if ($class_type == "Class_Wise") {
                                echo "<script>document.getElementById('class_wise').checked = 'true';</script>";
                                echo "<script>document.getElementById('section_row').hidden = 'hidden';</script>";
                            }
                            echo "<script>document.getElementById('class_label').innerHTML = '" . $class . "'</script>";
                            if ($_POST['Exam']) {
                                $exam = $_POST['Exam'];
                                echo "<script>document.getElementById('exam_label').innerHTML = '" . $exam . "'</script>";
                                if ($class_type == "Section_Wise") {
                                    $sql = "SELECT * FROM `stu_marks` WHERE Class = '$class' AND Section = '$section' AND Exam = '$exam'";
                                    $ids_sql = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                                } else {
                                    $sql = "SELECT * FROM `stu_marks` WHERE Class = '$class' AND Exam = '$exam'";
                                    $ids_sql = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Stu_Class = '$class'");
                                }
                                $mark_type = $_POST['mark_type'];
                                //Arrays
                                $details = array();

                                if ($mark_type == "GPA") {
                                    echo "<script>document.getElementById('gpa').checked = true;</script>";
                                } else {
                                    echo "<script>document.getElementById('normal').checked = true;</script>";
                                }
                                $exm_sql = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class' AND Exam = '$exam'");
                                $max = 0;
                                while ($row2 = mysqli_fetch_assoc($exm_sql)) {
                                    $max = $row2['Max_Marks'];
                                }
                                $sub_sql = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                                $sub_count = mysqli_num_rows($sub_sql);
                                $result = mysqli_query($link, $sql);
                                if (mysqli_num_rows($result) == 0) {
                                    echo "<script>alert('Data Not Available')</script>";
                                } else {
                                    $ids = array();
                                    $names = array();
                                    while ($id_row = mysqli_fetch_assoc($ids_sql)) {
                                        array_push($ids, $id_row['Id_No']);
                                        $names[$id_row['Id_No']] = $id_row['First_Name'];
                                    }

                                    $subs = array();
                                    $total = 0;
                                    while ($row1 = mysqli_fetch_assoc($sub_sql)) {
                                        array_push($subs, $row1['Max_Marks']);
                                        $total += (int)$row1['Max_Marks'];
                                        echo "<th style='text-align: center;'>" . $row1['Subjects'] . "</th>";
                                    }

                                    echo "<th>Total</th>";
                                    if ($mark_type == "Normal") {
                                        echo "<th>Percentage</th>
                                    <th>Grade</th>";
                                    } else {
                                        echo "<th>Grade</th>
                                    <th>GPA</th>";
                                    }
                                    echo "<th>Rank</th>";

                                    echo "</tr>";
                                    echo "</thead>";
                                    echo "<tbody id='tbody'>";
                                    echo "<tr>";
                                    $temp_total = array();
                                    //Getting Totals
                                    foreach ($ids as $id) {
                                        $sql1 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
                                        if (mysqli_num_rows($sql1) == 0) {
                                            $temp_total[$id] = '0';
                                        } else {
                                            while ($row1 = mysqli_fetch_assoc($sql1)) {
                                                $temp_total[$id] = $row1['Total'];
                                            }
                                        }
                                    }

                                    arsort($temp_total); //Sorting Based on Totals
                                    $rank = 1;
                                    $t = array_values($temp_total)[0];

                                    foreach (array_keys($temp_total) as $id) {
                                        $sql3 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
                                        if (mysqli_num_rows($sql3) == 0) {
                                            $details[$id]['First_Name'] = $names[$id];
                                            $details[$id]['Total'] = '0';
                                        } else {
                                            while ($row3 = mysqli_fetch_assoc($sql3)) {
                                                $details[$id]['First_Name'] = $names[$id];
                                                $temp = array();
                                                for ($sub = 1; $sub <= $sub_count; $sub++) {
                                                    array_push($temp, $row3['sub' . $sub]);
                                                }
                                                $details[$id]['Marks'] = $temp;
                                                $details[$id]['Tot'] = $row3['Total'];
                                            }
                                        }
                                    }
                                    $i = 1;
                                    foreach (array_keys($details) as $id) {
                                        echo '<tr>
                      <td style="height:60px;">' . $i . '</td>
                      <td>' . $id . '</td>
                      <td style="text-align: center;">' . $details[$id]['First_Name'] . '</td>
                      <td id="max" style="text-align: center;">' . $max . '</td>';
                                        for ($sub = 1; $sub <= $sub_count; $sub++) {
                                            echo '<td style="text-align: center;">' . $details[$id]['Marks'][$sub - 1];
                                        }
                                        echo '<td>' . $details[$id]['Tot'] . '/' . $total . '</td>';
                                        if ($mark_type == "Normal") {
                                            //Calculating Percentage and Grades
                                            $percentage = round(($details[$id]['Tot'] / $total) * 100, 1);
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
                                            echo '<td style="text-align:center">' . $percentage . '</td>
                                    <td>' . $grade . '</td>';
                                        } else {
                                            $grades = array();
                                            //Calculting Subject Wise Grades
                                            for ($sub = 1; $sub <= $sub_count; $sub++) {
                                                $mark = ((int)$details[$id]['Marks'][$sub - 1] / $subs[$sub - 1]) * 100;
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
                                            echo '<td style="text-align: center;">' . $grade . '</td>
                                        <td style="text-align: center;">' . $avg . '</td>';
                                        }
                                        if ($details[$id]['Tot'] != $t) {
                                            $rank++;
                                            $t = $details[$id]['Tot'];
                                        }
                                        echo '<td style="text-align: center;">' . $rank . '</td>';
                                        echo '</tr>';
                                        $i++;
                                    }
                                }
                            } else {
                                echo "<script>alert('Exam Not Selected')</script>";
                            }
                        } else {
                            echo "<script>alert('Class Not Selected')</script>";
                        }
                    }
                    ?>
                </tr>
                </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        let section_row = document.getElementById('section_row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    if (!section_row.hidden) {
                        section_row.hidden = 'hidden';
                    }
                    break;
                case 'section_wise':
                    if (section_row.hidden) {
                        section_row.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Fetch Exam -->
    <script type="text/javascript">
        function fetchExam(cls) {
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
        }
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            stuclass = '<?php echo $class; ?>';
            stusection = '<?php echo $section; ?>';
            exam = '<?php echo $exam; ?>';
            filename = stuclass + stusection + "_" + exam;
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

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table><tr><td style='font-size:25px;'>Name of Class:</td><td><?php echo $class . ' ' . $section; ?></td></tr><br></table></div>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table style='margin-bottom:20px;'><tr><td style='font-size:25px;'>Name of Exam:</td><td><?php echo $exam; ?></td></tr></table></div>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>