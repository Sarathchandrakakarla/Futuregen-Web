<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 33);

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
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
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
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="working" checked value="Working">
                        <label class="form-check-label" for="working">Working Days</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" id="holiday" value="Holiday">
                        <label class="form-check-label" for="holiday">Holiday</label>
                    </div>
                </div>
            </div>
            <div class="container" id="working-container">
                <div class="row justify-content-center mt-3">
                    <div class="col-lg-1">
                        <label for=""><b>Month:</b></label>
                    </div>
                    <div class="col-lg-3">
                        <select class="form-select" name="Month" id="month">
                            <option value="selectmonth" selected disabled>-- Select Month --</option>
                            <?php
                            $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May'];
                            foreach ($months as $mon) {
                                echo "<option value='" . $mon . "'>$mon</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="container">
                    <div class="row justify-content-center mt-4">
                        <div class="col-lg-2">
                            <label for=""><b>Working Days:</b></label>
                        </div>
                        <div class="col-lg-2">
                            <input type="number" class="form-control" name="Working_Days" id="working_days">
                        </div>
                    </div>
                </div>
            </div>
            <div class="container" id="holiday-container" hidden>
                <div class="row justify-content-center mt-3">
                    <div class="col-lg-1">
                        <label for=""><b>Date:</b></label>
                    </div>
                    <div class="col-lg-4">
                        <input type="date" class="form-control" value="<?php if (isset($date)) {
                                                                            echo $date;
                                                                        } else {
                                                                            echo date('Y-m-d');
                                                                        } ?>" name="Date" id="date" required>
                    </div>
                </div>
                <div class="container">
                    <div class="row justify-content-center mt-4">
                        <div class="col-lg-2">
                            <label for=""><b>Reason:</b></label>
                        </div>
                        <div class="col-lg-2">
                            <input type="text" class="form-control" name="Reason" id="reason">
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-4">
                        <div class="btn-wrapper"
                            <?php if (!can('create', MENU_ID)) { ?>
                            title="You don't have permission to insert/update working days or holidays."
                            <?php } ?>>
                            <button class="btn btn-primary" type="submit" name="insert" onclick="if(!confirm('Confirm to Insert/Update Working Days/Holidays?')){return false;}else{return true;}" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert/Update</button>
                        </div>
                        <div class="btn-wrapper"
                            <?php if (!can('delete', MENU_ID)) { ?>
                            title="You don't have permission to delete working days or holidays."
                            <?php } ?>>
                            <button class="btn btn-primary" type="submit" name="delete" onclick="if(!confirm('Confirm to Delete Holiday?')){return false;}else{return true;}" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>Delete</button>
                        </div>
                        <button class="btn btn-warning" type="reset">Clear</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php
    function format_date($date)
    {
        $arr = explode('-', $date);
        $t = $arr[0];
        $arr[0] = $arr[2];
        $arr[2] = $t;
        $date = implode('-', $arr);
        return $date;
    }
    if (isset($_POST['insert'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert/update working days or holidays.');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $type = $_POST['type'];
        echo "<script>document.getElementById('" . strtolower($type) . "').checked = true;</script>";

        if ($type == "Working") {
            echo "<script>
            document.getElementById('working-container').hidden = '';
            document.getElementById('holiday-container').hidden = 'hidden';
            </script>";
            if ($_POST['Month']) {
                $month = $_POST['Month'];
                echo "<script>document.getElementById('month').value = '" . $month . "';</script>";
                if ($_POST['Working_Days']) {
                    $working = $_POST['Working_Days'];
                    echo "<script>document.getElementById('working_days').value = '" . $working . "';</script>";
                    if (mysqli_num_rows(mysqli_query($link, "SELECT Working_Days FROM `working_days` WHERE Month = '$month'")) != 0) {
                        $working_query = mysqli_query($link, "UPDATE `working_days` SET Working_Days = '$working' WHERE Month = '$month'");
                        if ($working_query) {
                            echo "<script>alert('Working Days Updated Successfully!!')</script>";
                        } else {
                            echo "<script>alert('Working Days Updation Failed!!')</script>";
                        }
                    } else {
                        $working_query = mysqli_query($link, "INSERT INTO `working_days`(Month,Working_Days) VALUES('$month','$working')");
                        if ($working_query) {
                            echo "<script>alert('Working Days Inserted Successfully!!')</script>";
                        } else {
                            echo "<script>alert('Working Days Insertion Failed!!')</script>";
                        }
                    }
                } else {
                    echo "<script>alert('Please Enter Working Days!!')</script>";
                }
            } else {
                echo "<script>alert('Please Select Month!!')</script>";
            }
        } else if ($type == "Holiday") {
            echo "<script>
            document.getElementById('working-container').hidden = 'hidden';
            document.getElementById('holiday-container').hidden = '';
            </script>";
            $date = $_POST['Date'];
            echo "<script>document.getElementById('date').value = '" . $date . "';</script>";
            $date = format_date($date);
            if ($_POST['Reason']) {
                $reason = $_POST['Reason'];
                echo "<script>document.getElementById('reason').value = '" . $reason . "';</script>";
                if (mysqli_num_rows(mysqli_query($link, "SELECT Reason FROM `holidays` WHERE Date = '$date'")) != 0) {
                    $working_query = mysqli_query($link, "UPDATE `holidays` SET Reason = '$reason' WHERE Date = '$date'");
                    if ($working_query) {
                        echo "<script>alert('Holidays Updated Successfully!!')</script>";
                    } else {
                        echo "<script>alert('Holidays Updation Failed!!')</script>";
                    }
                } else {
                    $working_query = mysqli_query($link, "INSERT INTO `holidays`(Date,Reason) VALUES('$month','$reason')");
                    if ($working_query) {
                        echo "<script>alert('Holidays Inserted Successfully!!')</script>";
                    } else {
                        echo "<script>alert('Holidays Insertion Failed!!')</script>";
                    }
                }
            } else {
                echo "<script>alert('Please Enter Reason!!')</script>";
            }
        }
    }

    if (isset($_POST['delete'])) {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to delete working days or holidays.');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $date = $_POST['Date'];
        $date = format_date($date);
        if (mysqli_num_rows(mysqli_query($link, "SELECT Reason FROM `holidays` WHERE Date = '$date'")) == 0) {
            echo "<script>alert('No Holiday Found on " . $date . "')</script>";
        } else {
            $sql = mysqli_query($link, "DELETE FROM `holidays` WHERE Date = '$date'");
            if ($sql) {
                echo "<script>alert('Holiday Deleted Successfully!!')</script>";
            } else {
                echo "<script>alert('Holiday Deletion Failed!!')</script>";
            }
        }
    }

    ?>

    <!-- Scripts -->

    <!-- Change labels -->
    <script type="text/javascript">
        let working_container = document.getElementById('working-container');
        let holiday_container = document.getElementById('holiday-container');
        document.body.addEventListener('change', function(e) {
            let target = e.target;
            switch (target.id) {
                case 'working':
                    if (working_container.hidden) {
                        working_container.hidden = '';
                    }
                    if (!holiday_container.hidden) {
                        holiday_container.hidden = 'hidden';
                    }
                    break;
                case 'holiday':
                    if (!working_container.hidden) {
                        working_container.hidden = 'hidden';
                    }
                    if (holiday_container.hidden) {
                        holiday_container.hidden = '';
                    }
                    break;
            }
        });
    </script>

    <!-- Fetch Holidays -->
    <script type="text/javascript">
        function format_date(date) {
            arr = date.split("-")
            temp = arr[0]
            arr[0] = arr[2]
            arr[2] = temp
            date = arr.join("-")
            return date
        }
        $('#date').on('change', () => {
            date = $('#date').val();
            date = format_date(date)
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    Date: date
                },
                success: function(data) {
                    $('#reason').val(data);
                    console.log(date)
                }
            });
        });
    </script>

    <!-- Fetch Working Days -->
    <script type="text/javascript">
        $('#month').on('change', () => {
            month = $('#month').val();
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    Month: month
                },
                success: function(data) {
                    $('#working_days').val(data);
                }
            });
        });
    </script>
</body>

</html>