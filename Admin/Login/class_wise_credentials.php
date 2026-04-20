<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 86);

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

  .tooltip-wrapper {
    cursor: not-allowed;
  }

  .tooltip-wrapper .form-check-input {
    pointer-events: none;
  }

  .disabled {
    opacity: 0.5;
  }
</style>

<body class="bg-light">
  <?php
  include '../sidebar.php';
  ?>
  <form action="" method="POST">
    <div class="container">
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
    </div>
    <div class="container">
      <div class="row justify-content-center mt-4">
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
      <div class="col-lg-5">
        <h3><b>Class Wise Student Credentials Report</b></h3>
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
      <tr>
        <td style="font-size:20px;color:red">Name of Class:</td>
        <td id="class_label" style="font-size:20px;"></td>
      </tr>
    </table>
    <form action="" method="post">
      <table class="table table-striped table-hover" border="1">
        <thead class="bg-secondary text-light">
          <tr>
            <th style="padding:5px;">S.No</th>
            <th style="padding:5px;">Id No. / Username</th>
            <th style="padding:5px;">First Name</th>
            <th style="padding:5px;">Password</th>
            <th class="no-print" style="padding:5px;">Enable/Disable</th>
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
                echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
                if ($_POST['Section']) {
                  $section = $_POST['Section'];
                  echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                  echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                  echo "
                  <input type='hidden' name='Class' value='" . $class . "'/>
                  <input type='hidden' name='Section' value='" . $section . "'/>
                  ";

                  $sql = "SELECT Id_No,Stu_Name,Stu_Password,Status FROM student WHERE Id_No IN (SELECT Id_No FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section')";
                  $result = mysqli_query($link, $sql);
                  if (mysqli_num_rows($result) == 0) {
                    echo "<script>alert('Class or Section Not Available!')</script>";
                  } else {
                    $i = 1;
                    //$create_status = false;
                    while ($row = mysqli_fetch_assoc($result)) {
                      /*
                      $pass = "VHST".rand(1000,9999);
                      $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
                      $create_sql = mysqli_query($link,"INSERT INTO `student` VALUES('','".$row['Id_No']."','".$row['First_Name']."','$pass','$pass_hash')");
                      if($create_sql){
                         $create_status = true;
                      }
                      else{
                          $create_status = true;
                          break;
                      }
                    */
                      echo '<tr>
                      <td style="padding:5px;">' . $i . '</td>
                      <td style="padding:5px;">' . $row['Id_No'] . '</td>
                      <td style="padding-left:5px;">' . $row['Stu_Name'] . '</td>
                      <td style="padding-left:5px;padding-right:5px;">' . $row['Stu_Password'] . '</td>
                      <td class="no-print" style="padding-left:30px;">
                          <div class="form-check form-switch">';
                      if (can('update', MENU_ID)) {
                        echo '<div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="status[' . $row['Id_No'] . ']" value="Enabled" role="switch" id="switch_' . $row['Id_No'] . '" ' . ($row['Status'] === "Enabled" ? 'checked' : '') . '>
                              </div>';
                      } else {
                        echo '<div class="form-check form-switch tooltip-wrapper" data-bs-toggle="tooltip" data-bs-placement="top" title="You don\'t have permission to update status">
                                <input class="form-check-input disabled" type="checkbox" role="switch" disabled ' . ($row['Status'] === "Enabled" ? 'checked' : '') . '>
                              </div>';
                      }
                      echo '</div>
                        </td>
                      </tr>';
                      $i++;
                    }
                    /*
                    if($create_status){
                        echo "<script>alert('Student Credentials Created for ".$class." ".$section."')</script>";
                    }
                    else{
                        echo "<script>alert('Student Credentials Creation Failed for ".$class." ".$section."')</script>";
                    }
                    */
                  }
                } else {
                  echo "<script>alert('Please Select Section!')</script>";
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
  <div class="container">
    <div class="row justify-content-center mt-3">
      <div class="col-lg-2">
        <div class="btn-wrapper"
          <?php if (!can('update', MENU_ID)) { ?>
          title="You don't have permission to update student login access"
          <?php } ?>>
          <button class="btn btn-primary" name="Update" onclick="if(!confirm('Confirm to Update Access?')){return false;}else{return true;}" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Update Access</button>
        </div>
      </div>
    </div>
  </div>
  </form>

  <?php
  if (isset($_POST['Update'])) {
    if (!can('update', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to update student login access');
        location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    if (isset($_POST['Class']) && isset($_POST['Section'])) {
      $class = $_POST['Class'];
      $section = $_POST['Section'];
      if (isset($_POST['status'])) {
        foreach ($_POST['status'] as $id_no => $status) {
          $sql = "UPDATE student SET Status = 'Enabled' WHERE Id_No = '$id_no'";
          mysqli_query($link, $sql);
        }
      }
      $all_students = mysqli_query($link, "SELECT s.Id_No AS Id_No FROM student_master_data smd JOIN student s ON smd.Id_No = s.Id_No WHERE smd.Stu_Class = '$class' AND smd.Stu_Section = '$section' ");
      while ($student = mysqli_fetch_assoc($all_students)) {
        if (!isset($_POST['status'][$student['Id_No']])) {
          $sql = "UPDATE student SET Status = 'Disabled' WHERE Id_No = '" . $student['Id_No'] . "'";
          mysqli_query($link, $sql);
        }
      }
      echo '<script>alert("Access Updated Succesfully!");</script>';
    }
  }
  ?>

  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


  <!-- Scripts -->

  <!-- Export Table to Excel -->
  <script type="text/javascript">
    $('#export').on('click', function() {
      stuclass = '<?php echo $class; ?>';
      stusection = '<?php echo $section; ?>';
      filename = stuclass + stusection;
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

      // Get the iframe document and insert the printable content
      let printFrame = window.frames["print_frame"];
      let printContent = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
      printContent += "<p style='font-size:20px;'><b>Class: </b> <?php echo ($class . ' ' . $section); ?></p>";
      printContent += document.querySelector('.table-container').innerHTML;

      // Write the content into the iframe
      printFrame.document.body.innerHTML = printContent;
      printFrame.window.focus();
      printFrame.window.print();

      // Restore the "Enable/Disable" column after printing
      setTimeout(() => {
        noPrintElements.forEach(el => el.style.display = "");
      }, 500);
    }
  </script>
</body>

</html>