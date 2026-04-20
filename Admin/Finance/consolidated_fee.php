<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 73);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php
function getAcademicYear($startMonth = 4)
{
    $year = date("Y");
    $month = date("n");

    $start = ($month < $startMonth) ? $year - 1 : $year;

    return $start . "-" . substr($start + 1, -2);
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
        max-width: 1200px;
        max-height: 700px;
        margin-left: 8%;
        overflow-x: scroll;
    }

    th,
    td {
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
        </form>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Class Wise Consolidated Fee Report</b></h3>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Futuregen High School (<?= getAcademicYear(); ?>)</b></h3>
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
                <td><b>Type of Fee:</b></td>
                <td id="fee_label"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead id="thead">
                <th style="text-align:center;padding: 5px;">Class/Route</th>
                <th style="text-align:center;padding: 5px;">Actual Fee</th>
                <th style="text-align:center;padding: 5px;">Committed Fee</th>
                <th style="text-align:center;padding: 5px;">Last Year Balances</th>
                <th style="text-align:center;padding: 5px;">Total Fee</th>
                <th style="text-align:center;padding: 5px;">Collected Fee</th>
                <th style="text-align:center;padding: 5px;">Balance Fee</th>
                <th style="text-align:center;padding: 5px;">Consession Fee</th>
                <th style="text-align:center;padding: 5px;">% of Collection</th>
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
                        if ($_POST['Type']) {
                            $type = $_POST['Type'];
                            echo "<script>document.getElementById('type').value = '" . $type . "';
                            document.getElementById('fee_label').innerHTML = '" . $type . "';</script>";

                            if ($type == "Vehicle Fee") {

                                //For Vehicle Fee Type

                                //Arrays
                                $routes = array();
                                $ids = array();
                                $route_count = array();
                                $actual = array();
                                $committed = array();
                                $last = array();
                                $total = array();
                                $collected = array();
                                $balance = array();
                                $consession = array();
                                $percentage = array();

                                //Queries

                                //For Getting Routes

                                $query = mysqli_query($link, "SELECT * FROM `van_route` ORDER BY Van_Route");
                                while ($row = mysqli_fetch_assoc($query)) {
                                    array_push($routes, $row['Van_Route']);
                                }

                                //For Counting Number of Students in Each Route
                                foreach ($routes as $route) {
                                    $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '%CLASS%' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG')");
                                    $c = mysqli_num_rows($query1);
                                    $route_count[$route] = $c;
                                    $temp = array();
                                    while ($id_row = mysqli_fetch_assoc($query1)) {
                                        array_push($temp, $id_row['Id_No']);
                                    }
                                    $ids[$route] = $temp;
                                }

                                //For Getting Actual Fee of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $query2 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                                    while ($row2 = mysqli_fetch_assoc($query2)) {
                                        $actual[$route] = (int)$route_count[$route] * (int)$row2['Fee'];
                                        $sum += (int)$route_count[$route] * (int)$row2['Fee'];
                                    }
                                }
                                $actual["Total"] = $sum;

                                //For Getting Committed Fee of Each Route
                                $sum = 0;
                                $diff = array();
                                foreach ($routes as $route) {
                                    $route_sum = 0;
                                    foreach ($ids[$route] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                        if (mysqli_num_rows($query2) == 0) {
                                            echo "<script>alert(" . $id . "' Not Found in Fee Master Data!Please Verify this!')</script>";
                                        } else {
                                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                                $id_route = $row2['Route'];
                                                if ($id_route != $route) {
                                                    array_push($diff, $id);
                                                    #echo "<script>alert('".$id." Route is different in student master data and fee master data!Please Verify this!')</script>";
                                                }
                                                $route_sum += (int)$row2['Current_Balance'];
                                            }
                                        }
                                    }
                                    $committed[$route] = $route_sum;
                                    $sum += $route_sum;
                                }
                                $committed["Total"] = $sum;

                                //For Getting Last Year Balances of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $route_sum = 0;
                                    foreach ($ids[$route] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $route_sum += (int)$row2['Last_Balance'];
                                        }
                                    }
                                    $last[$route] = $route_sum;
                                    $sum += $route_sum;
                                }
                                $last["Total"] = $sum;

                                //For Calculating Total Fee of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $total[$route] = (int)$committed[$route] + (int)$last[$route];
                                    $sum += (int)$total[$route];
                                }
                                $total["Total"] = $sum;

                                //For Getting Collected/Paid Fee of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $route_sum = 0;
                                    foreach ($ids[$route] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $route_sum += (int)$row2['Fee'];
                                        }
                                    }
                                    $collected[$route] = $route_sum;
                                    $sum += $route_sum;
                                }
                                $collected["Total"] = $sum;

                                //For Calculating Balance Fee of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $balance[$route] = (int)$total[$route] - (int)$collected[$route];
                                    $sum += (int)$balance[$route];
                                }
                                $balance["Total"] = $sum;

                                //For Calculating Consession Fee of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    $consession[$route] = (int)$actual[$route] - (int)$committed[$route];
                                    $sum += (int)$consession[$route];
                                }
                                $consession["Total"] = $sum;

                                //For Calculating Collection Percentage of Each Route
                                $sum = 0;
                                foreach ($routes as $route) {
                                    if ((float)$committed[$route] == 0) {
                                        $percentage[$route] = 0;
                                    } else {
                                        $percentage[$route] = round(((float)$collected[$route] * 100) / (float)$committed[$route], 2);
                                    }
                                    $sum += (float)$percentage[$route];
                                }
                                $tot_percent = round((float)$collected["Total"] * 100 / (float)$committed["Total"], 2);
                                $percentage["Total"] = $tot_percent;

                                //Displaying The Data
                                foreach ($routes as $route) {
                                    echo '<td style="text-align:center">' . $route . '</td>
                                    <td style="text-align:center">' . $actual[$route] . '</td>
                                    <td style="text-align:center">' . $committed[$route] . '</td>
                                    <td style="text-align:center">' . $last[$route] . '</td>
                                    <td style="text-align:center">' . $total[$route] . '</td>
                                    <td style="text-align:center">' . $collected[$route] . '</td>
                                    <td style="text-align:center">' . $balance[$route] . '</td>
                                    <td style="text-align:center">' . $consession[$route] . '</td>
                                    <td style="text-align:center">' . $percentage[$route] . '</td>
                                </tr>';
                                }
                                echo '<tr>
                                    <td style="text-align:center"><b>Total</b></td>
                                    <td style="text-align:center"><b>' . end($actual) . '</b></td>
                                    <td style="text-align:center"><b>' . end($committed) . '</b></td>
                                    <td style="text-align:center"><b>' . end($last) . '</b></td>
                                    <td style="text-align:center"><b>' . end($total) . '</b></td>
                                    <td style="text-align:center"><b>' . end($collected) . '</b></td>
                                    <td style="text-align:center"><b>' . end($balance) . '</b></td>
                                    <td style="text-align:center"><b>' . end($consession) . '</b></td>
                                    <td style="text-align:center"><b>' . end($percentage) . '</b></td>
                                </tr>';
                            } else {

                                //For All Types of Fees Except Vehicle Fee

                                //Arrays
                                $classes = array("PreKG", "LKG", "UKG");
                                $i = 1;
                                while ($i <= 10) {
                                    array_push($classes, $i . " CLASS");
                                    $i++;
                                }
                                $class_count = array();
                                $ids = array();
                                $actual = array();
                                $committed = array();
                                $last = array();
                                $total = array();
                                $collected = array();
                                $balance = array();
                                $consession = array();
                                $percentage = array();

                                //Queries

                                //For Counting Number of Students in Each Class
                                foreach ($classes as $class) {
                                    if ($type == "Admission Fee") {
                                        $query1 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Class = '$class' AND Type='Admission Fee'");
                                    } else {
                                        $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class'");
                                    }
                                    $temp = array();
                                    while ($row1 = mysqli_fetch_assoc($query1)) {
                                        array_push($temp, $row1['Id_No']);
                                    }
                                    $ids[$class] = $temp;
                                    $c = mysqli_num_rows($query1);
                                    $class_count[$class] = $c;
                                }

                                //For Getting Actual Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $query2 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
                                    while ($row2 = mysqli_fetch_assoc($query2)) {
                                        $actual[$class] = (int)$class_count[$class] * (int)$row2['Fee'];
                                        $sum += (int)$class_count[$class] * (int)$row2['Fee'];
                                    }
                                }
                                $actual["Total"] = $sum;

                                //For Getting Committed Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $class_sum = 0;
                                    foreach ($ids[$class] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $class_sum += (int)$row2['Current_Balance'];
                                        }
                                    }
                                    $committed[$class] = $class_sum;
                                    $sum += (int)$class_sum;
                                }
                                $committed["Total"] = $sum;

                                //For Getting Last Year Balance Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $class_sum = 0;
                                    foreach ($ids[$class] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $class_sum += (int)$row2['Last_Balance'];
                                        }
                                    }
                                    $last[$class] = $class_sum;
                                    $sum += (int)$class_sum;
                                }
                                $last["Total"] = $sum;

                                //For Calculating Total Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $total[$class] = (int)$committed[$class] + (int)$last[$class];
                                    $sum += (int)$total[$class];
                                }
                                $total["Total"] = $sum;

                                //For Getting Collected/Paid Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $class_sum = 0;
                                    foreach ($ids[$class] as $id) {
                                        $query2 = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type'");
                                        while ($row2 = mysqli_fetch_assoc($query2)) {
                                            $class_sum += (int)$row2['Fee'];
                                        }
                                    }
                                    $collected[$class] = $class_sum;
                                    $sum += (int)$class_sum;
                                }
                                $collected["Total"] = $sum;

                                //For Calculating Balance Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $balance[$class] = (int)$total[$class] - (int)$collected[$class];
                                    $sum += (int)$balance[$class];
                                }
                                $balance["Total"] = $sum;

                                //For Calculating Consession Fee of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    $consession[$class] = (int)$actual[$class] - (int)$committed[$class];
                                    $sum += (int)$consession[$class];
                                }
                                $consession["Total"] = $sum;

                                //For Calculating Collection Percentage of Each Class
                                $sum = 0;
                                foreach ($classes as $class) {
                                    if ((float)$total[$class] == 0) {
                                        $percentage[$class] = 0;
                                    } else {
                                        $percentage[$class] = round(((float)$collected[$class] * 100) / (float)$total[$class], 2);
                                    }
                                    $sum += (float)$percentage[$class];
                                }
                                $tot_percent = round((float)$collected["Total"] * 100 / (float)$total["Total"], 2);
                                $percentage["Total"] = $tot_percent;

                                //Displaying The Data
                                foreach ($classes as $class) {
                                    echo '<td style="text-align:center">' . $class . '</td>
                                    <td style="text-align:center">' . $actual[$class] . '</td>
                                    <td style="text-align:center">' . $committed[$class] . '</td>
                                    <td style="text-align:center">' . $last[$class] . '</td>
                                    <td style="text-align:center">' . $total[$class] . '</td>
                                    <td style="text-align:center">' . $collected[$class] . '</td>
                                    <td style="text-align:center">' . $balance[$class] . '</td>
                                    <td style="text-align:center">' . $consession[$class] . '</td>
                                    <td style="text-align:center">' . $percentage[$class] . '</td>
                                </tr>';
                                }
                                echo '<tr>
                                    <td style="text-align:center"><b>Total</b></td>
                                    <td style="text-align:center"><b>' . end($actual) . '</b></td>
                                    <td style="text-align:center"><b>' . end($committed) . '</b></td>
                                    <td style="text-align:center"><b>' . end($last) . '</b></td>
                                    <td style="text-align:center"><b>' . end($total) . '</b></td>
                                    <td style="text-align:center"><b>' . end($collected) . '</b></td>
                                    <td style="text-align:center"><b>' . end($balance) . '</b></td>
                                    <td style="text-align:center"><b>' . end($consession) . '</b></td>
                                    <td style="text-align:center"><b>' . end($percentage) . '</b></td>
                                </tr>';
                            }
                        } else {
                            echo "<script>alert('Please Select Fee Type')</script>";
                        }
                    }
                    ?>
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = "<?php echo 'Consolidated_Fee_' . $type; ?>";
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
            window.frames["print_frame"].document.body.innerHTML += "<p><b>Type of Fee:</b><?php echo $type;
                                                                                            ?></p>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>