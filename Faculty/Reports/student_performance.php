<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 110);

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
        max-width: 700px;
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

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
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
                    <button class="btn btn-warning">Clear</button>
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
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update this report"
                        <?php } ?>>
                        <button class="btn btn-secondary edit" onclick="return false;" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>><span id="edit-text">Edit</span> <i class="bx bx-edit edit-icon"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-6">
                <h3><b>Class Wise Student Performance Entry</b></h3>
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
                        <th class="criteria-row text-center border" hidden>Reading</th>
                        <th class="criteria-row text-center border" hidden>Writing</th>
                        <th class="criteria-row text-center border" hidden>Learning</th>
                        <th class="criteria-row text-center border" hidden>Hand Writing</th>
                        <th class="criteria-row text-center border" hidden>Response in Class</th>
                        <th class="criteria-row text-center border" hidden>Overall Performance</th>
                        <th class="criteria-row text-center border" hidden>Grade Points</th>
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
                                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                                echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                                /* $sql = "SELECT smd.*,sp.Reading,sp.Writing,sp.Learning,sp.Handwriting,sp.Response,sp.Overall,sp.Grade FROM `student_master_data` smd LEFT JOIN `student_performance` sp ON smd.Id_No = sp.Id_No WHERE Stu_Class = '$class' AND Stu_Section = '$section' ORDER BY smd.Id_No"; */
                                $sql = "SELECT smd.Id_No, smd.First_Name, smd.Stu_Class, smd.Stu_Section, COALESCE(spm.Q3_Reading, spm.Q2_Reading, spm.Q1_Reading, sp.Reading) AS Reading, COALESCE(spm.Q3_Writing, spm.Q2_Writing, spm.Q1_Writing, sp.Writing) AS Writing, COALESCE(spm.Q3_Learning, spm.Q2_Learning, spm.Q1_Learning, sp.Learning) AS Learning, COALESCE(spm.Q3_Handwriting, spm.Q2_Handwriting, spm.Q1_Handwriting, sp.Handwriting) AS Handwriting, COALESCE(spm.Q3_Response, spm.Q2_Response, spm.Q1_Response, sp.Response) AS Response, COALESCE(spm.Q3_Overall, spm.Q2_Overall, spm.Q1_Overall, sp.Overall) AS Overall, COALESCE(spm.Q3_Grade, spm.Q2_Grade, spm.Q1_Grade, sp.Grade) AS Grade FROM student_master_data smd LEFT JOIN stu_performance_master spm ON smd.Id_No = spm.Id_No LEFT JOIN student_performance sp ON smd.Id_No = sp.Id_No WHERE smd.Stu_Class = '$class' AND smd.Stu_Section = '$section' ORDER BY smd.Id_No";
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
                                    $criteria = ['reading', 'writing', 'learning', 'handwriting', 'response', 'overall'];
                                    echo '
                                    <tr class="bg-' . $background . ' text-' . $text . '">
                                        <td class="border" style="padding:5px;">' . $i . '</td>
                                        <td class="border" style="padding:5px;">' . $row['Id_No'] . '</td>
                                        <td class="border" style="padding-left:5px;">' . $row['First_Name'] . '</td>';
                                    foreach ($criteria as $cat) {
                                        echo '
                                            <!-- ' . $cat . ' -->
                                        <td class="criteria-row border" hidden>
                                            <div style="display:flex; gap:10px;">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="' . $cat . '[' . $i - 1 . ']" id="' . $cat . '-excellent[' . $i - 1 . ']" value="Excellent" ' . (($row[ucfirst($cat)] == "Excellent") ? 'checked' : '') . ' ' . (!can('create', MENU_ID) ? 'disabled' : '') . '>
                                                    <label class="form-check-label" for="' . $cat . '-excellent[' . $i - 1 . ']">Excellent</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="' . $cat . '[' . $i - 1 . ']" id="' . $cat . '-good[' . $i - 1 . ']" value="Good" ' . (($row[ucfirst($cat)] == "Good") ? 'checked' : '') . ' ' . (!can('create', MENU_ID) ? 'disabled' : '') . '>
                                                    <label class="form-check-label" for="' . $cat . '-good[' . $i - 1 . ']">Good</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="' . $cat . '[' . $i - 1 . ']" id="' . $cat . '-bad[' . $i - 1 . ']" value="Bad" ' . (($row[ucfirst($cat)] == "Bad") ? 'checked' : '') . ' ' . (!can('create', MENU_ID) ? 'disabled' : '') . '>
                                                    <label class="form-check-label" for="' . $cat . '-bad[' . $i - 1 . ']">Bad</label>
                                                </div>
                                            </div>
                                        </td>
                                            ';
                                    }
                                    echo '
                                        <!-- Grade -->
                                        <td class="criteria-row border" hidden>
                                            <div style="display:flex; gap:10px;align-items:center;">
                                                <input class="form-control" type="number" oninput="validateGrade(this)" style="width:50px;" name="grade[' . $i - 1 . ']" id="grade[' . $i . ']" value="' . $row['Grade'] . '"/>
                                                <span>/10</span>
                                            </div>
                                        </td>
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
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="add"
                            onclick="return confirm('Confirm to Update Performance of <?php echo $class . ' ' . $section; ?>?') && validatePerformance();" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>
                            Update Performance
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <?php
    if (isset($_POST['add'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $class = $_SESSION['Class'];
        $section = $_SESSION['Section'];
        echo "<script>
                document.getElementById('class').value = '" . $class . "';
                document.getElementById('sec').value = '" . $section . "';
            </script>";
        $reading = $_POST['reading'];
        $writing = $_POST['writing'];
        $learning = $_POST['learning'];
        $handwriting = $_POST['handwriting'];
        $response = $_POST['response'];
        $overall = $_POST['overall'];
        $grade = $_POST['grade'];

        //Arrays
        $ids = array();
        $categories = [
            'Reading'      => $reading,
            'Writing'     => $writing,
            'Learning'     => $learning,
            'Handwriting' => $handwriting,
            'Response'  => $response,
            'Overall'   => $overall,
            'Grade'     => $grade,
        ];
        $final = [];
        $perf_status = true;

        //Queries
        $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section' ORDER BY Id_No");
        if (mysqli_num_rows($query1) == 0) {
            echo "<script>alert('Invalid Class or Section!');</script>";
            return;
        } else {
            while ($row1 = mysqli_fetch_assoc($query1)) {
                array_push($ids, $row1['Id_No']);
            }
        }
        foreach ($ids as $index => $id) {
            foreach ($categories as $key => $values) {
                $final[$id][$key] = $values[$index] ?? null; // safely fetch by index
            }
        }
        foreach ($final as $id => $performance) {
            $values = array_values($performance);
            $filledCount = count(array_filter($values, fn($v) => $v !== null));
            $totalCount = count($values);

            if ($filledCount === 0) {
                // ✅ Case 1: All values are null
                continue; // or handle accordingly
            } else if ($filledCount === $totalCount) {
                // ✅ Case 2: All values are filled (not null)
                $check_query = mysqli_query($link, "SELECT * FROM `student_performance` WHERE Id_No = '$id'");
                if (mysqli_num_rows($check_query) == 0) {
                    $query2 = "INSERT INTO `student_performance`(Id_No,Reading,Writing,Learning,Handwriting,Response,Overall,Grade) VALUES('$id','" . $performance['Reading'] . "','" . $performance['Writing'] . "','" . $performance['Learning'] . "','" . $performance['Handwriting'] . "','" . $performance['Response'] . "','" . $performance['Overall'] . "'," . $performance['Grade'] . ")";
                } else {
                    $query2 = "UPDATE `student_performance` SET Reading = '" . $performance['Reading'] . "',Writing = '" . $performance['Writing'] . "',Learning = '" . $performance['Learning'] . "',Handwriting = '" . $performance['Handwriting'] . "',Response = '" . $performance['Response'] . "',Overall = '" . $performance['Overall'] . "',Grade = " . $performance['Grade'] . " WHERE Id_No = '$id'";
                }
                if (!mysqli_query($link, $query2)) {
                    echo "<script>alert('Performance Updation Failed for " . $id . "');</script>";
                    exit;
                }
            }
        }
        echo "<script>alert('Performance Updated Successfully!');</script>";
    }
    ?>


    <!-- Scripts -->

    <!-- Change labels -->
    <script>
        $(document).ready(function() {
            $('.edit').on('click', function() {
                // Toggle all cells with class "criteria-row"
                document.querySelectorAll('.criteria-row').forEach((el) => {
                    el.hidden = !el.hidden;
                });

                // Toggle Edit/Close button text
                const $editText = $('#edit-text');
                $editText.text($editText.text().trim() === 'Edit' ? 'Close' : 'Edit');
                $('.table-container').css('max-width', $editText.text().trim() === 'Edit' ? '700px' : '1200px')

                // Toggle icon class
                $('#edit-icon').toggleClass('bg-edit bx-window-close');
            });
        });
    </script>

    <!-- To handle down arrow key navigation -->
    <script>
        document.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                const active = document.activeElement;
                if (active.classList.contains("form-control")) {
                    event.preventDefault();

                    // Get the current input's closest table row
                    const currentRow = active.closest("tr");
                    if (!currentRow) return;

                    // Find the index of the input inside the row (in case there are multiple inputs per row)
                    const inputsInRow = currentRow.querySelectorAll(".form-control");
                    const inputIndex = Array.from(inputsInRow).indexOf(active);

                    // Get the next row
                    const nextRow = currentRow.nextElementSibling;
                    if (nextRow) {
                        const inputsInNextRow = nextRow.querySelectorAll(".form-control");
                        if (inputsInNextRow[inputIndex]) {
                            inputsInNextRow[inputIndex].focus();
                            inputsInNextRow[inputIndex].select();
                        }
                    }
                }
            }
        });
    </script>

    <!-- Validate Grade -->
    <script>
        function validateGrade(ele) {
            event.preventDefault()
            let val = ele.value;
            if (val && (val < 1 || val > 10)) {
                $(ele).css('border-color', 'red');
                alert('Grade should be 1 to 10');
                $(ele).val()
            }
        }
    </script>

    <!-- Validate Radio Values -->
    <script>
        function validatePerformance() {
            const rows = document.querySelectorAll('#tbody tr');
            const criteriaCount = 7; // Reading, Writing, ..., Grade
            let isValid = true;
            let firstInvalidRow = null;
            let hasPartialFill = false;
            let hasInvalidGrade = false;

            rows.forEach((row, index) => {
                row.style.outline = ''; // Reset previous highlights

                const radios = row.querySelectorAll('input[type="radio"]:checked');
                const gradeInput = row.querySelector('input[type="number"]');
                const gradeVal = gradeInput?.value?.trim();

                let filled = radios.length;
                if (gradeVal) filled++;

                // ✅ Validate partial fill
                if (filled > 0 && filled < criteriaCount) {
                    isValid = false;
                    hasPartialFill = true;
                    row.style.outline = '2px solid red';
                    if (!firstInvalidRow) firstInvalidRow = row;
                }

                // ✅ Validate grade value (if present)
                if (gradeVal) {
                    const num = Number(gradeVal);
                    if (isNaN(num) || num < 1 || num > 10) {
                        isValid = true;
                        hasInvalidGrade = true;
                        row.style.outline = '2px solid orange';
                        if (!firstInvalidRow) firstInvalidRow = row;
                    }
                }
            });

            if (hasPartialFill) {
                alert("Please fill all partially filled rows before submitting.");
            } else if (hasInvalidGrade) {
                alert("Grade values must be between 1 and 10.");
            }

            if ((!isValid || hasInvalidGrade) && firstInvalidRow) {
                firstInvalidRow.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }

            return !(hasPartialFill || hasInvalidGrade);
        }
    </script>

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