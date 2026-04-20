<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 53);

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
        max-width: 1300px;
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
            <div class="row justify-content-center mt-3">
                <div class="col-lg-3">
                    <input type="checkbox" id="select_all" name="select_all" id="select_all" onclick="toggle(this)"><label for="select_all"><b>Select All</b></label><br>
                    <input type="checkbox" class="column" value="Emp_Id" id="Emp_Id" name="columns[]"><label for="Emp_Id">Id No</label><br>
                    <input type="checkbox" class="column" value="Emp_First_Name" id="Emp_First_Name" name="columns[]"><label for="Emp_First_Name">First Name</label><br>
                    <input type="checkbox" class="column" value="Emp_Sur_Name" id="Emp_Sur_Name" name="columns[]"><label for="Emp_Sur_Name">Sur Name</label><br>
                    <input type="checkbox" class="column" value="Father_Name" id="Father_Name" name="columns[]"><label for="Father_Name">Father Name</label><br>
                    <input type="checkbox" class="column" value="Qualification" id="Qualification" name="columns[]"><label for="Qualification">Qualification</label><br>
                    <input type="checkbox" class="column" value="Relation" id="Relation" name="columns[]"><label for="Relation">Relation</label><br>
                    <input type="checkbox" class="column" value="DOB" id="DOB" name="columns[]"><label for="DOB">DOB</label><br>
                </div>
                <div class="col-lg-3">
                    <input type="checkbox" class="column" value="Mobile" id="Mobile" name="columns[]"><label for="Mobile">All Mobile Nos</label><br>
                    <input type="checkbox" class="column" value="S_Mobile" id="S_Mobile" name="columns[]"><label for="S_Mobile">Single Mobile No</label><br>
                    <input type="checkbox" class="column" value="House_No" id="House_No" name="columns[]"><label for="House_No">House No</label><br>
                    <input type="checkbox" class="column" value="Area" id="Area" name="columns[]"><label for="Area">Area</label><br>
                    <input type="checkbox" class="column" value="Village" id="Village" name="columns[]"><label for="Village">Village</label><br>
                    <input type="checkbox" class="column" value="DOJ" id="DOJ" name="columns[]"><label for="DOJ">DOJ</label><br>
                    <input type="checkbox" class="column" value="Designation" id="Designation" name="columns[]"><label for="Designation">Designation</label><br>
                </div>
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
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable();resetTableHead();">Clear</button>
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
                <h3><b>Employee Details Report</b></h3>
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
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr class="table-head">
                    <th style="padding:5px;">S.No</th>
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
                        $query1 = mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Status = 'Working' ORDER BY Emp_Id");
                        if (mysqli_num_rows($query1) == 0) {
                            echo "<script>alert('No Employee Found!!')</script>";
                        } else {
                            $cols = array();
                            if (isset($_POST['columns'])) {
                                if ($_POST['select_all']) {
                                    echo "<script>document.getElementById('select_all').checked = true;</script>";
                                }
                                foreach ($_POST["columns"] as $col) {
                                    echo "<script>document.getElementById('" . $col . "').checked = true;</script>";
                                    array_push($cols, $col);
                                    echo "<script>
                                    $('.table-head').append('<th>" . str_replace('_', ' ', $col) . "</th>')
                                    </script>";
                                }
                                if ($photo == "With_Photo") {
                                    echo "<script>
                                    $('.table-head').append('<th>Emp Image</th>')
                                    </script>";
                                }
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($query1)) {
                                    echo '<tr>
                                            <td>' . $i . '</td>';
                                    foreach ($cols as $col) {
                                        if ($col == "S_Mobile") {
                                            if (str_contains($row['Mobile'], ',')) {
                                                echo '<td>' . trim(explode(',', $row['Mobile'], 2)[0]) . '</td>';
                                            } else if (str_contains($row['Mobile'], ' ')) {
                                                echo '<td>' . trim(explode(' ', $row['Mobile'], 2)[0]) . '</td>';
                                            } else {
                                                echo '<td>' . trim($row['Mobile']) . '</td>';
                                            }
                                        } else {
                                            echo '<td>' . $row[$col] . '</td>';
                                        }
                                    }
                                    if ($photo == "With_Photo") {
                                        if (file_exists("../../Images/emp_img/" . $row['Emp_Id'] . ".jpg")) {
                                            echo '<td oncontextmenu="return false;"><img src = "../../Images/emp_img/' . $row['Emp_Id'] . '.jpg" class="rounded" width="100px" height="100px"';
                                        } else {
                                            echo '<td oncontextmenu="return false;"><img src = "../../Images/emp_img/not_photo.jpg" class="rounded" width="100px" height="100px"';
                                        }
                                    }
                                    echo '</tr>';
                                    $i++;
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

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = "employee_list";
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
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'>EMPLOYEE LIST</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>

    <!-- Reset table head -->
    <script>
        function resetTableHead() {
            // Find the <tr> with the class 'table-head'
            const tr = document.querySelector('tr.table-head');

            // Keep the first child and remove the rest
            while (tr.children.length > 1) {
                tr.removeChild(tr.lastElementChild);
            }
        }
    </script>
</body>

</html>