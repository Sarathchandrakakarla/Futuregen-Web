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
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon" />
  <link rel="stylesheet" href="css/header.css" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <!-- Animation Links -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <!-- Font Links -->
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital@0;1&display=swap" rel="stylesheet">
</head>

<style>
  body {
    height: 100%;
    background: #e4e9f7;
    display: flex;
    flex-direction: column;
  }

  #main_img {
    width: 100%;
    height: 500px;
    opacity: 0.6;
    position: absolute;
  }

  #about_heading {
    position: relative;
    text-align: center;
    top: 150px;
    font-family: "Times New Roman", Times, serif;
    font-size: 80px;
    opacity: 0.7;
  }

  .head_content {
    margin-top: 450px;
  }

  #school_head {
    margin-top: 15px;
    text-align: center;
    font-family: "Times New Roman";
  }

  #school_img {
    border-radius: 5px 40px 5px 40px;
  }

  #about_school {
    margin-top: 30px;
    font-family: "Times New Roman", Times, serif;
    font-size: 22px;
    text-align: justify;
  }

  .content {
    display: flex;
  }

  .card,
  .management_img {
    border-radius: 5px 40px 5px 40px;
  }

  .about_management {
    margin-top: 150px;
  }

  .main_ul {
    list-style-image: url('Images/medal2.png');
    line-height: 35px;
  }

  ul .main_li {
    margin-left: 100px;
    font-weight: bold;
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

  @media screen and (max-width: 920px) {
    #main_img {
      height: 200px;
    }

    #about_heading {
      left: 0;
      top: 50px;
    }

    .head_content {
      margin-top: 130px;
    }

    #school_head {
      font-size: x-large;
    }

    .content {
      display: block;
    }

    #school_img {
      width: 330px;
    }

    .correspondent {
      margin-top: 130px;
    }

    ul .main_li {
      margin-left: -20px;
      font-size: 10px;
    }
  }

  /* Footer */
  footer {
    /* position: absolute; */
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

  @media (min-width:500px) {
    footer {
      bottom: -400%;
    }
  }

  @media (max-width:530px) {
    footer {
      background: #111;
      bottom: -970% !important;
      width: 100vw;
    }

    .footer-bottom {
      width: 100vw;
    }
  }

  @media (max-width:770px) {
    footer {
      background: #111;
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

  @media (min-width:500px) {
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
      <li><a class="active" href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
      <li><a href="/Victory/Gallery/gallery.php">Gallery</a></li>
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
  <picture class="img-fluid">
    <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/school1.jpg" id="main_img" alt="..." />
    <h2 id="about_heading">About Us</h2>
  </picture>
  <div class="container head_content" data-aos="fade-up">
    <h1 id="school_head">
      <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">"Victory"</span> The School To
      Create
      <span style="font-family: algerian">Talented & Skillful</span> Minds
    </h1>
    <div class="container content" data-aos="fade-up">
      <div class="container">
        <picture class="img-fluid">
          <img
            src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/building.jpg"
            id="school_img"
            alt="..."
            width="600px" />
        </picture>
      </div>
      <div class="container">
        <p id="about_school">
          The School was started in the year 1991 to cater the educational
          needs of the rural people of KODUR and the surrounding villages. To
          provide such good education, this was first school in Kodur, started
          with English medium from LKG to 10th Std with merely 87 Students in
          1991.
        </p>
      </div>
    </div>
    <h1 id="school_head">
      The <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">"Victory"</span> Management
    </h1>
    <div class="container" align="center">
      <picture class="img-fluid">
        <div class="row">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="card" style="width: 19rem; height: 17rem">
              <img
                src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Principal.jpg"
                class="card-img-top management_img"
                alt="..." />
              <div class="card-body">
                <h4 class="card-title"><strong>Principal</strong></h4>
                <h5 class="card-title">K.Ramakrishna Reddy</h5>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left">
            <div class="card correspondent" style="width: 19rem; height: 17rem">
              <img
                src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/correspondent.jpg"
                class="card-img-top management_img"
                alt="..." />
              <div class="card-body" id="corres-body">
                <h4 class="card-title"><strong>Correspondent</strong></h4>
                <h5 class="card-title">A.Narasimha Reddy</h5>
              </div>
            </div>
          </div>
        </div>
      </picture>
    </div>
    <div class="container about_management">
      <p id="about_school">
        Victory High School is proudly guided by the visionary leadership of Principal K. Ramakrishna Reddy and Correspondent A. Narasimha Reddy. With their unwavering commitment
        to education, they ensure that our institution remains a beacon of academic excellence and holistic development. Their combined expertise and dedication inspire our students
        and faculty alike, creating a dynamic and enriching learning environment that prepares our students for a bright future. Join us at Victory High School and be a part of this
        inspiring educational journey.
      </p>
    </div>
    <h1 id="school_head">
      Our <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">" Victory's "</span> Mission and
      Vision
    </h1>
    <div class="container" data-aos="zoom-out">
      <p id="about_school">
        &nbsp;&nbsp;&nbsp;&nbsp; The development of the society and
        self-reliance of an individual can only be possible with education by
        which character is formed, strength of mind is increased, the
        intellect is expanded and by which one can stand on one's own feet.
        <br /><br />

        &nbsp;&nbsp;&nbsp;&nbsp;Since the day of its inception, we have been
        trying to our levels best to bring all new technology to these rural
        students like implementing of innovative programmes such as IIT
        Foundation Course, Vedic Mathematics, Abacus etc to enable the
        students, face the new competitive challenges in higher studies. We
        try to provide scientific approach to the students by maintaining
        fully equipped science lab, LCD projector, Audio visual room, Computer
        lab and so on... <br /><br />

        &nbsp;&nbsp;&nbsp;&nbsp;This further resulted to make many of our
        students to become Doctors, Engineers, Software technocrats, Govt.
        employees in various departments.
      </p>
    </div>
    <h1 id="school_head">
      Our <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">" Victory's "</span> Academic
      Progress
    </h1>
    <div class="container" data-aos="fade-up">
      <p id="about_school">
        &nbsp;&nbsp;&nbsp;&nbsp;Since the year of its inception the school has been getting excellent results in both 10th class and 7th class public examinations.
        <br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;A great achievement our school got is: In SSC 2014, SSC2015, SSC2016 our students received PRATIBHA PURASKAR by Honourable Chief minister.
        <br><br>
        &nbsp;&nbsp;&nbsp;&nbsp;We feel, a little bit proud to announce that it has become a customary to our school to have admissions into the most prestigious institution in AP, the IIIT (triple IT) every year since its establishment.
        <br><br>
        Victory school students have been on the top position in Kodur town since 2006 for <b>12 consecutive years</b> getting Town 1st
      <div class="full-container" style="display: flex;">
        <div class="container">
          <ul class="main_ul">
            <li class="main_li">SSC 2006 552/600 &nbsp; KODUR TOWN 1st</li>
            <li class="main_li">SSC 2007 567/600 &nbsp; KODUR TOWN 1st</li>
            <li class="main_li">SSC 2008 569/600 &nbsp; DISTRICT 1st</li>
            <li class="main_li">SSC 2009 569/600 &nbsp; KODUR TOWN 1st</li>
            <li class="main_li">SSC 2010 565/600 &nbsp; KODUR TOWN 1st</li>
            <li class="main_li">SSC 2011 567/600 &nbsp; KODUR TOWN 1st</li>
            <li class="main_li">SSC 2012 9.8/10 GPA &nbsp; <br> 2 Students got KODUR TOWN 1st</li>
            <li class="main_li">SSC 2013 STATE GRADE
              <ul>
                <li>10/10 GPA</li>
                <li>9.8/10 - 7 students</li>
              </ul>
            </li>
            <li class="main_li">SSC 2014
              <ul>
                <li>10/10 GPA - 3 students</li>
                <li>9.8/10 - 9 students</li>
              </ul>
            </li>
            <li class="main_li">SSC 2015
              <ul>
                <li>10/10 GPA - 4 students</li>
                <li>9.8/10 - 4 students</li>
              </ul>
            </li>
          </ul>
        </div>
        <div class="container">
          <ul class="main_ul">
            <li class="main_li">SSC 2016
              <ul>
                <li>10/10 GPA</li>
                <li>9.8/10 - 13 students</li>
              </ul>
            </li>
            <li class="main_li">SSC 2017
              <ul>
                <li>10/10 GPA - 3 students</li>
                <li>9.8/10 - 5 students</li>
                <li>9.7/10 - 8 students</li>
              </ul>
            </li>
            <li class="main_li">SSC 2018
              <ul>
                <li>10/10 GPA - 5 students</li>
                <li>9.8/10 - 8 students</li>
                <li>9.7/10 - 12 students</li>
              </ul>
            </li>
            <li class="main_li">SSC 2019
              <ul>
                <li>10/10 GPA - 9 students</li>
                <li>9.8/10 - 9 students</li>
                <li>9.7/10 - 12 students</li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
      </p>
    </div>
    <h1 id="school_head">
      Our <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">" Victory's "</span> Co-Curricular Activities
    </h1>
    <div class="container" data-aos="fade-in">
      <p id="about_school">
        Besides the curricular activities we give much importance to all co-curricular and extra-curricular activities like Sports and games, dance social activities and cultural activities.
        We try to instill moral and traditional values in children and drive them towards responsibility and respect in the society. For this to happen we celebrate all festivals in harmony with all religious people.

      </p>
      <!-- The four columns -->
      <div class="row">
        <div class="column">
          <img
            src="Gallery/Images/event1.jpg"
            alt="Event1"
            style="width: 100%"
            onclick="myFunction(this);"
            loading="lazy" />
        </div>
        <div class="column">
          <img
            src="Gallery/Images/event2.jpg"
            alt="Event2"
            style="width: 100%"
            onclick="myFunction(this);"
            loading="lazy" />
        </div>
        <div class="column">
          <img
            src="Gallery/Images/event3.jpg"
            alt="Event3"
            style="width: 100%"
            onclick="myFunction(this);"
            loading="lazy" />
        </div>
      </div>
    </div>
  </div>
  <footer>
    <div class="footer-bottom">
      <p>&copy; <?php echo date('Y'); ?>, <a href="/"> <?= (isset($_SESSION['school_db']) && isset($_SESSION['school_db']['footer_msg'])) ? $_SESSION['school_db']['footer_msg'] : ''; ?> </a>. All Rights Reserved. </p>
      <p class="company-tag">Developed and Maintained by <u><a href="https://sarathtechgenics.netlify.app" target="_blank">Sarath Techgenics</a></u></p>
    </div>
  </footer>

  <!-- Scripts -->

  <!-- Animation Scripts-->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 2000,
      once: true,
    });
  </script>
</body>

</html>