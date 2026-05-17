<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 4);

requireLogin();
requireMenuAccess(MENU_ID);

if (!$_SESSION['Stu_Id_No']) {
  echo "<script>
  alert('Student Id Not Rendered');
  location.replace('show_student_page.php');
  </script>";
}

if (!can('update', MENU_ID)) {
  echo "<script>alert('You don\'t have permission to update student data');
    location.replace('/Futuregen/Admin/Student/show_student_page.php')</script>";
  exit;
}

$central_link = $central ?? connectCentralDB();
$branches = [];
$branch_query = mysqli_query($central_link, "SELECT school_code AS School_Code, display_name AS Display_Name, db_name AS Db_Name FROM school_master WHERE active_flag = 1");

while ($row = mysqli_fetch_assoc($branch_query)) {
  $branches[] = $row;
}

if (isset($_POST['action']) && $_POST['action'] === 'fetch_users') {

  $branch = mysqli_real_escape_string($link, $_POST['branch']);
  $type = mysqli_real_escape_string($link, $_POST['type']);
  $branch_db = '';

  foreach ($branches as $b) {
    if ($b['School_Code'] === $branch) {
      $branch_db = $b['Db_Name'];
      break;
    }
  }

  if ($branch_db === '') {
    echo json_encode([]);
    exit;
  }

  if ($type === 'Admin') {
    $sql = "SELECT Admin_Id_No AS Id_No, Admin_Name AS Name FROM {$branch_db}.admin";
  } else {
    $sql = "SELECT Emp_Id AS Id_No, Emp_First_Name AS Name FROM {$branch_db}.employee_master_data WHERE Status = 'Working'";
  }

  $result = mysqli_query($link, $sql);
  $users = [];

  if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
      $users[] = $row;
    }
  }

  echo json_encode($users);
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

