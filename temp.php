<?php
if (isset($_POST['text'])) {
    $myfile = fopen("test.txt", "r");
    if (filesize("test.txt") != 0) {
        $text = fread($myfile, filesize("test.txt"));
        if ($text != "") {
            //$_SESSION['Text'] = $text;
            echo $text;
        } else {
            echo 0;
        }
        fclose($myfile);
    } else {
        echo 0;
    }
}
