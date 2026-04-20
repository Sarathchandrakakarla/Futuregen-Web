<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 45);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <link rel="stylesheet" href="/Futuregen/css/form-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>
<style>
    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }
</style>

<body>
    <?php
    include '../sidebar.php';
    ?>

    <div class="container">

        <div class="content">
            <div class="title">Student Admission Entry Form</div>
            <form action="" method="POST" onsubmit="if(!confirm('Confirm to Add Student Data?')){return false;}else{return true;}">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Admission Book <span class="required">*</span></span>
                        <select name="Book" id="book">
                            <option value="selectbook" selected disabled>-- Select Book --</option>
                            <option value="a">A</option>
                            <option value="b">B</option>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Admission No. <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Admission No" id="adm_no" name="Adm_No" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Full Name <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Fullname" id="first_name" name="First_Name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Surname <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Surname" id="sur_name" name="Sur_Name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Name of Parent <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Parent Name" id="parent_name" name="Parent_Name" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Date Of Birth</span>
                        <input type="date" name="DOB" id="dob" />
                    </div>
                    <div class="input-box">
                        <span class="details">Residence</span>
                        <input type="text" placeholder="Enter Residence" name="Residence" id="residence" />
                    </div>
                    <div class="input-box">
                        <span class="details">Occupation</span>
                        <input type="text" placeholder="Enter Occupation" name="Occupation" id="occupation" />
                    </div>
                    <div class="input-box">
                        <span class="details">Previous School</span>
                        <input type="text" placeholder="Enter Previous School" id="previous_school" name="Previous_School" />
                    </div>
                    <div class="input-box">
                        <span class="details">Class Studied (Come) <span class="required">*</span></span>
                        <select name="Class_Come" id="class_come">
                            <option value="selectclass" selected disabled>--Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value='" . $i . " CLASS" . "'>" . $i . " CLASS" . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">Gender <span class="required">*</span></span>
                        <div class="category">
                            <input type="radio" id="m" value="m" name="Gender" />
                            <span><label for="m">Male</label></span>
                            <input type="radio" id="f" value="f" name="Gender" />
                            <span><label for="f">Female</label></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">TC No.</span>
                        <input type="text" id="tc_no" placeholder="Enter TC No." name="TC_No" />
                    </div>
                    <div class="input-box">
                        <span class="details">RecordSheet or TC Produced</span>
                        <input type="text" placeholder="Enter Recordsheet/TC Produced" id="tc_pro" name="TC_Pro" />
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Admission</span>
                        <input type="date" name="DOA" id="doa" />
                    </div>
                    <div class="input-box">
                        <span class="details">Mother Tongue</span>
                        <input type="text" placeholder="Enter Mother Tongue" id="mother_tongue" name="Mother_Tongue" />
                    </div>
                    <div class="input-box">
                        <span class="details">Nationality and State</span>
                        <input type="text" placeholder="Enter Nationality and State" id="nationality" name="Nationality" />
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">Religion</span>
                        <div class="category">
                            <input type="radio" id="HINDU" value="HINDU" name="Religion" />
                            <span><label for="HINDU">Hindu</label></span>
                            <input type="radio" id="ISLAM" value="ISLAM" name="Religion" />
                            <span><label for="ISLAM">Islam</label></span>
                            <input type="radio" id="CHRISTIAN" value="CHRISTIAN" name="Religion" />
                            <span><label for="CHRISTIAN">Christian</label></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">Caste</span>
                        <input type="text" placeholder="Enter Caste" name="Caste" id="caste" />
                    </div>
                    <div class="input-box">
                        <span class="details">Medium<span class="required">*</span></span>
                        <input type="text" placeholder="Enter Medium" name="Medium" id="medium" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Class of Admission <span class="required">*</span></span>
                        <select name="Class_Admission" id="class_admission">
                            <option value="selectclass" selected disabled>--Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value='" . $i . " CLASS" . "'>" . $i . " CLASS" . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Class of Leaving</span>
                        <select name="Class_Leave" id="class_leave">
                            <option value="selectclass" selected disabled>--Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value='" . $i . " CLASS" . "'>" . $i . " CLASS" . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Leaving</span>
                        <input type="date" name="DOL" id="dol" />
                    </div>
                    <div class="input-box">
                        <span class="details">No. and Date of TC Issued</span>
                        <input type="text" placeholder="Enter No. and Date of TC Issued" name="DOT" id="dot" />
                    </div>
                </div>
                <div class="button">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert student admission data"
                        <?php } ?>>
                        <input type="submit" name="add" value="Insert" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> />
                    </div>
                    <input type="reset" value="Clear" />
                </div>
            </form>
        </div>
    </div>

    <?php include '../../link.php';
    function format_date($date)
    {
        $dob = explode('-', $date);
        $temp = $dob[0];
        $dob[0] = $dob[2];
        $dob[2] = $temp;

        $date = implode('-', $dob);
        return $date;
    }
    function reset_date($date)
    {
        $dob = explode('-', $date);
        $temp = $dob[0];
        $dob[0] = $dob[2];
        $dob[2] = $temp;

        $date = implode('-', $dob);
        return $date;
    }
    if (isset($_POST["add"])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert student admission data');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }

        //Arrays
        $ids = array();

        //Optional
        $ids["dob"] = $_POST['DOB'];
        $ids["residence"] = $_POST['Residence'];
        $ids["occupation"] = $_POST['Occupation'];
        $ids["previous_school"] = $_POST['Previous_School'];
        $ids["tc_no"] = $_POST['TC_No'];
        $ids["tc_pro"] = $_POST['TC_Pro'];
        $ids["doa"] = $_POST['DOA'];
        $ids["mother_tongue"] = $_POST['Mother_Tongue'];
        $ids["nationality"] = $_POST['Nationality'];
        $religion = $_POST['Religion'];
        if ($religion == "HINDU") {
            $ids["HINDU"] = $religion;
        } else if ($religion == "ISLAM") {
            $ids["ISLAM"] = $religion;
        } else if ($religion == "CHRISTIAN") {
            $ids["CHRISTIAN"] = $religion;
        }
        $ids["caste"] = $_POST['Caste'];
        $ids["class_leave"] = $_POST['Class_Leave'];
        $ids["dol"] = $_POST['DOL'];
        $ids["dot"] = $_POST['DOT'];

        //Required
        $ids["adm_no"] = $_POST['Adm_No'];
        $ids["first_name"] = $_POST['First_Name'];
        $ids["sur_name"] = $_POST['Sur_Name'];
        $ids["parent_name"] = $_POST['Parent_Name'];
        $ids["medium"] = $_POST['Medium'];

        if ($_POST['Book']) {
            $ids["book"] = $_POST['Book'];
            if ($_POST['Gender']) {
                $gender = $_POST['Gender'];
                if ($gender == "m") {
                    $ids["m"] = $gender;
                } else if ($gender == "f") {
                    $ids["f"] = $gender;
                }
                if ($_POST['Class_Come']) {
                    $class_come = $_POST['Class_Come'];
                    $ids["class_come"] = $class_come;
                    if ($_POST['Class_Admission']) {
                        $class_admission = $_POST['Class_Admission'];
                        $ids["class_admission"] = $class_admission;

                        $ids["dob"] = format_date($ids["dob"]);
                        $ids["doa"] = format_date($ids["doa"]);
                        $ids["dol"] = format_date($ids["dol"]);

                        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `admission" . $ids['book'] . "` WHERE Adm_No LIKE '%" . $ids['adm_no'] . "%'")) != 0) {
                            echo "<script>alert('Student Already Exists!')</script>";
                        } else {
                            $sql = "INSERT INTO `admission" . $ids["book"] . "` VALUES('','" . $ids['adm_no'] . "','" . $ids['first_name'] . "',
            '" . $ids['sur_name'] . "','" . $ids['parent_name'] . "','" . $ids['dob'] . "','" . $ids['residence'] . "','" . $ids['occupation'] . "',
            '" . $ids['previous_school'] . "','" . $class_come . "','" . $gender . "','" . $ids['tc_no'] . "','" . $ids['tc_pro'] . "',
            '" . $ids['doa'] . "','" . $ids['mother_tongue'] . "','" . $ids['nationality'] . "','" . $religion . "','" . $ids['caste'] . "',
            '" . $ids['medium'] . "','" . $class_admission . "','" . $ids['class_leave'] . "','" . $ids['dol'] . "','" . $ids['dot'] . "')";
                            if (mysqli_query($link, $sql)) {
                                echo "<script>alert('Student Inserted Successfully!!')</script>";
                            } else {
                                echo "<script>alert('Student Insertion Failed!')</script>";
                            }
                        }
                    } else {
                        echo "<script>alert('Please Select Class of Admission!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class Studied (Come)!')</script>";
                }
            } else {
                echo "<script>alert('Please Select Gender!')</script>";
            }
        } else {
            echo "<script>alert('Please Select Book!')</script>";
        }
        foreach (array_keys($ids) as $id) {
            $ids["dob"] = reset_date($ids["dob"]);
            $ids["doa"] = reset_date($ids["doa"]);
            $ids["dol"] = reset_date($ids["dol"]);
            if ($id == "book" || $id == "class_come" || $id == "class_admission" || $id == "class_leave") {
                if (!$_POST['Class_Come']) {
                    $ids["class_come"] = "selectclass";
                } else if (!$_POST['Class_Admission']) {
                    $ids["class_admission"] = "selectclass";
                } else if (!$_POST['Class_Leave']) {
                    $ids["class_leave"] = "selectclass";
                } else if (!$_POST['Book']) {
                    $ids["book"] = "selectbook";
                }
            }
            if ($id == "m" || $id == "f"  || $id == "HINDU"  || $id == "ISLAM"  || $id == "CHRISTIAN") {
                echo "<script>document.getElementById('" . $id . "').checked = true;</script>";
            } else {
                echo "<script>document.getElementById('" . $id . "').value = '" . $ids[$id] . "'</script>";
            }
        }
    }
    ?>
</body>

</html>