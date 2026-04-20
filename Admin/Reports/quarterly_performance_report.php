<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 26);

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
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    .edit {
        cursor: pointer;
    }

    .modal {
        max-height: 700px;
        overflow-y: scroll;
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
    <?php include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_by" id="class_wise" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="report_by" id="individual" value="Individual">
                        <label class="form-check-label" for="individual">Individual</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Action" id="report" checked value="Report">
                        <label class="form-check-label" for="report">Report</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Action" id="save" value="Save">
                        <label class="form-check-label" for="save">Save</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-2 report-row">
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Class" id="class" aria-label="Default select example">
                        <option selected disabled>-- Select Class --</option>
                        <option>PreKG</option>
                        <option>LKG</option>
                        <option>UKG</option>
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
            <div class="row justify-content-center mt-2 id-row" hidden>
                <label for="Id" class="col-sm-2 col-form-label"><b>Student Id No</b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="Id_No" id="id_no" oninput="this.value = this.value.toUpperCase()">
                </div>
            </div>
            <div class="row justify-content-center mt-2 save-row" hidden>
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Quarter" id="quarter" aria-label="Default select example">
                        <option selected disabled>-- Select Quarter --</option>
                        <option value="Q1">Quarter 1</option>
                        <option value="Q2">Quarter 2</option>
                        <option value="Q3">Quarter 3</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="container report-col-row">
            <div class="row justify-content-center mt-3">
                <div class="col-lg-3">
                    <input type="checkbox" id="select_all" name="select_all" id="select_all" onclick="toggle(this)"><label for="select_all"><b>Select All</b></label><br>
                    <input type="checkbox" class="column" value="Q1" id="Q1" name="columns[]"><label for="Q1">Quarter 1</label><br>
                    <input type="checkbox" class="column" value="Q2" id="Q2" name="columns[]"><label for="Q2">Quarter 2</label><br>
                    <input type="checkbox" class="column" value="Q3" id="Q3" name="columns[]"><label for="Q3">Quarter 3</label><br>
                    <input type="checkbox" class="column" value="Present" id="Present" name="columns[]"><label for="Present">Present</label><br>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4 report-btn-row">
                <div class="col-lg-5">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable();document.querySelector('.report-row').hidden = '';document.querySelector('.report-btn-row').hidden = '';document.querySelector('.save-row').hidden = 'hidden';document.querySelector('.save-btn-row').hidden = 'hidden';document.querySelector('.report-col-row').hidden = '';">Clear</button>
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
            <div class="row justify-content-center mt-4 save-btn-row" hidden>
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to save this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="save" onclick="if(!confirm('Confirm to Save Data?')){return false;}else{return true;}" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Save</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable();document.querySelector('.report-row').hidden = '';document.querySelector('.report-btn-row').hidden = '';document.querySelector('.save-row').hidden = 'hidden';document.querySelector('.save-btn-row').hidden = 'hidden';document.querySelector('.report-col-row').hidden = '';">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('delete', MENU_ID)) { ?>
                        title="You don't have permission to delete this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="delete" onclick="if(!confirm('Confirm to Delete Present Data?')){return false;}else{return true;}" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>Delete Present Data</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-6">
                <h3><b>Quartely Student Performance Report</b></h3>
            </div>
        </div>
    </div>
    <form action="" method="post">
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
                    <td style="font-size:20px;color:red">Name of Class:</td>
                    <td id="class_label" style="font-size:20px;"></td>
                </tr>
            </table>
            <table class="table table-striped table-hover" border="1">
                <thead class="bg-secondary text-light">
                    <tr style="padding: 5px;" class="table-head">
                        <th class="border" style="padding:5px;text-align:center;" rowspan="2">S.No</th>
                        <th class="border" style="padding:5px;text-align:center;" rowspan="2">Id No.</th>
                        <th class="border" style="padding:5px;text-align:center;" rowspan="2">Name</th>
                        <th class="border" style="padding:5px;text-align:center;" rowspan="2">Class</th>
                    </tr>
                    <tr style="padding: 5px;" class="criteria-head">
                    </tr>
                </thead>
                <tbody id="tbody">
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        echo "
                        <script>
                            document.getElementById('report').checked = true;
                            document.querySelector('.report-row').hidden = '';
                            document.querySelector('.report-col-row').hidden = '';
                            document.querySelector('.report-btn-row').hidden = '';
                            document.querySelector('.save-row').hidden = 'hidden';
                            document.querySelector('.save-col-row').hidden = 'hidden';
                            document.querySelector('.save-btn-row').hidden = 'hidden';
                        </script>
                        ";
                        $report_by = $_POST['report_by'];
                        if ($report_by == "Class_Wise") {
                            echo "
                            <script>
                                document.getElementById('class_wise').checked = true;
                                document.querySelector('.report-row').hidden = '';
                                document.querySelector('.id-row').hidden = 'hidden';
                            </script>
                            ";
                        } else if ($report_by == "Individual") {
                            echo "
                            <script>
                                document.getElementById('individual').checked = true;
                                document.querySelector('.report-row').hidden = 'hidden';
                                document.querySelector('.id-row').hidden = '';
                            </script>
                            ";
                        }
                        $action = $_POST['Action'];
                        echo "<script>document.getElementById('report').checked = true;</script>";
                        $cols = array();
                        if (isset($_POST['columns'])) {
                            if ($_POST['select_all']) {
                                echo "<script>document.getElementById('select_all').checked = true;</script>";
                            }
                            foreach ($_POST["columns"] as $col) {
                                echo "<script>document.getElementById('" . $col . "').checked = true;</script>";
                                array_push($cols, $col);
                            }
                            $categories = ['Reading', 'Writing', 'Learning', 'Handwriting', 'Response', 'Overall', 'Grade'];
                            foreach ($categories as $cat) {
                                echo "
                                    <script>
                                    $('.table-head').append('<th class=\'border\' style=\'padding:5px;text-align:center;\' colspan=\'" . count($cols) . "\'>" . $cat . "</th>');
                                    </script>
                                    ";
                                foreach ($cols as $col) {
                                    echo "<script>
                                        $('.criteria-head').append('<th class=\'border\' style=\'padding:5px;\'>" . str_replace('Q', 'Quarter', $col) . "</th>');
                                    </script>";
                                }
                            }
                            $query1 = "SELECT smd.*,";
                            foreach ($cols as $col) {
                                if ($col != "Present") {
                                    foreach ($categories as $cat) {
                                        $query1 .= " spm." . $col . "_" . $cat . ",";
                                    }
                                    $query1 .= " spm." . $col . "_Timestamp,";
                                } else {
                                    foreach ($categories as $cat) {
                                        $query1 .= " sp." . $cat . ",";
                                    }
                                }
                            }
                            $query1 = rtrim($query1, ',');
                            $query1 .= " FROM `student_master_data` smd LEFT JOIN `stu_performance_master` spm ON smd.Id_No = spm.Id_No";
                            if (in_array('Present', $cols)) {
                                $query1 .= " LEFT JOIN `student_performance` sp ON smd.Id_No = sp.Id_No";
                            }
                            if ($report_by == "Class_Wise") {
                                if (!isset($_POST['Class']) && isset($_POST['Section'])) {
                                    echo "<script>alert('Section Only is Not Allowed!');</script>";
                                    exit;
                                } else if (!isset($_POST['Class']) && !isset($_POST['Section'])) {
                                    $query1 .= " WHERE smd.Stu_Class LIKE '% CLASS' OR smd.Stu_Class IN ('PreKG','LKG','UKG') ";
                                } else if (isset($_POST['Class']) && !isset($_POST['Section'])) {
                                    $class = $_POST['Class'];
                                    echo "<script>document.getElementById('class').value = '" . $class . "';</script>";
                                    $query1 .= " WHERE smd.Stu_Class = '" . $class . "' ";
                                } else if (isset($_POST['Class']) && isset($_POST['Section'])) {
                                    $class = $_POST['Class'];
                                    $section = $_POST['Section'];
                                    echo "<script>
                                    document.getElementById('class').value = '" . $class . "';
                                    document.getElementById('sec').value = '" . $section . "';
                                    </script>";
                                    $query1 .= " WHERE smd.Stu_Class = '" . $class . "' AND smd.Stu_Section = '" . $section . "'";
                                }
                                $query1 .= " ORDER BY FIELD(smd.Stu_Class,'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'),FIELD(smd.Stu_Section,'A','B','C','D','E')";
                            } else if ($report_by == "Individual") {
                                if ($_POST['Id_No']) {
                                    $id = $_POST['Id_No'];
                                    echo "<script>document.getElementById('id_no').value = '" . $id . "';</script>";
                                    $query1 .= " WHERE smd.Id_No = '$id'";
                                } else {
                                    echo "<script>alert('Please Enter Id_No');</script>";
                                    exit;
                                }
                            }

                            $query1 = mysqli_query($link, $query1);
                            if (mysqli_num_rows($query1) == 0) {
                                if ($report_by == "Class_Wise") {
                                    echo "<script>alert('Invalid Class or Section!');</script>";
                                } else if ($report_by == "Individual") {
                                    echo "<script>alert('Student Not Found!');</script>";
                                }
                            } else {
                                $i = 1;
                                while ($row1 = mysqli_fetch_assoc($query1)) {
                                    if ($report_by == "Individual" && str_contains(strtolower($row1['Stu_Class']), 'others') || str_contains(strtolower($row1['Stu_Class']), 'drop')) {
                                        echo "<script>alert('Student Passedout or Dropped!');</script>";
                                        exit;
                                    }
                                    echo '
                                    <tr>
                                        <td>' . $i . '</td>
                                        <td>' . $row1['Id_No'] . '</td>
                                        <td>' . $row1['First_Name'] . '</td>
                                        <td style="white-space:nowrap;">' . $row1['Stu_Class'] . ' ' . $row1['Stu_Section'] . '</td>';
                                    foreach ($categories as $cat) {
                                        foreach ($cols as $col) {
                                            if ($col != "Present") {
                                                echo '
                                                    <td>' . $row1[$col . '_' . $cat] . '</td>
                                                ';
                                            } else {
                                                echo '
                                                    <td>' . $row1[$cat] . '</td>
                                                ';
                                            }
                                        }
                                    }
                                    echo '</tr>
                                    ';
                                    $i++;
                                }
                            }
                        } else {
                            echo "<script>alert('No Column Selected!')</script>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </form>
    <!-- Missing List Modal -->
    <div class="modal fade" id="missinglist" tabindex="-1" aria-labelledby="missingListLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="missingListLabel">Missing Students List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-table-container">
                    <div class="row justify-content-center mb-2">
                        <div class="col-lg-1">
                            <button class="btn btn-success" onclick="printModalDiv();return false;">Print</button>
                        </div>
                    </div>
                    <table class="table table-striped" border="1">
                        <thead>
                            <th class="border border-dark">S No</th>
                            <th class="border border-dark">Id No.</th>
                            <th class="border border-dark">Name</th>
                            <th class="border border-dark">Class</th>
                        </thead>
                        <tbody id="modal-body-table">

                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['save'])) {
        if (!can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to save this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        echo "
        <script>
            document.getElementById('save').checked = true;
            document.querySelector('.report-row').hidden = 'hidden';
            document.querySelector('.report-col-row').hidden = 'hidden';
            document.querySelector('.report-btn-row').hidden = 'hidden';
            document.querySelector('.save-row').hidden = '';
            document.querySelector('.save-btn-row').hidden = '';
        </script>
        ";
        if ($_POST['Quarter']) {
            $quarter = $_POST['Quarter'];
            echo "<script>document.getElementById('quarter').value = '" . $quarter . "';</script>";

            // Arrays
            $ids = [];

            // Queries
            // Step 1: Fetch all students
            $query1 = mysqli_query($link, "SELECT Id_No, First_Name, Stu_Class, Stu_Section FROM `student_master_data` WHERE Stu_Class LIKE '% CLASS' OR Stu_Class IN ('PreKG','LKG','UKG') ORDER BY FIELD(Stu_Class,'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'),FIELD(Stu_Section,'A','B','C','D','E')");

            $ids = [];
            while ($row1 = mysqli_fetch_assoc($query1)) {
                $ids[$row1['Id_No']] = [$row1['First_Name'], $row1['Stu_Class'] . ' ' . $row1['Stu_Section']];
            }

            // Step 2: Fetch all Id_No from student_performance
            $presentQuery = mysqli_query($link, "SELECT smd.Id_No, smd.First_Name, smd.Stu_Class, smd.Stu_Section, sp.Reading, sp.Writing, sp.Learning, sp.Handwriting, sp.Response, sp.Overall, sp.Grade, CASE WHEN sp.Id_No IS NULL THEN 'Missing' ELSE 'Present' END AS Status FROM student_master_data smd LEFT JOIN student_performance sp ON smd.Id_No = sp.Id_No WHERE smd.Stu_Class LIKE '% CLASS' OR smd.Stu_Class IN ('PreKG','LKG','UKG') ORDER BY FIELD(smd.Stu_Class, 'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'), FIELD(smd.Stu_Section, 'A','B','C','D','E')");


            $presentIds = [];
            $missingIds = [];
            while ($row2 = mysqli_fetch_assoc($presentQuery)) {
                if ($row2['Status'] == "Present") {
                    $presentIds[$row2['Id_No']] = [$row2['Reading'], $row2['Writing'], $row2['Learning'], $row2['Handwriting'], $row2['Response'], $row2['Overall'], $row2['Grade']];
                } else if ($row2['Status'] == "Missing") {
                    $missingIds[] = $row2['Id_No'];
                }
            }

            // Step 4: Handle
            if (empty($missingIds)) {
                // All students have entries in student_performance
                $categories = ['Reading', 'Writing', 'Learning', 'Handwriting', 'Response', 'Overall', 'Grade'];
                foreach (array_keys($ids) as $id) {
                    if (!in_array($id, array_keys($presentIds))) {
                        continue;
                    }
                    $check_query = mysqli_query($link, "SELECT * FROM `stu_performance_master` WHERE Id_No = '$id'");
                    if (mysqli_num_rows($check_query) == 0) {
                        $columns = "Id_No";
                        $values = "'$id'";

                        foreach ($categories as $cat) {
                            $columns .= ", {$quarter}_$cat";
                            if ($cat != "Grade") {
                                $values .= ", '" . $presentIds[$id][array_search($cat, $categories)] . "'"; // or use '0', 'NA', etc. depending on data format
                            } else {
                                $values .= ", " . $presentIds[$id][array_search($cat, $categories)]; // or use '0', 'NA', etc. depending on data format
                            }
                        }

                        // Optionally add timestamp column
                        $columns .= ", {$quarter}_Timestamp";
                        $values .= ", CURRENT_TIMESTAMP"; // or use NULL if no default

                        // Final query
                        $query2 = "INSERT INTO `stu_performance_master` ($columns) VALUES ($values)";
                        mysqli_query($link, $query2);
                    } else {
                        $setClause = "";

                        foreach ($categories as $cat) {
                            $column = "{$quarter}_$cat";
                            $value = $presentIds[$id][array_search($cat, $categories)];
                            if ($cat != "Grade") {
                                $setClause .= "$column = '$value', ";
                            } else {
                                $setClause .= "$column = $value, ";
                            }
                        }

                        // Add/update timestamp
                        $setClause .= "{$quarter}_Timestamp = CURRENT_TIMESTAMP";

                        // Final query
                        $query2 = "UPDATE `stu_performance_master` SET $setClause WHERE Id_No = '$id'";
                        mysqli_query($link, $query2);
                    }
                }
                echo "<script>alert('Data Saved Successfully for " . str_replace('Q', 'Quarter ', $quarter) . "!');</script>";
            } else {
                // Some students are missing
                $body_text = "";
                $i = 1;
                foreach ($missingIds as $missingid) {
                    $body_text .= "
                                            <tr>
                                                <td>" . $i . "</td>
                                                <td>" . $missingid . "</td>
                                                <td>" . $ids[$missingid][0] . "</td>
                                                <td style='white-space:nowrap;'>" . $ids[$missingid][1] . "</td>
                                            </tr>
                                            ";
                    $i++;
                }
                echo "
                <script>
                    $('#modal-body-table').html(" . json_encode($body_text) . ");
                    const modal = new bootstrap.Modal(document.getElementById('missinglist'));
                    modal.show();
                </script>
                ";
            }
        } else {
            echo "<script>alert('Please Select Quarter!');</script>";
        }
    }
    ?>
    <?php
    if (isset($_POST['delete'])) {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to delete this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        echo "
        <script>
            document.getElementById('save').checked = true;
            document.querySelector('.report-row').hidden = 'hidden';
            document.querySelector('.report-col-row').hidden = 'hidden';
            document.querySelector('.report-btn-row').hidden = 'hidden';
            document.querySelector('.save-row').hidden = '';
            document.querySelector('.save-btn-row').hidden = '';
        </script>
        ";
        if (mysqli_query($link, "TRUNCATE TABLE `student_performance`")) {
            echo "<script>alert('Present Data Deleted Successfully!');</script>";
        } else {
            echo "<script>alert('Present Data Deletion Failed!');</script>";
        }
    }
    ?>
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

    <!-- Change labels -->
    <script type="text/javascript">
        let report_row = document.querySelector('.report-row');
        let id_row = document.querySelector('.id-row');
        let save_row = document.querySelector('.save-row');
        let report_btn_row = document.querySelector('.report-btn-row');
        let save_btn_row = document.querySelector('.save-btn-row');
        let report_col_row = document.querySelector('.report-col-row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    if (save.checked) {
                        if (!report_row.hidden) {
                            report_row.hidden = 'hidden';
                        }
                        if (!id_row.hidden) {
                            id_row.hidden = 'hidden';
                        }
                        if (save_row.hidden) {
                            save_row.hidden = '';
                        }
                        if (!report_btn_row.hidden) {
                            report_btn_row.hidden = 'hidden';
                        }
                        if (save_btn_row.hidden) {
                            save_btn_row.hidden = '';
                        }
                        if (!report_col_row.hidden) {
                            report_col_row.hidden = 'hidden';
                        }
                    } else {
                        if (report_row.hidden) {
                            report_row.hidden = '';
                        }
                        if (!id_row.hidden) {
                            id_row.hidden = 'hidden';
                        }
                        if (!save_row.hidden) {
                            save_row.hidden = 'hidden';
                        }
                        if (report_btn_row.hidden) {
                            report_btn_row.hidden = '';
                        }
                        if (!save_btn_row.hidden) {
                            save_btn_row.hidden = 'hidden';
                        }
                        if (report_col_row.hidden) {
                            report_col_row.hidden = '';
                        }
                    }

                    break;
                case 'individual':
                    if (save.checked) {
                        if (!report_row.hidden) {
                            report_row.hidden = 'hidden';
                        }
                        if (!id_row.hidden) {
                            id_row.hidden = 'hidden';
                        }
                        if (save_row.hidden) {
                            save_row.hidden = '';
                        }
                        if (!report_btn_row.hidden) {
                            report_btn_row.hidden = 'hidden';
                        }
                        if (save_btn_row.hidden) {
                            save_btn_row.hidden = '';
                        }
                        if (!report_col_row.hidden) {
                            report_col_row.hidden = 'hidden';
                        }
                    } else {
                        if (!report_row.hidden) {
                            report_row.hidden = 'hidden';
                        }
                        if (id_row.hidden) {
                            id_row.hidden = '';
                        }
                        if (!save_row.hidden) {
                            save_row.hidden = 'hidden';
                        }
                        if (report_btn_row.hidden) {
                            report_btn_row.hidden = '';
                        }
                        if (!save_btn_row.hidden) {
                            save_btn_row.hidden = 'hidden';
                        }
                        if (report_col_row.hidden) {
                            report_col_row.hidden = '';
                        }
                    }
                    break;
                case 'report':
                    if (individual.checked) {
                        if (!report_row.hidden) {
                            report_row.hidden = 'hidden';
                        }
                        if (id_row.hidden) {
                            id_row.hidden = '';
                        }
                    } else {
                        if (report_row.hidden) {
                            report_row.hidden = '';
                        }
                        if (!id_row.hidden) {
                            id_row.hidden = 'hidden';
                        }
                    }
                    if (!save_row.hidden) {
                        save_row.hidden = 'hidden';
                    }
                    if (report_btn_row.hidden) {
                        report_btn_row.hidden = '';
                    }
                    if (!save_btn_row.hidden) {
                        save_btn_row.hidden = 'hidden';
                    }
                    if (report_col_row.hidden) {
                        report_col_row.hidden = '';
                    }
                    break;
                case 'save':
                    if (!report_row.hidden) {
                        report_row.hidden = 'hidden';
                    }
                    if (!id_row.hidden) {
                        id_row.hidden = 'hidden';
                    }
                    if (!report_btn_row.hidden) {
                        report_btn_row.hidden = 'hidden';
                    }
                    if (!report_col_row.hidden) {
                        report_col_row.hidden = 'hidden';
                    }
                    if (save_row.hidden) {
                        save_row.hidden = '';
                    }
                    if (save_btn_row.hidden) {
                        save_btn_row.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = 'Quarterly Performance Report'

            // Select table
            var tableSelect = document.getElementById('table-container');

            // Use SheetJS to export the table as an Excel file
            var wb = XLSX.utils.table_to_book(tableSelect, {
                sheet: 'Sheet1'
            });

            // Specify filename
            filename = filename ? filename + '.xlsx' : 'excel_data.xlsx';

            // Download the file
            XLSX.writeFile(wb, filename);
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

    <!-- Print Modal Table -->
    <script type="text/javascript">
        function printModalDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.modal-table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>