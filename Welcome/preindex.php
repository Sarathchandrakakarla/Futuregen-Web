<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Futuregen/db_router.php';
$isSwitch = isset($_GET['switch']) && $_GET['switch'] == 1;
if ($isSwitch && !isset($_SESSION['switch_context'])) {
  header('Location: /Futuregen/Welcome/preindex.php');
  exit;
}

if (isset($_SESSION['school_db'])) {
  unset($_SESSION['school_db']);
}

/**
 * IMPORTANT:
 * We force CENTRAL DB for public landing
 */
$central = mysqli_connect(
  CENTRAL_DB_HOST,
  CENTRAL_DB_USER,
  CENTRAL_DB_PASS,
  CENTRAL_DB_NAME
);

$schools = [];
$q = mysqli_query(
  $central,
  "SELECT
        school_code,
        school_name,
        display_name,
        Root_Dir
     FROM school_master
     WHERE active_flag = 1
     and school_code = 'FGS'
     ORDER BY school_id"
);

while ($row = mysqli_fetch_assoc($q)) {
  $schools[] = $row;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta
    name="description"
    content="An Official Web Portal For Futuregen School, Kodur." />
  <meta name="description" content="Futuregen School in Railway Kodur, Tirupati District, Andhra Pradesh 516101 offers exceptional English medium education from Nursery to 8th grade. Modern facilities, experienced faculty, and holistic development. Admissions open for 2026-27.">
  <meta name="author" content="Futuregen School">
  <meta name="keywords" content="Futuregen School School, Railway Kodur school, English medium school Kodur, Tirupati district, Nursery to 10th admission, CBSE school Railway Kodur, best school Railway Kodur, Admissions open for 2026-27, quality education Andhra Pradesh, CBSE school, primary secondary education, pincode 516101">
  <meta name="creator" content="Futuregen School">
  <meta name="publisher" content="Futuregen School">
  <meta name="robots" content="index, follow">
  <meta property="og:description" content="Futuregen School in Railway Kodur, Tirupati District, Andhra Pradesh 516101 offers exceptional English medium education from Nursery to 8th grade. Modern facilities, experienced faculty, and holistic development.">
  <meta property="og:url" content="https://futuregenschool.com">
  <title>Futuregen School Portal</title>

  <link
    rel="shortcut icon"
    href="/Futuregen/Images/favicon.ico"
    type="image/x-icon" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css" />

  <!-- icon-link -->
  <link rel="stylesheet" href="/Futuregen/Welcome/line-awesome.min.css" />
  <!-- style-link -->
  <link rel="stylesheet" href="/Futuregen/Welcome/new_style.css" />
</head>

<body>
  <?php if ($isSwitch): ?>
    <div class="vs-switch-banner">
      🔄 You've switched schools.
      Please choose a school to continue.
    </div>
  <?php endif; ?>
  <?php if ($isSwitch): ?>
    <section class="vs-switch-login">
      <h2>Re-Login to Continue</h2>

      <form method="post" action="/Futuregen/Welcome/switch_auth.php">
        <!-- Username (read-only) -->
        <input type="text"
          value="<?= htmlspecialchars($_SESSION['switch_context']['username']) ?>"
          readonly>

        <!-- Branch selection (same parent only) -->
        <select name="school_code" required>
          <option value="">Select School</option>
          <?php foreach ($schools as $s): ?>
            <option value="<?= $s['school_code'] ?>">
              <?= htmlspecialchars($s['display_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <!-- Login type -->
        <select name="login_type" required>
          <option value="">Login As</option>
          <option value="Admin">Admin</option>
          <!-- <option value="Faculty">Faculty</option>
          <option value="Student">Student</option> -->
        </select>

        <!-- Password -->
        <input type="password" name="password" required placeholder="Password">

        <button type="submit">Continue</button>
      </form>
    </section>
  <?php endif; ?>

  <div class="vs-hero">
    <!-- background fingerprint / curves -->
    <img
      src="main-banner.svg"
      class="vs-bg-pattern"
      alt="Background pattern" />

    <!-- photo band using your existing image.jpg -->
    <div class="vs-photo-band">
      <img src="classroom-teacher.jpeg" alt="Students and classroom" />
    </div>

    <!-- logo -->
    <div class="vs-logo-wrap">
      <img
        src="/Futuregen/Images/Victory Logo.png"
        class="vs-logo"
        alt="Futuregen School"
        title="Futuregen School" />
    </div>

    <!-- text + CTA -->
    <section class="vs-content">
      <h1>Welcome to Futuregen School Portal</h1>
      <p class="vs-subtitle">
        Pioneering holistic education for young minds.
      </p>
    </section>

    <?php if (!$isSwitch && !empty($schools)): ?>
      <section class="vs-schools">
        <h2>Visit Your School</h2>

        <div class="vs-quick-links">
          <?php foreach ($schools as $s): ?>
            <form
              method="post"
              action="/Futuregen/index.php"
              class="vs-quick-card">
              <input
                type="hidden"
                name="school_code"
                value="<?= htmlspecialchars($s['school_code']) ?>" />

              <div class="vs-quick-icon">
                <i class="las la-school"></i>
              </div>

              <strong><?= htmlspecialchars($s['display_name']) ?></strong>

              <button type="submit" class="vs-btn-primary">
                Enter Portal
              </button>
            </form>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- quick links -->
    <section class="vs-quick-links">
      <div class="vs-quick-card">
        <span class="vs-quick-icon">
          <i class="las la-graduation-cap"></i>
        </span>
        <span class="vs-quick-text">Academic Excellence</span>
      </div>

      <div class="vs-quick-card">
        <span class="vs-quick-icon">
          <i class="las la-users"></i>
        </span>
        <span class="vs-quick-text">Expert Faculty</span>
      </div>

      <div class="vs-quick-card">
        <span class="vs-quick-icon">
          <i class="las la-heart"></i>
        </span>
        <span class="vs-quick-text">Student Care</span>
      </div>
    </section>

    <!-- footer -->
    <p class="vs-copyright">
      © <span id="year"></span>, Futuregen Educational Society. All rights
      reserved.
    </p>
  </div>

  <script type="text/javascript">
    document.getElementById("year").innerHTML = new Date().getFullYear();
  </script>
</body>

</html>