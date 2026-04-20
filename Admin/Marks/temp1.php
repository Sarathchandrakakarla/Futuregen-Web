<?php
include_once '../../link.php';
if ($_POST['stu_class'] && $_POST['exam']) {
    $cls = $_POST['stu_class'];
    $exm = $_POST['exam'];
    $sq = "SELECT * FROM `class_wise_examination` WHERE Class = '$cls' AND Exam = '$exm'";
    $re = mysqli_query($link, $sq);
    if (mysqli_num_rows($re) > 0) {
        while ($row = mysqli_fetch_assoc($re)) {
            echo $row['Max_Marks'];
        }
    } else {
        echo "0";
    }
}
