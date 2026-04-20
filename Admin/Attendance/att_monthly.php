<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 7);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

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
if (isset($_POST['add'])) {
  $month = $_SESSION['Month'];
  $class = $_SESSION['Class'];
  $section = $_SESSION['Section'];
  $working = $_SESSION['Working_Days'];
  echo "<script>document.getElementById('month').value = '" . $month . "';</script>";

  $present = $_POST['Present_Days'];
  //Arrays
  $ids = array();
  $final_present = array();
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
  foreach (array_keys($present) as $id) {
    $final_present[$ids[$i]] = $present[$id];
    $i++;
  }

  foreach (array_keys($final_present) as $id) {
    $check_sql = mysqli_query($link, "SELECT * FROM `stu_att_master` WHERE Id_No = '$id'");
    if ($check_sql) {
      if (mysqli_num_rows($check_sql) != 0) {
        $upload_query = mysqli_query($link, "UPDATE `stu_att_master`SET $month = '$final_present[$id]' WHERE Id_No = '$id'");
      } else {
        $upload_query = mysqli_query($link, "INSERT INTO `stu_att_master`(Id_No,$month) VALUES('$id','$final_present[$id]')");
      }
      if ($upload_query) {
        $att_status = true;
      } else {
        $att_status = false;
        break;
      }
    } else {
      echo "<script>alert('Id No Checking Query Error!!')</script>";
    }
  }

  if ($att_status) {
    echo "<script>alert('Monthly Attendance Uploaded Successfully!!')</script>";
  } else {
    echo "<script>alert('Monthly Attendance Upload Failed!!')</script>";
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
</style>

<body class="bg-light">
  <?php
  include '../sidebar.php';
  ?>
  <div class="container">
    <form action="" method="post">
      <div class="row justify-content-center mt-5">
        <div class="col-lg-1">
          <label for=""><b>Month:</b></label>
        </div>
        <div class="col-lg-3">
          <select class="form-select" name="Month" id="month">
            <option value="selectmonth" selected disabled>-- Select Month --</option>
            <?php
            $months = ['June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March', 'April', 'May'];
            foreach ($months as $mon) {
              echo "<option value='" . $mon . "'>$mon</option>";
            }
            ?>
          </select>
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
      <div class="container">
        <div class="row justify-content-center mt-4">
          <div class="col-lg-2">
            <label for=""><b>Working Days:</b></label>
          </div>
          <div class="col-lg-2">
            <input type="number" class="form-control" name="Working_Days" id="working_days" readonly>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row justify-content-center mt-4">
          <div class="col-lg-2">
            <button class="btn btn-primary" type="submit" name="show">Show</button>
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
          <th style="text-align: center;">Attendance</th>
        </thead>
        <tbody id="tbody">
          <tr>
            <?php
            if (isset($_POST['show'])) {
              $month = $_POST['Month'];
              $working = $_POST['Working_Days'];
              $_SESSION['Month'] = $month;
              $_SESSION['Working_Days'] = $working;
              echo "<script>document.getElementById('month').value = '" . $month . "';
              document.getElementById('working_days').value = '" . $working . "';</script>";


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
                      $i = 1;
                      while ($row1 = mysqli_fetch_assoc($query1)) {
                        array_push($ids, $row1['Id_No']);
                        $names[$row1['Id_No']] = $row1['First_Name'];
                        echo '
                      <td>' . $i . '</td>
                      <td>' . $row1['Id_No'] . '</td>
                      <td>' . $row1['First_Name'] . '</td>
                      <td>';
                        $query2 = mysqli_query($link, "SELECT $month FROM `stu_att_master` WHERE Id_No = '" . $row1['Id_No'] . "'");
                        if ($query2) {
                          if (mysqli_num_rows($query2) != 0) {
                            while ($row2 = mysqli_fetch_assoc($query2)) {
                              echo '<input type="number" class="form-control" value="' . $row2[$month] . '" name="Present_Days[' . $row1['Id_No'] . ']" placeholder="Present Days">
                      </td>
                      ';
                            }
                          } else {
                            echo '<input type="number" class="form-control" name="Present_Days[' . $row1['Id_No'] . ']" placeholder="Present Days">
                      </td>
                      ';
                          }
                        }
                        echo '</tr>';
                        $i++;
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
    <?php if (isset($class) && isset($section)) {
      $text = $class . ' ' . $section . ' of ' . $month;
    } ?>
    <div class="container">
      <div class="row justify-content-center mt-4">
        <div class="col-lg-3">
          <button class="btn btn-primary" type="submit" name="add" onclick="if(!confirm('Confirm to Upload Monthly Attendance of <?php echo $text; ?>?'))return false; else return true;">Upload Monthly Attendance</button>
        </div>
      </div>
    </div>
  </form>


  <!-- Scripts -->

  <!-- Fetch Working Days -->
  <script type="text/javascript">
    $('#month').on('change', () => {
      month = $('#month').val();
      $.ajax({
        type: 'post',
        url: 'temp.php',
        data: {
          Month: month
        },
        success: function(data) {
          $('#working_days').val(data);
        }
      });
    });
  </script>
</body>

</html>