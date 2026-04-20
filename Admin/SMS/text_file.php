<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 83);

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
        max-width: 900px;
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
                <div class="col-lg-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_type" id="section_wise" checked value="Section_Wise">
                        <label class="form-check-label" for="section_wise">Section Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_type" id="class_wise" value="Class_Wise">
                        <label class="form-check-label" for="class_wise">Class Wise</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="data_type" id="all_students" value="All_Students">
                        <label class="form-check-label" for="all_students">All Students</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-5" id="class_row">
                <label for="class_name" class="col-lg-2 col-form-label">Class</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam(this.value)">
                        <option value="selectclass" selected disabled>--Select Class--</option>
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
            </div>
            <div class="row justify-content-center mt-3" id="section_row">
                <label for="class_name" class="col-lg-2 col-form-label">Section</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Section" id="sec">
                        <option value="selectsection" selected disabled>--Select Section--</option>
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
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Download</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Create Text File of Phone Numbers</b></h3>
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['show'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view this report');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $final_flag = false;
        $file_name = "phone_numbers";
        echo "<script>document.getElementById('" . strtolower($_POST['data_type']) . "').checked=true;</script>";
        if ($_POST['data_type'] == "Class_Wise" || $_POST['data_type'] == "Section_Wise") {
            echo "<script>
                            document.getElementById('class_row').hidden='';
                            document.getElementById('section_row').hidden='hidden';
                            </script>";
            if ($_POST['Class']) {
                $flag = true;
                $class = $_POST['Class'];
                echo "<script>document.getElementById('class').value='" . $class . "';</script>";
                $file_name = "phone_numbers_" . $class;
                $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class'";
                if ($_POST['data_type'] == "Section_Wise") {
                    echo "<script>document.getElementById('section_row').hidden='';</script>";
                    if ($_POST['Section']) {
                        $section = $_POST['Section'];
                        echo "<script>document.getElementById('sec').value='" . $section . "';</script>";
                        $file_name .= " " . $section;
                        $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'";
                    } else {
                        $flag = false;
                        echo "<script>alert('Please Select Section!');</script>";
                    }
                }
                if ($flag) {
                    $result = mysqli_query($link, $sql);
                    $phones = [];
                    while ($row = mysqli_fetch_assoc($result)) {
                        $phones[] = trim(explode(',', $row['Mobile'])[0]);
                    }
                    $phones = array_unique($phones);
                    $final_flag = true;
                }
            } else {
                echo "<script>alert('Please Select Class!');</script>";
            }
        } else if ($_POST['data_type'] == "All_Students") {
            echo "<script>
                            document.getElementById('class_row').hidden='hidden';
                            document.getElementById('section_row').hidden='hidden';
                            </script>";
            $file_name = "phone_numbers-all students";
            $phones = [];
            $sql = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS')");
            while ($row1 = mysqli_fetch_assoc($sql)) {
                if (str_contains($row1['Mobile'], ',') && strlen(trim(explode(',', $row1['Mobile'])[0])) == 10) {
                    $phones[] = trim(explode(',', $row1['Mobile'])[0]);
                } else if (str_contains($row1['Mobile'], '.') && strlen(trim(explode('.', $row1['Mobile'])[0])) == 10) {
                    $phones[] = trim(explode('.', $row1['Mobile'])[0]);
                } else if (str_contains($row1['Mobile'], ' ') && strlen(trim(explode(' ', $row1['Mobile'])[0])) == 10) {
                    $phones[] = trim(explode(' ', $row1['Mobile'])[0]);
                } else {
                    if (strlen(trim($row1['Mobile'])) == 10) {
                        $phones[] = trim($row1['Mobile']);
                    }
                }
            }
            //$phones = array_unique($phones);
            $final_flag = true;
        }
        if ($final_flag) {
            $file = fopen("../phone_numbers.txt", "w");
            $text = implode("\n", $phones);
            fwrite($file, $text);
            fclose($file);
            echo "<a href='../phone_numbers.txt' id='file' download='" . $file_name . "' hidden>Download</a>";
            echo "<script>document.getElementById('file').click();</script>";
        }
    }
    ?>


    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        let section_row = document.getElementById('section_row');
        let class_row = document.getElementById('class_row');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'class_wise':
                    if (class_row.hidden) {
                        class_row.hidden = '';
                    }
                    if (!section_row.hidden) {
                        section_row.hidden = 'hidden';
                    }
                    break;
                case 'section_wise':
                    if (class_row.hidden) {
                        class_row.hidden = '';
                    }
                    if (section_row.hidden) {
                        section_row.hidden = '';
                    }
                    break;
                case 'all_students':
                    if (!class_row.hidden) {
                        class_row.hidden = 'hidden';
                    }
                    if (!section_row.hidden) {
                        section_row.hidden = 'hidden';
                    }
                    break;
            }
        });
    </script>

</body>

</html>