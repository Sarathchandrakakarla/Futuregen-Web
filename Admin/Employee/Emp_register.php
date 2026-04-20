<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 50);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
  <link rel="stylesheet" href="/Victory/css/form-style.css" />
  <!-- Boxiocns CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
</head>
<style>
  #sign-out {
    display: none;
  }

  @media screen and (max-width:920px) {
    #sign-out {
      display: block;
    }
  }
</style>

<body onload="getDate()">
  <?php
  include '../sidebar.php';
  ?>

  <div class="container">

    <div class="content">
      <div class="title">Employee Personal Details</div>
      <form action="" method="POST" onsubmit="if(!confirm('Confirm to Add New Employee?')){return false;}else{return true;}">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Id No. <span class="required">*</span></span>
            <input type="text" placeholder="Enter Id No" name="Id_No" id="id_no" oninput="this.value = this.value.toUpperCase()" required />
          </div>
          <div class="input-box">
            <span class="details">Full Name <span class="required">*</span></span>
            <input type="text" placeholder="Enter Fullname" name="First_Name" id="first_name" required />
          </div>
          <div class="input-box">
            <span class="details">Surname <span class="required">*</span></span>
            <input type="text" placeholder="Enter Surname" name="Sur_Name" id="sur_name" required />
          </div>
          <div class="input-box">
            <span class="details">Father Name <span class="required">*</span></span>
            <input type="text" placeholder="Enter Father Name" name="Father_Name" id="father_name" required />
          </div>
          <div class="input-box">
            <span class="details">Qualification
              <input type="text" placeholder="Enter Qualification" id="qualification" name="Qualification" />
          </div>
          <div class="gender-details">
            <span class="gender-title">Relation <span class="required">*</span></span>
            <div class="category">
              <input type="radio" value="s/o" id="s/o" name="Relation" />
              <span><label for="s/o">S/o</label></span>
              <input type="radio" value="d/o" id="d/o" name="Relation" />
              <span><label for="d/o">D/o</label></span>
              <input type="radio" value="w/o" id="w/o" name="Relation" />
              <span><label for="w/o">W/o</label></span>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Date Of Birth <span class="required">*</span></span>
            <input type="date" name="DOB" id="dob" required />
          </div>
          <div class="input-box">
            <span class="details">Mobile Number <span class="required">*</span></span>
            <input type="text" maxlength="10" minlength="10" id="mobile" placeholder="Enter Mobile No." name="Mobile" required />
          </div>
          <div class="input-box">
            <span class="details">Aadhar Number
              <input type="text" placeholder="Enter Aadhar No." id="aadhar" maxlength="12" name="Aadhar" />
          </div>
        </div>
        <div class="title">Employee Address Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">House No.
              <input type="text" placeholder="Enter House No." id="house_no" name="House_No" />
          </div>
          <div class="input-box">
            <span class="details">Area <span class="required">*</span></span>
            <input type="text" placeholder="Enter Area" name="Area" id="area" required />
          </div>
          <div class="input-box">
            <span class="details">Village/Town <span class="required">*</span></span>
            <input type="text" placeholder="Enter Village" name="Village" id="village" required />
          </div>
        </div>
        <div class="title">Salary Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">Date of Join <span class="required">*</span></span>
            <input type="date" name="DOJ" id="doj" required />
          </div>
          <div class="input-box">
            <span class="details">Designation</span>
            <input type="text" placeholder="Enter Designation" id="designation" name="Designation" />
          </div>
          <div class="input-box">
            <span class="details">Salary</span>
            <input type="text" placeholder="Enter Salary" id="salary" name="Salary" />
          </div>
          <div class="input-box">
            <span class="details">PF</span>
            <input type="text" placeholder="Enter PF" id="pf" name="PF" />
          </div>
          <div class="input-box">
            <span class="details">Date of PF Commit</span>
            <input type="date" name="DOPF" id="dopf" />
          </div>
          <div class="input-box">
            <span class="details">Working Status</span>
            <select name="Status" id="status">
              <option value="Working" selected>Working</option>
              <option value="Left Service">left Service</option>
            </select>
          </div>
        </div>
        <div class="title">Bank Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">Bank A/C No.</span>
            <input type="text" placeholder="Enter A/C No." id="ac_no" name="AC_No" />
          </div>
          <div class="input-box">
            <span class="details">Bank Name</span>
            <input type="text" placeholder="Enter Bank Name" id="bank_name" name="Bank_Name" />
          </div>
        </div>
        <div class="button">
          <div class="btn-wrapper"
            <?php if (!can('create', MENU_ID)) { ?>
            title="You don't have permission to insert employee data"
            <?php } ?>>
            <input type="submit" name="add" value="Insert" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> />
          </div>
          <input type="reset" value="Clear" />
        </div>
      </form>
    </div>
  </div>

  <?php include '../../link.php';
  function validate($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  function format_date($date)
  {
    $arr = explode('-', $date);
    $t = $arr[0];
    $arr[0] = $arr[2];
    $arr[2] = $t;
    $date = implode('-', $arr);
    return $date;
  }
  if (isset($_POST["add"])) {
    if (!can('create', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to insert employee data');
        location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    $id = validate($_POST['Id_No']);
    $firstname = validate($_POST['First_Name']);
    $surname = validate($_POST['Sur_Name']);
    $fathername = validate($_POST['Father_Name']);
    $qualification = validate($_POST['Qualification']);
    $dob = validate($_POST['DOB']);
    $mobile = validate($_POST['Mobile']);
    $aadhar = validate($_POST['Aadhar']);
    $houseno = validate($_POST['House_No']);
    $area = validate($_POST['Area']);
    $village = validate($_POST['Village']);
    $doj = validate($_POST['DOJ']);
    $designation = validate($_POST['Designation']);
    $salary = validate($_POST['Salary']);
    $pf = validate($_POST['PF']);
    $dopf = validate($_POST['DOPF']);
    $status = validate($_POST['Status']);
    $ac = validate($_POST['AC_No']);
    $bank = validate($_POST['Bank_Name']);

    echo "<script>document.getElementById('id_no').value = '" . $id . "'</script>";
    echo "<script>document.getElementById('first_name').value = '" . $firstname . "'</script>";
    echo "<script>document.getElementById('sur_name').value = '" . $surname . "'</script>";
    echo "<script>document.getElementById('father_name').value = '" . $fathername . "'</script>";
    echo "<script>document.getElementById('qualification').value = '" . $qualification . "'</script>";
    echo "<script>document.getElementById('dob').value = '" . $dob . "'</script>";
    echo "<script>document.getElementById('mobile').value = '" . $mobile . "'</script>";
    echo "<script>document.getElementById('aadhar').value = '" . $aadhar . "'</script>";
    echo "<script>document.getElementById('house_no').value = '" . $houseno . "'</script>";
    echo "<script>document.getElementById('area').value = '" . $area . "'</script>";
    echo "<script>document.getElementById('village').value = '" . $village . "'</script>";
    echo "<script>document.getElementById('doj').value = '" . $doj . "'</script>";
    echo "<script>document.getElementById('designation').value = '" . $designation . "'</script>";
    echo "<script>document.getElementById('salary').value = '" . $salary . "'</script>";
    echo "<script>document.getElementById('pf').value = '" . $pf . "'</script>";
    echo "<script>document.getElementById('dopf').value = '" . $dopf . "'</script>";
    echo "<script>document.getElementById('ac_no').value = '" . $ac . "'</script>";
    echo "<script>document.getElementById('bank_name').value = '" . $bank . "'</script>";

    if ($_POST['Relation']) {
      $relation = validate($_POST['Relation']);
      echo "<script>document.getElementById('" . $relation . "').checked = true;</script>";
    } else {
      echo "<script>alert('Please Select Relation!')</script>";
    }

    $doj = format_date($doj);
    $dob = format_date($dob);
    $dopf = format_date($dopf);

    if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `employee_master_data` WHERE Emp_Id = '$id'")) != 0) {
      echo "<script>alert('Employee Already Exists!');</script>";
    } else {
      $sql = mysqli_query($link, "INSERT INTO `employee_master_data` VALUES('', '$id'
        , '$firstname', '$surname', '$fathername', '$qualification',
        '$dob', '$relation', '$mobile', '$aadhar', '$houseno', '$area','$village','$doj','$designation','$salary','$pf','$dopf','$status','$ac','$bank')");
      if ($sql) {
        echo
        "
		<script>
		alert('Employee Data Inserted Successfully!');
        location.replace('');
		</script>
		";
      } else {
        echo
        "
		<script>
		alert('Employee Data Insertion Failed!');
		</script>
		";
      }
    }
  }
  ?>
  <!-- Scripts -->

  <!-- Get Today Date -->
  <script>
    function getDate() {
      var today = new Date();
      date = today.getFullYear() + '-' + ('0' + (today.getMonth() + 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2);
      document.getElementById("doj").value = date;
    }
  </script>
</body>

</html>