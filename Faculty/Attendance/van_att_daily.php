<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 124);

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
  <link rel="stylesheet" href="../../css/sidebar-style.css" />
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
        <div class="col-lg-1">
          <label for=""><b>Route:</b></label>
        </div>
        <div class="p-2 col-lg-4 rounded">
          <select class="form-select" name="Route" id="route" aria-label="Default select example">
            <option selected disabled>-- Select Route --</option>
            <?php
            $route_query = mysqli_query($link, "SELECT Van_Route FROM `van_route`");
            while ($route_row = mysqli_fetch_assoc($route_query)) {
              echo '
              <option value="' . $route_row['Van_Route'] . '">' . $route_row['Van_Route'] . '</option>';
            }
            ?>
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
            <label class="form-check-label" for="pm">Evening</label>
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


              if ($_POST['Route']) {
                $route = $_POST['Route'];
                $_SESSION['Route'] = $route;
                echo "<script>document.getElementById('route').value = '" . $route . "'</script>";

                //Arrays
                $ids = array();
                $names = array();

                $query1 = mysqli_query($link, "SELECT Id_No,First_Name FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '% CLASS' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG')");

                if ($query1) {
                  if (mysqli_num_rows($query1) == 0) {
                    echo "<script>alert('No Student Found in this Route!!')</script>";
                  } else {
                    while ($row1 = mysqli_fetch_assoc($query1)) {
                      array_push($ids, $row1['Id_No']);
                      $names[$row1['Id_No']] = $row1['First_Name'];
                    }

                    $canCreate = can('create', MENU_ID);
                    $i = 1;
                    foreach ($ids as $id) {
                      echo '
                        <tr>
                          <td>' . $i . '</td>
                          <td>' . $id . '</td>
                          <td>' . $names[$id] . '</td>
                          <td>';

                      $opts = [
                        'P' => 'Present',
                        'A' => 'Absent'
                      ];

                      foreach ($opts as $val => $label) {

                        $checked = ($val === 'P') ? 'checked' : '';

                        echo '
                            <div class="form-check form-check-inline ' . (!$canCreate ? 'disabled-wrapper' : '') . '"
                                ' . (!$canCreate ? 'title="You don\'t have permission to mark attendance"' : '') . '>

                                <input class="form-check-input"
                                      type="radio"
                                      name="att[' . $i . ']"
                                      id="' . strtolower($val) . '_' . $id . '"
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
                          </td>
                        </tr>';

                      $i++;
                    }

                    $date = format_date($date);
                    foreach ($ids as $id) {
                      //Checking If Data Already Exists
                      $check_query = mysqli_query($link, "SELECT * FROM `van_attendance_daily` WHERE Id_No = '$id' AND Date = '$date'");
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
                echo "<script>alert('Please Select Route!!')</script>";
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
      $route = $_SESSION['Route'];
      echo "<script>
      document.getElementById('route').value = '" . $route . "';
      document.getElementById('date').value = '" . $date . "';
              document.getElementById('" . strtolower($type) . "').checked = true</script>";

      $att = $_POST['att'];

      //Arrays
      $ids = array();
      $final_att = array();
      //Queries
      $query1 = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Van_Route = '$route' AND (Stu_Class LIKE '% CLASS' OR Stu_Class = 'PreKG' OR Stu_Class = 'LKG' OR Stu_Class = 'UKG')");

      if ($query1) {
        if (mysqli_num_rows($query1) == 0) {
          echo "<script>alert('No Student Found in this Route!!')</script>";
        } else {
          while ($row1 = mysqli_fetch_assoc($query1)) {
            array_push($ids, $row1['Id_No']);
          }
        }
      }

      $i = 0;
      foreach ($att as $status) {
        if ($status == "A") {
          $final_att[$ids[$i]] = $status;
        }
        $i++;
      }
      $date = format_date($date);

      foreach ($ids as $all_id) {
        if (array_key_exists($all_id, $final_att)) {
          //Checking If Data Already Exists
          $check_query = mysqli_query($link, "SELECT * FROM `van_attendance_daily` WHERE Id_No = '$all_id' AND Date = '$date'");
          if (mysqli_num_rows($check_query) != 0) {
            $upload_query = mysqli_query($link, "UPDATE `van_attendance_daily` SET $type = '$final_att[$all_id]' WHERE Id_No = '$all_id' AND Date = '$date'");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          } else {
            $upload_query = mysqli_query($link, "INSERT INTO `van_attendance_daily`(Id_No,Date,$type) VALUES('$all_id','$date','$final_att[$all_id]')");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          }
        } else {
          //Checking If Data Already Exists
          $check_query = mysqli_query($link, "SELECT * FROM `van_attendance_daily` WHERE Id_No = '$all_id' AND Date = '$date'");
          if (mysqli_num_rows($check_query) != 0) {
            $upload_query = mysqli_query($link, "UPDATE `van_attendance_daily` SET $type = '' WHERE Id_No = '$all_id' AND Date = '$date'");
            if ($upload_query) {
              $att_status = true;
            } else {
              $att_status = false;
              break;
            }
          }
        }
      }
      if ($att_status) {
        echo "<script>alert('Attendance Uploaded Successfully!!')</script>";
      } else {
        echo "<script>alert('Attendance Upload Failed!!')</script>";
      }
    }

    ?>
    <?php if (isset($route)) {
      $text = $route . ' on ' . $date . ' - ' . $type;
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