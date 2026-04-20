<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 114);

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
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
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
        max-width: 1350px;
        max-height: 500px;
        margin-left: 8%;
        overflow-x: scroll;
    }

    @media print {
        * {
            display: none;
        }

        #table-container {
            display: block;
        }
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
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
            <div class="row justify-content-center mt-5">
                <div class="col-lg-6">
                    <h2>Address By</h2>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="add_by" id="class_wise" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="add_by" id="area_wise" value="Area_Wise">
                        <label class="form-check-label" for="area_wise">Area Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="add_by" id="route_wise" value="Route_Wise">
                        <label class="form-check-label" for="route_wise">Route Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="add_by" id="id_wise" value="Id_Wise">
                        <label class="form-check-label" for="id_wise">Id Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="add_by" id="droppers" value="Droppers">
                        <label class="form-check-label" for="droppers">Droppers</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4" id="class_row">
                <div class="p-2 text-light col-lg-4 rounded">
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
                <div class="p-2 text-light col-lg-4 rounded">
                    <select class="form-select" name="Section" id="section" aria-label="Default select example">
                        <option selected disabled>-- Select Section --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <label for="add_by" class="col-sm-2 col-form-label" id="add_label" style="font-weight:bold;"></label>
                <div class="col-sm-4" id="inp_row" hidden>
                    <input type="text" class="form-control" name="txtinp" id="txtinp">
                </div>
                <div class="col-sm-4" id="route_row" hidden>
                    <select class="form-select" name="Route" id="route" aria-label="Default select example">
                        <option selected disabled>-- Select Route --</option>
                        <?php
                        $query = mysqli_query($link, "SELECT Van_Route FROM `van_route` ORDER BY Van_Route");
                        while ($r = mysqli_fetch_assoc($query)) {
                            echo "<option value='" . $r['Van_Route'] . "'>" . $r['Van_Route'] . "</option>";
                        }
                        echo "<option value='All_Routes'>All Routes</option>";
                        ?>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-4" id="id_row" hidden>
                <div class="col-sm-1">
                    <label for="">FROM:</label>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="From_Id" id="from_id" oninput="this.value = this.value.toUpperCase();">
                </div>
                <div class="col-sm-1">
                    <label for="">TO:</label>
                </div>
                <div class="col-sm-2">
                    <input type="text" class="form-control" name="To_Id" id="to_id" oninput="this.value = this.value.toUpperCase();">
                </div>
            </div>
            <div class="container">
                <div class="row justify-content-center mt-3">
                    <div class="col-lg-3">
                        <input type="checkbox" id="select_all" name="select_all" id="select_all" onclick="toggle(this)"><label for="select_all"><b>Select All</b></label><br>
                        <input type="checkbox" class="column" value="Id_No" id="Id_No" name="columns[]"><label for="Id_No">Id No</label><br>
                        <input type="checkbox" class="column" value="Adm_No" id="Adm_No" name="columns[]"><label for="Adm_No">Admission No</label><br>
                        <input type="checkbox" class="column" value="First_Name" id="First_Name" name="columns[]"><label for="First_Name">First Name</label><br>
                        <input type="checkbox" class="column" value="Sur_Name" id="Sur_Name" name="columns[]"><label for="Sur_Name">Sur Name</label><br>
                        <input type="checkbox" class="column" value="Father_Name" id="Father_Name" name="columns[]"><label for="Father_Name">Father Name</label><br>
                        <input type="checkbox" class="column" value="Mother_Name" id="Mother_Name" name="columns[]"><label for="Mother_Name">Mother Name</label><br>
                        <input type="checkbox" class="column" value="DOB" id="DOB" name="columns[]"><label for="DOB">DOB</label><br>
                        <input type="checkbox" class="column" value="Gender" id="Gender" name="columns[]"><label for="Gender">Gender</label><br>
                        <input type="checkbox" class="column" value="Mobile" id="Mobile" name="columns[]"><label for="Mobile">All Mobile Nos</label><br>
                        <input type="checkbox" class="column" value="S_Mobile" id="S_Mobile" name="columns[]"><label for="S_Mobile">Single Mobile No</label><br>
                        <input type="checkbox" class="column" value="Aadhar" id="Aadhar" name="columns[]"><label for="Aadhar">Aadhar No</label><br>
                        <input type="checkbox" class="column" value="Mother_Aadhar" id="Mother_Aadhar" name="columns[]"><label for="Mother_Aadhar">Mother Aadhar No</label><br>
                        <input type="checkbox" class="column" value="Father_Aadhar" id="Father_Aadhar" name="columns[]"><label for="Father_Aadhar">Father Aadhar No</label><br>
                    </div>
                    <div class="col-lg-3">
                        <input type="checkbox" class="column" value="Stu_Class" id="Stu_Class" name="columns[]"><label for="Stu_Class">Class</label><br>
                        <input type="checkbox" class="column" value="Stu_Section" id="Stu_Section" name="columns[]"><label for="Stu_Section">Section</label><br>
                        <input type="checkbox" class="column" value="Class_Section" id="Class_Section" name="columns[]"><label for="Class_Section">Class & Section</label><br>
                        <input type="checkbox" class="column" value="Religion" id="Religion" name="columns[]"><label for="Religion">Religion</label><br>
                        <input type="checkbox" class="column" value="Caste" id="Caste" name="columns[]"><label for="Caste">Caste</label><br>
                        <input type="checkbox" class="column" value="Category" id="Category" name="columns[]"><label for="Category">Category</label><br>
                        <input type="checkbox" class="column" value="House_No" id="House_No" name="columns[]"><label for="House_No">House No</label><br>
                        <input type="checkbox" class="column" value="Area" id="Area" name="columns[]"><label for="Area">Area</label><br>
                        <input type="checkbox" class="column" value="Village" id="Village" name="columns[]"><label for="Village">Village</label><br>
                        <input type="checkbox" class="column" value="DOJ" id="DOJ" name="columns[]"><label for="DOJ">DOJ</label><br>
                        <input type="checkbox" class="column" value="Previous_School" id="Previous_School" name="columns[]"><label for="Previous_School">Previous School</label><br>
                        <input type="checkbox" class="column" value="Van_Route" id="Van_Route" name="columns[]"><label for="Van_Route">Van Route</label><br>
                        <input type="checkbox" class="column" value="Referred_By" id="Referred_By" name="columns[]"><label for="Referred_By">Referred By</label><br>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Photo" id="wo_photo" checked value="Without_Photo">
                        <label class="form-check-label" for="wo_photo">Without Photo</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Photo" id="w_photo" value="With_Photo">
                        <label class="form-check-label" for="w_photo">With Photo</label>
                    </div>
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
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Address Details Report</b></h3>
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
                <td style="font-size:20px;color:red" id="label"></td>
                <td id="txt_label" style="font-size:20px;"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr style="padding: 5px;" class="table-head">
                    <th>S.No</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $search = $_POST['add_by'];
                        $photo = $_POST['Photo'];
                        if ($photo == 'With_Photo') {
                            echo "<script>
                            document.getElementById('w_photo').checked = true;
                            </script>";
                        } else {
                            echo "<script>
                            document.getElementById('wo_photo').checked = true;
                            </script>";
                        }
                        $flag = false;
                        if ($search == 'Class_Wise') {
                            echo "<script>document.getElementById('class_wise').checked = true;</script>";
                            if ($_POST['Class']) {
                                $class = $_POST['Class'];
                                echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                                if ($_POST['Section']) {
                                    $section = $_POST['Section'];
                                    echo "<script>document.getElementById('section').value = '" . $section . "'</script>";
                                    echo "<script>document.getElementById('label').innerHTML = 'Class:';
                                document.getElementById('txt_label').innerHTML = '" . $class . $section . "';</script>";
                                    $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'";
                                    $flag = true;
                                } else {
                                    $flag = false;
                                    echo "<script>alert('Please Select Section!')</script>";
                                }
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Select Class!')</script>";
                            }
                        } else if ($search == 'Area_Wise') {
                            echo "<script>document.getElementById('area_wise').checked = true;</script>";
                            echo "<script>
                            if(document.getElementById('area_wise').checked){
                                document.getElementById('class_row').hidden = 'hidden';
                                document.getElementById('inp_row').hidden = '';
                                document.getElementById('add_label').innerHTML = 'Area:';
                            }
                            </script>";
                            if ($_POST['txtinp']) {
                                $txtinp = $_POST['txtinp'];
                                echo "<script>document.getElementById('label').innerHTML = 'Area: ';
                                document.getElementById('txt_label').innerHTML = '" . $txtinp . "';</script>";
                                echo "<script>document.getElementById('txtinp').value = '" . $txtinp . "'</script>";
                                $sql = "SELECT * FROM `student_master_data` WHERE Area LIKE '%$txtinp%' AND (Stu_Class LIKE '%CLASS%' OR Stu_Class ='PreKG' OR Stu_Class ='LKG' OR Stu_Class ='UKG')";
                                $flag = true;
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Enter Area!')</script>";
                            }
                        } else if ($search == 'Route_Wise') {
                            echo "<script>document.getElementById('route_wise').checked = true;</script>";
                            echo "<script>
                            if(document.getElementById('route_wise').checked){
                                document.getElementById('class_row').hidden = 'hidden';
                                document.getElementById('route_row').hidden = '';
                                document.getElementById('add_label').innerHTML = 'Route: ';
                            }
                            </script>";
                            if ($_POST['Route']) {
                                $route = $_POST['Route'];
                                echo "<script>document.getElementById('route').value = '" . $route . "'</script>";
                                if ($route == "All_Routes") {
                                    $sql = "All_Routes";
                                } else {
                                    echo "<script>document.getElementById('label').innerHTML = 'Route:';
                                    document.getElementById('txt_label').innerHTML = '" . $route . "';</script>";
                                    //$sql = "SELECT * FROM `student_master_data` WHERE Van_Route LIKE '%$route%' AND (Stu_Class LIKE '%CLASS%' OR Stu_Class ='PreKG' OR Stu_Class ='LKG' OR Stu_Class ='UKG')";
                                    $sql = "SELECT * FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '%CLASS%' OR Stu_Class ='PreKG' OR Stu_Class ='LKG' OR Stu_Class ='UKG')";
                                }
                                $flag = true;
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Select Route!')</script>";
                            }
                        } else if ($search == "Id_Wise") {
                            echo "<script>document.getElementById('id_wise').checked = true;</script>";
                            echo "<script>
                            if(document.getElementById('id_wise').checked){
                                document.getElementById('class_row').hidden = 'hidden';
                                document.getElementById('route_row').hidden = 'hidden';
                                document.getElementById('id_row').hidden = '';
                            }
                            </script>";
                            if ($_POST['From_Id']) {
                                $from_id = $_POST['From_Id'];
                                echo "<script>document.getElementById('from_id').value = '" . $from_id . "';</script>";
                                if ($_POST['To_Id']) {
                                    $to_id = $_POST['To_Id'];
                                    echo "<script>document.getElementById('to_id').value = '" . $to_id . "';</script>";
                                } else {
                                    $to_query = mysqli_query($link, "SELECT Id_No FROM `student_master_data` ORDER BY S_No DESC LIMIT 1");
                                    while ($to_row = mysqli_fetch_assoc($to_query)) {
                                        $to_id = $to_row['Id_No'];      //Get Last Id No from student master data
                                    }
                                }
                                $sql = "SELECT * FROM `student_master_data` WHERE Id_No BETWEEN '" . $from_id . "' AND '" . $to_id . "' ORDER BY Id_No";
                                $flag = true;
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Enter From Id No!')</script>";
                            }
                        } else if ($search == "Droppers") {
                            echo "<script>document.getElementById('droppers').checked = true;</script>";
                            echo "<script>
                            if(document.getElementById('droppers').checked){
                                document.getElementById('class_row').hidden = 'hidden';
                                document.getElementById('inp_row').hidden = '';
                                document.getElementById('add_label').innerHTML = 'Drop Year:';
                                txtinp.placeholder = 'Ex:(24/25)';
                            }
                            </script>";
                            if ($_POST['txtinp']) {
                                $year = $_POST['txtinp'];
                                echo "<script>document.getElementById('txtinp').value = '" . $year . "';</script>";
                                $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class LIKE 'DROP%' AND Stu_Class LIKE '%$year'";
                                $flag = true;
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Enter Drop Year!')</script>";
                            }
                        }
                        if ($flag) {
                            $cols = array();
                            if (isset($_POST['columns'])) {
                                if ($_POST['select_all']) {
                                    echo "<script>document.getElementById('select_all').checked = true;</script>";
                                }
                                foreach ($_POST["columns"] as $col) {
                                    echo "<script>document.getElementById('" . $col . "').checked = true;</script>";
                                    array_push($cols, $col);
                                    echo "<script>
                                    $('.table-head').append('<th>" . $col . "</th>')
                                    </script>";
                                }
                                if ($photo == "With_Photo") {
                                    echo "<script>
                                    $('.table-head').append('<th>Stu Image</th>')
                                    </script>";
                                }
                                if ($sql != "All_Routes") {
                                    $result = mysqli_query($link, $sql);
                                    $i = 1;
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr style="padding: 5px;">
                                        <td>' . $i . '</td>';
                                            foreach ($cols as $col) {
                                                if ($col == "Class_Section") {
                                                    echo '<td>' . $row['Stu_Class'] . ' ' . $row['Stu_Section'] . '</td>';
                                                } else if ($col == "S_Mobile") {
                                                    if (str_contains($row['Mobile'], ',')) {
                                                        echo '<td>' . explode(',', $row['Mobile'], 2)[0] . '</td>';
                                                    } else if (str_contains($row['Mobile'], ' ')) {
                                                        echo '<td>' . explode(' ', $row['Mobile'], 2)[0] . '</td>';
                                                    } else {
                                                        echo '<td>' . $row['Mobile'] . '</td>';
                                                    }
                                                } else {
                                                    echo '<td>' . $row[$col] . '</td>';
                                                }
                                            }
                                            if ($photo == "With_Photo") {
                                                if (file_exists("../../Images/stu_img/" . $row['Id_No'] . ".jpg")) {
                                                    echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/' . $row['Id_No'] . '.jpg" class="rounded" width="100px" height="100px"';
                                                } else {
                                                    echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/not_photo.jpg" class="rounded" width="100px" height="100px"';
                                                }
                                            }
                                            echo '</tr>';
                                            $i++;
                                        }
                                    }
                                } else {
                                    $routes = array();
                                    $v_routes = mysqli_query($link, "SELECT Van_Route FROM `van_route`");
                                    while ($v_row = mysqli_fetch_assoc($v_routes)) {
                                        array_push($routes, $v_row['Van_Route']);
                                    }
                                    foreach ($routes as $r) {
                                        $sql = "SELECT * FROM `student_master_data` WHERE Van_Route LIKE '%$r%' AND (Stu_Class LIKE '%CLASS%' OR Stu_Class ='PreKG' OR Stu_Class ='LKG' OR Stu_Class ='UKG')";
                                        $result = mysqli_query($link, $sql);
                                        echo '<tr>
                                                    <td style="font-size:20px;color:red" id="label">Route: </td>
                                                    <td id="txt_label" style="font-size:20px;">' . $r . '</td>
                                        </tr>';
                                        echo '<script>document.getElementById("table-head").hidden = "hidden";</script>';
                                        echo '
                                            <tr style="padding: 5px;" class="table-head">
                                            <th>S.No</th>';
                                        foreach ($cols as $col) {
                                            echo '<th>' . $col . '</th>';
                                        }
                                        echo '</tr>
                                        ';
                                        $i = 1;
                                        if (mysqli_num_rows($result) > 0) {
                                            while ($row = mysqli_fetch_assoc($result)) {

                                                echo '<tr style="padding: 5px;">
                                                <td>' . $i . '</td>';
                                                foreach ($cols as $col) {
                                                    if ($col == "Class_Section") {
                                                        echo '<td>' . $row['Stu_Class'] . ' ' . $row['Stu_Section'] . '</td>';
                                                    } else if ($col == "S_Mobile") {
                                                        if (str_contains($row['Mobile'], ',')) {
                                                            echo '<td>' . explode(',', $row['Mobile'], 2)[0] . '</td>';
                                                        } else if (str_contains($row['Mobile'], ' ')) {
                                                            echo '<td>' . explode(' ', $row['Mobile'], 2)[0] . '</td>';
                                                        } else {
                                                            echo '<td>' . $row['Mobile'] . '</td>';
                                                        }
                                                    } else {
                                                        echo '<td>' . $row[$col] . '</td>';
                                                    }
                                                }
                                                if ($photo == "With_Photo") {
                                                    if (file_exists("../../Images/stu_img/" . $row['Id_No'] . ".jpg")) {
                                                        echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/' . $row['Id_No'] . '.jpg" class="rounded" width="100px" height="100px"';
                                                    } else {
                                                        echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/not_photo.jpg" class="rounded" width="100px" height="100px"';
                                                    }
                                                }
                                                echo '</tr>';
                                                $i++;
                                            }
                                        }
                                    }
                                }
                            } else {
                                echo "<script>alert('No Column Selected!')</script>";
                            }
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Checkbox Select All -->
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByClassName('column');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        $('.column').on('click', function() {
            if ($('.column').not(':checked').length == 0) {
                document.getElementById('select_all').checked = true;
            } else {
                document.getElementById('select_all').checked = false;
            }
        });
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<div class = 'container'><table style='margin-bottom:20px;'><tr><td style='text-align:center;font-size:35px;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td></tr></table></div>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table style='margin-bottom:8px;'><tr><td style='text-align:center;font-size:28px;'>Address Details</td></tr></table></div>";
            window.frames["print_frame"].document.body.innerHTML += "<div class = 'container'><table style='margin-bottom:10px;'><tr><td style='font-size:15px;'><b><?php if ($search == 'Class_Wise') {
                                                                                                                                                                        echo 'Class:';
                                                                                                                                                                    } else if ($search == 'Area_Wise') {
                                                                                                                                                                        echo 'Area:';
                                                                                                                                                                    } else if ($search == 'Route_Wise') {
                                                                                                                                                                        echo 'Route:';
                                                                                                                                                                    } ?></b></td><td style='font-size:15px;'><?php if ($search == 'Class_Wise') {
                                                                                                                                                                                                                    echo $class . ' ' . $section;
                                                                                                                                                                                                                } else if ($search == 'Area_Wise') {
                                                                                                                                                                                                                    echo $txtinp;
                                                                                                                                                                                                                } else if ($search == 'Route_Wise') {
                                                                                                                                                                                                                    echo $route;
                                                                                                                                                                                                                } ?></td></tr><br></table></div>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            type = '<?php echo $search; ?>';
            if (type == "Class_Wise") {
                stuclass = '<?php echo $class; ?>';
                stusection = '<?php echo $section; ?>';
                filename = stuclass + stusection + '_Address';
            } else if (type == "Area_Wise") {
                area = '<?php echo $txtinp; ?>';
                filename = area + '_Address';
            } else if (type == "Route_Wise") {
                route = '<?php echo $route; ?>';
                filename = route + '_Address';
            } else if (type == "Id_Wise") {
                filename = '<?php echo $from_id . " To " . $to_id; ?>';
            } else if (type == "Droppers") {
                filename = "Droppers";
            }
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
    <!-- Change labels -->
    <script type="text/javascript">
        let result = document.getElementById('add_label');
        let inp_row = document.getElementById('inp_row');
        let route_row = document.getElementById('route_row');
        let cls_row = document.getElementById('class_row');
        let id_row = document.getElementById('id_row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            let message;
            switch (target.id) {
                case 'class_wise':
                    message = '';
                    if (cls_row.hidden) {
                        cls_row.hidden = '';
                    }
                    if (!inp_row.hidden) {
                        inp_row.hidden = 'hidden';
                    }
                    if (!route_row.hidden) {
                        route_row.hidden = 'hidden';
                    }
                    if (!id_row.hidden) {
                        id_row.hidden = 'hidden';
                    }
                    if (!result.hidden) {
                        result.hidden = 'hidden';
                    }
                    break;
                case 'area_wise':
                    message = "Area: ";
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'hidden';
                    }
                    if (inp_row.hidden) {
                        inp_row.hidden = '';
                    }
                    if (!route_row.hidden) {
                        route_row.hidden = 'hidden';
                    }
                    if (!id_row.hidden) {
                        id_row.hidden = 'hidden';
                    }
                    if (result.hidden) {
                        result.hidden = '';
                    } else if (!result.hidden) {
                        result.hidden = '';
                    }
                    txtinp.placeholder = ""
                    break;
                case 'route_wise':
                    message = "Route: ";
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'hidden';
                    }
                    if (!inp_row.hidden) {
                        inp_row.hidden = 'hidden';
                    }
                    if (route_row.hidden) {
                        route_row.hidden = '';
                    }
                    if (!id_row.hidden) {
                        id_row.hidden = 'hidden';
                    }
                    if (result.hidden) {
                        result.hidden = '';
                    } else if (!result.hidden) {
                        result.hidden = '';
                    }
                    txtinp.placeholder = ""
                    break;
                case 'id_wise':
                    message = "";
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'hidden';
                    }
                    if (!inp_row.hidden) {
                        inp_row.hidden = 'hidden';
                    }
                    if (!route_row.hidden) {
                        route_row.hidden = 'hidden';
                    }
                    if (id_row.hidden) {
                        id_row.hidden = '';
                    }
                    if (!result.hidden) {
                        result.hidden = 'hidden';
                    }
                    txtinp.placeholder = ""
                    break;
                case 'droppers':
                    message = "Drop Year: ";
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'hidden';
                    }
                    if (inp_row.hidden) {
                        inp_row.hidden = '';
                    }
                    if (!route_row.hidden) {
                        route_row.hidden = 'hidden';
                    }
                    if (!id_row.hidden) {
                        id_row.hidden = 'hidden';
                    }
                    if (result.hidden) {
                        result.hidden = '';
                    }
                    txtinp.placeholder = "Ex:(24/25)"
                    break;
                default:
                    if (result.innerHTML == "Area: ") {
                        message = "Area: ";
                    } else if (result.innerHTML == "Route: ") {
                        message = "Route: ";
                    } else if (result.innerHTML == "Drop Year: ") {
                        message = "Drop Year: ";
                    } else {
                        message = "";
                    }
            }
            result.innerHTML = message;
        });
    </script>
</body>

</html>