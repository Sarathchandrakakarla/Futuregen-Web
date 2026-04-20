<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 4);

requireLogin();
requireMenuAccess(MENU_ID);

if (!$_SESSION['Emp_Id']) {
  echo "<script>
  alert('Employee Id Not Rendered');
  location.replace('show_emp_page.php');
  </script>";
}

if (!can('update', MENU_ID)) {
  echo "<script>alert('You don\'t have permission to update employee data');
    location.replace('/Victory/Admin/Student/show_emp_page.php')</script>";
  exit;
}
error_reporting(0);
?>


<?php
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
if (isset($_POST["update"])) {
  if (!can('update', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update employee data');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['Id_No']);
  $firstname = validate($_POST['First_Name']);
  $surname = validate($_POST['Sur_Name']);
  $fathername = validate($_POST['Father_Name']);
  $qualification = validate($_POST['Qualification']);
  $relation = validate($_POST['Relation']);
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
  $bank_name = validate($_POST['Bank_Name']);

  if ($dob != "") {
    $dob = format_date($dob);
  }
  if ($doj != "") {
    $doj = format_date($doj);
  }
  if ($dopf != "") {
    $dopf = format_date($dopf);
  }

  $sql = mysqli_query($link, "UPDATE `employee_master_data` SET 
        Emp_First_Name = '$firstname', Emp_Sur_Name = '$surname', Father_Name = '$fathername', Qualification = '$qualification',
        DOB = '$dob', Relation = '$relation', Mobile = '$mobile', Aadhar = '$aadhar', House_No = '$houseno', Area = '$area',
        Village = '$village', DOJ = '$doj', Designation = '$designation', Salary = '$salary', PF = '$pf', DOPF = '$dopf',
        Status = '$status', AC_No = '$ac', Bank_Name = '$bank' WHERE Emp_Id = '$id'");
  if ($sql) {
    echo
    "
		<script>
		alert('Employee Data Updated Successfully!');
        location.replace('');
		</script>
		";
  } else {
    echo
    "
		<script>
		alert('Employee Data Updation Failed!');
		</script>
		";
  }
}
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
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

<body>
  <?php
  include '../sidebar.php';
  ?>

  <div class="container">

    <div class="content">
      <div class="title">Employee Personal Details</div>
      <form action="" method="POST" onsubmit="if(!confirm('Confirm to Update Employee Data?')){return false}else{return true}">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Id No. <span class="required">*</span></span>
            <input type="text" placeholder="Enter Id No" value="<?php echo $_SESSION['Emp_Id'] ?>" name="Id_No" oninput="this.value = this.value.toUpperCase()" required />
          </div>
          <div class="input-box">
            <span class="details">Full Name <span class="required">*</span></span>
            <input type="text" placeholder="Enter Fullname" value="<?php echo $_SESSION['Emp_First_Name'] ?>" name="First_Name" required />
          </div>
          <div class="input-box">
            <span class="details">Surname</span>
            <input type="text" placeholder="Enter Surname" value="<?php echo $_SESSION['Emp_Sur_Name'] ?>" name="Sur_Name" />
          </div>
          <div class="input-box">
            <span class="details">Father Name/Husband Name</span>
            <input type="text" placeholder="Enter Father Name" value="<?php echo $_SESSION['Father_Name'] ?>" name="Father_Name" />
          </div>
          <div class="input-box">
            <span class="details">Qualification</span>
            <input type="text" placeholder="Enter Qualification" value="<?php echo $_SESSION['Qualification'] ?>" name="Qualification" />
          </div>
          <div class="gender-details">
            <span class="gender-title">Relation</span>
            <div class="category">
              <input type="radio" id="s/o" value="s/o" name="Relation" <?php echo ($_SESSION['Relation'] == 's/o') ? 'checked' : '' ?> />
              <span><label for="s/o">S/o</label></span>
              <input type="radio" id="d/o" value="d/o" name="Relation" <?php echo ($_SESSION['Relation'] == 'd/o') ? 'checked' : '' ?> />
              <span><label for="d/o">D/o</label></span>
              <input type="radio" id="w/o" value="w/o" name="Relation" <?php echo ($_SESSION['Relation'] == 'w/o') ? 'checked' : '' ?> />
              <span><label for="w/o">W/o</label></span>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Date Of Birth</span>
            <input type="date" name="DOB" id="dob" required />
          </div>
          <div class="input-box">
            <span class="details">Mobile Number</span>
            <input type="text" maxlength="10" minlength="10" placeholder="Enter Mobile No." value="<?php echo $_SESSION['Mobile'] ?>" name="Mobile" required />
          </div>
          <div class="input-box">
            <span class="details">Aadhar Number</span>
            <input type="text" placeholder="Enter Aadhar No." maxlength="12" minlength="12" value="<?php echo $_SESSION['Aadhar'] ?>" name="Aadhar" required />
          </div>
        </div>
        <div class="title">Employee Address Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">House No.</span>
            <input type="text" placeholder="Enter House No." value="<?php echo $_SESSION['House_No'] ?>" name="House_No" />
          </div>
          <div class="input-box">
            <span class="details">Area</span>
            <input type="text" placeholder="Enter Area" value="<?php echo $_SESSION['Area'] ?>" name="Area" />
          </div>
          <div class="input-box">
            <span class="details">Village/Town</span>
            <input type="text" placeholder="Enter Village" value="<?php echo $_SESSION['Village'] ?>" name="Village" />
          </div>
        </div>
        <div class="title">Salary Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">Date of Join</span>
            <input type="date" name="DOJ" id="doj" />
          </div>
          <div class="input-box">
            <span class="details">Designation</span>
            <input type="text" placeholder="Enter Designation" value="<?php echo $_SESSION['Designation'] ?>" name="Designation" />
          </div>
          <div class="input-box">
            <span class="details">Salary</span>
            <input type="text" placeholder="Enter Salary" value="<?php echo $_SESSION['Salary'] ?>" name="Salary" />
          </div>
          <div class="input-box">
            <span class="details">PF</span>
            <input type="text" placeholder="Enter PF" value="<?php echo $_SESSION['PF'] ?>" name="PF" />
          </div>
          <div class="input-box">
            <span class="details">Date of PF Commit</span>
            <input type="date" name="DOPF" id="dopf" />
          </div>
          <div class="input-box">
            <span class="details">Working Status</span>
            <select name="Status" id="status">
              <option value="--Select Status--" disabled>--Select Status--</option>
              <option value="Working" <?php if (isset($_SESSION['Status']) && $_SESSION['Status'] == "Working") {
                                        echo "selected";
                                      } ?>>Working</option>
              <option value="Left Service" <?php if (isset($_SESSION['Status']) && $_SESSION['Status'] == "Left Service") {
                                              echo "selected";
                                            } ?>>left Service</option>
            </select>
          </div>
        </div>
        <div class="title">Bank Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">Bank A/C No.</span>
            <input type="text" placeholder="Enter A/C No." value="<?php echo $_SESSION['AC_No'] ?>" name="AC_No" />
          </div>
          <div class="input-box">
            <span class="details">Bank Name</span>
            <input type="text" placeholder="Enter Bank Name" value="<?php echo $_SESSION['Bank_Name'] ?>" name="Bank_Name" />
          </div>
        </div>
        <div class="button">
          <div class="btn-wrapper"
            <?php if (!can('update', MENU_ID)) { ?>
            title="You don't have permission to update student data"
            <?php } ?>>
            <input type="submit" name="update" value="Update" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?> />
          </div>
          <input type="submit" name="update" value="Update" />
          <input type="reset" value="Reset" />
        </div>
      </form>
    </div>
  </div>
  <!-- Scripts -->

  <!-- Set Working Status, DOB, DOJ -->
  <script type="text/javascript">
    function reset_date(date) {
      arr = date.split("-")
      temp = arr[0]
      arr[0] = arr[2]
      arr[2] = temp
      date = arr.join("-")
      return date
    }
    $(document).ready(function() {
      var dob = reset_date('<?php echo $_SESSION['DOB'] ?>');
      var doj = reset_date('<?php echo $_SESSION['DOJ'] ?>');
      dopf = '<?php if (isset($_SESSION['DOPF'])) {
                echo $_SESSION['DOPF'];
              } ?>';
      var dopf = reset_date(dopf);
      $('#dob').val(dob);
      $('#doj').val(doj);
      <?php if (isset($_SESSION['DOPF'])) {
        echo "$('#dopf').val(dopf)";
      } ?>
    });
  </script>

</body>

</html>