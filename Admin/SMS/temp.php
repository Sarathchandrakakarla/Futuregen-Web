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
}
