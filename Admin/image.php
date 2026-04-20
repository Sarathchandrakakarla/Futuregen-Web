<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 94);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/sidebar-style.css" />
    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
    body {
        height: 1000px;
    }

    #choose {
        position: relative;
        overflow: hidden;
    }

    .file {
        cursor: pointer;
        position: absolute;
        transform: scale(3);
        opacity: 0;
    }

    .img-row {
        padding: 5%;
        border: 4px dashed grey;
    }

    .img-container i {
        font-size: 5rem;
    }

    @media screen and (max-width:600px) {
        .img-container i {
            margin-left: 150px;
            font-size: 3rem;
        }

        #choose {
            margin-left: 120px;
        }

        .btn-container {
            margin-left: 150px;
        }

        #img_type {
            width: 200px;
            margin-left: 30%;
        }

        .instruction-container {
            width: 220px;
        }

    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>
    <form action="" method="POST" enctype="multipart/form-data" onsubmit="return upload">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="text-light col-lg-4 rounded">
                    <select class="form-select" id="img_type" name="img_type" aria-label="Default select example">
                        <option value="" selected disabled>-- Select Image Type --</option>
                        <option value="Home">Home Images</option>
                        <option value="Gallery">Gallery Images</option>
                        <option value="Student">Student Images</option>
                        <option value="Employee">Employee Images</option>
                        <option value="Parent_Male">Male Parent Images</option>
                        <option value="Parent_Female">Female Parent Images</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="container instruction-container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <strong style="color:red;">Don't Close or Refresh.Please Wait While Processing Till Alert!!</strong>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <h5><strong>Instructions</strong></h5>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <label for="home">For Home Images</label>
                    <ul>
                        <li>Maximum Files = 5</li>
                        <li>File Dimensions : Width:2000px, Height: 843px</li>
                        <li>Name Convention: event1,event2</li>
                    </ul>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <label for="home">For Gallery Images</label>
                    <ul>
                        <li>Maximum Files = 21</li>
                        <li>File Dimensions : Width:900px, Height: Proportional to Width</li>
                        <li>Name Convention: event1,event2</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php $canCreate = can('create', MENU_ID); ?>
        <div class="container img-container">
            <div class="row img-row justify-content-center mt-5">
                <div class="col-lg-2">
                    <div class="btn-wrapper <?= !$canCreate ? 'disabled-wrapper' : '' ?>"
                        <?= !$canCreate ? 'title="You don\'t have permission to upload images"' : '' ?>>
                        <i class="bx bx-image-add"></i>
                        <p>
                            <button class="btn btn-primary" id="choose" <?= !$canCreate ? 'disabled' : '' ?>>Choose Files
                                <input type="file" class="file" name="img[]" <?= !$canCreate ? 'disabled' : '' ?>
                                    <?= $canCreate ? 'required' : '' ?> accept=".jpg,.jpeg" multiple>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container btn-container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to upload images"
                        <?php } ?>>
                        <button class="btn btn-primary upload" type="submit" name="upload" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>><i class="bx bx-upload"></i>Upload</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <?php

    if (isset($_POST['upload'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to upload images');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $type = $_POST['img_type'];
        $new_file_name = array();
        $flag = false;
        if ($type == "Home") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Images/slides/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        } else if ($type == "Gallery") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Gallery/Images/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        } else if ($type == "Student") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Images/stu_img/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        } else if ($type == "Employee") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Images/emp_img/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        } else if ($type == "Parent_Male") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Images/parent_img_male/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        } else if ($type == "Parent_Female") {
            foreach ($_FILES['img']['name'] as $img_name) {
                array_push($new_file_name, substr($img_name, 0, strrpos($img_name, ".")));
            }
            $i = 0;
            foreach ($new_file_name as $new_name) {
                $filename = $new_name . ".jpg";
                $location = "../Images/parent_img_female/" . $filename;
                if (move_uploaded_file($_FILES['img']['tmp_name'][$i], $location)) {
                    $i++;
                    $flag = true;
                } else {
                    $flag = false;
                    break;
                }
            }
            if ($flag) {
                echo '<script>alert("Successfully Uploaded")</script>';
            } else {
                echo '<script>alert("Upload Failed")</script>';
            }
        }
    }

    ?>

    <!-- Scripts -->

    <!-- Limit File Upload -->
    <script>
        $(function upload() {
            $(".upload").click(function() {
                var $fileUpload = $(".file");
                var $fileType = document.querySelector('#img_type').value;
                if ($fileType == '') {
                    alert("Select Image Type");
                    return false;
                } else if ($fileType == 'Home') {
                    if (parseInt($fileUpload.get(0).files.length) > 5) {
                        alert("You are only allowed to upload a maximum of 5 files");
                        return false;
                    } else {
                        return true;
                    }
                } else if ($fileType == 'Gallery') {
                    if (parseInt($fileUpload.get(0).files.length) > 21) {
                        alert("You are only allowed to upload a maximum of 21 files");
                        return false;
                    } else {
                        return true;
                    }
                }
            });
        });
    </script>
</body>

</html>