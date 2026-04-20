<?php
include '../../link.php';
if (isset($_POST['Action']) && $_POST['Action'] == "Get_Details") {
    $id = $_POST['Id_No'];
    $query = mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$id'");
    if (mysqli_num_rows($query) == 0) {
        print_r(json_encode(["success" => false, "message" => "No Employee Found with Id No. " . $id]));
        return;
    } else {
        $emp_name = mysqli_fetch_all($query)[0][0];
        print_r(json_encode(["success" => true, "data" => [$emp_name]]));
        return;
    }
}
