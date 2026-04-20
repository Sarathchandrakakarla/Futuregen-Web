<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 54);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<?php
if (isset($_POST['Save'])) {
    if (!can('update', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert/update class teacher');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $id = $_POST['Id_No'];
    $class = $_POST['Class'];
    $section = $_POST['Section'];
    if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Emp_Id = '$id'")) == 0) {
        echo "<script>alert('No Employee Found with Id No. " . $id . "');</script>";
    } else {
        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `class_teacher` WHERE Class = '$class' AND Section = '$section'")) == 0) {
            $query = "INSERT INTO `class_teacher`(Class,Section,Id_No) VALUES('$class','$section','$id')";
            $message = "Class Teacher Inserted Successfully!";
            $err_message = "Class Teacher Insertion Failed!";
        } else {
            $query = "UPDATE `class_teacher` SET Id_No = '$id' WHERE Class = '$class' AND Section = '$section'";
            $message = "Class Teacher Updated Successfully!";
            $err_message = "Class Teacher Updation Failed!";
        }
        if (mysqli_query($link, $query)) {
            echo "<script>alert('" . $message . "');</script>";
        } else {
            echo "<script>alert('" . $err_message . "');</script>";
        }
    }
}

if (isset($_POST['Action']) && $_POST['Action'] == "Delete") {
    if (!can('delete', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to delete class teacher');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $class = $_POST['Class'];
    $section = $_POST['Section'];
    if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `class_teacher` WHERE Class = '$class' AND Section = '$section'")) == 0) {
        echo '<script>alert("Class Teacher Not Found for this Class and Section!Please Refresh and Verify!");</script>';
    } else {
        if (mysqli_query($link, "DELETE FROM `class_teacher` WHERE Class = '$class' AND Section = '$section'")) {
            echo "<script>alert('Class Teacher Deleted Successfully!');</script>";
        } else {
            echo "<script>alert('Class Teacher Deletion Failed!');</script>";
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

    .icon {
        cursor: pointer;
    }

    .disabled {
        opacity: 0.5;
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
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable();">Clear</button>
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
                <h3><b>Class Teacher List</b></h3>
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
                    <th style="padding:5px;">Class</th>
                    <th style="padding:5px;">Class Teacher Id No.</th>
                    <th style="padding:5px;">Class Teacher Name</th>
                    <th style="padding:5px;">Mobile Number</th>
                    <th class="no-print" style="padding:5px;">Action</th>
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
                        $sql = "SELECT smd.Stu_Class AS Class, smd.Stu_Section AS Section, emd.Emp_First_Name, emd.Mobile, emd.Emp_Id, ct.Id_No, CASE WHEN ct.Id_No IS NOT NULL THEN 'Assigned' ELSE 'Not Assigned' END AS Status FROM (SELECT DISTINCT Stu_Class, Stu_Section FROM student_master_data WHERE Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS')) AS smd LEFT JOIN class_teacher ct ON smd.Stu_Class = ct.Class AND smd.Stu_Section = ct.Section LEFT JOIN employee_master_data emd ON ct.Id_No = emd.Emp_Id ORDER BY FIELD(smd.Stu_Class, 'PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS'), smd.Stu_Section";
                        $result = mysqli_query($link, $sql);
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<tr>
                                <td style="padding:5px;">' . $i . '</td>
                                <td style="padding:5px;">' . $row['Class'] . ' ' . $row['Section'] . '</td>
                                <td style="padding:5px;">' . $row['Id_No'] . '</td>
                                <td style="padding:5px;">' . $row['Emp_First_Name'] . '</td>
                                <td style="padding:5px;">' . $row['Mobile'] . '</td>';
                            if ($row['Status'] == "Not Assigned") {
                                echo '<td class="no-print" style="padding:5px;"><i class="bx bx-plus icon insert bg-primary text-white rounded-circle p-2" data-bs-toggle="modal" data-bs-target="#Modal" data-class="' . $row['Class'] . '"  data-section="' . $row['Section'] . '"></i></td>';
                            } else {
                                echo '
                                <td class="no-print" style="padding:5px;">

                                    <i class="bx bx-edit icon edit rounded-circle p-2 ' .
                                    (can('update', MENU_ID) ? 'bg-warning' : 'bg-secondary disabled') . '"
                                        ' .
                                    (can('update', MENU_ID)
                                        ? 'data-bs-toggle="modal" data-bs-target="#Modal"
                                            data-class="' . $row['Class'] . '"
                                            data-section="' . $row['Section'] . '"
                                            onclick="getDetails(\'' . $row['Id_No'] . '\');"'
                                        : 'title="You don\'t have permission to edit"'
                                    ) . '>
                                    </i>

                                    <i class="bx bx-trash icon delete rounded-circle p-2 ' .
                                    (can('delete', MENU_ID) ? 'bg-danger text-white' : 'bg-secondary disabled') . '"
                                        ' .
                                    (can('delete', MENU_ID)
                                        ? 'onclick="deleteRow(\'' . $row['Id_No'] . '\')"'
                                        : 'title="You don\'t have permission to delete"'
                                    ) . '>
                                    </i>

                                </td>';
                            }
                            echo '</tr>';
                            $i++;
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>

    <form action="" method="post">
        <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="text" placeholder="Enter Employee Id No." class="form-control mb-3" name="Id_No" id="id_no" oninput="this.value = this.value.replace(/\s/g, '').toUpperCase();" onchange="getDetails(this.value)" required>
                        <input type="text" placeholder="Employee Name" class="form-control" name="Name" id="name" required readonly>
                        <input type="hidden" name="Class" id="modal_class" required>
                        <input type="hidden" name="Section" id="modal_section" required>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <div class="btn-wrapper"
                            <?php if (!can('update', MENU_ID)) { ?>
                            title="You don't have permission to update class teacher"
                            <?php } ?>>
                            <button type="submit" class="btn btn-primary" name="Save" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


    <!-- Scripts -->

    <!-- Set Modal Details -->
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('Modal');
            const modalTitle = modal.querySelector('.modal-title');

            modal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const className = trigger.getAttribute('data-class');
                const section = trigger.getAttribute('data-section');

                if (modalTitle && className && section) {
                    document.getElementById('modal_class').value = className;
                    document.getElementById('modal_section').value = section;
                    modalTitle.textContent = `Assign Class Teacher to ${className} - ${section}`;
                }
            });
            modal.addEventListener('hide.bs.modal', function(event) {
                document.getElementById('id_no').value = null;
                document.getElementById('name').value = null;
            })
        });
    </script>

    <!-- Get Employee Details -->
    <script type="text/javascript">
        function getDetails(id_no) {
            document.getElementById('id_no').value = null;
            document.getElementById('name').value = null;
            if (id_no.trim().length == 7) {
                $.ajax({
                    type: "POST",
                    url: "temp.php",
                    data: {
                        Action: "Get_Details",
                        Id_No: id_no
                    },
                    success: function(data) {
                        data = JSON.parse(data)
                        if (data['success'] == true) {
                            document.getElementById('id_no').value = id_no;
                            document.getElementById('name').value = data['data'][0];
                        } else if (data['success'] == false) {
                            alert(data['message'])
                        } else {
                            console.log(data);
                        }
                    }
                })
            } else {
                alert('Please Enter Valid Id no.')
            }
        }
    </script>

    <!-- Delete Row -->
    <script type="text/javascript">
        $(".delete").click(function() {
            const CAN_DELETE = <?= can('delete', MENU_ID) ? 'true' : 'false' ?>;
            if (!CAN_DELETE) {
                alert("You do not have permission to delete class teacher");
                return;
            }
            let cls = $(this).parent().siblings().eq(1).text();
            let section = cls.charAt(cls.length - 1).trim()
            cls = cls.slice(0, cls.length - 1).trim();
            let id_no = $(this).parent().siblings().eq(2).text();
            if (!confirm('Confirm to Delete Class Teacher for ' + cls + ' ' + section + '?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: '',
                    data: {
                        Action: 'Delete',
                        Class: cls,
                        Section: section,
                    },
                    success: function(data) {
                        alert('Class Teacher Deleted Successfully!! Refresh to get data updated!')
                    }
                });
            }
        });
    </script>

    <!-- Export Table to Excel -->
    <script type="text/javascript">
        $('#export').on('click', function() {
            filename = 'Class Teacher List';
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
            // Select all elements in the "Enable/Disable" column
            let noPrintElements = document.querySelectorAll(".no-print");

            // Hide them before printing
            noPrintElements.forEach(el => el.style.display = "none");
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
            setTimeout(() => {
                noPrintElements.forEach(el => el.style.display = "");
            }, 500);
        }
    </script>
</body>

</html>