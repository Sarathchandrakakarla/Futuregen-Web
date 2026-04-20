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
    location.replace('/Victory/Admin/Student/show_student_page.php')</script>";
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
  $van = validate($_POST['Van_Route']);
  $refer = validate($_POST['Referred_By']);
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
          Village = '$village', DOJ = '$doj', Previous_School = '$previous', Referred_By = '$refer',";
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
  if ($siblings_update_status && isset($update_sql)) {

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
                 SET Route=NULL
                 WHERE Id_No='$id' AND Type='Vehicle Fee'"
          );
          if (!$upd) {
            throw new Exception('Vehicle Fee route NULL update failed');
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
</style>

<body>
  <?php
  include '../sidebar.php';
  ?>

  <div class="container">

    <div class="content">
      <div class="title">Student Personal Details</div>
      <form action="" method="POST" onsubmit="if(!confirm('Confirm to Update Student Data?')){return false;}else{return true;}">
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
              for ($i = 1; $i <= 10; $i++) {
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
          <div class="input-box">
            <span class="details">Van Route</span>
            <select class="form-control" name="Van_Route" id="van_route">
              <option value="" <?php if (isset($_POST['Van_Route']) && $_POST['Van_Route'] == "") {
                                  echo "selected";
                                } ?>>-- Select Route --</option>
              <?php
              $van_sql = mysqli_query($link, "SELECT Van_Route FROM `van_route` ORDER BY Van_Route");
              while ($van_row = mysqli_fetch_assoc($van_sql)) {
                echo '<option value="' . $van_row['Van_Route'] . '"';
                if (isset($_POST['Van_Route']) && $_POST['Van_Route'] == $van_row['Van_Route']) {
                  echo 'selected';
                } else if ($_SESSION['Van_Route'] == $van_row['Van_Route']) {
                  echo 'selected';
                }
                echo '>' . $van_row['Van_Route'] . '</option>';
              }
              ?>
              <option value="Drop" <?php if (isset($_POST['Van_Route']) && $_POST['Van_Route'] == "Drop") {
                                      echo 'selected';
                                    } else if ($_SESSION['Van_Route'] == "Drop") {
                                      echo 'selected';
                                    } ?>>Drop</option>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Referred By</span>
            <input type="text" placeholder="Enter Referred By" value="<?php if (isset($_POST['Referred_By'])) {
                                                                        echo $_POST['Referred_By'];
                                                                      } else {
                                                                        echo $_SESSION['Referred_By'];
                                                                      } ?>" name="Referred_By" />
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
  </script>
</body>

</html>