if (isset($_POST["update"])) {
  if (!can('update', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update student data');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['Stu_Id_No']);
  $adm = validate($_POST['Stu_Adm_No']);
  $firstname = validate($_POST['First_Name']);
  $surname = validate($_POST['Sur_Name']);
  $fathername = validate($_POST['Father_Name']);
  $mothername = validate($_POST['Mother_Name']);
  $dob = validate($_POST['DOB']);
  $gender = validate($_POST['Gender']);
  $mobile = validate($_POST['Mobile']);
  $aadhar = validate($_POST['Aadhar']);
  $mother_aadhar = validate($_POST['Mother_Aadhar']);
  $father_aadhar = validate($_POST['Father_Aadhar']);
  $class = validate($_POST['Stu_Class']);
  $section = validate($_POST['Stu_Section']);
  $pass_class = validate($_POST['Pass_Class']);
  $religion = validate($_POST['Religion']);
  $caste = validate($_POST['Caste']);
  $category = validate($_POST['Category']);
  $houseno = validate($_POST['House_No']);
  $area = validate($_POST['Area']);
  $village = validate($_POST['Village']);
  $doj = validate($_POST['DOJ']);
  $previous = validate($_POST['Previous_School']);
  $student_type = validate($_POST['Student_Type'] ?? '');
  $valid_student_types = ['Day Scholar', 'Hosteller', 'Vanner'];
  $van = validate($_POST['Van_Route'] ?? '');
  $refer = mysqli_real_escape_string($link, validate($_POST['Referred_By']));
  $refer_id = isset($_POST['Referred_By_Id']) ? mysqli_real_escape_string($link, validate($_POST['Referred_By_Id'])) : '';
  $referred_by_type = $_POST['Referred_By_Type'] ?? '';
  $referral_update_status = true;
  $student_type_update_status = true;

  if (!in_array($student_type, $valid_student_types, true)) {
    $student_type_update_status = false;
    echo "<script>alert('Please select valid student type!')</script>";
  }

  if ($student_type === 'Vanner') {
    if ($van === '') {
      $student_type_update_status = false;
      echo "<script>alert('Please select Van Route for Vanner student!')</script>";
    }
  } else {
    $van = '';
  }

  if ($referred_by_type === 'Staff') {
    if ($refer == '' || $refer_id == '') {
      $referral_update_status = false;
      echo "<script>alert('Please select staff referral!')</script>";
    }
  } else {
    if ($refer == '') {
      $referral_update_status = false;
      echo "<script>alert('Please enter referred by!')</script>";
    }
    $refer_id = '';
  }

  $refer_id_sql = $refer_id == '' ? "NULL" : "'$refer_id'";
  if ($_POST['Pass_Class'] && $pass_class != ' ' && $pass_class != '') {
    $class = $pass_class;
    $section = '';
  }
  $d = explode('-', $dob);
  $j = explode('-', $doj);
  $dob = $d[2] . "-" . $d[1] . "-" . $d[0];
  /* //Removing 20 from 2023
  if (substr($j[0], 0, strlen("20")) == "20") {
    $j[0] = substr($j[0], strlen("20"));
  }
  //Removing 19 from 1998
  else if (substr($j[0], 0, strlen("19")) == "19") {
    $j[0] = substr($j[0], strlen("19"));
  } */
  $doj = $j[2] . "-" . $j[1] . "-" . $j[0];
  //Siblings Arrangement
  $sibling_status = $_POST['Siblings'];
  $siblings_update_status = true;
  $update_sql = "UPDATE `student_master_data`
        SET Adm_No = '$adm', First_Name = '$firstname', Sur_Name = '$surname', Father_Name = '$fathername', Mother_Name = '$mothername',
         DOB = '$dob', Gender = '$gender', Mobile = '$mobile', Aadhar = '$aadhar', Mother_Aadhar = '$mother_aadhar', Father_Aadhar = '$father_aadhar', Stu_Class = '$class', Stu_Section = '$section',
          Religion = '$religion', Caste = '$caste', Category = '$category', House_No = '$houseno', Area = '$area',
          Village = '$village', DOJ = '$doj', Previous_School = '$previous', Referred_By = '$refer', Referred_By_Id = $refer_id_sql, Student_Type = '$student_type',";
  if ($van == "") {
    $update_sql .= " Van_Route = NULL,";
  } else {
    $update_sql .= " Van_Route = '$van',";
  }
  if ($sibling_status == "Yes") {
    $no_of_siblings = $_POST['No_Of_Siblings'];
    if ($no_of_siblings == 0) {
      $siblings_update_status = false;
      echo "<script>alert('Siblings Should Not Be 0 if Siblings is Yes!')</script>";
    }
    if (isset($_POST['Sib_Id_No']) && $_POST['Sib_Id_No']) {
      $siblings = implode(',', $_POST['Sib_Id_No']);
      foreach ($_POST['Sib_Id_No'] as $sibling) {     //Checking if Sibling Id No is not entered(null)
        if ($sibling == "") {
          echo "<script>alert('Please Enter All Siblings!')</script>";
          $siblings_update_status = false;
          break;
        }
      }
      if ($siblings_update_status) {
        $update_sql .= "Siblings = '$siblings' WHERE Id_No = '$id'";
      }
    }
  } else {
    $update_sql .= "Siblings = NULL WHERE Id_No = '$id'";
  }
  if ($student_type_update_status && $referral_update_status && $siblings_update_status && isset($update_sql)) {

    /* if (mysqli_query($link, $update_sql)) {
      if (str_contains(strtolower($class), "drop")) {
        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type IN ('School Fee','Vehicle Fee')")) != 0) {
          if (mysqli_query($link, "UPDATE `stu_fee_master_data` SET Class = '$class',Section = '' WHERE Id_No = '$id' AND Type IN ('School Fee','Vehicle Fee')")) {
            echo "<script>alert('Student Updated as Drop in Fee Master Data Successfully!')</script>";
          }
        }
      }
      if ($van != "") {
        if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = 'Vehicle Fee'")) == 0) {
          echo "<script>alert('Student Not Found in Fee Master Data!Please Insert Manually for Vehicle fee!')</script>";
        } else {
          $actual = '';
          $actual_status = true;
          if ($van != "Drop") {
            $sql = mysqli_query($link, "SELECT Fee FROM `actual_fee` WHERE Type = 'Vehicle Fee' AND Route = '$van'");
            if (mysqli_num_rows($sql) == 0) {
              echo "<script>alert('Actual Fee Not Available for " . $van . "')</script>";
              $actual_status = false;
            } else {
              while ($row = mysqli_fetch_assoc($sql)) {
                $actual = $row['Fee'];
              }
            }
          }
          if (!$actual_status) {
            echo "<script>alert('Fee Master Data Updation Failed!')</script>";
          } else {
            if (mysqli_query($link, "UPDATE `stu_fee_master_data` SET First_Name = '$firstname', Class = '$class', Section = '$section'
          ,Street = '$area',Actual = '$actual', Route = '$van' WHERE Id_No = '$id' AND Type = 'Vehicle Fee'")) {
              echo "<script>alert('Fee Master Data Updated Successfully')</script>";
            }
          }
        }
      }

      echo
      "
      <script>
      alert('Succesfully Updated');
      </script>
      ";
    } else {
      echo
      "
      <script>
      alert('Updation Failed (SQL error)');
      </script>
      ";
    } */

    /* Student Updation(student_master_data) */
    $studentUpdated = false;

    mysqli_begin_transaction($link);

    try {

      if (!mysqli_query($link, $update_sql)) {
        throw new Exception('Student update failed');
      }

      mysqli_commit($link);
      $studentUpdated = true;
    } catch (Exception $e) {

      mysqli_rollback($link);

      $msg = addslashes($e->getMessage());
      echo "<script>alert('$msg');</script>";
    }


    /* Student Fee Data Updation(stu_fee_master_data) */
    if ($studentUpdated) {

      mysqli_begin_transaction($link);

      try {

        /* =========================================================
           SCHOOL FEE — ALWAYS
        ========================================================= */

        $sfQ = mysqli_query(
          $link,
          "SELECT * FROM stu_fee_master_data
             WHERE Id_No='$id' AND Type='School Fee'
             LIMIT 1"
        );
        if (!$sfQ) {
          throw new Exception('School Fee select failed');
        }

        $sf_exists = mysqli_num_rows($sfQ) > 0;
        $sf_row = $sf_exists ? mysqli_fetch_assoc($sfQ) : null;

        /* ---------- INSERT SCHOOL FEE (IF MISSING) ---------- */
        if (!$sf_exists) {

          $afQ = mysqli_query(
            $link,
            "SELECT Fee FROM actual_fee
                 WHERE Type='School Fee' AND Class='$class'
                 LIMIT 1"
          );
          if (!$afQ) {
            throw new Exception('Actual School Fee lookup failed');
          }

          if (mysqli_num_rows($afQ)) {

            $actual = mysqli_fetch_assoc($afQ)['Fee'];

            $ins = mysqli_query(
              $link,
              "INSERT INTO stu_fee_master_data VALUES (
                        '', '$id', '$firstname', '$class', '$section',
                        '$area', 'School Fee',
                        '$actual', '0', '$actual',
                        '$actual', NULL
                    )"
            );
            if (!$ins) {
              throw new Exception('School Fee insert failed');
            }
          }
        } else {

          /* ---------- SNAPSHOT UPDATE ---------- */
          $upd = mysqli_query(
            $link,
            "UPDATE stu_fee_master_data SET
                    First_Name='$firstname',
                    Class='$class',
                    Section='$section',
                    Street='$area'
                 WHERE Id_No='$id' AND Type='School Fee'"
          );
          if (!$upd) {
            throw new Exception('School Fee snapshot update failed');
          }

          /* ---------- RECALC ONLY IF CLASS CHANGED ---------- */
          if ($sf_row['Class'] !== $class) {

            $afQ = mysqli_query(
              $link,
              "SELECT Fee FROM actual_fee
                     WHERE Type='School Fee' AND Class='$class'
                     LIMIT 1"
            );
            if (!$afQ) {
              throw new Exception('Actual School Fee lookup failed');
            }

            if (mysqli_num_rows($afQ)) {

              $new_actual  = mysqli_fetch_assoc($afQ)['Fee'];
              $old_actual  = $sf_row['Actual'];
              $old_current = $sf_row['Current_Balance'];
              $last        = $sf_row['Last_Balance'];

              $concession  = max(0, $old_actual - $old_current);
              $new_current = max(0, $new_actual - $concession);
              $total       = $last + $new_current;

              $upd = mysqli_query(
                $link,
                "UPDATE stu_fee_master_data SET
                            Actual='$new_actual',
                            Current_Balance='$new_current',
                            Total='$total'
                         WHERE Id_No='$id' AND Type='School Fee'"
              );
              if (!$upd) {
                throw new Exception('School Fee recalculation failed');
              }
            }
          }
        }

        /* =========================================================
           VEHICLE FEE — CONDITIONAL
        ========================================================= */

        if ($van === "") {

          $upd = mysqli_query(
            $link,
            "UPDATE stu_fee_master_data
                 SET First_Name='$firstname',
                     Class='$class',
                     Section='$section',
                     Street='$area',
                     Current_Balance='0',
                     Total=Last_Balance,
                     Route=NULL
                 WHERE Id_No='$id' AND Type='Vehicle Fee'"
          );
          if (!$upd) {
            throw new Exception('Vehicle Fee inactive update failed');
          }
        } elseif ($van === "Drop") {

          $upd = mysqli_query(
            $link,
            "UPDATE stu_fee_master_data
                 SET Route='Drop'
                 WHERE Id_No='$id' AND Type='Vehicle Fee'"
          );
          if (!$upd) {
            throw new Exception('Vehicle Fee Drop update failed');
          }
        } else {

          $vfQ = mysqli_query(
            $link,
            "SELECT * FROM stu_fee_master_data
                 WHERE Id_No='$id' AND Type='Vehicle Fee'
                 LIMIT 1"
          );
          if (!$vfQ) {
            throw new Exception('Vehicle Fee select failed');
          }

          $vf_exists = mysqli_num_rows($vfQ) > 0;
          $vf_row = $vf_exists ? mysqli_fetch_assoc($vfQ) : null;

          /* ---------- INSERT VEHICLE FEE (IF MISSING) ---------- */
          if (!$vf_exists) {

            $afQ = mysqli_query(
              $link,
              "SELECT Fee FROM actual_fee
                     WHERE Type='Vehicle Fee' AND Route='$van'
                     LIMIT 1"
            );
            if (!$afQ) {
              throw new Exception('Actual Vehicle Fee lookup failed');
            }

            if (mysqli_num_rows($afQ)) {

              $actual = mysqli_fetch_assoc($afQ)['Fee'];

              $ins = mysqli_query(
                $link,
                "INSERT INTO stu_fee_master_data VALUES (
                            '', '$id', '$firstname', '$class', '$section',
                            '$area', 'Vehicle Fee',
                            '$actual', '0', '$actual',
                            '$actual', '$van'
                        )"
              );
              if (!$ins) {
                throw new Exception('Vehicle Fee insert failed');
              }
            }
          } else {

            /* ---------- SNAPSHOT UPDATE ---------- */
            $upd = mysqli_query(
              $link,
              "UPDATE stu_fee_master_data SET
                        First_Name='$firstname',
                        Class='$class',
                        Section='$section',
                        Street='$area',
                        Route='$van'
                     WHERE Id_No='$id' AND Type='Vehicle Fee'"
            );
            if (!$upd) {
              throw new Exception('Vehicle Fee snapshot update failed');
            }

            /* ---------- RECALC ONLY IF ROUTE CHANGED ---------- */
            if ($vf_row['Route'] !== $van) {

              $afQ = mysqli_query(
                $link,
                "SELECT Fee FROM actual_fee
                         WHERE Type='Vehicle Fee' AND Route='$van'
                         LIMIT 1"
              );
              if (!$afQ) {
                throw new Exception('Actual Vehicle Fee lookup failed');
              }

              if (mysqli_num_rows($afQ)) {

                $new_actual  = mysqli_fetch_assoc($afQ)['Fee'];
                $old_actual  = $vf_row['Actual'];
                $old_current = $vf_row['Current_Balance'];
                $last        = $vf_row['Last_Balance'];

                $concession  = max(0, $old_actual - $old_current);
                $new_current = max(0, $new_actual - $concession);
                $total       = $last + $new_current;

                $upd = mysqli_query(
                  $link,
                  "UPDATE stu_fee_master_data SET
                                Actual='$new_actual',
                                Current_Balance='$new_current',
                                Total='$total'
                             WHERE Id_No='$id' AND Type='Vehicle Fee'"
                );
                if (!$upd) {
                  throw new Exception('Vehicle Fee recalculation failed');
                }
              }
            }
          }
        }

        /* =========================================================
           HOSTEL FEE - CONDITIONAL
        ========================================================= */

        $hfQ = mysqli_query(
          $link,
          "SELECT * FROM stu_fee_master_data
             WHERE Id_No='$id' AND Type='Hostel Fee'
             LIMIT 1"
        );
        if (!$hfQ) {
          throw new Exception('Hostel Fee select failed');
        }

        $hf_exists = mysqli_num_rows($hfQ) > 0;
        $hf_row = $hf_exists ? mysqli_fetch_assoc($hfQ) : null;

        if ($student_type === 'Hosteller') {
          $afQ = mysqli_query(
            $link,
            "SELECT Fee FROM actual_fee
                 WHERE Type='Hostel Fee' AND Class='$class'
                 LIMIT 1"
          );
          if (!$afQ) {
            throw new Exception('Actual Hostel Fee lookup failed');
          }

          if (mysqli_num_rows($afQ) == 0) {
            throw new Exception('Hostel fee not configured');
          }

          $hostel_actual = mysqli_fetch_assoc($afQ)['Fee'];

          if (!$hf_exists) {
            $ins = mysqli_query(
              $link,
              "INSERT INTO stu_fee_master_data VALUES (
                        '', '$id', '$firstname', '$class', '$section',
                        '$area', 'Hostel Fee',
                        '$hostel_actual', '0', '$hostel_actual',
                        '$hostel_actual', NULL
                    )"
            );
            if (!$ins) {
              throw new Exception('Hostel Fee insert failed');
            }
          } else {
            $old_actual  = $hf_row['Actual'];
            $old_current = $hf_row['Current_Balance'];
            $last        = $hf_row['Last_Balance'];

            $concession  = max(0, $old_actual - $old_current);
            $new_current = max(0, $hostel_actual - $concession);
            $total       = $last + $new_current;

            $upd = mysqli_query(
              $link,
              "UPDATE stu_fee_master_data SET
                        First_Name='$firstname',
                        Class='$class',
                        Section='$section',
                        Street='$area',
                        Actual='$hostel_actual',
                        Current_Balance='$new_current',
                        Total='$total',
                        Route=NULL
                     WHERE Id_No='$id' AND Type='Hostel Fee'"
            );
            if (!$upd) {
              throw new Exception('Hostel Fee update failed');
            }
          }
        } elseif ($hf_exists) {
          $upd = mysqli_query(
            $link,
            "UPDATE stu_fee_master_data SET
                      First_Name='$firstname',
                      Class='$class',
                      Section='$section',
                      Street='$area',
                      Current_Balance='0',
                      Total=Last_Balance,
                      Route=NULL
                   WHERE Id_No='$id' AND Type='Hostel Fee'"
          );
          if (!$upd) {
            throw new Exception('Hostel Fee inactive update failed');
          }
        }

        mysqli_commit($link);

        echo "<script>alert('Successfully Updated');</script>";
      } catch (Exception $e) {

        mysqli_rollback($link);
        error_log($e->getMessage());

        echo "<script>
            alert('Student updated, but fee update failed. Please verify fees.');
        </script>";
      }
    }
  }
}

