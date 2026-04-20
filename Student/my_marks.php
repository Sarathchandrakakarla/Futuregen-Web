<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 133);

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
  table tbody tr td img {
    margin-left: 50px;
    border-radius: 10px;
  }

  .table-container {
    max-width: 800px;
  }

  @media screen and (max-width:576px) {
    .container {
      margin-left: 17%;
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

<body>
  <?php include 'sidebar.php'; ?>
  <form action="" method="POST" autocomplete="off">
    <div class="container">
      <div class="row justify-content-center mt-5">
        <div class="col-lg-3">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="Exam_Type" id="all_exams" onchange="examType()" checked value="All_Exams">
            <label class="form-check-label" for="all_exams">All Exams</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="Exam_Type" id="single_exam" onchange="examType()" value="Single_Exam">
            <label class="form-check-label" for="single_exam">Single Exam</label>
          </div>
        </div>
        <div class="col-lg-3" id="exam_row" hidden>
          <select class="form-select" name="Exam" id="exam" aria-label="Default select example">
            <option selected disabled>-- Select Exam --</option>
          </select>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row justify-content-center mt-4">
        <div class="col-lg-3">
          <div class="btn-wrapper"
            <?php if (!can('view', MENU_ID)) { ?>
            title="You don't have permission to view your marks! Please Contact School Office"
            <?php } ?>>
            <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
          </div>
          <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
          <div class="btn-wrapper"
            <?php if (!can('print', MENU_ID)) { ?>
            title="You don't have permission to print your marks! Please Contact School Office"
            <?php } ?>>
            <button class="btn btn-success" onclick="printDiv();return false;" <?php echo !can('print', MENU_ID) ? 'disabled' : ''; ?>>Print</button>
          </div>
        </div>
      </div>
    </div>
  </form>
  <?php
  if (isset($_POST['show'])) {
    if (!can('view', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to view your marks! Please Contact School Office');
        location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    $id = $_SESSION['Id_No'];
    $class = $_SESSION['Stu_Class'];
    $examtype = $_POST['Exam_Type'];
    if ($examtype == "Single_Exam") {

      if ($_POST['Exam']) {
        $exam = $_POST['Exam'];

        //Arrays
        $subs = array();
        $marks = array();
        $max = 0;

        //Queries
        $s = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class' AND Exam = '$exam'");
        while ($r = mysqli_fetch_assoc($s)) {
          $max = $r['Max_Marks'];
        }

        $query1 = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
        while ($row = mysqli_fetch_assoc($query1)) {
          array_push($subs, $row['Subjects']);
        }
        $sub_count = count($subs);

        $query2 = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
        $tot = 0;
        while ($row5 = mysqli_fetch_assoc($query2)) {
          for ($i = 1; $i <= $sub_count; $i++) {
            array_push($marks, $row5['sub' . $i]);
            $tot += (int)$row5['sub' . $i];
          }
          array_push($marks, $tot);
        }
        echo '<div class="container table-container" id="table-container">
          <table class="table table-hover" border="1">
            <tbody id="tbody">
            <tr>';
        echo '<td class="bg-secondary text-light" colspan="2" style="width:1000px;text-align:center;"><b>' . $exam . '</b></td></tr>';
        $c = 0;
        $tot_max = 0;
        foreach ($subs as $sub) {
          echo '<tr>
        <tr>
        <th style="height:30px;">' . $sub . '</th>';
          if ($marks[$c] == "A") {

            echo '<td style="text-align:center">' . $marks[$c] . '</td>';
          } else {
            echo '<td style="text-align:center">' . $marks[$c] . '/' . $max . '</td>';
          }
          echo '</tr>
        </tr>';
          $tot_max += $max;
          $c++;
        }
        $percentage = round(($marks[$c] / $tot_max) * 100, 1);
        if ($percentage >= 80 && $percentage <= 100) {
          $grade = "Excellent";
        } else if ($percentage >= 70 && $percentage < 80) {
          $grade = "Good";
        } else if ($percentage >= 60 && $percentage < 70) {
          $grade = "Satisfactory";
        } else if ($percentage >= 50 && $percentage < 60) {
          $grade = "Above Average";
        } else if ($percentage >= 35 && $percentage < 50) {
          $grade = "Average";
        } else if ($percentage > 0 && $percentage < 35) {
          $grade = "Below Average";
        } else {
          $grade = "";
        }
        echo '<tr>
      <tr>
      <th style="height:30px;">Total</th>
      <td style="text-align:center">' . $marks[$c] . '/' . $tot_max . '</td>
      </tr>
      <tr>
      <th style="height:30px;">Percentage</th>
      <td style="text-align:center">' . $percentage . '</td>
      </tr>
      <tr>
      <th style="height:30px;">Grade</th>
      <td style="text-align:center">' . $grade . '</td>
      </tr>
      </tr>';
      } else {
        echo "<script>alert('Please Select Exam!')</script>";
      }
    } else if ($examtype == "All_Exams") {

      //Arrays
      $exams = array();
      $subs = array();
      $max = array();
      $marks = array();
      $sub_count = array();
      $full_marks = array();
      if (!str_contains($class, ' CLASS')) {
        echo "<script>alert(Data Not Available for this Student)</script>";
      } else {
        $exm_sql = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class'");
        while ($row2 = mysqli_fetch_assoc($exm_sql)) {
          array_push($exams, $row2['Exam']);
        }
        foreach ($exams as $exam) {
          $sub_sql = mysqli_query($link, "SELECT * FROM `class_wise_subjects` WHERE Class = '$class' AND Exam = '$exam'");
          $sub_count[$exam] = mysqli_num_rows($sub_sql);
          $temp = array();
          while ($row3 = mysqli_fetch_assoc($sub_sql)) {
            array_push($temp, $row3['Subjects']);
          }
          $subs[$exam] = $temp;
          $temp = array();
        }


        foreach ($exams as $exam) {
          $sub_sql = mysqli_query($link, "SELECT * FROM `class_wise_examination` WHERE Class = '$class' AND Exam = '$exam'");
          while ($row3 = mysqli_fetch_assoc($sub_sql)) {
            $max[$exam] = $row3['Max_Marks'];
          }
        }

        foreach ($exams as $exam) {
          $marks_sql = mysqli_query($link, "SELECT * FROM `stu_marks` WHERE Id_No = '$id' AND Exam = '$exam'");
          $temp1 = array();
          while ($row4 = mysqli_fetch_assoc($marks_sql)) {
            $i = 1;
            foreach ($subs[$exam] as $sub) {
              $temp1[$sub] = $row4['sub' . $i];
              $i++;
            }
            $i = 1;
            $full_marks[$exam] = $temp1;
          }
        }

        echo '<div class="container table-container" id="table-container">
          <table class="table table-hover" border="1">
            <tbody id="tbody">
            <tr>';

        foreach ($exams as $exam) {
          foreach (array_keys($full_marks) as $exm) {
            if ($exam == $exm) {
              if ($sub_count[$exam] != 0) {
                echo '<td class="bg-secondary text-light" colspan="2" style="width:1000px;text-align:center;"><b>' . $exam . '</b></td>';
                echo '</tr>
                    <tr>';
                $tot = 0;
                $max_tot = 0;
                foreach ($subs[$exam] as $sub) {
                  echo '<tr>
                          <th style="height:30px;">' . $sub . '</th>';
                  if ($full_marks[$exam][$sub] == "A") {
                    echo '<td style="text-align:center;padding-left:10px;">' . $full_marks[$exam][$sub] . '</td>';
                  } else {
                    echo '<td style="text-align:center;padding-left:10px;">' . $full_marks[$exam][$sub] . '/' . $max[$exam] . '</td>';
                  }
                  echo '</tr>';
                  $tot += (int)$full_marks[$exam][$sub];
                  $max_tot += $max[$exam];
                }
                $percentage = round(($tot / $max_tot) * 100, 1);
                if ($percentage >= 80 && $percentage <= 100) {
                  $grade = "Excellent";
                } else if ($percentage >= 70 && $percentage < 80) {
                  $grade = "Good";
                } else if ($percentage >= 60 && $percentage < 70) {
                  $grade = "Satisfactory";
                } else if ($percentage >= 50 && $percentage < 60) {
                  $grade = "Above Average";
                } else if ($percentage >= 35 && $percentage < 50) {
                  $grade = "Average";
                } else if ($percentage > 0 && $percentage < 35) {
                  $grade = "Below Average";
                } else {
                  $grade = "";
                }
                echo '<tr>
                    <th>Total</th>
                    <td style="text-align:center;padding-left:10px;">' . $tot . '/' . $max_tot . '</td>
                    </tr>';
                echo '<tr>
                    <th>Percentage</th>
                    <td style="text-align:center;padding-left:10px;">' . $percentage  . '</td>
                    </tr>';
                echo '<tr>
                    <th>Grade</th>
                    <td style="text-align:center;padding-left:10px;">' . $grade  . '</td>
                    </tr>';
                $tot = 0;

                echo '</tr>';
              }
            }
          }
        }
      }
    }
  }
  ?>
  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


  <!-- Scripts -->

  <!-- Change labels -->
  <script type="text/javascript">
    function examType() {
      exam_row = document.getElementById('exam_row');
      if (document.getElementById('all_exams').checked) {
        p = document.getElementById('all_exams').value;
        exam_row.hidden = 'hidden';
      } else if (document.getElementById('single_exam').checked) {
        p = document.getElementById('single_exam').value;
        exam_row.hidden = '';
        $('#exam').html('');
        $.ajax({
          type: 'post',
          url: 'temp.php',
          data: {},
          success: function(data) {
            console.log(data);
            $('#exam').html(data);
          }
        })
      }
    }
  </script>

  <!-- Print Table -->
  <script type="text/javascript">
    function printDiv() {
      window.frames["print_frame"].document.body.innerHTML = "<div class='container'><table><tr><td><img src='<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png' alt='...' width='60px'></td><td><p style='font-size:30px;'>Student Marks Details Report</p></td></tr></table></div>";
      window.frames["print_frame"].document.body.innerHTML += "<p><b>Id No: </b><?php echo $_SESSION['Id_No']; ?></p>";
      window.frames["print_frame"].document.body.innerHTML += "<p><b>Name: </b><?php echo $_SESSION['First_Name']; ?> </p>";
      window.frames["print_frame"].document.body.innerHTML += "<p><b>Name of Examination: </b><?php if ($examtype == "All_Exams") {
                                                                                                echo "All Exams";
                                                                                              } else {
                                                                                                echo $exam;
                                                                                              } ?></p>";
      window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
      window.frames["print_frame"].window.focus();
      window.frames["print_frame"].window.print();
    }
  </script>
</body>

</html>