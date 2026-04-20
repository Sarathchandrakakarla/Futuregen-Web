<?php
session_start();
if (!isset($_SESSION['school_db'])) {
  header('Location: /Victory/Welcome/preindex.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="css/header.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <!-- Bootstrap Links -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
    crossorigin="anonymous" />

  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <!-- Boxiocns CDN Link -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link
    href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css"
    rel="stylesheet" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
</head>
<style>
  body {
    background-image: linear-gradient(to top, #cfd9df 0%, #e2ebf0 100%);
    height: 1300px;
    display: flex;
    flex-direction: column;
  }

  #body_heading {
    margin-top: 20px;
    text-align: center;
    font-family: "Algerian";
    font-size: 45px;
  }

  .container {
    margin-left: 15%;
  }

  .row {
    column-gap: 30%;
  }

  .card {
    padding-top: 5px;
    background: transparent;
    border: 0;
  }

  .card img {
    height: 18rem;
    border-radius: 50%;
  }

  #correspondent {
    height: 29rem;
    border-radius: 50%;
    padding-top: 5px;
  }

  .card-body {
    margin-top: 17px;
  }

  #corres-body {
    margin-top: 42px;
  }

  .con-2 {
    margin-top: 250px;
  }

  #reach_head {
    margin-top: 200px;
    text-align: center;
  }

  .map {
    padding-left: 15%;
  }

  @media screen and (max-width: 576px) {
    .container {
      margin-left: 3%;
    }

    .map {
      margin-left: -30px !important;
    }

    .row {
      gap: 270px;
    }

    #map_frame {
      width: 90vw;
    }
  }

  @media screen and (min-width: 1000px) {
    .map {
      margin-left: 15% !important;
    }
  }

  @media screen and (min-width: 576px) and (max-width: 990px) {
    .container {
      margin-left: 25%;
    }

    .map {
      margin-left: -30px !important;
    }

    .row {
      gap: 270px;
    }

    #map_frame {
      width: 90vw;
    }
  }

  /* Footer */
  footer {
    bottom: 0;
    left: 0;
    right: 0;
    height: auto;
    width: 98.9vw;
    padding-top: 40px;
    color: #fff;
  }

  .footer-bottom {
    background: #000;
    width: 98.9vw;
    padding: 20px;
    padding-bottom: 40px;
    text-align: center;
  }

  .footer-bottom p {
    float: left;
    font-size: 14px;
    word-spacing: 2px;
    text-transform: capitalize;
  }

  .footer-bottom p a {
    color: #44bae8;
    font-size: 16px;
    text-decoration: none;
  }

  .footer-menu {
    float: right;
  }

  .footer-menu ul {
    display: flex;
  }

  .footer-menu ul li {
    padding-right: 10px;
    display: block;
  }

  .footer-menu ul li a {
    color: #cfd2d6;
    text-decoration: none;
  }

  .footer-menu ul li a:hover {
    color: #27bcda;
  }

  @media (min-width: 500px) {
    footer {
      bottom: -155%;
    }
  }

  @media (max-width: 500px) {
    body {
      height: 1500px;
    }

    footer {
      background: #111;
      bottom: -350%;
      width: 100vw;
    }

    .footer-bottom {
      width: 100vw;
    }

    .footer-menu ul {
      display: flex;
      margin-top: 10px;
      margin-bottom: 20px;
    }
  }

  .company-tag {
    padding-top: 3px;
    float: right !important;
  }

  @media (min-width: 500px) {
    .company-tag {
      margin-left: 15%;
    }
  }
</style>

<body>
  <nav>
    <div class="logo">
      <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png" alt="..." width="70px" />
    </div>
    <div class="heading">
      <h3><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h3>
    </div>
    <input type="checkbox" id="click" />
    <label for="click" class="menu-btn">
      <i class="fas fa-bars"></i>
    </label>
    <ul>
      <li><a href="/Victory/index.php">Home</a></li>
      <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
      <li><a href="/Victory/Gallery/gallery.php">Gallery</a></li>
      <li><a class="active" href="<?= $_SESSION['school_db']['Root_Dir'] ?>/contact.php">Contact</a></li>
      <li><a href="/Victory/youtube.php" id="link">Our Stories</a></li>
      <li><a href="/Victory/blog/blog_index.php" id="link">Our Blog</a></li>
      <li>
        <a href="#">Login</a>
        <ul class="login-sub-menu sub-menu">
          <li><a href="/Victory/Admin/admin_login.php">Admin Login</a></li>
          <li><a href="/Victory/Student/student_login.php">Student Login</a></li>
          <li><a href="/Victory/Faculty/faculty_login.php">Faculty Login</a></li>
        </ul>
      </li>
    </ul>
  </nav>
  <h2 id="body_heading">Management</h2>
  <div class="container">
    <div class="row m-4">
      <div class="card" style="width: 19rem; height: 19rem">
        <img src="Images/Principal.jpg" class="card-img-top" alt="..." />
        <div class="card-body">
          <h4 class="card-title"><strong>Principal</strong></h4>
          <h5 class="card-title">Kakarla Ramakrishna Reddy</h5>
          <p class="card-text"><b>Phone:</b> 9440289081</p>
          <p class="card-text"><b>Mail:</b> kakarlavic@gmail.com</p>
        </div>
      </div>
      <div class="card" style="width: 19rem; height: 19rem">
        <img
          src="Images/correspondent.jpg"
          id="correspondent"
          class="card-img-top"
          alt="..." />
        <div class="card-body" id="corres-body">
          <h4 class="card-title"><strong>Correspondent</strong></h4>
          <h5 class="card-title">Avisana Narasimha Reddy</h5>
          <p class="card-text"><b>Phone:</b> 9885027156</p>
          <p class="card-text"><b>Mail:</b> anreddyvic@gmail.com</p>
        </div>
      </div>
    </div>
  </div>
  <h2 id="reach_head">Reach Us</h2>
  <div class="map">
    <iframe
      src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d242.2269367908367!2d79.35767652260118!3d13.941774833169124!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bb2d369fe7aeb93%3A0x14505e5d2c9d70c3!2sW9R5%2BP35%2C%20Koduru%2C%20Andhra%20Pradesh%20516101!5e0!3m2!1sen!2sin!4v1774897204465!5m2!1sen!2sin"
      width="600"
      height="450"
      style="border: 0"
      allowfullscreen=""
      loading="lazy"
      referrerpolicy="no-referrer-when-downgrade"
      id="map_frame"></iframe>
  </div>
  <footer>
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?>, <a href="/"> <?= (isset($_SESSION['school_db']) && isset($_SESSION['school_db']['footer_msg'])) ? $_SESSION['school_db']['footer_msg'] : ''; ?> </a>. All Rights Reserved. </p>
      <p class="company-tag">
        Developed and Maintained by
        <u><a href="https://sarathtechgenics.netlify.app" target="_blank">Sarath Techgenics</a></u>
      </p>
    </div>
  </footer>

  <!-- Scripts -->

  <!-- Copyright Year -->
  <script>
    var d = new Date();
    document.getElementById("year").innerHTML = d.getFullYear();
  </script>
</body>

</html>