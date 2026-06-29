<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 4);

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

function isValidStudentIdFormat($student_id)
{
  return preg_match('/^FGST\d{4}$/', $student_id);
}

function writeStudentIdChangeLog($link, $old_id, $new_id, $reason, $status, $status_reason)
{
  $old_id = mysqli_real_escape_string($link, $old_id);
  $new_id = mysqli_real_escape_string($link, $new_id);
  $reason = mysqli_real_escape_string($link, $reason);
  $status = mysqli_real_escape_string($link, $status);
  $status_reason = mysqli_real_escape_string($link, $status_reason);
  $changed_by_id = mysqli_real_escape_string($link, $_SESSION['Admin_Id_No']);
  $changed_by_name = mysqli_real_escape_string($link, $_SESSION['Admin_Name']);
  $changed_by_user_type = mysqli_real_escape_string($link, 'Admin');

  $log_sql = "INSERT INTO student_id_change_logs
    (Old_Id_No, New_Id_No, Reason, Changed_By_Id, Changed_By_Name, Changed_By_UserType, Status, Status_Reason, Created_At)
    VALUES
    ('$old_id', '$new_id', '$reason', '$changed_by_id', '$changed_by_name', '$changed_by_user_type', '$status', '$status_reason', NOW())";

  if (!mysqli_query($link, $log_sql)) {
    return "Failed inserting student_id_change_logs. SQL Error: " . mysqli_error($link);
  }

  return '';
}

// ===== Change Student ID Transaction =====
$change_student_id_old = '';
$change_student_id_new = '';
$change_student_id_reason = '';
$change_student_id_errors = [];
$change_student_id_success = '';
$change_student_modal_open = false;
$change_student_verified_data = [];
$show_change_student_form = false;
$tables = [
  ['table' => 'student_master_data', 'label' => 'student_master_data'],
  ['table' => 'student', 'label' => 'student'],
  ['table' => 'stu_fee_master_data', 'label' => 'stu_fee_master_data'],
  ['table' => 'stu_paid_fee', 'label' => 'stu_paid_fee'],
  ['table' => 'fee_balances', 'label' => 'fee_balances'],
  ['table' => 'commit_date', 'label' => 'commit_date'],
  ['table' => 'vvip', 'label' => 'vvip'],
  ['table' => 'attendance_daily', 'label' => 'attendance_daily'],
  ['table' => 'van_attendance_daily', 'label' => 'van_attendance_daily'],
  ['table' => 'stu_att_master', 'label' => 'stu_att_master'],
  ['table' => 'stu_marks', 'label' => 'stu_marks'],
  ['table' => 'student_homework', 'label' => 'student_homework'],
  ['table' => 'student_performance', 'label' => 'student_performance'],
  ['table' => 'stu_performance_master', 'label' => 'stu_performance_master'],
  ['table' => 'central.applications', 'label' => 'central.applications'],
];

