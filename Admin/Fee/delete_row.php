<?php
include_once '../../link.php';
if ($_POST['Id_No']) {
    if (!can('delete', 67)) {
        echo "permission";
        exit;
    }
    $id = $_POST['Id_No'];
    $sql1 = mysqli_query($link, "DELETE FROM `vvip` WHERE Id_No = '$id'");
    if ($sql1) {
        echo 1;
        return;
    } else {
        echo 0;
        return;
    }
}
