<?php
session_start();
if (!isset($_SESSION['school_db'])) {
  header('Location: /Futuregen/Welcome/preindex.php');
  exit;
}

$documents = [
  "Government Approvals" => [
    [
      "title" => "School Permission Proceedings",
      "front" => "/Futuregen/Images/approvals/Permission1.jpg",
      "back" => "/Futuregen/Images/approvals/Permission2.jpg"
    ],
  ],
];
?>

<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
  <!-- Controlling Cache -->
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
  <meta http-equiv="Pragma" content="no-cache" />
  <meta http-equiv="Expires" content="0" />
  <!-- Links for Header -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />

  <!-- Links for Carousel -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<style>
  /* Google Fonts Import Link */
  @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap");

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
  }

  body {
    background-color: lightblue;
    min-height: 100vh;
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
    font-size: 15px;
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
    z-index: 20;
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

  marquee {
    padding-left: 10%;
  }

  @media (max-width: 920px) {
    marquee {
      padding-left: 0;
    }
  }

  .icon {
    animation: blink 0.3s infinite ease-in;
  }

  @keyframes blink {
    0% {
      opacity: 1;
    }

    100% {
      opacity: 0;
    }
  }

  /* Footer */
  footer {
    position: absolute;
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
      top: 110%;
    }
  }

  @media (max-width:768px) {

    footer,
    .footer-bottom {
      width: 100vw;
    }
  }

  @media (max-width:500px) {
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

  .approvals-section {
    background: #fff;
    padding: 60px 0;
    flex: 1;
  }

  .approvals-intro {
    max-width: 720px;
    margin: 0 auto 35px;
    color: #555;
    line-height: 1.7;
  }

  .approval-category {
    margin-bottom: 35px;
  }

  .approval-card {
    margin-bottom: 30px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    border: 0;
    border-radius: 12px;
    overflow: hidden;
    height: 100%;
  }

  .approval-card img {
    height: 220px;
    object-fit: cover;
    width: 100%;
  }

  .approval-card .card-body {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .approval-card .btn {
    margin-top: 8px;
  }

  .approval-modal-image {
    width: 100%;
    max-height: 75vh;
    object-fit: contain;
  }

  footer {
    position: static;
    width: 100%;
    margin-top: auto;
    padding-top: 0;
  }

  .footer-bottom {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
  }

  .footer-bottom p {
    float: none;
    margin: 0;
  }

  .company-tag {
    margin-left: 0 !important;
  }

  @media (max-width:768px) {
    .footer-bottom {
      justify-content: center;
      text-align: center;
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
      <h3 style="<?php if ($_SESSION['school_db']['school_code'] == 'FGS') echo 'font-size: medium;'; ?>"><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h3>
    </div>
    <input type="checkbox" id="click" />
    <label for="click" class="menu-btn">
      <i class="fas fa-bars"></i>
    </label>
    <ul>
      <li><a href="/Futuregen/index.php">Home</a></li>
      <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
      <li><a class="active" href="#approvals-section">Approvals & Affiliations</a></li>
      <!--<li><a href="/Futuregen/Gallery/gallery.php">Gallery</a></li>-->
      <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/contact.php">Contact</a></li>
      <!--<li><a href="/Futuregen/youtube.php" id="link">Our Stories</a></li>
      <li><a href="/Futuregen/blog/blog_index.php" id="link">Our Blog</a></li>-->
      <li>
        <a href="#">Login</a>
        <ul class="login-sub-menu sub-menu">
          <li><a href="/Futuregen/Admin/admin_login.php">Admin Login</a></li>
          <li><a href="/Futuregen/Student/student_login.php">Student Login</a></li>
          <li><a href="/Futuregen/Faculty/faculty_login.php">Faculty Login</a></li>
        </ul>
      </li>
    </ul>
  </nav>

  <section id="approvals-section" class="approvals-section">
    <div class="container">
      <div class="text-center mb-5">
        <h1 class="mb-3">Approvals & Affiliations</h1>
        <p class="approvals-intro">
          Our institution is committed to maintaining recognized standards of education and administration. Below are the key approvals and affiliation documents that reflect our compliance and academic credibility.
        </p>
      </div>

      <?php foreach ($documents as $category => $items): ?>
        <div class="approval-category">
          <h3 class="mb-4"><?= htmlspecialchars($category) ?></h3>
          <div class="row">
            <?php foreach ($items as $document): ?>
              <div class="col-12 col-sm-6 col-md-6 col-lg-3 d-flex">
                <div class="card approval-card w-100">
                  <img src="<?= htmlspecialchars($document['front']) ?>" class="card-img-top" alt="<?= htmlspecialchars($document['title']) ?>">
                  <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($document['title']) ?></h5>
                    <div class="mt-3">
                      <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-toggle="modal"
                        data-target="#approvalModal"
                        data-title="<?= htmlspecialchars($document['title']) ?>"
                        data-front="<?= htmlspecialchars($document['front']) ?>"
                        data-back="<?= htmlspecialchars(isset($document['back']) ? $document['back'] : '') ?>">
                        View
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="approvalModalLabel">Document Preview</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <div id="approvalModalToggleWrap" class="btn-group mb-3 d-none" role="group" aria-label="Document side toggle">
            <button type="button" class="btn btn-outline-primary active" id="approvalFrontBtn">Front</button>
            <button type="button" class="btn btn-outline-primary" id="approvalBackBtn">Back</button>
          </div>
          <img src="" alt="Approval Document" id="approvalModalImage" class="approval-modal-image img-fluid">
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
  <script>
    var approvalModalState = {
      front: '',
      back: ''
    };

    $('#approvalModal').on('show.bs.modal', function(event) {
      var button = $(event.relatedTarget);
      var title = button.data('title');
      var front = button.data('front');
      var back = button.data('back');
      var modal = $(this);
      var toggleWrap = modal.find('#approvalModalToggleWrap');

      approvalModalState.front = front;
      approvalModalState.back = back;

      modal.find('.modal-title').text(title);
      modal.find('#approvalModalImage').attr('src', front).attr('alt', title);
      modal.find('#approvalFrontBtn').addClass('active');
      modal.find('#approvalBackBtn').removeClass('active');

      if (back) {
        toggleWrap.removeClass('d-none');
      } else {
        toggleWrap.addClass('d-none');
      }
    });

    $('#approvalFrontBtn').on('click', function() {
      $('#approvalModalImage').attr('src', approvalModalState.front);
      $(this).addClass('active');
      $('#approvalBackBtn').removeClass('active');
    });

    $('#approvalBackBtn').on('click', function() {
      if (!approvalModalState.back) {
        return;
      }

      $('#approvalModalImage').attr('src', approvalModalState.back);
      $(this).addClass('active');
      $('#approvalFrontBtn').removeClass('active');
    });
  </script>

</body>

</html>
