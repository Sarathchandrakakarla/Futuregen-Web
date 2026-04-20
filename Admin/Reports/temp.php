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
/*
if($_POST['stu_class'] && $_POST['exam']){
    $cls = $_POST['stu_class'];
    $exm = $_POST['exam'];
    $sq = "SELECT * FROM `class_wise_examination` WHERE Class = '$cls' AND Exam = '$exm'";
    $re = mysqli_query($link,$sq);
    if(mysqli_num_rows($re)>0){
        while($row = mysqli_fetch_assoc($re)){
            echo $row['Max_Marks'];
        }
    }
    else{
        echo "Max Marks not Found";
    }
}
*/
