<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 61);

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

  <!-- Excel Links -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
  <script src="//cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>

  <!-- Bootstrap Links -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
  body {
    overflow-x: scroll;
  }

  .table-container {
    max-width: 1200px;
    max-height: 500px;
    overflow-x: scroll;
    display: none;
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
        <div class="p-2 col-lg-4 rounded">
          <select class="form-select" name="Class" id="cls" aria-label="Default select example">
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
      <div class="row justify-content-center mt-3">
        <div class="col-lg-4" style="color: red;">
          NOTE: Please Press Show before exporting to Excel
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
  <div class="container" id="alert-container" style="display: none;">
    <div class="row justify-content-center mt-4">
      <div class="col-lg-4">
        <div class="alert alert-success d-flex align-items-center" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
          </svg>
          <div>
            Now, You Can Export to Excel
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="container table-container" id="table-container">
    <table class="table table-striped table-hover" border="1">
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align: center;font-size:30px;color:red" colspan="3"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align: center;font-size:30px;color:red" colspan="2">LEDGER REPORT</th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
        <?php
        if (isset($_POST['show'])) {
          if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view ledger');
              location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
          }
          echo '<tr>
                  <th></th>
                  <th style="text-align: center;font-size:25px;color:red">NAME OF CLASS:</th>
                  <th style="text-align: center;font-size:25px;color:blue;" id="class_section">' . $class . ' ' . $section . '</th>
                </tr>
                <tr>
                  <th style="color:blue;">Id No.</th>
                  <th style="color:blue;">Name</th>
                  <th style="color:blue;">Previous Balance</th>
                  <th style="color:blue;">Commit Fee</th>
                  <th style="color:blue;">I Term</th>
                  <th style="color:blue;">RC No</th>
                  <th style="color:blue;">Date</th>
                  <th style="color:blue;">II Term</th>
                  <th style="color:blue;">RC No</th>
                  <th style="color:blue;">Date</th>
                  <th style="color:blue;">III Term</th>
                  <th style="color:blue;">RC No</th>
                  <th style="color:blue;">Date</th>
                  <th style="color:blue;">IV Term</th>
                  <th style="color:blue;">RC No</th>
                  <th style="color:blue;">Date</th>
                </tr>
              </thead>
              <tbody id="tbody">';
          if ($_POST['Class']) {
            $class = $_POST['Class'];
            echo '<script>document.getElementById("cls").value = "' . $class . '";</script>';
            if ($_POST['Section']) {
              $section = $_POST['Section'];
              echo '<script>document.getElementById("sec").value = "' . $section . '";</script>';
              echo '<script>document.getElementById("class_section").innerHTML = "' . $class . ' ' . $section . '";</script>';
              $sql = "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'";
              $result = mysqli_query($link, $sql);
              if (mysqli_num_rows($result) == 0) {
                echo "<script>alert('Invalid Class & Section!!')</script>";
              } else {
                while ($row = mysqli_fetch_assoc($result)) {
                  if (str_contains($row['Mobile'], ',')) {
                    $mobile = trim(explode(',', $row['Mobile'], 2)[0]);
                  } else if (str_contains($row['Mobile'], ' ')) {
                    $mobile = trim(explode(' ', $row['Mobile'], 2)[0]);
                  } else {
                    $mobile = $row['Mobile'];
                  }
                  echo '
                  <tr>
                  <td style="padding:5px;" rowspan="2">' . $row['Id_No'] . '<br/>' . $mobile . '</td>
                  <td style="padding-left:5px;">' . $row['First_Name'] . '  ' . $row['Sur_Name'] . '</td>
                  ';

                  $fees = array();

                  //Getting Total From Stu Fee Master Data
                  $sql1 = mysqli_query($link, "SELECT Last_Balance FROM `stu_fee_master_data` WHERE Id_No = '" . $row['Id_No'] . "' AND Type = 'School Fee'");
                  if (mysqli_num_rows($sql1) == 0) {
                    $fees['Balance'] = 0;
                  } else {
                    while ($row1 = mysqli_fetch_assoc($sql1)) {
                      $fees['Balance'] = (int)$row1['Last_Balance'];
                    }
                  }
                  echo '
                  <td style="padding:5px;">' . $fees['Balance'] . '</td>
                  </tr>
                  ';

                  //Vehicle Fee

                  //Getting Total From Stu Fee Master Data
                  $van_sql1 = mysqli_query($link, "SELECT Last_Balance FROM `stu_fee_master_data` WHERE Id_No = '" . $row['Id_No'] . "' AND Type = 'Vehicle Fee'");
                  if (mysqli_num_rows($van_sql1) == 0) {
                    $fees['Van_Balance'] = 0;
                  } else {
                    while ($van_row1 = mysqli_fetch_assoc($van_sql1)) {
                      $fees['Van_Balance'] = (int)$van_row1['Last_Balance'];
                    }
                  }
                  echo '<tr>
                  <td style="padding:5px;">' . $row['Van_Route'] . '</td>
                  <td style="padding:5px;">' . $fees['Van_Balance'] . '</td>
                  </tr>';
                  $i++;
                }
                echo "<script>document.getElementById('alert-container').style.display = 'block';</script>";
              }
            } else {
              echo "<script>alert('Please Select Section!!')</script>";
            }
          } else {
            echo "<script>alert('Please Select Class!!')</script>";
          }
        }
        ?>
        </tr>
        </tbody>
    </table>
  </div>
  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


  <!-- Scripts -->

  <!-- Export Table to Excel -->
  <script type="text/javascript">
    $('#export').on('click', function() {
      stuclass = '<?php echo $class; ?>';
      stusection = '<?php echo $section; ?>';
      filename = stuclass + stusection + "_FeeLedger";
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
</body>

</html>