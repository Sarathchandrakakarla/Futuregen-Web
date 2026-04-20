<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 30);

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
    max-width: 1200px;
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
      <div class="row justify-content-center mt-5">
        <div class="col-lg-1">
          <strong>Date:</strong>
        </div>
        <div class="col-lg-3 rounded">
          <input type="date" class="form-control" name="Date" id="date" required>
        </div>
      </div>
      <div class="row justify-content-center mt-2">
        <div class="col-lg-1">
          <strong>Class:</strong>
        </div>
        <div class="col-lg-3 rounded">
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
      </div>
      <div class="row justify-content-center mt-2">
        <div class="col-lg-1">
          <strong>Section:</strong>
        </div>
        <div class="col-lg-3 rounded">
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
        <h3><b>Class Wise Homework Report</b></h3>
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
    <table class="table table-striped table-hover" border="1">
      <thead class="bg-secondary text-light">
        <tr>
          <th style="padding:5px;">S.No</th>
          <th style="padding:5px;">Id No.</th>
          <th>Name</th>
          <th>Mobile</th>
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
                echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";
                echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
                $query1 = mysqli_query($link, "SELECT DISTINCT Subjects AS Subject FROM `class_wise_subjects` WHERE Class = '$class'");
                $subjects = [];
                while ($row1 = mysqli_fetch_assoc($query1)) {
                  $subjects[] = $row1['Subject'];
                  echo '
                  <th>' . $row1['Subject'] . '</th>
                  ';
                }
              }
            }
          }
          ?>
        </tr>
      </thead>
      <tbody id="tbody">
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
        echo "<script>date.value = '" . date('Y-m-d') . "';</script>";
        if (isset($_POST['show'])) {
          if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
          }
          $date = $_POST['Date'];
          echo "<script>date.value = '" . $date . "';</script>";
          $date = format_date($date);
          if ($_POST['Class']) {
            $class = $_POST['Class'];
            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
            if ($_POST['Section']) {
              $section = $_POST['Section'];
              echo "<script>document.getElementById('class_label').innerHTML = '" . $class . ' ' . $section . "'</script>";
              echo "<script>document.getElementById('sec').value = '" . $section . "'</script>";

              $query2 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Stu_Class = '$class' AND Stu_Section = '$section'");
              if (mysqli_num_rows($query2) == 0) {
                echo "<script>alert('Class or Section Not Available!')</script>";
              } else {
                $query3 = mysqli_query($link, "SELECT smd.Id_No, smd.First_Name, smd.Mobile, cws.Subjects AS Subject, CASE WHEN hw.Subject IS NULL OR (hw.Image IS NULL AND hw.Text IS NULL) THEN 'Not Given' WHEN sh.Id_No IS NULL THEN 'Not Viewed Yet' ELSE 'Viewed' END AS View_Status FROM student_master_data smd CROSS JOIN (SELECT DISTINCT Subjects FROM class_wise_subjects WHERE Class = '$class') cws LEFT JOIN student_homework sh ON smd.Id_No = sh.Id_No AND sh.Subject = cws.Subjects AND sh.Date = '$date' LEFT JOIN homework hw ON hw.Subject = cws.Subjects AND hw.Date = '$date' AND hw.Class = '$class' AND hw.Section = '$section' WHERE smd.Stu_Class = '$class' AND smd.Stu_Section = '$section'");
                $students = [];
                while ($row = mysqli_fetch_array($query3)) {
                  $id = $row[0];
                  $name = $row[1];
                  $mobile = $row[2];
                  $subject = $row[3];
                  $status = $row[4];

                  $mobile = trim(explode(',', $mobile)[0]);
                  $mobile = trim(explode(' ', $mobile)[0]);

                  if (!isset($students[$id])) {
                    $students[$id] = ['Name' => $name, 'Mobile' => $mobile, "Subjects" => []];
                  }

                  $students[$id]['Subjects'][$subject] = $status;
                }
                $i = 1;
                foreach ($students as $id => $details) {
                  echo '
                    <tr>
                      <td>' . $i . '</td>
                      <td>' . $id . '</td>
                      <td>' . $details['Name'] . '</td>
                      <td>' . $details['Mobile'] . '</td>
                      ';
                  foreach ($subjects as $subject) {
                    echo '<td style="white-space:nowrap;">' . $details['Subjects'][$subject] . '</td>';
                  }
                  echo '</tr>
                    ';
                  $i++;
                }
              }
            } else {
              echo "<script>alert('Please Select Section!')</script>";
            }
          } else {
            echo "<script>alert('Please Select Class!')</script>";
          }
        }
        ?>
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
      filename = stuclass + stusection + '_Homework Report';
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
      window.frames["print_frame"].document.body.innerHTML += "<p style='font-size:20px;'><b>Class: </b> <?php echo $class . ' ' . $section; ?></p>";
      window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
      window.frames["print_frame"].window.focus();
      window.frames["print_frame"].window.print();
    }
  </script>
</body>

</html>