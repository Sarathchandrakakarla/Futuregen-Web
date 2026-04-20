<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 65);

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
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fee_by" id="class_wise" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fee_by" id="route_wise" value="Route_Wise">
                        <label class="form-check-label" for="route_wise">Route Wise</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <label for="add_by" class="col-sm-2 col-form-label">Type of Fee:</label>
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Type" id="type" aria-label="Default select example">
                        <option value="selectfeetype" selected disabled>-- Select Fee Type --</option>
                        <option value="School Fee">School Fee</option>
                        <option value="Admission Fee">Admission Fee</option>
                        <option value="Computer Fee">Computer Fee</option>
                        <option value="Vehicle Fee">Vehicle Fee</option>
                        <option value="Examination Fee">Examination Fee</option>
                        <option value="Book Fee">Book Fee</option>
                        <?php
                        if ($_SESSION['school_db']['school_code'] == "FGS") {
                        ?>
                            <option value="Hostel Fee">Hostel Fee</option>
                        <?php
                        }
                        ?>
                    </select>
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
            <div class="row justify-content-center mt-4" id="route_row" hidden>
                <label for="add_by" class="col-sm-2 col-form-label" id="add_label">Route:</label>
                <div class="col-sm-4" id="route_row">
                    <select class="form-select" name="Route" id="route" aria-label="Default select example">
                        <option selected disabled>-- Select Route --</option>
                        <?php
                        $query = mysqli_query($link, "SELECT Van_Route FROM `van_route`");
                        while ($r = mysqli_fetch_assoc($query)) {
                            echo "<option value='" . $r['Van_Route'] . "'>" . $r['Van_Route'] . "</option>";
                        }
                        ?>
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
            <div class="col-lg-5">
                <h3><b>Class Wise Fee Balances Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding: 10px;text-align:center;">Actual Fee</th>
                    <th style="padding: 10px;text-align:center;">Last Year Balances</th>
                    <th style="padding: 10px;text-align:center;">Committed Fee</th>
                    <th style="padding: 10px;text-align:center;">Total Fee</th>
                    <th style="padding: 10px;text-align:center;">Collected Fee</th>
                    <th style="padding: 10px;text-align:center;">Consession Fee</th>
                    <th style="padding: 10px;text-align:center;">Balance Fee</th>
                </tr>
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
                        $fee_by = $_POST['fee_by'];
                        if ($fee_by == "Class_Wise") {
                            echo "<script>document.getElementById('type').value = 'selectfeetype';
                            document.getElementById('type').disabled = '';
                            document.getElementById('class_wise').checked = true;
                            document.getElementById('route_row').hidden = 'hidden';
                            document.getElementById('class_row').hidden = '';</script>";
                            if ($_POST['Class']) {
                                $class = $_POST['Class'];
                                echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                                if ($_POST['Section']) {
                                    $section = $_POST['Section'];
                                    echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                                    if ($_POST['Type']) {
                                        $type = $_POST['Type'];
                                        echo "<script>document.getElementById('type').value = '" . $type . "'</script>";

                                        //Arrays
                                        $ids = array();
                                        $fees = array();

                                        //Queries

                                        //Checking If Class and Section are Valid
                                        $id_sql = mysqli_query($link, "SELECT Id_No FROM `student_master_data` 
                                        WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                                        if (mysqli_num_rows($id_sql) == 0) {
                                            echo "<script>alert('Section Not Available!!')</script>";
                                        } else {
                                            while ($id_row = mysqli_fetch_assoc($id_sql)) {
                                                array_push($ids, $id_row['Id_No']);
                                            }

                                            //Getting Actual Fee for that Class
                                            $query1 = mysqli_query($link, "SELECT Fee FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
                                            while ($row1 = mysqli_fetch_assoc($query1)) {
                                                $fees['Actual_Fee'] = (int)$row1['Fee'];
                                            }

                                            //Initialzing All Types of Fees
                                            $fees['Committed_Fee'] = 0;
                                            $fees['Collected_Fee'] = 0;
                                            foreach ($ids as $id) {
                                                //Getting Sum of Committed Fee Each Stduent of that Class
                                                $query2 = mysqli_query($link, "SELECT Current_Balance AS Committed_Fee, Last_Balance,Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                                while ($row2 = mysqli_fetch_assoc($query2)) {
                                                    $fees['Last_Balance'] += (int)$row2['Last_Balance'];
                                                    $fees['Committed_Fee'] += (int)$row2['Committed_Fee'];
                                                    $fees['Total'] = (int)$fees['Committed_Fee'] + (int)$fees['Last_Balance'];
                                                }
                                                //Getting Collected Fee
                                                $query3 = mysqli_query($link, "SELECT Fee AS Collected_Fee FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type'");

                                                while ($row3 = mysqli_fetch_assoc($query3)) {
                                                    $fees['Collected_Fee'] += (int)$row3['Collected_Fee'];
                                                }
                                            }

                                            //Calculating Consession Fee and Balance Fee
                                            $fees['Consession_Fee'] = $fees['Actual_Fee'] * count($ids) - $fees['Committed_Fee'];
                                            $fees['Balance_Fee'] = $fees['Total'] - $fees['Collected_Fee'];

                                            //Printing All Fees
                                            echo '
                                            <td style="padding: 10px;text-align:center;">' . ($fees['Actual_Fee']) * count($ids) . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Last_Balance'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Committed_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Total'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Collected_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Consession_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Balance_Fee'] . '</td>
                                            ';
                                        }
                                    } else {
                                        echo "<script>alert('Please Select Fee Type!!')</script>";
                                    }
                                } else {
                                    echo "<script>alert('Please Select Section!!')</script>";
                                }
                            } else {
                                echo "<script>alert('Please Select Class!!')</script>";
                            }
                        } else if ($fee_by == "Route_Wise") {
                            echo "<script>document.getElementById('type').value = 'Vehicle Fee';
                            document.getElementById('type').disabled = 'disabled';
                            document.getElementById('route_wise').checked = true;
                            document.getElementById('route_row').hidden = '';
                            document.getElementById('class_row').hidden = 'hidden';</script>";
                            if ($_POST['Route']) {
                                $route = $_POST['Route'];
                                echo "<script>document.getElementById('route').value = '" . $route . "'</script>";
                                $type = 'Vehicle Fee';
                                echo "<script>document.getElementById('type').value = '" . $type . "'</script>";

                                //Arrays
                                $ids = array();
                                $fees = array();

                                //Queries
                                //Getting Id Nos of that Route
                                $id_sql = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE (Stu_Class LIKE '%CLASS%' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG') AND Van_Route = '$route'");

                                //Checking If Students Available for that Route
                                if (mysqli_num_rows($id_sql) == 0) {
                                    echo "<script>alert('No Student Found in this Route!')</script>";
                                } else {
                                    while ($id_row = mysqli_fetch_assoc($id_sql)) {
                                        array_push($ids, $id_row['Id_No']);
                                    }

                                    //Initializing Each Type of Fee
                                    $fees['Committed_Fee'] = 0;
                                    $fees['Collected_Fee'] = 0;
                                    foreach ($ids as $id) {
                                        //Getting Actual Fee for that Route
                                        $query1 = mysqli_query($link, "SELECT Fee FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                                        while ($row1 = mysqli_fetch_assoc($query1)) {
                                            $fees['Actual_Fee'] = (int)$row1['Fee'];
                                        }

                                        //Getting Student Count and Sum of Committed Fee of that Class
                                        $query2 = mysqli_query($link, "SELECT Current_Balance AS Committed_Fee,Last_Balance,Total FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $fees['Committed_Fee'] += (int)$row2['Committed_Fee'];
                                            $fees['Last_Balance'] += (int)$row2['Last_Balance'];
                                            $fees['Total'] = (int)$fees['Committed_Fee'] + (int)$fees['Last_Balance'];
                                        }

                                        //Getting Collected Fee
                                        $query3 = mysqli_query($link, "SELECT Fee AS Collected_Fee FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type'");

                                        while ($row3 = mysqli_fetch_assoc($query3)) {
                                            $fees['Collected_Fee'] += (int)$row3['Collected_Fee'];
                                        }
                                    }

                                    //Calculating Consession Fee and Balance Fee
                                    $fees['Consession_Fee'] = $fees['Actual_Fee'] * count($ids) - $fees['Committed_Fee'];
                                    $fees['Balance_Fee'] = $fees['Total'] - $fees['Collected_Fee'];
                                    //Printing All Fees
                                    echo '
                                            <td style="padding: 10px;text-align:center;">' . ($fees['Actual_Fee']) * count($ids) . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Last_Balance'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Committed_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Total'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Collected_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Consession_Fee'] . '</td>
                                            <td style="padding: 10px;text-align:center;">' . $fees['Balance_Fee'] . '</td>';
                                }
                            } else {
                                echo "<script>alert('Please Select Route!!')</script>";
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

    <!-- Change labels -->
    <script type="text/javascript">
        let route_row = document.getElementById('route_row');
        let cls_row = document.getElementById('class_row');
        type = document.getElementById('type');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    type.value = 'selectfeetype'
                    type.disabled = '';
                    if (cls_row.hidden) {
                        cls_row.hidden = '';
                    }
                    if (!route_row.hidden) {
                        route_row.hidden = 'true';
                    }
                    break;
                case 'route_wise':
                    type.value = 'Vehicle Fee'
                    type.disabled = 'disabled';
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'true';
                    }
                    if (route_row.hidden) {
                        route_row.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += "<p style='font-size:20px;'><b><?php if ($type == "Vehicle Fee") {
                                                                                                        echo "Route: ";
                                                                                                        if (isset($route)) {
                                                                                                            echo $route;
                                                                                                        }
                                                                                                    } else {
                                                                                                        echo "Class: ";
                                                                                                        if (isset($class) && isset($section)) {
                                                                                                            echo $class . ' ' . $section;
                                                                                                        }
                                                                                                    } ?></b></p>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>