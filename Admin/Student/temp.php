<?php

include '../../link.php';
if (isset($_POST['Id_No'])) {
    $id = $_POST['Id_No'];
    $sql = mysqli_query($link, "SELECT Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '$id'");
    if (mysqli_num_rows($sql) == 0) {
        echo '0';
    } else {
        while ($row = mysqli_fetch_assoc($sql)) {
            echo $row['Stu_Class'] . ' ' . $row['Stu_Section'];
        }
    }
}
