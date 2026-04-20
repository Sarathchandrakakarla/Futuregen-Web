<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 130);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php
include '../link.php';
if (isset($_POST['change'])) {
  function validate($data)
  {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  if (!can('update', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to change your password! Please Contact School Office!');
      location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
    exit;
  }
  $id = validate($_POST['UserName']);
  $old = validate($_POST['O_Password']);
  $new = validate($_POST['N_Password']);
  if ($old == $new) {
    echo "<script>alert('Old Password and New Password are same')</script>";
  } else {
    $res = mysqli_query($link, "SELECT * FROM `faculty` WHERE Id_No = '$id'");
    if (mysqli_num_rows($res) == 1) {
      $row = mysqli_fetch_assoc($res);
      $opasshash = $row['Fac_Hash'];
      if (password_verify($old, $opasshash)) {
        $npasshash = password_hash($new, PASSWORD_DEFAULT);
        if (mysqli_query($link, "UPDATE faculty SET Fac_Hash = '$npasshash', Password = '$new' WHERE Id_No = '$id'")) {
          echo "<script>alert('Password Updated Successfully')</script>";
        } else {
          echo "<script>alert('Password Updation Failed due to Internal Error')</script>";
        }
      } else {
        echo "<script>alert('Incorrect Old Password');</script>";
      }
    } else {
      echo "<script>alert('Username Incorrect');</script>";
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
  <link rel="stylesheet" href="../css/sidebar-style.css" />
  <link rel="stylesheet" href="../css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
  <!-- Boxiocns CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
  body {
    background: #E4E9F7;
  }

  #sign-out {
    display: none;
  }

  @media screen and (max-width:920px) {
    #sign-out {
      display: block;
    }

    .wrapper {
      width: 320px;
      margin-left: 50px;
    }
  }

  #hide {
    position: absolute;
    margin: 1% 21%;
    font-size: 25px;
    cursor: pointer;
    z-index: 1;
    color: #1abc9c;
  }

  @media screen and (max-width:920px) {
    #hide {
      position: absolute;
      text-align: right;
      margin: 2% 67%;
      font-size: 20px;
    }
  }
</style>

<body>
  <?php include 'sidebar.php'; ?>
  <div class="container">
    <div class="wrapper">
      <div class="title"><span>Change Password</span></div>
      <form action="" method="post" autocomplete="off">
        <div class="row">
          <i class="fas fa-user"></i>
          <input type="text" placeholder="User Name" name="UserName" value="<?php if (isset($id)) {
                                                                              echo $id;
                                                                            } else {
                                                                              echo $_SESSION['Id_No'];
                                                                            } ?>" oninput="this.value = this.value.toUpperCase()" readonly required>
        </div>
        <span class="fas fa-eye" id="hide"></span>
        <div class="row">
          <i class="fas fa-lock" id="old_togglePassword"></i>
          <input type="password" id="old_password" placeholder="Old Password" value="<?php if (isset($old)) {
                                                                                        echo $old;
                                                                                      } else {
                                                                                        echo "";
                                                                                      } ?>" name="O_Password" required>
        </div>
        <div class="row">
          <i class="fas fa-lock" id="new_togglePassword"></i>
          <input type="password" id="new_password" placeholder="New Password" value="<?php if (isset($new)) {
                                                                                        echo $new;
                                                                                      } else {
                                                                                        echo "";
                                                                                      } ?>" name="N_Password" required>
        </div>
        <div class="pass"><a href="forgot_password.php">Forgot password?</a></div>
        <div class="row button">
          <div class="btn-wrapper <?php echo !can('update', MENU_ID) ? 'rbac-disabled' : ''; ?>"
            <?php if (!can('update', MENU_ID)) { ?>
            title="You don't have permission to update student/faculty password"
            <?php } ?>>
            <input type="submit" name="change" class="btn" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Show/Hide Password -->
  <script type="text/javascript">
    $('#hide').on('click', function() {
      $(this).toggleClass('fa-eye');
      $(this).toggleClass('fa-eye-slash');
      if ($(this).hasClass('fa-eye-slash')) {
        $('#old_password').attr('type', 'text')
        $('#new_password').attr('type', 'text')
      } else {
        $('#old_password').attr('type', 'password')
        $('#new_password').attr('type', 'password')
      }
    });
  </script>
</body>

</html>