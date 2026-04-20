<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

if (isset($_POST['Action']) && $_POST['Action'] == "View" && isset($_POST['Date']) && isset($_POST['Type'])) {
    if (!can('view', 69)) {
        echo "permission";
        exit;
    }
    $date = $_POST['Date'];
    $type = $_POST['Type'];
    $d = explode('-', $date);
    $t = $d[0];
    $d[0] = $d[2];
    $d[2] = $t;
    switch ($d[1]) {
        case '1':
            $d[1] = "Jan";
            break;
        case '2':
            $d[1] = "Feb";
            break;
        case '3':
            $d[1] = "Mar";
            break;
        case '4':
            $d[1] = "Apr";
            break;
        case '5':
            $d[1] = "May";
            break;
        case '6':
            $d[1] = "Jun";
            break;
        case '7':
            $d[1] = "Jul";
            break;
        case '8':
            $d[1] = "Aug";
            break;
        case '9':
            $d[1] = "Sep";
            break;
        case '10':
            $d[1] = "Oct";
            break;
        case '11':
            $d[1] = "Nov";
            break;
        case '12':
            $d[1] = "Dec";
            break;
    }
    $date = implode('-', $d);
    if ($type == "Expenditure") {
        $sql = mysqli_query($link, "SELECT td.AC_No,dmd.Name,td.* FROM `tran_details` td JOIN `debiter_master_data` dmd ON td.AC_No = dmd.AC_No WHERE DOP = '$date'");
        if ($sql) {
            if (mysqli_num_rows($sql) == 0) {
                echo "";
            } else {
                $details = array();
                $total = 0;
                while ($row = mysqli_fetch_assoc($sql)) {
                    $temp = array();
                    array_push($temp, $row['AC_No']);
                    array_push($temp, $row['Name']);
                    array_push($temp, $row['Amount']);
                    array_push($temp, $row['Purpose']);
                    array_push($temp, $row['Bill_No']);
                    $total += (int)$row['Amount'];
                    array_push($details, $temp);
                }
                $text = "<tr>";
                $i = 1;
                foreach ($details as $detail) {
                    $text .= "<td>" . $i . "</td>";
                    foreach ($detail as $col) {
                        $text .= "<td style='text-align:center;'>" . $col . "</td>";
                    }
                    $text .= "<td style='text-align:center;'><i class='bx bx-trash delete' onclick='delete_row(this)'></i></td>";
                    $text .= "</tr>";
                    $i++;
                }
                $text .= "
                <tr>
                    <td colspan='3' style='text-align:center;'><b>Total</b></td>
                    <td style='text-align:center;'><b>" . $total . "</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                ";

                echo $text;
            }
        }
    } else if ($type == "Collection") {
        $sql = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Type = 'School Fee' AND DOP = '$date'");
        $sql1 = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Type = 'Vehicle Fee' AND DOP = '$date'");
        $fee_types = array('School Fee', 'Vehicle Fee', 'Admission Fee', 'Computer fee', 'Examination Fee');
        if ($_SESSION['school_db']['school_code'] == "FGS") {
            $fee_types[] = 'Hostel Fee';
        }
        $grand_total = 0;
        $grand_cash_total = 0;
        $grand_upi_total = 0;
        $text = "<tr>";
        foreach ($fee_types as $type) {
            $sql = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Type = '$type' AND DOP = '$date'");
            if ($sql) {
                if (mysqli_num_rows($sql) > 0) {
                    $cash_total = 0;
                    $upi_total = 0;
                    $total = 0;
                    $details = array();
                    while ($row = mysqli_fetch_assoc($sql)) {
                        $temp = array();
                        array_push($temp, $row['Id_No']);
                        array_push($temp, $row['First_Name']);
                        array_push($temp, $row['Class'] . ' ' . $row['Section']);
                        array_push($temp, $row['Fee']);
                        array_push($temp, $row['Bill_No']);
                        array_push($temp, $row['Payment_Type']);
                        if ($row['Payment_Type'] == "Cash") {
                            $cash_total += (int)$row['Fee'];
                        } else if ($row['Payment_Type'] == "UPI") {
                            $upi_total += (int)$row['Fee'];
                        }
                        $total += (int)$row['Fee'];
                        array_push($details, $temp);
                    }
                    $grand_cash_total += $cash_total;
                    $grand_upi_total += $upi_total;
                    $grand_total += $total;
                    $text .= "
                <tr>
                    <td colspan='8' style='text-align:center'><b>" . $type . "</b></td>
                </tr>
                <tr>";
                    $i = 1;
                    foreach ($details as $detail) {
                        $text .= "<td>" . $i . "</td>";
                        foreach ($detail as $col) {
                            $text .= "<td style='padding-left:20px;text-align:center;white-space:nowrap;'>" . $col . "</td>";
                        }
                        $text .= "<td><label style='opacity:0' id='fee_type'>" . $type . "</label><i class='bx bx-trash delete' onclick='delete_row(this)'></i></td>";
                        $text .= "</tr>";
                        $i++;
                    }
                    $text .= "
                <tr>
                    <td></td>
                    <td></td>
                    <td style='text-align:center;'><b>Cash Total</b></td>
                    <td style='text-align:center;'><b>" . $cash_total . "</b></td>
                    <td></td>
                    <td style='text-align:center;'><b>UPI Total</b></td>
                    <td style='text-align:center;'><b>" . $upi_total . "</b></td>
                    <td></td>
                </tr>";
                    $text .= "
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style='text-align:center;'><b>Total</b></td>
                    <td style='text-align:center;'><b>" . $total . "</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>";
                }
            }
        }
        $text .= "
                <tr>
                    <td></td>
                    <td></td>
                    <td style='text-align:center;'><b>Grand Cash Total</b></td>
                    <td style='text-align:center;'><b>" . ($grand_cash_total) . "</b></td>
                    <td></td>
                    <td style='text-align:center;white-space:nowrap;'><b>Grand UPI Total</b></td>
                    <td style='text-align:center;'><b>" . ($grand_upi_total) . "</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style='text-align:center;white-space:nowrap;'><b>Grand Total</b></td>
                    <td style='text-align:center;'><b>" . ($grand_total) . "</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                ";
        $debiter_query = mysqli_query($link, "SELECT * FROM `tran_details` WHERE DOP = '$date' AND AC_No = 'VHDB051'");
        $upi_amount = 0;
        if (mysqli_num_rows($debiter_query) != 0) {
            while ($debiter_row = mysqli_fetch_assoc($debiter_query)) {
                $upi_amount = $debiter_row['Amount'];
            }
        }
        $text .= "
            <tr>
                <td colspan='8'></td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td style='text-align:center;white-space:nowrap;'><b>Student UPI Amount</b></td>
                <td><b>" . $upi_amount . "</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            ";

        echo $text;
        /*
        if ($sql && $sql1) {
            if (mysqli_num_rows($sql) == 0 && mysqli_num_rows($sql1) == 0) {
                echo "";
            } else {
                $text = "";
                
                $vehicle_total = 0;
                
                if (mysqli_num_rows($sql1) > 0) {
                    $vehicle_details = array();
                    while ($row = mysqli_fetch_assoc($sql1)) {
                        $temp = array();
                        array_push($temp, $row['Id_No']);
                        array_push($temp, $row['First_Name']);
                        array_push($temp, $row['Class'] . ' ' . $row['Section']);
                        array_push($temp, $row['Fee']);
                        array_push($temp, $row['Bill_No']);
                        $vehicle_total += (int)$row['Fee'];
                        array_push($vehicle_details, $temp);
                    }
                    $text .= "
                <tr>
                    <td colspan='6' style='text-align:center'><b>Vehicle Fee</b></td>
                </tr>
                <tr>";
                    $i = 1;
                    foreach ($vehicle_details as $vehicle_detail) {
                        $text .= "<td>" . $i . "</td>";
                        foreach ($vehicle_detail as $col) {
                            $text .= "<td>" . $col . "</td>";
                        }
                        $text .= "<td><label style='opacity:0' id='fee_type'>Vehicle Fee</label><i class='bx bx-trash delete' onclick='delete_row(this)'></i></td>";
                        $text .= "</tr>";
                        $i++;
                    }
                    $text .= "
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    
                    <td><b>Total</b></td>
                    <td><b>" . $vehicle_total . "</b></td>
                    
                </tr>";
                }
                $text .= "
                <tr>
                    <td></td>
                    <td></td>
                    <td style='text-align:center;'><b>Grand Total</b></td>
                    <td><b>" . ($total + $vehicle_total) . "</b></td>
                    <td></td>
                    <td></td>
                </tr>
                ";
                echo $text;
            }
        }
        */
    }
}

