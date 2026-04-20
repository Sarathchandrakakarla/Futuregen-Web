<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 87);

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
        <h3><b>Faculty Credentials Report</b></h3>
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
        <td></td>
        <td></td>
        <td style="font-size:25px;" colspan="2">Faculty Credentials</td>
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
              $sql = "SELECT Id_No,Faculty_Name,Password,Status FROM faculty WHERE Id_No IN (SELECT Id_No FROM `employee_master_data` WHERE Id_No != 'VHST02674' AND Status='Working')";
              $result = mysqli_query($link, $sql);
              $i = 1;
              while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>
                <td style="padding:5px;">' . $i . '</td>
                <td style="padding:5px;">' . $row['Id_No'] . '</td>
                <td style="padding-left:5px;">' . $row['Faculty_Name'] . '</td>
                <td style="padding-left:5px;padding-right:5px;">' . $row['Password'] . '</td>
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
  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

  <?php
  if (isset($_POST['Update'])) {
    if (!can('update', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to update faculty login access');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    foreach ($_POST['status'] as $id_no => $status) {
      $sql = "UPDATE faculty SET Status = 'Enabled' WHERE Id_No = '$id_no'";
      mysqli_query($link, $sql);
    }
    $all_employees = mysqli_query($link, "SELECT f.Id_No AS Id_No FROM faculty f JOIN `employee_master_data` emd ON f.Id_No = emd.Emp_Id WHERE emd.Emp_Id != 'VHST02674' AND emd.Status = 'Working'");
    while ($employee = mysqli_fetch_assoc($all_employees)) {
      if (!isset($_POST['status'][$employee['Id_No']])) {
        $sql = "UPDATE faculty SET Status = 'Disabled' WHERE Id_No = '" . $employee['Id_No'] . "'";
        mysqli_query($link, $sql);
      }
    }
    echo '<script>alert("Access Updated Succesfully!");</script>';
  }
  ?>


  <!-- Scripts -->

  <!-- Export Table to Excel -->
  <script type="text/javascript">
    $('#export').on('click', function() {
      filename = 'faculty_credentials';
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
      window.frames["print_frame"].document.body.innerHTML += "<h2 style='text-align:center;'>Faculty Credentials List</h2>";
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