// ===== Change Student ID Feature =====
if (isset($_POST['verify_change_student_id'])) {
  if (!can('custom1', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update student data');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }

  $show_change_student_form = true;
  $change_student_id_old = strtoupper(validate($_POST['old_student_id']));
  $change_student_id_new = strtoupper(validate($_POST['new_student_id']));
  $change_student_id_reason = validate($_POST['change_student_id_reason']);

  if ($change_student_id_old == '') {
    $change_student_id_errors[] = 'Old Student ID No is required.';
  }

  if ($change_student_id_new == '') {
    $change_student_id_errors[] = 'New Student ID No is required.';
  }

  if ($change_student_id_reason == '') {
    $change_student_id_errors[] = 'Reason is required.';
  }

  if ($change_student_id_old != '' && !isValidStudentIdFormat($change_student_id_old)) {
    $change_student_id_errors[] = 'Invalid Old Student ID format.';
  }

  if ($change_student_id_new != '' && !isValidStudentIdFormat($change_student_id_new)) {
    $change_student_id_errors[] = 'Invalid New Student ID format.';
  }

  if (
    $change_student_id_old != '' &&
    $change_student_id_new != '' &&
    $change_student_id_old == $change_student_id_new
  ) {
    $change_student_id_errors[] = 'Old Student ID No and New Student ID No must be different.';
  }

  if (count($change_student_id_errors) == 0) {
    $old_student_sql = "SELECT * FROM student_master_data WHERE Id_No = '$change_student_id_old'";
    $old_student_result = mysqli_query($link, $old_student_sql);

    if (mysqli_num_rows($old_student_result) != 1) {
      $change_student_id_errors[] = 'Old Student ID No does not exist.';
    } else {
      $new_student_sql = "SELECT Id_No FROM student_master_data WHERE Id_No = '$change_student_id_new'";
      $new_student_result = mysqli_query($link, $new_student_sql);

      if (mysqli_num_rows($new_student_result) > 0) {
        $change_student_id_errors[] = 'New Student ID No already exists.';
      } else {
        $student_row = mysqli_fetch_assoc($old_student_result);
        $change_student_verified_data = [
          'student_name' => trim($student_row['First_Name']),
          'sur_name' => trim($student_row['Sur_Name']),
          'father_name' => $student_row['Father_Name'],
          'class' => $student_row['Stu_Class'],
          'section' => $student_row['Stu_Section'],
          'mobile_number' => $student_row['Mobile'],
          'branch' => !empty($_SESSION['school_db']['display_name']) ? $_SESSION['school_db']['display_name'] : '',
          'old_student_id' => $change_student_id_old,
          'new_student_id' => $change_student_id_new,
          'reason' => $change_student_id_reason
        ];
        $change_student_modal_open = true;
      }
    }
  }
}

// ===== Change Student ID Feature =====
if (isset($_POST['confirm_change_student_id'])) {
  if (!can('custom1', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update student data');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }

  $show_change_student_form = true;
  $change_student_id_old = strtoupper(validate($_POST['old_student_id']));
  $change_student_id_new = strtoupper(validate($_POST['new_student_id']));
  $change_student_id_reason = validate($_POST['change_student_id_reason']);

  // ===== Change Student ID Transaction =====
  $change_student_id_failure_reason = '';

  // Validation
  if ($change_student_id_old == '') {
    $change_student_id_failure_reason = 'Old Student ID No is required.';
  } elseif ($change_student_id_new == '') {
    $change_student_id_failure_reason = 'New Student ID No is required.';
  } elseif ($change_student_id_reason == '') {
    $change_student_id_failure_reason = 'Reason is required.';
  } elseif (!isValidStudentIdFormat($change_student_id_old)) {
    $change_student_id_failure_reason = 'Invalid Old Student ID format.';
  } elseif (!isValidStudentIdFormat($change_student_id_new)) {
    $change_student_id_failure_reason = 'Invalid New Student ID format.';
  } elseif ($change_student_id_old == $change_student_id_new) {
    $change_student_id_failure_reason = 'Old Student ID No and New Student ID No must be different.';
  }

  if ($change_student_id_failure_reason == '') {
    $old_student_check = mysqli_query($link, "SELECT * FROM student_master_data WHERE Id_No = '$change_student_id_old' LIMIT 1");

    if (!$old_student_check) {
      $change_student_id_failure_reason = 'SQL Exception: ' . mysqli_error($link);
    } elseif (mysqli_num_rows($old_student_check) != 1) {
      $change_student_id_failure_reason = 'Old Student ID not found.';
    } else {
      $new_student_check = mysqli_query($link, "SELECT Id_No FROM student_master_data WHERE Id_No = '$change_student_id_new' LIMIT 1");

      if (!$new_student_check) {
        $change_student_id_failure_reason = 'SQL Exception: ' . mysqli_error($link);
      } elseif (mysqli_num_rows($new_student_check) > 0) {
        $change_student_id_failure_reason = 'New Student ID already exists.';
      }
    }
  }

  if ($change_student_id_failure_reason == '') {
    try {
      // Begin Transaction
      mysqli_begin_transaction($link);

      $siblings_query = mysqli_query(
        $link,
        "SELECT Id_No, Siblings
        FROM student_master_data
        WHERE Siblings LIKE '%$change_student_id_old%'"
      );

      if (!$siblings_query) {
        throw new Exception("Failed reading student_master_data sibling references. SQL Error: " . mysqli_error($link));
      }

      while ($siblings_row = mysqli_fetch_assoc($siblings_query)) {
        $siblings_list = preg_split('/\s*,\s*/', trim($siblings_row['Siblings']));
        $updated = false;

        foreach ($siblings_list as $index => $sibling_id) {
          if ($sibling_id === $change_student_id_old) {
            $siblings_list[$index] = $change_student_id_new;
            $updated = true;
          }
        }

        if ($updated) {
          $new_siblings_value = mysqli_real_escape_string($link, implode(',', $siblings_list));
          $siblings_owner_id = mysqli_real_escape_string($link, $siblings_row['Id_No']);

          if (!mysqli_query($link, "UPDATE student_master_data SET Siblings = '$new_siblings_value' WHERE Id_No = '$siblings_owner_id'")) {
            throw new Exception("Failed updating student_master_data sibling references. SQL Error: " . mysqli_error($link));
          }
        }
      }

      // Update Student ID Across Tables
      foreach ($tables as $table_details) {
        $table_name = $table_details['table'];

        if (strpos($table_name, '.') !== false) {
          $table_parts = explode('.', $table_name, 2);
          $update_sql = "UPDATE `" . $table_parts[0] . "`.`" . $table_parts[1] . "` SET Id_No = '$change_student_id_new' WHERE Id_No = '$change_student_id_old'";
        } else {
          $update_sql = "UPDATE `$table_name` SET Id_No = '$change_student_id_new' WHERE Id_No = '$change_student_id_old'";
        }

        if (!mysqli_query($link, $update_sql)) {
          throw new Exception("Failed updating " . $table_details['label'] . ". SQL Error: " . mysqli_error($link));
        }
      }

      $success_reason = "Student ID changed successfully from $change_student_id_old to $change_student_id_new.";

      // Insert Log
      $log_error = writeStudentIdChangeLog(
        $link,
        $change_student_id_old,
        $change_student_id_new,
        $change_student_id_reason,
        'Success',
        $success_reason
      );

      if ($log_error != '') {
        throw new Exception($log_error);
      }

      // Commit
      mysqli_commit($link);

      $change_student_id_success = "Student ID changed successfully.<br><br>Old ID: $change_student_id_old<br>New ID: $change_student_id_new";
    } catch (Exception $e) {
      // Rollback
      mysqli_rollback($link);
      $change_student_id_failure_reason = $e->getMessage();
    }
  }

  if ($change_student_id_failure_reason != '') {
    // Insert Log
    $log_error = writeStudentIdChangeLog(
      $link,
      $change_student_id_old,
      $change_student_id_new,
      $change_student_id_reason,
      'Failure',
      $change_student_id_failure_reason
    );

    if ($log_error != '') {
      $change_student_id_failure_reason .= '<br>' . $log_error;
    }

    $change_student_id_errors[] = $change_student_id_failure_reason;
  }
}

//For Show
if (isset($_POST['show'])) {
  if ($_POST['show_id']) {
    if (!can('view', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to view student data');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    $id = validate($_POST['show_id']);

    $sql = "SELECT * FROM student_master_data WHERE Id_No = '$id'";
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) == 1) {
      $row = mysqli_fetch_assoc($result);
      $stu_id = $row['Id_No'];
      $stu_adm = $row['Adm_No'];
      $firstname = $row['First_Name'];
      $surname = $row['Sur_Name'];
      $fathername = $row['Father_Name'];
      $mothername = $row['Mother_Name'];
      $dob = $row['DOB'];
      $gender = $row['Gender'];
      $mobile = $row['Mobile'];
      $aadhar = $row['Aadhar'];
      $mother_aadhar = $row['Mother_Aadhar'];
      $father_aadhar = $row['Father_Aadhar'];
      $class = $row['Stu_Class'];
      $section = $row['Stu_Section'];
      $religion = $row['Religion'];
      $caste = $row['Caste'];
      $category = $row['Category'];
      $houseno = $row['House_No'];
      $area = $row['Area'];
      $village = $row['Village'];
      $doj = $row['DOJ'];
      $previous = $row['Previous_School'];
      $van = $row['Van_Route'];
      $refer = $row['Referred_By'];
      $siblings = $row['Siblings'];
      $_SESSION['Stu_Id_No'] = $stu_id;
      $_SESSION['Stu_Adm_No'] = $stu_adm;
      $_SESSION['First_Name'] = $firstname;
      $_SESSION['Sur_Name'] = $surname;
      $_SESSION['Father_Name'] = $fathername;
      $_SESSION['Mother_Name'] = $mothername;
      $_SESSION['DOB'] = $dob;
      $_SESSION['Gender'] = $gender;
      $_SESSION['Mobile'] = $mobile;
      $_SESSION['Aadhar'] = $aadhar;
      $_SESSION['Mother_Aadhar'] = $mother_aadhar;
      $_SESSION['Father_Aadhar'] = $father_aadhar;
      $_SESSION['Stu_Class'] = $class;
      $_SESSION['Stu_Section'] = $section;
      $_SESSION['Religion'] = $religion;
      $_SESSION['Caste'] = $caste;
      $_SESSION['Category'] = $category;
      $_SESSION['House_No'] = $houseno;
      $_SESSION['Area'] = $area;
      $_SESSION['Village'] = $village;
      $_SESSION['DOJ'] = $doj;
      $_SESSION['Previous_School'] = $previous;
      $_SESSION['Van_Route'] = $van;
      $_SESSION['Referred_By'] = $refer;
      $_SESSION['Siblings'] = $siblings;
      echo "<script>window.open('show_student_details.php','_blank')</script>";
      //header('Location: show_student_details.php');
    } else {
      echo "<script>alert('Incorrect ID');</script>";
    }
  } else {
    echo "<script>alert('Please Enter Id');</script>";
  }
}

// For Update
if (isset($_POST['update'])) {
  if ($_POST['show_id']) {
    if (!can('update', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to update student data');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    $id = validate($_POST['show_id']);

    $sql = "SELECT * FROM student_master_data WHERE Id_No = '$id'";
    $result = mysqli_query($link, $sql);
    if (mysqli_num_rows($result) == 1) {
      $row = mysqli_fetch_assoc($result);
      $stu_id = $row['Id_No'];
      $stu_adm = $row['Adm_No'];
      $firstname = $row['First_Name'];
      $surname = $row['Sur_Name'];
      $fathername = $row['Father_Name'];
      $mothername = $row['Mother_Name'];
      $dob = $row['DOB'];
      $gender = $row['Gender'];
      $mobile = $row['Mobile'];
      $aadhar = $row['Aadhar'];
      $mother_aadhar = $row['Mother_Aadhar'];
      $father_aadhar = $row['Father_Aadhar'];
      $class = $row['Stu_Class'];
      $section = $row['Stu_Section'];
      $religion = $row['Religion'];
      $caste = $row['Caste'];
      $category = $row['Category'];
      $houseno = $row['House_No'];
      $area = $row['Area'];
      $village = $row['Village'];
      $doj = $row['DOJ'];
      $previous = $row['Previous_School'];
      $van = $row['Van_Route'];
      $refer = $row['Referred_By'];
      $siblings = $row['Siblings'];
      $siblings_status = ($siblings != "" || $siblings != NULL) ? 'Yes' : 'No';
      $_SESSION['Stu_Id_No'] = $stu_id;
      $_SESSION['Stu_Adm_No'] = $stu_adm;
      $_SESSION['First_Name'] = $firstname;
      $_SESSION['Sur_Name'] = $surname;
      $_SESSION['Father_Name'] = $fathername;
      $_SESSION['Mother_Name'] = $mothername;
      $_SESSION['DOB'] = $dob;
      $_SESSION['Gender'] = $gender;
      $_SESSION['Mobile'] = $mobile;
      $_SESSION['Aadhar'] = $aadhar;
      $_SESSION['Mother_Aadhar'] = $mother_aadhar;
      $_SESSION['Father_Aadhar'] = $father_aadhar;
      $_SESSION['Stu_Class'] = $class;
      $_SESSION['Stu_Section'] = $section;
      $_SESSION['Religion'] = $religion;
      $_SESSION['Caste'] = $caste;
      $_SESSION['Category'] = $category;
      $_SESSION['House_No'] = $houseno;
      $_SESSION['Area'] = $area;
      $_SESSION['Village'] = $village;
      $_SESSION['DOJ'] = $doj;
      $_SESSION['Previous_School'] = $previous;
      $_SESSION['Van_Route'] = $van;
      $_SESSION['Referred_By'] = $refer;
      $_SESSION['Siblings'] = $siblings;
      $_SESSION['Sibling_Status'] = $siblings_status;
      echo "<script>window.open('update_student.php','_blank')</script>";
      //header('Location: update_student.php');
    } else {
      echo "<script>alert('Incorrect ID');</script>";
    }
  } else {
    echo "<script>alert('Please Enter Id');</script>";
  }
}

//For Delete
if (isset($_POST['delete'])) {
  if (!can('delete', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to delete student data');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  if ($_POST['show_id']) {
    $id = validate($_POST['show_id']);
    try {
      mysqli_begin_transaction($link);

      $sql_search = "SELECT * FROM `student_master_data` WHERE Id_No = '$id'";
      $result = mysqli_query($link, $sql_search);

      if (!$result) {
        throw new Exception('Deletion Failed (SQL Error)');
      }

      if (mysqli_num_rows($result) == 0) {
        throw new Exception('Student ID is Not Available in Student Database');
      }

      foreach ($tables as $table_details) {
        $table_name = $table_details['table'];

        if ($table_name == 'student_master_data' || $table_name == 'central.applications') {
          continue;
        }

        if (strpos($table_name, '.') !== false) {
          $table_parts = explode('.', $table_name, 2);
          $delete_sql = "DELETE FROM `" . $table_parts[0] . "`.`" . $table_parts[1] . "` WHERE Id_No = '$id'";
        } else {
          $delete_sql = "DELETE FROM `$table_name` WHERE Id_No = '$id'";
        }

        if (!mysqli_query($link, $delete_sql)) {
          throw new Exception('Deletion Failed (SQL Error)');
        }
      }

      $application_sql = "UPDATE `central`.`applications`
        SET
          Id_No = NULL,
          Converted_By = NULL,
          Converted_At = NULL,
          Status = 'Active'
        WHERE Id_No = '$id'";

      if (!mysqli_query($link, $application_sql)) {
        throw new Exception('Deletion Failed (SQL Error)');
      }

      $sql = "DELETE FROM `student_master_data` WHERE Id_No = '$id'";

      if (!mysqli_query($link, $sql)) {
        throw new Exception('Deletion Failed (SQL Error)');
      }

      mysqli_commit($link);

      writeStudentIdChangeLog(
        $link,
        $id,
        'Deleted',
        'Student Deleted',
        'Success',
        'Student Hard Deleted Successfully'
      );

      echo "<script>alert('Student Details Deleted Successfully!');</script>";
    } catch (Exception $e) {
      mysqli_rollback($link);

      writeStudentIdChangeLog(
        $link,
        $id,
        'Deleted',
        'Student Deleted',
        'Failure',
        $e->getMessage()
      );

      echo "<script>alert('" . $e->getMessage() . "');</script>";
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
  <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
  <!-- Boxiocns CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <!-- Bootstrap Links -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>

<style>
  .student-controller-wrap {
    max-width: 520px;
  }

  .erp-card {
    background-color: #D6DEE7;
    border: 1px solid #d6dde5;
    border-radius: 0.7rem;
    margin-left: 18% !important;
  }

  .erp-card .card-header {
    background-color: #C5D0DB;
    border-bottom: 1px solid #d6dde5;
    padding: 1rem 1.25rem;
  }

  .erp-card .card-title {
    margin-bottom: 0;
    color: #212529;
    font-size: 1.05rem;
    font-weight: 700;
  }

  .erp-card .card-body {
    padding: 1.25rem;
  }

  .section-label {
    color: #495057;
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 0.45rem;
  }

  .erp-form-control,
  .erp-textarea,
  .erp-btn {
    min-height: 44px;
  }

  .erp-textarea {
    min-height: 110px;
  }

  .erp-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.45rem;
    border-radius: 0.5rem;
    padding: 0.55rem 1rem;
    font-weight: 500;
  }

  .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
  }

  .btn-wrapper {
    display: inline-flex;
  }

  .change-id-value-box {
    border: 1px solid rgba(13, 110, 253, 0.18);
    border-radius: 0.6rem;
    background-color: #f8f9fa;
    padding: 0.75rem;
  }

  .change-id-meta-label {
    color: #6c757d;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-bottom: 0.25rem;
  }

  .change-id-meta-value {
    color: #212529;
    font-size: 0.95rem;
    font-weight: 500;
    word-break: break-word;
  }

  .change-student-id-summary .row {
    row-gap: 1rem;
  }

  .change-student-id-summary .badge {
    font-size: 0.9rem;
    padding: 0.55rem 0.8rem;
  }

  .modal-content {
    background-color: #ffffff;
    border: 0;
    border-radius: 0.9rem;
  }

  .modal-header,
  .modal-footer {
    padding: 1rem 1.25rem;
  }

  .modal-body {
    padding: 1.25rem;
  }

  /* Tooltip wrapper MUST be a real box */
  .btn-wrapper {
    display: inline-block;
  }

  /* Cursor only when disabled */
  .btn-wrapper button:disabled {
    cursor: not-allowed;
  }

  #change-student-id-container {
    display: none;
  }

  .change-student-id-summary p {
    margin-bottom: 0.5rem;
  }


  @media screen and (max-width:576px) {
    .student-controller-wrap {
      max-width: 100%;
      padding-left: 0.75rem;
      padding-right: 0.75rem;
    }

    .erp-card .card-body,
    .erp-card .card-header {
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .action-buttons .btn-wrapper,
    .action-buttons .btn,
    .d-flex.gap-2.flex-wrap .btn {
      width: 100%;
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
  <div class="container student-controller-wrap py-4">
    <!-- ===== Change Student ID Feature ===== -->
    <div id="student-details-container">
      <div class="card erp-card shadow-sm mt-3 mb-4">
        <div class="card-header">
          <h4 class="card-title">Student Details</h4>
        </div>
        <div class="card-body">
          <form action="" method="post" id="form">
            <div class="mb-3">
              <label for="id" class="section-label">Student ID No</label>
              <input type="text" class="form-control erp-form-control" id="id" name="show_id" value="<?php if (isset($id)) {
                                                                                                        echo $id;
                                                                                                      } else {
                                                                                                        echo "";
                                                                                                      } ?>" placeholder="Student Id No." oninput="this.value = this.value.toUpperCase()" required>
            </div>
            <div class="action-buttons">
              <div class="btn-wrapper"
                <?php if (!can('view', MENU_ID)) { ?>
                title="You don't have permission to view student data"
                <?php } ?>>
                <button class="btn btn-primary erp-btn" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>><i class="bi bi-eye"></i>Show</button>
              </div>
              <div class="btn-wrapper"
                <?php if (!can('update', MENU_ID)) { ?>
                title="You don't have permission to update student data"
                <?php } ?>>
                <button class="btn btn-warning erp-btn" type="submit" name="update" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>><i class="bi bi-pencil"></i>Modify</button>
              </div>
              <div class="btn-wrapper"
                <?php if (!can('delete', MENU_ID)) { ?>
                title="You don't have permission to delete student data"
                <?php } ?>>
                <button class="btn btn-danger erp-btn" type="submit" name="delete" onclick="if(!confirm('Confirm to Delete Student Data?')){return false;}else{return true;}" <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>><i class="bi bi-trash"></i>Delete</button>
              </div>
              <!-- ===== Change Student ID Feature ===== -->
              <div class="btn-wrapper"
                <?php if (!can('custom1', MENU_ID)) { ?>
                title="You don't have permission to change student ID"
                <?php } ?>>
                <button class="btn btn-info text-light erp-btn" type="button" onclick="openChangeStudentIdForm()" <?php echo !can('custom1', MENU_ID) ? 'disabled' : ''; ?>><i class="bi bi-arrow-left-right"></i>Change Student ID No</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- ===== Change Student ID Feature ===== -->
    <div id="change-student-id-container">
      <div class="card erp-card shadow-sm mt-3 mb-4">
        <div class="card-header">
          <h4 class="card-title">Change Student ID No</h4>
        </div>
        <div class="card-body">
          <?php if (!empty($change_student_id_errors)) { ?>
            <div class="alert alert-danger mb-4" role="alert">
              <?php echo implode('<br>', $change_student_id_errors); ?>
            </div>
          <?php } ?>

          <?php if ($change_student_id_success != '') { ?>
            <div class="alert alert-success mb-4" role="alert">
              <?php echo $change_student_id_success; ?>
            </div>
          <?php } ?>

          <form action="" method="post" id="change-student-id-form">
            <div class="mb-3">
              <label for="old_student_id" class="form-label section-label">Old Student ID No</label>
              <input type="text" class="form-control erp-form-control" id="old_student_id" name="old_student_id" value="<?php echo htmlspecialchars($change_student_id_old); ?>" oninput="this.value = this.value.toUpperCase()" required>
            </div>
            <div class="mb-3">
              <label for="new_student_id" class="form-label section-label">New Student ID No</label>
              <input type="text" class="form-control erp-form-control" id="new_student_id" name="new_student_id" value="<?php echo htmlspecialchars($change_student_id_new); ?>" oninput="this.value = this.value.toUpperCase()" required>
            </div>
            <div class="mb-3">
              <label for="change_student_id_reason" class="form-label section-label">Reason</label>
              <textarea class="form-control erp-textarea" id="change_student_id_reason" name="change_student_id_reason" rows="3" required><?php echo htmlspecialchars($change_student_id_reason); ?></textarea>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <button class="btn btn-primary erp-btn" type="submit" name="verify_change_student_id"><i class="bi bi-person-check"></i>Verify Student</button>
              <button class="btn btn-secondary erp-btn" type="button" onclick="closeChangeStudentIdForm()"><i class="bi bi-x-circle"></i>Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== Change Student ID Feature ===== -->
  <div class="modal fade" id="changeStudentIdModal" tabindex="-1" aria-labelledby="changeStudentIdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="changeStudentIdModalLabel">Verify Student ID Change</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="change-student-id-summary">
            <div class="row">
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Student Name</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['student_name'] ?? ''); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Sur Name</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['sur_name'] ?? ''); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Father Name</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['father_name'] ?? ''); ?></div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Class</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['class'] ?? ''); ?></div>
                </div>
              </div>
              <div class="col-md-3 col-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Section</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['section'] ?? ''); ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Mobile Number</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['mobile_number'] ?? ''); ?></div>
                </div>
              </div>
              <?php if (!empty($change_student_verified_data['branch'])) { ?>
                <div class="col-md-6">
                  <div class="change-id-value-box h-100">
                    <div class="change-id-meta-label">Branch</div>
                    <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['branch']); ?></div>
                  </div>
                </div>
              <?php } ?>
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">Old Student ID</div>
                  <span class="badge bg-secondary"><?php echo htmlspecialchars($change_student_verified_data['old_student_id'] ?? ''); ?></span>
                </div>
              </div>
              <div class="col-md-6">
                <div class="change-id-value-box h-100">
                  <div class="change-id-meta-label">New Student ID</div>
                  <span class="badge bg-primary"><?php echo htmlspecialchars($change_student_verified_data['new_student_id'] ?? ''); ?></span>
                </div>
              </div>
              <div class="col-12">
                <div class="change-id-value-box">
                  <div class="change-id-meta-label">Reason</div>
                  <div class="change-id-meta-value"><?php echo htmlspecialchars($change_student_verified_data['reason'] ?? ''); ?></div>
                </div>
              </div>
            </div>
          </div>
          <div class="alert alert-warning mt-4 mb-0 d-flex align-items-start gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
              This operation will update Student ID across all related records. Please verify the student before confirming.
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary erp-btn" data-bs-dismiss="modal"><i class="bi bi-x-circle"></i>Cancel</button>
          <form action="" method="post" class="m-0">
            <input type="hidden" name="old_student_id" value="<?php echo htmlspecialchars($change_student_id_old); ?>">
            <input type="hidden" name="new_student_id" value="<?php echo htmlspecialchars($change_student_id_new); ?>">
            <input type="hidden" name="change_student_id_reason" value="<?php echo htmlspecialchars($change_student_id_reason); ?>">
            <button type="submit" class="btn btn-success erp-btn" name="confirm_change_student_id"><i class="bi bi-check-circle"></i>Confirm Change</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== Change Student ID Feature ===== -->
  <script>
    function openChangeStudentIdForm() {
      document.getElementById('student-details-container').style.display = 'none';
      document.getElementById('change-student-id-container').style.display = 'block';
    }

    function closeChangeStudentIdForm() {
      document.getElementById('student-details-container').style.display = 'block';
      document.getElementById('change-student-id-container').style.display = 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
      var shouldShowChangeStudentForm = <?php echo $show_change_student_form ? 'true' : 'false'; ?>;
      var shouldOpenChangeStudentModal = <?php echo $change_student_modal_open ? 'true' : 'false'; ?>;

      if (shouldShowChangeStudentForm) {
        openChangeStudentIdForm();
      }

      if (shouldOpenChangeStudentModal) {
        var changeStudentIdModalElement = document.getElementById('changeStudentIdModal');
        var changeStudentIdModal = new bootstrap.Modal(changeStudentIdModalElement);
        changeStudentIdModal.show();
      }
    });
  </script>

</body>

</html>