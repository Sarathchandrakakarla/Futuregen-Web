<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 57);

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
    <link rel="stylesheet" href="../../css/sidebar-style.css" />
    <link rel="stylesheet" href="../../css/style.css">
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
        background: #E4E9F7;
    }

    .container {
        max-width: 550px;
    }

    .wrapper {
        height: 550px;
    }

    .button1 {
        margin-top: 10px;
    }

    #sign-out {
        display: none;
    }

    #fee {
        padding-left: 10px;
    }

    /* 🔒 Disabled state */
    form .button input:disabled {
        cursor: not-allowed;
        opacity: 0.6;
        background: linear-gradient(135deg, #b5b5b5, #8e8e8e);
    }

    .btn-wrapper {
        display: contents;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    @media screen and (max-width:700px) {
        .container {
            margin-left: 70px;
            max-width: 340px;
        }
    }
</style>

<body>
    <?php
    include '../sidebar.php';
    ?>
    <div class="container">
        <div class="wrapper">
            <div class="title"><span>Actual Fee Entry</span></div>
            <form action="" method="post" autocomplete="off">
                <div class="row justify-content-center">
                    <div class="col-lg-4">
                        <label for="Fee_type"><b>Fee Type:</b></label>
                    </div>
                    <div class="col-lg-6">
                        <select name="Fee_Type" class="form-select" id="fee_type">
                            <option value="selectfeetype" selected disabled>-- Select Fee Type --</option>
                            <option value="School Fee">School Fee</option>
                            <option value="Examination Fee">Examination Fee</option>
                            <option value="Computer Fee">Computer Fee</option>
                            <option value="Admission Fee">Admission Fee</option>
                            <option value="Vehicle Fee">Vehicle Fee</option>
                            <option value="Book Fee">Book Fee</option>
                            <?php
                            if ($_SESSION['school_db']['school_code'] == "FGS") {
                            ?>
                                <option value="Hostel Fee">Hostel Fee</option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center" id="cls_row" hidden>
                    <div class="col-lg-4">
                        <label for="Route"><b>Class:</b></label>
                    </div>
                    <div class="p-2 col-lg-6 rounded">
                        <select class="form-select" name="Class" id="class" aria-label="Default select example">
                            <option selected disabled>-- Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 10; $i++) {
                                echo "<option value='" . $i . " CLASS'>" . $i . " CLASS</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center" id="route_row" hidden>
                    <div class="col-lg-4">
                        <label for="Route"><b>Route:</b></label>
                    </div>
                    <div class="p-2 col-lg-6 rounded">
                        <select class="form-select" name="Route" id="route" aria-label="Default select example">
                            <option selected disabled>-- Select Route --</option>
                            <?php
                            $sql = mysqli_query($link, "SELECT * FROM `van_route` ORDER BY Van_Route");
                            while ($row = mysqli_fetch_assoc($sql)) {
                                echo "<option value='" . $row['Van_Route'] . "'>" . $row['Van_Route'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row justify-content-center" style="margin-top: 30px;">
                    <div class="col-lg-4">
                        <label for="password_head" id="password_head"><b>Fee:</b></label>
                    </div>
                    <div class="col-lg-6">
                        <input type="number" id="fee" name="Fee">
                    </div>
                </div>
                <div class="row button">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert actual fee"
                        <?php } ?>>
                        <input type="submit" class="button1" name="Insert" value="Insert" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>
                    </div>
                    <input type="reset" class="button1" onclick="document.getElementById('cls_row').hidden='hidden';document.getElementById('route_row').hidden='hidden';$('.wrapper').css('height','550px');" value="Clear">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view actual fee"
                        <?php } ?>>
                        <input type="submit" class="button1" name="Find" value="Find" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update actual fee"
                        <?php } ?>>
                        <input type="submit" class="button1" name="Update" value="Update" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('delete', MENU_ID)) { ?>
                        title="You don't have permission to delete actual fee"
                        <?php } ?>>
                        <input type="submit" class="button1" name="Delete" value="Delete" onclick="if(!confirm('Confirm to Delete Actual Fee?')){return false;}else{return true;}" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php

    if (isset($_POST['Insert'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert actual fee');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        echo "<script>$('.wrapper').css('height','600px');</script>";
        if ($_POST['Fee_Type']) {
            $type = $_POST['Fee_Type'];
            echo "<script>document.getElementById('fee_type').value = '" . $type . "'</script>";
            if ($type == "Vehicle Fee") {
                echo "<script>document.getElementById('cls_row').hidden = 'hidden';
            document.getElementById('route_row').hidden = '';</script>";
                if ($_POST['Route']) {
                    $route = $_POST['Route'];
                    echo "<script>document.getElementById('route').value = '" . $route . "'</script>";
                    if ($_POST['Fee']) {
                        $fee = $_POST['Fee'];
                        echo "<script>document.getElementById('fee').value = '" . $fee . "'</script>";
                        $query1 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                        if (mysqli_num_rows($query1) > 0) {
                            echo "<script>alert('Data Already Exists!')</script>";
                        } else {
                            $query2 = mysqli_query($link, "INSERT INTO `actual_fee`(Type,Route,Fee) VALUES('$type','$route','$fee')");
                            if ($query2) {
                                echo "<script>alert('Fee Inserted Successfully!')</script>";
                            } else {
                                echo "<script>alert('Fee Insertion Failed due to SQL Error')</script>";
                            }
                        }
                    } else {
                        echo "<script>alert('Please Enter Fee!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Route!')</script>";
                }
            } else {
                echo "<script>document.getElementById('cls_row').hidden = '';
            document.getElementById('route_row').hidden = 'hidden';</script>";
                if ($_POST['Class']) {
                    $class = $_POST['Class'];
                    echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                    if ($_POST['Fee']) {
                        $fee = $_POST['Fee'];
                        echo "<script>document.getElementById('fee').value = '" . $fee . "'</script>";
                        $query1 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
                        if (mysqli_num_rows($query1) > 0) {
                            echo "<script>alert('Data Already Exists!');</script>";
                        } else {
                            $query2 = mysqli_query($link, "INSERT INTO `actual_fee`(Type,Class,Fee) VALUES('$type','$class','$fee')");
                            if ($query2) {
                                echo "<script>alert('Fee Inserted Successfully!')</script>";
                            } else {
                                echo "<script>alert('Fee Insertion Failed due to SQL Error')</script>";
                            }
                        }
                    } else {
                        echo "<script>alert('Please Enter Fee!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class!')</script>";
                }
            }
        } else {
            echo "<script>alert('Please Select Fee Type!')</script>";
        }
    } else if (isset($_POST['Find'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view actual fee');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        echo "<script>$('.wrapper').css('height','600px');</script>";
        if (isset($_POST['Fee_Type'])) {
            $type = $_POST['Fee_Type'];
            echo "<script>document.getElementById('fee_type').value = '" . $type . "'</script>";
            if ($type == "Vehicle Fee") {
                echo "<script>document.getElementById('cls_row').hidden = 'hidden';
            document.getElementById('route_row').hidden = '';</script>";
                if ($_POST['Route']) {
                    $route = $_POST['Route'];
                    echo "<script>document.getElementById('route').value = '" . $route . "'</script>";
                    $query3 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                    if (mysqli_num_rows($query3) == 0) {
                        echo "<script>alert('Data Not Available!')</script>";
                    } else {
                        while ($row3 = mysqli_fetch_assoc($query3)) {
                            $fee = $row3['Fee'];
                        }
                        echo "<script>$('#fee').val(" . $fee . ");</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Route!')</script>";
                }
            } else {
                echo "<script>document.getElementById('cls_row').hidden = '';
            document.getElementById('route_row').hidden = 'hidden';</script>";
                if ($_POST['Class']) {
                    $class = $_POST['Class'];
                    echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                    $query3 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
                    if (mysqli_num_rows($query3) == 0) {
                        echo "<script>alert('Data Not Available!')</script>";
                    } else {
                        while ($row3 = mysqli_fetch_assoc($query3)) {
                            $fee = $row3['Fee'];
                        }
                        echo "<script>$('#fee').val(" . $fee . ");</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class!')</script>";
                }
            }
        } else {
            echo "<script>alert('Please Select Fee Type!');</script>";
        }
    } else if (isset($_POST['Update'])) {
        if (!can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to update actual fee');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        echo "<script>$('.wrapper').css('height','600px');</script>";
        if ($_POST['Fee_Type']) {
            $type = $_POST['Fee_Type'];
            echo "<script>document.getElementById('fee_type').value = '" . $type . "';</script>";
            if ($type == "Vehicle Fee") {
                echo "<script>document.getElementById('cls_row').hidden = 'hidden';
            document.getElementById('route_row').hidden = '';</script>";
                if ($_POST['Route']) {
                    $route = $_POST['Route'];
                    echo "<script>document.getElementById('route').value = '" . $route . "';</script>";
                    if ($_POST['Fee']) {
                        $fee = $_POST['Fee'];
                        echo "<script>document.getElementById('fee').value = '" . $fee . "';</script>";
                        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'")) != 0) {
                            $query2 = mysqli_query($link, "UPDATE `actual_fee` SET Fee = '$fee' WHERE Route = '$route' AND Type = '$type'");
                            if ($query2) {
                                echo "<script>alert('Fee Updated Successfully!')</script>";
                            } else {
                                echo "<script>alert('Fee Insertion Failed due to SQL Error')</script>";
                            }
                        } else {
                            echo "<script>alert('Actual Fee not Found for this Route! Please Insert Route!')</script>";
                        }
                    } else {
                        echo "<script>alert('Please Enter Fee!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Route!')</script>";
                }
            } else {
                echo "<script>document.getElementById('cls_row').hidden = '';
            document.getElementById('route_row').hidden = 'hidden';</script>";
                if ($_POST['Class']) {
                    $class = $_POST['Class'];
                    echo "<script>document.getElementById('class').value = '" . $class . "';</script>";
                    if ($_POST['Fee']) {
                        $fee = $_POST['Fee'];
                        echo "<script>document.getElementById('fee').value = '" . $fee . "';</script>";
                        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'")) != 0) {
                            $query2 = mysqli_query($link, "UPDATE `actual_fee` SET Fee = '$fee' WHERE Class = '$class' AND Type = '$type'");
                            if ($query2) {
                                echo "<script>alert('Fee Updated Successfully!')</script>";
                            } else {
                                echo "<script>alert('Fee Insertion Failed due to SQL Error')</script>";
                            }
                        } else {
                            echo "<script>alert('Actual Fee not Found for this Class! Please Insert Class!')</script>";
                        }
                    } else {
                        echo "<script>alert('Please Enter Fee!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class!')</script>";
                }
            }
        } else {
            echo "<script>alert('Please Select Fee Type!')</script>";
        }
    } else if (isset($_POST['Delete'])) {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to delete actual fee');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        if ($_POST['Fee_Type']) {
            $type = $_POST['Fee_Type'];
            if ($type == "Vehicle Fee") {
                if ($_POST['Route']) {
                    $route = $_POST['Route'];
                    $query4 = mysqli_query($link, "DELETE FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                    if ($query4) {
                        echo "<script>alert('Fee Deleted Successfully!')</script>";
                    } else {
                        echo "<script>alert('Fee Deletion Failed due to SQL Error')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Route!')</script>";
                }
            } else {
                if ($_POST['Class']) {
                    $class = $_POST['Class'];
                    $query4 = mysqli_query($link, "DELETE FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
                    if ($query4) {
                        echo "<script>alert('Fee Deleted Successfully!');</script>";
                    } else {
                        echo "<script>alert('Fee Deletion Failed due to SQL Error')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Class!')</script>";
                }
            }
        } else {
            echo "<script>alert('Please Select Fee Type!')</script>";
        }
    }

    ?>
    <!-- Scripts -->

    <!-- Show/Hide Route -->
    <script type="text/javascript">
        $('#fee_type').on('change', function() {
            $('.wrapper').css("height", "600px");
            type = document.getElementById('fee_type').value;
            cls = document.getElementById('cls_row');
            route = document.getElementById('route_row');
            if (type == "Vehicle Fee") {
                cls.hidden = 'hidden';
                route.hidden = '';
            } else {
                cls.hidden = '';
                route.hidden = 'hidden';
            }
        });
    </script>
</body>

</html>