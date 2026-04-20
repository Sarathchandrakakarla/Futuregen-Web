<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 18);

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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 1350px;
        max-height: 500px;
        overflow-x: scroll;
    }

    label {
        font-weight: bold;
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
        }
    }

    @media screen and (max-width:1405px) {
        .table-container {
            margin-left: 70px;
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
<!--  onload="location.replace('/Victory/test/construction.html')" -->

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST" id="inp_form">
        <div class="container form-container">
            <div class="row justify-content-center mt-5">
                <label for="class" class="col-lg-1 col-form-label">Class</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam(this.value)">
                        <option selected disabled>--Select Class--</option>
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
            <div class="row justify-content-center mt-3">
                <label for="subject_name" class="col-lg-1 col-form-label">Section</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Section" id="section">
                        <option selected disabled>--Select Section--</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <label for="exam_name" class="col-lg-1 col-form-label">Examination Name</label>
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
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3 id="heading1"><b>Class Wise Marks</b></h3>
                <br>
                <p style="color: red;">*For Absent enter 'A'</p>
            </div>
        </div>
    </div>
    <form action="" method="POST">
        <div class="container table-container">
            <table class="table table-striped table-hover" id="marks-table">
                <thead class="bg-secondary text-light">
                    <th>S.No</th>
                    <th>Id No.</th>
                    <th>Student Name</th>
                    <th>Exam</th>
                    <th>Class</th>
                    <th>Section</th>
                    <?php
                    if (isset($_POST['ok'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            echo "<script>document.getElementById('class').value='$class'</script>";
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
                                echo "<script>$('#section').val('$section')</script>";
                                if ($_POST['Exam']) {
                                    $exam = $_POST['Exam'];
                                    echo "<script>document.getElementById('exam').value='$exam';</script>";
                                    $_SESSION['exm_Class'] = $class;
                                    $_SESSION['exm_Section'] = $section;
                                    $_SESSION['exm_Exam'] = $exam;
                                    $result1 = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                                    if (mysqli_num_rows($result1) == 0) {
                                        echo "<script>alert('There are No subjects corresponding to this Class and Exam');</script>";
                                    } else {
                                        while ($row1 = mysqli_fetch_assoc($result1)) {
                                            echo '
                                        <th>' . $row1['Subjects'] . '</th>';
                                        }
                                        echo '<tbody id="tbody">';
                                        $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'";
                                        $result = mysqli_query($link, $sql);
                                        $k = 1;
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>
                                            <td>' . $k . '</td>
                                            <td>' . $row['Id_No'] . '</td>
                                            <td>' . $row['First_Name'] . '</td>
                                            <td>' . $exam . '</td>
                                            <td>' . $row['Stu_Class'] . '</td>
                                            <td>' . $row['Stu_Section'] . '</td>';
                                            $result1 = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
                                            $j = 0;
                                            $sub_count = 1;
                                            while ($row1 = mysqli_fetch_assoc($result1)) {
                                                $res = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '" . $row['Id_No'] . "' AND Exam = '" . $exam . "'");
                                                $row2 = mysqli_fetch_assoc($res);
                                                echo '
                                            <td><input type="text" class="form-control mark" value="' . $row2["sub" . $sub_count] . '" name="mark[]" onfocus="this.select();" id="markinp" style="width:50px;" ' . (!can('create', MENU_ID) ? 'disabled' : '') . '></td>';
                                                $sub_count++;
                                                $j++;
                                            }
                                            echo '</tr>';
                                            $k++;
                                        }
                                    }
                                } else {
                                    echo "<script>alert('Please Select Exam!');</script>";
                                }
                            } else {
                                echo "<script>alert('Please Select Section!');</script>";
                            }
                        } else {
                            echo "<script>alert('Please Select Class!');</script>";
                        }
                    }
                    ?>
                    </tr>
                    </tbody>
            </table>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="add" onclick="if(!confirm('Confirm to Upload Marks?'))return false; else return true;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Upload Marks</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php
    if (isset($_POST['add'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        //Varibles
        $cls = $_SESSION['exm_Class'];
        $sec = $_SESSION['exm_Section'];
        $exm = $_SESSION['exm_Exam'];
        $marks = $_POST['mark'];
        echo "<script>
        document.getElementById('class').value='$cls';
        document.getElementById('section').value='$sec';
        </script>";
        $s = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$cls'");
        echo "<script>document.getElementById('exam').innerHTML = '';</script>";
        if (mysqli_num_rows($s) > 0) {
            echo "<script>$('#exam').html('<option value=" . 'selectexam' . " disabled selected>--Select Exam--</option>');</script>";
            while ($r = mysqli_fetch_assoc($s)) {
                echo "<script>$('#exam').append('<option value=' + '" . $r['Exam'] . "' + '>" . $r['Exam'] . "</option>');</script>";
            }
        } else {
            echo "<script>$('#exam').html('<option selected disabled>No Exam Found</option>');</script>";
        }
        echo "<script>document.getElementById('exam').value='$exm';</script>";

        //Arrays
        $ids = array();
        $names = array();
        $subs = array();
        $max = array();
        $id_old_marks = array();
        $id_new_marks = array();

        //Queries
        $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '" . $cls . "' AND Stu_Section = '" . $sec . "'");
        $query2 = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '" . $cls . "' AND Exam = '" . $exm . "'");
        $query4 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Class = '" . $cls . "' AND Section = '" . $sec . "' AND Exam = '" . $exm . "'");


        if ($query1) {
            //Geting Id Nos and Subjects
            while ($row1 = mysqli_fetch_assoc($query1)) {
                array_push($ids, $row1['Id_No']);
                $names[$row1['Id_No']]  = $row1['First_Name'];
            }
            if ($query2) {
                $max_total = 0;
                //Fetching Max Marks of Each subject
                while ($row2 = mysqli_fetch_assoc($query2)) {
                    array_push($subs, $row2['Subjects']);
                    $max[$row2['Subjects']] = $row2['Max_Marks'];
                    $max_total += $row2['Max_Marks'];
                }
                $max['Total'] = $max_total;

                $new_marks = array_chunk($marks, count($subs), true);
                //Set Entered Marks Corresponding to Id_No
                $temp1 = array();
                for ($i = 0; $i < count($new_marks); $i++) {
                    foreach ($new_marks[$i] as $new_mark) {
                        array_push($temp1, $new_mark);
                    }
                    $id_new_marks[$ids[$i]] = $temp1;
                    $temp1 = array();
                }

                //Set Existing Marks Corresponding to Id_No
                $c_subs = count($subs);
                $temp = array();
                foreach ($ids as $id) {
                    $query3 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '" . $id . "' AND Exam = '" . $exm . "'");
                    while ($row3 = mysqli_fetch_assoc($query3)) {
                        for ($c = 1; $c <= $c_subs; $c++) {
                            array_push($temp, $row3['sub' . $c]);
                        }
                        $id_old_marks[$id] = $temp;
                        $temp = array();
                    }
                }

                $total = 0;
                $status = false;
                $i_sql = "INSERT INTO `stu_marks` ";
                $u_sql = "UPDATE `stu_marks` SET ";
                foreach ($ids as $id) {
                    //Adding All Marks to Total
                    foreach ($id_new_marks[$id] as $new_mark) {
                        if ($new_mark != 'A') {
                            $total += (int)$new_mark;
                        }
                    }

                    //Checking If there is data of that class,section and Exam
                    if (mysqli_num_rows($query4) != 0) {
                        $flag = false;
                        while ($row4 = mysqli_fetch_assoc($query4)) {
                            if ($id == $row4['Id_No']) {
                                $flag = true;
                                break;
                            } else {
                                $flag = false;
                            }
                        }
                        //If Id is not there in DB, Insert Student
                        if (!$flag) {

                            $i_sql .= "(Class,Section,Id_No,First_Name,Exam)VALUES('" . $cls . "','" . $sec . "','" . $id . "','" . $names[$id] . "','" . $exm . "');";
                            if (mysqli_query($link, $i_sql)) {
                                $status = true;
                            } else {
                                $status = false;
                                break;
                            }
                            $i_sql = "INSERT INTO `stu_marks` ";

                            //Checking Every Mark in local and DB and Update Data
                            for ($c = 0; $c < count($subs); $c++) {
                                if (($id_old_marks[$id][$c] != '') || ($id_new_marks[$id][$c] != '' && $id_old_marks[$id][$c] == '')) {
                                    $u_sql .= "sub" . ($c + 1) . " = '" . $id_new_marks[$id][$c] . "',Total = '" . $total . "' WHERE Id_No = '" . $id . "' AND Exam = '" . $exm . "';";
                                    if (mysqli_query($link, $u_sql)) {
                                        $status = true;
                                    } else {
                                        $status = false;
                                        break;
                                    }
                                    $u_sql = "UPDATE `stu_marks` SET ";
                                }
                            }
                            $total = 0;
                        }
                        //If Id is Present in DB
                        else {
                            //Checking every Mark in local and DB and Update data
                            for ($c = 0; $c < count($subs); $c++) {
                                if (($id_old_marks[$id][$c] != '') || ($id_new_marks[$id][$c] != '' && $id_old_marks[$id][$c] == '')) {
                                    $u_sql .= "sub" . ($c + 1) . " = '" . $id_new_marks[$id][$c] . "',Total = '" . $total . "' WHERE Id_No = '" . $id . "' AND Exam = '" . $exm . "';";
                                    if (mysqli_query($link, $u_sql)) {
                                        $status = true;
                                    } else {
                                        $status = false;
                                        break;
                                    }
                                    $u_sql = "UPDATE `stu_marks` SET ";
                                }
                            }
                            $total = 0;
                        }
                    }
                    //If there are no rows of that class,section and Exam
                    else {
                        //Insert the Student
                        $i_sql .= "(Class,Section,Id_No,First_Name,Exam)VALUES('" . $cls . "','" . $sec . "','" . $id . "','" . $names[$id] . "','" . $exm . "');";
                        if (mysqli_query($link, $i_sql)) {
                            $status = true;
                        } else {
                            $status = false;
                            break;
                        }
                        $i_sql = "INSERT INTO `stu_marks` ";

                        //Checking Every Mark in local and DB and Update Data
                        for ($c = 0; $c < count($subs); $c++) {
                            if (($id_old_marks[$id][$c] != '') || ($id_new_marks[$id][$c] != '' && $id_old_marks[$id][$c] == '')) {
                                $u_sql .= "sub" . ($c + 1) . " = '" . $id_new_marks[$id][$c] . "',Total = '" . $total . "' WHERE Id_No = '" . $id . "' AND Exam = '" . $exm . "';";
                                if (mysqli_query($link, $u_sql)) {
                                    $status = true;
                                } else {
                                    $status = false;
                                    break;
                                }
                                $u_sql = "UPDATE `stu_marks` SET ";
                            }
                        }
                        $total = 0;
                    }
                }
                if ($status) {
                    echo "<script>alert('Marks Succesfully Added');</script>";
                } else {
                    echo "<script>alert('Failed to Add Marks')</script>";
                }
            } else {
                echo "<script>alert('Something went wrong with Subjects SQL query');location.replace('class_marks.php')</script>";
            }
        } else {
            echo "<script>alert('Something went wrong with Stu Data SQL query');location.replace('class_marks.php')</script>";
        }
    }
    ?>


    <!-- Scripts -->

    <!-- Prevent Default Enter Key -->
    <script type="text/javascript">
        $(document).ready(function() {
            $(window).keydown(function(event) {
                if (event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
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

    <script>
        // JavaScript code to handle down arrow key navigation
        document.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                const focusedInput = document.activeElement;
                const allInputs = document.querySelectorAll("#marks-table tbody tr .form-control");
                const currentIndex = Array.from(allInputs).indexOf(focusedInput);
                const subjectsCount = parseInt('<?php echo isset($sub_count) ? $sub_count : 0; ?>');
                const nextIndex = (currentIndex + subjectsCount - 1);
                event.preventDefault();
                $(allInputs[nextIndex]).focus().select();
            }
        });
    </script>
</body>

</html>