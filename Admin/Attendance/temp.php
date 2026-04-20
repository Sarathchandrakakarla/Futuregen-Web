<?php
include '../../link.php';
if (isset($_POST['Month'])) {
    $month = $_POST['Month'];
    $sql = mysqli_query($link, "SELECT Working_Days AS Days FROM `working_days` WHERE Month = '$month'");
    while ($row = mysqli_fetch_assoc($sql)) {
        echo $row['Days'];
    }
}

if (isset($_POST['Date'])) {
    $date = $_POST['Date'];
    $sql1 = mysqli_query($link, "SELECT Reason FROM `holidays` WHERE Date = '$date'");
    while ($row1 = mysqli_fetch_assoc($sql1)) {
        echo $row1['Reason'];
    }
}

if (isset($_POST['Id_No']) && isset($_POST['Date']) && isset($_POST['Time'])) {
    $id = $_POST['Id_No'];
    $date = $_POST['Date'];
    $time = $_POST['Time'];
    if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `attendance_daily` WHERE Id_No = '$id' AND Date = '$date'")) == 0) {
        echo "-1";
    } else {
        $sql2 = mysqli_query($link, "UPDATE `attendance_daily` SET $time = '' WHERE Id_No = '$id' AND Date = '$date'");
        if ($sql2) {
            echo "success";
        } else {
            echo "failure";
        }
    }
}
