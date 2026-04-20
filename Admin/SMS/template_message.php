<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 81);

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
        <form action="" method="post" enctype="multipart/form-data">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">Excel File</div>
                <div class="col-lg-3">
                    <input type="file" class="form-control" accept=".xlsx" name="Excel" id="excel">
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">Template Id</div>
                <div class="col-lg-3">
                    <input type="number" class="form-control" placeholder="6 Digit Template Id" name="T_Id" id="t_id">
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">No Of Placeholders</div>
                <div class="col-lg-3">
                    <input type="number" class="form-control" min="0" max="10" name="No_Of_Placeholders" placeholder="Min:0 Max:10" id="no_of_placeholders">
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-4">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="T_Type" id="without_file" checked value="Without_File">
                        <label class="form-check-label" for="without_file">Without File</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="T_Type" id="with_file" value="With_File">
                        <label class="form-check-label" for="with_file">With File</label>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-3" id="media_row" hidden>
                <div class="col-lg-2">File</div>
                <div class="col-lg-3">
                    <input type="file" class="form-control" name="File" accept=".jpg,.png,.pdf,.mp4" id="file">
                </div>
            </div>
            <div class="container" id="alert_container" hidden>
                <div class="row justify-content-center mt-4">
                    <div class="col-lg-4">
                        <div class="alert alert-success d-flex align-items-center" role="alert">
                            <div>
                                File Uploaded Successfully! Now You can Send Messages!!
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to upload or send SMS"
                        <?php } ?>>
                        <button type="submit" class="btn btn-primary" onclick="if(excel.value == ''){alert('Please Select Excel File!');return false;}else{return true;}" name="Upload" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Upload Excel</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to send SMS"
                        <?php } ?>>
                        <button class="btn btn-success" name="Send" id="send" onclick="return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Send</button>
                    </div>
                    <button type="reset" class="btn btn-warning" onclick="media_row.hidden = 'hidden';alert_container.hidden = 'hidden';send_alert_container.hidden = 'hidden';">Clear</button>
                </div>
            </div>
        </form>
        <div class="container" id="send_alert_container" hidden>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4">
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <div>
                            Messages Sent Successfully!! <br>
                            Total Messages: <span id="total"></span> <br>
                            Sent Messages: <span id="sent"></span> <br>
                            Failed Messages: <span id="failed"></span> <br>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4" id="send_error_alert" hidden>
                <div class="col-lg-4">
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <div>
                            Sorry! Message could not sent be to these Mobile Numbers:<br>
                            <span id="error_mobiles"></span> <br>
                            Please Check these Mobile Numbers!
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        if (isset($_POST['Upload'])) {
            if (!can('create', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to send SMS');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
            }
            $excel = $_FILES['Excel'];
            $excel_name = $excel['name'];
            $location = "../../Files/Message Files/excel.xlsx";
            if (move_uploaded_file($_FILES['Excel']['tmp_name'], $location)) {
                echo "<script>
                let al = document.querySelector('.alert');
                document.querySelector('#alert_container').hidden = '';
                if(al.classList.contains('alert-danger')){
                    al.classList.remove('alert-danger');
                    al.classList.add('alert-success');
                }
                </script>";
            } else {
                echo "<script>
                let al = document.querySelector('.alert');
                document.querySelector('#alert_container').hidden = '';
                if(al.classList.contains('alert-success')){
                    al.classList.remove('alert-success');
                    al.classList.add('alert-danger');
                    al.innerHTML = 'File Upload Failed!';
                }
                </script>";
            }
        }

        if (isset($_POST['Send'])) {
            if (!can('create', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to send SMS');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
            }
            echo "<script>alert_container.hidden = 'hidden';</script>";
            if ($_POST['T_Id']) {
                $T_Id = $_POST['T_Id'];
                echo "<script>document.getElementById('t_id').value = '" . $T_Id . "';</script>";
                if (strlen($T_Id) == 6) {
                    if (isset($_POST['No_Of_Placeholders'])) {
                        $No_Of_Placeholders = $_POST['No_Of_Placeholders'];
                        echo "<script>document.getElementById('no_of_placeholders').value = '" . $No_Of_Placeholders . "'</script>";
                        $flag = true;
                        if (!file_exists("../../Files/Message Files/excel.xlsx")) {
                            echo "<script>alert('Excel File Not Found! Please Upload Excel File First then Try Again!');</script>";
                            $flag = false;
                        }
                        if ($_POST['T_Type'] == "With_File") {
                            echo "<script>with_file.checked = true;media_row.hidden = '';</script>";
                            if ($_FILES['File']) {
                                $extension = pathinfo($_FILES['File']['name'], PATHINFO_EXTENSION);
                                $name = $_FILES['File']['name'];
                                if (move_uploaded_file($_FILES['File']['tmp_name'], '../../Files/Message Files/file.' . $extension)) {
                                    $flag = true;
                                } else {
                                    $flag = false;
                                    echo "<script>alert('Message File Upload Failed!')</script>";
                                }
                            } else {
                                $flag = false;
                                echo "<script>alert('Please Select Message File!')</script>";
                            }
                        }
                        if ($flag) {
                            require '../../Miscellaneous/excelReader/excel_reader2.php';
                            require '../../Miscellaneous/excelReader/SpreadsheetReader.php';
                            $directory = "../../Files/Message Files/excel.xlsx";
                            $reader = new SpreadsheetReader($directory);
                            $details = [];
                            foreach ($reader as $key => $row) {
                                if ($key == 0) continue;
                                $details[$row[0]] = [];
                                for ($i = 0; $i < $No_Of_Placeholders; $i++) {
                                    $details[$row[0]][] = $row[$i + 1];
                                }
                            }
                            $ch = curl_init("https://wapi.wbbox.in/v2/wamessage/send");

                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                                'Content-Type: application/json',
                                'apikey: 1aa0a6ca-2ef7-11ef-ad4f-92672d2d0c2d'
                            ));

                            $send_count = 0;
                            $failed_mobiles = [];
                            foreach ($details as $mobile => $data) {
                                if (trim($mobile) != "") {
                                    $body = array(
                                        "from" => "919133663334",
                                        "to" => "",
                                        "type" => "template",
                                        "message" => array(
                                            "templateid" => $T_Id
                                        )
                                    );
                                    if ($_POST['T_Type'] == "With_File" && $_FILES['File']) {
                                        $body['message']['url'] = "https://victoryschools.in/Victory/Files/Message Files/file." . $extension;
                                    }
                                    $body["to"] = "91" . trim($mobile);
                                    foreach ($data as $placeholder) {
                                        $body["message"]["placeholders"][] = $placeholder;
                                    }
                                    $data_string = json_encode($body);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                                    $response = curl_exec($ch);
                                    if (explode(":", explode(',', $response)[1])[1] != '"SUCCESS"' || explode(":", explode(',', $response)[0])[1] != '"200"') {
                                        $failed_mobiles[] = $mobile;
                                    } else {
                                        $send_count++;
                                    }
                                }
                            }
                            curl_close($ch);
                            $failed_count = count($details) - $send_count;
                            echo "
                            <script>
                                send_alert_container.hidden = '';
                                total.innerHTML = '" . count($details) . "';
                                sent.innerHTML = '" . $send_count . "';
                                failed.innerHTML = '" . $failed_count . "';
                            </script>
                            ";
                            if ($failed_count != 0) {
                                echo "
                                <script>
                                    send_error_alert.hidden = '';
                                    error_mobiles.innerHTML = '" . implode(',', $failed_mobiles) . "';
                                </script>
                                ";
                            } else {
                                echo "
                                <script>
                                    send_error_alert.hidden = 'hidden';
                                    error_mobiles.innerHTML = '';
                                </script>
                                ";
                            }
                        } else {
                            echo "
                                <script>
                                    send_alert_container.hidden = 'hidden';
                                </script>
                                ";
                        }
                    } else {
                        echo "<script>alert('Please Enter No Of Placeholders!');</script>";
                    }
                } else {
                    echo "<script>alert('Template Id Should be of 6 digits!');</script>";
                }
            } else {
                echo "<script>alert('Please Enter Template Id!');</script>";
            }
        }
        ?>
    </div>

    <!-- Scripts -->
    <script>
        $('#t_id').on('change', () => {
            var id = $('#t_id').val();
            if (id.length != 6) {
                alert('Template Id Should Be of 6 digits')
            }
        })
        $(document).on('change', (e) => {
            if (e.target.id == "with_file") {
                $('#media_row').removeAttr('hidden');
                file.required = true;
            } else if (e.target.id == "without_file") {
                $('#media_row').attr('hidden', 'hidden');
                file.required = false;
            }
        })
    </script>

</body>

</html>