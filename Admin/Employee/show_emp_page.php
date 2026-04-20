<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 51);

requireLogin();
requireMenuAccess(MENU_ID);

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
if (isset($_POST['show'])) {
  if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view employee data');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['show_id']);

  $sql = "SELECT * FROM employee_master_data WHERE Emp_Id = '$id'";
  $result = mysqli_query($link, $sql);
  if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $emp_id = $row['Emp_Id'];
    $firstname = $row['Emp_First_Name'];
    $surname = $row['Emp_Sur_Name'];
    $fathername = $row['Father_Name'];
    $qualification = $row['Qualification'];
    $dob = $row['DOB'];
    $relation = $row['Relation'];
    $mobile = $row['Mobile'];
    $aadhar = $row['Aadhar'];
    $houseno = $row['House_No'];
    $area = $row['Area'];
    $village = $row['Village'];
    $doj = $row['DOJ'];
    $designation = $row['Designation'];
    $salary = $row['Salary'];
    $pf = $row['PF'];
    $dopf = $row['DOPF'];
    $status = $row['Status'];
    $ac = $row['AC_No'];
    $bank = $row['Bank_Name'];
    $_SESSION['Emp_Id_No'] = $emp_id;
    $_SESSION['First_Name'] = $firstname;
    $_SESSION['Sur_Name'] = $surname;
    $_SESSION['Father_Name'] = $fathername;
    $_SESSION['Qualification'] = $qualification;
    $_SESSION['DOB'] = $dob;
    $_SESSION['Relation'] = $relation;
    $_SESSION['Mobile'] = $mobile;
    $_SESSION['Aadhar'] = $aadhar;
    $_SESSION['House_No'] = $houseno;
    $_SESSION['Area'] = $area;
    $_SESSION['Village'] = $village;
    $_SESSION['DOJ'] = $doj;
    $_SESSION['Designation'] = $designation;
    $_SESSION['Salary'] = $salary;
    $_SESSION['PF'] = $pf;
    $_SESSION['DOPF'] = $dopf;
    $_SESSION['Status'] = $status;
    $_SESSION['AC_No'] = $ac;
    $_SESSION['Bank_Name'] = $bank;
    header('Location: show_emp_details.php');
  } else {
    echo "<script>alert('Incorrect ID');</script>";
  }
}

if (isset($_POST['update'])) {
  if (!can('update', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update employee data');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['show_id']);

  $sql = "SELECT * FROM employee_master_data WHERE Emp_Id = '$id'";
  $result = mysqli_query($link, $sql);
  if (mysqli_num_rows($result) == 1) {
    $row = mysqli_fetch_assoc($result);
    $emp_id = $row['Emp_Id'];
    $firstname = $row['Emp_First_Name'];
    $surname = $row['Emp_Sur_Name'];
    $fathername = $row['Father_Name'];
    $qualification = $row['Qualification'];
    $dob = $row['DOB'];
    $relation = $row['Relation'];
    $gender = $row['Gender'];
    $mobile = $row['Mobile'];
    $aadhar = $row['Aadhar'];
    $houseno = $row['House_No'];
    $area = $row['Area'];
    $village = $row['Village'];
    $doj = $row['DOJ'];
    $designation = $row['Designation'];
    $salary = $row['Salary'];
    $pf = $row['PF'];
    $dopf = $row['DOPF'];
    $status = $row['Status'];
    $ac = $row['AC_No'];
    $bank = $row['Bank_Name'];
    $_SESSION['Emp_Id'] = $emp_id;
    $_SESSION['Emp_First_Name'] = $firstname;
    $_SESSION['Emp_Sur_Name'] = $surname;
    $_SESSION['Father_Name'] = $fathername;
    $_SESSION['Qualification'] = $qualification;
    $_SESSION['DOB'] = $dob;
    $_SESSION['Relation'] = $relation;
    $_SESSION['Mobile'] = $mobile;
    $_SESSION['Aadhar'] = $aadhar;
    $_SESSION['House_No'] = $houseno;
    $_SESSION['Area'] = $area;
    $_SESSION['Village'] = $village;
    $_SESSION['DOJ'] = $doj;
    $_SESSION['Designation'] = $designation;
    $_SESSION['Salary'] = $salary;
    $_SESSION['PF'] = $pf;
    $_SESSION['DOPF'] = $dopf;
    $_SESSION['Status'] = $status;
    $_SESSION['AC_No'] = $ac;
    $_SESSION['Bank_Name'] = $bank;
    header('Location: update_emp.php');
  } else {
    echo "<script>alert('Incorrect ID');</script>";
  }
}

if (isset($_POST['delete'])) {
  if (!can('delete', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to delete employee');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['show_id']);

  echo "<script>if(!confirm('Confirm To Delete Employee Data from Employee Database?')){
    location.replace('');
  }</script>";
  $sql_search = "SELECT * FROM employee_master_data WHERE Emp_Id = '$id'";
  $result = mysqli_query($link, $sql_search);
  if (mysqli_num_rows($result) == 0) {
    echo "<script>alert('Employee ID is Not Available in Employee Database');</script>";
  } else {
    $sql = "DELETE FROM employee_master_data WHERE Emp_Id = '$id'";
    if (mysqli_query($link, $sql)) {
      echo "<script>alert('Employee Details Deleted Successfully!!');</script>";
    } else {
      echo "<script>alert('Employee Details Deletion Failed');</script>";
    }
  }
}
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>

<style>
  @media screen and (max-width:576px) {
    .container {
      width: 70%;
    }

    .card {
      width: 50%;
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
  <div class="container col-lg-4 mt-5">
    <div class="card bg-secondary p-3" style="width: 25rem;">
      <div class="card-title text-light">
        <h4>Show/Modify Employee Details</h4>
      </div>
      <div class="card-body">
        <form action="" method="post">
          <input type="text" class="form-control" name="show_id" placeholder="Employee Id No." value="<?php if (isset($id)) {
                                                                                                        echo $id;
                                                                                                      } else {
                                                                                                        echo "";
                                                                                                      } ?>" oninput="this.value = this.value.toUpperCase()" required>
          <div class="buttons mt-3">
            <div class="btn-wrapper"
              <?php if (!can('view', MENU_ID)) { ?>
              title="You don't have permission to view employee data"
              <?php } ?>>
              <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
            </div>
            <div class="btn-wrapper"
              <?php if (!can('update', MENU_ID)) { ?>
              title="You don't have permission to update employee data"
              <?php } ?>>
              <button class="btn btn-warning" type="submit" name="update" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Modify</button>
            </div>
            <div class="btn-wrapper"
              <?php if (!can('delete', MENU_ID)) { ?>
              title="You don't have permission to delete employee data"
              <?php } ?>>
              <button class="btn btn-danger" type="submit" name="delete" onclick="if(!confirm('Confirm to Delete Employee Data?')){return false;}else{return true;}" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>Delete</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>

</html>