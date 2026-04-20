<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 29);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php
if (isset($_POST['Delete_All'])) {
    if (!can('custom1', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to delete past homeworks');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        $handle = opendir($dir);
        while (($file = readdir($handle)) !== false) {
            if ($file != '.' && $file != '..') {
                $filePath = $dir . '/' . $file;
                if (is_dir($filePath)) {
                    deleteDirectory($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        closedir($handle);
        return rmdir($dir);
    }
    $classes = ["PreKG", "LKG", "UKG"];
    for ($i = 1; $i <= 10; $i++) {
        $classes[] = $i . " CLASS";
    }
    $sections = ["A", "B", "C", "D"];
    $today = new DateTime();
    $today = $today->modify("-7 days")->format('d-m-Y');
    $today = new DateTime($today);
    foreach ($classes as $class) {
        foreach ($sections as $section) {
            if (is_dir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/")) {
                $files = array_slice(scandir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/"), 2);
                foreach ($files as $file) {
                    $d = new DateTime($file);
                    if ($d->getTimestamp() < $today->getTimestamp()) {
                        deleteDirectory($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/" . $file);
                    }
                }
            }
        }
    }
    $delete_sql = mysqli_query($link, "DELETE FROM `homework` WHERE STR_TO_DATE(Date, '%d-%m-%Y') < CURDATE() - INTERVAL 7 DAY AND Class = '$class' AND Section = '$section' AND Subject = '$subject'");
    $student_delete_sql = mysqli_query($link, "DELETE sh FROM `student_homework` sh JOIN `student_master_data` smd ON sh.Id_No = smd.Id_No WHERE STR_TO_DATE(sh.Date, '%d-%m-%Y') < CURDATE() - INTERVAL 7 DAY AND smd.Stu_Class = '$class' AND smd.Stu_Section = '$section' AND sh.Subject = '$subject'");


    if ($delete_sql && $student_delete_sql) {
        echo "<script>alert('Successfully Deleted!');</script>";
    } else {
        echo "<script>alert('Successfully Deletion Failed!');</script>";
    }
}

if (isset($_POST['Previous'])) {
    if (!can('custom1', MENU_ID)) {
        $flag = "false";
        echo $flag;
        return;
    }
    $flag = "false";
    $classes = ["PreKG", "LKG", "UKG"];
    for ($i = 1; $i <= 10; $i++) {
        $classes[] = $i . " CLASS";
    }
    $sections = ["A", "B", "C", "D", "E"];
    $date = new DateTime();
    $date = $date->modify("-7 days")->format('d-m-Y');
    $date = new DateTime($date);
    foreach ($classes as $class) {
        foreach ($sections as $section) {
            $sql = mysqli_query($link, "SELECT * FROM `homework` WHERE Class = '$class' AND Section = '$section'");
            while ($row = mysqli_fetch_assoc($sql)) {
                $d = new DateTime($row['Date']);
                if ($d->getTimestamp() < $date->getTimestamp()) {
                    $flag = "true";
                    break 3;
                }
            }
            if (is_dir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/")) {
                $files = array_slice(scandir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/"), 2);
                foreach ($files as $file) {
                    $d = new DateTime($file);
                    if ($d->getTimestamp() < $date->getTimestamp()) {
                        $flag = "true";
                        break 3;
                    }
                }
            }
        }
    }
    echo $flag;
    return;
}

?>
<?php
if (isset($_POST['GetDetails'])) {
    if (!can('update', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to update homework');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $date = $_POST['Date'];
    $class = $_POST['Class'];
    $section = $_POST['Section'];
    $subject = $_POST['Subject'];
    $query1 = mysqli_query($link, "SELECT Text,Image FROM `homework` WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '$subject'");
    if (mysqli_num_rows($query1) == 0) {
        echo "No Data Found!";
    } else {
        $arr = ["Image" => NULL, "Text" => NULL];
        while ($row1 = mysqli_fetch_assoc($query1)) {
            if ($row1['Image'] != NULL) {
                $arr["Image"] = $row1['Image'];
            }
            if ($row1['Text'] != NULL) {
                $arr["Text"] = $row1['Text'];
            }
        }
        echo json_encode($arr);
    }
    return;
}

if (isset($_POST['Action'])) {
    $action = $_POST['Action'];
    $date = format_date($_POST['Date']);
    $class = $_POST['Class'];
    $section = $_POST['Section'];
    $subject = $_POST['Subject'];
    if ($action == "new" || $action == "update") {
        if ($action == "new" && !can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to create homwork');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        if ($action == "update" && !can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to update homework');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }

        $location = $_SESSION['school_db']['Root_Dir'] . '/Files/Homework/' . $class . ' ' . $section;
        if (!is_dir($location)) {
            mkdir($location);
        }
        $location = $_SESSION['school_db']['Root_Dir'] . '/Files/Homework/' . $class . ' ' . $section . '/' . $date . '/';
        if (!is_dir($location)) {
            mkdir($location);
        }
        $html = "
        <!DOCTYPE html>
        <html>
            <head></head>
            <body>
                <h3 style='text-align:center;'>VICTORY E.M SCHOOL</h3>
                <h4 style='text-align:center;'>Student Homework - " . $subject . " - " . $date . "</h4>
            ";
        if ($_POST['Text'] != "") {
            $text = $_POST['Text'];
            $html .= "
            <p style='font-size:25px;'>" . $text . "</p> <br/>
            ";
        }
        if ($_POST['file_type'] == "With_File") {
            $filenames = [];
            if ($_POST['Image_Count'] != 0) {
                $prev_files = [];
                foreach (array_slice(scandir($location), 2) as $f) {
                    if (str_contains($f, $subject) && !str_contains($f, ".pdf")) {
                        $prev_files[explode('.', $f)[0][strlen(explode('.', $f)[0]) - 1]] = explode('.', $f)[1];
                    }
                }
                $database_files = [];
                $query3 = mysqli_query($link, "SELECT * FROM `homework` WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '$subject'");
                while ($row3 = mysqli_fetch_assoc($query3)) {
                    foreach (explode(',', $row3['Image']) as $f) {
                        $database_files[] = explode("/", $f)[count(explode("/", $f)) - 1];
                    }
                }
                $temp = [];
                for ($i = 1; $i <= $_POST['Image_Count']; $i++) {
                    if (!isset($_FILES['Image' . $i])) {
                        $temp[] = $_POST['Image' . $i];
                    }
                }
                sort($temp);
                $k = 1;
                for ($i = 1, $k = 1; $i <= $_POST['Image_Count']; $i++, $k++) {
                    if (!isset($_FILES['Image' . $i])) {
                        if (in_array($_POST['Image' . $i], $temp)) {
                            $t = $_POST['Image' . $i];
                            copy($location . $subject . $t[strlen($t) - 1] . "." . $prev_files[$t[strlen($t) - 1]], $location . $subject . $k . "." . $prev_files[$t[strlen($t) - 1]]);
                            $filename = $location . $subject . $k . "." . $prev_files[$t[strlen($t) - 1]];
                        }
                    } else {
                        $file = $_FILES['Image' . $i];
                        if (!move_uploaded_file($file['tmp_name'], $location . $subject . $k . "." . pathinfo($file['name'], PATHINFO_EXTENSION))) {
                            echo "upload_failure";
                        } else {
                            $filename = $location . $subject . $k . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                        }
                    }
                    if (isset($filename)) {
                        $filenames[] = $filename;
                        $html .= "<img src='" . $filename . "' style='width:85%;padding:2%;object-fit:contain;' />";
                    }
                }
                /* for ($i = 1; $i <= $_POST['Image_Count']; $i++) {
                    $j = 0;
                    if (!isset($_FILES['Image' . $i])) {
                        $t = $_POST['Image' . $i];
                        if (!in_array($t[strlen($t) - 1], array_keys($prev_files))) { //checking modal_img<num> num is already in folder
                            $j = $t[strlen($t) - 1];
                        } else {
                            if ($j != 0) {
                                copy($location . $subject . $j . "." . $prev_files[$t[strlen($t) - 1]], $location . $subject . $i . "." . $prev_files[$t[strlen($t) - 1]]);
                                $filenames[] = $location . $subject . $j . "." . $prev_files[$t[strlen($t) - 1]];
                            } else {
                                $filenames[] = $location . $subject . $i . "." . $prev_files[$t[strlen($t) - 1]];
                            }
                        }
                    } else {
                        $file = $_FILES['Image' . $i];
                        if ($j != 0) {
                            $filename = $location . $subject . $j . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                        } else {
                            $filename = $location . $subject . $i . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                        }
                        if (!move_uploaded_file($file['tmp_name'], $filename)) {
                            echo "upload_failure";
                        } else {
                            $html .= "<img src='" . $filename . "' style='width:100%;' />";
                            if ($j != 0) {
                                $filenames[] = $location . $subject . $j . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                            } else {
                                $filenames[] = $location . $subject . $i . "." . pathinfo($file['name'], PATHINFO_EXTENSION);
                            }
                        }
                    }
                } */
                $files = array_slice(scandir($location), 2);
                foreach ($files as $f) {
                    if (!str_contains($f, ".pdf")) {
                        if ((int)explode('.', $f)[0][strlen(explode('.', $f)[0]) - 1] > $_POST['Image_Count']) {
                            unlink($location . $f);
                        }
                    }
                }
            } else {
                $sql = mysqli_query($link, "SELECT * FROM `homework` WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '$subject'");
                while ($row2 = mysqli_fetch_assoc($sql)) {
                    foreach (explode(',', $row2['Image']) as $img) {
                        $html .= "<img src='" . $img . "' style='width:85%;padding:2%;object-fit:contain;' />";
                        $filenames[] = $img;
                    }
                }
            }
        }
        $html .= "</body>
        </html>";
        require '../SMS/vendor/autoload.php';
        try {
            $html2pdf = new Html2Pdf($orientation = 'P', $format = 'A4');
            $html2pdf->writeHTML($html);
            $d = str_replace('\\', '/', dirname(dirname(__DIR__)));
            $html2pdf->output($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . ' ' . $section . '/' . $date . '/' . $subject . '.pdf', 'F');
        } catch (Exception $e) {
            echo $e->getmessage();
        }
        if ($action == "new") {
            $sql = "INSERT INTO `homework`(Date,Class,Section,Subject";
            if (isset($text)) {
                $sql .= ",Text";
            }
            if ($_POST['file_type'] == "With_File" && count($filenames) != 0) {
                $sql .= ",Image";
            }
            $sql .= ") VALUES('$date','$class','$section','$subject'";
            if (isset($text)) {
                $sql .= ",'$text'";
            }
            if ($_POST['file_type'] == "With_File" && count($filenames) != 0) {
                $filename = implode(',', $filenames);
                $sql .= ",'$filename'";
            }
            $sql .= ")";
            if (mysqli_query($link, $sql)) {
                echo "success";
            }
        } else if ($action == "update") {
            $sql = "UPDATE `homework` SET ";
            if (isset($text)) {
                $sql .= "Text='$text',";
            } else {
                $sql .= "Text=NULL,";
            }
            if ($_POST['file_type'] == "With_File") {
                $filename = implode(',', $filenames);
                $sql .= "Image='$filename'";
            } else {
                $sql .= "Image=NULL";
            }
            $sql .= " WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '$subject'";
            if (mysqli_query($link, $sql)) {
                echo "success";
            }
        }
    } else if ($action == "delete") {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to delete homework');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        if (is_dir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/" . $date)) {
            foreach (array_slice(scandir($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/" . $date), 2) as $file) {
                if (str_contains($file, $subject)) {
                    unlink($_SESSION['school_db']['Root_Dir'] . "/Files/Homework/" . $class . " " . $section . "/" . $date . "/" . $file);
                }
            }
        }
        $delete_sql = mysqli_query($link, "DELETE FROM `homework` WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '$subject'");
        if ($delete_sql) {
            echo "success";
        }
    }
    return;
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />

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
        max-width: 800px;
        max-height: 500px;
        overflow-x: scroll;
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

    .disabled-icon {
        color: grey !important;
        cursor: not-allowed !important;
        pointer-events: none;
    }

    .modal-title {
        color: #000 !important;
    }
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="p-2 col-lg-2 rounded">
                    <label for="">Class:</label>
                </div>
                <div class="p-2 col-lg-3 rounded">
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
            </div>
            <div class="row justify-content-center mt-2">
                <div class="p-2 col-lg-2 rounded">
                    <label for="">Section:</label>
                </div>
                <div class="p-2 col-lg-3 rounded">
                    <select class="form-select" name="Section" id="section" aria-label="Default select example">
                        <option selected disabled>-- Select Section --</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="p-2 col-lg-2 rounded">
                    <label for="">Date of Homework:</label>
                </div>
                <div class="p-2 col-lg-3 rounded">
                    <input type="date" class="form-control" name="Date" id="date">
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
                    <button class="btn btn-warning" type="reset" onclick="hideTable();">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('custom1', MENU_ID)) { ?>
                        title="You don't have permission to delete past homeworks"
                        <?php } ?>>
                        <button class="btn btn-danger" type="submit" name="Delete_All" onclick="if(!confirm('Confirm to Delete Past Week Homeworks?')){return false;}else{return true;}" <?php echo !can('custom1', MENU_ID) ? 'disabled' : ''; ?>>Delete Past Week Homeworks</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="modal fade" id="modal" tabindex="-1">
        <form action="" method="post" enctype="multipart/form-data" id="modal_form">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <input type="hidden" name="Details" id="modal_details" value="">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="file" class="form-control" name="File" id="img_file" accept="image/*" multiple onchange="loadImage()">
                        <div class="row justify-content-center" id="file_row" hidden>
                            <div class="col-lg-8 mt-3">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="file_type" id="with_file" checked value="With_File">
                                    <label class="form-check-label" for="with_file">With File</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="file_type" id="without_file" value="Without_File">
                                    <label class="form-check-label" for="without_file">Without File</label>
                                </div>
                            </div>
                        </div>
                        <div class="img_container">
                        </div>
                        <textarea class="form-control mt-4" name="Text" id="text" cols="30" rows="10" placeholder="Enter Text (Optional):"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" onclick="$('.img_container').empty();">Clear All Images</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>

                        <button type="button"
                            class="btn btn-primary"
                            id="save_btn"
                            data-allowed="0"
                            onclick="save();">
                            Save changes
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="alert-container" hidden>
        <div class="row justify-content-center mt-3">
            <div class="col-lg-4">
                <div class="alert alert-warning" role="alert">
                    ⚠️Please Delete Past week Homeworks!
                </div>
            </div>
        </div>

    </div>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5">
                <h3><b>Manage Class Wise Homeworks</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="width:300px;padding-left:30px;">Subjects</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr>
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
                    echo "<script>date.value = '" . date('Y-m-d') . "';</script>";
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $date = $_POST['Date'];
                        echo "<script>date.value = '" . $date . "';</script>";
                        $date = format_date($date);
                        if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                            if ($_POST['Section']) {
                                $section = $_POST['Section'];
                                echo "<script>document.getElementById('section').value = '" . $section . "'</script>";
                                if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'")) == 0) {
                                    echo "<script>alert('Class or Section Not Available!')</script>";
                                } else {
                                    $query1 = mysqli_query($link, "SELECT DISTINCT Subjects FROM `class_wise_subjects` WHERE Class = '$class'");
                                    if (mysqli_num_rows($query1) == 0) {
                                        echo "<script>alert('Subjects Not Available for this Class!')</script>";
                                    }
                                    $i = 1;
                                    $canCreate = can('create', MENU_ID);
                                    $canView = can('view', MENU_ID);
                                    $canUpdate = can('update', MENU_ID);
                                    $canDelete = can('delete', MENU_ID);
                                    $canStats = can('custom2', MENU_ID);
                                    while ($row1 = mysqli_fetch_assoc($query1)) {
                                        echo '
                                        <tr>
                                            <td>' . $i . '</td>
                                            <td style="padding-left:30px;">' . $row1['Subjects'] . '</td>
                                            <td>';
                                        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `homework` WHERE Date = '$date' AND Class = '$class' AND Section = '$section' AND Subject = '" . $row1['Subjects'] . "'")) == 0) {
                                            echo '
                                            <span class="btn-wrapper ' . (!$canCreate ? 'disabled-wrapper' : '') . '"
                                                ' . (!$canCreate ? 'title="You don\'t have permission to create new entry"' : '') . '>
                                                <button class="btn btn-primary"
                                                        id="new_' . $row1['Subjects'] . '"
                                                        data-mode="create"
                                                        data-allowed="' . ($canCreate ? '1' : '0') . '"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modal"
                                                        onclick="' . ($canCreate
                                                ? 'modal_form.reset();ShowModal(this)'
                                                : 'return false;') . '">
                                                    <i class="bx bx-message-square-add"></i> New
                                                </button>
                                            </span>
                                            ';
                                        } else {
                                            echo '
                                            <a href="' . $_SESSION['school_db']['Root_Dir'] . '/Files/Homework/' . $class . ' ' . $section . '/' . $date . '/' . $row1['Subjects'] . '.pdf"
                                            target="_blank"
                                            class="btn btn-warning ' . (!$canView ? 'disabled' : '') . '"
                                            ' . (!$canView ? 'title="You don\'t have permission to view"' : '') . '>
                                            <i class="fas fa-eye"></i> View
                                            </a>

                                            <span class="btn-wrapper ' . (!$canUpdate ? 'disabled-wrapper' : '') . '"
                                                ' . (!$canUpdate ? 'title="You don\'t have permission to update"' : '') . '>
                                                <button class="btn btn-success"
                                                        id="update_' . $row1['Subjects'] . '"
                                                        data-mode="update"
                                                        data-allowed="' . ($canUpdate ? '1' : '0') . '"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modal"
                                                        onclick="' . ($canUpdate ? 'ShowModal(this)' : 'return false;') . '">
                                                    <i class="bx bx-edit-alt"></i> Update
                                                </button>
                                            </span>

                                            <span class="btn-wrapper ' . (!$canDelete ? 'disabled-wrapper' : '') . '"
                                                ' . (!$canDelete ? 'title="You don\'t have permission to delete"' : '') . '>
                                                <button class="btn btn-danger"
                                                        id="delete_' . $row1['Subjects'] . '"
                                                        onclick="' . ($canDelete ? 'deletework(this.id)' : 'return false;') . '">
                                                    <i class="bx bx-trash"></i> Delete
                                                </button>
                                            </span>

                                            <span class="btn-wrapper ' . (!$canStats ? 'disabled-wrapper' : '') . '"
                                                ' . (!$canStats ? 'title="You don\'t have permission to view analytics"' : '') . '>
                                                <a href="' . ($canStats
                                                ? '/Futuregen/Admin/Homework/homework_analytics.php?Action=show&Date=' . $date . '&Class=' . $class . '&Section=' . $section . '&Subject=' . $row1['Subjects']
                                                : '#') . '"
                                                target="_blank">
                                                    <button class="btn btn-primary" ' . (!$canStats ? 'onclick="return false;"' : '') . '>
                                                        <i class="bx bx-line-chart"></i> Analytics
                                                    </button>
                                                </a>
                                            </span>
                                            ';
                                        }
                                        echo '
                                            </td>
                                        </tr>';
                                        $i++;
                                    }
                                }
                            } else {
                                echo "<script>alert('Please Select Section!')</script>";
                            }
                        } else {
                            echo "<script>alert('Please Select Class!')</script>";
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Scripts -->

    <!-- With File or Without File -->
    <script>
        document.addEventListener('change', (e) => {
            if (e.target.id == 'with_file' && img_file.hidden) {
                img_file.hidden = "";
            } else if (e.target.id == 'without_file' && !img_file.hidden) {
                img_file.hidden = "hidden";
            }
        })
    </script>

    <!-- Set Date -->
    <script>
        function setDate() {
            var today = new Date().toISOString().split('T')[0];
            date.value = today;
        }
    </script>

    <!-- Warning for Past Week HomeWorks -->
    <script>
        $.ajax({
            url: "",
            type: "POST",
            data: {
                "Previous": "true"
            },
            success: function(data) {
                if (data == "false") {
                    document.querySelector('.alert-container').hidden = "hidden";
                } else {
                    document.querySelector('.alert-container').hidden = "";
                }
            }
        })
    </script>

    <!-- Show Class, Section, Subject on Modal, getDetails of -->
    <script>
        function getDetails(date, cls, sec, subject) {
            $.ajax({
                url: "",
                type: "POST",
                data: {
                    GetDetails: true,
                    Date: date,
                    Class: cls,
                    Section: sec,
                    Subject: subject
                },
                success: function(data) {
                    if (data == "No Data Found") {
                        alert("No Data Found of this Subject for this Class and Section!")
                    } else {
                        data = JSON.parse(data)
                        if (data['Image'] != null) {
                            data['Image'] = data['Image'].split(",")
                            with_file.checked = true;
                            $('.img-container').empty();
                            data['Image'].forEach((img, i) => {
                                $('.img_container').append('<div style="display:flex;"><input type="checkbox" name="img" value="modal_img' + (i + 1) + '" checked /><img src="' + img + "?timestamp=" + new Date().getTime() + '" alt="" class="mt-3" id="modal_img' + (i + 1) + '" style="width:100%"></div>')
                            });
                        } else {
                            without_file.checked = true;
                            img_file.hidden = "hidden";
                            $('#modal_img').attr('src', "");
                        }
                        if (data['Text'] != null) {
                            $('#text').html(data['Text']);
                        } else {
                            $('#text').html("");
                        }
                    }
                }
            })
        }

        function ShowModal(el) {


            // 🔐 RBAC check (client-side safety)
            if (el.dataset.allowed !== "1") {
                alert("You don't have permission to perform this action");
                return false;
            }

            var mode = el.dataset.mode; // create | update
            var subject = el.id.split("_")[1];
            var d = date.value.split("-").reverse().join("-");

            $('#modal').off("shown.bs.modal");
            $('#modal').off("hide.bs.modal");

            $('#modal').on('shown.bs.modal', function() {

                document.getElementById('save_btn').dataset.allowed =
                    (mode === "create" && <?= $canCreate ? 'true' : 'false' ?>) ||
                    (mode === "update" && <?= $canUpdate ? 'true' : 'false' ?>) ?
                    "1" : "0";

                $('.modal-title').html(
                    $('#class').val() + " " +
                    $('#section').val() + " " +
                    subject
                );

                $('#modal_details').val(
                    mode + "," +
                    $('#class').val() + "," +
                    $('#section').val() + "," +
                    subject
                );

                if (mode === "update") {
                    file_row.hidden = "";
                    img_file.hidden = "";
                    getDetails(d, $('#class').val(), $('#section').val(), subject);
                } else {
                    file_row.hidden = "hidden";
                    img_file.hidden = "";
                }
            });
        }

        modal.addEventListener('hide.bs.modal', function() {
            document.getElementById("modal_form").reset()
            $('.img_container').empty()
            text.innerHTML = "";
            img_file.value = "";
        })
    </script>

    <!-- Delete Homework of the class, section, subject -->
    <script>
        function deletework(eleid) {
            var date = $('#date').val();
            var cls = $('#class').val();
            var section = $('#section').val();
            var subject = eleid.split("_")[1];
            var par_id = $('#' + eleid).parent()
            if (confirm("Confirm to Delete Homework of " + cls + " " + section + " " + subject + " on " + date + "?")) {
                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        Action: "delete",
                        Date: date,
                        Class: cls,
                        Section: section,
                        Subject: subject
                    },
                    success: function(data) {
                        if (data.trim() == "success") {
                            alert("Successfully Deleted!");
                            var p = $('#' + eleid).parent();
                            $('#' + eleid).parent().empty();
                            p.append('<button class="btn btn-primary" data-bs-toggle="modal" id="new_' + subject + '" data-bs-target="#modal" onclick="ShowModal(this.id)"><i class="bx bx-message-square-add"></i> New</button>')
                        } else {
                            alert("Failed to Delete!");
                        }
                    }
                });
            } else {
                return false;
            }
        }
    </script>

    <!-- Load Image -->
    <script>
        async function loadImage() {
            //$(".img_container").empty()
            let formData = new FormData();
            let fileInput = document.getElementById("img_file");
            if (fileInput.files.length === 0) {
                console.error("No file selected");
                return;
            }
            formData.append("Image_Count", fileInput.files.length);
            Object.entries(fileInput.files).forEach((f, i) => {
                formData.append("Image" + (i + 1), f[1]);
            })
            $.ajax({
                url: "img.php",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    data = data.split('|')
                    data.forEach((d, i) => {
                        if (d.trim() != "") {
                            d = d.split(",")
                            if (d[0] == "Success") {
                                $('.img_container').append('<div style="display:flex;"><input type="checkbox" name="img" value="modal_img' + ($('.img_container input').length + 1) + '" checked /><img src="' + "temp_img" + (i + 1) + "." + d[1] + "?timestamp=" + new Date().getTime() + '" alt="" class="mt-3" id="modal_img' + ($('.img_container input').length + 1) + '" style="width:100%"></div>')
                            } else {
                                alert("Image Upload Failed!")
                            }
                        }
                    })

                }
            })
        }
    </script>

    <!-- Save HomeWork -->
    <script>
        function save() {
            if (document.getElementById('save_btn').dataset.allowed !== "1") {
                alert("You don't have permission to save changes");
                return;
            }
            let formData = new FormData();
            let action = "";
            $('#modal_form').serializeArray().forEach((e) => {
                if (e.name != "img") {
                    if (e.name != "Details") {
                        formData.append(e.name, e.value);
                    } else {
                        let details = e.value.split(",");
                        formData.append("Action", details[0]);
                        action = details[0]
                        formData.append("Class", details[1]);
                        formData.append("Section", details[2]);
                        formData.append("Subject", details[3]);
                    }
                }
            })
            formData.append("Image_Count", $('.img_container input:checked').length);
            var previous = $(".img_container input").length - img_file.files.length;
            $('.img_container input:checked').each((i, e) => {
                if (action == "new") {
                    formData.append("Image" + (i + 1), img_file.files[parseInt(e.value[e.value.length - 1]) - 1]);
                } else if (action == "update") {
                    if (parseInt(e.value[e.value.length - 1]) > previous) {
                        formData.append("Image" + (i + 1), img_file.files[parseInt(e.value[e.value.length - 1]) - previous - 1]);
                    } else {
                        formData.append("Image" + (i + 1), e.value);
                    }
                }
            })
            formData.append("Date", date.value);
            if ((action == "new" && $('.img_container input:checked').length == 0 && text.value.trim() == "") || (action == "update" && (!with_file.checked || $('.img_container input:checked').length == 0) && text.value.trim() == "")) {
                alert("To Save, Either Image or Text should be present!")
            } else {
                $.ajax({
                    url: "",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    cache: false,
                    success: function(data) {
                        if (data.trim() == "success") {
                            bootstrap.Modal.getInstance(document.getElementById('modal')).hide()
                            alert("Saved Successfully!");
                            var p;
                            var subject, cls, section, date;
                            formData.forEach((e, v) => {
                                if (v == "Subject") {
                                    p = $('#new_' + e).parent()
                                    subject = e;
                                }
                                if (v == "Class") {
                                    cls = e;
                                }
                                if (v == "Section") {
                                    section = e;
                                }
                                if (v == "Date") {
                                    date = e;
                                    date = date.split("-").reverse().join("-");
                                }
                            })
                            p.empty();
                            p.append('<a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/Files/Homework/' + cls + ' ' + section + '/' + date + '/' + subject + '.pdf" target="_blank" class="btn btn-warning me-1"><i class="fas fa-eye"></i> View</a><button class="btn btn-success me-1" data-bs-toggle="modal" id="update_' + subject + '" data-bs-target="#modal" onclick="ShowModal(this.id)"><i class="bx bx-edit-alt"></i> Update</button><button class="btn btn-danger" id="delete_' + subject + '" onclick="deletework(this.id)"><i class="bx bx-trash"></i> Delete</button>')
                            text.innerHTML = ""
                        } else {
                            alert("Failed to Save!");
                        }
                    }
                })
            }
        }
    </script>
</body>

</html>