$current_referred_by = $_SESSION['Referred_By'] ?? '';
$current_referred_by_id = '';
$current_student_type = $_SESSION['Student_Type'] ?? '';
$current_van_route = $_SESSION['Van_Route'] ?? '';

if (isset($_SESSION['Stu_Id_No'])) {
  $current_id = mysqli_real_escape_string($link, $_SESSION['Stu_Id_No']);
  $current_referral_query = mysqli_query($link, "SELECT Referred_By, Referred_By_Id, Student_Type, Van_Route FROM `student_master_data` WHERE Id_No = '$current_id' LIMIT 1");
  if ($current_referral_query && $current_referral_row = mysqli_fetch_assoc($current_referral_query)) {
    $current_referred_by = $current_referral_row['Referred_By'];
    $current_referred_by_id = $current_referral_row['Referred_By_Id'];
    $current_student_type = $current_referral_row['Student_Type'];
    $current_van_route = $current_referral_row['Van_Route'];
  }
}

$form_referred_by = isset($_POST['Referred_By']) ? $_POST['Referred_By'] : $current_referred_by;
$form_referred_by_id = isset($_POST['Referred_By_Id']) ? $_POST['Referred_By_Id'] : $current_referred_by_id;
$form_referred_by_type = isset($_POST['Referred_By_Type']) ? $_POST['Referred_By_Type'] : ($current_referred_by_id == '' || $current_referred_by_id === null ? 'Non-Staff' : 'Staff');
$form_student_type = isset($_POST['Student_Type']) ? $_POST['Student_Type'] : ($current_student_type ?: 'Day Scholar');
$form_van_route = isset($_POST['Van_Route']) ? $_POST['Van_Route'] : $current_van_route;
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
  <link rel="stylesheet" href="/Futuregen/css/form-style.css" />
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

  /* Slider */
  .quantity {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
  }

  .quantity__minus,
  .quantity__plus {
    display: block;
    width: 35px;
    height: 35px;
    background: #dee0ee;
    text-align: center;
    padding-top: 4.5px;
  }

  .quantity__minus:hover,
  .quantity__plus:hover {
    background: #575b71;
    color: #fff;
  }

  .quantity__minus {
    border-radius: 50%;
    cursor: pointer;
    margin-right: 5px;
  }

  .quantity__plus {
    border-radius: 50%;
    cursor: pointer;
    margin-left: 5px;
  }

  .quantity__input {
    text-align: center;
  }

  .referral-modal {
    position: fixed;
    z-index: 9999;
    inset: 0;
    display: none;
    justify-content: center;
    align-items: center;
    padding: 15px;
    background: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
  }

  .referral-modal-content {
    background: #fff;
    width: 100%;
    max-width: 500px;
    padding: 24px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  }

  .referral-modal-content h3 {
    margin-bottom: 20px;
    font-size: 22px;
  }

  .modal-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    flex-wrap: wrap;
  }

  .btn {
    display: inline-block;
    padding: 8px 14px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 14px;
  }

  .btn-primary {
    background: #0d6efd;
    color: #fff;
  }

  .btn-secondary {
    background: #6c757d;
    color: #fff;
  }

  #selected_staff {
    margin-top: 8px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background: #f8f9fa;
    min-height: 40px;
  }

  @media (max-width: 600px) {
    .referral-modal {
      align-items: flex-start;
      padding-top: 80px;
    }

    .referral-modal-content {
      padding: 18px;
      border-radius: 8px;
      max-width: 350px;
      width: 100%;
      margin: 0 auto;
    }

    .referral-modal-content h3 {
      font-size: 18px;
    }

    .modal-buttons {
      flex-direction: column;
    }

    .modal-buttons .btn {
      width: 100%;
    }
  }
