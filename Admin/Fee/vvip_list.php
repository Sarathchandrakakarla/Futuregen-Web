<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 67);

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
    if ($_POST['Id_No']) {
        $id = $_POST['Id_No'];

        $check_sql = mysqli_query($link, "SELECT * FROM `vvip` WHERE Id_No = '$id'");
        if (mysqli_num_rows($check_sql) > 0) {
            echo "<script>alert('Student Already Exists!!')</script>";
        } else {
            $sql = mysqli_query($link, "INSERT INTO `vvip`(Id_No) VALUES('$id')");

            if ($sql) {
                echo "<script>alert('New Student Inserted Successfully!!')</script>";
            } else {
                echo "<script>alert('New Student Insertion Failed!!')</script>";
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

    .disabled {
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
                    <label for=""><b>Add New Student</b></label>
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
                    <input type="text" class="form-control" id="inp" name="Id_No" placeholder="Enter Student Id No." value="<?php if (isset($id)) {
                                                                                                                                echo $id;
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
                <h3><b>VVIP Students List</b></h3>
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
                    <th style="padding:5px;">Id No.</th>
                    <th style="padding:5px;">Student Name</th>
                    <th style="padding:5px;">Class</th>
                    <th style="padding:5px;">Action</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $sql = "SELECT smd.Id_No AS Id_No,smd.* FROM `vvip` v JOIN `student_master_data` smd ON v.Id_No = smd.Id_No WHERE smd.Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS') ORDER BY FIELD(Stu_Class,'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'),Stu_Section";
                    $result = mysqli_query($link, $sql);
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>
                            <td style="padding:5px;">' . $i . '</td>
                            <td style="padding:5px;">' . $row['Id_No'] . '</td>
                            <td style="padding:5px;">' . $row['First_Name'] . '</td>
                            <td style="padding:5px;">' . $row['Stu_Class'] . ' ' . $row['Stu_Section'] . '</td>
                            <td style="padding:5px;">';

                        if (can('delete', MENU_ID)) {
                            echo '<i class="bx bx-trash delete text-danger"
                                onclick="delete_row(\'' . $row['Id_No'] . '\')"></i>';
                        } else {
                            echo '<i class="bx bx-trash delete text-secondary disabled"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="You don\'t have permission to delete"></i>';
                        }
                        echo '  </td>
                        </tr>';
                        $i++;
                    }
                }
                ?>
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
            id_no = $(this).parent().siblings().eq(1).text();
            if (!confirm('Confirm to delete Student: ' + id_no + '?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: 'delete_row.php',
                    data: {
                        Id_No: id_no
                    },
                    success: function(data) {
                        if (data == "1") {
                            alert('Student Deleted Successfully!! Refresh to get data updated!')
                        } else if (data == "permission") {
                            alert('You don\'t have permission to delete VVIP Student Data')
                        } else {
                            alert('Student Deletion Failed!')
                        }
                    }
                });
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = 'VVIP Students List';
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
            window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>VVIP Students List</h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>