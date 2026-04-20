<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 7);

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
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 900px;
        max-height: 500px;
        overflow-x: scroll;
    }

    #section {
        text-align: center;
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
        }
    }

    @media print {
        * {
            display: none;
        }

        #table-container {
            display: block;
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

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <form action="" method="post">
            <div class="row mt-5 justify-content-center">
                <div class="col-lg-2">Template Id</div>
                <div class="col-lg-3">
                    <input type="number" class="form-control" name="T_Id" id="t_id" required>
                </div>
            </div>
            <div class="row mt-3 justify-content-center">
                <div class="col-lg-2">Template Type</div>
                <div class="col-lg-3">
                    <select name="T_Type" id="t_type" class="form-control" onchange="media_row.hidden = (this.value == 'Media') ? '':'hidden'">
                        <option value="" selected disabled>-- Select Template Type --</option>
                        <option value="Text">Text</option>
                        <option value="Media">Media</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3 justify-content-center" id="media_row" hidden>
                <div class="col-lg-2">Media Type</div>
                <div class="col-lg-3">
                    <select name="M_Type" id="m_type" class="form-control">
                        <option value="" selected disabled>-- Select Media Type --</option>
                        <option value="Document">Document</option>
                        <option value="Image">Image</option>
                        <option value="Video">Video</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3 justify-content-center">
                <div class="col-lg-2">No. of Placeholders</div>
                <div class="col-lg-3">
                    <input type="number" name="No_Of_Placeholders" class="form-control" id="no_of_placeholders" min="0" max="10">
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-4">
                    <button class="btn btn-primary" name="Insert" type="submit">Insert</button>
                    <button class="btn btn-primary" name="Find" type="submit">Find</button>
                    <button class="btn btn-success" name="Update" type="submit" onclick="if(!confirm('Confirm to Update the Template?')){return false;}else{return true;}">Update</button>
                    <button class="btn btn-danger" name="Delete" type="submit" onclick="if(!confirm('Confirm to Delete the Template?')){return false;}else{return true;}">Delete</button>
                    <button class="btn btn-warning" type="reset" onclick="document.getElementById('media_row').hidden = 'hidden';">Clear</button>
                </div>
            </div>
        </form>

        <?php
        if (isset($_POST['Insert'])) {
            $T_Id = $_POST['T_Id'];
            echo "<script>document.getElementById('t_id').value = '" . $T_Id . "'</script>";
            if ($_POST['T_Type']) {
                $T_Type = $_POST['T_Type'];
                echo "<script>document.getElementById('t_type').value = '" . $T_Type . "'</script>";
                $flag = true;
                if ($T_Type == "Media") {
                    if ($_POST['M_Type']) {
                        $M_Type = $_POST['M_Type'];
                        echo "<script>
                                document.getElementById('media_row').hidden = '';
                                document.getElementById('m_type').value = '" . $M_Type . "';
                              </script>";
                    } else {
                        $flag = false;
                        echo "<script>alert('Please Select Media Type;')</script>";
                    }
                } else {
                    echo "<script>document.getElementById('media_row').hidden = 'hidden';</script>";
                }
                if ($flag) {
                    if ($_POST['No_Of_Placeholders']) {
                        $No_Of_Placeholders = $_POST['No_Of_Placeholders'];
                        echo "<script>document.getElementById('no_of_placeholders').value = '" . $No_Of_Placeholders . "'</script>";
                        if (strlen($T_Id) != 6) {
                            echo "<script>alert('Template Id must be 6 digits long!')</script>";
                        } else {
                            if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `whatsapp_templates` WHERE T_Id = '$T_Id'")) != 0) {
                                echo "<script>alert('Template already exists')</script>";
                            } else {
                                if ($T_Type == "Text") {
                                    $query = "INSERT INTO `whatsapp_templates`(T_Id, T_Type,Placeholders) VALUES ('$T_Id','$T_Type','$No_Of_Placeholders')";
                                } else {
                                    $query = "INSERT INTO `whatsapp_templates`(T_Id, T_Type,M_Type, Placeholders) VALUES ('$T_Id','$T_Type','$M_Type','$No_Of_Placeholders')";
                                }
                                if (mysqli_query($link, $query)) {
                                    echo "<script>alert('Template Inserted Successfully')</script>";
                                } else {
                                    echo "<script>alert('Template Insertion Failed')</script>";
                                }
                            }
                        }
                    } else {
                        echo "<script>alert('Please Enter No Of Placeholders!')</script>";
                    }
                }
            } else {
                echo "<script>alert('Please Select Template Type!')</script>";
            }
        }
        if (isset($_POST['Find'])) {
            $T_Id = $_POST['T_Id'];
            echo "<script>document.getElementById('t_id').value = '" . $T_Id . "';</script>";
            $query = mysqli_query($link, "SELECT * FROM `whatsapp_templates` WHERE T_Id = '$T_Id'");
            if (mysqli_num_rows($query) == 0) {
                echo "<script>alert('Template Not Found')</script>";
            } else {
                $row = mysqli_fetch_assoc($query);
                echo "<script>document.getElementById('t_type').value = '" . $row['T_Type'] . "'</script>";
                if ($row['T_Type'] == "Media") {
                    echo "
                    <script>
                    document.getElementById('media_row').hidden = '';
                    document.getElementById('m_type').value = '" . $row['M_Type'] . "';
                    </script>";
                }
                echo "<script>document.getElementById('no_of_placeholders').value = '" . $row['Placeholders'] . "'</script>";
            }
        }
        if (isset($_POST['Update'])) {
            $T_Id = $_POST['T_Id'];
            echo "<script>document.getElementById('t_id').value = '" . $T_Id . "'</script>";
            if ($_POST['T_Type']) {
                $T_Type = $_POST['T_Type'];
                echo "<script>document.getElementById('t_type').value = '" . $T_Type . "'</script>";
                $flag = true;
                if ($T_Type == "Media") {
                    if ($_POST['M_Type']) {
                        $M_Type = $_POST['M_Type'];
                        echo "<script>
                                document.getElementById('media_row').hidden = '';
                                document.getElementById('m_type').value = '" . $M_Type . "';
                              </script>";
                    } else {
                        $flag = false;
                        echo "<script>alert('Please Select Media Type;')</script>";
                    }
                } else {
                    echo "<script>document.getElementById('media_row').hidden = 'hidden';</script>";
                }
                if ($flag) {
                    if ($_POST['No_Of_Placeholders']) {
                        $No_Of_Placeholders = $_POST['No_Of_Placeholders'];
                        echo "<script>document.getElementById('no_of_placeholders').value = '" . $No_Of_Placeholders . "'</script>";
                        if (strlen($T_Id) != 6) {
                            echo "<script>alert('Template Id must be 6 digits long!')</script>";
                        } else {
                            if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `whatsapp_templates` WHERE T_Id = '$T_Id'")) == 0) {
                                echo "<script>alert('Template Not Found')</script>";
                            } else {
                                if ($T_Type == "Text") {
                                    $query = "UPDATE `whatsapp_templates` SET T_Type = '$T_Type',Placeholders = '$No_Of_Placeholders' WHERE T_Id = '$T_Id'";
                                } else {
                                    $query = "UPDATE `whatsapp_templates` SET T_Type = '$T_Type',M_Type = '$M_Type',Placeholders = '$No_Of_Placeholders' WHERE T_Id = '$T_Id'";
                                }
                                if (mysqli_query($link, $query)) {
                                    echo "<script>alert('Template Updated Successfully')</script>";
                                } else {
                                    echo "<script>alert('Template Updation Failed')</script>";
                                }
                            }
                        }
                    } else {
                        echo "<script>alert('Please Enter No Of Placeholders!')</script>";
                    }
                }
            } else {
                echo "<script>alert('Please Select Template Type!')</script>";
            }
        }
        if (isset($_POST['Delete'])) {
            $T_Id = $_POST['T_Id'];
            if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `whatsapp_templates` WHERE T_Id = '$T_Id'")) == 0) {
                echo "<script>alert('Template Not Found')</script>";
            } else {
                $query = "DELETE FROM `whatsapp_templates` WHERE T_Id = '$T_Id'";
                if (mysqli_query($link, $query)) {
                    echo "<script>alert('Template Deleted Successfully')</script>";
                } else {
                    echo "<script>alert('Template Deletion Failed')</script>";
                }
            }
        }
        ?>
    </div>

</body>

</html>