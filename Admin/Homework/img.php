<?php
foreach (scandir('./') as $file) {
    if (str_contains($file, "temp_img")) {
        unlink($file);
    }
}
for ($i = 1; $i <= $_POST['Image_Count']; $i++) {
    $filename = "temp_img" . $i;
    $extension = explode('.', $_FILES['Image' . $i]['name'])[1];
    $location = $filename . "." . $extension;

    if (move_uploaded_file($_FILES['Image' . $i]['tmp_name'], $location)) {
        echo 'Success,' . $extension . '|';
    } else {
        echo 'Failure';
    }
}
