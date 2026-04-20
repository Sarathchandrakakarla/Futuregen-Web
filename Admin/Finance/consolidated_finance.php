<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 74);

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
        max-width: 1200px;
        max-height: 500px;
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
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-2">Date Range: </div>
                <div class="col-lg-2">
                    <input type="date" class="form-control" name="From_Date" id="from_date">
                </div>
                <div class="col-lg-2">
                    <input type="date" class="form-control" name="To_Date" id="to_date">
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-6">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Type" id="expenses" checked value="Expenses">
                        <label class="form-check-label" for="expenses">Expenses</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Type" id="collections" value="Collections">
                        <label class="form-check-label" for="collections">Collections</label>
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
            <div class="col-lg-5">
                <h3><b>Consolidated Finance Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table hidden>
            <tr>
                <td style="font-size:30px;" colspan="4"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td>
            </tr>
            <tr>
                <td style="font-size:27px;" colspan="4" id="title"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;" id="col1">Debiter Id</th>
                    <th id="col2">Debiter Name</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                function format_date($date)
                {
                    return date('d-m-Y', strtotime($date));
                }
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $type = $_POST['Type'];
                    echo "<script>document.getElementById('" . strtolower($type) . "').checked = true;</script>";
                    if ($type == "Expenses") {
                        echo "
                        <script>
                            col1.innerHTML = 'Debiter Id';
                            col2.hidden = col2.hidden ? '' : '';
                        </script>
                        ";
                    } else if ($type == "Collections") {
                        echo "
                        <script>
                            col1.innerHTML = 'Class/Route';
                            if(!col2.hidden){
                                col2.hidden = 'hidden';
                            }
                        </script>
                        ";
                    }
                    $from_date = "";
                    $to_date = "";
                    $grand_total = 0;
                    if ($_POST['From_Date']) {
                        $from_date = $_POST['From_Date'];
                    } else {
                        if ($type == "Expenses") {
                            $query1 = mysqli_query($link, "SELECT DATE_FORMAT(MIN(STR_TO_DATE(DOP, '%d-%b-%Y')), '%Y-%m-%d') AS Start_Date FROM `tran_details`");
                        } else if ($type == "Collections") {
                            $query1 = mysqli_query($link, "SELECT DATE_FORMAT(MIN(STR_TO_DATE(DOP, '%d-%b-%Y')), '%Y-%m-%d') AS Start_Date FROM `stu_paid_fee`");
                        }
                        $row1 = mysqli_fetch_array($query1);
                        $from_date = $row1['Start_Date'];
                    }
                    echo "<script>document.getElementById('from_date').value='" . $from_date . "';</script>";
                    if ($_POST['To_Date']) {
                        $to_date = $_POST['To_Date'];
                    } else {
                        $to_date = date('Y-m-d');
                    }
                    echo "<script>document.getElementById('to_date').value='" . $to_date . "';</script>";
                    echo "<script>document.getElementById('title').innerHTML = '" . $type . "(" . format_date($from_date) . " - " . format_date($to_date) . ")';</script>";
                    if ($type == "Expenses") {
                        $query2 = mysqli_query($link, "SELECT td.AC_No AS Debiter_Id, dmd.Name AS Debiter_Name, SUM(CAST(td.Amount AS INT)) AS Total_Amount FROM tran_details td JOIN debiter_master_data dmd ON td.AC_No = dmd.AC_No WHERE STR_TO_DATE(td.DOP, '%d-%b-%Y') BETWEEN STR_TO_DATE('$from_date', '%Y-%m-%d') AND STR_TO_DATE('$to_date', '%Y-%m-%d') AND td.AC_No NOT IN ('VHDB050','VHDB051') GROUP BY td.AC_No, dmd.Name");
                        $i = 1;
                        while ($row2 = mysqli_fetch_assoc($query2)) {
                            echo "
                        <tr>
                            <td>" . $i . "</td>
                            <td>" . $row2['Debiter_Id'] . "</td>
                            <td>" . $row2['Debiter_Name'] . "</td>
                            <td>" . $row2['Total_Amount'] . "</td>
                        </tr>
                        ";
                            $grand_total += (int)$row2['Total_Amount'];
                            $i++;
                        }
                    } else if ($type == "Collections") {
                        $fee_types = ['School Fee', 'Admission Fee', 'Vehicle Fee', 'Book Fee'];
                        if ($_SESSION['school_db']['school_code'] == "FGS") {
                            $fee_types[] = 'Hostel Fee';
                        }
                        foreach ($fee_types as $fee_type) {
                            if ($fee_type != "Vehicle Fee") {
                                $query2 = mysqli_query($link, "SELECT Class,SUM(CAST(Fee AS INT)) AS Total_Amount FROM `stu_paid_fee` WHERE Type='$fee_type' AND STR_TO_DATE(DOP, '%d-%b-%Y') BETWEEN STR_TO_DATE('$from_date', '%Y-%m-%d') AND STR_TO_DATE('$to_date', '%Y-%m-%d') GROUP BY Class");
                            } else {
                                $query2 = mysqli_query($link, "SELECT Route,SUM(CAST(Fee AS INT)) AS Total_Amount FROM `stu_paid_fee` WHERE Type='$fee_type' AND STR_TO_DATE(DOP, '%d-%b-%Y') BETWEEN STR_TO_DATE('$from_date', '%Y-%m-%d') AND STR_TO_DATE('$to_date', '%Y-%m-%d') GROUP BY Route");
                            }

                            echo "
                            <tr>
                                <td colspan='3' style='font-weight:bold;text-align:center;'>" . $fee_type . "</td>
                            </tr>
                            ";
                            $i = 1;
                            $total = 0;
                            while ($row2 = mysqli_fetch_assoc($query2)) {
                                echo "
                                <tr>
                                    <td>" . $i . "</td>";
                                if ($fee_type != "Vehicle Fee") {
                                    echo "<td>" . $row2['Class'] . "</td>";
                                } else {
                                    echo "<td>" . $row2['Route'] . "</td>";
                                }
                                echo "<td style='text-align:center;'>" . $row2['Total_Amount'] . "</td>
                                </tr>
                                ";
                                $total += (int)$row2['Total_Amount'];
                                $i++;
                            }
                            $grand_total += $total;
                            echo "
                            <tr>
                                <td colspan='2' style='font-weight:bold;'>Grand Total</td>
                                <td style='font-weight:bold;'>" . $total . "</td>
                            </tr>
                            ";
                        }
                    }
                    echo "
                    <tr>";
                    if ($type == "Expenses") {
                        echo "<td colspan='3' style='font-weight:bold;'>Grand Total</td>";
                    } else if ($type == "Collections") {
                        echo "<td colspan='2' style='font-weight:bold;'>Overall Grand Total</td>";
                    }
                    echo "<td style='font-weight:bold;'>" . $grand_total . "</td>
                    </tr>
                    ";
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
            filename = "<?php echo 'Consolidated_' . $type; ?>";
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
            window.frames["print_frame"].document.body.innerHTML += "<h3 style='text-align:center;'><?php echo $type . "(" . format_date($from_date) . " - " . format_date($to_date) . ")"; ?></h3>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>