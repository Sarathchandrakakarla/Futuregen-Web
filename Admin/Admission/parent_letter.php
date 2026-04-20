<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 47);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php

if (isset($_POST['Ok'])) {
    if (!can('view', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to view this report');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    if ($_POST['Class']) {
        $class = $_POST['Class'];
        if ($_POST['Section']) {
            $section = $_POST['Section'];

            //Arrays
            $ids = array();
            $adms = array();
            $names = array();
            $sur_names = array();
            $father_names = array();
            $mother_names = array();
            $dobs = array();
            $castes = array();

            $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
            while ($row1 = mysqli_fetch_assoc($query1)) {
                array_push($adms, $row1['Adm_No']);
                $ids[$row1['Adm_No']] = $row1['Id_No'];
                $mother_names[$row1['Adm_No']] = $row1['Mother_Name'];
            }

            foreach ($adms as $adm) {
                $query2 = mysqli_query($link, "SELECT * FROM `admissionb` WHERE Adm_No = '$adm'");
                if (mysqli_num_rows($query2) > 1) {
                    echo "<script>alert('Admission No: " . $adm . " has multiple records. Please Verify this!')</script>";
                } else if (mysqli_num_rows($query2) == 0) {
                    echo "<script>alert('No Student Found with Admission No: " . $adm . ". Please Verify this!')</script>";
                } else {
                    while ($row2 = mysqli_fetch_assoc($query2)) {
                        $names[$adm] = $row2['First_Name'];
                        $sur_names[$adm] = $row2['Sur_Name'];
                        $father_names[$adm] = $row2['Parent_Name'];
                        $dobs[$adm] = $row2['DOB'];
                        $castes[$adm] = $row2['Caste'];
                    }
                }
            }
            $text = array();
            $i = 1;
            foreach ($adms as $adm) {
                $text[$adm] = '<table style="border: solid black;margin-top:15px;height:48%;margin-left:20px;">
        <tr>
            <td></td>
            <td style="font-size: 20px;padding-bottom: 10px;" colspan="3"><b>విక్టరీ హైస్కూల్, గాంధీ నగర్, కోడూరు</b></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="4" style="padding-bottom: 25px;font-weight: bold;">మా స్కూల్ నందు చదువుతున్న మీ అమ్మాయి/అబ్బాయి  <u>' . $names[$adm] . '</u> <br>   యొక్క వివరాలు ఈ క్రింది విధంగా నమోదు అయి ఉన్నాయి. మీరు ఒకసారి పరిశీలించి, ఏదైనా మార్పులున్నచో మీరే స్వయముగా వచ్చి సరి చూచుకొనగలరు. పబ్లిక్ పరీక్షల మార్కుల జాబితాలో ఇవియే వివరాలు నమోదు అవుతాయి, తర్వాత మార్చలేము.</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Class:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">' . $class . ' ' . $section . '</td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Student ID:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">' . $ids[$adm] . '</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">Mother Name:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">' . $mother_names[$adm] . '</td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Admission No.</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">' . $adm . '</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Date of Birth:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">' . $dobs[$adm] . '</td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Surname:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">' . $sur_names[$adm] . '</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Caste:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">' . $castes[$adm] . '</td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Student Name:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">' . $names[$adm] . '</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;"></td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;"></td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;">Father Name:</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">' . $father_names[$adm] . '</td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;"></td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;"></td>
        </tr>
        <tr>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">Student\'s Signature</td>
            <td></td>
            <td></td>
            <td style="padding:0 10px 10px;font-family: Times, serif;font-weight:700;white-space:nowrap;">Parent\'s/Guardian\'s Signature</td>
        </tr>
    </table>';
                $i++;
            }
        } else {
            echo "<script>alert('Please Select Class!!')</script>";
        }
    } else {
        echo "<script>alert('Please Select Class!!')</script>";
    }
}

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
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Class" id="class" aria-label="Default select example">
                        <option selected disabled>-- Select Class --</option>
                        <option value="6 CLASS" <?php if (isset($class) && $class == "6 CLASS") {
                                                    echo "selected";
                                                } else {
                                                    echo "";
                                                } ?>>6 CLASS</option>
                        <option value="7 CLASS" <?php if (isset($class) && $class == "7 CLASS") {
                                                    echo "selected";
                                                } else {
                                                    echo "";
                                                } ?>>7 CLASS</option>
                        <option value="8 CLASS" <?php if (isset($class) && $class == "8 CLASS") {
                                                    echo "selected";
                                                } else {
                                                    echo "";
                                                } ?>>8 CLASS</option>
                        <option value="9 CLASS" <?php if (isset($class) && $class == "9 CLASS") {
                                                    echo "selected";
                                                } else {
                                                    echo "";
                                                } ?>>9 CLASS</option>
                        <option value="10 CLASS" <?php if (isset($class) && $class == "10 CLASS") {
                                                        echo "selected";
                                                    } else {
                                                        echo "";
                                                    } ?>>10 CLASS</option>
                    </select>
                </div>
                <div class="p-2 col-lg-4 rounded">
                    <select class="form-select" name="Section" id="sec" aria-label="Default select example">
                        <option selected disabled>-- Select Section --</option>
                        <option value="A" <?php if (isset($section) && $section == "A") {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>A</option>
                        <option value="B" <?php if (isset($section) && $section == "B") {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>B</option>
                        <option value="C" <?php if (isset($section) && $section == "C") {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>C</option>
                        <option value="D" <?php if (isset($section) && $section == "D") {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>D</option>
                        <option value="E" <?php if (isset($section) && $section == "E") {
                                                echo "selected";
                                            } else {
                                                echo "";
                                            } ?>>E</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <p style="color:red;">NOTE: PLEASE GIVE MARGIN:MINIMUM IN PAGE SETUP</p>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="Ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Parent Letter</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <?php
        foreach ($adms as $adm) {
            echo $text[$adm];
        } ?>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>




    <!-- Scripts -->

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML =
                document.querySelector(".table-container").innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>