<?php
include_once('../../link.php');
session_start();
if (!$_SESSION['Id_No']) {
  echo "<script>alert('Faculty Id Not Rendered');
    location.replace('../faculty_login.php');</script>";
  exit;
}
error_reporting(0);
date_default_timezone_set('Asia/Kolkata');

// Helper: Sanitize filename to prevent spaces, special chars for security & consistency
function sanitizeFilename($filename)
{
  $filename = preg_replace("/[^a-zA-Z0-9\-_\.]/", "", $filename); // Allow only alphanumeric, dash, underscore, dot
  return $filename;
}

function createPostFolder($postId)
{
  $folder = "../../Images/blog/posts_images/post_" . intval($postId);
  if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
  }
  return $folder;
}

// Process Create, Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  if (isset($_POST['add'])) {
    // === CREATE POST ===
    $title = mysqli_real_escape_string($link, trim($_POST['Title']));
    $desc = mysqli_real_escape_string($link, trim($_POST['Description']));
    $body = mysqli_real_escape_string($link, trim($_POST['Body']));
    $postedOn = date("Y-m-d H:i:s");

    // Get Author
    $author = $_SESSION['Id_No']; // hardcoded or from session
    $author_type = 'Faculty';

    // Validate required fields
    if (!$title || !$desc || !$body || empty($_FILES['Cover_Photo']['name'])) {
      echo "<script>alert('Please fill all mandatory fields and upload cover photo.');
                location.replace('manage_blog.php');</script>";
      exit;
    }

    // Insert preliminary row to get Post_Id
    $insert = mysqli_query($link, "INSERT INTO posts_requests (Title, Description, Body, Cover_Photo, Media, Links, Author, Posted_On) VALUES 
          ('$title', '$desc', '$body', '', '[]', '', '$author','$author_type', '$postedOn')");
    if (!$insert) {
      echo "<script>alert('Database error on post creation.');
                location.replace('manage_blog.php');</script>";
      exit;
    }
    $postId = mysqli_insert_id($link);

    // Create folder for this post media
    $postFolder = createPostFolder($postId);

    // Handle Cover Photo Upload and Sanitization
    $coverTmp = $_FILES['Cover_Photo']['tmp_name'];
    $coverName = sanitizeFilename(basename($_FILES['Cover_Photo']['name']));
    $coverPath = $postFolder . "/cover_" . time() . "_" . $coverName;
    move_uploaded_file($coverTmp, $coverPath);
    $coverFileName = basename($coverPath);

    // Handle Media Upload (any number)
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

    // Handle Links
    $linksArray = array_filter(array_map('trim', $_POST['Links'] ?? []));
    $linksString = $linksArray ? implode(",", $linksArray) : "";

    // Update DB record with cover photo, media JSON, and links
    $update = mysqli_query($link, "UPDATE posts_requests SET Cover_Photo='$coverFileName', Media='" . mysqli_real_escape_string($link, $mediaJson) . "', Links='" . mysqli_real_escape_string($link, $linksString) . "' WHERE Post_Id=$postId");

    echo "<script>alert('Post created successfully.'); location.replace('manage_blog.php');</script>";
    exit;
  }

  if (isset($_POST['update'])) {
    // === EDIT POST ===
    $postId = intval($_POST['Post_Id']);
    $title = mysqli_real_escape_string($link, trim($_POST['Title']));
    $desc = mysqli_real_escape_string($link, trim($_POST['Description']));
    $body = mysqli_real_escape_string($link, trim($_POST['Body']));
    $postedOn = date("Y-m-d H:i:s"); // optional update timestamp

    if (!$title || !$desc || !$body) {
      echo "<script>alert('Title, Description and Body are mandatory.');
              location.replace('manage_blog.php');</script>";
      exit;
    }

    // Fetch existing post info
    $res = mysqli_query($link, "SELECT * FROM posts WHERE Post_Id=$postId");
    $post = mysqli_fetch_assoc($res);
    if (!$post) {
      echo "<script>alert('Post not found.');
              location.replace('manage_blog.php');</script>";
      exit;
    }

    $postFolder = createPostFolder($postId); // Your function to create folder

    // Handle cover photo update if new file uploaded
    $coverPhoto = $post['Cover_Photo'];
    if (!empty($_FILES['Cover_Photo']['name'])) {
      $coverTemp = $_FILES['Cover_Photo']['tmp_name'];
      $coverName = sanitizeFilename(basename($_FILES['Cover_Photo']['name']));
      $coverPath = $postFolder . "/cover_" . time() . "_" . $coverName;
      if (move_uploaded_file($coverTemp, $coverPath)) {
        // Delete old cover photo if exists
        if (file_exists($postFolder . "/" . $coverPhoto))
          unlink($postFolder . "/" . $coverPhoto);
        $coverPhoto = basename($coverPath);
      }
    }

    // Handle removal of selected media files
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

    // Handle new media file uploads
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

    // Process Links safely as concatenated string
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

    // Get Author
    $author = $_SESSION['Id_No'];
    $author_type = 'Faculty';

    // Update record in DB
    $query = mysqli_query($link, "INSERT INTO posts_requests (Title, Description, Body, Cover_Photo, Media, Links, Author, Posted_On) VALUES 
          ('$title', '$desc', '$body', '$coverPhoto', '$mediaJson', '$linksStr', '$author','$author_type', '$postedOn')");

    if ($query) {
      echo "<script>alert('Post updated successfully.'); location.replace('manage_blog.php');</script>";
    } else {
      echo "<script>alert('Failed to update post.'); location.replace('manage_blog.php');</script>";
    }
    exit;
  }

  if (isset($_POST['delete'])) {
    // === DELETE POST ===
    $postId = intval($_POST['Post_Id']);
    $postFolder = "../../Images/blog/posts_images/post_" . $postId;

    // Delete folder content & folder
    if (is_dir($postFolder)) {
      $files = glob("$postFolder/*");
      foreach ($files as $file) {
        if (is_file($file)) unlink($file);
      }
      rmdir($postFolder);
    }

    mysqli_query($link, "DELETE FROM posts WHERE Post_Id=$postId");

    echo "<script>alert('Post deleted successfully.'); location.replace('manage_blog.php');</script>";
    exit;
  }

  if (isset($_POST['fetch'])) {
    $whereClauses = [];

    if (!empty($_POST['filterTitle'])) {
      $title = mysqli_real_escape_string($link, $_POST['filterTitle']);
      $whereClauses[] = "Title LIKE '%$title%'";
    }
    if (!empty($_POST['filterAuthor'])) {
      $author = mysqli_real_escape_string($link, $_POST['filterAuthor']);
      $whereClauses[] = "Author LIKE '%$author%'";
    }
    if (!empty($_POST['filterAuthorType'])) {
      $authorType = mysqli_real_escape_string($link, $_POST['filterAuthorType']);
      $whereClauses[] = "Author_Type = '$authorType'";
    }
    if (!empty($_POST['filterSpecificDate'])) {
      $specificDate = mysqli_real_escape_string($link, $_POST['filterSpecificDate']);
      $whereClauses[] = "DATE(Posted_On) = '$specificDate'";
    } else {
      // Existing date range logic
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

    $whereSql = '';
    if ($whereClauses) {
      $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    $sql = "SELECT * FROM posts $whereSql ORDER BY Posted_On DESC";

    $res = mysqli_query($link, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
      // Output each row - keep your existing table row HTML here
      echo "<tr>";
      echo "<td>" . htmlspecialchars($row['Post_Id']) . "</td>";
      echo "<td><img src='../../Images/blog/posts_images/post_" . $row['Post_Id'] . "/" . htmlspecialchars($row['Cover_Photo']) . "' class='img-thumbnail rounded' style='width: 200px; height: 100px'></td>";
      echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
      echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
      echo "<td>" . htmlspecialchars($row['Author']) . "</td>";
      echo "<td>" . date("d-m-Y H:i", strtotime($row['Posted_On'])) . "</td>";
      echo '
      <td style="height:115px;display: flex;gap:20px;align-items:center;">
        <a href="/Victory/blog/post.php?id=' . $row['Post_Id'] . '" target="_blank" class="btn btn-sm btn-success d-flex justify-content-center align-items-center" style="width: 70px;height:40px;gap:8px;">
          <i class="fas fa-eye"></i> <span>View</span>
        </a>
        <button class="btn btn-sm btn-warning d-flex justify-content-center align-items-center" style="width: 70px;height:40px;gap:8px;" data-bs-toggle="modal" data-bs-target="#editPostModal' . $row['Post_Id'] . '">
          <i class="bx bx-edit"></i> <span>Edit</span>
        </button>
        <form method="POST" action="" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this post?\');">
          <input type="hidden" name="Post_Id" value="' . $row['Post_Id'] . '" />
          <button type="submit" name="delete" class="btn btn-sm btn-danger d-flex justify-content-center align-items-center" style="width: 80px;height:40px;gap:8px;">
            <i class="bx bx-trash"></i> <span>Delete</span>
          </button>
        </form>
      </td>
      ';
      echo "</tr>";
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
  </style>
</head>

<body class="bg-light">
  <?php include '../sidebar.php'; ?>

  <div class="container my-4" style="margin-left: 8%;">
    <h2 class="mb-4">Manage Blog Posts</h2>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPostModal">
      <i class="bx bx-plus"></i> Add New Post
    </button>

    <form id="filterForm" class="row g-3 mb-3 align-items-end">
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
        <button type="button" class="btn btn-outline-secondary" id="resetFilters">Reset</button>
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
            <th>Author</th>
            <th>Posted On</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>

        </tbody>

      </table>
    </div>
    <?php
    $res = mysqli_query($link, "SELECT * FROM posts ORDER BY Posted_On DESC");
    while ($row = mysqli_fetch_assoc($res)) {
      $mediaFiles = json_decode($row['Media'], true) ?: [];
    ?>

      <!-- Edit Modal -->
      <div class="modal fade" id="editPostModal<?= $row['Post_Id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <form method="POST" id="editblogPostForm<?= $row['Post_Id']; ?>" action="" enctype="multipart/form-data" onsubmit="return handleEditFormSubmit(event,<?= $row['Post_Id']; ?>)">
              <input type="hidden" name="Post_Id" value="<?= $row['Post_Id']; ?>" />
              <div class="modal-header">
                <h5 class="modal-title">Edit Blog Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label>Title <span style="color:red;">*</span></label>
                  <input type="text" required name="Title" id="Title<?= $row['Post_Id']; ?>" class="form-control" value="<?= htmlspecialchars($row['Title']); ?>" />
                </div>
                <div class="mb-3">
                  <label>Description <span style="color:red;">*</span></label>
                  <textarea name="Description" required class="form-control" id="Description<?= $row['Post_Id']; ?>"><?= htmlspecialchars($row['Description']); ?></textarea>
                </div>
                <div class="mb-3">
                  <label>Body <span style="color:red;">*</span></label>
                  <div id="editBody<?= $row['Post_Id']; ?>" class="summernote">
                  </div>
                  <input type="hidden" name="Body" id="edithiddenBody<?= $row['Post_Id']; ?>" value="<?= htmlspecialchars($row['Body']); ?>" />
                </div>
                <div class="mb-3">
                  <label>Cover Photo (current) <span style="color:red;">*</span></label><br />
                  <img src="../../Images/blog/posts_images/post_<?= $row['Post_Id']; ?>/<?= htmlspecialchars($row['Cover_Photo']); ?>" class="img-thumbnail mb-2" style="width: 200px;height:100px" />
                  <input type="file" name="Cover_Photo" accept="image/*" class="form-control" id="editcoverPhotoInput<?= $row['Post_Id']; ?>" onchange="handleEditCoverPhotoInput(event,<?= $row['Post_Id']; ?>)" />
                  <div id="editcoverPhotoPreview<?= $row['Post_Id']; ?>" class="mt-2"></div>
                </div>
                <div class="mb-3">
                  <label>Media (images/videos/pdfs only)</label>
                  <div class="d-flex flex-wrap mb-2">
                    <?php
                    foreach ($mediaFiles as $mediaFile) {
                      $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
                      $mediaPath = "../../Images/blog/posts_images/post_" . $row['Post_Id'] . "/" . htmlspecialchars($mediaFile);
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
                      echo '<div><input type="checkbox" name="remove_media[]" id="remove_media' . $row['Post_Id'] . '" value="' . htmlspecialchars($mediaFile) . '"> Remove</div>';
                      echo '</div>';
                    }
                    ?>
                  </div>
                  <input type="file" name="Media[]" accept="image/*,video/*,application/pdf" multiple class="form-control" id="editmediaInput<?= $row['Post_Id']; ?>" onchange="handleEditMediaInput(event,<?= $row['Post_Id']; ?>)" />
                  <div id="editmediaPreview<?= $row['Post_Id']; ?>" class="mt-2 d-flex flex-wrap gap-2"></div>
                </div>
                <div class="mb-3">
                  <label>Links (optional)</label>
                  <div id="edit-links-<?= $row['Post_Id']; ?>">
                    <?php
                    $linksArr = !empty($row['Links']) ? explode(',', $row['Links']) : [''];
                    foreach ($linksArr as $linkVal) {
                      echo '<div class="input-group mb-2">
                              <input type="url" name="Links[]" id="Links' . $row['Post_Id'] . '" placeholder="https://example.com" class="form-control" value="' . htmlspecialchars($linkVal) . '">
                              <button type="button" class="btn btn-danger remove-link-btn">&times;</button>
                            </div>';
                    }
                    ?>
                  </div>
                  <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLinkField('edit-links-<?= $row['Post_Id']; ?>','<?= $row['Post_Id']; ?>')">+ Add Link</button>
                </div>

                <div class="mb-3">
                  <label>Author</label>
                  <div class="form-check d-inline-block ms-2">
                    <input class="form-check-input" type="checkbox" value="" id="addOtherAuthorCheckbox<?= $row['Post_Id']; ?>" <?= $row['Author'] != 'School' ? 'checked' : ''; ?> onchange="toggleAddAuthorBox(<?= $row['Post_Id']; ?>)">
                    <label class="form-check-label" for="addOtherAuthorCheckbox<?= $row['Post_Id']; ?>">
                      Other Author
                    </label>
                  </div>
                  <input type="text" name="Author" id="addAuthorInput<?= $row['Post_Id']; ?>" class="form-control mt-2" value="<?= $row['Author']; ?>" <?= $row['Author'] != 'School' ? '' : 'disabled'; ?>>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-success">Update Post</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
        $(document).ready(function() {
          $('#editBody<?= $row['Post_Id']; ?>').summernote({
            height: 200,
            toolbar: [
              ['style', ['bold', 'italic', 'underline', 'clear']],
              ['font', ['strikethrough', 'superscript', 'subscript']],
              ['para', ['ul', 'ol', 'paragraph']],
              ['insert', ['link']],
            ],
          });
          $('#editBody<?= $row['Post_Id']; ?>').summernote('code', <?= json_encode($row['Body']); ?>);
        });
      </script>

    <?php } ?>
  </div>

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

            <div class="mb-3">
              <label>Author</label>
              <div class="form-check d-inline-block ms-2">
                <input class="form-check-input" type="checkbox" value="" id="addOtherAuthorCheckbox" onchange="toggleAddAuthorBox()">
                <label class="form-check-label" for="addOtherAuthorCheckbox">
                  Other Author
                </label>
              </div>
              <input type="text" name="Author" id="addAuthorInput" class="form-control mt-2" value="School" disabled>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="add" class="btn btn-success">Save Post</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    //Summernote Editor
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
    function handleEditCoverPhotoInput(event, post_id) {
      const editcoverPhotoInput = document.getElementById(`editcoverPhotoInput${post_id}`);
      const editcoverPhotoPreview = document.getElementById(`editcoverPhotoPreview${post_id}`);

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
    function handleEditMediaInput(event, post_id) {
      const editmediaInput = document.getElementById(`editmediaInput${post_id}`);
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
      editrenderMediaPreviews(post_id);

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
    function editrenderMediaPreviews(post_id) {
      const editmediaPreview = document.getElementById(`editmediaPreview${post_id}`);
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
        removeBtn.onclick = () => editremoveFileFromSelection(index, post_id);
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
    function editremoveFileFromSelection(index, post_id = null) {
      URL.revokeObjectURL(editselectedMediaFiles[index].previewUrl);
      editselectedMediaFiles.splice(index, 1);
      if (post_id) {
        editrenderMediaPreviews(post_id);
      } else {
        editrenderMediaPreviews();
      }
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

      // Handle Author
      if (document.getElementById('addOtherAuthorCheckbox').checked) {
        formData.append('Author', document.getElementById('addAuthorInput').value.trim());
      }

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
    async function handleEditFormSubmit(e, post_id) {
      e.preventDefault();

      // Validate summernote body
      $(`#edithiddenBody${post_id}`).val($(`#editBody${post_id}`).summernote('code').trim());
      if ($(`#edithiddenBody${post_id}`).val().trim() === '') {
        alert('Body content is required.');
        $(`#editBody${post_id}`).summernote('focus');
        return false;
      }

      const editcoverPhotoInput = document.getElementById(`editcoverPhotoInput${post_id}`);

      const formData = new FormData();
      formData.append('Post_Id', post_id);
      formData.append('Title', document.querySelector(`#Title${post_id}`).value.trim());
      formData.append('Description', document.querySelector(`#Description${post_id}`).value.trim());
      formData.append('Body', $(`#edithiddenBody${post_id}`).val());

      // Cover Photo Append
      if (editcoverPhotoInput.files.length > 0) {
        formData.append('Cover_Photo', editcoverPhotoInput.files[0]);
      }

      // Append media files from editselectedMediaFiles array
      editselectedMediaFiles.forEach((fileObj) => {
        formData.append('Media[]', fileObj.file);
      });

      // Gather Remove Media
      document.querySelectorAll(`#remove_media${post_id}:checked`).forEach(removeMedia => {
        if (removeMedia.value.trim()) {
          formData.append('remove_media[]', removeMedia.value.trim());
        }
      });

      // Gather Links
      document.querySelectorAll(`#Links${post_id}`).forEach(linkInput => {
        if (linkInput.value.trim()) {
          formData.append('Links[]', linkInput.value.trim());
        }
      });

      // Handle Author
      if (document.getElementById(`addOtherAuthorCheckbox${post_id}`).checked) {
        formData.append('Author', document.getElementById(`addAuthorInput${post_id}`).value.trim());
      }

      // Update action flag
      formData.append('update', '1');

      try {
        const response = await fetch('manage_blog.php', {
          method: 'POST',
          body: formData,
        });

        if (!response.ok) throw new Error('Network response was not ok');

        const resText = await response.text();

        alert('Post updated successfully.');
        window.location.reload();

      } catch (error) {
        alert('Upload failed: ' + error.message);
      }

      return false;
    }
  </script>

  <script>
    function toggleAddAuthorBox(post_id = null) {
      const cb = document.getElementById('addOtherAuthorCheckbox' + (post_id ? post_id : ''));
      const authorInput = document.getElementById('addAuthorInput' + (post_id ? post_id : ''));
      authorInput.disabled = !cb.checked;
      if (!cb.checked) {
        authorInput.value = "School";
      }
    }

    function loadFilteredPosts() {
      const params = {
        fetch: 1,
        filterTitle: $('#filterTitle').val(),
        filterAuthor: $('#filterAuthor').val(),
        filterAuthorType: $('#filterAuthorType').val(),
        filterDateFrom: $('#filterDateFrom').val(),
        filterDateTo: $('#filterDateTo').val(),
        filterSpecificDate: $('#filterSpecificDate').val(),
      };

      $.ajax({
        url: '',
        method: 'POST',
        data: params,
        beforeSend: function() {
          $('.table-container').html('<p>Loading...</p>');
        },
        success: function(data) {
          $('.table-container').html(`
        <table class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Cover</th>
              <th>Title</th>
              <th>Description</th>
              <th>Author</th>
              <th>Posted On</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>${data}</tbody>
        </table>
      `);
        },
        error: function() {
          $('.table-container').html('<p>Error loading data.</p>');
        }
      });
    }

    $(document).ready(function() {
      // Load initial posts
      loadFilteredPosts();

      // Handle form submit
      $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadFilteredPosts();
      });

      // Reset filters
      $('#resetFilters').on('click', function() {
        $('#filterForm')[0].reset();
        loadFilteredPosts();
      });
    });
  </script>
</body>

</html>