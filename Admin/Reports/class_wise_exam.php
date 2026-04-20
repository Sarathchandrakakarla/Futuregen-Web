<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 7);

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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
  body {
    overflow-x: scroll;
  }

  .table-container {
    max-width: 1000px;
    max-height: 500px;
    margin-left: 8%;
    overflow-x: scroll;
  }

  #section {
    text-align: center;
  }

  .delete {
    cursor: pointer;
    font-size: 30px;
  }

  .modify {
    cursor: pointer;
    font-size: 30px;
  }

  .save {
    cursor: pointer;
    font-size: 30px;
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
          <p style="color: red;">NOTE: click show without selecting class for All Classes</p>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row justify-content-center mt-4">
        <div class="col-lg-3">
          <button class="btn btn-primary" type="submit" name="show">Show</button>
          <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
        </div>
      </div>
    </div>
  </form>
  <div class="container">
    <div class="row justify-content-center mt-4">
      <div class="col-lg-5">
        <h3><b>Class Wise Exam Details Report</b></h3>
      </div>
    </div>
  </div>
  <div class="container table-container" id="table-container">
    <table class="table table-striped table-hover">
      <thead class="bg-secondary text-light">
        <tr>
          <th id="1">S.No</th>
          <th>Class</th>
          <th>Exam Name</th>
          <th>Max Marks</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tbody">
        <tr>
          <?php
          if (isset($_POST['show'])) {
            $class = $_POST['Class'];
            if ($class == '') {
              $sql = "SELECT * FROM `class_wise_examination` ORDER BY Class";
            } else {
              echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
              $sql = "SELECT * FROM `class_wise_examination` WHERE Class = '$class'";
            }
            $result = mysqli_query($link, $sql);
            $i = 1;
            while ($row = mysqli_fetch_assoc($result)) {
              echo '<tr>
                <td>' . $i . '</td>
                <td>' . $row['Class'] . '</td>
                <td id="' . $i . ' exam">' . $row['Exam'] . '</td>
                <td id="' . $i . ' max">' . $row['Max_Marks'] . '</td>
                <td><i class="bx bx-trash delete"></i><i class="bx bx-edit modify"></i></i></td>
                </tr>';
              $i++;
            }
          }
          ?>
        </tr>
      </tbody>
    </table>
  </div>
  <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>


  <!-- Scripts -->

  <!-- Print Table -->
  <script type="text/javascript">
    function printDiv() {
      window.frames["print_frame"].document.body.innerHTML = document.querySelector('.table-container').innerHTML;
      window.frames["print_frame"].window.focus();
      window.frames["print_frame"].window.print();
    }
  </script>
  <!-- Modify Row -->
  <script type="text/javascript">
    $(".modify").click(function() {
      cls = $(this).parent().siblings().eq(1).text();
      exm = $(this).parent().siblings().eq(2).text();
      max = $(this).parent().siblings().eq(3).text();
      (async () => {

        const {
          value: data
        } = await Swal.fire({
          showCloseButton: true,
          html: "<label for='text'>Class:</label><input type='text' id='class' value='" + cls + "' disabled><br>" +
            "<label for='text'>Exam Name:</label><input type='text' id='exam' value='" + exm + "' disabled><br>" +
            "<label for='text'>Max Marks:</label><input type='text' id='max' value='" + max + "'>",
          preConfirm: () => {
            return [
              document.getElementById('class').value,
              document.getElementById('exam').value,
              document.getElementById('max').value
            ]
          }
        })

        if (data) {
          arr = [];
          for (var key in data) {
            arr.push(data[key]);
          }
          stuclass = arr[0];
          exm = arr[1];
          marks = arr[2];
          $.ajax({
            type: 'post',
            url: 'modify_row.php',
            data: {
              class: stuclass,
              exam: exm,
              max: marks
            },
            success: function(data) {
              alert('Examination Modified Successfully!! Refresh to get data updated!')
            }
          });
        }

      })()
    });
  </script>
  <!-- Delete Row -->
  <script type="text/javascript">
    $(".delete").click(function() {
      cls = $(this).parent().siblings().eq(1).text();
      exm = $(this).parent().siblings().eq(2).text();
      if (!confirm('Confirm to delete ' + cls + ' ' + exm + ' Exam?')) {
        return;
      } else {
        $.ajax({
          type: 'post',
          url: 'delete_row.php',
          data: {
            class: cls,
            exam: exm
          },
          success: function(data) {
            alert('Examination Deleted Successfully!! Refresh to get data updated!')
          }
        });
      }
    });
  </script>
</body>

</html>