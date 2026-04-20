<?php

include '../../link.php';
if ($_POST['class'] && $_POST['exam'] && $_POST['max']) {
    $class = $_POST['class'];
    $exam = $_POST['exam'];
    $max = $_POST['max'];
    $s = "UPDATE `class_wise_examination` SET Max_Marks = $max WHERE Class = '$class' and Exam = '$exam'";
    $res = mysqli_query($link, $s);
    if ($res) {
        echo "Success";
    } else {
        echo "Failure";
    }
}

if ($_POST['Class'] && $_POST['Exam'] && $_POST['Max'] && $_POST['sub']) {
    $class = $_POST['Class'];
    $exam = $_POST['Exam'];
    $sub = $_POST['sub'];
    $max = $_POST['Max'];
    $s = "UPDATE `class_wise_subjects` SET Max_Marks = $max WHERE Class = '$class' and Exam = '$exam' AND Subjects = '$sub'";
    $res = mysqli_query($link, $s);
    if ($res) {
        echo "Success";
    } else {
        echo "Failure";
    }
}
