<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 14);

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
        <h3><b>Images Missing Details Report</b></h3>
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
          <th style="padding:5px;">Id No.</th>
          <th>Name</th>
          <th>Class</th>
          <th>Missing Images</th>
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
            $classes = ['PreKG', 'LKG', 'UKG'];
            for ($i = 1; $i <= 10; $i++) {
              $classes[] = $i . " CLASS";
            }
            $sections = ['A', 'B', 'C', 'D', 'E'];
            $i = 1;
            foreach ($classes as $class) {
              foreach ($sections as $section) {
                $query1 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
                if (mysqli_num_rows($query1) > 0) {
                  while ($row1 = mysqli_fetch_assoc($query1)) {
                    $id = $row1['Id_No'];
                    $stu_img = file_exists("../../Images/stu_img/" . $id . ".jpg");
                    $parent_img_male = file_exists("../../Images/parent_img_male/" . $id . ".jpg");
                    $parent_img_female = file_exists("../../Images/parent_img_female/" . $id . ".jpg");
                    if (!$stu_img || !$parent_img_male || !$parent_img_female) {
                      echo "
                      <tr>
                        <td style='padding:5px;'>" . $i . "</td>
                        <td style='padding:5px;'>" . $id . "</td>
                        <td style='padding:5px;'>" . $row1['First_Name'] . "</td>
                        <td style='padding:5px;white-space:nowrap'>" . $class . " " . $section . "</td>
                        <td style='padding:5px;white-space:nowrap'>";
                      if (!$stu_img) {
                        echo "Student Img, ";
                      }
                      if (!$parent_img_male) {
                        echo "Father Img, ";
                      }
                      if (!$parent_img_female) {
                        echo "Mother Img ";
                      }
                      echo "</td>
                      </tr>
                      ";
                      $i++;
                    }
                  }
                }
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

  <!-- Export Table to Excel -->
  <script type="text/javascript">
    $('#export').on('click', function() {
      filename = "Missing Images Report";
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