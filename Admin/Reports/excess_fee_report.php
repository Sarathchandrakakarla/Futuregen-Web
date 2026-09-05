<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 66);

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
        max-width: 1300px;
        max-height: 500px;
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 25%;
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
                        <input class="form-check-input" type="radio" name="report_type" id="excess" checked value="Excess">
                        <label class="form-check-label" for="excess">Excess</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_type" id="not_paid" value="Not_Paid">
                        <label class="form-check-label" for="not_paid">Not Paid</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_type" id="paid" value="Paid">
                        <label class="form-check-label" for="paid">Paid</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
                    <select name="Type" id="type" class="form-control" required>
                        <option value="" selected disabled>-- Select Fee Type --</option>
                        <option value="School Fee">School Fee</option>
                        <option value="Vehicle Fee">Vehicle Fee</option>
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
            <div class="col-lg-7">
                <h3><b>Excess Fee Balance Student Details Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table hidden>
            <tr>
                <td colspan="4"></td>
                <td style="font-size:30px;" colspan="4"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr id="headings">
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Id No.</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th id="committed">Committed Fee</th>
                    <th id="last_balance">Last Year Balance</th>
                    <th id="total">Total</th>
                    <th id="paid_header" hidden>Paid</th>
                    <th id="route_header" hidden>Route</th>
                    <th>Mobile Number</th>
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
                        $report_type = $_POST['report_type'];
                        echo "<script>" . strtolower($report_type) . ".checked = true;</script>";
                        $type = $_POST['Type'];
                        echo "<script>type.value='" . $type . "';</script>";
                        $ids = [];
                        $fees = [];
                        if ($report_type == 'Paid') {
                            echo '<script>
                            document.getElementById("total").hidden = "hidden";
                            document.getElementById("committed").innerHTML = "Total Paid";
                            document.getElementById("last_balance").innerHTML = "Balance";
                            document.getElementById("route_header").hidden = "";
                            </script>';
                            if ($type == "Vehicle Fee") {
                                echo '<script>document.getElementById("route_header").hidden = "";</script>';

                                $query1 = mysqli_query($link, "SELECT smd.Id_No,smd.First_Name,CONCAT(smd.Stu_Class,' ',smd.Stu_Section) AS Class,smd.Mobile,smd.Van_Route,p.Total_Paid,CASE WHEN sfmd.Id_No IS NOT NULL THEN (sfmd.Current_Balance + sfmd.Last_Balance) - p.Total_Paid ELSE fb.Balance - p.Total_Paid END AS Balance FROM (SELECT Id_No,SUM(Fee) AS Total_Paid FROM stu_paid_fee WHERE Type = '$type' GROUP BY Id_No) p JOIN student_master_data smd ON smd.Id_No = p.Id_No LEFT JOIN stu_fee_master_data sfmd ON sfmd.Id_No = p.Id_No AND sfmd.Type = '$type' LEFT JOIN fee_balances fb ON fb.Id_No = p.Id_No AND fb.Type = '$type' WHERE smd.Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS') AND smd.Van_Route IN (SELECT Van_Route FROM van_route)");
                            } else {
                                echo '<script>document.getElementById("route_header").hidden = "hidden";</script>';

                                $query1 = mysqli_query($link, "SELECT smd.Id_No,smd.First_Name,CONCAT(smd.Stu_Class,' ',smd.Stu_Section) AS Class,smd.Mobile,smd.Van_Route,p.Total_Paid,CASE WHEN sfmd.Id_No IS NOT NULL THEN (sfmd.Current_Balance + sfmd.Last_Balance) - p.Total_Paid ELSE fb.Balance - p.Total_Paid END AS Balance FROM (SELECT Id_No,SUM(Fee) AS Total_Paid FROM stu_paid_fee WHERE Type = '$type' GROUP BY Id_No) p JOIN student_master_data smd ON smd.Id_No = p.Id_No LEFT JOIN stu_fee_master_data sfmd ON sfmd.Id_No = p.Id_No AND sfmd.Type = '$type' LEFT JOIN fee_balances fb ON fb.Id_No = p.Id_No AND fb.Type = '$type' WHERE smd.Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS') " . ($type == "Hostel Fee" ? "AND smd.Student_Type = 'Hosteller'" : ""));
                            }
                            if (mysqli_num_rows($query1) == 0) {
                                echo "<script>alert('No Students Paid ');</script>";
                            } else {
                                $i = 1;
                                while ($row1 = mysqli_fetch_assoc($query1)) {
                                    echo '
                                    <tr>
                                        <td>' . $i . '</td>
                                        <td>' . $row1['Id_No'] . '</td>
                                        <td>' . $row1['First_Name'] . '</td>
                                        <td>' . $row1['Class'] . '</td>
                                        <td>' . $row1['Total_Paid'] . '</td>
                                        <td>' . $row1['Balance'] . '</td>
                                        ';
                                    if ($type == "Vehicle Fee") {
                                        echo '
                                        <td>' . $row1['Van_Route'] . '</td>
                                        ';
                                    }
                                    echo '
                                        <td>' . $row1['Mobile'] . '</td>
                                    </tr>
                                    ';
                                    $i++;
                                }
                            }
                        } else {
                            if ($type != "Vehicle Fee") {
                                $classes = ['PreKG', 'LKG', 'UKG'];
                                for ($i = 1; $i <= 10; $i++) {
                                    $classes[] = $i . " CLASS";
                                }
                                foreach ($classes as $class) {
                                    $query2 = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class'");
                                    while ($row2 = mysqli_fetch_array($query2)) {
                                        $ids[] = $row2['Id_No'];
                                    }
                                }
                            } else {
                                $routes = [];
                                $query1 = mysqli_query($link, "SELECT * FROM van_route ORDER BY Van_Route");
                                while ($row1 = mysqli_fetch_array($query1)) {
                                    $routes[] = $row1['Van_Route'];
                                }
                                foreach ($routes as $route) {
                                    $query2 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Van_Route = '$route' AND ((Stu_Class LIKE '%CLASS%') OR (Stu_Class LIKE '%KG')) ORDER BY Id_No");
                                    while ($row2 = mysqli_fetch_assoc($query2)) {
                                        $ids[] = $row2['Id_No'];
                                    }
                                }
                            }
                            foreach ($ids as $id) {
                                $query3 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id'");
                                while ($row3 = mysqli_fetch_assoc($query3)) {
                                    $query4 = mysqli_query($link, "SELECT * FROM stu_fee_master_data WHERE Id_No = '" . $row3['Id_No'] . "' AND Type = '" . $type . "'");
                                    if (mysqli_num_rows($query4) == 0 && $type != "Book Fee") {
                                        echo "<script>alert('" . $row3['Id_No'] . " Not Found in fee master data for this Fee Type!');</script>";
                                    } else {
                                        $query5 = mysqli_query($link, "SELECT * FROM stu_paid_fee WHERE Id_No = '" . $row3['Id_No'] . "' AND Type = '" . $type . "'");
                                        $paid = 0;
                                        while ($row5 = mysqli_fetch_assoc($query5)) {
                                            $paid += (int)$row5['Fee'];
                                        }
                                        while ($row4 = mysqli_fetch_array($query4)) {
                                            if ($report_type == "Excess" && (int)$row4['Last_Balance'] != 0 && (int)$row4['Current_Balance'] + (int)$row4['Last_Balance'] - $paid > (int)$row4['Current_Balance']) {
                                                $fees[$row3['Id_No']] = ["Name" => $row3['First_Name'], "Class" => $row3['Stu_Class'] . " " . $row3['Stu_Section'], "Committed" => $row4['Current_Balance'], "Previous" => $row4['Last_Balance'], "Total" => (int)$row4['Current_Balance'] + (int)$row4['Last_Balance'], "Paid" => $paid, "Mobile" => $row3['Mobile']];
                                                if ($type == "Vehicle Fee") {
                                                    $fees[$row3['Id_No']]["Route"] = $row3['Van_Route'];
                                                }
                                            } else if ($report_type == "Not_Paid" && $paid == 0) {
                                                $fees[$row3['Id_No']] = ["Name" => $row3['First_Name'], "Class" => $row3['Stu_Class'] . " " . $row3['Stu_Section'], "Committed" => $row4['Current_Balance'], "Previous" => $row4['Last_Balance'], "Total" => (int)$row4['Current_Balance'] + (int)$row4['Last_Balance'], "Mobile" => $row3['Mobile']];
                                                if ($type == "Vehicle Fee") {
                                                    $fees[$row3['Id_No']]["Route"] = $row3['Van_Route'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            if ($type == "Vehicle Fee") {
                                echo '
                            <script>$("#headings").append("<th>Route</th>")</script>
                            ';
                            }
                            if ($report_type == "Excess") {
                                echo '
                            <script>document.getElementById("paid_header").hidden = "";</script>
                            ';
                            } else {
                                echo '
                            <script>document.getElementById("paid_header").hidden = "hidden";</script>
                            ';
                            }
                            $i = 1;
                            foreach ($fees as $id => $details) {
                                echo '
                            <tr>
                                <td>' . $i . '</td>
                                <td>' . $id . '</td>
                                <td>' . $details['Name'] . '</td>
                                <td>' . $details['Class'] . '</td>
                                <td>' . $details['Committed'] . '</td>
                                <td>' . $details['Previous'] . '</td>
                                <td>' . $details['Total'] . '</td>
                                ';
                                if ($report_type == "Excess") {
                                    echo '
                                <td>' . $details['Paid'] . '</td>
                                ';
                                }
                                if ($type == "Vehicle Fee") {
                                    echo '
                                <td>' . $details['Route'] . '</td>
                                ';
                                }
                                echo '
                                <td>' . $details['Mobile'] . '</td>
                            </tr>
                            ';
                                $i++;
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

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            stuclass = '<?php echo $class; ?>';
            stusection = '<?php echo $section; ?>';
            filename = stuclass + stusection;
            exportTableToExcel({
                tableId: 'table-container',
                filename: filename,
            });
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