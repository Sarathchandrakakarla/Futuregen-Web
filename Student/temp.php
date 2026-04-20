<?php
include '../link.php';
session_start();
$id = $_SESSION['Id_No'];
$sql = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id'");
if (mysqli_num_rows($sql) == 0) {
    echo "<option selected disabled>No Exam Found</option>";
} else {
    echo "<option value='selectexam' disabled selected>--Select Exam--</option>";
    while ($r = mysqli_fetch_assoc($sql)) {
        echo "<option value='" . $r['Exam'] . "'>" . $r['Exam'] . "</option>";
    }
}
