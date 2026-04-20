<?php
session_start();
$filename = $_SESSION['Admin_Id_No'] . ".jpg";
$location = "../Images/admin_img/" . $filename;
if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
    echo 'Success';
} else {
    echo 'Failure';
}
