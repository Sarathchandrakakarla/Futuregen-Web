<?php
include_once '../../link.php';
if ($_POST['class']) {
    $class = $_POST['class'];
    $s = "SELECT * FROM `class_wise_examination` WHERE Class = '$class'";
    $res = mysqli_query($link, $s);
    if (mysqli_num_rows($res) > 0) {
        echo "<option value='selectexam' disabled selected>--Select Exam--</option>";
        while ($r = mysqli_fetch_assoc($res)) {
            echo "<option value='" . $r['Exam'] . "'>" . $r['Exam'] . "</option>";
        }
    } else {
        echo "<option selected disabled>No Exam Found</option>";
    }
} else if ($_POST['Id']) {
    $id = $_POST['Id'];
    $sql = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id'");
    if (mysqli_num_rows($sql) == 0) {
        echo "<option selected disabled>No Exam Found</option>";
    } else {
        echo "<option value='selectexam' disabled selected>--Select Exam--</option>";
        while ($r = mysqli_fetch_assoc($sql)) {
            echo "<option value='" . $r['Exam'] . "'>" . $r['Exam'] . "</option>";
        }
    }
}
