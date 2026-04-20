<?php
include '../../link.php';
include_once('../includes/rbac_helper.php');

define('MENU_ID', 17);

requireMenuAccess(MENU_ID);

if (isset($_POST['page'])) {
    if (!can('create', MENU_ID)) {
        echo "permission";
        exit;
    }
    $page = $_POST['page'];
    if ($page == "sub") {
        $class = $_POST['Class'];
        $exam = $_POST['Exam'];
        $subject = $_POST['Subject'];
        $max = $_POST['Max'];
        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM class_wise_subjects WHERE Class = '$class' AND Exam = '$exam' AND Subjects = '$subject'")) >= 1) {
            echo "exists";
        } else {
            mysqli_query($link, "INSERT INTO class_wise_subjects VALUES('','$class','$exam','$subject','$max')");
            $sql = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class='$class' AND Exam='$exam'");
            $i = 1;
            while ($row = mysqli_fetch_assoc($sql)) {
                echo '<tr>
                <td>' . $i . '</td>
                <td>' . $row['Class'] . '</td>
                <td>' . $row['Exam'] . '</td>
                <td>' . $row['Subjects'] . '</td>
                <td>' . $row['Max_Marks'] . '</td>
                </tr>';
                $i++;
            }
        }
    }
}
