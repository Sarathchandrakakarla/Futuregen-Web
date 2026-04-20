<?php
include '../link.php';
// Your database connection code here
// Get the current date
$date = date('d-m');

// Assuming you have a "birthdays" table with columns "name" and "date"
$query = "SELECT First_Name,Mobile FROM `student_master_data` WHERE (Stu_Class LIKE '% CLASS%' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG') AND DOB LIKE '$date%'";
$result = mysqli_query($link, $query);

// Fetch all the birthdays from the database
$birthdays = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close the database connection


// Iterate through each birthday
foreach ($birthdays as $birthday) {
    $name = $birthday['First_Name'];
    $mobile = $birthday['Mobile'];
    if (str_contains($mobile, ",")) {
        $mobile = explode(',', $mobile)[0];
    } else if (str_contains($mobile, " ")) {
        $mobile = explode(' ', $mobile)[0];
    }
    sendMessage($name, $mobile, $link);
}

// Function to send the birthday message
function sendMessage($name, $mobile, $link)
{
    // Add your code here to send the birthday message
    // This could be an email, a notification, or any other method you prefer
    // For simplicity, we'll just print the message to the console
    echo "Happy birthday, $name!\n";
    mysqli_query($link, "INSERT INTO `birthday` VALUES('','$name','$mobile')");
}
mysqli_close($link);
