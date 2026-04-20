<?php
session_start();
if (!isset($_SESSION['school_db'])) {
  header('Location: /Victory/Welcome/preindex.php');
  exit;
}
?>
<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link
    rel="shortcut icon"
    href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico"
    type="image/x-icon" />
  <!-- Controlling Cache -->
  <meta
    http-equiv="Cache-Control"
    content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <!-- Links for Header -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <!-- Bootstrap Links -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js"></script>
</head>

<style>
  /*Header */
  /* Google Fonts Import Link */
  @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  body {
    background-image: linear-gradient(to top, #cfd9df 0%, #e2ebf0 100%);
    display: flex;
    flex-direction: column;
  }

  /* Header */
  nav {
    display: flex;
    height: 80px;
    width: 100%;
    /* background: #1b1b1b; */
    background-image: linear-gradient(to top, #48c6ef 0%, #6f86d6 100%);
    align-items: center;
    justify-content: space-evenly;
    flex-wrap: wrap;
  }

  nav .heading {
    color: #fff;
    font-size: large;
  }

  nav ul {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
  }

  nav ul li {
    margin: 0 5px;
    position: relative;
  }

  nav ul li a {
    color: #f2f2f2;
    text-decoration: none;
    font-size: 18px;
    font-weight: 500;
    padding: 8px 15px;
    border-radius: 5px;
    letter-spacing: 1px;
    transition: all 0.3s ease;
  }

  nav ul li a.active,
  nav ul li a:hover {
    color: #111;
    background: #fff;
    text-decoration: none;
  }

  nav .menu-btn i {
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    display: none;
  }

  input[type="checkbox"] {
    display: none;
  }

  /* New */
  nav ul li .sub-menu {
    width: max-content;
    position: absolute;
    top: 35px;
    left: 0;
    /* background: #1b1b1b; */
    background-image: linear-gradient(to top, #48c6ef 0%, #6f86d6 100%);
    padding: 0 0 10px 0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    border-radius: 0 0 4px 4px;
    display: none;
    z-index: 2;
  }

  nav ul li:hover .login-sub-menu {
    display: block;
  }

  ul li .sub-menu li {
    padding: 10px 0 0 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  }

  ul li .sub-menu a {
    color: #fff;
    font-size: 15px;
    font-weight: 500;
  }

  @media (max-width: 920px) {
    nav .logo {
      font-size: 20px;
    }

    nav .logo img {
      width: 50px;
    }

    nav .menu-btn i {
      display: block;
    }

    nav ul {
      position: fixed;
      top: 80px;
      left: -100%;
      background: #111;
      height: 100vh;
      width: 100%;
      text-align: center;
      display: block;
      transition: all 0.3s ease;
      z-index: 20;
      overflow-y: auto;
    }

    #click:checked~ul {
      left: 0;
    }

    nav ul li {
      width: 100%;
      margin: 20px 0;
    }

    nav ul li a {
      width: 100%;
      margin-left: -100%;
      display: block;
      font-size: 20px;
      transition: 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    #click:checked~ul li a {
      margin-left: 0px;
    }

    nav ul li a.active,
    nav ul li a:hover {
      background: none;
      color: cyan;
    }

    nav ul li .sub-menu {
      left: 80px;
      height: 300px;
    }

    ul li .sub-menu li {
      padding: 0 100px 0 0;
      margin-left: 30px;
    }
  }

  #gallery_heading {
    margin-top: 2%;
    font-family: "Times New Roman", Times, serif;
    font-size: 35px;
  }

  /* The grid: Four equal columns that floats next to each other */
  .column {
    float: left;
    width: 33.33%;
    padding: 10px;
  }

  /* Style the images inside the grid */
  .column img {
    cursor: pointer;
    border-radius: 8px;
  }

  /* Clear floats after the columns */
  .row:after {
    content: "";
    display: table;
    clear: both;
  }

  @media screen and (max-width: 650px) {
    .column {
      width: 100%;
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
      bottom: -270%;
    }
  }

  @media (max-width: 700px) {
    footer {
      background: #111;
      bottom: -705%;
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
  <!-- Header -->
  <nav>
    <div class="logo">
      <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png" alt="..." width="70px" />
    </div>
    <div class="heading">
      <h3 style="<?php if($_SESSION['school_db']['school_code'] == 'FGS') echo 'font-size: medium;'; ?>"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h3>
    </div>
    <input type="checkbox" id="click" />
    <label for="click" class="menu-btn">
      <i class="fas fa-bars"></i>
    </label>
    <ul>
      <li><a href="/Victory/index.php">Home</a></li>
      <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
      <li><a class="active" href="/Victory/Gallery/gallery.php">Gallery</a></li>
      <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/contact.php">Contact</a></li>
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
  <div style="text-align: center">
    <h2 id="gallery_heading"><?= (isset($_SESSION['school_db']) && $_SESSION['school_db']['school_code'] == "VHS") ? 'VICTORY' : 'FUTUREGEN'; ?> EVENTS</h2>
  </div>

  <!-- The four columns -->
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event1.jpg"
        alt="Event1"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event2.jpg"
        alt="Event2"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event3.jpg"
        alt="Event3"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event4.jpg"
        alt="Event4"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event5.jpg"
        alt="Event5"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event6.jpg"
        alt="Event6"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event7.jpg"
        alt="Event7"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event8.jpg"
        alt="Event8"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event9.jpg"
        alt="Event9"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event10.jpg"
        alt="Event10"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event11.jpg"
        alt="Event11"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event12.jpg"
        alt="Event12"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event13.jpg"
        alt="Event13"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event14.jpg"
        alt="Event14"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event15.jpg"
        alt="Event15"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event16.jpg"
        alt="Event16"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event17.jpg"
        alt="Event17"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event18.jpg"
        alt="Event18"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
  </div>
  <div class="row">
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event19.jpg"
        alt="Event19"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event20.jpg"
        alt="Event20"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
    <div class="column">
      <img
        src="<?= $_SESSION['school_db']['Root_Dir'] ?>/gallery/Images/event21.jpg"
        alt="Event21"
        style="width: 100%"
        onclick="myFunction(this);"
        loading="lazy" />
    </div>
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

  <!-- Image Zoom -->
  <script type="text/javascript">
    function myFunction(imgs) {
      Swal.fire({
        imageUrl: imgs.src,
        imageWidth: "80%",
        imageHeight: "80%",
        imageAlt: "Custom image",
        width: "70%",
        showCloseButton: false,
        showConfirmButton: false,
      });
    }
  </script>
</body>

</html>