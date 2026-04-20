<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 135);

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
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine" />

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
    #sign-out {
        display: none;
    }

    .table-container {
        max-width: 700px;
        max-height: 500px;
        overflow-x: scroll;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }

        .container {
            width: 80vw;
            margin-left: 20%;
        }
    }
</style>

<body>
    <?php include 'sidebar.php'; ?>
    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <strong>Date of Homework:</strong>
                </div>
                <div class="col-lg-3">
                    <input type="date" class="form-control" id="date" name="Date" />
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view your homeworks! Please Contact School Office"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>

    <div class="container table-container mt-4">
        <table class="table table-bordered table-hover table-striped border-dark" id="table">
            <thead class="bg-warning">
                <th>S No.</th>
                <th>Subject</th>
                <th>Actions</th>
            </thead>
            <tbody class="">
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
                echo "<script>date.value = '" . date('Y-m-d') . "';</script>";
                if (isset($_POST['Show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view your homeworks! Please Contact School Office');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $date = $_POST['Date'];
                    echo "<script>date.value = '" . $date . "';</script>";
                    $date = format_date($date);
                    $query1 = mysqli_query($link, "SELECT * FROM `homework` WHERE Date = '$date' AND Class = '" . $_SESSION["Stu_Class"] . "' AND Section = '" . $_SESSION["Stu_Section"] . "'");
                    if (mysqli_num_rows($query1) == 0) {
                        echo "
                        <tr>
                            <td colspan='3' class='text-center'>No Homework Found on " . $date . "</td>
                        </tr>
                        ";
                    } else {
                        $homeworks = [];
                        $i = 1;
                        while ($row1 = mysqli_fetch_assoc($query1)) {
                            $homeworks[] = $row1['Subject'];
                            echo "
                            <tr>
                                <td>" . $i . "</td>
                                <td>" . $row1['Subject'] . "</td>
                                <td style='display:flex;gap:50px;'>";
                            echo "<a href='/Futuregen/Files/Homework/" . $row1['Class'] . " " . $row1['Section'] . "/" . $row1['Date'] . "/" . $row1['Subject'] . ".pdf' download>Download File</a>";
                            echo "</td>
                            </tr>
                            ";
                            $i++;
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>


</body>

</html>