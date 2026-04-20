<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 59);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php

if (isset($_POST['Ok'])) {
    if (!can('view', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to view student fee details');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    if ($_POST['Type']) {
        $type = $_POST['Type'];
        $date = $_POST['DOP'];
        $_SESSION['DOP'] = $date;
        if ($_POST['Id_No']) {
            $id = $_POST['Id_No'];
            $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id'");
            if (mysqli_num_rows($query1) == 0) {
                echo "<script>alert('No Student Found!')</script>";
            } else {
                while ($row = mysqli_fetch_assoc($query1)) {
                    $name = $row['First_Name'];
                    if ($type === 'Vehicle Fee') {
                        $route = $row['Van_Route'];
                        if ($route === '' || $route === NULL) {
                            echo "<script>alert('Vehicle route not assigned to student');</script>";
                            exit;
                        }
                    } else {
                        $class = $row['Stu_Class'];
                        if ($class === '' || $class === NULL) {
                            echo "<script>alert('Student class not assigned');</script>";
                            exit;
                        }
                    }
                }
                $total_fee = 0;
                $ignore = 0;
                $tot_query = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type' LIMIT 1");
                if (mysqli_num_rows($tot_query) == 0) {
                    $query2 = mysqli_query($link, "SELECT * FROM `fee_balances` WHERE Id_No = '$id' AND Type = '$type' LIMIT 1");
                    if (mysqli_num_rows($query2) == 0) {
                        echo "<script>alert('Student Not Found in Fee Master Data and Fee Balances! Please Add student in Fee Master Data via Student Modify Page')</script>";
                        exit;
                    } else {
                        $fee_source = "fee_balances";
                        while ($row2 = mysqli_fetch_assoc($query2)) {
                            $total_fee = $row2['Balance'];
                        }
                    }
                } else {
                    $fee_source = "stu_fee_master_data";
                    while ($tot_row = mysqli_fetch_assoc($tot_query)) {
                        $total_fee = $tot_row['Last_Balance'] + $tot_row['Current_Balance'];
                    }
                }
                $paid_query = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Id_No = '$id' AND Type = '$type'");
                $paid_tot = 0;
                while ($paid_row = mysqli_fetch_assoc($paid_query)) {
                    $paid_tot += (int)$paid_row['Fee'];
                }
                $final = $total_fee - $paid_tot;
                $_SESSION['Final_Balance'] = max(0, $final);
                $_SESSION['FEE_SOURCE'] = $fee_source;
                if ($type === 'Vehicle Fee') {
                    $_SESSION['FEE_KEY'] = $route;
                } else {
                    $_SESSION['FEE_KEY'] = $class;
                }
            }
        } else {
            echo "<script>alert('Please Enter ID No!')</script>";
        }
    } else {
        echo "<script>alert('Please Select Fee Type!')</script>";
    }
}

function format_date($date)
{
    $date = explode('-', $date);
    return $date[2] . '-' . $date[1] . '-' . $date[0];
}

if (isset($_POST['add'])) {

    /* ---------- Permission ---------- */
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\\'t have permission to insert student fee payment details');
              location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    /* ---------- Basic Inputs ---------- */
    if (empty($_POST['Type'])) {
        echo "<script>alert('Please Select Fee Type!')</script>";
        exit;
    }

    if (empty($_POST['Id_No'])) {
        echo "<script>alert('Please Enter ID No!')</script>";
        exit;
    }

    if (empty($_POST['Amount'])) {
        echo "<script>alert('Please Enter Amount!')</script>";
        exit;
    }

    if (empty($_POST['Bill_No'])) {
        echo "<script>alert('Please Enter Bill No.!')</script>";
        exit;
    }

    $type         = $_POST['Type'];
    $id           = $_POST['Id_No'];
    $amount       = (int)$_POST['Amount'];
    $bill         = $_POST['Bill_No'];
    $payment_type = $_POST['Payment_Type'];
    $date_raw     = $_POST['DOP'];

    /* ---------- Session values from OK screen ---------- */
    if (!isset($_SESSION['Final_Balance'], $_SESSION['FEE_SOURCE'], $_SESSION['FEE_KEY'])) {
        echo "<script>alert('Session expired. Please fetch student details again.')</script>";
        exit;
    }

    $final_balance = (int)$_SESSION['Final_Balance'];
    $fee_source    = $_SESSION['FEE_SOURCE'];   // 'stu_fee_master_data' | 'fee_balances'
    $fee_key       = $_SESSION['FEE_KEY'];      // class OR route

    /* ---------- Amount Validation ---------- */
    if ($amount <= 0) {
        echo "<script>alert('Invalid payment amount!')</script>";
        exit;
    }

    if ($amount > $final_balance) {
        echo "<script>alert('Amount exceeds outstanding balance!')</script>";
        exit;
    }

    /* ---------- Fetch student (safe) ---------- */
    $stuQ = mysqli_query(
        $link,
        "SELECT First_Name, Stu_Class, Stu_Section, Mobile, Van_Route
         FROM student_master_data
         WHERE Id_No = '$id'
         LIMIT 1"
    );

    if (!$stuQ || mysqli_num_rows($stuQ) == 0) {
        echo "<script>alert('Student not found!')</script>";
        exit;
    }

    $stu = mysqli_fetch_assoc($stuQ);

    $name    = $stu['First_Name'];
    $class   = $stu['Stu_Class'];
    $section = $stu['Stu_Section'];
    $mobile  = $stu['Mobile'];
    $route   = $stu['Van_Route'];

    /* ---------- Date Formatting ---------- */
    $arr = explode('-', $date_raw);   // yyyy-mm-dd
    $monthMap = [
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Aug',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec'
    ];
    $date = $arr[2] . "-" . $monthMap[(int)$arr[1]] . "-" . $arr[0];

    /* =========================================================
       TRANSACTION START
    ========================================================= */

    mysqli_begin_transaction($link);

    try {

        /* ---------- Insert Payment Ledger ---------- */
        if ($type === 'Vehicle Fee') {

            $ins = mysqli_query(
                $link,
                "INSERT INTO stu_paid_fee VALUES (
                    '',
                    '$id',
                    '$name',
                    '$type',
                    '$class',
                    '$section',
                    '$amount',
                    '$date',
                    '$payment_type',
                    '$bill',
                    '$route'
                )"
            );
        } else {

            $ins = mysqli_query(
                $link,
                "INSERT INTO stu_paid_fee VALUES (
                    '',
                    '$id',
                    '$name',
                    '$type',
                    '$class',
                    '$section',
                    '$amount',
                    '$date',
                    '$payment_type',
                    '$bill',
                    NULL
                )"
            );
        }

        if (!$ins) {
            throw new Exception('Payment insert failed');
        }

        /* ---------- Update fee_balances ONLY if fallback ---------- */
        if ($fee_source === 'fee_balances') {

            $upd = mysqli_query(
                $link,
                "UPDATE fee_balances
                 SET Balance = Balance - $amount
                 WHERE Id_No = '$id' AND Type = '$type'
                 LIMIT 1"
            );

            if (!$upd) {
                throw new Exception('Fee balance update failed');
            }
        }

        mysqli_commit($link);

        /* ---------- SMS (unchanged logic) ---------- */
        if (str_contains($mobile, ',')) {
            $mobile = explode(',', $mobile, 2)[0];
        } elseif (str_contains($mobile, ' ')) {
            $mobile = explode(' ', $mobile, 2)[0];
        }

        $text = "Dear parent, We received with thanks, the amount of Rs $amount towards the $type of your child $name on "
            . format_date($_SESSION['DOP'])
            . " Principal, Victory High School, KDR";

        echo '<a href="http://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $mobile . '&message=' . $text . '&route=TRANS&TemplateID=1707173494146888652&format=JSON" id="sms_link" hidden></a>';

        echo '<script>
            async function send(url){ await fetch(url); }
            send(document.getElementById("sms_link").href);
        </script>';

        echo "<script>alert('Fee Inserted Successfully!!')</script>";
    } catch (Exception $e) {

        mysqli_rollback($link);
        error_log($e->getMessage());

        echo "<script>alert('Payment failed. Please try again.')</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Poppins", sans-serif;
    }

    .required {
        color: red;
        font-size: 20px;
    }

    .container {
        margin: 50px 350px;
        max-width: 700px;
        width: 100%;
        padding: 25px 30px;
        border-radius: 5px;
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
        background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
    }

    .container .title {
        font-size: 25px;
        font-weight: 500;
        position: relative;
    }

    .container .title::before {
        content: "";
        position: absolute;
        left: 0;
        bottom: 0;
        height: 5px;
        width: 100%;
        border-radius: 5px;
        background: linear-gradient(135deg, #71b7e6, #9b59b6);
    }

    .content form .user-details {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        margin: 20px 0 12px 0;
    }

    form .user-details .input-box {
        margin-bottom: 15px;
        width: calc(100% / 2 - 20px);
    }

    form .input-box span.details {
        display: block;
        font-weight: 500;
        margin-bottom: 5px;
    }

    .user-details .input-box input,
    select {
        height: 45px;
        width: 100%;
        outline: none;
        font-size: 16px;
        border-radius: 5px;
        padding-left: 15px;
        border: 1px solid #ccc;
        border-bottom-width: 2px;
        transition: all 0.3s ease;
    }

    .user-details .input-box input:focus,
    .user-details .input-box input:valid {
        border-color: #9b59b6;
    }

    form .gender-details .gender-title {
        font-size: 20px;
        font-weight: 500;
    }

    form .category {
        display: flex;
        width: 80%;
        margin: 14px 0;
        justify-content: space-between;
        font-size: large;
    }

    form .category input {
        margin-left: 20px;
    }

    form .button {
        height: 45px;
        margin: 35px 0;
    }

    form .button input {
        height: 100%;
        width: 100%;
        border-radius: 5px;
        border: none;
        color: #fff;
        font-size: 18px;
        font-weight: 500;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 5px;
        background: linear-gradient(135deg, #71b7e6, #9b59b6);
    }

    form .button input:hover {
        /* transform: scale(0.99); */
        background: linear-gradient(-135deg, #71b7e6, #9b59b6);
    }

    @media (max-width: 584px) {
        .container {
            max-width: 70%;
            margin: 30px 80px;
        }

        form .user-details .input-box {
            margin-bottom: 15px;
            width: 100%;
        }

        form .category {
            width: 100%;
        }

        .content form .user-details {
            max-height: 300px;
            overflow-y: scroll;
        }

        .user-details::-webkit-scrollbar {
            width: 5px;
        }
    }

    @media (max-width: 459px) {
        .container .content .category {
            flex-direction: column;
        }
    }

    #sign-out {
        display: none;
    }

    /* 🔒 Disabled state */
    form .button input:disabled {
        cursor: not-allowed;
        opacity: 0.6;
        background: linear-gradient(135deg, #b5b5b5, #8e8e8e);
    }

    .btn-wrapper {
        display: contents;
    }

    @media screen and (min-width:600px) {
        #ok {
            margin-top: 30px;
            margin-left: 50px;
        }
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
            <div class="title">Student Fee Payment Entry</div>
            <form action="" method="POST">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Fee Type<span class="required">*</span></span>
                        <select name="Type" id="type">
                            <option value="selectfeetype" disabled selected>-- Select Fee Type --</option>
                            <option value="School Fee" <?php if (isset($type) && $type == "School Fee") {
                                                            echo 'selected';
                                                        } else {
                                                            echo "";
                                                        } ?>>School Fee</option>
                            <option value="Admission Fee" <?php if (isset($type) && $type == "Admission Fee") {
                                                                echo 'selected';
                                                            } else {
                                                                echo "";
                                                            } ?>>Admission Fee</option>
                            <option value="Examination Fee" <?php if (isset($type) && $type == "Examination Fee") {
                                                                echo 'selected';
                                                            } else {
                                                                echo "";
                                                            } ?>>Examination Fee</option>
                            <option value="Computer Fee" <?php if (isset($type) && $type == "Computer Fee") {
                                                                echo 'selected';
                                                            } else {
                                                                echo "";
                                                            } ?>>Computer Fee</option>
                            <option value="Vehicle Fee" <?php if (isset($type) && $type == "Vehicle Fee") {
                                                            echo 'selected';
                                                        } else {
                                                            echo "";
                                                        } ?>>Vehicle Fee</option>
                            <option value="Book Fee" <?php if (isset($type) && $type == "Book Fee") {
                                                            echo 'selected';
                                                        } else {
                                                            echo "";
                                                        } ?>>Book Fee</option>
                            <?php
                            if ($_SESSION['school_db']['school_code'] == "FGS") {
                            ?>
                                <option value="Hostel Fee" <?php if (isset($type) && $type == "Hostel Fee") {
                                                                echo "selected";
                                                            } else {
                                                                echo "";
                                                            } ?>>Hostel Fee</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-box">
                    </div>
                    <div class="input-box">
                        <span class="details">Id No. <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Id No" value="<?php if (isset($id)) {
                                                                                echo $id;
                                                                            } else {
                                                                                echo "";
                                                                            } ?>" name="Id_No" oninput="this.value = this.value.toUpperCase()" required />
                    </div>
                    <div class="input-box">
                        <span class="details"></span>
                        <div class="btn-wrapper"
                            <?php if (!can('view', MENU_ID)) { ?>
                            title="You don't have permission to view student fee details"
                            <?php } ?>>
                            <button class="btn btn-primary" id="ok" name="Ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">Full Name</span>
                        <input type="text" name="First_Name" value="<?php if (isset($name)) {
                                                                        echo $name;
                                                                    } else {
                                                                        echo "";
                                                                    } ?>" disabled required />
                    </div>
                    <div class="input-box">
                        <span class="details">Route</span>
                        <input type="text" name="Route" id="route" value="<?php if (isset($route) && $type == "Vehicle Fee") {
                                                                                echo $route;
                                                                            } else {
                                                                                echo "";
                                                                            } ?>" disabled />
                    </div>
                    <div class="input-box">
                        <span class="details">Amount</span>
                        <input type="text" name="Amount" value="<?php if (isset($amount)) {
                                                                    echo $amount;
                                                                } ?>" />
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Payment</span>
                        <input type="date" name="DOP" id="dop" value="<?php if (isset($_SESSION['DOP'])) {
                                                                            echo $_SESSION['DOP'];
                                                                        } else {
                                                                            echo date('Y-m-d');
                                                                        } ?>" />
                    </div>
                    <div class="input-box">
                        <span class="details">Bill No.</span>
                        <input type="text" id="last" value="<?php if (isset($bill)) {
                                                                echo $bill;
                                                            } else {
                                                                echo "";
                                                            } ?>" name="Bill_No" />
                    </div>
                    <div class="input-box">
                        <span class="details">Fee Balance</span>
                        <input type="text" id="last" value="<?php if (isset($_SESSION['Final_Balance'])) {
                                                                echo $_SESSION['Final_Balance'];
                                                            } else {
                                                                echo "";
                                                            } ?>" name="Fee_Balance" readonly />
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">Mode of Payment</span>
                        <div class="category">
                            <input type="radio" id="cash" value="Cash" name="Payment_Type" <?php if (!isset($_SESSION['Payment_Type']) || (isset($_SESSION['Payment_Type']) && $_SESSION['Payment_Type'] == "Cash")) {
                                                                                                echo "checked";
                                                                                            } ?> />
                            <span><label for="cash">Cash</label></span>
                            <input type="radio" id="upi" value="UPI" name="Payment_Type" <?php if (isset($_SESSION['Payment_Type']) && $_SESSION['Payment_Type'] == "UPI") {
                                                                                                echo "checked";
                                                                                            } ?> />
                            <span><label for="upi">UPI</label></span>
                        </div>
                    </div>
                </div>
                <div class="button">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert student fee payment details"
                        <?php } ?>>
                        <input type="submit" name="add" value="Insert" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Scripts -->

    <!-- Get Today Date -->
    <script>
        function getDate() {
            var today = new Date();
            date = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);
            document.getElementById("dop").value = date;
        }
    </script>
</body>

</html>