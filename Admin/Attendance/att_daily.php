<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 32);

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

  .delete {
    color: red;
    cursor: pointer;
    font-size: 20px;
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
      <div class="row justify-content-center mt-5">
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
      <div class="row justify-content-center mt-5">
        <div class="p-2 col-lg-4 rounded">
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
        <div class="p-2 col-lg-4 rounded">
          <select class="form-select" name="Section" id="sec" aria-label="Default select example">
            <option selected disabled>-- Select Section --</option>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
            <option value="E">E</option>
          </select>
        </div>
      </div>
      <div class="row justify-content-center mt-4">
        <div class="col-lg-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="att_type" id="am" checked value="AM">
            <label class="form-check-label" for="am">Morning</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="att_type" id="pm" value="PM">
            <label class="form-check-label" for="pm">Afternoon</label>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row justify-content-center mt-4">
          <div class="col-lg-2">
            <div class="btn-wrapper"
              <?php if (!can('view', MENU_ID)) { ?>
              title="You don't have permission to view this report"
              <?php } ?>>
              <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
            </div>
            <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
          </div>
        </div>
      </div>
    </form>
  </div>
  <form action="" method="POST">
    <div class="container table-container">
      <table class="table table-striped">
        <thead>
          <th>S.No</th>
          <th>Id No.</th>
          <th>Name</th>
          <th>Attendance</th>
        </thead>
        <tbody id="tbody">
          <tr>
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
            if (isset($_POST['show'])) {
              if (!can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              $date = $_POST['Date'];
              $type = $_POST['att_type'];
              $_SESSION['Date'] = $date;
              $_SESSION['Type'] = $type;
              echo "<script>document.getElementById('date').value = '" . $date . "';
              document.getElementById('" . strtolower($type) . "').checked = true</script>";


              if ($_POST['Class']) {
                $class = $_POST['Class'];
                $_SESSION['Class'] = $class;
                echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                if ($_POST['Section']) {
                  $section = $_POST['Section'];
                  $_SESSION['Section'] = $section;
                  echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";

                  //Arrays
                  $ids = array();
                  $names = array();

                  //Queries
                  $query1 = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");

                  if ($query1) {
                    if (mysqli_num_rows($query1) == 0) {
                      echo "<script>alert('Invalid Class or Section!!')</script>";
                    } else {

                      while ($row1 = mysqli_fetch_assoc($query1)) {
                        array_push($ids, $row1['Id_No']);
                        $names[$row1['Id_No']] = $row1['First_Name'];
                      }

                      $canCreate = can('create', MENU_ID);
                      $i = 1;
                      foreach ($ids as $id) {
                        echo '
                        <td>' . $i . '</td>
                        <td>' . $id . '</td>
                        <td>' . $names[$id] . '</td>
                        <td>';

                        $opts = [
                          'P' => 'Present',
                          'A' => 'Absent',
                          'L' => 'Leave'
                        ];

                        foreach ($opts as $val => $label) {

                          $checked = ($val === 'P') ? 'checked' : '';

                          echo '
                            <div class="form-check form-check-inline ' . (!$canCreate ? 'disabled-wrapper' : '') . '"
                                ' . (!$canCreate ? 'title="You don\'t have permission to mark attendance"' : '') . '>

                                <input class="form-check-input"
                                      type="radio"
                                      name="att[' . $i . ']"
                                      id="' . strtolower($val) . '[' . $id . ']"
                                      value="' . $val . '"
                                      ' . $checked . '
                                      ' . (!$canCreate ? 'disabled' : '') . '>

                                <label class="form-check-label"
                                      for="' . strtolower($val) . '_' . $id . '">
                                    ' . $label . '
                                </label>
                            </div>';
                        }

                        echo '
                        </td>';
                        echo '</tr>';
                        $i++;
                      }

                      $date = format_date($date);
                      foreach ($ids as $id) {
                        //Checking If Data Already Exists
                        $check_query = mysqli_query($link, "SELECT * FROM `attendance_daily` WHERE Id_No = '$id' AND Date = '$date'");
                        if (mysqli_num_rows($check_query) != 0) {
                          while ($check_row = mysqli_fetch_assoc($check_query)) {
                            $att_type = strtolower($check_row[$type]);
                            echo "
                                <script>document.getElementById('" . $att_type . "[" . $id . "]').checked = true;</script>
                              ";
                          }
                        }
                      }
                    }
                  } else {
                    echo "<script>alert('Error in Fetching Id Nos!')</script>";
                  }
                } else {
                  echo "<script>alert('Please Select Section!!')</script>";
                }
              } else {
                echo "<script>alert('Please Select Class!!')</script>";
              }
            }

            ?>
        </tbody>
      </table>
    </div>
    <?php
    if (isset($_POST['add'])) {
      if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert into this report');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
      }
      $date = $_SESSION['Date'];
      $type = $_SESSION['Type'];
      $class = $_SESSION['Class'];
      $section = $_SESSION['Section'];
      echo "<script>
      document.getElementById('class').value = '" . $class . "';
      document.getElementById('sec').value = '" . $section . "';
      document.getElementById('date').value = '" . $date . "';
      document.getElementById('" . strtolower($type) . "').checked = true;
      </script>";

      $att = $_POST['att'];
      $att_status = true;

      //Arrays
      $ids = array();
      $final_att = array();
      //Queries
      $query1 = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");

      if ($query1) {
        if (mysqli_num_rows($query1) == 0) {
          echo "<script>alert('Invalid Class or Section!!')</script>";
        } else {
          while ($row1 = mysqli_fetch_assoc($query1)) {
            array_push($ids, $row1['Id_No']);
          }
        }
      }

      $i = 0;
      foreach ($att as $status) {
        if ($status == "A" || $status == "L") {
          $final_att[$ids[$i]] = $status;
        }
        $i++;
      }
      $date = format_date($date);

      foreach ($ids as $all_id) {
        if (array_key_exists($all_id, $final_att)) {
          //Checking If Data Already Exists
          $check_query = mysqli_query($link, "SELECT * FROM `attendance_daily` WHERE Id_No = '$all_id' AND Date = '$date'");
          if (mysqli_num_rows($check_query) != 0) {
            $upload_query = mysqli_query($link, "UPDATE `attendance_daily` SET $type = '$final_att[$all_id]' WHERE Id_No = '$all_id' AND Date = '$date'");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          } else {
            $upload_query = mysqli_query($link, "INSERT INTO `attendance_daily`(Id_No,Date,$type) VALUES('$all_id','$date','$final_att[$all_id]')");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          }
        } else {
          //Checking If Data Already Exists
          $check_query = mysqli_query($link, "SELECT * FROM `attendance_daily` WHERE Id_No = '$all_id' AND Date = '$date'");
          if (mysqli_num_rows($check_query) != 0) {
            $upload_query = mysqli_query($link, "UPDATE `attendance_daily` SET $type = '' WHERE Id_No = '$all_id' AND Date = '$date'");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          }
        }
        /*
        foreach (array_keys($final_att) as $id) {
          //Checking If Data Already Exists
          $check_query = mysqli_query($link, "SELECT * FROM `attendance_daily` WHERE Id_No = '$id' AND Date = '$date'");
          if (mysqli_num_rows($check_query) != 0) {
            if ($all_id == $id) {
              $upload_query = mysqli_query($link, "UPDATE `attendance_daily` SET $type = '$final_att[$id]' WHERE Id_No = '$id' AND Date = '$date'");
              if ($upload_query) {
                $att_status = true;
              } else {
                $att_status = false;
                break;
              }
            } else {
              echo $all_id;
              $upload_query = mysqli_query($link, "UPDATE `attendance_daily` SET $type = '' WHERE Id_No = '$all_id' AND Date = '$date'");
              if ($upload_query) {
                $att_status = true;
              } else {
                $att_status = false;
                break;
              }
            }
          } else {
            $upload_query = mysqli_query($link, "INSERT INTO `attendance_daily`(Id_No,Date,$type) VALUES('$id','$date','$final_att[$id]')");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          }
        }
        */
      }
      if ($att_status) {
        //Submitting Attendance Status
        $submit_check_query = mysqli_query($link, "SELECT * FROM `class_attendance` WHERE Class = '$class' AND Section = '$section' AND Date = '$date'");
        if (mysqli_num_rows($submit_check_query) != 0) {
          while ($submit_row = mysqli_fetch_assoc($submit_check_query)) {
            $submit_status = $submit_row[$type . '_Status'];
          }
        }
        date_default_timezone_set('Asia/Kolkata');
        $force_submit_status = false;
        if (mysqli_num_rows($submit_check_query) == 0) {
          $submit_query = mysqli_query($link, "INSERT INTO `class_attendance`(Date,Class,Section," . $type . "_Status," . $type . "_Punch_Time) VALUES('$date','$class','$section','Submitted','" . date('h:i:s a', time()) . "')");
        } else {
          if ($submit_status == null) {
            $submit_query = mysqli_query($link, "UPDATE `class_attendance` SET " . $type . "_Status = 'Submitted'," . $type . "_Punch_Time = '" . date('h:i:s a', time()) . "' WHERE Class = '$class' AND Section = '$section' AND Date = '$date'");
          } else {
            $force_submit_status = true;
          }
        }
        if ((isset($submit_query) && $submit_query) || ($force_submit_status)) {
          echo "<script>alert('Attendance Uploaded Successfully!!')</script>";
        }
      } else {
        echo "<script>alert('Attendance Upload Failed!!')</script>";
      }
    }

    ?>
    <?php if (isset($class) && isset($section)) {
      $text = $class . ' ' . $section . ' on ' . $date . ' - ' . $type;
    } ?>
    <div class="container">
      <div class="row justify-content-center mt-4">
        <div class="col-lg-3">
          <div class="btn-wrapper"
            <?php if (!can('create', MENU_ID)) { ?>
            title="You don't have permission to insert into this report"
            <?php } ?>>
            <button class="btn btn-primary" type="submit" name="add" onclick="if(!confirm('Confirm to Upload Attendance of <?php echo $text; ?>?'))return false; else return true;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Upload Attendance</button>
          </div>
        </div>
      </div>
    </div>
  </form>

</body>

</html>