</style>

<body>
  <?php
  include '../sidebar.php';
  ?>

  <div class="container">

    <div class="content">
      <div class="title">Student Personal Details</div>
      <form action="" method="POST" onsubmit="return validateStudentUpdate();">
        <input type="hidden" name="Referred_By_Id" id="referred_by_id" value="<?php echo htmlspecialchars($form_referred_by_id); ?>">
        <input type="hidden" id="original_referred_by" value="<?php echo htmlspecialchars($current_referred_by); ?>">
        <input type="hidden" id="original_referred_by_id" value="<?php echo htmlspecialchars($current_referred_by_id); ?>">
        <input type="hidden" id="original_van_route" value="<?php echo htmlspecialchars($current_van_route); ?>">
        <input type="hidden" id="original_student_type" value="<?php echo htmlspecialchars($current_student_type); ?>">
        <div class="user-details main-section">
          <div class="input-box">
            <span class="details">Id No. <span class="required">*</span></span>
            <input type="text" placeholder="Enter Id No" value="<?php echo $_SESSION['Stu_Id_No']; ?>" id="id_no" name="Stu_Id_No" oninput="this.value = this.value.toUpperCase()" readonly required />
          </div>
          <div class="input-box">
            <span class="details">Admission No.</span>
            <input type="text" placeholder="Enter Admission No" value="<?php if (isset($_POST['Stu_Adm_No'])) {
                                                                          echo $_POST['Stu_Adm_No'];
                                                                        } else {
                                                                          echo $_SESSION['Stu_Adm_No'];
                                                                        } ?>" name="Stu_Adm_No" />
          </div>
          <div class="input-box">
            <span class="details">Full Name <span class="required">*</span></span>
            <input type="text" placeholder="Enter Fullname" value="<?php if (isset($_POST['First_Name'])) {
                                                                      echo $_POST['First_Name'];
                                                                    } else {
                                                                      echo $_SESSION['First_Name'];
                                                                    } ?>" name="First_Name" required />
          </div>
          <div class="input-box">
            <span class="details">Surname</span>
            <input type="text" placeholder="Enter Surname" value="<?php if (isset($_POST['Sur_Name'])) {
                                                                    echo $_POST['Sur_Name'];
                                                                  } else {
                                                                    echo $_SESSION['Sur_Name'];
                                                                  } ?>" name="Sur_Name" />
          </div>
          <div class="input-box">
            <span class="details">Father Name</span>
            <input type="text" placeholder="Enter Father Name" value="<?php if (isset($_POST['Father_Name'])) {
                                                                        echo $_POST['Father_Name'];
                                                                      } else {
                                                                        echo $_SESSION['Father_Name'];
                                                                      } ?>" name="Father_Name" />
          </div>
          <div class="input-box">
            <span class="details">Mother Name</span>
            <input type="text" placeholder="Enter Mother Name" value="<?php if (isset($_POST['Mother_Name'])) {
                                                                        echo $_POST['Mother_Name'];
                                                                      } else {
                                                                        echo $_SESSION['Mother_Name'];
                                                                      } ?>" name="Mother_Name" />
          </div>
          <div class="input-box">
            <span class="details">Class</span>
            <select name="Stu_Class" id="class">
              <option value="selectclass" selected disabled>--Select Class --</option>
              <option value="PreKG">PreKG</option>
              <option value="LKG">LKG</option>
              <option value="UKG">UKG</option>
              <?php
              for ($i = 1; $i <= 8; $i++) {
                echo "<option value='" . $i . " CLASS" . "'>" . $i . " CLASS" . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Section</span>
            <select name="Stu_Section" id="section">
              <option value="selectsection" selected disabled>--Select Section --</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
              <option value="E">E</option>
            </select>
          </div>
          <div class="input-box">
            <input type="text" placeholder="Enter Passed Out Class" id="pass_class" value="<?php if (isset($_POST['Pass_Class'])) {
                                                                                              echo $_POST['Pass_Class'];
                                                                                            } ?>" name="Pass_Class" />
          </div>
          <div class="input-box">
            <span class="required" style="font-size: 15px;"> For Passed Out - <u>OthersPassedout-23</u> <br> For Drop - <u>DROP-7-23/24</u></span>
          </div>
          <div class="gender-details">
            <span class="gender-title">Gender</span>
            <div class="category">
              <input type="radio" value="Boy" id="boy" name="Gender" <?php if (isset($_POST['Gender']) && $_POST['Gender'] == "Boy") {
                                                                        echo 'checked';
                                                                      } else if ($_SESSION['Gender'] == "Boy") {
                                                                        echo 'checked';
                                                                      } else {
                                                                        echo "";
                                                                      } ?> />
              <span><label for="boy">Boy</label></span>
              <input type="radio" value="Girl" id="girl" name="Gender" <?php if (isset($_POST['Gender']) && $_POST['Gender'] == "Girl") {
                                                                          echo 'checked';
                                                                        } else if ($_SESSION['Gender'] == "Girl") {
                                                                          echo 'checked';
                                                                        } else {
                                                                          echo "";
                                                                        } ?> />
              <span><label for="girl">Girl</label></span>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Date Of Birth</span>
            <input type="date" id="dob" name="DOB" value="<?php if (isset($_POST['DOB'])) {
                                                            echo $_POST['DOB'];
                                                          } ?>" />
          </div>
          <div class="input-box">
            <span class="details">Mobile Number</span>
            <input type="text" placeholder="Enter Mobile No." value="<?php if (isset($_POST['Mobile'])) {
                                                                        echo $_POST['Mobile'];
                                                                      } else {
                                                                        echo $_SESSION['Mobile'];
                                                                      } ?>" name="Mobile" />
          </div>
          <div class="input-box">
            <span class="details">Aadhar Number</span>
            <input type="text" placeholder="Enter Aadhar No." maxlength="12" value="<?php if (isset($_POST['Aadhar'])) {
                                                                                      echo $_POST['Aadhar'];
                                                                                    } else {
                                                                                      echo $_SESSION['Aadhar'];
                                                                                    } ?>" name="Aadhar" />
          </div>
          <div class="input-box">
            <span class="details">Mother Aadhar Number</span>
            <input type="text" placeholder="Enter Mother Aadhar No." maxlength="12" value="<?php if (isset($_POST['Mother_Aadhar'])) {
                                                                                              echo $_POST['Mother_Aadhar'];
                                                                                            } else {
                                                                                              echo $_SESSION['Mother_Aadhar'];
                                                                                            } ?>" name="Mother_Aadhar" />
          </div>
          <div class="input-box">
            <span class="details">Father Aadhar Number</span>
            <input type="text" placeholder="Enter Father Aadhar No." maxlength="12" value="<?php if (isset($_POST['Father_Aadhar'])) {
                                                                                              echo $_POST['Father_Aadhar'];
                                                                                            } else {
                                                                                              echo $_SESSION['Father_Aadhar'];
                                                                                            } ?>" name="Father_Aadhar" />
          </div>
          <div class="gender-details">
            <span class="gender-title">Siblings</span>
            <div class="category">
              <input type="radio" id="yes" value="Yes" name="Siblings" <?php if ($sibling_status == "Yes") {
                                                                          echo 'checked';
                                                                        } else if (!isset($_POST['Siblings']) && $_SESSION['Sibling_Status'] == "Yes") {
                                                                          echo 'checked';
                                                                        } else {
                                                                          echo "";
                                                                        } ?> />
              <span><label for="yes">Yes</label></span>
              <input type="radio" id="no" value="No" name="Siblings" <?php if (isset($_POST['Siblings']) && $_POST['Siblings'] == "No") {
                                                                        echo 'checked';
                                                                      } else if (!isset($_POST['Siblings']) && $_SESSION['Sibling_Status'] == "No") {
                                                                        echo 'checked';
                                                                      } else {
                                                                        echo "";
                                                                      } ?> />
              <span><label for="no">No</label></span>
            </div>
          </div>
          <div class="input-box no_siblings" <?php if (isset($_POST['Siblings']) && $_POST['Siblings'] == "Yes") {
                                                echo '';
                                              } else if (!isset($_POST['Siblings']) && $_SESSION['Sibling_Status'] == "Yes") {
                                                echo '';
                                              } else {
                                                echo "hidden";
                                              } ?>><span>No. Of Siblings</span>
            <div class="quantity">
              <span class="quantity__minus"><span>-</span></span>
              <input name="No_Of_Siblings" type="text" class="quantity__input no_of_siblings" value="0" readonly>
              <span class="quantity__plus"><span>+</span></span>
            </div>
          </div>
        </div>
        <div class="title siblings-title" <?php if (isset($_POST['Siblings']) && $_POST['Siblings'] == "Yes") {
                                            echo '';
                                          } else if (!isset($_POST['Siblings']) && $_SESSION['Sibling_Status'] == "Yes") {
                                            echo '';
                                          } else {
                                            echo "hidden";
                                          } ?>>Siblings Details</div>
        <div class="user-details siblings-section">
          <?php
          if (isset($_POST['Siblings']) && $_POST['Siblings'] == "Yes") {
            $siblings = explode(',', $siblings);
            echo "<script>document.querySelector('.no_of_siblings').value = '" . $no_of_siblings . "'</script>";
            $classes = array();
            if ($no_of_siblings != 0) {
              foreach ($siblings as $sibling) {
                $query1 = mysqli_query($link, "SELECT Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '$sibling'");
                while ($row1 = mysqli_fetch_assoc($query1)) {
                  $classes[$sibling] = $row1['Stu_Class'] . ' ' . $row1['Stu_Section'];
                }
                echo '
              <div class="all_siblings input-box">
                <span>Sibling Id No.</span>
                <input type="text" placeholder="Enter Sibling Id No" id="id_no[]" name="Sib_Id_No[]" value = "' . $sibling . '" oninput="this.value = this.value.toUpperCase()"/>
              </div>
              <div class="all_siblings input-box">
                <span>Sibling Class</span>
                <input type="text" placeholder="Enter Sibling Class" value = "' . $classes[$sibling] . '" id="sib_class[]" name="Sib_Class[]" readonly/>
              </div>';
              }
            }
          } else if (!isset($_POST['Siblings']) && $_SESSION['Sibling_Status'] == "Yes") {
            $siblings = explode(',', $_SESSION['Siblings']);
            echo "<script>document.querySelector('.no_of_siblings').value = '" . count($siblings) . "'</script>";
            $classes = array();
            foreach ($siblings as $sibling) {
              $query1 = mysqli_query($link, "SELECT Stu_Class,Stu_Section FROM `student_master_data` WHERE Id_No = '$sibling'");
              while ($row1 = mysqli_fetch_assoc($query1)) {
                $classes[$sibling] = $row1['Stu_Class'] . ' ' . $row1['Stu_Section'];
              }
              echo '
              <div class="all_siblings input-box">
                <span>Sibling Id No.</span>
                <input type="text" placeholder="Enter Sibling Id No" class="sib_id_no" id="id_no[]" name="Sib_Id_No[]" value = "' . $sibling . '" oninput="this.value = this.value.toUpperCase()"/>
              </div>
              <div class="all_siblings input-box">
                <span>Sibling Class</span>
                <input type="text" placeholder="Enter Sibling Class" class="sib_class" value = "' . $classes[$sibling] . '" id="sib_class[]" name="Sib_Class[]" readonly/>
              </div>';
            }
          }
          ?>
        </div>
        <div class="title">Student Address Details</div>
        <div class="user-details">
          <div class="gender-details">
            <span class="gender-title">Religion</span>
            <div class="category">
              <?php
              ?>
              <input type="radio" name="Religion" id="indian-hindu" value="Indian-Hindu" <?php if (isset($_POST['Religion']) && $_POST['Religion'] == "Indian-Hindu") {
                                                                                            echo 'checked';
                                                                                          } else if (!isset($_POST['Religion']) && strcasecmp($_SESSION['Religion'], 'Indian-Hindu') == 0) {
                                                                                            echo "checked";
                                                                                          } else {
                                                                                            echo '';
                                                                                          } ?> />
              <span><label for="indian-hindu">Indian-Hindu</label></span>
              <input type="radio" name="Religion" id="indian-islam" value="Indian-Islam" <?php if (isset($_POST['Religion']) && $_POST['Religion'] == "Indian-Islam") {
                                                                                            echo 'checked';
                                                                                          } else if (!isset($_POST['Religion']) && strcasecmp($_SESSION['Religion'], 'Indian-Islam') == 0) {
                                                                                            echo "checked";
                                                                                          } else {
                                                                                          } ?> />
              <span><label for="indian-islam">Indian-islam</label></span>
              <input type="radio" name="Religion" id="indian-christian" value="Indian-Christian" <?php if (isset($_POST['Religion']) && $_POST['Religion'] == "Indian-Christian") {
                                                                                                    echo 'checked';
                                                                                                  } else if (!isset($_POST['Religion']) && strcasecmp($_SESSION['Religion'], 'Indian-Christian') == 0) {
                                                                                                    echo "checked";
                                                                                                  } else {
                                                                                                  } ?> />
              <span><label for="indian-christian">Indian-Christian</label></span>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Caste</span>
            <input type="text" placeholder="Enter Caste" value="<?php if (isset($_POST['Caste'])) {
                                                                  echo $_POST['Caste'];
                                                                } else {
                                                                  echo $_SESSION['Caste'];
                                                                } ?>" name="Caste" />
          </div>
          <div class="input-box">
            <span class="details">Category</span>
            <select name="Category" id="category">
              <option value="selectcategory" disabled>--Select Category--</option>
              <option value="OC">OC</option>
              <option value="BC">BC</option>
              <option value="ST">ST</option>
              <option value="SC">SC</option>
              <option value="Mi">Mi</option>
            </select>
          </div>
          <div class="input-box">
            <span class="details">House No.</span>
            <input type="text" placeholder="Enter House No." value="<?php if (isset($_POST['House_No'])) {
                                                                      echo $_POST['House_No'];
                                                                    } else {
                                                                      echo $_SESSION['House_No'];
                                                                    } ?>" name="House_No" />
          </div>
          <div class="input-box">
            <span class="details">Area</span>
            <input type="text" placeholder="Enter Area" value="<?php if (isset($_POST['Area'])) {
                                                                  echo $_POST['Area'];
                                                                } else {
                                                                  echo $_SESSION['Area'];
                                                                } ?>" name="Area" />
          </div>
          <div class="input-box">
            <span class="details">Village/Town</span>
            <input type="text" placeholder="Enter Village" value="<?php if (isset($_POST['Village'])) {
                                                                    echo $_POST['Village'];
                                                                  } else {
                                                                    echo $_SESSION['Village'];
                                                                  } ?>" name="Village" />
          </div>
        </div>
        <div class="title">Other Details</div>
        <div class="user-details">
          <div class="input-box">
            <span class="details">Date of Join</span>
            <input type="date" id="doj" name="DOJ" value="<?php if (isset($_POST['DOJ'])) {
                                                            echo $_POST['DOJ'];
                                                          } ?>" />
          </div>
          <div class="input-box">
            <span class="details">Previous School</span>
            <input type="text" placeholder="Enter Previous School" value="<?php if (isset($_POST['Previous_School'])) {
                                                                            echo $_POST['Previous_School'];
                                                                          } else {
                                                                            echo $_SESSION['Previous_School'];
                                                                           } ?>" name="Previous_School" />
          </div>
          <div class="gender-details">
            <span class="gender-title">Student Type <span class="required">*</span></span>
            <div class="category">
              <input type="radio" id="day_scholar" value="Day Scholar" name="Student_Type" <?php if ($form_student_type == 'Day Scholar') {
                                                                                              echo 'checked';
                                                                                            } ?> required />
              <span><label for="day_scholar">Day Scholar</label></span>
              <input type="radio" id="hosteller" value="Hosteller" name="Student_Type" <?php if ($form_student_type == 'Hosteller') {
                                                                                          echo 'checked';
                                                                                        } ?> />
              <span><label for="hosteller">Hosteller</label></span>
              <input type="radio" id="vanner" value="Vanner" name="Student_Type" <?php if ($form_student_type == 'Vanner') {
                                                                                    echo 'checked';
                                                                                  } ?> />
              <span><label for="vanner">Vanner</label></span>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Van Route</span>
            <select class="form-control" name="Van_Route" id="van_route" <?php if ($form_student_type != 'Vanner') {
                                                                            echo 'disabled';
                                                                          } ?>>
              <option value="" <?php if ($form_van_route == "") {
                                   echo "selected";
                                 } ?>>-- Select Route --</option>
              <?php
              $van_sql = mysqli_query($link, "SELECT Van_Route FROM `van_route` ORDER BY Van_Route");
              while ($van_row = mysqli_fetch_assoc($van_sql)) {
                echo '<option value="' . $van_row['Van_Route'] . '"';
                if ($form_van_route == $van_row['Van_Route']) {
                  echo 'selected';
                }
                echo '>' . $van_row['Van_Route'] . '</option>';
              }
              ?>
              <option value="Drop" <?php if ($form_van_route == "Drop") {
                                      echo 'selected';
                                    } ?>>Drop</option>
            </select>
          </div>
          <div class="gender-details">
            <span class="gender-title">Referred By Type <span class="required">*</span></span>
            <div class="category">
              <input type="radio" id="staff" value="Staff" name="Referred_By_Type" <?php if ($form_referred_by_type == 'Staff') {
                                                                                      echo 'checked';
                                                                                    } ?> />
              <span><label for="staff">Staff</label></span>
              <input type="radio" id="non-staff" value="Non-Staff" name="Referred_By_Type" <?php if ($form_referred_by_type != 'Staff') {
                                                                                            echo 'checked';
                                                                                          } ?> />
              <span><label for="non-staff">Non-Staff</label></span>
            </div>
          </div>

          <div class="input-box" id="staff_box">
            <span class="details">Referred By</span>
            <input type="hidden" name="Referred_By" id="referred_by_hidden" value="<?php echo htmlspecialchars($form_referred_by_type == 'Staff' ? $form_referred_by : ''); ?>">
            <div id="selected_staff"><?php echo htmlspecialchars($form_referred_by_type == 'Staff' && $form_referred_by_id != '' ? $form_referred_by . ' (' . $form_referred_by_id . ')' : ''); ?></div>
            <button type="button" class="btn btn-primary" style="margin-top:8px;" onclick="openReferralModal()">Change Staff</button>
          </div>

          <div class="input-box" id="nonstaff_box">
            <span class="details">Referred By</span>
            <input type="text" placeholder="Enter Referred By" value="<?php echo htmlspecialchars($form_referred_by_type != 'Staff' ? $form_referred_by : ''); ?>" id="referred_by_text" name="Referred_By" />
          </div>
        </div>
        <div class="button">
          <div class="btn-wrapper"
            <?php if (!can('update', MENU_ID)) { ?>
            title="You don't have permission to update student data"
            <?php } ?>>
            <input type="submit" name="update" value="Update" onclick="return checkUpdate();" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?> />
          </div>
        </div>
      </form>
    </div>
  </div>

  <div id="referral_modal" class="referral-modal">
    <div class="referral-modal-content">
      <h3>Select Staff</h3>

      <div class="input-box">
        <span class="details">Branch</span>
        <select id="branch" name="Branch">
          <option value="">-- Select Branch --</option>
          <?php foreach ($branches as $b) { ?>
            <option value="<?= $b['School_Code'] ?>">
              <?= $b['Display_Name'] ?>
            </option>
          <?php } ?>
        </select>
      </div>

      <div class="input-box">
        <span class="details">User Type</span>
        <select id="user_type" name="User_Type">
          <option value="">-- Select Type --</option>
          <option value="Admin">Admin</option>
          <option value="Faculty">Faculty</option>
        </select>
      </div>

      <div class="input-box">
        <span class="details">User</span>
        <select id="user" name="Owner_Id">
          <option value="">-- Select User --</option>
        </select>
      </div>

      <div class="modal-buttons">
        <button type="button" class="btn btn-primary" onclick="selectUser()">Select</button>
        <button type="button" class="btn btn-secondary" onclick="closeReferralModal()">Cancel</button>
      </div>
    </div>
  </div>


  <!-- Scripts -->

  <!-- Set Values of Class, Section, Category, DOB, DOJ -->
  <script type="text/javascript">
    $(document).ready(function() {
      stuclass = '<?php if (isset($class)) {
                    echo $class;
                  } else {
                    echo $_SESSION['Stu_Class'];
                  } ?>';
      stusection = '<?php if (isset($section)) {
                      echo $section;
                    } else {
                      echo $_SESSION['Stu_Section'];
                    } ?>';
      category = '<?php if (isset($_POST['Category'])) {
                    echo $_POST['Category'];
                  } else {
                    echo $_SESSION['Category'];
                  } ?>';
      var dob = '<?php echo $_SESSION['DOB'] ?>';
      date1 = dob.substring(0, 2);
      month1 = dob.substring(3, 5);
      year1 = dob.substring(6, 10);
      var doj = '<?php echo $_SESSION['DOJ'] ?>';
      date2 = doj.substring(0, 2);
      month2 = doj.substring(3, 5);
      year2 = doj.substring(6, 10);
      if (stuclass.toLowerCase().includes("others") || stuclass.toLowerCase().includes("drop")) {
        $('#class').find('option[value="selectclass"]').attr('selected', 'selected');
        $('#section').find('option[value="selectsection"]').attr('selected', 'selected');
        $('#pass_class').val(stuclass);
      } else {
        $('#pass_class').val(' ');
        $('#class').find('option[value="' + stuclass + '"]').attr('selected', 'selected');
        $('#section').find('option[value="' + stusection + '"]').attr('selected', 'selected');
      }
      $('#category').find('option[value="' + category + '"]').attr('selected', 'selected');
      <?php if (!isset($_POST['DOB'])) { ?>
        $('#dob').val(year1 + '-' + month1 + '-' + date1);
      <?php } ?>
      <?php if (!isset($_POST['DOJ'])) { ?>
        $('#doj').val(year2 + '-' + month2 + '-' + date2);
        console.log(doj)
        /* if (year2 == "99") {
          $('#doj').val('19' + year2 + '-' + month2 + '-' + date2);
        } else {
          $('#doj').val('20' + year2 + '-' + month2 + '-' + date2);
        } */
      <?php } ?>
    });
  </script>

  <!-- Referred By Staff / Non-Staff Flow -->
  <script type="text/javascript">
    const staffRadio = document.querySelector('input[value="Staff"]');
    const nonStaffRadio = document.querySelector('input[value="Non-Staff"]');
    const staff_box = document.getElementById('staff_box');
    const nonstaff_box = document.getElementById('nonstaff_box');
    const referred_by_text = document.getElementById('referred_by_text');
    const referred_by_hidden = document.getElementById('referred_by_hidden');
    const referred_by_id = document.getElementById('referred_by_id');
    const selected_staff = document.getElementById('selected_staff');
    const branch = document.getElementById('branch');
    const user_type = document.getElementById('user_type');
    const user = document.getElementById('user');
    const studentTypeRadios = document.querySelectorAll('input[name="Student_Type"]');
    const vanRoute = document.getElementById('van_route');
    const originalReferredBy = document.getElementById('original_referred_by');
    const originalReferredById = document.getElementById('original_referred_by_id');
    const originalVanRoute = document.getElementById('original_van_route');
    const originalStudentType = document.getElementById('original_student_type');

    const currentState = {
      referred_by: (referred_by_text && referred_by_text.value) || (referred_by_hidden && referred_by_hidden.value) || originalReferredBy.value || '',
      nonstaff_referred_by: (nonStaffRadio.checked && referred_by_text) ? referred_by_text.value : '',
      staff_referred_by: (staffRadio.checked && referred_by_hidden) ? referred_by_hidden.value : '',
      referred_by_id: referred_by_id.value || originalReferredById.value || '',
      van_route: vanRoute.value || originalVanRoute.value || '',
      student_type: selectedStudentType() || originalStudentType.value || 'Day Scholar'
    };

    function selectedStudentType() {
      const selected = document.querySelector('input[name="Student_Type"]:checked');
      return selected ? selected.value : '';
    }

    function isEmptyRoute(value) {
      return value === '' || value === 'NULL' || value === null;
    }

    function rememberCurrentRoute() {
      currentState.van_route = vanRoute.value;
    }

    function restoreVanRouteIfNeeded() {
      const preservedRoute = !isEmptyRoute(currentState.van_route) ? currentState.van_route : originalVanRoute.value;

      if (isEmptyRoute(vanRoute.value) && !isEmptyRoute(preservedRoute)) {
        vanRoute.value = preservedRoute;
      }
    }

    function handleStudentTypeChange() {
      const studentType = selectedStudentType();
      currentState.student_type = studentType;

      if (studentType === 'Vanner') {
        vanRoute.disabled = false;
        vanRoute.required = true;
        restoreVanRouteIfNeeded();
      } else {
        rememberCurrentRoute();
        vanRoute.disabled = true;
        vanRoute.required = false;
      }
    }

    function toggleReferral(keepCurrentStaff) {
      if (staffRadio.checked) {
        staff_box.style.display = 'block';
        nonstaff_box.style.display = 'none';
        referred_by_text.disabled = true;
        referred_by_hidden.disabled = false;

        if (!keepCurrentStaff && !referred_by_id.value) {
          referred_by_id.value = currentState.referred_by_id || originalReferredById.value || '';
        }

        if (!referred_by_hidden.value.trim()) {
          referred_by_hidden.value = currentState.staff_referred_by || originalReferredBy.value || '';
        }

        if (!selected_staff.innerText.trim() && referred_by_hidden.value.trim() && referred_by_id.value.trim()) {
          selected_staff.innerText = referred_by_hidden.value + " (" + referred_by_id.value + ")";
        }
      } else {
        staff_box.style.display = 'none';
        nonstaff_box.style.display = 'block';
        referred_by_text.disabled = false;
        referred_by_hidden.disabled = true;

        if (!referred_by_text.value.trim()) {
          referred_by_text.value = currentState.nonstaff_referred_by || currentState.referred_by || originalReferredBy.value || '';
        }
      }
    }

    staffRadio.addEventListener('change', function() {
      toggleReferral(false);
    });
    nonStaffRadio.addEventListener('change', function() {
      toggleReferral(false);
    });
    referred_by_text.addEventListener('input', function() {
      if (nonStaffRadio.checked) {
        currentState.referred_by = referred_by_text.value;
        currentState.nonstaff_referred_by = referred_by_text.value;
      }
    });
    referred_by_hidden.addEventListener('input', function() {
      if (staffRadio.checked) {
        currentState.referred_by = referred_by_hidden.value;
        currentState.staff_referred_by = referred_by_hidden.value;
      }
    });
    vanRoute.addEventListener('change', rememberCurrentRoute);
    studentTypeRadios.forEach(radio => radio.addEventListener('change', handleStudentTypeChange));

    function openReferralModal() {
      document.getElementById('referral_modal').style.display = 'flex';
    }

    function closeReferralModal() {
      document.getElementById('referral_modal').style.display = 'none';
    }

    function selectUser() {
      let selected = user.value;
      let text = user.options[user.selectedIndex].text;

      if (!branch.value || !user_type.value) return alert("Please select Branch and Type");
      if (!selected) return alert("Select user");

      referred_by_id.value = selected;
      referred_by_hidden.value = text;
      selected_staff.innerText = text + " (" + selected + ")";
      currentState.referred_by = text;
      currentState.staff_referred_by = text;
      currentState.referred_by_id = selected;

      closeReferralModal();
    }

    function fetchReferralUsers() {
      let selectedBranch = branch.value;
      let selectedType = user_type.value;

      if (!selectedBranch || !selectedType) {
        user.innerHTML = '<option value="">-- Select User --</option>';
        return;
      }

      let data = new FormData();
      data.append('action', 'fetch_users');
      data.append('branch', selectedBranch);
      data.append('type', selectedType);

      fetch('', {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(users => {
          let options = '<option value="">-- Select User --</option>';

          users.forEach(u => {
            options += `<option value="${u.Id_No}">${u.Name}</option>`;
          });

          user.innerHTML = options;
        });
    }

    branch.addEventListener('change', fetchReferralUsers);
    user_type.addEventListener('change', fetchReferralUsers);

    toggleReferral(true);
    handleStudentTypeChange();
  </script>

  <!-- Add/Remove Slider and Sibling Text Boxes -->
  <script type="text/javascript">
    function add_Ele(val) {
      if (val != '-1') {
        sibling = '<div class="all_siblings input-box"><span>Sibling Id No.</span><input type="text" placeholder="Enter Sibling Id No" class="sib_id_no" id="id_no[' + parseInt($('.no_of_siblings').val()) + ']" name="Sib_Id_No[]" oninput="this.value = this.value.toUpperCase()"/></div><div class="all_siblings input-box"><span>Sibling Class</span><input type="text" placeholder="Enter Sibling Class" class="sib_class" id="sib_class[' + parseInt($('.no_of_siblings').val()) + ']" name="Sib_Class[]" readonly/></div>'
        $('.siblings-section').append(sibling)
      } else {
        if ($('.no_of_siblings').val() > 1)
          $('.siblings-section').children().last().remove()
        $('.siblings-section').children().last().remove()
      }
    }

    document.body.addEventListener('change', function(e) {
      if (e.target.id == "yes") {
        document.querySelector('.siblings-title').hidden = '';
        $('.main-section').append('<div class="input-box no_siblings"><span>No. Of Siblings</span><div class="quantity"><span class="quantity__minus"><span>-</span></span><input name="No_Of_Siblings" type="text" class="quantity__input no_of_siblings" value="0"readonly><span class="quantity__plus"><span>+</span></span></div></div>')
        $(document).ready(function() {
          const minus = $('.quantity__minus');
          const plus = $('.quantity__plus');
          const input = $('.quantity__input');
          minus.click(function(e) {
            e.preventDefault();
            var value = input.val();
            if (value > 1) {
              value--;
            }
            input.val(value);
          });

          plus.click(function(e) {
            e.preventDefault();
            var value = input.val();
            value++;
            input.val(value);
          })
        });
        $('.quantity__minus').click(() => {
          if ($('.no_of_siblings').val() > 1)
            add_Ele(-1)
        });
        $('.quantity__plus').on('click', function() {
          add_Ele(1)
        });

      } else if (e.target.id == "no") {
        document.querySelector('.siblings-title').hidden = 'hidden';
        $('.no_siblings').remove()
        $('.all_siblings').remove()
      }
    });

    $(document).ready(function() {
      const minus = $('.quantity__minus');
      const plus = $('.quantity__plus');
      const input = $('.quantity__input');
      minus.click(function(e) {
        e.preventDefault();
        var value = input.val();
        if (value > 1) {
          value--;
        }
        input.val(value);
      });

      plus.click(function(e) {
        e.preventDefault();
        var value = input.val();
        value++;
        input.val(value);
      })
    });

    $('.quantity__minus').click(() => {
      if ($('.no_of_siblings').val() > 1)
        add_Ele(-1)
    });

    $('.quantity__plus').on('click', function() {
      add_Ele(1)
    });
  </script>

  <!-- Getting Id No of Each Sibling -->
  <script type="text/javascript">
    $(document).on('change', function(e) {
      if (e.target.id.includes('id_no[')) {
        sib_class = "sib_class[" + e.target.id.split('[')[1].charAt(0) + "]"
        id_no = document.getElementById(e.target.id).value
        original_id = document.getElementById('id_no').value
        var flag = true;
        document.querySelectorAll('.sib_id_no').forEach((ele) => {
          if (ele.value == id_no && ele.id != e.target.id) {
            flag = false;
            return;
          }
        });
        if (!flag) {
          alert("Duplicate Siblings are Not Allowed!")
        } else {
          if (id_no == original_id) {
            alert('Sibling Id and Student Id are Same')
          } else {
            $.ajax({
              type: 'post',
              url: 'temp.php',
              data: {
                Id_No: id_no,
              },
              success: function(data) {
                if (data == "0") {
                  alert('No Student Found With ' + id_no)
                } else {
                  document.getElementById(sib_class).value = data
                }
              }
            });
          }
        }
      }
    });
  </script>

  <!-- Validating Siblings Classes -->
  <script>
    function checkUpdate() {
      let flag = true;
      document.querySelectorAll('.sib_class').forEach((e) => {
        if (e.value == "") {
          alert('Invalid Siblings');
          flag = false;
          return;
        }
      })
      return flag;
    }

    function validateStudentUpdate() {
      if (!checkUpdate()) {
        return false;
      }

      const studentType = selectedStudentType();

      if (!studentType) {
        alert('Please select Student Type');
        return false;
      }

      if (studentType === 'Vanner' && isEmptyRoute(vanRoute.value)) {
        alert('Please select Van Route for Vanner student');
        return false;
      }

      if (staffRadio.checked) {
        if (!referred_by_hidden.value.trim() || !referred_by_id.value.trim()) {
          alert("Select staff");
          return false;
        }
      } else {
        if (!referred_by_text.value.trim()) {
          alert("Enter referred by");
          return false;
        }
      }

      if (!confirm('Confirm to Update Student Data?')) {
        return false;
      }

      return true;
    }
  </script>
</body>

</html>
