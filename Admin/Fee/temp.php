<?php
include '../../link.php';
include_once('../includes/rbac_helper.php');

if (isset($_POST['route'])) {
    $route = $_POST['route'];
    $sql = mysqli_query($link, "SELECT Fee FROM `actual_fee` WHERE Route = '$route' AND Type = 'Vehicle Fee'");
    if ($sql) {
        if (mysqli_num_rows($sql) == 0) {
            echo '0';
        } else {
            while ($fees = mysqli_fetch_assoc($sql)) {
                $fee = $fees['Fee'];
            }
            echo $fee;
        }
    }
}

if (isset($_POST['Fee_Type'])) {
    if (!can('delete', 64)) {
        echo "permission";
        exit;
    }
    $fee_type = $_POST['Fee_Type'];
    $id_no = $_POST['Id_No'];
    $amount = $_POST['Amount'];
    $bill_no = $_POST['Bill_No'];
    $date = $_POST['Date'];
    $query1 = mysqli_query($link, "SELECT * FROM `stu_paid_fee` WHERE Id_No = '$id_no' AND Fee = '$amount' AND Bill_No = '$bill_no' AND Type = '$fee_type' AND DOP = '$date'");
    if (mysqli_num_rows($query1) == 0) {
        echo "0";
    } else {
        $query2 = mysqli_query($link, "DELETE FROM `stu_paid_fee` WHERE Id_No = '$id_no' AND Fee = '$amount' AND Bill_No = '$bill_no' AND Type = '$fee_type' AND DOP = '$date' LIMIT 1");
        if ($query2) {
            echo "success";
        } else {
            echo "failure";
        }
    }
}