if (isset($_POST['Action']) && $_POST['Action'] == "View" && isset($_POST['AC_No'])) {
    if (!can('view', 70)) {
        echo "permission";
        exit;
    }
    $ac = $_POST['AC_No'];
    $query1 = mysqli_query($link, "SELECT * FROM `debiter_master_data` WHERE AC_No = '$ac'");
    if (mysqli_num_rows($query1) == 0) {
        echo "0";
    } else {
        $txt = "";
        while ($row1 = mysqli_fetch_assoc($query1)) {
            $txt .= $row1['AC_No'] . ',';
            $txt .= $row1['Name'] . ',';
            $txt .= $row1['Address'] . ',';
            $txt .= $row1['Mobile'] . ',';
            $txt .= $row1['Amount'] . ',';
            $txt .= $row1['DOC'];
        }
        echo $txt;
    }
}

if (isset($_POST['Action']) && $_POST['Action'] == "Delete" && isset($_POST['Type'])) {
    if (!can('delete', 69)) {
        echo "permission";
        exit;
    }
    $type = $_POST['Type'];
    if ($type == "expenses") {
        $ac_no = $_POST['ac_No'];
        $amount = $_POST['Amount'];
        $purpose = $_POST['Purpose'];
        $date = $_POST['Date'];
        $query1 = mysqli_query($link, "SELECT * FROM `tran_details` WHERE AC_No = '$ac_no' AND Amount = '$amount' AND Purpose = '$purpose' AND DOP = '$date'");
        if (mysqli_num_rows($query1) == 0) {
            echo "0";
        } else {
            $query2 = mysqli_query($link, "DELETE FROM `tran_details` WHERE AC_No = '$ac_no' AND Amount = '$amount' AND Purpose = '$purpose' AND DOP = '$date' LIMIT 1");
            if ($query2) {
                echo "success";
            } else {
                echo "failure";
            }
        }
    } else if ($type == "collections") {
        $fee_type = $_POST['Fee_Type'];
        $id_no = $_POST['Id_No'];
        $bill_no = $_POST['Bill_No'];
        $date = $_POST['Date'];
        $query1 = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Id_No = '$id_no' AND Bill_No = '$bill_no' AND Type = '$fee_type' AND DOP = '$date'");
        if (mysqli_num_rows($query1) == 0) {
            echo "0";
        } else {
            $query2 = mysqli_query($link, "DELETE FROM `stu_paid_fee` WHERE Id_No = '$id_no' AND Bill_No = '$bill_no' AND Type = '$fee_type' AND DOP = '$date' LIMIT 1");
            if ($query2) {
                echo "success";
            } else {
                echo "failure";
            }
        }
    }
}
