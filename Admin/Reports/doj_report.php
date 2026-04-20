<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 11);

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
                        <input class="form-check-input" type="radio" name="Report_By" id="by_date" checked value="By_Date">
                        <label class="form-check-label" for="by_date">By Date</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Report_By" id="by_date_range" value="By_Date_Range">
                        <label class="form-check-label" for="by_date_range">By Date Range</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <label for=""><b id="add_label">Date:</b></label>
                </div>
                <div class="col-lg-4">
                    <input type="date" class="form-control" value="<?php if (isset($from_date)) {
                                                                        echo $from_date;
                                                                    } else {
                                                                        echo date('Y-m-d');
                                                                    } ?>" name="From_Date" id="from_date" required>
                </div>
                <div class="col-lg-4" id="date_row" hidden>
                    <input type="date" class="form-control" value="<?php if (isset($to_date)) {
                                                                        echo $to_date;
                                                                    } ?>" name="To_Date" id="to_date">
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-4">
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
                    <button class="btn btn-warning" type="reset" onclick="hideTable();document.getElementById('date_row').hidden='hidden';">Clear</button>
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
                <h3><b>Date of Join Report</b></h3>
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
                <td style="font-size:20px;">Date:</td>
                <td id="date_label" style="font-size:20px;"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr class="table-head">
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Id No.</th>
                    <th>Student Name</th>
                    <th>Father Name</th>
                    <th>Class</th>
                    <th>Area</th>
                    <th>Village</th>
                    <th>Mobile</th>
                    <th>DOJ</th>
                    <th>Referred By</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                function format_date($date)
                {
                    $arr = explode('-', $date);
                    $t = $arr[0];
                    $arr[0] = $arr[2];
                    $arr[2] = $t;
                    $date = implode('-', $arr);
                    return $date;
                }
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $report_by = $_POST['Report_By'];
                    $photo = $_POST['Photo'];
                    if ($photo == 'With_Photo') {
                        echo "<script>
                            document.getElementById('w_photo').checked = true;
                            $('.table-head').append('<th>Stu Image</th>');
                            </script>";
                    } else {
                        echo "<script>
                            document.getElementById('wo_photo').checked = true;
                            </script>";
                    }
                    if ($report_by == "By_Date") {
                        $from_date = $_POST['From_Date'];
                        echo "
                            <script>
                                document.getElementById('by_date').checked = true;
                                document.getElementById('date_row').hidden = 'hidden';
                                document.getElementById('add_label').innerHTML = 'Date: ';
                                document.getElementById('from_date').value = '" . $from_date . "';
                            </script>";
                        $from_date = format_date($from_date);
                        echo "
                            <script>
                                document.getElementById('date_label').innerHTML = '" . $from_date . "';
                            </script>";
                        $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE DOJ = '$from_date'");
                    } else if ($report_by == "By_Date_Range") {
                        $from_date = $_POST['From_Date'];
                        if ($_POST['To_Date']) {
                            $to_date = $_POST['To_Date'];
                        } else {
                            $to_date = date('Y-m-d');
                        }
                        echo "
                            <script>
                                document.getElementById('by_date_range').checked = true;
                                document.getElementById('date_row').hidden = '';
                                document.getElementById('add_label').innerHTML = 'Date Range: ';
                                document.getElementById('from_date').value = '" . $from_date . "';
                                document.getElementById('to_date').value = '" . $to_date . "';
                            </script>";
                        $from_date = format_date($from_date);
                        $to_date = format_date($to_date);
                        echo "
                            <script>
                                document.getElementById('date_label').innerHTML = '" . $from_date . " - " . $to_date . "';
                            </script>";
                        $query1 = mysqli_query($link, "SELECT * FROM student_master_data WHERE STR_TO_DATE(DOJ, '%d-%m-%Y') BETWEEN STR_TO_DATE('$from_date', '%d-%m-%Y') AND STR_TO_DATE('$to_date', '%d-%m-%Y')");
                    }
                    if ($query1) {
                        if (mysqli_num_rows($query1) == 0) {
                            echo "<script>alert('No Student Joined on given dates');</script>";
                        } else {
                            $i = 1;
                            while ($row1 = mysqli_fetch_assoc($query1)) {
                                echo '
                                <tr>
                                    <td>' . $i . '</td>
                                    <td>' . $row1['Id_No'] . '</td>
                                    <td>' . $row1['First_Name'] . '</td>
                                    <td>' . $row1['Father_Name'] . '</td>
                                    <td style="white-space:nowrap;">' . $row1['Stu_Class'] . ' ' . $row1['Stu_Section'] . '</td>
                                    <td>' . $row1['Area'] . '</td>
                                    <td>' . $row1['Village'] . '</td>
                                    <td>' . $row1['Mobile'] . '</td>
                                    <td style="white-space:nowrap;">' . $row1['DOJ'] . '</td>
                                    <td>' . $row1['Referred_By'] . '</td>';
                                if ($photo == "With_Photo") {
                                    if (file_exists("../../Images/stu_img/" . $row1['Id_No'] . ".jpg")) {
                                        echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/' . $row1['Id_No'] . '.jpg" class="rounded" width="100px" height="100px"';
                                    } else {
                                        echo '<td oncontextmenu="return false;"><img src = "../../Images/stu_img/not_photo.jpg" class="rounded" width="100px" height="100px"';
                                    }
                                }
                                echo '</tr>
                                ';
                                $i++;
                            }
                        }
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
            let date = '<?php if ($report_by == "By_Date") echo $from_date;
                        else if ($report_by == "By_Date_Range") echo $from_date . ' - ' . $to_date ?>';
            filename = "Joined Students " + date;
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
            window.frames["print_frame"].document.body.innerHTML += "<p style='font-size:20px;'><b>Date: </b> <?php if ($report_by == 'By_Date') {
                                                                                                                    echo $from_date;
                                                                                                                } else if ($report_by == 'By_Date_Range') {
                                                                                                                    echo $from_date . ' - ' . $to_date;
                                                                                                                } ?></p>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>

    <!-- Change labels -->
    <script type="text/javascript">
        let result = document.getElementById('add_label');
        let date_row = document.getElementById('date_row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            let message;
            switch (target.id) {
                case 'by_date':
                    message = 'Date: ';
                    if (!date_row.hidden) {
                        date_row.hidden = 'hidden';
                    }
                    break;
                case 'by_date_range':
                    message = "Date Range: ";
                    if (date_row.hidden) {
                        date_row.hidden = '';
                    }
                    break;
                default:
                    message = document.getElementById('add_label').innerHTML;
            }
            result.innerHTML = message;
        });
    </script>
</body>

</html>