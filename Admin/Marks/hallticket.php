<?php
/*
Hallticket
width:23.00cm
height:10.20cm
left:1.70cm
right:1.20cm
top:0.70cm
bottom:0.40cm
*/
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 23);

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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST" autocomplete="off">
        <div class="container">
            <div class="row justify-content-center mt-5">
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
            <div class="row justify-content-center mt-3" id="class_row">
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam(this.value)" aria-label="Default select example">
                        <option selected disabled>-- Select Class --</option>
                        <option value="PreKG">PreKG</option>
                        <option value="LKG">LKG</option>
                        <option value="UKG">UKG</option>
                        <?php
                        for ($i = 1; $i <= 10; $i++) {
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
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="Ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
                    </div>
                    <button class="btn btn-warning" type="reset"" onclick=" hideTable()">Clear</button>
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
        <div class="row justify-content-center mt-3">
            <div class="col-lg-5" style="color: red;">
                NOTE: 1. Please Give Margin: Minimum in Page Setup <br>
                2. Place the ribbon at " I " letter in "HIGH" word in hall ticket
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Hall Ticket</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <?php

        if (isset($_POST['Ok'])) {
            if (!can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
            }
            $stu_type = $_POST['stu_type'];
            if ($stu_type == "Single") {
                echo "<script>
                document.getElementById('single').checked = true;
                document.getElementById('id_row').hidden = '';
                document.getElementById('class_row').hidden = 'hidden';
                </script>";

                //Arrays
                $details = array();
                if ($_POST['Id_No']) {
                    $id = $_POST['Id_No'];
                    echo "<script>document.getElementById('id_no').value = '" . $id . "'</script>";
                    if ($_POST['Exam']) {
                        $exam = $_POST['Exam'];
                        $query1 = mysqli_query($link, "SELECT Adm_No,First_Name,Father_Name,Stu_Class,Stu_Section,DOB FROM `student_master_data` WHERE Id_No = '$id'");
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            $details[$id] = array($row1['Adm_No'], $row1['First_Name'], substr($row1['Father_Name'], 0, 20), $row1['DOB']);
                            $class = $row1['Stu_Class'];
                            $section = $row1['Stu_Section'];
                        }

                        //Table Creation
                        echo '
                            <div class="full-container" style="padding-top:1cm;">
                                <table style="margin-left: 5cm;">
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][0] . '</td>
                                        <td style="width: 120px;"></td>
                                        <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][1] . '</td>
                                        <td style="width: 175px;"></td>
                                        <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $id . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][2] . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][3] . '</td>
                                    </tr>
                                </table>
                            </div>
                            ';

                        /*
                        //Table Creation
                        echo ' <table style="margin-top:1cm;margin-left: 4.2cm;">
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][0] . '</td>
                            <td style="width: 120px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][1] . '</td>
                            <td style="width: 175px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $id . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][2] . '</td>
                        </tr>
                    </table>';
                    */
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
                    if ($_POST['Section']) {
                        $section = $_POST['Section'];
                        echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                        if ($_POST['Exam']) {
                            $exam = $_POST['Exam'];

                            //Arrays
                            $ids = array();
                            $details = array();

                            //Queries
                            $query1 = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");

                            while ($row1 = mysqli_fetch_assoc($query1)) {
                                array_push($ids, $row1['Id_No']);
                            }
                            foreach ($ids as $id) {
                                $query2 = mysqli_query($link, "SELECT Adm_No,First_Name,Father_Name,DOB FROM `student_master_data` WHERE Id_No = '$id'");
                                while ($row2 = mysqli_fetch_assoc($query2)) {
                                    $details[$id] = array($row2['Adm_No'], $row2['First_Name'], substr($row2['Father_Name'], 0, 20), $row2['DOB']);
                                }
                            }

                            //Table Creation
                            echo '
                            <div class="full-container" style="padding-top:1cm;">
                                <table style="margin-left: 5cm;">
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][0] . '</td>
                                        <td style="width: 120px;"></td>
                                        <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][1] . '</td>
                                        <td style="width: 175px;"></td>
                                        <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $ids[0] . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][2] . '</td>
                                    </tr>
                                    <tr style="line-height: 30px;">
                                        <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][3] . '</td>
                                    </tr>
                                </table>
                            </div>
                            ';
                            /*
                            echo ' <table style="margin-top:0.5cm;margin-left: 4.2cm;">
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][0] . '</td>
                            <td style="width: 120px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][1] . '</td>
                            <td style="width: 175px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $ids[0] . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$ids[0]][2] . '</td>
                        </tr>
                    </table>';
                    */
                            foreach ($ids as $id) {
                                echo '
                                <div class="full-container" style="padding-top:2cm;">
                                    <table style="margin-left: 5cm;">
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][0] . '</td>
                                            <td style="width: 120px;"></td>
                                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                                        </tr>
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                                        </tr>
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][1] . '</td>
                                            <td style="width: 175px;"></td>
                                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                                        </tr>
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $id . '</td>
                                        </tr>
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][2] . '</td>
                                        </tr>
                                        <tr style="line-height: 30px;">
                                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][3] . '</td>
                                        </tr>
                                    </table>
                                </div>
                                ';
                                /*
                                echo ' <table style="padding-top:85px;margin-top:6cm;margin-left: 4.1cm;">
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][0] . '</td>
                            <td style="width: 120px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . date('d-m-Y') . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $exam . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][1] . '</td>
                            <td style="width: 170px;"></td>
                            <td style="font-size: 15px;font-family:' . 'Arial' . '">' . $class . ' ' . $section . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $id . '</td>
                        </tr>
                        <tr style="line-height: 30px;">
                            <td style="font-size: 18px;font-family:' . 'Arial' . '">' . $details[$id][2] . '</td>
                        </tr>
                    </table>';
                    */
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