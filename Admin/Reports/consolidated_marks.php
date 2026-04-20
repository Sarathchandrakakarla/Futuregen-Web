<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 27);

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
        margin-left: 8%;
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
                <div class="p-2 col-lg-4 rounded">
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
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
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
    </form>

    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Examinations</b></h3>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['ok'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $ids = array();
        $names = array();
        $exams = array();
        $totals = array();
        if ($_POST['Class']) {
            $class = $_POST['Class'];
            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
            $class_type = $_POST['class_type'];
            if ($class_type == "Section_Wise") {
                if ($_POST['Section']) {
                    $section = $_POST['Section'];
                    echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                    echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                    $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'";
                } else {
                    echo "<script>alert('Please Select Section!')</script>";
                }
            } else if ($class_type == "Class_Wise") {
                echo "<script>
                document.getElementById('class_wise').checked = true;
                document.getElementById('sec').hidden = 'hidden';
                </script>";
                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . "'</script>";
                $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class'";
            }
            $result = mysqli_query($link, $sql);
            if (mysqli_num_rows($result) == 0) {
                echo "<script>alert('Section Not Available!')</script>";
            } else {
                $_SESSION['Class'] = $class;
                if ($class_type == "Section_Wise") {
                    $_SESSION['Section'] = $section;
                }
                $_SESSION['class_type'] = $class_type;
                //Getting Exams of that Class
                $query1 = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class'");
                while ($row1 = mysqli_fetch_assoc($query1)) {
                    array_push($exams, $row1['Exam']);
                }
                echo '
                    <form action="" method="POST">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-3">
                    ';
                echo '<input type="checkbox" id="select_all" onclick="toggle(this)"><label for="select_all">Select All</label><br>';
                foreach ($exams as $exam) {
                    echo '<input type="checkbox" class="exam" id="' . $exam . '" value="' . $exam . '" name="exams[]"><label for="' . $exam . '">' . $exam . '</label><br>';
                }
                echo '
                            </div>
                        </div>
                    </div> 
                    <div class="container">
                        <div class="row justify-content-center mt-5">
                            <div class="col-lg-2">
                                <button class="btn btn-primary" type="submit" name="show">Show</button>
                            </div>
                        </div>
                    </div>
                    </form>
                    ';
            }
        } else {
            echo "<script>alert('Please Select Class!')</script>";
        }
    }
    ?>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Consolidated Marks Report</b></h3>
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
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Id No.</th>
                    <th>Name</th>
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        if (isset($_POST['exams'])) {
                            $ids = array();
                            $names = array();
                            $exams = array();
                            $totals = array();
                            $temp_totals = array();
                            $max_marks = array();
                            $class_type = $_SESSION['class_type'];
                            $class = $_SESSION['Class'];
                            echo "<script>document.getElementById('class').value = '" . $class . "';</script>";
                            if ($class_type == "Section_Wise") {
                                $section = $_SESSION['Section'];
                                echo "<script>document.getElementById('sec').value = '" . $section . "';</script>";
                                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                                $sql = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                            } else if ($class_type == "Class_Wise") {
                                echo "<script>
                                document.getElementById('class_wise').checked = true;
                                document.getElementById('sec').hidden = 'hidden';
                                </script>";
                                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . "'</script>";
                                $sql = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class'");
                            }
                            //Getting Id Nos and Names of that Class and Section
                            while ($row = mysqli_fetch_assoc($sql)) {
                                array_push($ids, $row['Id_No']);
                                $names[$row['Id_No']] = $row['First_Name'];
                            }
                            //Getting Selected Exams
                            foreach ($_POST['exams'] as $exam) {
                                array_push($exams, $exam);
                            }

                            //Getting Max Marks of each exam
                            $max_total = 0;
                            foreach ($exams as $exam) {
                                $max_query = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class' AND Exam = '$exam'");
                                while ($max_row = mysqli_fetch_assoc($max_query)) {
                                    $max_total += (int)$max_row['Max_Marks'];
                                }
                            }
                            //Getting Student Total in each Exam
                            foreach ($ids as $id) {
                                foreach ($exams as $exam) {
                                    $query2 = mysqli_query($link, "SELECT Total FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
                                    if ($query2) {
                                        if (mysqli_num_rows($query2) == 0) {
                                            $totals[$id][$exam] = 0;
                                        } else {
                                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                                $totals[$id][$exam] = $row2['Total'];
                                            }
                                        }
                                    }
                                }
                            }
                            foreach ($exams as $exam) {
                                echo "<th>" . $exam . "</th>";
                            }
                            echo '<th>Total</th>
                            <th>Percentage</th>
                            <th>Rank</th>
                            ';
                            echo '
                                    </tr>
                                </thead>
                                <tbody id="tbody">
                                    <tr>
                            ';
                            //Calculating Grand Total of all Totals
                            foreach ($ids as $id) {
                                $sum = 0;
                                foreach ($exams as $exam) {
                                    $sum += (int)$totals[$id][$exam];
                                }
                                $temp_totals[$id] = (int)$sum;
                            }
                            arsort($temp_totals); //Sorting Based on Grand Totals
                            $rank = 1;
                            $t = array_values($temp_totals)[0];
                            $i = 1;
                            foreach (array_keys($temp_totals) as $id) {
                                echo '
                            <td>' . $i . '</td>
                            <td>' . $id . '</td>
                            <td>' . $names[$id] . '</td>
                            ';
                                $sum = 0;
                                foreach ($exams as $exam) {
                                    $sum += (int)$totals[$id][$exam];
                                    echo '
                                <td>' . $totals[$id][$exam] . '</td>
                                ';
                                }
                                echo '
                                        <td>' . $sum . '</td>
                                        <td>' . round(((float)$sum / (float)$max_total) * 100, 1) . '%</td>
                                        ';
                                if ($sum != $t) {
                                    $rank++;
                                    $t = $sum;
                                }
                                echo '
                                        <td>' . $rank . '</td>';
                                echo '
                                </tr>
                                ';
                                $i++;
                            }
                            echo '
                                </tr>
                                </tbody>
                                </table>
                                </div>';
                        } else {
                            echo "<script>alert('No Exam Selected')</script>";
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
        let sec = document.getElementById('sec');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    if (!sec.hidden) {
                        sec.hidden = 'hidden';
                    }
                    break;
                case 'section_wise':
                    if (sec.hidden) {
                        sec.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Checkbox Select All -->
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByClassName('exam');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        $('.exam').on('click', function() {
            if ($('.exam').not(':checked').length == 0) {
                document.getElementById('select_all').checked = true;
            } else {
                document.getElementById('select_all').checked = false;
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            stuclass = '<?php echo $class; ?>';
            stusection = '<?php echo $section; ?>';
            filename = stuclass + stusection + '_consolidated_marks';
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
            window.frames["print_frame"].document.body.innerHTML += "<p style='font-size:20px;'><b>Class: </b> <?php if ($class == '' && $section == '') {
                                                                                                                    echo 'All Classes';
                                                                                                                } else {
                                                                                                                    echo $class . ' ' . $section;
                                                                                                                } ?></p>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>