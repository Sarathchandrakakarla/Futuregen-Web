<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 43);

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
        margin-left: 15%;
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
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <label for=""> <b>Employee Id:</b></label>
                </div>
                <div class="col-lg-3">
                    <input type="text" placeholder="Enter Employee Id" class="form-control" name="Id_No" id="id_no" oninput="this.value = this.value.toUpperCase()" required>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
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
                    <div class="btn-wrapper"
                        <?php if (!can('view', 53)) { ?>
                        title="You don't have permission to view employee list"
                        <?php } ?>>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#emplist" <?php echo !can('view', 53) ? 'disabled' : ''; ?>>
                            Employee List
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Faculty Time Table</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table hidden>
            <tr>
                <td style="font-size:30px;text-align:center;" colspan="8"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td>
            </tr>
            <tr>
                <td style="font-size:25px;text-align:center;" colspan="8"><span id="emp_label"></span>Time Table</td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <?php
                    for ($i = 1; $i <= 8; $i++) {
                    ?>
                        <th style="padding: 5px;text-align:center;">Period <?php echo $i; ?></th>
                    <?php
                    }
                    ?>
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
                        $id = $_POST['Id_No'];
                        echo '<script>
                            document.getElementById("id_no").value = "' . $id . '";
                        </script>';
                        $query1 = mysqli_query($link, "SELECT CASE WHEN NOT EXISTS (SELECT 1 FROM employee_master_data WHERE Emp_Id = '$id') THEN 'Employee Not Found' WHEN NOT EXISTS (SELECT 1 FROM time_table WHERE (Period1 LIKE '$id%' OR Period2 LIKE '$id%' OR Period3 LIKE '$id%' OR Period4 LIKE '$id%' OR Period5 LIKE '$id%' OR Period6 LIKE '$id%' OR Period7 LIKE '$id%' OR Period8 LIKE '$id%')) THEN 'Time Table Not Assigned' ELSE 'OK' END AS status");
                        $status = mysqli_fetch_array($query1)['status'];
                        if ($status == "OK") {
                            $query2 = mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Emp_Id = '$id'");
                            $row2 = mysqli_fetch_row($query2);
                            $emp_name = $row2[2];
                            echo "<script>document.getElementById('emp_label').innerHTML = '" . $id . " " . $emp_name . " ';</script>";
                            $query3 = mysqli_query($link, "SELECT GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period1, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period1, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period1, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period2, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period2, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period2, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period3, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period3, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period3, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period4, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period4, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period4, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period5, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period5, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period5, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period6, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period6, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period6, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period7, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period7, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period7, GROUP_CONCAT(CASE WHEN SUBSTRING_INDEX(Period8, ',', 1) = '$id' THEN CONCAT(Class, ' ', Section, '|', IFNULL(NULLIF(SUBSTRING_INDEX(Period8, ',', -1), '$id'), 'No Subject')) ELSE NULL END SEPARATOR '; |') AS Period8 FROM time_table");


                            $row3 = mysqli_fetch_row($query3);
                            foreach ($row3 as $period => $value) {
                                if (is_null($value) || trim($value) == '') {
                                    $row3[$period] = "Not Alloted";
                                }
                                echo "<td style='padding:5px;text-align:center;white-space:nowrap;'>" . str_replace('|', '<br>', $row3[$period]) . "</td>";
                            }
                        } else if ($status == "Employee Not Found") {
                            echo "<script>alert('" . $status . "');</script>";
                        } else {
                            echo "<tr>
                                    <td colspan='8' class='text-center'>" . $status . "</td>
                                </tr>";
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- Employee List Modal -->
    <div class="modal fade" id="emplist" tabindex="-1" aria-labelledby="empListLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="empListLabel">Employee List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                            <th class="border border-dark">S No</th>
                            <th class="border border-dark">Id No.</th>
                            <th class="border border-dark">Name</th>
                        </thead>
                        <tbody>
                            <?php
                            if (can('view', 53)) {
                                $query4 = mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Status = 'Working' ORDER BY Emp_Id");
                                $i = 1;
                                while ($row4 = mysqli_fetch_assoc($query4)) {
                                    echo "
                                    <tr>
                                        <td class='border border-dark'>" . $i . "</td>
                                        <td class='border border-dark'>" . $row4['Emp_Id'] . "</td>
                                        <td class='border border-dark'>" . $row4['Emp_First_Name'] . "</td>
                                    </tr>
                                    ";
                                    $i++;
                                }
                            } else {
                                echo "
                                <tr>
                                    <td class='border border-dark text-center' colspan='3'>You don't have permission to view employee list</td>
                                </tr>
                                ";
                            }
                            ?>
                        </tbody>

                    </table>

                </div>
            </div>
        </div>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            empname = '<?php echo $id . ' ' . $emp_name; ?>'
            filename = empname + " Time Table";
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
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?php if (isset($id)) {
                                                                                                        echo $id . ' ' . $emp_name;
                                                                                                    } ?> Time Table</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>