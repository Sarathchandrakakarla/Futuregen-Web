<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 134);

requireLogin();
requireMenuAccess(MENU_ID);
if (!can('view', MENU_ID)) {
    echo "<script>
  alert('You don\'t have permission to view your attendance! Please Contact School Office');
  location.replace('/Victory/Student/student_dashboard.php');
  </script>";
}
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
    body {
        background: #E4E9F7;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    .table-container {
        margin: 4% 10%;
    }

    #sign-out {
        display: none;
    }

    th,
    td {
        text-align: center;
    }

    @media screen and (max-width:920px) {
        .table {
            margin: 10% 10%;
        }
    }
</style>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="container" id="alert-container" hidden>
        <div class="row justify-content-center mt-5">
            <div class="col-lg-5">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <svg class="bi flex-shrink-0 me-2" width="26" height="26" role="img" aria-label="Danger:">
                        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                    </svg>
                    <div>
                        Sorry, You are not Found in Attendance Data! <br> Contact Computer Operator !
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h1 class="mt-4" style="font-family:'Times New Roman';text-align: center;">Monthly Attendance</h1>
    <div class="container table-container">
        <table class="table table-striped table-bordered table-hover">
            <thead class="bg-warning">
                <th>Month</th>
                <th>No. of Working Days</th>
                <th>No. of Present Days</th>
                <th>No. of Absent Days</th>
            </thead>
            <tbody class="table-group-divider">
                <?php
                $sql = mysqli_query($link, "SELECT * FROM `stu_att_master` WHERE Id_No = '" . $_SESSION['Id_No'] . "'");
                if (mysqli_num_rows($sql) > 0) {
                    while ($row = mysqli_fetch_assoc($sql)) {
                        foreach (array_keys($row) as $month) {
                            if ($month != "S_No" && $month != "Id_No") {
                                $working_sql = mysqli_query($link, "SELECT Working_Days FROM `working_days` WHERE Month = '$month'");
                                $working_days = 0;
                                while ($working_row = mysqli_fetch_assoc($working_sql)) {
                                    $working_days = $working_row['Working_Days'];
                                }
                                echo "<tr>
                                <td class='bg-secondary text-white' style='font-weight:bold;'>" . $month . "</td>";
                                if ($working_days != 0) {
                                    echo '<td>' . $working_days . '</td>
                                    <td>' . $row[$month] . '</td>
                                    <td>' . (int)($working_days) - (int)($row[$month]) . '</td>
                                    ';
                                }
                                echo "</tr>";
                            }
                        }
                    }
                } else {
                    echo "<script>document.getElementById('alert-container').hidden = '';</script>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

</html>