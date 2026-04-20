<?php
/*
Study Certificate
width:23.00cm
height:15.00cm
left:1.70cm
right:1.30cm
top:0.50cm
bottom:0.70cm
*/
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 48);

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
        max-height: 600px;
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
                <div class="col-lg-2">
                    <label for="">Admission No.:</label>
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="Adm_No" id="adm_no" placeholder="Adm No(4235/2014-15)" required>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-2">
                    <label for="">Id No.:</label>
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="Id_No" id="id_no" placeholder="Id No (VHST01234)" oninput="this.value = this.value.toUpperCase();" required>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-2">
                    <label for="">Class(es):</label>
                </div>
                <div class="col-lg-2">
                    <input type="text" class="form-control" name="Classes" id="classes" placeholder="Class(es) (6-8)" required>
                </div>
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Conduct" id="good" checked value="Good">
                        <label class="form-check-label" for="good">Good</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="Conduct" id="satisfactory" value="Satisfactory">
                        <label class="form-check-label" for="satisfactory">Satisfactory</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <p style="color:red;">NOTE: PLEASE GIVE MARGIN:MINIMUM IN PAGE SETUP</p>
                </div>
            </div> -->
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
                <h3><b>Study Certificate</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <?php
        function Roman($num)
        {
            $mapping = [
                10 => 'X',
                9 => 'IX',
                5 => 'V',
                4 => 'IV',
                1 => 'I'
            ];

            $result = '';

            foreach ($mapping as $value => $roman) {
                while ($num >= $value) {
                    $result .= $roman;
                    $num -= $value;
                }
            }

            return $result;
        }
        function formatClass($class)
        {
            if (str_contains($class, "-")) {
                $classes = [];
                foreach (explode('-', $class) as $c) {
                    $classes[] = Roman($c);
                }
                $class = implode(' to ', $classes);
                return $class;
            } else {
                return Roman($class);
            }
        }
        function getPeriod($classes, $initial)
        {
            if (str_contains($classes, "-")) {
                $class1 = trim(explode('-', $classes)[0]);
                $class2 = trim(explode('-', $classes)[1]);
                $years = (int)$class2 - (int)$class1 + 1;
            } else {
                $years = 1;
            }
            $periods = [];
            $year = explode('-', $initial)[1];
            array_push($periods, $initial);
            $final_year = (int)$year + $years - 1;
            array_push($periods, "20" . $final_year - 1 . "-" . $final_year);
            return $periods;
        }

        function numberToWords($num)
        {
            $ones = array(
                0 => "zero",
                1 => "one",
                2 => "two",
                3 => "three",
                4 => "four",
                5 => "five",
                6 => "six",
                7 => "seven",
                8 => "eight",
                9 => "nine",
                10 => "ten",
                11 => "eleven",
                12 => "twelve",
                13 => "thirteen",
                14 => "fourteen",
                15 => "fifteen",
                16 => "sixteen",
                17 => "seventeen",
                18 => "eighteen",
                19 => "nineteen"
            );
            $tens = array(
                0 => "zero",
                1 => "ten",
                2 => "twenty",
                3 => "thirty",
                4 => "forty",
                5 => "fifty",
                6 => "sixty",
                7 => "seventy",
                8 => "eighty",
                9 => "ninety"
            );

            if ($num < 20) {
                return $ones[$num];
            } elseif ($num < 100) {
                return $tens[intval($num / 10)] . (($num % 10 != 0) ? "-" . $ones[$num % 10] : "");
            } else {
                return $ones[intval($num / 100)] . " hundred" . (($num % 100 != 0) ? " " . numberToWords($num % 100) : "");
            }
        }

        function DateToWords($date)
        {
            $months = array(
                1 => "January",
                2 => "February",
                3 => "March",
                4 => "April",
                5 => "May",
                6 => "June",
                7 => "July",
                8 => "August",
                9 => "September",
                10 => "October",
                11 => "November",
                12 => "December"
            );

            $daySuffix = function ($day) {
                if (!in_array(($day % 100), array(11, 12, 13))) {
                    switch ($day % 10) {
                        case 1:
                            return $day . "st";
                        case 2:
                            return $day . "nd";
                        case 3:
                            return $day . "rd";
                    }
                }
                return $day . "th";
            };

            $dateParts = explode("-", $date);
            $day = (int)$dateParts[0];
            $month = (int)$dateParts[1];
            $year = (int)$dateParts[2];

            $dayInWords = $daySuffix($day);
            $monthInWords = $months[$month];
            $yearInWords = numberToWords((int)($year / 1000)) . " thousand " . (($year % 1000 != 0) ? numberToWords($year % 1000) : "");

            return $dayInWords . " " . $monthInWords . " " . $yearInWords;
        }

        if (isset($_POST['Ok'])) {
            if (!can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
            }
            $adm_no = $_POST['Adm_No'];
            $id_no = $_POST['Id_No'];
            $classes = $_POST['Classes'];
            $conduct = $_POST['Conduct'];
            echo "<script>
            document.getElementById('adm_no').value = '" . $adm_no . "';
            document.getElementById('id_no').value = '" . $id_no . "';
            document.getElementById('classes').value = '" . $classes . "';
            document.getElementById('" . strtolower($conduct) . "').checked = true;
            </script>";
            $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id_no'");
            if (mysqli_num_rows($query1) == 0) {
                echo "<script>alert('Student Not Found with Id No. " . $id_no . "');</script>";
            } else {
                while ($row1 = mysqli_fetch_assoc($query1)) {
                    $name = $row1['Sur_Name'] . " " . explode('.', $row1['First_Name'])[1];
                    if (explode(' ', $row1['Father_Name'])[0] == $row1['Sur_Name']) {
                        $father_name = $row1['Father_Name'];
                    } else {
                        $father_name = $row1['Sur_Name'] . " " . $row1['Father_Name'];
                    }
                    $gender = $row1['Gender'] == "Boy" ? "Son" : "Daughter";
                    $pronoun = $row1['Gender'] == "Boy" ? "his" : "her";
                    $name_prefix = $row1['Gender'] == "Boy" ? "Master" : "Kum";
                    $dob = $row1['DOB'];
                    $religion = $row1['Religion'];
                    $caste = $row1['Caste'];
                    $category = $row1['Category'];
                    $years = getPeriod($classes, explode('/', $adm_no)[1]);
                    $classes = formatClass($classes);
                }
                $text = "
                <table width='100%' style='margin-top:1.9cm;font-family:Arial;'>
                    <thead>
                        <tr>
                            <th style='text-align:center;font-size:20px;'><span style='border:2px solid black;border-radius:10px;padding:5px;'>STUDY - CUM - BONAFIDE CERTIFICATE</span></th>
                        </tr>
                        <tr>
                            <th style='font-size:18px;text-align:right;width:100%;padding-top:20px;padding-right:50px;'>Admn No. " . $adm_no . "</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style='padding-top:20px;' colspan='2'>
                                <p style='font-size:21px;line-height:30px;'>
                                    This is to certify that " . $name_prefix . " <u>" . $name . "</u><br/>
                                    " . $gender . " of Sri <u>" . $father_name . "</u><br/>
                                    has studied in this school from <u>" . $classes . "</u> Class(es) during the period from <u> " . $years[0] . "</u> to <u> " . $years[1] . "</u>. During this period " . $pronoun . " conduct has been found <u>" . strtoupper($conduct) . "</u>. <br/><br/>
                                    " . ucfirst($pronoun) . " Date of Birth as per our school records is <u>" . $dob . "</u> in words <u> " . DateToWords($dob) . " </u> ,  Caste:<u>" . $religion . " " . $caste . " (" . $category . ")</u>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding-top:50px;'>
                                Date: " . date('d-m-Y') . "<br/>
                                Place: Kodur.
                                <span style='padding-left:400px;'>Signature of the Headmaster</span>
                            </td>
                            <!--<td style='padding-top:75px;'></td>-->
                        </tr>
                    </tbody>
                </table>
                ";
                echo $text;
            }
        }
        ?>
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