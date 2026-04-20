<?php
include '../link.php';
if (isset($_POST['Type']) && isset($_POST['Id'])) {
    $id = $_POST['Id'];
    $type = $_POST['Type'];
    if ($type == "Student") {
        $sql = mysqli_query($link, "SELECT First_Name AS Name FROM `student_master_data` WHERE Id_No = '$id'");
        $sql1 = mysqli_query($link, "SELECT Stu_Password AS Pass,Status FROM `student` WHERE Id_No = '$id'");
    } else if ($type == "Faculty") {
        $sql = mysqli_query($link, "SELECT Emp_First_Name AS Name FROM `employee_master_data` WHERE Emp_Id = '$id'");
        $sql1 = mysqli_query($link, "SELECT Password AS Pass,Role FROM `faculty` WHERE Id_No = '$id'");
    }
    if ($sql) {

        //Fetching Name of User from Master_data
        if (mysqli_num_rows($sql) == 0) {
            echo "0";
        } else {
            while ($row = mysqli_fetch_assoc($sql)) {
                echo $row['Name'] . ',';
            }
        }


        if ($sql1) {
            if (mysqli_num_rows($sql1) != 0) {
                while ($row1 = mysqli_fetch_assoc($sql1)) {
                    echo $row1['Pass'] . ',';
                    if ($type == "Faculty") {
                        echo $row1['Role'] . ',';
                    } else if ($type == "Student") {
                        echo $row1['Status'] . ',';
                    }
                }
            }
        }
    }
}


if (isset($_POST['Id_No'])) {
    $id = $_POST['Id_No'];
    $sql = mysqli_query($link, "SELECT Admin_Name AS Name,Mobile,Admin_Password,Role FROM `admin` WHERE Admin_Id_No = '$id'");
    if ($sql) {
        if (mysqli_num_rows($sql) == 0) {
            echo "0";
        } else {
            while ($row = mysqli_fetch_assoc($sql)) {
                $arr = array($row['Name'], $row['Mobile'], $row['Admin_Password'], $row['Admin_Password'], $row['Role']);
                foreach ($arr as $a) {
                    echo $a . ',';
                }
            }
        }
    }
}

if (isset($_POST['text'])) {
    $myfile = fopen("../test.txt", "r");
    if (filesize("../test.txt") != 0) {
        $text = fread($myfile, filesize("../test.txt"));
        if ($text != "") {
            echo $text;
        }
        fclose($myfile);
    }
}

if (isset($_POST['Video_Id'])) {
    $video_id = $_POST['Video_Id'];
    $flag = false;
    if ($_POST['Action'] == "Delete_Video") {
        $s = "DELETE FROM `youtube` WHERE Video_Id = '$video_id'";
        $res = mysqli_query($link, $s);
        if ($res) {
            $flag = true;
        } else {
            $flag = false;
        }
    } else if ($_POST['Action'] == "Edit_Title") {
        if (isset($_POST['Video_Title'])) {
            $video_title = $_POST['Video_Title'];
            $s = "UPDATE `youtube` SET Video_Title = '$video_title' WHERE Video_Id = '$video_id'";
            $res = mysqli_query($link, $s);
            if ($res) {
                $flag = true;
            } else {
                $flag = false;
            }
        } else {
            $flag = false;
        }
    }
    if ($flag) {
        echo "Success";
    } else {
        echo "Failure";
    }
}

if (isset($_POST['Group'])) {
    $group = $_POST['Group'];
    $s = "DELETE FROM `topics` WHERE Topic = '$group'";
    $res = mysqli_query($link, $s);
    if ($res) {
        echo "Success";
    } else {
        echo "Failure";
    }
}
