<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 58);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php
if (isset($_POST['Ok'])) {
  if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view committed fee');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  if ($_POST['Type']) {
    $type = $_POST['Type'];
    if ($_POST['Id_No']) {
      $id = $_POST['Id_No'];
      $query2 = mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id'");
      //$query2 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
      if (mysqli_num_rows($query2) == 0) {
        echo "<script>alert('Student Not Found!')</script>";
      } else {
        while ($row = mysqli_fetch_assoc($query2)) {
          $name = $row['First_Name'];
          $class = $row['Stu_Class'];
          $section = $row['Stu_Section'];
          $village = $row['Area'];
          $master_route = $row['Van_Route'];
        }
        if (str_contains(strtolower($class), "others") || str_contains(strtolower($class), "drop")) {
          $query3 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
          if (mysqli_num_rows($query3) == 0) {
            $fee_save_query = mysqli_query($link, "SELECT * FROM `fee_balances` WHERE Id_No = '$id' AND Type = '$type'");
            if (mysqli_num_rows($fee_save_query) != 0) {
              while ($fee_row = mysqli_fetch_assoc($fee_save_query)) {
                $last = $fee_row['Balance'];
                $committed = 0;
                $total = (int)$last + (int)$committed;
              }
            } else {
              echo "<script>alert('Student Not Found in Fee Master Data!')</script>";
            }
          } else {
            while ($row4 = mysqli_fetch_assoc($query3)) {
              $route = $row4['Route'];
              $actual = $row4['Actual'];
              $last = $row4['Last_Balance'];
              $committed = $row4['Current_Balance'];
              $total = (int)$last + (int)$committed;
            }
          }
          echo "<script>alert('Student Passedout or Dropped!')</script>";
        } else {
          $query3 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
          if (mysqli_num_rows($query3) == 0) {
            if ($type != "Vehicle Fee") {
              $query4 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
              //Getting Actual Fee
              if (mysqli_num_rows($query4) != 0) {
                while ($row = mysqli_fetch_assoc($query4)) {
                  $actual = $row['Fee'];
                }
              }
            } else {
              if ($type == "Vehicle Fee") {
                $route = $master_route;
              }
              if ($route == "Drop") {
                $actual = 0;
                $query3 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
                if (mysqli_num_rows($query3) == 0) {
                  echo "<script>alert('Student Not Found in Fee Master Data!')</script>";
                } else {
                  while ($row4 = mysqli_fetch_assoc($query3)) {
                    $route = $row4['Route'];
                    $actual = $row4['Actual'];
                    $last = $row4['Last_Balance'];
                    $committed = $row4['Current_Balance'];
                    $total = (int)$last + (int)$committed;
                  }
                }
                echo "<script>alert('Student Dropped from Van Route!')</script>";
              } else {
                $query4 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
                //Getting Actual Fee
                if (mysqli_num_rows($query4) != 0) {
                  while ($row = mysqli_fetch_assoc($query4)) {
                    $actual = $row['Fee'];
                  }
                }
              }
            }
            $last = 0;
            echo "<script>alert('Student Not Found for this Fee Type!')</script>";
          } else {
            $route_status = true;
            if ($type == "Vehicle Fee") {
              while ($row2 = mysqli_fetch_assoc($query3)) {
                $route = $row2['Route'];
              }
              if ($master_route != $route) {
                $route_status = false;
                echo "<script>alert('Van Route is different in Student master data and Fee Master data!Please Verify this!')</script>";
              } else {
                if ($master_route == "Drop") {
                  echo "<script>alert('Student Dropped Van Route!')</script>";
                }
                $query4 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Route = '$route' AND Type = '$type'");
              }
            } else {
              $query4 = mysqli_query($link, "SELECT * FROM `actual_fee` WHERE Class = '$class' AND Type = '$type'");
            }
            if ($route_status) {
              if (mysqli_num_rows($query4) == 0) {
                echo "<script>alert('Actual Fee Not Available!')</script>";
              } else {
                while ($row = mysqli_fetch_assoc($query4)) {
                  $actual = $row['Fee'];
                }
              }
            }
            $query3 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
            while ($row4 = mysqli_fetch_assoc($query3)) {
              $route = $row4['Route'];
              $last = $row4['Last_Balance'];
              $committed = $row4['Current_Balance'];
              $total = (int)$last + (int)$committed;
            }
          }
        }
      }
    } else {
      echo "<script>alert('Please Enter ID No!')</script>";
    }
  } else {
    echo "<script>alert('Please Select Fee Type!')</script>";
  }
}

