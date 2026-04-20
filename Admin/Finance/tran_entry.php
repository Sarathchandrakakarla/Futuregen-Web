<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 69);

requireLogin();
requireMenuAccess(MENU_ID);

//error_reporting(0);
?>

<?php

function format_date($date)
{
    $arr = explode('-', $date);
    $t = $arr[0];
    $arr[0] = $arr[2];
    $arr[2] = $t;
    switch ($arr[1]) {
        case '1':
            $arr[1] = "Jan";
            break;
        case '2':
            $arr[1] = "Feb";
            break;
        case '3':
            $arr[1] = "Mar";
            break;
        case '4':
            $arr[1] = "Apr";
            break;
        case '5':
            $arr[1] = "May";
            break;
        case '6':
            $arr[1] = "Jun";
            break;
        case '7':
            $arr[1] = "Jul";
            break;
        case '8':
            $arr[1] = "Aug";
            break;
        case '9':
            $arr[1] = "Sep";
            break;
        case '10':
            $arr[1] = "Oct";
            break;
        case '11':
            $arr[1] = "Nov";
            break;
        case '12':
            $arr[1] = "Dec";
            break;
    }
    return implode('-', $arr);
}

if (isset($_POST['Ok'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert expenses');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $ac = $_POST['AC_No'];
    $date = $_POST['DOP'];

    //Queries
    $query1 = mysqli_query($link, "SELECT * FROM `debiter_master_data` WHERE AC_No = '$ac'");
    if ($query1) {
        if (mysqli_num_rows($query1) == 0) {
            echo "<script>alert('No Debiter Found');</script>";
        } else {
            while ($row1 = mysqli_fetch_assoc($query1)) {
                $name = $row1['Name'];
                $max = $row1['Amount'];
                $_SESSION['Max'] = (int)$max;
            }
        }
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

    .entry-container {
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

    .table-container {
        max-width: 1100px;
        max-height: 500px;
        overflow-x: scroll;
    }

    .delete {
        color: red;
        cursor: pointer;
    }

    @media print {
        #action_head {
            display: none;
        }
    }

    /* Hover ONLY when enabled */
    form .button input:not(:disabled):hover {
        background: linear-gradient(-135deg, #71b7e6, #9b59b6);
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
</style>

<body>
    <?php include '../sidebar.php'; ?>

    <div class="container entry-container">

        <div class="content">
            <div class="title">Expenditure Details Entry</div>
            <form action="" method="POST">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Date of Payment<span class="required">*</span></span>
                        <input type="date" name="DOP" id="dop" value="<?php if (isset($date)) {
                                                                            echo $date;
                                                                        } else {
                                                                            echo date("Y-m-d");
                                                                        } ?>" required>
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">View</span>
                        <div class="category">
                            <input type="radio" id="expenditure" value="Expenditure" name="Type" checked />
                            <span><label for="expenditure">Expenditure</label></span>
                            <input type="radio" id="collection" value="Collection" name="Type" />
                            <span><label for="collection">Collection</label></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">Account No. <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Account No" value="<?php if (isset($ac)) {
                                                                                        echo $ac;
                                                                                    } ?>" name="AC_No" id="ac_no" oninput="this.value = this.value.toUpperCase()" required />
                    </div>
                    <div class="input-box">
                        <span class="details"></span>
                        <button class="btn btn-primary" id="ok" name="Ok">OK</button>
                    </div>
                    <div class="input-box">
                        <span class="details">Debiter's Name</span>
                        <input type="text" value="<?php if (isset($name)) {
                                                        echo $name;
                                                    } ?>" name="Name" id="name" readonly required />
                    </div>
                    <div class="input-box">
                        <span class="details">Amount</span>
                        <input type="text" value="<?php if (isset($amount)) {
                                                        echo $amount;
                                                    } ?>" name="Amount" id="amount" />
                    </div>
                    <div class="input-box">
                        <span class="details">Voucher/Bill No.</span>
                        <input type="text" value="<?php if (isset($bill)) {
                                                        echo $bill;
                                                    } ?>" name="Bill_No" id="bill_no" />
                    </div>
                    <div class="input-box">
                        <span class="details">Purpose</span>
                        <input type="text" value="<?php if (isset($purpose)) {
                                                        echo $purpose;
                                                    } ?>" name="Purpose" id="purpose" />
                    </div>
                </div>
                <div class="button">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert expenses"
                        <?php } ?>>
                        <input type="submit" name="add" value="Insert" onclick="if(!confirm('Confirm to Insert Debiter Data?')){return false;}else{return true;}" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> />
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <button class="btn btn-success" id="ok" onclick="printDiv();return false;">Print</button>
            </div>
        </div>
    </div>

    <div class="container table-container">
        <table class="table table-striped">
            <thead>
                <th colspan="8" style="text-align: center;">Date of payment: <span id="date"></span></th>
            </thead>
            <thead id="expenses" hidden>
                <th style='text-align:center;'>S.No</th>
                <th style='text-align:center;'>AC.No</th>
                <th style='text-align:center;'>Name</th>
                <th style='text-align:center;'>Amount</th>
                <th style='text-align:center;'>Purpose</th>
                <th style='text-align:center;'>Bill No</th>
                <th style='text-align:center;' id='action_head'>Action</th>
            </thead>
            <thead id="collects" hidden>
                <th>S.No</th>
                <th>Id.No</th>
                <th>Name</th>
                <th>Class</th>
                <th>Amount</th>
                <th>Bill No</th>
                <th>Mode of Payment</th>
                <th>Action</th>
            </thead>
            <tbody id="tbody">
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <?php

    if (isset($_POST['add'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert expenses');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $date = $_POST['DOP'];
        $ac = $_POST['AC_No'];
        $name = $_POST['Name'];
        echo "<script>document.getElementById('dop').value = '" . $date . "';
        document.getElementById('ac_no').value = '" . $ac . "';
        document.getElementById('name').value = '" . $name . "';</script>";

        if ($_POST['Amount']) {
            $amount = $_POST['Amount'];
            $query1 = mysqli_query($link, "SELECT Amount FROM `debiter_master_data`  WHERE AC_No = '$ac'");
            $query2 = mysqli_query($link, "SELECT Amount FROM `tran_details` WHERE AC_No = '$ac'");
            while ($row1 = mysqli_fetch_assoc($query1)) {
                $max = (int)$row1['Amount'];
            }
            $paid = 0;
            while ($row2 = mysqli_fetch_assoc($query2)) {
                $paid += (int)$row2['Amount'];
            }
            if ((int)$amount + (int)$paid > $max) {
                echo "<script>alert('Amount Exceeded!!');</script>";
            } else {
                echo "<script>document.getElementById('amount').value = '" . $amount . "';</script>";
                if ($_POST['Bill_No']) {
                    $bill  = $_POST['Bill_No'];
                } else {
                    $bill = '0';
                }
                echo "<script>document.getElementById('bill_no').value = '" . $bill . "';</script>";

                if ($_POST['Purpose']) {
                    $purpose = $_POST['Purpose'];
                    echo "<script>document.getElementById('purpose').value = '" . $purpose . "';</script>";

                    $new_date = format_date($date);
                    $sql = mysqli_query($link, "INSERT INTO `tran_details` VALUES('','$ac','$amount','$new_date','$bill','$purpose')");
                    if ($sql) {
                        echo "<script>alert('Inserted Successfully!')</script>";
                    } else {
                        echo "<script>alert('Insertion Failed!')</script>";
                    }

                    $sql1 = mysqli_query($link, "SELECT * FROM `tran_details` WHERE DOP = '$new_date'");
                    $details = array();
                    while ($row = mysqli_fetch_assoc($sql1)) {
                        $temp = array();
                        array_push($temp, $row['AC_No']);
                        array_push($temp, $row['Amount']);
                        array_push($temp, $row['Purpose']);
                        array_push($temp, $row['Bill_No']);
                        array_push($temp, $row['DOP']);
                        array_push($details, $temp);
                    }

                    $text = "<tr>";
                    $i = 1;
                    foreach ($details as $detail) {
                        $text .= "<td>" . $i . "</td>";
                        foreach ($detail as $col) {
                            $text .= "<td>" . $col . "</td>";
                        }
                        //$text .= "<td><i class='bx bx-trash delete'></i></td>";
                        $text .= "</tr>";
                        $i++;
                    }
                    echo "<script>document.getElementById('tbody').innerHTML = '" . $text . "'</script>";
                } else {
                    echo "<script>alert('Please Enter Purpose!');</script>";
                }
            }
        } else {
            echo "<script>alert('Please Enter Amount!');</script>";
        }
    }

    ?>
    <!-- Scripts -->

    <!-- Global Const Variables for can_update,can_allocate -->
    <script>
        const CAN_VIEW = <?= can('view', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_DELETE = <?= can('delete', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            if (document.getElementById('expenditure').checked) {
                window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Day Expenses Report</h2>";
            } else {
                window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Day Collections Report</h2>";
            }
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>

    <!-- Get Existing Data on that Date -->
    <script type="text/javascript">
        function myFunction(item, index) {
            text += index + ": " + item + "<br>";
        }
        $('#ac_no').on('focus', function() {
            if (!CAN_VIEW) {
                alert("You do not have permission to view expenses/collections");
                return;
            }
            date = document.getElementById('dop').value;
            var mydate = new Date(date);
            var month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ][mydate.getMonth()];
            var d = mydate.getDate();
            if (d < 10) {
                d = '0' + d;
            }
            var str = d + '-' + month + '-' + mydate.getFullYear();
            document.getElementById('date').innerHTML = str;
            if (document.getElementById('expenditure').checked) {
                type = document.getElementById('expenditure').value;
                document.getElementById('expenses').hidden = '';
                document.getElementById('collects').hidden = 'hidden';
            } else {
                type = document.getElementById('collection').value;
                document.getElementById('expenses').hidden = 'hidden';
                document.getElementById('collects').hidden = '';
            }
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    Action: 'View',
                    Date: date,
                    Type: type
                },
                success: function(data) {
                    if (data == "permission") {
                        alert('You don\'t have permission to view expenses/collections');
                    } else {
                        document.querySelector('#tbody').innerHTML = data;
                    }
                }
            });
        });
    </script>

    <!-- Delete Row -->
    <script type="text/javascript">
        function delete_row(e) {
            if (!CAN_DELETE) {
                alert("You do not have permission to delete expenses/collections");
                return;
            }
            type = document.getElementById('expenditure').checked ? 'expenses' : 'collections';
            date = document.getElementById('date').innerHTML
            if (type == "expenses") {
                ac_no = $(e).parent().siblings().eq(1).text();
                amount = $(e).parent().siblings().eq(3).text();
                purpose = $(e).parent().siblings().eq(4).text();
                if (confirm('Confirm to Delete Payment ' + ac_no + '  ' + amount + '  ' + purpose + ' on ' + date + '?')) {
                    $.ajax({
                        type: 'post',
                        url: 'temp.php',
                        data: {
                            Action: 'Delete',
                            ac_No: ac_no,
                            Amount: amount,
                            Purpose: purpose,
                            Date: date,
                            Type: type,
                        },
                        success: function(data) {
                            console.log(data)
                            if (data == "success") {
                                alert('Payment Deleted Successfully!!');
                            } else if (data == "failure") {
                                alert('Payment Deletion Failed!');
                            } else if (data == "permission") {
                                alert('You don\'t have permission to delete expenses');
                            } else {
                                alert('No Payment Found!');
                            }
                        }
                    });
                }
            } else if (type == "collections") {
                id_no = $(e).parent().siblings().eq(1).text();
                bill_no = $(e).parent().siblings().eq(5).text();
                fee_type = $(e).siblings().eq(0).text();
                if (confirm('Confirm to Delete Collection ' + id_no + '  ' + bill_no + ' on ' + date + '?')) {
                    $.ajax({
                        type: 'post',
                        url: 'temp.php',
                        data: {
                            Action: 'Delete',
                            Id_No: id_no,
                            Date: date,
                            Bill_No: bill_no,
                            Type: type,
                            Fee_Type: fee_type
                        },
                        success: function(data) {
                            console.log(data);
                            if (data == "success") {
                                alert('Payment Deleted Successfully!!');
                            } else if (data == "failure") {
                                alert('Payment Deletion Failed!');
                            } else if (data = "permission") {
                                alert('You don\'t have permission to delete collections');
                            } else {
                                alert('No Collection Found!');
                            }
                        }
                    });
                }
            }
        }
    </script>
</body>

</html>