<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 17);

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 1000px;
        max-height: 500px;
        overflow-x: scroll;
        margin-left: 8%;
    }

    label {
        font-weight: bold;
    }

    .delete {
        cursor: pointer;
        font-size: 30px;
    }

    .modify {
        cursor: pointer;
        font-size: 30px;
    }

    #new_sub,
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

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
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

    .disabled-icon {
        color: grey !important;
        cursor: not-allowed !important;
        pointer-events: none;
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
                    <label for=""><b>Add New Subject</b></label>
                </div>
                <div class="col-lg-1">
                    <button class="btn btn-primary" style="border-radius: 50%;" id="plus" onclick="reveal();return false;"> <i class="bx bx-plus" id="plus-icon"></i> </button>
                </div>
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <input type="text" class="form-control" id="new_sub" name="New_Subject" placeholder="Enter Subject Name" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> value="<?php if (isset($exam)) {
                                                                                                                                                                                                        echo $exam;
                                                                                                                                                                                                    } else {
                                                                                                                                                                                                        echo '';
                                                                                                                                                                                                    } ?>" style="opacity: 0;">
                    </div>
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <button class="btn btn-warning" type="submit" name="insert" id="add-btn" onclick="return false;" style="opacity: 0;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container form-container">
            <div class="row justify-content-center mt-5">
                <label for="class_name" class="col-lg-2 col-form-label">Class</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Class" id="class" onchange="fetchExam(this.value)">
                        <option selected disabled>--Select Class--</option>
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
            <div class="row justify-content-center mt-3">
                <label for="exam_name" class="col-lg-2 col-form-label">Examination Name</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Exam" id="exam">
                        <option value="selectexam">--Select Exam--</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <label for="subject_name" class="col-lg-2 col-form-label">Subject Name</label>
                <div class="col-sm-3">
                    <select class="form-select" name="Subject" id="subject">
                        <option value="selectsubject" disabled selected>--Select Subject--</option>
                        <option value="Telugu">Telugu</option>
                        <option value="Hindi">Hindi</option>
                        <option value="English">English</option>
                        <option value="Maths">Maths</option>
                        <option value="Science">Science</option>
                        <option value="Social">Social</option>
                        <option value="GK">GK</option>
                        <option value="EVS">EVS</option>
                        <option value="Rhymes">Rhymes</option>
                        <option value="Computer">Computer</option>
                        <option value="Drawing">Drawing</option>
                        <option value="Telugu1">Telugu1</option>
                        <option value="Telugu2">Telugu2</option>
                        <option value="English1">English1</option>
                        <option value="English2">English2</option>
                        <option value="Maths1">Maths1</option>
                        <option value="Maths2">Maths2</option>
                        <option value="PS">PS</option>
                        <option value="NS">NS</option>
                        <option value="Social1">Social1</option>
                        <option value="Social2">Social2</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <label for="max_marks" class="col-lg-2 col-form-label" id="Search_Label">Max Marks</label>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="max" name="Max_Marks">
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-3">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert into this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="button" id="add" onclick="insert_row();return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> name="add">ADD</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Class Wise Subjects</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container">
        <table class="table table-striped table-hover">
            <thead class="bg-secondary text-light">
                <tr id="table-head">
                    <th>S.No</th>
                    <th>Class</th>
                    <th>Examination</th>
                    <th>Subject</th>
                    <th>Max Marks</th>
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
                        if ($_POST['Class']) {
                            $class = $_POST['Class'];
                            if ($_POST['Exam']) {
                                $exam = $_POST['Exam'];
                                $sql = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class='$class' AND Exam='$exam'");
                                if ($sql) {
                                    echo "<script>$('#table-head').append('<th>Action</th>')</script>";
                                    $i = 1;
                                    while ($row = mysqli_fetch_assoc($sql)) {
                                        $canDelete = can('delete', MENU_ID);
                                        $canUpdate = can('update', MENU_ID);

                                        echo '
                                        <tr>
                                            <td>' . $i . '</td>
                                            <td>' . $row['Class'] . '</td>
                                            <td>' . $row['Exam'] . '</td>
                                            <td>' . $row['Subjects'] . '</td>
                                            <td>' . $row['Max_Marks'] . '</td>
                                            <td>';

                                        // DELETE ICON
                                        echo '<span class="btn-wrapper" style="cursor:' . (!$canDelete ? 'not-allowed' : 'pointer') . '" ' .
                                            (!$canDelete ? 'title="You don\'t have permission to delete"' : '') . '>
                                            <i class="bx bx-trash delete ' . (!$canDelete ? 'disabled-icon' : '') . '"
                                                data-allowed="' . ($canDelete ? '1' : '0') . '"></i>
                                            </span>';

                                        // MODIFY ICON
                                        echo '<span class="btn-wrapper ms-2" style="cursor:' . (!$canUpdate ? 'not-allowed' : 'pointer') . '" ' .
                                            (!$canUpdate ? 'title="You don\'t have permission to modify"' : '') . '>
                                            <i class="bx bx-edit modify ' . (!$canUpdate ? 'disabled-icon' : '') . '"
                                            data-allowed="' . ($canUpdate ? '1' : '0') . '"></i>
                                        </span>';

                                        echo '</td></tr>';
                                        $i++;
                                    }
                                }
                            } else {
                                echo "<script>alert('Please Select Exam!')</script>";
                            }
                        } else {
                            echo "<script>alert('Please Select Class!')</script>";
                        }
                    }
                    ?>
                </tr>
            </tbody>
        </table>
    </div>


    <!-- Scripts -->

    <!-- Fetch Exam -->
    <script type="text/javascript">
        function fetchExam(cls) {
            $('#exam').html('');
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    class: cls
                },
                success: function(data) {
                    $("#exam").html(data);
                }
            })
        }
    </script>
    <!-- Insert Row -->
    <script type="text/javascript">
        function insert_row() {
            event.preventDefault()
            if ($('#class').val() == null) {
                alert('Please Select Class!');
            } else {
                cls = $('#class').val();
                if ($('#exam').val() == null) {
                    alert('Please Select Exam!');
                } else {
                    exm = $('#exam').val();
                    if ($('#subject').val() == null && $('#new_sub').val() == null) {
                        alert('Please Select Subject!');
                    } else {
                        if (($('#subject').val() != null || $('#subject').val() != '') && ($('#new_sub').val() == null || $('#new_sub').val() == '')) {
                            sub = $('#subject').val();
                        } else if (($('#subject').val() == null || $('#subject').val() == '') && ($('#new_sub').val() != null || $('#new_sub').val() != '')) {
                            sub = $('#new_sub').val();
                        } else if (($('#subject').val() != null || $('#subject').val() != '') && ($('#new_sub').val() != null || $('#new_sub').val() != '')) {
                            sub = $('#new_sub').val();
                        }
                        console.log(sub)
                        return;
                        if ($('#max').val() == "") {
                            alert('Please Enter Max Marks!');
                        } else {
                            max = $('#max').val();
                            $.ajax({
                                type: 'POST',
                                url: 'add_row.php',
                                data: {
                                    page: 'sub',
                                    Class: cls,
                                    Exam: exm,
                                    Subject: sub,
                                    Max: max
                                },
                                success: function(data) {
                                    if (data == "exists") {
                                        alert("Class and Examination and Subject is Already Added!")
                                    } else if (data == "failed") {
                                        alert("Subject Insertion Failed!")
                                    } else if (data == "permission") {
                                        alert('You don\'t have permission to insert into this report');
                                        location.replace('/Victory/Admin/Marks/class_wise_subjects.php');
                                    } else {
                                        alert("Subject Added Successfully!")
                                        document.getElementById('tbody').innerHTML = data;
                                    }
                                }
                            });
                        }
                    }
                }
            }
        }
        $('#add-btn').on('click', function() {
            if ($(this).hasClass('disabled-icon')) {
                return false;
            }
            insert_row()
        });
    </script>
    <!-- Modify Row -->
    <script type="text/javascript">
        $(".modify").click(function() {
            if ($(this).hasClass('disabled-icon')) {
                return false;
            }
            cls = $(this).parent().parent().siblings().eq(1).text();
            exm = $(this).parent().parent().siblings().eq(2).text();
            sub = $(this).parent().parent().siblings().eq(3).text();
            max = $(this).parent().parent().siblings().eq(4).text();
            (async () => {

                const {
                    value: data
                } = await Swal.fire({
                    showCloseButton: true,
                    html: "<label for='text'>Class:</label><input type='text' id='m_class' value='" + cls + "' disabled><br>" +
                        "<label for='text'>Exam Name:</label><input type='text' id='m_exam' value='" + exm + "' disabled><br>" +
                        "<label for='text'>Subject Name:</label><input type='text' id='m_sub' value='" + sub + "' disabled><br>" +
                        "<label for='text'>Max Marks:</label><input type='text' id='m_max' value='" + max + "'>",
                    preConfirm: () => {
                        return [
                            document.getElementById('m_class').value,
                            document.getElementById('m_exam').value,
                            document.getElementById('m_sub').value,
                            document.getElementById('m_max').value
                        ]
                    }
                })

                if (data) {
                    arr = [];
                    for (var key in data) {
                        arr.push(data[key]);
                    }
                    stuclass = arr[0];
                    exm = arr[1];
                    Sub = arr[2];
                    marks = arr[3];
                    console.log(stuclass, exm, Sub, marks);
                    $.ajax({
                        type: 'post',
                        url: 'modify_row.php',
                        data: {
                            Class: stuclass,
                            Exam: exm,
                            sub: Sub,
                            Max: marks
                        },
                        success: function(data) {
                            console.log(data)
                            alert('Max Marks Modified Successfully!! Refresh to get data updated!')
                        }
                    });
                }

            })()
        });
    </script>
    <!-- Delete Row -->
    <script type="text/javascript">
        $(".delete").click(function() {
            if ($(this).hasClass('disabled-icon')) {
                return false;
            }
            cls = $(this).parent().parent().siblings().eq(1).text();
            exm = $(this).parent().parent().siblings().eq(2).text();
            sub = $(this).parent().parent().siblings().eq(3).text();
            if (!confirm('Confirm to delete ' + cls + ' ' + exm + ' ' + sub + ' Subject?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: 'delete_row.php',
                    data: {
                        Class: cls,
                        Exam: exm,
                        Subject: sub
                    },
                    success: function(data) {
                        alert('Subject Deleted Successfully!! Refresh to get data updated!')
                    }
                });
            }
        });
    </script>
    <!-- Revealing Text Box -->
    <script type="text/javascript">
        function reveal() {
            button = document.getElementById('plus-icon');
            if (!button.classList.contains('open')) {
                button.style.transform = 'rotate(45deg)';
                button.style.transition = 'all .4s';

                txtinp = document.getElementById('new_sub')
                txtinp.style.animation = "mymove 1s ease-in 1";
                txtinp.style.opacity = 1;

                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "mymove 1s ease-in 1";
                txtbtn.style.opacity = 1;
            } else {
                button.style.transform = 'rotate(90deg)';
                button.style.transition = 'all .4s';

                txtinp = document.getElementById('new_sub')
                txtinp.style.animation = "myrevmove 1s ease-out 1";


                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "myrevmove 1s ease-out 1";

                txtinp.style.opacity = 0;
                txtbtn.style.opacity = 0;
            }
            button.classList.toggle('open');
        }
    </script>
</body>

</html>