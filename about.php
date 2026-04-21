<?php
session_start();
if (!isset($_SESSION['school_db'])) {
  header('Location: /Futuregen/Welcome/preindex.php');
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <!-- Animation Links -->
  <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
  <!-- Font Links -->
  <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital@0;1&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-color: #2c3e50;
      --secondary-color: #3498db;
      --accent-color: #e74c3c;
      --light-bg: #f8f9fa;
      --white: #ffffff;
      --dark-text: #2d3748;
      --gray-text: #64748b;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      --shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.12);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      line-height: 1.6;
      color: var(--dark-text);
      overflow-x: hidden;
      background: #f0f4f8;
    }

    /* Hero Section */
    .hero-section {
      position: relative;
      height: 70vh;
      min-height: 500px;
      display: flex;
      align-items: center;
      overflow: hidden;
    }

    #main_img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      filter: brightness(0.7) contrast(1.1);
      position: absolute;
      top: 0;
      left: 0;
      z-index: -2;
    }

    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(255, 152, 0, 0.50), rgba(255, 235, 59, 0.45));
      z-index: -1;
    }

    #about_heading {
      position: relative;
      text-align: center;
      font-family: 'Libre Baskerville', serif;
      font-size: clamp(2.5rem, 6vw, 4rem);
      font-weight: 400;
      color: var(--white);
      text-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
      letter-spacing: 1px;
      margin-bottom: 1rem;
    }

    /* Content Sections */
    .head_content {
      padding: 2rem 0;
      max-width: 1200px;
      margin: 0 auto;
    }

    #school_head {
      margin: 2rem 0 1.5rem;
      text-align: center;
      font-family: 'Libre Baskerville', serif;
      font-size: clamp(1.5rem, 3vw, 2.5rem);
      font-weight: 600;
      color: var(--primary-color);
      position: relative;
    }

    #school_head::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 3px;
      background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
      border-radius: 2px;
    }

    #school_img {
      border-radius: 15px;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      width: 90%;
      height: 20%;
      object-fit: cover;
    }

    #school_img:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    #about_school {
      font-size: 1.1rem;
      line-height: 1.8;
      color: var(--gray-text);
      text-align: justify;
      margin: 1.5rem 0;
      font-weight: 400;
    }

    /* Management Cards - SIZE REDUCED */
    .card {
      border: none;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      background: #ffffff;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12);
    }

    .management_img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s ease;
    }

    .card:hover .management_img {
      transform: scale(1.02);
    }

    .card-body {
      padding: 1rem;
      text-align: center;
      flex: 1;
    }

    .card-title h4 {
      color: #2c3e50;
      font-weight: 700;
      font-size: 1rem;
      margin-bottom: 0.2rem;
    }

    .card-title h5 {
      color: #3498db;
      font-weight: 600;
      font-size: 0.95rem;
      margin: 0;
    }

    /* Mission Vision Section */
    .mission-vision {
      background: var(--white);
      padding: 2.5rem 0;
      border-radius: 15px;
      margin: 2rem 0;
      box-shadow: var(--shadow);
    }

    .mission-vision h4 {
      color: var(--secondary-color);
      font-size: 1.5rem;
      margin: 1.5rem 0 1rem;
      font-weight: 600;
    }

    .mission-vision h5 {
      color: var(--primary-color);
      font-size: 1.2rem;
      margin: 1.5rem 0 1rem;
      display: flex;
      align-items: center;
      gap: 0.6rem;
    }

    /* Feature Sections */
    .features-section {
      background: linear-gradient(135deg, var(--light-bg) 0%, #eef2f7 100%);
      padding: 2.5rem 0;
      border-radius: 15px;
      margin: 2rem 0;
    }

    .feature-item {
      background: var(--white);
      padding: 1.8rem;
      border-radius: 12px;
      margin-bottom: 1.5rem;
      box-shadow: var(--shadow);
      transition: all 0.3s ease;
      border-left: 4px solid var(--secondary-color);
    }

    .feature-item:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-hover);
    }

    /* Responsive Design */
    @media (max-width: 992px) {
      #main_img {
        min-height: 200px;
      }

      .hero-section {
        height: 20vh;
        min-height: 200px;
      }

      #about_heading {
        font-size: 2.5rem;
      }

      .management-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }

      .card {
        max-height: 400px;
      }
    }

    @media (max-width: 768px) {
      .head_content {
        padding: 1.5rem 1rem;
      }

      #school_head {
        font-size: 1.8rem;
      }

      #main_img {
        height: 200px;
      }

      #about_school {
        font-size: 1rem;
      }

      .feature-item {
        padding: 1.5rem 1.2rem;
      }

      .management-grid {
        gap: 1rem;
      }

      .card {
        width: 18rem !important;
        height: 25rem !important;
        margin: 0 auto;
      }
    }

    /* Compact Footer */
    footer {
      background: linear-gradient(135deg, var(--primary-color) 0%, #1a252f 100%);
      color: var(--white);
      padding: 1.5rem 0 1rem;
      margin-top: 3rem;
    }

    .footer-bottom {
      padding: 1rem 0;
      text-align: center;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      margin-top: 1rem;
      font-size: 0.9rem;
    }

    .footer-bottom p {
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }

    .company-tag {
      font-size: 0.95rem;
      font-weight: 500;
    }

    .footer-bottom a {
      color: var(--secondary-color);
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }

    .footer-bottom a:hover {
      color: #5dade2;
    }

    /* Navigation fixes */
    nav ul li a.active {
      color: var(--secondary-color) !important;
      font-weight: 600;
    }

    /* Smooth scrolling */
    html {
      scroll-behavior: smooth;
    }

    /* AOS Animation tweaks */
    [data-aos] {
      transition-duration: 0.8s;
    }
  </style>
</head>

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
      <li><a href="/Futuregen/index.php">Home</a></li>
      <li><a class="active" href="<?= $_SESSION['school_db']['Root_Dir'] ?>/about.php">About</a></li>
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

  <!-- Hero Section -->
  <section class="hero-section">
    <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/school1.jpg" id="main_img" alt="..." />
    <div class="hero-overlay"></div>
    <div class="container">
      <h2 id="about_heading" data-aos="fade-up">About Us</h2>
    </div>
  </section>

  <div class="container head_content">
    <!-- School Introduction -->
    <h1 id="school_head" data-aos="fade-up">
      <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">"Futuregen"</span> The School for Empowering Young Minds for a Brighter Future
    </h1>

    <div class="container content" data-aos="fade-up">
      <div class="row g-4 align-items-center">
        <div class="col-lg-6">
          <picture class="img-fluid">
            <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/building.jpg" id="school_img" alt="..." />
          </picture>
        </div>
        <div class="col-lg-6">
          <p id="about_school">
            Victory's Futuregen EM High School is an initiative of Victory Schools, carrying forward a legacy of excellence in education. We are committed to providing a perfect blend of academics, technology, and values to prepare students for a rapidly changing world.
          </p>
        </div>
      </div>
    </div>

    <!-- Management Section -->
    <h1 id="school_head" data-aos="fade-up">
      The <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">"Futuregen"</span> Management
    </h1>

    <div class="container" align="center">
      <picture class="img-fluid">
        <div class="row management-grid">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="card" style="width: 19rem; height: 27rem">
              <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Principal.jpg" class="card-img-top management_img" alt="..." />
              <div class="card-body">
                <h4 class="card-title"><strong>Principal</strong></h4>
                <h5 class="card-title">K.Ramakrishna Reddy</h5>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left">
            <div class="card correspondent" style="width: 19rem; height: 27rem">
              <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/correspondent.jpg" class="card-img-top management_img" alt="..." />
              <div class="card-body" id="corres-body">
                <h4 class="card-title"><strong>Correspondent</strong></h4>
                <h5 class="card-title">A.Narasimha Reddy</h5>
              </div>
            </div>
          </div>
        </div>
      </picture>
    </div>

    <div class="container about_management" data-aos="fade-up">
      <p id="about_school">
        Victory's Futuregen EM High School stands as a testament to visionary leadership under the guidance of Principal K. Ramakrishna Reddy and Correspondent A. Narasimha Reddy. Their unwavering commitment to excellence in education continues to shape an institution that nurtures both academic achievement and holistic development.<br><br>

        With their combined expertise, experience, and dedication, they foster a dynamic and inspiring learning environment where students are encouraged to explore their potential, develop essential life skills, and grow into confident, future-ready individuals. Their leadership not only motivates our students but also empowers our faculty to deliver meaningful and impactful education.<br><br>

        At Victory's Futuregen EM High School, we are proud to cultivate a culture of innovation, integrity, and excellence—preparing our learners to succeed in an ever-evolving world.<br><br>

        Join us and become a part of an inspiring educational journey toward a brighter future.
      </p>
    </div>

    <!-- Mission and Vision -->
    <div class="mission-vision" data-aos="zoom-in">
      <h1 id="school_head" style="text-align: center; margin-bottom: 1.5rem;">
        Our <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">" Futuregen's "</span> Mission and Vision
      </h1>
      <div class="container">
        <p id="about_school">
        <h4>Our Vision</h4>
        To nurture confident, responsible, and innovative global citizens who are equipped with the knowledge, skills, and values needed to thrive in an ever-changing world. We aspire to create lifelong learners who think independently, act ethically, and contribute meaningfully to society.<br><br>
        <h4>Our Mission</h4><br>
        <h5>🎓Deliver High-Quality Education</h5>
        We are committed to providing a strong academic foundation through the advanced curriculum, enriched with modern teaching methodologies, technology integration, and a focus on conceptual understanding that prepares students for academic excellence and future challenges.<br><br>
        <h5>🧠Promote Creativity and Critical Thinking</h5>
        We encourage students to explore ideas, question concepts, and think beyond textbooks. Through interactive learning, problem-solving activities, and innovative practices, we foster creativity, curiosity, and analytical thinking.<br><br>
        <h5>🌟Ensure Holistic Student Development</h5>
        Our approach goes beyond academics to nurture the physical, emotional, social, and intellectual growth of every student. We provide opportunities in sports, arts, leadership, and life skills to help students become well-rounded individuals.<br><br>
        <h5>🕊️Instill Discipline and Strong Moral Values</h5>
        We emphasize character building by instilling discipline, integrity, respect, and empathy. Our goal is to shape responsible individuals who uphold strong moral values and contribute positively to their communities.
        </p>
      </div>
    </div>

    <!-- Why Parents Trust Us -->
    <h1 id="school_head" data-aos="fade-up">Why Parents Trust Us</h1>
    <div class="features-section" data-aos="fade-up">
      <div class="container">
        <p id="about_school" style="margin-bottom: 1.5rem;">
          At Victory's Futuregen EM High School, we understand that choosing the right school is one of the most important decisions for every parent. Our commitment to excellence, safety, and holistic development has earned the trust and confidence of families who seek the very best for their children.
        </p>

        <div class="row g-4">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">👩‍🏫Experienced & Caring Faculty</h4>
              <p>Our team of dedicated educators brings a wealth of knowledge, experience, and passion for teaching. Beyond academics, they serve as mentors and guides, ensuring that every child receives personal attention, encouragement, and the support needed to thrive.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">💻Smart Classrooms with Digital Learning</h4>
              <p>We integrate modern technology into our teaching practices through well-equipped smart classrooms. Interactive digital tools make learning engaging, effective, and aligned with the needs of today's learners, enhancing understanding and retention.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-right">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">🗣️Focus on Communication & Personality Development</h4>
              <p>We place strong emphasis on developing confident communicators and well-rounded individuals. Through regular activities, presentations, and interactive sessions, students build essential life skills such as public speaking, teamwork, leadership, and self-expression.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">🛡️Safe and Secure Campus</h4>
              <p>The safety and well-being of our students are our top priorities. Our campus is designed with robust safety measures, continuous supervision, and a nurturing environment where children feel secure, respected, and cared for at all times.</p>
            </div>
          </div>
          <div class="col-12" data-aos="fade-up">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">📚Strong Academic Foundation</h4>
              <p>We focus on building a solid academic base through a structured curriculum, conceptual clarity, and consistent assessment. Our approach ensures that students not only excel in examinations but also develop a deep understanding of subjects that prepares them for future success.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Facilities Section -->
    <h1 id="school_head" data-aos="fade-up">
      Our <span style="font-family: 'Libre Baskerville', serif;font-style:italic;">" Futuregen's "</span> Facilities
    </h1>
    <div class="features-section" data-aos="fade-up">
      <div class="container">
        <p id="about_school" style="margin-bottom: 1.5rem;">
          At Victory's Futuregen EM High School, our campus is thoughtfully designed to create a stimulating, safe, and inspiring environment where students can explore, learn, and grow with confidence. Every aspect of our infrastructure reflects our commitment to providing a balanced blend of comfort, innovation, and functionality.
        </p>

        <div class="row g-4">
          <div class="col-lg-6" data-aos="fade-right">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">💻Modern, Well-Equipped Digitalized Classrooms</h4>
              <p>Our classrooms are equipped with advanced digital learning tools that make lessons interactive, engaging, and effective. Technology-enabled teaching enhances understanding, encourages participation, and prepares students for a digitally driven world.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">🏠A/C Residential Campus</h4>
              <p>We offer a comfortable, fully air-conditioned residential facility that provides a home-like atmosphere for our students. With well-maintained living spaces and attentive supervision, we ensure a safe, secure, and nurturing environment for all boarders.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-right">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">⚽Spacious Sports and Play Areas</h4>
              <p>We believe that physical activity is essential for overall development. Our campus features expansive sports grounds and play areas where students can participate in a variety of indoor and outdoor games, promoting fitness, teamwork, and sportsmanship.</p>
            </div>
          </div>
          <div class="col-lg-6" data-aos="fade-left">
            <div class="feature-item">
              <h4 style="font-family: 'Times New Roman', Times, serif;font-weight:bold;">🍎Nutritious & Hygienic Meals</h4>
              <p>Students are provided with well-balanced, nutritious meals including breakfast, lunch, and evening snacks. All food is prepared under strict hygiene standards, ensuring both quality and health, while supporting the energy and well-being of growing children.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="container">
      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?>, <a href="/"><?= (isset($_SESSION['school_db']) && isset($_SESSION['school_db']['footer_msg'])) ? $_SESSION['school_db']['footer_msg'] : ''; ?></a>. All Rights Reserved.</p>
        <p class="company-tag">Developed and Maintained by <u><a href="https://sarathtechgenics.netlify.app" target="_blank">Sarath Techgenics</a></u></p>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
  <script>
    AOS.init({
      duration: 800,
      once: true,
      offset: 80,
    });

    var d = new Date();
    if (document.getElementById("year")) {
      document.getElementById("year").innerHTML = d.getFullYear();
    }
  </script>
</body>

</html>