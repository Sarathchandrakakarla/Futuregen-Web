<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 9);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<?php

if (isset($_POST['add'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    if ($_POST['Route']) {
        $route = $_POST['Route'];

        $check_sql = mysqli_query($link, "SELECT * FROM `van_route` WHERE Van_Route = '$route'");
        if (mysqli_num_rows($check_sql) > 0) {
            echo "<script>alert('Route Already Exists!!')</script>";
        } else {
            $sql = mysqli_query($link, "INSERT INTO `van_route`(Van_Route) VALUES('$route')");

            if ($sql) {
                echo "<script>alert('New Route Inserted Successfully!!')</script>";
            } else {
                echo "<script>alert('New Route Insertion Failed!!')</script>";
            }
        }
    }
}

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
        max-width: 700px;
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

    #inp,
    #add-btn {
        position: relative;
    }

    @keyframes mymove {
        from {
            opacity: 0;
            left: -100px;
        }

        to {
            left: 0;
            opacity: 1;
        }
    }

    @keyframes myrevmove {
        from {
            opacity: 1;
            left: 0;
        }

        to {
            left: -100px;
            opacity: 0;
        }
    }

    .delete {
        cursor: pointer;
        font-size: 20px;
        color: red;
    }

    .disabled-icon {
        color: grey;
        cursor: not-allowed;
    }
</style>

<body class="bg-light">
    <?php
    include '../sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <label for=""><b>Add New Route</b></label>
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <button class="btn btn-primary" style="border-radius: 50%;" id="plus" onclick="reveal();return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>> <i class="bx bx-plus" id="plus-icon"></i> </button>
                    </div>
                </div>
                <div class="col-lg-3">
                    <input type="text" class="form-control" id="inp" name="Route" placeholder="Enter Route Name" value="<?php if (isset($route)) {
                                                                                                                            echo $route;
                                                                                                                        } else {
                                                                                                                            echo '';
                                                                                                                        } ?>" style="opacity: 0;">
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <button class="btn btn-warning" name="add" id="add-btn" style="opacity: 0;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('print', MENU_ID)) { ?>
                        title="You don't have permission to print this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('export', MENU_ID)) { ?>
                        title="You don't have permission to export this report"
                        <?php } ?>>
                        <button class="btn btn-success" onclick="return false;" id="export" <?php echo !can('export', MENU_ID) ? 'disabled' : ''; ?>>Export To Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-3">
                <h3><b>Route Wise Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table hidden>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="font-size:30px;" colspan="4"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></td>
            </tr>
        </table>
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Route Name</th>
                    <th style="padding:5px;">Action</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <tr>
                    <?php
                    if (isset($_POST['show'])) {
                        if (!can('view', MENU_ID)) {
                            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                            exit;
                        }
                        $sql = "SELECT * FROM `van_route` ORDER BY Van_Route";
                        $result = mysqli_query($link, $sql);
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>
                            <td style="padding:5px;">' . $i . '</td>
                            <td style="padding:5px;">' . $row['Van_Route'] . '</td>
                            <td style="padding:5px;">';

                            $canDelete = can('delete', MENU_ID);

                            echo '<div class="btn-wrapper" ' .
                                (!$canDelete ? 'title="You don\'t have permission to delete"' : '') . '>';

                            echo '<i class="bx bx-trash delete ' . (!$canDelete ? 'disabled-icon' : '') . '"
                            data-allowed="' . ($canDelete ? '1' : '0') . '"></i>';

                            echo '</div></td></tr>';

                            $i++;
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Revealing Text Box -->

    <script type="text/javascript">
        function reveal() {
            button = document.getElementById('plus-icon');
            if (!button.classList.contains('open')) {
                button.style.transform = 'rotate(45deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "mymove 1s ease-in 1";
                txtinp.style.opacity = 1;

                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "mymove 1s ease-in 1";
                txtbtn.style.opacity = 1;
            } else {
                button.style.transform = 'rotate(90deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "myrevmove 1s ease-out 1";


                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "myrevmove 1s ease-out 1";

                txtinp.style.opacity = 0;
                txtbtn.style.opacity = 0;
            }
            button.classList.toggle('open');
        }
    </script>

    <!-- Delete Row -->
    <script type="text/javascript">
        $(".delete").click(function() {
            if ($(this).data('allowed') !== 1) {
                return false;
            }
            route = $(this).parent().siblings().eq(1).text();
            if (!confirm('Confirm to delete Route: ' + route + '?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: 'delete_row.php',
                    data: {
                        Route: route
                    },
                    success: function(data) {
                        alert('Route Deleted Successfully!! Refresh to get data updated!')
                    }
                });
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = 'Route_Wise_Report';
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById('table-container');
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            // Specify file name
            filename = filename ? filename + '.xls' : 'excel_data.xls';

            // Create download link element
            downloadLink = document.createElement("a");

            document.body.appendChild(downloadLink);

            if (navigator.msSaveOrOpenBlob) {
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob(blob, filename);
            } else {
                // Create a link to the file
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

                // Setting the file name
                downloadLink.download = filename;

                //triggering the function
                downloadLink.click();
            }
        });
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>