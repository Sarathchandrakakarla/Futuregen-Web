<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 82);

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

    .tooltip-wrapper {
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .disabled {
        opacity: 0.5;
        cursor: not-allowed;
        text-decoration: none;

    }
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="p-2 col-lg-4 rounded">
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
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to send SMS"
                        <?php } ?>>
                        <button class="btn btn-success" name="send" id="send" onclick="return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Send</button>
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
                <h3><b>Send SMS of Credentials</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead>
                <th>S.No</th>
                <th>Id No.</th>
                <th>Name</th>
                <th>Class</th>
                <th>Password</th>
                <th>SMS Link</th>
                <th>
                    <?php if (can('create', MENU_ID)) { ?>
                        <input type="checkbox"
                            class="form-check-input"
                            id="select_all"
                            onclick="toggle(this)">
                        <label for="select_all">Select All</label>

                    <?php } else { ?>
                        <span class="tooltip-wrapper"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="You don't have permission to select all and send SMS">

                            <input type="checkbox"
                                class="form-check-input disabled"
                                id="select_all"
                                disabled>

                            <label for="select_all">Select All</label>
                        </span>

                    <?php } ?>
                </th>
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
                        $flag = false;
                        if (!$_POST['Class'] && !$_POST['Section']) {
                            $mobile_sql = mysqli_query($link, "SELECT Id_No,Stu_Class,Stu_Section,Mobile FROM `student_master_data` WHERE Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS') AND Stu_Section IN ('A','B','C','D') ORDER BY FIELD(Stu_Class,'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'),Stu_Section");
                            $flag = true;
                        } else if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            echo "<script>document.getElementById('class').value='$class';</script>";
                            if ($_POST['Section']) {
                                $section = $_POST['Section'];
                                echo "<script>document.getElementById('sec').value='$section';</script>";
                                $mobile_sql = mysqli_query($link, "SELECT Id_No,Stu_Class,Stu_Section,Mobile FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                                $flag = true;
                            } else {
                                echo "<script>alert('Please Select Section!')</script>";
                            }
                        } else {
                            echo "<script>alert('Please Select Class!')</script>";
                        }
                        if ($flag) {
                            $mobiles = array();
                            $classes = array();
                            if (mysqli_num_rows($mobile_sql) > 0) {
                                while ($mobile_row = mysqli_fetch_assoc($mobile_sql)) {
                                    $mobiles[$mobile_row['Id_No']] = $mobile_row['Mobile'];
                                    $classes[$mobile_row['Id_No']] = $mobile_row['Stu_Class'] . ' ' . $mobile_row['Stu_Section'];
                                }
                            }
                            $sql = mysqli_query($link, "SELECT c.Id_No AS Id_No,c.Stu_Name AS Stu_Name,c.Stu_Password AS Password FROM student c JOIN student_master_data s ON s.Id_No = c.Id_No WHERE s.Id_No IN ('" . implode("','", array_keys($mobiles)) . "') ORDER BY FIELD(s.Stu_Class,'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'),FIELD(s.Stu_Section,'A','B','C','D','E')");
                            if (mysqli_num_rows($sql) == 0 && $class && $section) {
                                echo "<script>alert('Class or Section Not Available!')</script>";
                            } else {
                                $i = 1;
                                while ($row = mysqli_fetch_assoc($sql)) {
                                    if (str_contains($mobiles[$row['Id_No']], ',')) {
                                        $mobile = explode(',', $mobiles[$row['Id_No']], 2)[0];
                                    } else if (str_contains($mobiles[$row['Id_No']], ' ')) {
                                        $mobile = explode(' ', $mobiles[$row['Id_No']], 2)[0];
                                    } else {
                                        $mobile = $mobiles[$row['Id_No']];
                                    }
                                    $text = "Dear student,The credentials to login VICTORYSCHOOLS portal are as follows.Name :" . $row['Stu_Name'] . " ,Username:" . $row['Id_No'] . ",Password:" . $row['Password'] . ".Change pass word once you login.Principal,Victory schools,KDR";
                                    $text = urlencode($text);
                                    echo '<tr>
                                        <td style="padding:5px;">' . $i . '</td>
                                        <td style="padding:5px;">' . $row['Id_No'] . '</td>
                                        <td style="padding-left:5px;">' . $row['Stu_Name'] . '</td>';
                                    if ($class && $section) {
                                        echo '<td style="padding-left:5px;white-space:nowrap;">' . $class . ' ' . $section . '</td>';
                                    } else {
                                        echo '<td style="padding-left:5px;white-space:nowrap;">' . $classes[$row['Id_No']] . '</td>';
                                    }
                                    echo '<td style="padding-left:5px;padding-right:5px;">' . $row['Password'] . '</td>
                                        <td>';
                                    if (can('create', MENU_ID)) {
                                        echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobile . '&message=' . $text . '&route=TRANS&TemplateID=1707169202054795768&format=JSON" class="sms_link">' . $mobile . '</a>';
                                    } else {
                                        echo '<a href="javascript:void(0)"
                                                    class="text-secondary disabled"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="You don\'t have permission to send SMS">
                                                    ' . $mobile . '
                                                </a>';
                                    }
                                    echo '</td>
                                            <td>';
                                    if (can("create", MENU_ID)) {
                                        echo '<input type="checkbox" class="form-check-input student" id="student" name="student[' . $id . ']" value="' . $mobile . '">';
                                    } else {
                                        echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to select and send SMS">
                                                <input type="checkbox" class="form-check-input student disabled" disabled> 
                                            </span>';
                                    }
                                    echo '</td>
                                        </tr>';
                                    $i++;
                                }
                            }
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>


    <!-- Scripts -->

    <!-- Global Const Variable for can_send -->
    <script>
        const CAN_SEND = <?= can('create', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Checkbox Select All -->
    <script type="text/javascript">
        function toggle(source) {
            checkboxes = document.getElementsByClassName('student');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        $('.student').on('click', function() {
            if ($('.student').not(':checked').length == 0) {
                document.getElementById('select_all').checked = true;
            } else {
                document.getElementById('select_all').checked = false;
            }
        });
    </script>

    <!-- Send SMS -->
    <!--
    <script>
        $('#send').on('click', () => {
            absentees = []
            $(".student:checked").each(function() {
                absentees.push($(this).parent().siblings().eq(4).children().attr('href'));
                //mywin = window.open($(this).parent().siblings().eq(4).children().attr('href'), '_blank')
            });
            if (absentees.length > 0) {
                absentees.forEach((stu) => {
                    mywin = window.open(stu, '_blank')
                })
            }
            /*
            $('.sms_link').each(function() {
                mywin = window.open($(this).attr('href'), '_blank')
            });
            */
        });
    </script>
    -->
    <script>
        async function send(url) {
            var response = await fetch(url);
        }
        $('#send').on('click', () => {
            if (!CAN_SEND) {
                alert("You do not have permission to Send SMS");
                return;
            }
            absentees = []
            $(".student:checked").each(function() {
                absentees.push($(this).parent().siblings().eq(5).children().attr('href'));
                //mywin = window.open($(this).parent().siblings().eq(4).children().attr('href'), '_blank')
            });
            if (absentees.length > 0) {
                absentees.forEach((stu) => {
                    //console.log(stu)
                    send(stu)
                    //mywin = window.open(stu, '_blank')
                })
                alert('All SMS Sent Successfully!')
            } else {
                alert('No Student Selected!')
            }
            /*
            $('.sms_link').each(function() {
                mywin = window.open($(this).attr('href'), '_blank')
            });
            */
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            cls = '<?php echo $class; ?>';
            sec = '<?php echo $section; ?>';
            if (cls != '' && sec != '') {
                filename = cls + sec;
            } else {
                filename = 'All Students';
            }
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
</body>

</html>