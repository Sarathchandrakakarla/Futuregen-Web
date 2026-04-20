<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 115);

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
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    .edit {
        cursor: pointer;
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
            <div class="row justify-content-center mt-5">
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
        </div>
        <div class="container">
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
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-6">
                <h3><b>Class Wise Student Performance Report</b></h3>
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
                    <tr>
                        <th class="border" style="padding:5px;">S.No</th>
                        <th class="border" style="padding:5px;">Id No.</th>
                        <th class="border">Name</th>
                        <th class="criteria-row text-center border">Reading</th>
                        <th class="criteria-row text-center border">Writing</th>
                        <th class="criteria-row text-center border">Learning</th>
                        <th class="criteria-row text-center border">Hand Writing</th>
                        <th class="criteria-row text-center border">Response in Class</th>
                        <th class="criteria-row text-center border">Overall Performance</th>
                        <th class="criteria-row text-center border">Grade Points</th>
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
                        if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            $_SESSION['Class'] = $class;
                            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                            if ($_POST['Section']) {
                                $section = $_POST['Section'];
                                $_SESSION['Section'] = $section;
                                echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                                /* $sql = "SELECT smd.*,sp.Reading,sp.Writing,sp.Learning,sp.Handwriting,sp.Response,sp.Overall,sp.Grade FROM `student_master_data` smd LEFT JOIN `student_performance` sp ON smd.Id_No = sp.Id_No WHERE Stu_Class = '$class' AND Stu_Section = '$section' ORDER BY smd.Id_No"; */
                                $sql = "SELECT smd.Id_No, smd.First_Name, smd.Stu_Class, smd.Stu_Section, COALESCE(spm.Q3_Reading, spm.Q2_Reading, spm.Q1_Reading, sp.Reading) AS Reading, COALESCE(spm.Q3_Writing, spm.Q2_Writing, spm.Q1_Writing, sp.Writing) AS Writing, COALESCE(spm.Q3_Learning, spm.Q2_Learning, spm.Q1_Learning, sp.Learning) AS Learning, COALESCE(spm.Q3_Handwriting, spm.Q2_Handwriting, spm.Q1_Handwriting, sp.Handwriting) AS Handwriting, COALESCE(spm.Q3_Response, spm.Q2_Response, spm.Q1_Response, sp.Response) AS Response, COALESCE(spm.Q3_Overall, spm.Q2_Overall, spm.Q1_Overall, sp.Overall) AS Overall, COALESCE(spm.Q3_Grade, spm.Q2_Grade, spm.Q1_Grade, sp.Grade) AS Grade FROM student_master_data smd LEFT JOIN stu_performance_master spm ON smd.Id_No = spm.Id_No LEFT JOIN student_performance sp ON smd.Id_No = sp.Id_No WHERE smd.Stu_Class = '$class' AND smd.Stu_Section = '$section' ORDER BY smd.Id_No;";
                                $result = mysqli_query($link, $sql);
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $background = "light";
                                    $text = "dark";
                                    if ($row['Overall'] == "Excellent") {
                                        $text = "light";
                                        $background = 'success';
                                    } else if ($row['Overall'] == "Good") {
                                        $text = "dark";
                                        $background = 'warning';
                                    } else if ($row['Overall'] == "Bad") {
                                        $text = "light";
                                        $background = 'danger';
                                    }
                                    $criteria = ['Reading', 'Writing', 'Learning', 'Handwriting', 'Response', 'Overall', 'Grade'];
                                    echo '
                                    <tr class="bg-' . $background . ' text-' . $text . '">
                                        <td class="border" style="padding:5px;">' . $i . '</td>
                                        <td class="border" style="padding:5px;">' . $row['Id_No'] . '</td>
                                        <td class="border" style="padding-left:5px;">' . $row['First_Name'] . '</td>';
                                    foreach ($criteria as $cat) {
                                        echo '
                                        <td class="criteria-row border" style="text-align:center;">' . $row[$cat] . '</td>
                                        ';
                                    }
                                    echo '
                                    </tr>';
                                    $i++;
                                }
                            } else {
                                echo "<script>alert('Please Select Section!')</script>";
                            }
                        } else {
                            echo "<script>alert('Please Select Class!')</script>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </form>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Export Table to Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script type="text/javascript">
        $('#export').on('click', function() {
            stuclass = '<?php echo $class; ?>';
            stusection = '<?php echo $section; ?>';
            filename = stuclass + stusection;

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
</body>

</html>