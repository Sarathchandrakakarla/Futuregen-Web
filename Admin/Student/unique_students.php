<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 13);

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
    max-width: 1350px;
    max-height: 500px;
    margin-left: 8%;
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
  <form action="" method="POST">
    <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-lg-6">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stu_type" id="siblings" checked value="Siblings">
            <label class="form-check-label" for="siblings">Siblings</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stu_type" id="no_siblings" value="No_Siblings">
            <label class="form-check-label" for="no_siblings">No Siblings</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stu_type" id="combined" value="Combined">
            <label class="form-check-label" for="combined">Combined Siblings and Unique</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="stu_type" id="all_students" value="All_Students">
            <label class="form-check-label" for="all_students">All Students</label>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row justify-content-center mt-3">
          <div class="col-lg-3">
            <input type="checkbox" id="select_all" name="select_all" id="select_all" onclick="toggle(this)"><label for="select_all"><b>Select All</b></label><br>
            <input type="checkbox" class="column" value="Id_No" id="Id_No" name="columns[]"><label for="Id_No">Id No</label><br>
            <input type="checkbox" class="column" value="Adm_No" id="Adm_No" name="columns[]"><label for="Adm_No">Admission No</label><br>
            <input type="checkbox" class="column" value="First_Name" id="First_Name" name="columns[]"><label for="First_Name">First Name</label><br>
            <input type="checkbox" class="column" value="Sur_Name" id="Sur_Name" name="columns[]"><label for="Sur_Name">Sur Name</label><br>
            <input type="checkbox" class="column" value="Father_Name" id="Father_Name" name="columns[]"><label for="Father_Name">Father Name</label><br>
            <input type="checkbox" class="column" value="Mother_Name" id="Mother_Name" name="columns[]"><label for="Mother_Name">Mother Name</label><br>
            <input type="checkbox" class="column" value="DOB" id="DOB" name="columns[]"><label for="DOB">DOB</label><br>
            <input type="checkbox" class="column" value="Gender" id="Gender" name="columns[]"><label for="Gender">Gender</label><br>
            <input type="checkbox" class="column" value="Mobile" id="Mobile" name="columns[]"><label for="Mobile">All Mobile Nos</label><br>
            <input type="checkbox" class="column" value="S_Mobile" id="S_Mobile" name="columns[]"><label for="S_Mobile">Single Mobile No</label><br>
            <input type="checkbox" class="column" value="Aadhar" id="Aadhar" name="columns[]"><label for="Aadhar">Aadhar No</label><br>
          </div>
          <div class="col-lg-3">
            <input type="checkbox" class="column" value="Stu_Class" id="Stu_Class" name="columns[]"><label for="Stu_Class">Class</label><br>
            <input type="checkbox" class="column" value="Stu_Section" id="Stu_Section" name="columns[]"><label for="Stu_Section">Section</label><br>
            <input type="checkbox" class="column" value="Class_Section" id="Class_Section" name="columns[]"><label for="Class_Section">Class & Section</label><br>
            <input type="checkbox" class="column" value="Religion" id="Religion" name="columns[]"><label for="Religion">Religion</label><br>
            <input type="checkbox" class="column" value="Caste" id="Caste" name="columns[]"><label for="Caste">Caste</label><br>
            <input type="checkbox" class="column" value="Category" id="Category" name="columns[]"><label for="Category">Category</label><br>
            <input type="checkbox" class="column" value="House_No" id="House_No" name="columns[]"><label for="House_No">House No</label><br>
            <input type="checkbox" class="column" value="Area" id="Area" name="columns[]"><label for="Area">Area</label><br>
            <input type="checkbox" class="column" value="Village" id="Village" name="columns[]"><label for="Village">Village</label><br>
            <input type="checkbox" class="column" value="DOJ" id="DOJ" name="columns[]"><label for="DOJ">DOJ</label><br>
            <input type="checkbox" class="column" value="Previous_School" id="Previous_School" name="columns[]"><label for="Previous_School">Previous School</label><br>
            <input type="checkbox" class="column" value="Van_Route" id="Van_Route" name="columns[]"><label for="Van_Route">Van Route</label><br>
            <input type="checkbox" class="column" value="Referred_By" id="Referred_By" name="columns[]"><label for="Referred_By">Referred By</label><br>
            <input type="checkbox" class="column" value="Siblings" id="Siblings" name="columns[]"><label for="Siblings">Siblings</label><br>
            <input type="checkbox" class="column" value="Siblings_Details" id="Siblings_Details" name="columns[]"><label for="Siblings_Details">Siblings Details</label><br>
          </div>
        </div>
      </div>
      <div class="container">
        <div class="row justify-content-center mt-4">
          <div class="col-lg-6">
            <div class="btn-wrapper"
              <?php if (!can('view', MENU_ID)) { ?>
              title="You don't have permission to view this report"
              <?php } ?>>
              <button class="btn btn-danger" type="submit" name="Update" onclick="if(!confirm('Confirm to Update Current Strength?')){return false;}else{return true;}" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Update Current Strength</button>
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
    </div>
  </form>
  <div class="container">
    <div class="row justify-content-center mt-4">
      <div class="col-lg-5">
        <h3><b>Siblings Student Details Report</b></h3>
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
        <tr class="table-head">
          <th style="padding:5px;">S.No</th>
        </tr>
      </thead>
      <tbody id="tbody">
        <tr>
          <?php
          if (isset($_POST['show']) || isset($_POST['Update'])) {

            if (isset($_POST['show'])) {
              if (!can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              $cols_flag = false;
              $cols = array();
              if (isset($_POST['columns'])) {
                if ($_POST['select_all']) {
                  echo "<script>document.getElementById('select_all').checked = true;</script>";
                }
                foreach ($_POST["columns"] as $col) {
                  echo "<script>document.getElementById('" . $col . "').checked = true;</script>";
                  array_push($cols, $col);
                  echo "<script>
                                    $('.table-head').append('<th>" . $col . "</th>')
                                    </script>";
                }
                $cols_flag = true;
              } else {
                echo "<script>alert('No Column Selected!')</script>";
              }
            }

            if ((isset($_POST['show']) && $cols_flag) || (isset($_POST['Update']))) {
              if ((isset($_POST['show']) && $cols_flag) && !can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              if ((isset($_POST['Update'])) && !can('update', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to update this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              //Arrays
              $classes = ['PreKG', 'LKG', 'UKG'];
              for ($i = 1; $i <= 10; $i++) {
                array_push($classes, $i . ' CLASS');
              }
              $sections = ['A', 'B', 'C', 'D', 'E'];
              $all_ids = [];
              $unique_ids = [];
              $sibling_ids = [];

              //Getting All Ids
              foreach ($classes as $class) {
                foreach ($sections as $section) {
                  $query1 = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                  if (mysqli_num_rows($query1) != 0) {
                    while ($row1 = mysqli_fetch_assoc($query1)) {
                      array_push($all_ids, $row1['Id_No']);
                    }
                  }
                }
              }

              //Getting No Siblings Students
              foreach ($all_ids as $id) {
                $query2 = mysqli_query($link, "SELECT Siblings FROM `student_master_data` WHERE Id_No = '$id'");
                while ($row2 = mysqli_fetch_assoc($query2)) {
                  $siblings = $row2['Siblings'];
                  if ($siblings == "" || $siblings == NULL) {
                    array_push($unique_ids, $id);
                  }
                }
              }

              //Getting Only Siblings Students
              $count = count($all_ids);
              $skip = [];
              for ($i = 0; $i < $count; $i++) {
                $id = $all_ids[$i];
                $query2 = mysqli_query($link, "SELECT Siblings FROM `student_master_data` WHERE Id_No = '$id'");
                while ($row2 = mysqli_fetch_assoc($query2)) {
                  $siblings = $row2['Siblings'];
                  if ($siblings != "" && $siblings != NULL) {
                    $sibling_arr = explode(',', $siblings);
                    foreach ($sibling_arr as $sibling) {
                      array_push($skip, trim($sibling));
                    }

                    $flag = true;
                    foreach ($skip as $s) {
                      if ($id == $s) {
                        unset($all_ids[$i]);
                        $flag = false;
                      }
                    }
                    if ($flag) {
                      array_push($sibling_ids, $id);
                    }
                  }
                }
              }
            }
            if (isset($_POST['show'])) {
              if (!can('view', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              if ($_POST['stu_type']) {
                $type = $_POST['stu_type'];
                echo "<script>document.getElementById('" . strtolower($type) . "').checked = true;</script>";
                if ($type == "Siblings") {
                  $print_ids = $sibling_ids;
                } else if ($type == "No_Siblings") {
                  $print_ids = $unique_ids;
                } else if ($type == "Combined") {
                  $current_ids = array_unique(array_merge($unique_ids, $sibling_ids));
                  //Sorting By Class and Section
                  $current_ids = mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Id_No IN ('" . implode("','", $current_ids) . "') ORDER BY FIELD(Stu_Class, 'PreKG', 'LKG', 'UKG', '1 CLASS', '2 CLASS', '3 CLASS', '4 CLASS', '5 CLASS', '6 CLASS', '7 CLASS', '8 CLASS', '9 CLASS', '10 CLASS'),FIELD(Stu_Section, 'A', 'B', 'C', 'D','E')");
                  $sorted_ids = [];
                  while ($sort_row = mysqli_fetch_assoc($current_ids)) {
                    $sorted_ids[] = $sort_row['Id_No'];
                  }
                  $print_ids = $sorted_ids;
                } else if ($type == "All_Students") {
                  $current_ids = array_unique(array_merge($unique_ids, $sibling_ids));
                  $print_ids = [];
                  $all_students_sql = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class IN ('PreKG','LKG','UKG','1 CLASS','2 CLASS','3 CLASS','4 CLASS','5 CLASS','6 CLASS','7 CLASS','8 CLASS','9 CLASS','10 CLASS')");
                  while ($all_students_row = mysqli_fetch_assoc($all_students_sql)) {
                    $print_ids[] = $all_students_row['Id_No'];
                  }
                }

                $i = 1;
                foreach ($print_ids as $print_id) {
                  $query3 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$print_id'");
                  while ($row3 = mysqli_fetch_assoc($query3)) {
                    echo '<tr>
                      <td>' . $i . '</td>';
                    foreach ($cols as $col) {
                      if ($col == "Class_Section") {
                        echo '<td>' . $row3['Stu_Class'] . ' ' . $row3['Stu_Section'] . '</td>';
                      } else if ($col == "S_Mobile") {
                        if (str_contains($row3['Mobile'], ',')) {
                          echo '<td>' . explode(',', $row3['Mobile'], 2)[0] . '</td>';
                        } else if (str_contains($row3['Mobile'], ' ')) {
                          echo '<td>' . explode(' ', $row3['Mobile'], 2)[0] . '</td>';
                        } else {
                          echo '<td>' . $row3['Mobile'] . '</td>';
                        }
                      } else if ($col == "Siblings_Details") {
                        echo '<td>';
                        if ($row3['Siblings'] != NULL && $row3['Siblings'] != "") {
                          $sibling_id_nos = explode(',', $row3['Siblings']);
                          foreach ($sibling_id_nos as $sibling_details) {
                            $sibling_query = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$sibling_details'");
                            while ($sibling_row = mysqli_fetch_assoc($sibling_query)) {
                              echo $sibling_row['First_Name'] . ',' . $sibling_row['Stu_Class'] . ' ' . $sibling_row['Stu_Section'];
                            }
                            echo '<br>';
                          }
                        }
                        echo '</td>';
                      } else {
                        echo '<td>' . $row3[$col] . '</td>';
                      }
                    }
                    echo '</tr>';
                  }
                  $i++;
                }
              }
            } else if (isset($_POST['Update'])) {
              if (!can('update', MENU_ID)) {
                echo "<script>alert('You don\'t have permission to update this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                exit;
              }
              $current_ids = array_unique(array_merge($unique_ids, $sibling_ids));
              $update_status = false;
              $sibling_classes = [];
              $classes = ['PreKG', 'LKG', 'UKG'];
              for ($i = 1; $i <= 10; $i++) {
                array_push($classes, $i . ' CLASS');
              }
              $sections = ['A', 'B', 'C', 'D'];
              foreach ($classes as $class) {
                foreach ($sections as $section) {
                  if (mysqli_num_rows(mysqli_query($link, "SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'")) != 0) {
                    $sibling_classes[$class][$section] = [];
                  }
                }
              }
              foreach ($current_ids as $id) {
                $query4 = mysqli_query($link, "SELECT Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '$id'");
                while ($row4 = mysqli_fetch_assoc($query4)) {
                  array_push($sibling_classes[$row4['Stu_Class']][$row4['Stu_Section']], $id);
                }
              }

              foreach (array_keys($sibling_classes) as $class) {
                foreach (array_keys($sibling_classes[$class]) as $section) {
                  if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `current_strength` WHERE Class = '$class' AND Section = '$section'")) == 0) {
                    $insert_sql = "INSERT INTO `current_strength` VALUES('','" . $class . "','" . $section . "','";
                    if (count($sibling_classes[$class][$section]) == 0) {
                      $insert_sql .= "NULL')";
                    } else {
                      foreach ($sibling_classes[$class][$section] as $student) {
                        if ($student != end($sibling_classes[$class][$section])) {
                          $insert_sql .= $student . ",";
                        } else {
                          $insert_sql .= $student . "')";
                        }
                      }
                    }
                    if (mysqli_query($link, $insert_sql)) {
                      $update_status = true;
                    } else {
                      $update_status = false;
                      break 2;
                    }
                  } else {
                    $update_sql = "UPDATE `current_strength` SET Students = '";
                    if (count($sibling_classes[$class][$section]) == 0) {
                      $check_sql = mysqli_query($link, "SELECT * FROM `current_strength` WHERE Class = '$class' AND Section = '$section'");
                      if (mysqli_num_rows($check_sql) != 0) {
                        while ($check_row = mysqli_fetch_assoc($check_sql)) {
                          $db_students = $check_row['Students'];
                        }
                        if ($db_students == NULL || $db_students == "") {
                          continue;
                        } else {
                          $update_sql = "UPDATE `current_strength` SET Students = NULL WHERE Class = '$class' AND Section = '$section'";
                        }
                      }
                    } else {
                      foreach ($sibling_classes[$class][$section] as $student) {
                        if ($student != end($sibling_classes[$class][$section])) {
                          $update_sql .= $student . ",";
                        } else {
                          $update_sql .= $student . "'";
                        }
                      }
                      $update_sql .= " WHERE Class = '" . $class . "' AND Section = '" . $section . "'";
                    }
                    if (mysqli_query($link, $update_sql)) {
                      $update_status = true;
                    } else {
                      $update_status = false;
                      break 2;
                    }
                  }
                }
              }
              if ($update_status) {
                echo "<script>alert('Current Strength Updated Succesfully!')</script>";
              } else {
                echo "<script>alert('Current Strength Updation Failed!')</script>";
              }
            }
          }
          ?>
        </tr>
      </tbody>
    </table>
  </div>
  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


  <!-- Scripts -->

  <!-- Checkbox Select All -->
  <script type="text/javascript">
    function toggle(source) {
      checkboxes = document.getElementsByClassName('column');
      for (var i = 0, n = checkboxes.length; i < n; i++) {
        checkboxes[i].checked = source.checked;
      }
    }
    $('.column').on('click', function() {
      if ($('.column').not(':checked').length == 0) {
        document.getElementById('select_all').checked = true;
      } else {
        document.getElementById('select_all').checked = false;
      }
    });
  </script>

  <!-- Export Table to Excel -->
  <script type="text/javascript">
    $('#export').on('click', function() {
      filename = 'unique_students';
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