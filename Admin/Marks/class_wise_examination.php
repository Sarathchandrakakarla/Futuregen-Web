<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 16);

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
          <label for=""><b>Add New Exam</b></label>
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
          <input type="text" class="form-control" id="inp" name="New_Exam" placeholder="Enter Exam Name" value="<?php if (isset($exam)) {
                                                                                                                  echo $exam;
                                                                                                                } else {
                                                                                                                  echo '';
                                                                                                                } ?>" style="opacity: 0;">
        </div>
        <div class="col-lg-1">
          <div class="btn-wrapper"
            <?php if (!can('create', MENU_ID)) { ?>
            title="You don't have permission to insert into this report"
            <?php } ?>>
            <button class="btn btn-warning" name="insert" id="add-btn" style="opacity: 0;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert</button>
          </div>
        </div>
      </div>
    </div>
    <div class="container form-container">
      <div class="row justify-content-center mt-5">
        <label for="Search_By" class="col-lg-2 col-form-label">Class</label>
        <div class="col-sm-3 rounded">
          <select class="form-select" id="class" name="Class">
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
      <div class="row justify-content-center mt-3">
        <label for="exam_name" class="col-lg-2 col-form-label">Examination Name</label>
        <div class="col-sm-3">
          <select class="form-select" id="exam" name="Exam">
            <option selected disabled>-- Select Exam --</option>
            <optgroup label="Assignments">
              <?php
              for ($i = 1; $i <= 8; $i++) {
                echo '<option value="ASS-' . $i . '">ASS-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Formative Assessments">
              <?php
              for ($i = 1; $i <= 4; $i++) {
                echo '<option value="FA-' . $i . '">FA-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Unit Tests">
              <?php
              for ($i = 1; $i <= 4; $i++) {
                echo '<option value="UNIT-' . $i . '">UNIT-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Summative Assessments">
              <?php
              for ($i = 1; $i <= 3; $i++) {
                echo '<option value="SA-' . $i . '">SA-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Semesters">
              <?php
              for ($i = 1; $i <= 3; $i++) {
                echo '<option value="SEMESTER-' . $i . '">SEMESTER-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Revision Tests">
              <?php
              for ($i = 1; $i <= 10; $i++) {
                echo '<option value="REVISION-' . $i . '">REVISION-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Grand Tests">
              <?php
              for ($i = 1; $i <= 5; $i++) {
                echo '<option value="GRAND-TEST-' . $i . '">GRAND-TEST-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Objective Exams">
              <?php
              for ($i = 1; $i <= 4; $i++) {
                echo '<option value="OBJECTIVE-EXAM-' . $i . '">OBJECTIVE-EXAM-' . $i . '</option>';
              }
              ?>
            </optgroup>
            <optgroup label="Pre-Finals and Finals">
              <option value="QUARTERLY">QUARTERLY</option>
              <option value="HALFYEARLY">HALFYEARLY</option>
              <option value="PRE-FINAL">PRE-FINAL</option>
              <option value="ANNUAL">ANNUAL</option>
            </optgroup>
            <optgroup label="Others">
              <option value="Drawing">Drawing</option>
              <option value="IIT-EXAM">IIT-EXAM</option>
            </optgroup>
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
            <button class="btn btn-primary" type="submit" name="add" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>ADD</button>
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
        <label for="" style="color: red;">NOTE: Please Use Capital Letters for New Exam</label>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row justify-content-center mt-4">
      <div class="col-lg-4">
        <h3><b>Class Wise Examinations</b></h3>
      </div>
    </div>
  </div>
  <div class="container table-container">
    <table class="table table-striped table-hover">
      <thead class="bg-secondary text-light">
        <tr>
          <th>S.No</th>
          <th>Class</th>
          <th>Examination</th>
          <th>Max Marks</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tbody">
        <tr>
          <?php
          if (isset($_POST['add'])) {
            if (!can('create', MENU_ID)) {
              echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
              exit;
            }
            if ($_POST['Class']) {
              $class = $_POST['Class'];
              echo "<script>document.getElementById('class').value='$class'</script>";
              if ($_POST['Exam']) {
                $exam = $_POST['Exam'];
                echo "<script>document.getElementById('exam').value='$exam'</script>";
                if ($_POST['Max_Marks']) {
                  $max = $_POST['Max_Marks'];
                  echo "<script>document.getElementById('max').value='$max'</script>";

                  if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM class_wise_examination WHERE Class = '$class' AND EXAM = '$exam'")) >= 1) {
                    echo "<script>alert('Class and Examination is Already Added');</script>";
                  } else {
                    mysqli_query($link, "INSERT INTO class_wise_examination VALUES('','$class','$exam','$max')");
                    $sql = "SELECT * FROM `class_wise_examination` WHERE Class='$class'";
                    $result = mysqli_query($link, $sql);
                    echo "<script>alert('Examination Added Successfully');</script>";
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                      echo '<tr>
                                <td>' . $i . '</td>
                                <td>' . $row['Class'] . '</td>
                                <td>' . $row['Exam'] . '</td>
                                <td>' . $row['Max_Marks'] . '</td>
                                <td><i class="bx bx-trash delete"></i></td>
                                </tr>';
                      $i++;
                    }
                  }
                } else {
                  echo "<script>alert('Please Enter Max Marks!');</script>";
                }
              } else {
                echo "<script>alert('Please Select Exam!');</script>";
              }
            } else {
              echo "<script>alert('Please Select Class!');</script>";
            }
          }

          if (isset($_POST['insert'])) {
            if (!can('create', MENU_ID)) {
              echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
              exit;
            }
            if ($_POST['Class']) {
              $class = $_POST['Class'];
              echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
              if ($_POST['New_Exam']) {
                $exam = $_POST['New_Exam'];
                $max = $_POST['Max_Marks'];
                echo "<script>document.getElementById('inp').value = '" . $exam . "';
                document.getElementById('max').value = '" . $max . "';</script>";
                if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM class_wise_examination WHERE Class = '$class' AND EXAM = '$exam'")) >= 1) {
                  echo "<script>alert('Class and Examination is Already Added');</script>";
                } else {
                  mysqli_query($link, "INSERT INTO class_wise_examination VALUES('','$class','$exam','$max')");
                  $sql = "SELECT * FROM `class_wise_examination`";
                  $result = mysqli_query($link, $sql);
                  echo "<script>alert('Examination Added Successfully');</script>";
                  $i = 1;
                  while ($row = mysqli_fetch_assoc($result)) {
                    echo '<tr>
                  <td>' . $i . '</td>
                  <td>' . $row['Class'] . '</td>
                  <td>' . $row['Exam'] . '</td>
                  <td>' . $row['Max_Marks'] . '</td>
                  <td><i class="bx bx-trash delete"></i></td>
                  </tr>';
                    $i++;
                  }
                }
              } else {
                echo "<script>alert('Please Select Exam!!');</script>";
              }
            } else {
              echo "<script>alert('Please Select Class!!');</script>";
            }
          }

          if (isset($_POST['show'])) {
            if (!can('view', MENU_ID)) {
              echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
              exit;
            }
            if ($_POST['Class']) {
              $class = $_POST['Class'];
              echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
              $sql = "SELECT * FROM `class_wise_examination` WHERE Class = '$class'";
              $result = mysqli_query($link, $sql);
              $i = 1;
              while ($row = mysqli_fetch_assoc($result)) {

                $canDelete = can('delete', MENU_ID);
                $canUpdate = can('update', MENU_ID);

                echo '<tr>
                <td>' . $i . '</td>
                <td>' . $row['Class'] . '</td>
                <td id="' . $i . ' exam">' . $row['Exam'] . '</td>
                <td id="' . $i . ' max">' . $row['Max_Marks'] . '</td>
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
            } else {
              echo "<script>alert('Please Select Class!!');</script>";
            }
          }
          ?>
        </tr>
      </tbody>
    </table>
  </div>


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

  <!-- Modify Row -->
  <script type="text/javascript">
    $(".modify").click(function() {
      if ($(this).hasClass('disabled-icon')) {
        return false;
      }
      cls = $(this).parent().parent().siblings().eq(1).text();
      exm = $(this).parent().parent().siblings().eq(2).text();
      max = $(this).parent().parent().siblings().eq(3).text();
      (async () => {

        const {
          value: data
        } = await Swal.fire({
          showCloseButton: true,
          html: "<label for='text'>Class:</label><input type='text' id='m_class' value='" + cls + "' disabled><br>" +
            "<label for='text'>Exam Name:</label><input type='text' id='m_exam' value='" + exm + "' disabled><br>" +
            "<label for='text'>Max Marks:</label><input type='text' id='m_max' value='" + max + "'>",
          preConfirm: () => {
            return [
              document.getElementById('m_class').value,
              document.getElementById('m_exam').value,
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
          marks = arr[2];
          $.ajax({
            type: 'post',
            url: 'modify_row.php',
            data: {
              class: stuclass,
              exam: exm,
              max: marks
            },
            success: function(data) {
              alert('Examination Modified Successfully!! Refresh to get data updated!')
            }
          });
        }

      })()
    });
  </script>

  <!-- delete row -->
  <script type="text/javascript">
    $(".delete").click(function() {
      if ($(this).hasClass('disabled-icon')) {
        return false;
      }
      cls = $(this).parent().parent().siblings().eq(1).text();
      exm = $(this).parent().parent().siblings().eq(2).text();
      if (!confirm('Confirm to delete ' + cls + ' ' + exm + ' Exam?')) {
        return;
      } else {
        $.ajax({
          type: 'post',
          url: 'delete_row.php',
          data: {
            class: cls,
            exam: exm
          },
          success: function(data) {
            alert('Examination Deleted Successfully!! Refresh to get data updated!')
          }
        });
      }
    });
  </script>

</body>

</html>