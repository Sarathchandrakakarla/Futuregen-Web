<?php
session_start();
if (!$_SESSION['Id_No']) {
  echo "<script>
  alert('Faculty Id Not Rendered');
  location.replace('faculty_login.php');
  </script>
  </script>";
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="../css/sidebar-style.css" />
  <!-- Controlling Cache -->
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />

  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine" />
  <!-- Boxiocns CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
  body {
    background: linear-gradient(120deg, #136a8a, #267871);
  }

  #sign-out {
    display: none;
  }

  @media screen and (max-width:920px) {
    #sign-out {
      display: block;
    }
  }

  .img-container {
    display: grid;
    margin-top: 50px;
    place-items: center;
  }

  /* TypeWriter Effect */
  .type-container {
    display: flex;
    margin-top: 100px;
  }

  #vic_heading {
    margin-left: 500px;
    display: flex;
    font-size: 3em;
    color: yellow;
  }

  .typing-demo {
    width: 43ch;
    animation: typing 2s steps(42), blink .5s step-end infinite alternate;
    white-space: nowrap;
    overflow: hidden;
    border-right: 3px solid;
    font-family: "Times New Roman", serif;
    font-weight: bold;
    /*font-family: 'Times New Roman';*/
    font-size: 2em;
    color: yellow;
  }

  @keyframes typing {
    from {
      width: 0
    }
  }

  @keyframes blink {
    50% {
      border-color: transparent
    }
  }

  @media screen and (max-width:960px) {
    .type-container {
      display: flex;
      margin-top: 100px;
    }

    #vic_heading {
      margin-left: 80px;
      display: flex;
      font-size: 1em;
    }

    .typing-demo {
      font-size: 1em;
    }
  }
</style>

<body>
  <?php include 'sidebar.php'; ?>

  <div class="container img-container" data-aos="fade-in">
    <img src="/Futuregen/Images/Victory Logo.png" alt="Logo" width="200px">
  </div>
  <div class="container type-container">
    <div class="head">
      <p id="vic_heading"><b style="padding-right: 30px;font-family: 'algerian';">Futuregen</b></p>
    </div>
    <div class="wrapper">
      <div class="typing-demo">
        A Place Where Future Talented Minds Assemble...
      </div>
      <div class="typing-demo">
        A Place Which Brings Glory To Your Skill...
      </div>
    </div>
  </div>

</body>

</html>