if (isset($_POST['add'])) {
  if (!can('update', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to update committed fee');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = $_POST['Id_No'];
  $name = $_POST['First_Name'];
  $class = $_POST['Class'];
  $section = $_POST['Section'];
  $pass_class = $_POST['Pass_Class'];
  $village = $_POST['Village'];
  $type = $_POST['Type'];
  $actual = $_POST['Actual_Fee'];
  $last = $_POST['Last_Balance'];
  $committed = $_POST['Current_Balance'];
  $total = $_POST['Total'];
  if ($type == "Vehicle Fee") {
    $route = $_POST['Van_Route'];
  }
  $sql1 = mysqli_query($link, "SELECT * FROM `stu_fee_master_data` WHERE Id_No = '$id' AND Type = '$type'");
  if (mysqli_num_rows($sql1) == 0) {
    if ($type != "Vehicle Fee") {
      $sql = "INSERT INTO `stu_fee_master_data` VALUES ('','$id','$name','$class','$section','$village','$type','$actual','$last','$committed','$total','')";
    } else {
      if ($route != "") {
        $sql = "INSERT INTO `stu_fee_master_data` VALUES ('','$id','$name','$class','$section','$village','$type','$actual','$last','$committed','$total','$route')";
      } else {
        echo "<script>alert('Please Update Van Route in Student Details!')</script>";
      }
    }
    if (mysqli_query($link, $sql)) {
      echo "<script>alert('Fee Inserted Successfully!!')</script>";
    } else {
      echo "<script>alert('Fee Insertion Failed!!')</script>";
    }
  } else {
    if ($pass_class != "") {
      $class = $pass_class;
      $section = "";
    }
    if ($type != "Vehicle Fee") {
      $sql = "UPDATE `stu_fee_master_data` SET Class = '$class', Section = '$section', Street = '$village', Last_Balance = '$last',Current_Balance = '$committed', Total = '$total' WHERE Id_No = '$id' AND Type = '$type'";
    } else {
      $sql = "UPDATE `stu_fee_master_data` SET Class = '$class', Section = '$section', Street = '$village',Route = '$route', Last_Balance = '$last',Current_Balance = '$committed', Total = '$total' WHERE Id_No = '$id' AND Type = '$type'";
    }
    if (mysqli_query($link, $sql)) {
      echo "<script>alert('Fee Updated Successfully!!')</script>";
    } else {
      echo "<script>alert('Fee Updation Failed!!')</script>";
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
  <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
  <!-- Boxiocns CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <!-- Bootstrap Links -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
  @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  .required {
    color: red;
    font-size: 20px;
  }

  .container {
    margin: 50px 350px;
    max-width: 700px;
    width: 100%;
    padding: 25px 30px;
    border-radius: 5px;
    box-shadow: 0 5px 10px rgba(0, 0, 0, 0.15);
    background-image: linear-gradient(to top, #37ecba 0%, #72afd3 100%);
  }

  .container .title {
    font-size: 25px;
    font-weight: 500;
    position: relative;
  }

  .container .title::before {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    height: 5px;
    width: 100%;
    border-radius: 5px;
    background: linear-gradient(135deg, #71b7e6, #9b59b6);
  }

  .content form .user-details {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin: 20px 0 12px 0;
  }

  form .user-details .input-box {
    margin-bottom: 15px;
    width: calc(100% / 2 - 20px);
  }

  form .input-box span.details {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
  }

  .user-details .input-box input,
  select {
    height: 45px;
    width: 100%;
    outline: none;
    font-size: 16px;
    border-radius: 5px;
    padding-left: 15px;
    border: 1px solid #ccc;
    border-bottom-width: 2px;
    transition: all 0.3s ease;
  }

  .user-details .input-box input:focus,
  .user-details .input-box input:valid {
    border-color: #9b59b6;
  }

  form .gender-details .gender-title {
    font-size: 20px;
    font-weight: 500;
  }

  form .category {
    display: flex;
    width: 80%;
    margin: 14px 0;
    justify-content: space-between;
    font-size: large;
  }

  form .category input {
    margin-left: 20px;
  }

  form .button {
    height: 45px;
    margin: 35px 0;
  }

  form .button input {
    height: 100%;
    width: 100%;
    border-radius: 5px;
    border: none;
    color: #fff;
    font-size: 18px;
    font-weight: 500;
    letter-spacing: 1px;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 5px;
    background: linear-gradient(135deg, #71b7e6, #9b59b6);
  }

  form .button input:hover {
    /* transform: scale(0.99); */
    background: linear-gradient(-135deg, #71b7e6, #9b59b6);
  }

  @media (max-width: 584px) {
    .container {
      max-width: 70%;
      margin: 30px 80px;
    }

    form .user-details .input-box {
      margin-bottom: 15px;
      width: 100%;
    }

    form .category {
      width: 100%;
    }

    .content form .user-details {
      max-height: 300px;
      overflow-y: scroll;
    }

    .user-details::-webkit-scrollbar {
      width: 5px;
    }
  }

  @media (max-width: 459px) {
    .container .content .category {
      flex-direction: column;
    }
  }

  #sign-out {
    display: none;
  }

  @media screen and (min-width:600px) {
    #ok {
      margin-top: 30px;
      margin-left: 50px;
    }
  }

  @media screen and (max-width:920px) {
    #sign-out {
      display: block;
    }
  }

  #class,
  #section,
  #van_route {
    pointer-events: none;
  }

  /* 🔒 Disabled state */
  form .button input:disabled {
    cursor: not-allowed;
    opacity: 0.6;
    background: linear-gradient(135deg, #b5b5b5, #8e8e8e);
  }

  .btn-wrapper {
    display: contents;
  }
</style>

<body>
  <?php
  include '../sidebar.php';
  ?>

  <div class="container">

    <div class="content">
      <div class="title">Student Commited Fee Entry</div>
      <form action="" method="POST">
        <div class="user-details">
          <div class="input-box">
            <span class="details">Fee Type<span class="required">*</span></span>
            <select name="Type" id="type">
              <option value="selectfeetype" disabled <?php if (isset($type) && $type == "selectfeetype") {
                                                        echo "selected";
                                                      } else {
                                                        echo "";
                                                      } ?>>-- Select Fee Type --</option>
              <option value="School Fee" <?php if (isset($type) && $type == "School Fee") {
                                            echo "selected";
                                          } else {
                                            echo "";
                                          } ?>>School Fee</option>
              <option value="Admission Fee" <?php if (isset($type) && $type == "Admission Fee") {
                                              echo "selected";
                                            } else {
                                              echo "";
                                            } ?>>Admission Fee</option>
              <option value="Examination Fee" <?php if (isset($type) && $type == "Examination Fee") {
                                                echo "selected";
                                              } else {
                                                echo "";
                                              } ?>>Examination Fee</option>
              <option value="Computer Fee" <?php if (isset($type) && $type == "Computer Fee") {
                                              echo "selected";
                                            } else {
                                              echo "";
                                            } ?>>Computer Fee</option>
              <option value="Vehicle Fee" <?php if (isset($type) && $type == "Vehicle Fee") {
                                            echo "selected";
                                          } else {
                                            echo "";
                                          } ?>>Vehicle Fee</option>
              <option value="Book Fee" <?php if (isset($type) && $type == "Book Fee") {
                                          echo "selected";
                                        } else {
                                          echo "";
                                        } ?>>Book Fee</option>
              <?php
              if ($_SESSION['school_db']['school_code'] == "FGS") {
              ?>
                <option value="Hostel Fee" <?php if (isset($type) && $type == "Hostel Fee") {
                                              echo "selected";
                                            } else {
                                              echo "";
                                            } ?>>Hostel Fee</option>
              <?php
              }
              ?>
            </select>
          </div>
          <div class="input-box">
          </div>
          <div class="input-box">
            <span class="details">Id No. <span class="required">*</span></span>
            <input type="text" placeholder="Enter Id No" value="<?php if (isset($id)) {
                                                                  echo $id;
                                                                } else {
                                                                  echo "";
                                                                } ?>" name="Id_No" oninput="this.value = this.value.toUpperCase()" required />
          </div>
          <div class="input-box">
            <span class="details"></span>
            <div class="btn-wrapper"
              <?php if (!can('view', MENU_ID)) { ?>
              title="You don't have permission to view committed fee"
              <?php } ?>>
              <button class="btn btn-primary" id="ok" name="Ok" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>OK</button>
            </div>
          </div>
          <div class="input-box">
            <span class="details">Full Name</span>
            <input type="text" name="First_Name" value="<?php if (isset($name)) {
                                                          echo $name;
                                                        } else {
                                                          echo "";
                                                        } ?>" readonly required />
          </div>
          <div class="input-box">
            <span class="details">Passedout/Drop</span>
            <input type="text" id="pass_class" name="Pass_Class" value="<?php if (isset($class) && (str_contains(strtolower($class), "other") || str_contains(strtolower($class), "drop"))) {
                                                                          echo $class;
                                                                        } else {
                                                                          echo "";
                                                                        } ?>" readonly required />
          </div>
          <div class="input-box">
            <span class="details">Class</span>
            <select name="Class" id="class">
              <option value="selectclass" selected disabled>--Select Class --</option>
              <option value="PreKG" <?php if (isset($class) && $class == "PreKG") {
                                      echo "selected";
                                    } else {
                                      echo "";
                                    } ?>>PreKG</option>
              <option value="LKG" <?php if (isset($class) && $class == "LKG") {
                                    echo "selected";
                                  } else {
                                    echo "";
                                  } ?>>LKG</option>
              <option value="UKG" <?php if (isset($class) && $class == "UKG") {
                                    echo "selected";
                                  } else {
                                    echo "";
                                  } ?>>UKG</option>
              <?php
              for ($i = 1; $i <= 10; $i++) {
                echo "<option value='" . $i . " CLASS" . "'";
                if (isset($class) && $class == $i . " CLASS") {
                  echo "selected";
                } else {
                  echo "";
                }
                echo ">" . $i . " CLASS" . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Section</span>
            <select name="Section" id="section">
              <option value="selectsection" selected disabled>--Select Section --</option>
              <option value="A" <?php if (isset($section) && $section == "A") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>A</option>
              <option value="B" <?php if (isset($section) && $section == "B") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>B</option>
              <option value="C" <?php if (isset($section) && $section == "C") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>C</option>
              <option value="D" <?php if (isset($section) && $section == "D") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>D</option>
              <option value="E" <?php if (isset($section) && $section == "E") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>E</option>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Village/Town</span>
            <input type="text" name="Village" value="<?php if (isset($village)) {
                                                        echo $village;
                                                      } else {
                                                        echo '';
                                                      } ?>" readonly />
          </div>
          <div class="input-box">
            <span class="details">Van Route</span>
            <select name="Van_Route" id="van_route" <?php if (isset($type) && $type != "Vehicle Fee") {
                                                      echo 'style="pointer-events:none;"';
                                                    } else {
                                                      echo '';
                                                    } ?>>
              <option value="" <?php if (isset($route) && $route == "" && $type == "Vehicle Fee") {
                                  echo "selected";
                                } else {
                                  echo "";
                                } ?>>-- Select Route --</option>
              <?php
              $query1 = mysqli_query($link, "SELECT * FROM `van_route`");
              while ($row1 = mysqli_fetch_assoc($query1)) {
                echo "<option value = '" . $row1['Van_Route'] . "'";
                if (isset($route) && $route == $row1['Van_Route'] && $type == "Vehicle Fee") {
                  echo "selected";
                } else {
                  echo "";
                }
                echo ">" . $row1['Van_Route'] . "</option>";
              }
              ?>
              <option value="Drop" <?php if (isset($route) && $route == "Drop" && $type == "Vehicle Fee") {
                                      echo "selected";
                                    } else {
                                      echo "";
                                    } ?>>Drop</option>
            </select>
          </div>
          <div class="input-box">
            <span class="details">Actual Fee</span>
            <input type="text" name="Actual_Fee" id="actual" value="<?php if (isset($actual)) {
                                                                      echo $actual;
                                                                    } else {
                                                                      echo "";
                                                                    } ?>" required readonly />
          </div>
          <div class="input-box">
            <span class="details">Last Year Balance</span>
            <input type="text" id="last" value="<?php if (isset($last)) {
                                                  echo $last;
                                                } else {
                                                  echo "";
                                                } ?>" name="Last_Balance" />
          </div>
          <div class="input-box">
            <span class="details">Commited Fee</span>
            <input type="text" id="current" name="Current_Balance" value="<?php if (isset($committed)) {
                                                                            echo $committed;
                                                                          } else {
                                                                            echo "";
                                                                          } ?>" />
          </div>
          <div class="input-box">
            <span class="details">Total Fee</span>
            <input type="text" name="Total" id="total" value="<?php if (isset($total)) {
                                                                echo $total;
                                                              } else {
                                                                echo "";
                                                              } ?>" />
          </div>
        </div>
        <div class="button">
          <div class="btn-wrapper"
            <?php if (!can('update', MENU_ID)) { ?>
            title="You don't have permission to insert/update committed fee"
            <?php } ?>>
            <input type="submit" name="add" value="Insert/Update" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?> />
          </div>
        </div>
      </form>
    </div>
  </div>
  <!-- Scripts -->

  <!-- To get Actual Fee on change route or class -->
  <script type="text/javascript">
    $("#van_route").on('change', function() {
      $.ajax({
        url: 'temp.php',
        type: 'POST',
        data: {
          route: $("#van_route").val()
        },
        success: function(data) {
          route = $("#van_route").val();
          if (data == "0") {
            alert('Actual Fee Not Available for ' + route)
          } else {
            $("#actual").val(data);
          }
        }
      })
    });
  </script>

  <!-- To Calculate Instant Total -->
  <script>
    $('#total').on('focus', function() {
      last = document.getElementById('last').value;
      curr = document.getElementById('current').value;
      if (last == "") {
        last = 0;
      }
      if (curr == "") {
        curr = 0;
      }
      $('#total').val(parseInt(last) + parseInt(curr));
    });
  </script>
</body>

</html>