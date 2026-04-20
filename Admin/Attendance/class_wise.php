<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 38);

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
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <form action="" method="post">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-1">
                    <label for=""><b>Month:</b></label>
                </div>
                <div class="col-lg-3">
                    <select class="form-select" name="Month" id="month">
                        <option value="selectmonth" selected disabled>-- Select Month --</option>
                        <?php
                        $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May'];
                        foreach ($months as $mon) {
                            echo "<option value='" . $mon . "'>$mon</option>";
                        }
                        ?>
                    </select>
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
            <div class="container">
                <div class="row justify-content-center mt-2">
                    <div class="col-lg-6">
                        <label for="" style="color: red;">NOTE: Please Enter Working Days before showing a month attendance</label>
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
    <form action="" method="POST">
        <div class="container table-container">
            <table class="table table-striped">
                <thead>
                    <th>S.No</th>
                    <th>Id No.</th>
                    <th>Name</th>
                    <th>No. of Working Days</th>
                    <th>No. of Present Days</th>
                    <th>No. of Absent Days</th>
                </thead>
                <tbody id="tbody">
                    <tr>
                        <?php
                        if (isset($_POST['show'])) {
                            if (!can('view', MENU_ID)) {
                                echo "<script>alert('You don\'t have permission to view this report');
                                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                                exit;
                            }
                            $month = $_POST['Month'];
                            $class = $_POST['Class'];
                            $section = $_POST['Section'];
                            echo "<script>
                                document.getElementById('month').value = '" . $month . "';
                                document.getElementById('class').value = '" . $class . "';
                                document.getElementById('sec').value = '" . $section . "';
                            </script>";

                            //Arrays
                            $ids = array();
                            $names = array();
                            $absent_days = array();
                            $months = array();

                            //Getting No. of Working Days of that Month
                            $working_query = mysqli_query($link, "SELECT Working_Days FROM `working_days` WHERE Month = '$month'");
                            if (mysqli_num_rows($working_query) == 0) {
                                $working_days = 0;
                            } else {
                                while ($working_row = mysqli_fetch_assoc($working_query)) {
                                    $working_days = $working_row['Working_Days'];
                                }
                            }

                            $month_number = date("n", strtotime($month));

                            //Queries
                            $query1 = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                            if ($query1) {
                                while ($row1 = mysqli_fetch_assoc($query1)) {
                                    array_push($ids, $row1['Id_No']);
                                    $names[$row1['Id_No']] = $row1['First_Name'];
                                }
                                foreach ($ids as $id) {
                                    $absent_days[$id] = 0;
                                }
                                foreach ($ids as $id) {
                                    if ($month_number < 10) {
                                        $query2 = mysqli_query($link, "SELECT COUNT(AM) AS AM FROM `attendance_daily` WHERE Id_No = '$id' AND AM = 'A' AND Date LIKE '%-0" . $month_number . "-%'");
                                    } else {
                                        $query2 = mysqli_query($link, "SELECT COUNT(AM) AS AM FROM `attendance_daily` WHERE Id_No = '$id' AND AM = 'A' AND Date LIKE '%-" . $month_number . "-%'");
                                    }

                                    if (mysqli_num_rows($query2) == 0) {
                                        $absent_days[$id] += 0;
                                    } else {
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $absent_days[$id] += $row2['AM'];
                                        }
                                    }

                                    if ($month_number < 10) {
                                        $query3 = mysqli_query($link, "SELECT COUNT(PM) AS PM FROM `attendance_daily` WHERE Id_No = '$id' AND PM = 'A' AND Date LIKE '%-0" . $month_number . "-%'");
                                    } else {
                                        $query3 = mysqli_query($link, "SELECT COUNT(PM) AS PM FROM `attendance_daily` WHERE Id_No = '$id' AND PM = 'A' AND Date LIKE '%-" . $month_number . "-%'");
                                    }
                                    if (mysqli_num_rows($query3) == 0) {
                                        $absent_days[$id] += 0;
                                    } else {
                                        while ($row3 = mysqli_fetch_assoc($query3)) {
                                            $absent_days[$id] += $row3['PM'];
                                        }
                                    }
                                }
                            }
                            if ($query1) {
                                $status = true;
                                foreach ($ids as $id) {
                                    $present_days = ((int)$working_days - ceil((int)$absent_days[$id] / 2));
                                    if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `stu_att_master` WHERE Id_No = '$id'")) == 0) {
                                        $query4 = mysqli_query($link, "INSERT INTO `stu_att_master`(Id_No,$month) VALUES('$id','$present_days')");
                                        if ($query4) {
                                            $status = true;
                                        } else {
                                            $status = false;
                                            break;
                                        }
                                    } else {
                                        $query4 = mysqli_query($link, "UPDATE `stu_att_master` SET $month = '$present_days' WHERE Id_No = '$id'");
                                        if ($query4) {
                                            $status = true;
                                        } else {
                                            $status = false;
                                            break;
                                        }
                                    }
                                }
                                if ($status) {
                                    echo "<script>alert('Attendance Updated Successfully!!')</script>";
                                } else {
                                    echo "<script>alert('Attendance Updation Failed!!')</script>";
                                }
                            }
                            if ($query1) {
                                $i = 1;
                                foreach ($ids as $id) {
                                    $query5 = mysqli_query($link, "SELECT $month AS Present FROM `stu_att_master` WHERE Id_No = '$id'");
                                    if ($query5) {
                                        if (mysqli_num_rows($query5) == 0) {
                                            echo "<script>alert('No Student Found with Id: " . $id . " in Attendance Master')</script>";
                                        } else {
                                            while ($row5 = mysqli_fetch_assoc($query5)) {
                                                $present = $row5['Present'];
                                            }
                                            echo '
                                            <td>' . $i . '</td>
                                            <td>' . $id . '</td>
                                            <td>' . $names[$id] . '</td>
                                            <td>' . $working_days . '</td>
                                            <td>' . $present . '</td>
                                            <td>' . (int)$working_days - (int)$present . '</td>
                                            ';
                                            echo '</tr>';
                                        }
                                    }
                                    $i++;
                                }
                            }
                        }

                        ?>
                </tbody>
            </table>
        </div>
    </form>
</body>

</html>