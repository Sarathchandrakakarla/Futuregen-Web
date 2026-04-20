<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 64);

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
        max-width: 800px;
        max-height: 500px;
        overflow-x: scroll;
    }

    .detail-container {
        max-width: 900px;
        max-height: 500px;
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    @media screen and (max-width:576px) {
        .container {
            width: 100%;
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

    .delete {
        color: red;
        cursor: pointer;
    }

    .disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <label for="Id" class="col-sm-2 col-form-label"><b>Fee Type:</b></label>
                <div class="col-sm-4">
                    <select name="Type" class="form-select" id="fee_type">
                        <option value="selectfeetype" selected disabled>-- Select Fee Type --</option>
                        <option value="School Fee">School Fee</option>
                        <option value="Examination Fee">Examination Fee</option>
                        <option value="Computer Fee">Computer Fee</option>
                        <option value="Admission Fee">Admission Fee</option>
                        <option value="Vehicle Fee">Vehicle Fee</option>
                        <option value="Book Fee">Book Fee</option>
                        <?php
                        if ($_SESSION['school_db']['school_code'] == "FGS") {
                        ?>
                            <option value="Hostel Fee">Hostel Fee</option>
                        <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <label for="Id" class="col-sm-2 col-form-label"><b>Student Id No</b></label>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="Id_No" id="id" oninput="this.value = this.value.toUpperCase()">
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
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Student Fee Details Report</b></h3>
            </div>
        </div>
    </div>
    <?php
    if (isset($_POST['show'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\\'t have permission to view this report');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }

        if (!$_POST['Type'] || !$_POST['Id_No']) {
            echo "<script>alert('Please select Fee Type and enter Id No');</script>";
            exit;
        }

        $type = $_POST['Type'];
        $id   = $_POST['Id_No'];

        echo "<script>
                document.getElementById('id').value = '$id';
                document.getElementById('fee_type').value = '$type';
            </script>";

        /* =========================================================
        STUDENT DETAILS (MANDATORY)
        ========================================================= */
        $id_sql = mysqli_query(
            $link,
            "SELECT First_Name, Father_Name, Stu_Class, Stu_Section
         FROM student_master_data
         WHERE Id_No='$id'
         LIMIT 1"
        );

        if (mysqli_num_rows($id_sql) == 0) {
            echo "<script>alert('Student Not Found in Student Master Data!')</script>";
            exit;
        }

        $id_row = mysqli_fetch_assoc($id_sql);

        $name        = $id_row['First_Name'];
        $father_name = $id_row['Father_Name'];
        $class       = $id_row['Stu_Class'] . ' ' . $id_row['Stu_Section'];

        /* =========================================================
        TOTAL FEE (PRIMARY → FALLBACK)
        ========================================================= */
        $total = 0;

        $query1 = mysqli_query(
            $link,
            "SELECT Total
         FROM stu_fee_master_data
         WHERE Id_No='$id' AND Type='$type'
         LIMIT 1"
        );

        if (mysqli_num_rows($query1) > 0) {

            $row1 = mysqli_fetch_assoc($query1);
            $total = (int)$row1['Total'];
        } else {

            $query_fb = mysqli_query(
                $link,
                "SELECT Balance
             FROM fee_balances
             WHERE Id_No='$id' AND Type='$type'
             LIMIT 1"
            );

            if (mysqli_num_rows($query_fb) == 0) {
                echo "<script>alert(
                'Student Not Found in Fee Master Data and Fee Balances'
            )</script>";
                exit;
            }

            $row_fb = mysqli_fetch_assoc($query_fb);
            $total = (int)$row_fb['Balance'];
        }

        /* =========================================================
        PAID LOGIC — YOUR ORIGINAL STYLE (UNCHANGED)
        ========================================================= */
        $fee_details = [];
        $sum = 0;

        $query2 = mysqli_query(
            $link,
            "SELECT Bill_No, Fee, DOP
         FROM stu_paid_fee
         WHERE Id_No='$id' AND Type='$type'"
        );

        if (mysqli_num_rows($query2) == 0) {

            $paid = 0;
            $balance = $total;
        } else {

            while ($row2 = mysqli_fetch_assoc($query2)) {

                $sum += (int)$row2['Fee'];

                $temp = [];
                array_push($temp, $row2['Bill_No']);
                array_push($temp, $row2['Fee']);
                array_push($temp, $row2['DOP']);

                array_push($fee_details, $temp);
            }

            $paid = $sum;
            $balance = max(0, (int)$total - (int)$paid);
        }
        echo '
            <div class="container table-container mt-5">
            <table class="table table-striped table-bordered" style="width:100%;">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th style="border:2px solid black;background-color:#6c757d;color:white;" class="text-center" colspan="4">Personal Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Id No.:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $id . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Name:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $name . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Father Name:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $father_name . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Class:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $class . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Type of Fee:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $type . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Total Fee:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $total . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Fee Paid:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $paid . '</td>
                    </tr>
                    <tr>
                        <td style="border-width: 0px 0px 2px 2px;border-color:black;border-style:solid;"><b>Fee Balance:</b></td>
                        <td style="border-width: 0px 2px 2px;border-color:black;border-style:solid;padding-left:5px;" colspan="3">' . $balance . '</td>
                    </tr>
                </tbody>
                <thead class="bg-secondary text-white">
                    <tr>
                        <th style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;background-color:#6c757d;color:white;" class="text-center" colspan="4">Fee Details</th>
                    </tr>
                    <tr class="text-center">
                        <td style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;text-align:center;"><b>Bill No.</b></td>
                        <td style="border-width: 0px 2px 2px 0px;border-color:black;border-style:solid;text-align:center;"><b>Amount</b></td>
                        <td style="border-width: 0px 2px 2px 0px;border-color:black;border-style:solid;text-align:center;"><b>Date of Payment</b></td>
                        <td style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;text-align:center;" class="action"><b>Action</b></td>
                    </tr>
                </thead>
                <tbody class="text-center" id="tbody">';
        if (count($fee_details) == 0) {
            echo '<td style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;text-align:center;" colspan="4">No Payments Yet!</td>';
        }
        foreach ($fee_details as $details) {
            echo '<tr>';
            $i = true;
            foreach ($details as $detail) {
                if ($i) {
                    echo '<td style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;text-align:center;">' . $detail . '</td>';
                } else {
                    echo '<td style="border-width: 0px 2px 2px 0px;border-color:black;border-style:solid;text-align:center;">' . $detail . '</td>';
                }
                $i = false;
            }
            echo '<td style="border-width: 0px 2px 2px 2px;border-color:black;border-style:solid;" class="action">';
            if (can('delete', MENU_ID)) {
                echo '<i class="bx bx-trash delete text-danger" onclick="delete_row(this)"></i>';
            } else {
                echo '<i class="bx bx-trash delete text-secondary disabled"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="You don\'t have permission to delete student fee payments"></i>';
            }
            echo '</td>
            </tr>';
        }
        echo '
                </tbody>
            </table>
        </div>
        ';
    }
    ?>
    <iframe name="print_frame" width="0" height="0" onblur="$('#action').show();" frameborder="0" src="about:blank"></iframe>

    <!-- Delete Row -->
    <script type="text/javascript">
        function delete_row(e) {
            id_no = document.getElementById('id').value;
            bill_no = $(e).parent().siblings().eq(0).text();
            amount = $(e).parent().siblings().eq(1).text();
            date = $(e).parent().siblings().eq(2).text();
            fee_type = document.getElementById('fee_type').value;
            if (confirm('Confirm to Delete Payment ' + id_no + '  ' + bill_no + ' on ' + date + '?')) {
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        Id_No: id_no,
                        Date: date,
                        Bill_No: bill_no,
                        Amount: amount,
                        Fee_Type: fee_type
                    },
                    success: function(data) {
                        if (data == "success") {
                            alert('Payment Deleted Successfully!!');
                        } else if (data == "failure") {
                            alert('Payment Deletion Failed!');
                        } else if (data == "permission") {
                            alert('You don\'t have permission to delete student fee payment details');
                        } else {
                            alert('No Collection Found!');
                        }
                    }
                });
            }
        }
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<div class='container' style='display:flex;margin-left:30px;'><img src='/Futuregen/Images/Victory Logo.png' alt='...' width='80px'><h2 style='margin-left:110px;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2></div>";
            window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Student Fee Details</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].document.querySelectorAll('.action').forEach((ele) => {
                ele.style.display = 'none';
            })
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>