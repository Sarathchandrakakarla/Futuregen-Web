<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 137);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
  echo "<script>alert('You don\'t have permission to view blog posts');
      location.replace('/Victory/Admin/admin_dashboard.php')</script>";
  exit;
}

error_reporting(0);
?>
<?php
date_default_timezone_set('Asia/Kolkata');

// Helper: Sanitize filename (allow alphanumeric, _, -, .)
function sanitizeFilename($filename)
{
  return preg_replace("/[^a-zA-Z0-9\-_\.]/", "", $filename);
}

// Create media folder for post or request (pass folder suffix: '' or '_request')
function createPostFolder($postId, $suffix = '')
{
  $folder = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . intval($postId) . $suffix;
  if (!is_dir($folder)) {
    mkdir($folder, 0775, true);
  }
  return $folder;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['add'])) {
    if (!can('create', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to create post');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    // ==== ADD POST REQUEST ====
    $title = mysqli_real_escape_string($link, trim($_POST['Title']));
    $desc = mysqli_real_escape_string($link, trim($_POST['Description']));
    $body = mysqli_real_escape_string($link, trim($_POST['Body']));
    $postedOn = date("Y-m-d H:i:s");

    $author = $_SESSION['Id_No'];
    $author_type = 'Faculty';

    if (!$title || !$desc || !$body || empty($_FILES['Cover_Photo']['name'])) {
      echo "<script>alert('Please fill all mandatory fields and upload cover photo.'); location.replace('manage_blog.php');</script>";
      exit;
    }

    // Insert initial request row
    $insert = mysqli_query($link, "INSERT INTO posts_requests (Title, Description, Body, Cover_Photo, Media, Links, Author, Author_Type, Posted_On) VALUES 
               ('$title', '$desc', '$body', '', '[]', '', '$author', '$author_type', '$postedOn')");
    if (!$insert) {
      echo "<script>alert('Database error on post creation.'); location.replace('manage_blog.php');</script>";
      exit;
    }
    $postId = mysqli_insert_id($link);

    // Create request media folder with suffix "_request"
    $postFolder = createPostFolder($postId, '_request');

    // Cover Photo Upload
    $coverTmp = $_FILES['Cover_Photo']['tmp_name'];
    $coverName = sanitizeFilename(basename($_FILES['Cover_Photo']['name']));
    $coverPath = $postFolder . "/cover_" . time() . "_" . $coverName;
    move_uploaded_file($coverTmp, $coverPath);
    $coverFileName = basename($coverPath);

    // Media Upload
    $mediaFiles = [];
    if (!empty($_FILES['Media']['name'][0])) {
      $count = 1;
      foreach ($_FILES['Media']['name'] as $key => $name) {
        $tmpName = $_FILES['Media']['tmp_name'][$key];
        $safeName = sanitizeFilename(basename($name));
        $targetPath = $postFolder . "/media_" . time() . "_{$count}_" . $safeName;
        if (move_uploaded_file($tmpName, $targetPath)) {
          $mediaFiles[] = basename($targetPath);
        }
        $count++;
      }
    }
    $mediaJson = json_encode($mediaFiles);

    // Links processing
    $linksArray = array_filter(array_map('trim', $_POST['Links'] ?? []));
    $linksString = $linksArray ? implode(",", $linksArray) : "";

    // Update request record with cover photo, media, links
    mysqli_query($link, "UPDATE posts_requests SET Cover_Photo='$coverFileName', Media='" . mysqli_real_escape_string($link, $mediaJson) . "', Links='" . mysqli_real_escape_string($link, $linksString) . "' WHERE Post_Id=$postId");

    echo "<script>alert('Post request submitted successfully for approval.'); location.replace('manage_blog.php');</script>";
    exit;
  }

  if (isset($_POST['update'])) {
    if (!can('update', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to update post');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    // ==== EDIT POST REQUEST ====
    $postId = intval($_POST['Post_Id']);
    $title = mysqli_real_escape_string($link, trim($_POST['Title']));
    $desc = mysqli_real_escape_string($link, trim($_POST['Description']));
    $body = mysqli_real_escape_string($link, trim($_POST['Body']));
    $type = $_POST['Type'];
    $author = $_SESSION['Id_No'];
    $author_type = 'Faculty';
    $postedOn = date("Y-m-d H:i:s");

    if (!$title || !$desc || !$body) {
      echo json_encode(['status' => 'error', 'message' => 'Title, Description and Body are mandatory.']);
      exit;
    }

    if ($type == "request") {
      // Fetch existing request owned by user
      $res = mysqli_query($link, "SELECT * FROM posts_requests WHERE Post_Id=$postId AND Author='$author'");
      $post = mysqli_fetch_assoc($res);
      if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Request not found or not owned by you.']);
        exit;
      }
      $postFolder = createPostFolder($postId, '_request');
    } else if ($type == "post") {
      // Fetch existing request owned by user
      $res = mysqli_query($link, "SELECT * FROM posts WHERE Post_Id=$postId AND Author='$author'");
      $post = mysqli_fetch_assoc($res);
      if (!$post) {
        echo json_encode(['status' => 'error', 'message' => 'Request not found or not owned by you.']);
        exit;
      }
      if (mysqli_num_rows(mysqli_query($link, "SELECT 1 FROM posts_requests WHERE Original_Post_Id = '$postId' AND Author = '$author'")) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Request Already Exists for this Post.Please Edit that request if you need!']);
        exit;
      }
      $updateQuery = mysqli_query($link, "INSERT INTO posts_requests (Original_Post_Id,Title, Description, Body, Author, Author_Type, Posted_On) VALUES 
               ('$postId','$title', '$desc', '$body', '$author', '$author_type', '$postedOn')");
      $requestId = mysqli_insert_id($link);
      $postFolder = createPostFolder($requestId, '_request');

      // Copying Existing Media
      $src = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $postId;
      $dst = $postFolder;

      // Ensure source directory exists
      if (!is_dir($src)) {
        echo json_encode(['status' => 'error', 'message' => 'Source folder not found: $src']);
        exit;
      }

      // Ensure destination folder exists
      if (!is_dir($dst)) {
        mkdir($dst, 0775, true);
      }

      $files = scandir($src);
      foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        $srcPath = $src . DIRECTORY_SEPARATOR . $file;
        $dstPath = $dst . DIRECTORY_SEPARATOR . $file;
        if (is_file($srcPath)) {
          if (!copy($srcPath, $dstPath)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to copy $srcPath to $dstPath']);
          }
        }
      }
    }

    // Handle cover photo update if new file uploaded
    $coverPhoto = $post['Cover_Photo'];
    if (!empty($_FILES['Cover_Photo']['name'])) {
      $coverTemp = $_FILES['Cover_Photo']['tmp_name'];
      $coverName = sanitizeFilename(basename($_FILES['Cover_Photo']['name']));
      $coverPath = $postFolder . "/cover_" . time() . "_" . $coverName;
      if (move_uploaded_file($coverTemp, $coverPath)) {
        if ($coverPhoto && file_exists($postFolder . "/" . $coverPhoto)) unlink($postFolder . "/" . $coverPhoto);
        $coverPhoto = basename($coverPath);
      }
    }

    // Handle media removal
    $existingMedia = json_decode($post['Media'], true) ?: [];
    $removeMedia = $_POST['remove_media'] ?? [];
    $existingMedia = array_filter($existingMedia, function ($mediaFile) use ($removeMedia, $postFolder) {
      if (in_array($mediaFile, $removeMedia)) {
        $filePath = $postFolder . "/" . $mediaFile;
        if (file_exists($filePath)) unlink($filePath);
        return false;
      }
      return true;
    });

    // New media uploads
    $newMediaFiles = [];
    if (!empty($_FILES['Media']['name'][0])) {
      $count = 0;
      foreach ($_FILES['Media']['name'] as $key => $name) {
        if ($_FILES['Media']['error'][$key] !== UPLOAD_ERR_OK) continue;
        $tmpName = $_FILES['Media']['tmp_name'][$key];
        $safeName = sanitizeFilename(basename($name));
        $targetPath = $postFolder . "/media_" . time() . "_" . $count . "_" . $safeName;
        if (move_uploaded_file($tmpName, $targetPath)) {
          $newMediaFiles[] = basename($targetPath);
        }
        $count++;
      }
    }

    $finalMedia = array_merge($existingMedia, $newMediaFiles);
    $mediaJson = json_encode($finalMedia);

    // Links processing
    if (isset($_POST['Links'])) {
      if (is_array($_POST['Links'])) {
        $linksArr = array_filter(array_map('trim', $_POST['Links']));
      } else {
        $linksArr = array_filter(array_map('trim', explode(',', $_POST['Links'])));
      }
    } else {
      $linksArr = [];
    }
    $linksStr = implode(',', $linksArr);
    $linksStr = mysqli_real_escape_string($link, $linksStr);

    // Update posts_requests with new data, set Status to Pending on update
    if ($type == "request") {
      $updateQuery = "UPDATE posts_requests SET Title='$title', Description='$desc', Body='$body', Cover_Photo='$coverPhoto', Media='$mediaJson', Links='$linksStr', Posted_On='$postedOn',Status='Pending',Remarks='' WHERE Post_Id=$postId";
    } else if ($type == "post") {
      $updateQuery = "UPDATE posts_requests SET Cover_Photo='$coverPhoto', Media='$mediaJson', Links='$linksStr' WHERE Post_Id=$requestId";
    }
    $update = mysqli_query($link, $updateQuery);

    if ($update) {
      echo json_encode(['status' => 'error', 'message' => 'Post update request submitted successfully.']);
    } else {
      echo json_encode(['status' => 'error', 'message' => 'Failed to update post request.']);
    }
    exit;
  }

  if (isset($_POST['delete'])) {
    if (!can('delete', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to delete post');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    // ==== DELETE POST OR REQUEST ====

    $postId = intval($_POST['Post_Id']);
    $postFolderRequest = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $postId . "_request";
    $postFolderPublished = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $postId;

    // Check if post request exists for this postId by this user
    $res = mysqli_query($link, "SELECT * FROM posts_requests WHERE Post_Id=$postId AND Author='" . $_SESSION['Id_No'] . "'");
    $requestExists = mysqli_num_rows($res) > 0;

    if ($requestExists) {
      // Delete request media folder and request row
      if (is_dir($postFolderRequest)) {
        $files = glob($postFolderRequest . "/*");
        foreach ($files as $file) {
          if (is_file($file)) unlink($file);
        }
        rmdir($postFolderRequest);
      }
      mysqli_query($link, "DELETE FROM posts_requests WHERE Post_Id=$postId AND Author='" . $_SESSION['Id_No'] . "'");
      echo "<script>alert('Post request deleted successfully.'); location.replace('manage_blog.php');</script>";
      exit;
    } else {
      // Else, try to delete published post owned by user
      $resPub = mysqli_query($link, "SELECT * FROM posts WHERE Post_Id=$postId AND Author='" . $_SESSION['Id_No'] . "'");
      if (mysqli_num_rows($resPub) > 0) {
        if (is_dir($postFolderPublished)) {
          $files = glob($postFolderPublished . "/*");
          foreach ($files as $file) {
            if (is_file($file)) unlink($file);
          }
          rmdir($postFolderPublished);
        }
        mysqli_query($link, "DELETE FROM posts WHERE Post_Id=$postId AND Author='" . $_SESSION['Id_No'] . "'");
        echo "<script>alert('Published post deleted successfully.'); location.replace('manage_blog.php');</script>";
        exit;
      } else {
        echo "<script>alert('Post not found.'); location.replace('manage_blog.php');</script>";
        exit;
      }
    }
  }

  if (isset($_POST['fetch'])) {
    if (!can('view', MENU_ID)) {
      echo "<script>alert('You don\'t have permission to view posts');
          location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
      exit;
    }
    // ==== FETCH POSTS / REQUESTS (For Filters) ====
    $author = $_SESSION['Id_No'];

    $whereClauses = [];

    // Filters for title and author
    if (!empty($_POST['filterTitle'])) {
      $title = mysqli_real_escape_string($link, $_POST['filterTitle']);
      $whereClauses[] = "Title LIKE '%$title%'";
    }
    if (!empty($_POST['filterStatus'])) {
      $status = mysqli_real_escape_string($link, $_POST['filterStatus']);
      $whereClauses[] = "Status = '$status'";
    }
    // Date filters
    if (!empty($_POST['filterSpecificDate'])) {
      $specificDate = mysqli_real_escape_string($link, $_POST['filterSpecificDate']);
      $whereClauses[] = "DATE(Posted_On) = '$specificDate'";
    } else {
      if (!empty($_POST['filterDateFrom']) && !empty($_POST['filterDateTo'])) {
        $dateFrom = mysqli_real_escape_string($link, $_POST['filterDateFrom']);
        $dateTo = mysqli_real_escape_string($link, $_POST['filterDateTo']);
        $whereClauses[] = "DATE(Posted_On) BETWEEN '$dateFrom' AND '$dateTo'";
      } elseif (!empty($_POST['filterDateFrom'])) {
        $dateFrom = mysqli_real_escape_string($link, $_POST['filterDateFrom']);
        $whereClauses[] = "DATE(Posted_On) >= '$dateFrom'";
      } elseif (!empty($_POST['filterDateTo'])) {
        $dateTo = mysqli_real_escape_string($link, $_POST['filterDateTo']);
        $whereClauses[] = "DATE(Posted_On) <= '$dateTo'";
      }
    }

    // Combine where clauses
    $whereSql = '';
    if ($whereClauses) {
      $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }
    if ($_POST['data'] == 'posts') {
      $sqlRequests = "SELECT Post_Id, Title, Description, Author, Cover_Photo, Posted_On,Remarks FROM `posts` WHERE Author='" . $author . "'";
    } else if ($_POST['data'] == 'requests') {
      $sqlRequests = "SELECT Post_Id, Title, Description, Author, Cover_Photo, Posted_On, Status,Remarks FROM `posts_requests` WHERE Author='" . $author . "'";
    }
    // Fetch pending and rejected from 'posts_requests'
    if (count($whereClauses) >= 1) {
      $sqlRequests .= ' AND ' . implode(' AND ', $whereClauses);
    } else {
      $sqlRequests .= implode(' AND ', $whereClauses);
    }

    // Combine all
    $sql = "$sqlRequests ORDER BY Posted_On DESC";
    $res = mysqli_query($link, $sql);

    if (mysqli_num_rows($res) == 0) {
      echo "
      <tr>
        <td class='text-center' colspan='" . ($_POST['data'] == 'requests' ? '8' : '7') . "'>No " . ($_POST['data'] == 'requests' ? 'Requests' : 'Posts') . " Yet!</td>
      </tr>
      ";
    }

    while ($row = mysqli_fetch_assoc($res)) {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($row['Post_Id']) . "</td>";
      /* ===== COVER IMAGE ===== */
      if ($_POST['data'] == 'requests') {
        echo "<td style='width:200px;height:100px'>
            <img src='" . $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_{$row['Post_Id']}_request/" . htmlspecialchars($row['Cover_Photo']) . "'
                 class='img-thumbnail rounded'
                 style='width:200px;height:100px'>
          </td>";
      } else {
        echo "<td style='width:200px;height:100px'>
            <img src='" . $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_{$row['Post_Id']}/" . htmlspecialchars($row['Cover_Photo']) . "'
                 class='img-thumbnail rounded'
                 style='width:200px;height:100px'>
          </td>";
      }
      echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
      echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
      echo "<td>" . date("d-m-Y H:i", strtotime($row['Posted_On'])) . "</td>";
      if ($_POST['data'] == 'requests') {
        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
      }
      echo "<td>" . htmlspecialchars($row['Remarks']) . "</td>";
      echo "<td style='height:115px;display:flex;gap:20px;align-items:center;'>";

      /* ==========================================================
     VIEW / EDIT (same logic, RBAC enforced)
     ========================================================== */

      if ($_POST['data'] != 'requests' || ($_POST['data'] == 'requests' && $row['Status'] !== 'Blocked')) {
        /* ===== REQUEST MODE ===== */
        if ($_POST['data'] == 'requests') {

          echo '<div ' . (!can('update', MENU_ID) ? 'title="You don\'t have permission to edit this request"' : '') . '>';

          if (can('update', MENU_ID)) {
            echo "<button class='btn btn-sm btn-warning d-flex justify-content-center align-items-center'
                     style='width:70px;height:40px;gap:8px;'
                     data-bs-toggle='modal'
                     data-bs-target='#editRequestModal{$row['Post_Id']}'>
                <i class='bx bx-edit'></i> <span>Edit</span>
              </button>";
          } else {
            echo "<button class='btn btn-sm btn-secondary d-flex justify-content-center align-items-center'
                     style='width:70px;height:40px;gap:8px;'
                     disabled>
                <i class='bx bx-edit'></i> <span>Edit</span>
              </button>";
          }

          echo '</div>';

          /* ===== NORMAL MODE ===== */
        } else {

          /* ----- VIEW ----- */
          echo '<div ' . (!can('view', MENU_ID) ? 'title="You don\'t have permission to view this post"' : '') . '>';

          if (can('view', MENU_ID)) {
            echo "<a href='/Victory/blog/post.php?id={$row['Post_Id']}'
                   target='_blank'
                   class='btn btn-sm btn-success d-flex justify-content-center align-items-center'
                   style='width:70px;height:40px;gap:8px;'>
                <i class='fas fa-eye'></i> <span>View</span>
              </a>";
          } else {
            echo "<a href='javascript:void(0)'
                   class='btn btn-sm btn-secondary d-flex justify-content-center align-items-center disabled'
                   style='width:70px;height:40px;gap:8px;'>
                <i class='fas fa-eye'></i> <span>View</span>
              </a>";
          }

          echo '</div>';

          /* ----- EDIT ----- */
          echo '<div ' . (!can('update', MENU_ID) ? 'title="You don\'t have permission to edit this post"' : '') . '>';

          if (can('update', MENU_ID)) {
            echo "<button class='btn btn-sm btn-warning d-flex justify-content-center align-items-center'
                     style='width:70px;height:40px;gap:8px;'
                     data-bs-toggle='modal'
                     data-bs-target='#editPostModal{$row['Post_Id']}'>
                <i class='bx bx-edit'></i> <span>Edit</span>
              </button>";
          } else {
            echo "<button class='btn btn-sm btn-secondary d-flex justify-content-center align-items-center'
                     style='width:70px;height:40px;gap:8px;'
                     disabled>
                <i class='bx bx-edit'></i> <span>Edit</span>
              </button>";
          }

          echo '</div>';
        }
      }

      /* ==========================================================
     DELETE (always shown, RBAC controlled)
     ========================================================== */

      echo '<div ' . (!can('delete', MENU_ID) ? 'title="You don\'t have permission to delete this post"' : '') . '>';

      if (can('delete', MENU_ID)) {
        echo "<form method='POST'
                 action=''
                 style='display:inline;'
                 onsubmit='return confirm(\"Are you sure you want to delete this post?\");'>
            <input type='hidden' name='Post_Id' value='{$row['Post_Id']}'>
            <button type='submit'
                    name='delete'
                    class='btn btn-sm btn-danger d-flex justify-content-center align-items-center'
                    style='width:80px;height:40px;gap:8px;'>
              <i class='bx bx-trash'></i> <span>Delete</span>
            </button>
          </form>";
      } else {
        echo "<button class='btn btn-sm btn-secondary d-flex justify-content-center align-items-center'
                 style='width:80px;height:40px;gap:8px;'
                 disabled>
            <i class='bx bx-trash'></i> <span>Delete</span>
          </button>";
      }

      echo '</div>';

      echo "</td></tr>";
    }
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
  <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon" />

  <!-- CSS and JS -->
  <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

  <!-- Summernote CSS/JS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>

  <style>
    body {
      overflow-x: scroll;
    }

    .table-container {
      max-width: 1200px;
      max-height: 600px;
      overflow-x: scroll;
    }

    @media screen and (max-width: 576px) {
      .container {
        width: 80%;
        margin-left: 20%;
        overflow-x: scroll;
      }
    }

    @media print {
      * {
        display: none;
      }

      #table-container {
        display: block;
      }
    }

    #sign-out {
      display: none;
    }

    @media screen and (max-width: 920px) {
      #sign-out {
        display: block;
      }
    }

    .img-thumbnail {
      object-fit: cover;
      height: 60px;
      width: 60px;
    }

    td div[title],
    td div[title] * {
      cursor: not-allowed !important;
    }

    .disabled {
      pointer-events: none;
      opacity: 0.6;
    }
  </style>
</head>

<body class="bg-light">
  <?php include '../sidebar.php';
  ?>

  <div class="container my-4" style="margin-left: 8%;">
    <h2 class="mb-4 text-center">Manage Your Requests</h2>

    <div class="btn-wrapper"
      <?php if (!can('create', MENU_ID)) { ?>
      title="You don't have permission to create blog post"
      <?php } ?>>
      <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPostModal" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>
        <i class="bx bx-plus"></i> Add New Post
      </button>
    </div>

    <form id="requestsFilterForm" class="row g-3 mb-3 align-items-end">
      <div class="col-md-3">
        <label for="filterTitle" class="form-label">Search Title</label>
        <input type="text" class="form-control" id="filterTitle" name="filterTitle" placeholder="Search Title...">
      </div>
      <div class="col-md-3">
        <label for="filterStatus" class="form-label">Status</label>
        <select class="form-control" name="filterStatus" id="filterStatus">
          <option value="">-- Select Status --</option>
          <option value="Pending">Pending</option>
          <option value="Rejected">Rejected</option>
        </select>
      </div>
      <div class="col-md-2">
        <label for="filterSpecificDate" class="form-label">Specific Date</label>
        <input type="date" class="form-control" id="filterSpecificDate" name="filterSpecificDate" value="<?= htmlspecialchars($_GET['filterSpecificDate'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label for="filterDateFrom" class="form-label">Date From</label>
        <input type="date" class="form-control" id="filterDateFrom" name="filterDateFrom">
      </div>
      <div class="col-md-3">
        <label for="filterDateTo" class="form-label">Date To</label>
        <input type="date" class="form-control" id="filterDateTo" name="filterDateTo">
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <div class="btn-wrapper"
          <?php if (!can('view', MENU_ID)) { ?>
          title="You don't have permission to view/filter blog posts"
          <?php } ?>>
          <button type="submit" class="btn btn-primary" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Apply Filters</button>
        </div>
        <button type="button" class="btn btn-outline-secondary" id="requestsresetFilters">Reset</button>
      </div>
    </form>

    <div class="table-responsive table-container">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Description</th>
            <th>Posted On</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="requestsTableBody">
          <!-- Rows loaded by AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <div class="container my-5" style="margin-left: 8%;">
    <h2 class="mb-4 text-center">Manage Your Posts</h2>

    <form id="postsFilterForm" class="row g-3 mb-3 align-items-end">
      <div class="col-md-3">
        <label for="filterTitle" class="form-label">Search Title</label>
        <input type="text" class="form-control" id="filterTitle" name="filterTitle" placeholder="Search Title...">
      </div>
      <div class="col-md-2">
        <label for="filterSpecificDate" class="form-label">Specific Date</label>
        <input type="date" class="form-control" id="filterSpecificDate" name="filterSpecificDate" value="<?= htmlspecialchars($_GET['filterSpecificDate'] ?? '') ?>">
      </div>
      <div class="col-md-3">
        <label for="filterDateFrom" class="form-label">Date From</label>
        <input type="date" class="form-control" id="filterDateFrom" name="filterDateFrom">
      </div>
      <div class="col-md-3">
        <label for="filterDateTo" class="form-label">Date To</label>
        <input type="date" class="form-control" id="filterDateTo" name="filterDateTo">
      </div>
      <div class="col-md-3 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">Apply Filters</button>
        <button type="button" class="btn btn-outline-secondary" id="postsresetFilters">Reset</button>
      </div>
    </form>

    <div class="table-responsive table-container">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Cover</th>
            <th>Title</th>
            <th>Description</th>
            <th>Posted On</th>
            <th>Remarks</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="postsTableBody">
          <!-- Rows loaded by AJAX -->
        </tbody>
      </table>
    </div>
  </div>

  <?php
  $res = mysqli_query($link, "SELECT * FROM posts_requests WHERE Author = '" . $_SESSION['Id_No'] . "' ORDER BY Posted_On DESC");
  while ($row = mysqli_fetch_assoc($res)) {
    $mediaFiles = json_decode($row['Media'], true) ?: [];
  ?>

    <!-- Edit Request Modal -->
    <div class="modal fade" id="editRequestModal<?= $row['Post_Id']; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" id="editblogRequestForm<?= $row['Post_Id']; ?>" action="" enctype="multipart/form-data" onsubmit="return handleEditFormSubmit(event,<?= $row['Post_Id']; ?>,'request')">
            <input type="hidden" name="Post_Id" value="<?= $row['Post_Id']; ?>" />
            <div class="modal-header">
              <h5 class="modal-title">Edit Blog Post Request</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Title <span style="color:red;">*</span></label>
                <input type="text" required name="Title" id="requestTitle<?= $row['Post_Id']; ?>" class="form-control" value="<?= htmlspecialchars($row['Title']); ?>" />
              </div>
              <div class="mb-3">
                <label>Description <span style="color:red;">*</span></label>
                <textarea name="Description" required class="form-control" id="requestDescription<?= $row['Post_Id']; ?>"><?= htmlspecialchars($row['Description']); ?></textarea>
              </div>
              <div class="mb-3">
                <label>Body <span style="color:red;">*</span></label>
                <div id="requesteditBody<?= $row['Post_Id']; ?>" class="summernote">
                </div>
                <input type="hidden" name="Body" id="requestedithiddenBody<?= $row['Post_Id']; ?>" value="<?= htmlspecialchars($row['Body']); ?>" />
              </div>
              <div class="mb-3">
                <label>Cover Photo (current) <span style="color:red;">*</span></label><br />
                <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/blog/posts_images/post_<?= $row['Post_Id']; ?>_request/<?= htmlspecialchars($row['Cover_Photo']); ?>" class="img-thumbnail mb-2" style="width: 200px;height:100px" />
                <input type="file" name="Cover_Photo" accept="image/*" class="form-control" id="requesteditcoverPhotoInput<?= $row['Post_Id']; ?>" onchange="handleEditCoverPhotoInput(event,<?= $row['Post_Id']; ?>,'request')" />
                <div id="requesteditcoverPhotoPreview<?= $row['Post_Id']; ?>" class="mt-2"></div>
              </div>
              <div class="mb-3">
                <label>Media (images/videos/pdfs only)</label>
                <div class="d-flex flex-wrap mb-2">
                  <?php
                  foreach ($mediaFiles as $mediaFile) {
                    $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
                    $mediaPath = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $row['Post_Id'] . "_request/" . htmlspecialchars($mediaFile);
                    echo '<div class="me-2 mb-2 text-center">';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                      echo '<img src="' . $mediaPath . '" class="img-thumbnail" width="100">';
                    } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                      echo '<video width="150" controls>
                              <source src="' . $mediaPath . '" type="video/' . $ext . '">
                              Your browser does not support the video tag.
                            </video>';
                    } elseif ($ext === 'pdf') {
                      echo '<a href="' . $mediaPath . '" target="_blank" title="Open PDF" style="font-size: 2.45em; color: #d9534f;">
                              <i class="bi bi-file-earmark-pdf"></i></a>';
                    } else {
                      echo '<a href="' . $mediaPath . '" target="_blank">' . htmlspecialchars($mediaFile) . '</a>';
                    }
                    echo '<div><input type="checkbox" name="remove_media[]" id="requestremove_media' . $row['Post_Id'] . '" value="' . htmlspecialchars($mediaFile) . '"> Remove</div>';
                    echo '</div>';
                  }
                  ?>
                </div>
                <input type="file" name="Media[]" accept="image/*,video/*,application/pdf" multiple class="form-control" id="requesteditmediaInput<?= $row['Post_Id']; ?>" onchange="handleEditMediaInput(event,<?= $row['Post_Id']; ?>,'request')" />
                <div id="requesteditmediaPreview<?= $row['Post_Id']; ?>" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
              <div class="mb-3">
                <label>Links (optional)</label>
                <div id="edit-links-request-<?= $row['Post_Id']; ?>">
                  <?php
                  $linksArr = !empty($row['Links']) ? explode(',', $row['Links']) : [''];
                  foreach ($linksArr as $linkVal) {
                    echo '<div class="input-group mb-2">
                              <input type="url" name="Links[]" id="requestLinks' . $row['Post_Id'] . '" placeholder="https://example.com" class="form-control" value="' . htmlspecialchars($linkVal) . '">
                              <button type="button" class="btn btn-danger remove-link-btn">&times;</button>
                            </div>';
                  }
                  ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLinkField('edit-links-request-<?= $row['Post_Id']; ?>','<?= $row['Post_Id']; ?>')">+ Add Link</button>
              </div>
            </div>
            <div class="modal-footer">
              <div class="btn-wrapper"
                <?php if (!can('update', MENU_ID)) { ?>
                title="You don't have permission to update this post"
                <?php } ?>>
                <button type="submit" name="update" class="btn btn-success" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Update Post</button>
              </div>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        $('#requesteditBody<?= $row['Post_Id']; ?>').summernote({
          height: 200,
          toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
          ],
        });
        $('#requesteditBody<?= $row['Post_Id']; ?>').summernote('code', <?= json_encode($row['Body']); ?>);
      });
    </script>

  <?php } ?>

  <?php
  $res1 = mysqli_query($link, "SELECT * FROM posts WHERE Author = '" . $_SESSION['Id_No'] . "' ORDER BY Posted_On DESC");
  while ($row1 = mysqli_fetch_assoc($res1)) {
    $mediaFiles = json_decode($row1['Media'], true) ?: [];
  ?>

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal<?= $row1['Post_Id']; ?>" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form method="POST" id="editblogPostForm<?= $row1['Post_Id']; ?>" action="" enctype="multipart/form-data" onsubmit="return handleEditFormSubmit(event,<?= $row1['Post_Id']; ?>,'post')">
            <input type="hidden" name="Post_Id" value="<?= $row1['Post_Id']; ?>" />
            <div class="modal-header">
              <h5 class="modal-title">Edit Blog Post</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Title <span style="color:red;">*</span></label>
                <input type="text" required name="Title" id="postTitle<?= $row1['Post_Id']; ?>" class="form-control" value="<?= htmlspecialchars($row1['Title']); ?>" />
              </div>
              <div class="mb-3">
                <label>Description <span style="color:red;">*</span></label>
                <textarea name="Description" required class="form-control" id="postDescription<?= $row1['Post_Id']; ?>"><?= htmlspecialchars($row1['Description']); ?></textarea>
              </div>
              <div class="mb-3">
                <label>Body <span style="color:red;">*</span></label>
                <div id="posteditBody<?= $row1['Post_Id']; ?>" class="summernote">
                </div>
                <input type="hidden" name="Body" id="postedithiddenBody<?= $row1['Post_Id']; ?>" value="<?= htmlspecialchars($row1['Body']); ?>" />
              </div>
              <div class="mb-3">
                <label>Cover Photo (current) <span style="color:red;">*</span></label><br />
                <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/blog/posts_images/post_<?= $row1['Post_Id']; ?>/<?= htmlspecialchars($row1['Cover_Photo']); ?>" class="img-thumbnail mb-2" style="width: 200px;height:100px" />
                <input type="file" name="Cover_Photo" accept="image/*" class="form-control" id="posteditcoverPhotoInput<?= $row1['Post_Id']; ?>" onchange="handleEditCoverPhotoInput(event,<?= $row1['Post_Id']; ?>,'post')" />
                <div id="posteditcoverPhotoPreview<?= $row1['Post_Id']; ?>" class="mt-2"></div>
              </div>
              <div class="mb-3">
                <label>Media (images/videos/pdfs only)</label>
                <div class="d-flex flex-wrap mb-2">
                  <?php
                  foreach ($mediaFiles as $mediaFile) {
                    $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
                    $mediaPath = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $row1['Post_Id'] . "/" . htmlspecialchars($mediaFile);
                    echo '<div class="me-2 mb-2 text-center">';
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                      echo '<img src="' . $mediaPath . '" class="img-thumbnail" width="100">';
                    } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                      echo '<video width="150" controls>
                              <source src="' . $mediaPath . '" type="video/' . $ext . '">
                              Your brow1ser does not support the video tag.
                            </video>';
                    } elseif ($ext === 'pdf') {
                      echo '<a href="' . $mediaPath . '" target="_blank" title="Open PDF" style="font-size: 2.45em; color: #d9534f;">
                              <i class="bi bi-file-earmark-pdf"></i></a>';
                    } else {
                      echo '<a href="' . $mediaPath . '" target="_blank">' . htmlspecialchars($mediaFile) . '</a>';
                    }
                    echo '<div><input type="checkbox" name="remove_media[]" id="postremove_media' . $row1['Post_Id'] . '" value="' . htmlspecialchars($mediaFile) . '"> Remove</div>';
                    echo '</div>';
                  }
                  ?>
                </div>
                <input type="file" name="Media[]" accept="image/*,video/*,application/pdf" multiple class="form-control" id="posteditmediaInput<?= $row1['Post_Id']; ?>" onchange="handleEditMediaInput(event,<?= $row1['Post_Id']; ?>,'post')" />
                <div id="posteditmediaPreview<?= $row1['Post_Id']; ?>" class="mt-2 d-flex flex-wrap gap-2"></div>
              </div>
              <div class="mb-3">
                <label>Links (optional)</label>
                <div id="edit-links-post-<?= $row1['Post_Id']; ?>">
                  <?php
                  $linksArr = !empty($row1['Links']) ? explode(',', $row1['Links']) : [''];
                  foreach ($linksArr as $linkVal) {
                    echo '<div class="input-group mb-2">
                              <input type="url" name="Links[]" id="postLinks' . $row1['Post_Id'] . '" placeholder="https://example.com" class="form-control" value="' . htmlspecialchars($linkVal) . '">
                              <button type="button" class="btn btn-danger remove-link-btn">&times;</button>
                            </div>';
                  }
                  ?>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLinkField('edit-links-post-<?= $row1['Post_Id']; ?>','<?= $row1['Post_Id']; ?>')">+ Add Link</button>
              </div>
            </div>
            <div class="modal-footer">
              <div class="btn-wrapper"
                <?php if (!can('update', MENU_ID)) { ?>
                title="You don't have permission to update this post"
                <?php } ?>>
                <button type="submit" name="update" class="btn btn-success" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Update Post</button>
              </div>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        $('#posteditBody<?= $row1['Post_Id']; ?>').summernote({
          height: 200,
          toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['font', ['strikethrough', 'superscript', 'subscript']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['link']],
          ],
        });
        $('#posteditBody<?= $row1['Post_Id']; ?>').summernote('code', <?= json_encode($row1['Body']); ?>);
      });
    </script>

  <?php } ?>

  <!-- Add Post Modal -->
  <div class="modal fade" id="addPostModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="addblogPostForm" method="POST" enctype="multipart/form-data" onsubmit="return handleAddFormSubmit(event)">
          <div class="modal-header">
            <h5 class="modal-title">Add Blog Post</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Title <span style="color:red;">*</span></label>
              <input type="text" required name="Title" class="form-control" />
            </div>

            <div class="mb-3">
              <label>Description <span style="color:red;">*</span></label>
              <textarea required name="Description" class="form-control"></textarea>
            </div>

            <div class="mb-3">
              <label>Body <span style="color:red;">*</span></label>
              <div id="addBody" class="summernote"></div>
              <input type="hidden" name="Body" id="addhiddenBody" />
            </div>

            <div class="mb-3">
              <label>Cover Photo <span style="color:red;">*</span></label>
              <input type="file" required name="Cover_Photo" accept="image/*" class="form-control" id="addcoverPhotoInput" onchange="handleAddCoverPhotoInput(event)" />
              <div id="addcoverPhotoPreview" class="mt-2"></div>
            </div>

            <div class="mb-3">
              <label>Media (images/videos/pdfs only)</label>
              <input type="file" name="Media[]" accept="image/*,video/*,application/pdf" multiple class="form-control" id="addmediaInput" onchange="handleAddMediaInput(event)" />
              <div id="addmediaPreview" class="mt-2 d-flex flex-wrap gap-2"></div>
            </div>

            <div class="mb-3">
              <label>Reference Links (optional)</label>
              <div id="add-links">
                <div class="input-group mb-2">
                  <input type="url" name="Links[]" placeholder="https://example.com" class="form-control" />
                  <button type="button" class="btn btn-danger remove-link-btn">&times;</button>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLinkField('add-links')">+ Add Link</button>
            </div>
          </div>
          <div class="modal-footer">
            <div class="btn-wrapper"
              <?php if (!can('create', MENU_ID)) { ?>
              title="You don't have permission to create blog post"
              <?php } ?>>
              <button type="submit" name="add" class="btn btn-success" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Submit Post for Approval</button>
            </div>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    //Summernote Editor in Add Modal
    $(document).ready(function() {
      $('#addBody').summernote({
        height: 200,
        toolbar: [
          ['style', ['bold', 'italic', 'underline', 'clear']],
          ['font', ['strikethrough', 'superscript', 'subscript']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview']],
        ],
      });
      $('#addBody').summernote('code', '')
    });

    // Cover photo preview

    //Preview Cover Photo in Add Form
    function handleAddCoverPhotoInput(event) {
      const addcoverPhotoInput = document.getElementById('addcoverPhotoInput');
      const addcoverPhotoPreview = document.getElementById('addcoverPhotoPreview');
      addcoverPhotoPreview.innerHTML = '';
      const file = event.target.files[0];
      if (!file) return;
      if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.maxWidth = '200px';
        img.style.maxHeight = '120px';
        img.onload = () => URL.revokeObjectURL(img.src);
        addcoverPhotoPreview.appendChild(img);
      } else {
        addcoverPhotoPreview.textContent = 'Selected file is not an image';
      }
    }

    //Preview Cover Photo in Edit Form
    function handleEditCoverPhotoInput(event, post_id, type) {
      const editcoverPhotoInput = document.getElementById(`${type}editcoverPhotoInput${post_id}`);
      const editcoverPhotoPreview = document.getElementById(`${type}editcoverPhotoPreview${post_id}`);

      // Clear any existing preview
      editcoverPhotoPreview.innerHTML = '';

      const file = event.target.files[0];
      if (!file) return;

      if (file.type.startsWith('image/')) {
        // Create container div to hold image and remove button
        const container = document.createElement('div');
        container.style.position = 'relative';
        container.style.display = 'inline-block';
        container.style.width = '200px';

        // Create the image element
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.style.width = '100%';
        img.style.height = '120px';
        img.style.objectFit = 'cover';
        img.onload = () => URL.revokeObjectURL(img.src);
        container.appendChild(img);

        // Create the remove button
        const removeBtn = document.createElement('button');
        removeBtn.textContent = '×';
        removeBtn.title = 'Remove selected image';
        Object.assign(removeBtn.style, {
          position: 'absolute',
          top: '5px',
          right: '5px',
          background: 'rgba(255, 0, 0, 0.7)',
          border: 'none',
          color: 'white',
          width: '25px',
          height: '25px',
          borderRadius: '50%',
          cursor: 'pointer',
          fontWeight: 'bold',
          fontSize: '20px',
          lineHeight: '20px',
          padding: '0',
        });

        // Remove button event handler
        removeBtn.onclick = function(e) {
          e.stopPropagation();
          // Clear the file input
          editcoverPhotoInput.value = '';
          // Remove preview
          editcoverPhotoPreview.innerHTML = '';
        };

        container.appendChild(removeBtn);

        // Append container to preview div
        editcoverPhotoPreview.appendChild(container);
      } else {
        editcoverPhotoPreview.textContent = 'Selected file is not an image';
      }
    }

    // Media preview and selection
    let addselectedMediaFiles = [],
      editselectedMediaFiles = [];

    //Handle Media Inputs change in Add Form
    function handleAddMediaInput(event) {
      const addmediaInput = document.getElementById('addmediaInput');
      const newFiles = Array.from(event.target.files);
      newFiles.forEach(file => {
        // Avoid duplicates (optional)
        if (!addselectedMediaFiles.some(f => f.file.name === file.name && f.file.size === file.size && f.file.lastModified === file.lastModified)) {
          addselectedMediaFiles.push({
            file: file,
            previewUrl: URL.createObjectURL(file),
          });
        }
      });
      addrenderMediaPreviews();

      // Clear input so same files can be reselected after removal
      addmediaInput.value = '';
    }

    //Handle Media Inputs change in Edit Form
    function handleEditMediaInput(event, post_id, type) {
      const editmediaInput = document.getElementById(`${type}editmediaInput${post_id}`);
      const newFiles = Array.from(event.target.files);
      newFiles.forEach(file => {
        // Avoid duplicates (optional)
        if (!editselectedMediaFiles.some(f => f.file.name === file.name && f.file.size === file.size && f.file.lastModified === file.lastModified)) {
          editselectedMediaFiles.push({
            file: file,
            previewUrl: URL.createObjectURL(file),
          });
        }
      });
      editrenderMediaPreviews(post_id, type);

      // Clear input so same files can be reselected after removal
      editmediaInput.value = '';
    }

    //Handle Media Previews change in Add Form
    function addrenderMediaPreviews() {
      const addmediaPreview = document.getElementById('addmediaPreview');
      addmediaPreview.innerHTML = '';
      addselectedMediaFiles.forEach((fileObj, index) => {
        const container = document.createElement('div');
        container.style.position = 'relative';
        container.style.width = '120px';
        container.style.border = '1px solid #ccc';
        container.style.borderRadius = '4px';
        container.style.padding = '5px';

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'x';
        removeBtn.style.position = 'absolute';
        removeBtn.style.top = '2px';
        removeBtn.style.right = '6px';
        removeBtn.style.background = 'red';
        removeBtn.style.color = 'white';
        removeBtn.style.border = 'none';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '20px';
        removeBtn.style.height = '20px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.title = 'Remove file';
        removeBtn.onclick = () => addremoveFileFromSelection(index);
        container.appendChild(removeBtn);

        const file = fileObj.file;
        const ext = file.name.split('.').pop().toLowerCase();

        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = fileObj.previewUrl;
          img.style.width = '100%';
          img.style.height = 'auto';
          img.style.objectFit = 'cover';
          container.appendChild(img);
        } else if (file.type.startsWith('video/')) {
          const video = document.createElement('video');
          video.src = fileObj.previewUrl;
          video.controls = true;
          video.style.width = '100%';
          container.appendChild(video);
        } else if (ext === 'pdf') {
          const icon = document.createElement('i');
          icon.className = 'bi bi-file-earmark-pdf';
          icon.style.fontSize = '3rem';
          icon.style.color = '#d9534f';
          icon.style.display = 'block';
          icon.style.margin = '0 auto';
          icon.style.cursor = 'pointer';

          container.appendChild(icon);

          container.title = 'Open PDF in new tab';
          container.onclick = () => window.open(fileObj.previewUrl, '_blank');
        } else {
          const span = document.createElement('span');
          span.textContent = file.name;
          container.appendChild(span);
        }
        addmediaPreview.appendChild(container);
      });
    }

    //Handle Media Previews change in Edit Form
    function editrenderMediaPreviews(post_id, type) {
      const editmediaPreview = document.getElementById(`${type}editmediaPreview${post_id}`);
      editmediaPreview.innerHTML = '';
      editselectedMediaFiles.forEach((fileObj, index) => {
        const container = document.createElement('div');
        container.style.position = 'relative';
        container.style.width = '120px';
        container.style.border = '1px solid #ccc';
        container.style.borderRadius = '4px';
        container.style.padding = '5px';

        // Remove button
        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'x';
        removeBtn.style.position = 'absolute';
        removeBtn.style.top = '2px';
        removeBtn.style.right = '6px';
        removeBtn.style.background = 'red';
        removeBtn.style.color = 'white';
        removeBtn.style.border = 'none';
        removeBtn.style.borderRadius = '50%';
        removeBtn.style.width = '20px';
        removeBtn.style.height = '20px';
        removeBtn.style.cursor = 'pointer';
        removeBtn.title = 'Remove file';
        removeBtn.onclick = () => editremoveFileFromSelection(index, post_id, type);
        container.appendChild(removeBtn);

        const file = fileObj.file;
        const ext = file.name.split('.').pop().toLowerCase();

        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = fileObj.previewUrl;
          img.style.width = '100%';
          img.style.height = 'auto';
          img.style.objectFit = 'cover';
          container.appendChild(img);
        } else if (file.type.startsWith('video/')) {
          const video = document.createElement('video');
          video.src = fileObj.previewUrl;
          video.controls = true;
          video.style.width = '100%';
          container.appendChild(video);
        } else if (ext === 'pdf') {
          const icon = document.createElement('i');
          icon.className = 'bi bi-file-earmark-pdf';
          icon.style.fontSize = '3rem';
          icon.style.color = '#d9534f';
          icon.style.display = 'block';
          icon.style.margin = '0 auto';
          icon.style.cursor = 'pointer';

          container.appendChild(icon);

          container.title = 'Open PDF in new tab';
          container.onclick = () => window.open(fileObj.previewUrl, '_blank');
        } else {
          const span = document.createElement('span');
          span.textContent = file.name;
          container.appendChild(span);
        }
        editmediaPreview.appendChild(container);
      });
    }

    //Handle Media Removal in Add Form
    function addremoveFileFromSelection(index) {
      URL.revokeObjectURL(addselectedMediaFiles[index].previewUrl);
      addselectedMediaFiles.splice(index, 1);
      addrenderMediaPreviews();
    }

    //Handle Media Removal in Edit Form
    function editremoveFileFromSelection(index, post_id, type) {
      URL.revokeObjectURL(editselectedMediaFiles[index].previewUrl);
      editselectedMediaFiles.splice(index, 1);
      editrenderMediaPreviews(post_id, type);
      /* if (post_id) {
      } else {
        editrenderMediaPreviews();
      } */
    }

    //Add Link
    function addLinkField(containerId, post_id = null) {
      let container = document.getElementById(containerId);
      let div = document.createElement("div");
      div.className = "input-group mb-2";
      if (post_id) {
        div.innerHTML = `<input type="url" name="Links[]" id="Links${post_id}" placeholder="https://example.com" class="form-control" />
                   <button type="button" class="btn btn-danger remove-link-btn">&times;</button>`;
      } else {
        div.innerHTML = `<input type="url" name="Links[]" placeholder="https://example.com" class="form-control" />
                   <button type="button" class="btn btn-danger remove-link-btn">&times;</button>`;
      }

      container.appendChild(div);
    }

    // Remove Link Handler
    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('remove-link-btn')) {
        e.target.parentElement.remove();
      }
    });

    // Handle form submission with AJAX sending the FormData and all selected media files in Add Form
    async function handleAddFormSubmit(e) {
      e.preventDefault();

      // Validate summernote body
      $('#addhiddenBody').val($('#addBody').summernote('code').trim());
      if ($('#addhiddenBody').val().trim() === '') {
        alert('Body content is required.');
        $('#addBody').summernote('focus');
        return false;
      }

      const addcoverPhotoInput = document.getElementById('addcoverPhotoInput');
      // Validate cover photo input
      if (addcoverPhotoInput.files.length === 0) {
        alert('Please select a cover photo.');
        return false;
      }

      const maxTotalSize = 120 * 1024 * 1024;
      let totalSize = addcoverPhotoInput.files[0].size;
      for (const fileObj of addselectedMediaFiles) {
        totalSize += fileObj.file.size;
      }
      if (totalSize > maxTotalSize) {
        alert('Total selected files exceed 128MB. Please reduce file sizes or number or Try to upload multiple times.');
        return false;
      }
      const formData = new FormData();

      formData.append('Title', document.querySelector('#addblogPostForm input[name="Title"]').value.trim());
      formData.append('Description', document.querySelector('#addblogPostForm textarea[name="Description"]').value.trim());
      formData.append('Body', $('#addhiddenBody').val());

      // Cover Photo Append
      formData.append('Cover_Photo', addcoverPhotoInput.files[0]);

      // Append media files from addselectedMediaFiles array
      addselectedMediaFiles.forEach((fileObj) => {
        formData.append('Media[]', fileObj.file);
      });

      // Gather Links
      document.querySelectorAll('#addblogPostForm input[name="Links[]"]').forEach(linkInput => {
        if (linkInput.value.trim()) {
          formData.append('Links[]', linkInput.value.trim());
        }
      });

      // Add action flag
      formData.append('add', '1');

      try {
        const response = await fetch('manage_blog.php', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const resText = await response.text();

        alert('Post uploaded successfully.');
        window.location.reload();

      } catch (error) {
        alert('Upload failed: ' + error.message);
      }

      return false;
    }

    // Handle form submission with AJAX sending the FormData and all selected media files in Edit Form
    async function handleEditFormSubmit(e, post_id, type) {
      e.preventDefault();

      // Validate summernote body
      var bodyHtml = $(`#${type}editBody${post_id}`).summernote('code').trim();
      if (
        !bodyHtml ||
        bodyHtml === '<br>' ||
        bodyHtml === '<p><br></p>' ||
        bodyHtml.replace(/<[^>]*>/g, '').trim() === ''
      ) {
        bodyHtml = '';
      }
      $(`#${type}edithiddenBody${post_id}`).val(bodyHtml);
      if ($(`#${type}edithiddenBody${post_id}`).val().trim() === '') {
        alert('Body content is required.');
        $(`#${type}editBody${post_id}`).summernote('focus');
        return false;
      }

      const editcoverPhotoInput = document.getElementById(`${type}editcoverPhotoInput${post_id}`);

      const formData = new FormData();
      formData.append('Post_Id', post_id);
      formData.append('Title', document.querySelector(`#${type}Title${post_id}`).value.trim());
      formData.append('Description', document.querySelector(`#${type}Description${post_id}`).value.trim());
      formData.append('Body', $(`#${type}edithiddenBody${post_id}`).val());

      // Cover Photo Append
      if (editcoverPhotoInput.files.length > 0) {
        formData.append('Cover_Photo', editcoverPhotoInput.files[0]);
      }

      // Append media files from editselectedMediaFiles array
      editselectedMediaFiles.forEach((fileObj) => {
        formData.append('Media[]', fileObj.file);
      });

      // Gather Remove Media
      document.querySelectorAll(`#${type}remove_media${post_id}:checked`).forEach(removeMedia => {
        if (removeMedia.value.trim()) {
          formData.append('remove_media[]', removeMedia.value.trim());
        }
      });

      // Gather Links
      document.querySelectorAll(`#${type}Links${post_id}`).forEach(linkInput => {
        if (linkInput.value.trim()) {
          formData.append('Links[]', linkInput.value.trim());
        }
      });

      // Update action flag
      formData.append('Type', type);
      formData.append('update', '1');

      try {
        const response = await fetch('manage_blog.php', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) throw new Error('Network response was not ok');

        /* const resText = await response.text();

        alert('Post updated successfully.'); */
        const resJson = await response.json();

        if (resJson.status === 'error') {
          alert(resJson.message); // Show the PHP error here
        } else {
          alert(resJson.message); // Success message from PHP
        }
        window.location.reload();

      } catch (error) {
        alert('Upload failed: ' + error.message);
      }

      return false;
    }
  </script>

  <script>
    function loadFilteredRequests() {
      const params = {
        fetch: 1,
        data: 'requests',
        filterTitle: $('#requestsFilterForm #filterTitle').val(),
        filterStatus: $('#requestsFilterForm #filterStatus').val(),
        filterAuthor: $('#requestsFilterForm #filterAuthor').val(),
        filterAuthorType: $('#requestsFilterForm #filterAuthorType').val(),
        filterDateFrom: $('#requestsFilterForm #filterDateFrom').val(),
        filterDateTo: $('#requestsFilterForm #filterDateTo').val(),
        filterSpecificDate: $('#requestsFilterForm #filterSpecificDate').val(),
      };

      $.ajax({
        url: '', // current PHP page
        method: 'POST',
        data: params,
        beforeSend: function() {
          $('#requestsTableBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
        },
        success: function(data) {
          $('#requestsTableBody').html(data);
        },
        error: function() {
          $('#requestsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading data.</td></tr>');
        }
      });
    }

    function loadFilteredPosts() {
      const params = {
        fetch: 1,
        data: 'posts',
        filterTitle: $('#postsFilterForm #filterTitle').val(),
        filterStatus: $('#postsFilterForm #filterStatus').val(),
        filterAuthor: $('#postsFilterForm #filterAuthor').val(),
        filterAuthorType: $('#postsFilterForm #filterAuthorType').val(),
        filterDateFrom: $('#postsFilterForm #filterDateFrom').val(),
        filterDateTo: $('#postsFilterForm #filterDateTo').val(),
        filterSpecificDate: $('#postsFilterForm #filterSpecificDate').val(),
      };

      $.ajax({
        url: '', // current PHP page
        method: 'POST',
        data: params,
        beforeSend: function() {
          $('#postsTableBody').html('<tr><td colspan="7" class="text-center">Loading...</td></tr>');
        },
        success: function(data) {
          $('#postsTableBody').html(data);
        },
        error: function() {
          $('#postsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading data.</td></tr>');
        }
      });
    }

    $(document).ready(function() {
      // Initial load
      loadFilteredRequests();
      loadFilteredPosts();

      // Requests Filter form submission
      $('#requestsFilterForm').on('submit', function(e) {
        e.preventDefault();
        loadFilteredRequests();
      });

      // Posts Filter form submission
      $('#postsFilterForm').on('submit', function(e) {
        e.preventDefault();
        loadFilteredPosts();
      });

      // Requests Reset filters
      $('#requestsresetFilters').on('click', function() {
        $('#requestsFilterForm')[0].reset();
        loadFilteredRequests();
      });

      // Posts Reset filters
      $('#postsresetFilters').on('click', function() {
        $('#postsFilterForm')[0].reset();
        loadFilteredPosts();
      });
    });
  </script>
</body>

</html>