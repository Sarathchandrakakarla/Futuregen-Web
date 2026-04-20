<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 88);

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
        max-width: 1100px;
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
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-3">
                <div class="col-lg-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Report_Type" id="class_wise" checked value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Report_Type" id="date_wise" value="Date_Wise">
                        <label class="form-check-label" for="date_wise">Date Wise</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-4" id="date_row" hidden>
            <div class="col-lg-2">Date Range: </div>
            <div class="col-lg-2">
                <input type="date" class="form-control" name="From_Date" id="from_date">
            </div>
            <div class="col-lg-2">
                <input type="date" class="form-control" name="To_Date" id="to_date">
            </div>
        </div>
        <div class="row justify-content-center mt-4" id="class_row">
            <div class="p-2 text-light col-lg-3 rounded">
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
            <div class="p-2 text-light col-lg-3 rounded">
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
            <div class="col-lg-4">
                <h3><b>Student App Login Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container" id="report_container" hidden>
        <div class="row justify-content-center mt-2">
            <div class="col-lg-3" style="font-weight: bold;">Logged In : <span id="logged_in"></span></div>
            <div class="col-lg-3" style="font-weight: bold;">Not Logged In:<span id="not_logged_in"></span></div>
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
                <td style="font-size:20px;color:red">Report Type:</td>
                <td id="type_txt_label" style="font-size:20px;"></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr style="padding: 5px;">
                    <th>S.No</th>
                    <th>Id No.</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Password</th>
                    <th>Mobile</th>
                    <th>Login Status</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                function getData()
                {
                    $result = [];
                    //Fetching logged in students
                    $command = 'scp -o StrictHostKeyChecking=no -i "victoryserverkp.pem" ec2-user@ec2-18-61-98-208.ap-south-2.compute.amazonaws.com:Victory-Server/activity.log activity.log 2>&1';
                    $output = shell_exec($command);
                    function parseLogFile()
                    {
                        // Path to your log file
                        $file_path = 'activity.log';

                        // Check if the file exists
                        if (!file_exists($file_path)) {
                            echo "File not found.\n";
                            return [];
                        }

                        // Open the file for reading
                        $file = fopen($file_path, 'r');

                        // Check if the file opened successfully
                        if (!$file) {
                            echo "Error opening the file.\n";
                            return [];
                        }

                        // Array to store the parsed and filtered logs
                        $filteredLogs = [];

                        // Read the file line by line
                        while (($line = fgets($file)) !== false) {
                            $line = trim($line);  // Remove unnecessary spaces and newlines

                            // Skip empty lines
                            if ($line === "") {
                                continue;
                            }

                            // Try to parse the line as JSON
                            $log = json_decode($line, true); // true for associative array

                            // If parsing is successful, check the filter conditions
                            if ($log !== null) {
                                // Check if the log matches the filtering criteria
                                if (
                                    isset($log['message']['user']) && $log['message']['user'] === 'Student' &&
                                    isset($log['message']['task']) && $log['message']['task'] === 'logged in'
                                ) {
                                    // Add the log to the filtered logs array
                                    $filteredLogs[] = $log;
                                }
                            } else {
                                // If JSON parsing fails, log an error (optional)
                                echo "Error parsing JSON: $line\n";
                            }
                        }

                        // Close the file after processing
                        fclose($file);

                        // Return the filtered logs
                        return $filteredLogs;
                    }

                    $decoded_response = parseLogFile();

                    foreach ($decoded_response as $student) {
                        if ($student['message']['username'] != "VHST02674" && $student['message']['username'] != "APPSTUDENT")
                            if (!in_array($student['message']['username'], array_keys($result))) {
                                $result[$student['message']['username']] = $student['timestamp'];
                            }
                    }
                    return $result;
                }
                function checkFirstLoginDate($loggedin_students, $from_date = null, $to_date = null)
                {
                    // Convert the 'from' date to a Unix timestamp
                    $from_timestamp = $from_date ? strtotime($from_date . " 00:00:00") : null;
                    $to_timestamp = $to_date ? strtotime($to_date . " 23:59:59") : null; // Set 'to' date as end of day

                    // Filter the students based on the date range
                    $filtered_students = array_filter($loggedin_students, function ($timestamp) use ($from_timestamp, $to_timestamp) {
                        $student_timestamp = strtotime($timestamp);
                        if ($from_timestamp && $to_timestamp) {
                            return $student_timestamp >= $from_timestamp && $student_timestamp <= $to_timestamp;
                        } else if ($from_timestamp && !$to_timestamp) {
                            return $student_timestamp >= $from_timestamp;
                        } else if (!$from_timestamp && $to_timestamp) {
                            return $student_timestamp <= $to_timestamp;
                        }
                    });

                    return $filtered_students;
                }
                function format_date($date)
                {
                    $dob = explode('-', $date);
                    $temp = $dob[0];
                    $dob[0] = $dob[2];
                    $dob[2] = $temp;

                    $date = implode('-', $dob);
                    return $date;
                }

                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $report_type = $_POST['Report_Type'];
                    $report = ["Not Logged In" => 0, "Logged In" => 0];
                    echo "<script>report_container.hidden = '';</script>";
                    $classes = ['PreKG', 'LKG', 'UKG'];
                    for ($i = 1; $i <= 10; $i++) {
                        $classes[] = $i . " CLASS";
                    }
                    if ($report_type == "Class_Wise") {
                        echo "
                        <script>
                            class_wise.checked = true;
                            class_row.hidden = '';
                            date_row.hidden = 'hidden'
                        </script>
                        ";
                        $loggedin_students = getData();
                        if (!isset($_POST['Class']) && !isset($_POST['Section'])) {
                            echo '<script>type_txt_label.innerHTML = "All Classes";</script>';
                            $query1 = mysqli_query($link, "SELECT smd.Id_No,First_Name,Mobile,Stu_Class AS Class,Stu_Section AS Section,Mobile,Stu_Password FROM `student_master_data` smd JOIN `student` s ON smd.Id_No = s.Id_No WHERE Stu_Class IN ('" . implode("','", $classes) . "') ORDER BY FIELD(Stu_Class,'" . implode("','", $classes) . "'), FIELD(Stu_Section,'A','B','C','D','E')");
                        } else if (isset($_POST['Class']) && !isset($_POST['Section'])) {
                            $class = $_POST['Class'];
                            echo "
                            <script>
                                document.getElementById('class').value = '" . $class . "';
                                type_txt_label.innerHTML = '" . $class . "';
                            </script>";
                            $query1 = mysqli_query($link, "SELECT smd.Id_No,First_Name,Mobile,Stu_Class AS Class,Stu_Section AS Section,Mobile,Stu_Password FROM `student_master_data` smd JOIN `student` s ON smd.Id_No = s.Id_No WHERE Stu_Class = '$class' ORDER BY Stu_Section");
                        } else if (isset($_POST['Class']) && isset($_POST['Section'])) {
                            $class = $_POST['Class'];
                            $section = $_POST['Section'];
                            echo "
                            <script>
                                document.getElementById('class').value = '" . $class . "';
                                document.getElementById('sec').value = '" . $section . "';
                                type_txt_label.innerHTML = '" . $class . " " . $section . "';
                            </script>";
                            $query1 = mysqli_query($link, "SELECT smd.Id_No,First_Name,Mobile,Stu_Class AS Class,Stu_Section AS Section,Mobile,Stu_Password FROM `student_master_data` smd JOIN `student` s ON smd.Id_No = s.Id_No WHERE Stu_Class = '$class' AND Stu_Section = '$section' ORDER BY Stu_Section");
                        }
                    } else if ($report_type == "Date_Wise") {
                        echo "
                        <script>
                            date_wise.checked = true;
                            class_row.hidden = 'hidden';
                            date_row.hidden = ''
                        </script>
                        ";
                        if (!$_POST['From_Date'] && !$_POST['To_Date']) {
                            $loggedin_students = getData();
                        } else if ($_POST['From_Date'] && !$_POST['To_Date']) {
                            $from_date = $_POST['From_Date'];
                            echo "
                            <script>
                                from_date.value = '" . $from_date . "';
                                to_date.value = '" . date('Y-m-d') . "';
                                type_txt_label.innerHTML = '" . format_date($from_date) . " - " . date('d-m-Y') . "';
                            </script>
                            ";
                            $loggedin_students = getData();
                            $loggedin_students = checkFirstLoginDate($loggedin_students, $from_date);
                        } else if (!$_POST['From_Date'] && $_POST['To_Date']) {
                            $to_date = $_POST['To_Date'];
                            echo "
                            <script>
                                to_date.value = '" . $to_date . "';
                            </script>
                            ";
                            $loggedin_students = getData();
                            $loggedin_students = checkFirstLoginDate($loggedin_students, null, $to_date);
                        } else if ($_POST['From_Date'] && $_POST['To_Date']) {
                            $from_date = $_POST['From_Date'];
                            $to_date = $_POST['To_Date'];
                            echo "
                            <script>
                                from_date.value = '" . $from_date . "';
                                to_date.value = '" . $to_date . "';
                                type_txt_label.innerHTML = '" . format_date($from_date) . " - " . format_date($to_date) . "';
                            </script>
                            ";
                            $loggedin_students = getData();
                            $loggedin_students = checkFirstLoginDate($loggedin_students, $from_date, $to_date);
                        }
                        $query1 = mysqli_query($link, "SELECT smd.Id_No,First_Name,Stu_Class AS Class,Stu_Section AS Section,Mobile,Stu_Password FROM `student_master_data` smd JOIN `student` s ON smd.Id_No = s.Id_No WHERE smd.Id_No IN ('" . implode("','", array_keys($loggedin_students)) . "') ORDER BY FIELD(Stu_Class,'" . implode("','", $classes) . "'), FIELD(Stu_Section,'A','B','C','D','E')");
                        if (count($loggedin_students) > 0) {
                            //To Get Newest and Oldest Dates of Logged In Students
                            $dateObjects = array_map(function ($dateString) {
                                return DateTime::createFromFormat('m/d/Y, g:i:s A', $dateString);
                            }, array_values($loggedin_students));
                            $oldestDate = min($dateObjects)->format('Y-m-d');
                            $newestDate = max($dateObjects)->format('Y-m-d');
                            if (!$_POST['From_Date'] && !$_POST['To_Date']) {
                                echo "
                                <script>
                                    from_date.value = '" . $oldestDate . "';
                                    to_date.value = '" . $newestDate . "';
                                    type_txt_label.innerHTML = '" . format_date($oldestDate) . " - " . format_date($newestDate) . "';
                                </script>
                            ";
                            } else if (!$_POST['From_Date'] && $_POST['To_Date']) {
                                echo "
                                <script>
                                    from_date.value = '" . $oldestDate . "';
                                    type_txt_label.innerHTML = '" . format_date($oldestDate) . " - " . format_date($to_date) . "';
                                </script>
                            ";
                            }
                        }
                    }

                    $i = 1;
                    while ($row1 = mysqli_fetch_assoc($query1)) {
                        $mobile = $row1['Mobile'];
                        $mobile = trim(explode(',', $mobile)[0]);
                        $mobile = trim(explode(' ', $mobile)[0]);
                        echo '
                                <tr>
                                    <td>' . $i . '</td>
                                    <td>' . $row1['Id_No'] . '</td>
                                    <td>' . $row1['First_Name'] . '</td>
                                    <td>' . $row1['Class'] . ' ' . $row1['Section'] . '</td>
                                    <td>' . $row1['Stu_Password'] . '</td>
                                    <td>' . $mobile . '</td>
                                    <td>';
                        if (in_array(strtoupper(trim($row1['Id_No'])), array_map('trim', array_keys($loggedin_students)))) {
                            echo 'Logged In';
                            $report['Logged In']++;
                        } else {
                            echo 'Not Logged In';
                            $report['Not Logged In']++;
                        }

                        echo '</td>
                                </tr>
                                ';
                        $i++;
                    }
                    echo '
                        <script>
                            logged_in.innerHTML = ' . $report['Logged In'] . ';
                            not_logged_in.innerHTML = ' . $report['Not Logged In'] . ';
                        </script>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        let date_row = document.getElementById('date_row');
        let cls_row = document.getElementById('class_row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    if (cls_row.hidden) {
                        cls_row.hidden = '';
                    }
                    if (!date_row.hidden) {
                        date_row.hidden = 'hidden';
                    }
                    break;
                case 'date_wise':
                    if (!cls_row.hidden) {
                        cls_row.hidden = 'hidden';
                    }
                    if (date_row.hidden) {
                        date_row.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = type_txt_label.innerHTML;
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
            // Select all elements in the "Enable/Disable" column
            let noPrintElements = document.querySelectorAll(".no-print");

            // Hide them before printing
            noPrintElements.forEach(el => el.style.display = "none");

            // Get the iframe document and insert the printable content
            let printFrame = window.frames["print_frame"];
            let printContent = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            printContent += "<p style='font-size:20px;'><b><?php if (isset($report_type)) {
                                                                echo str_replace("_Wise", '', $report_type);
                                                            } ?>: </b>";
            printContent += type_txt_label.innerHTML + "</p>";
            printContent += document.querySelector('.table-container').innerHTML;

            // Write the content into the iframe
            printFrame.document.body.innerHTML = printContent;
            printFrame.window.focus();
            printFrame.window.print();

            // Restore the "Enable/Disable" column after printing
            setTimeout(() => {
                noPrintElements.forEach(el => el.style.display = "");
            }, 500);
        }
    </script>
</body>

</html>