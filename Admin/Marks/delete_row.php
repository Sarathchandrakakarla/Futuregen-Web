<?php

include_once '../../link.php';
if ($_POST['class'] && $_POST['exam']) {
    $class = $_POST['class'];
    $exam = $_POST['exam'];
    $s = "DELETE FROM `class_wise_examination` WHERE Class = '$class' and Exam = '$exam'";
    $res = mysqli_query($link, $s);
    if ($res) {
        echo "Success";
    } else {
        echo "Failure";
    }
}
if ($_POST['Class'] && $_POST['Exam'] && $_POST['Subject']) {
    $class = $_POST['Class'];
    $exam = $_POST['Exam'];
    $subject = $_POST['Subject'];
    $s = "DELETE FROM `class_wise_subjects` WHERE Class = '$class' and Exam = '$exam' and Subjects = '$subject'";
    $res = mysqli_query($link, $s);
    if ($res) {
        echo "Success";
    } else {
        echo "Failure";
    }
}
