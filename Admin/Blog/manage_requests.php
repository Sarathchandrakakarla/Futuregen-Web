<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 91);

requireLogin();
requireMenuAccess(MENU_ID);

if (!can('view', MENU_ID)) {
    echo "<script>alert('You don\'t have permission to view blog requests');
      location.replace('/Futuregen/Admin/admin_dashboard.php')</script>";
    exit;
}

error_reporting(0);
?>

<?php
date_default_timezone_set('Asia/Kolkata');

// Helper: Sanitize filename to prevent spaces, special chars for security & consistency
function sanitizeFilename($filename)
{
    $filename = preg_replace("/[^a-zA-Z0-9\-_\.]/", "", $filename); // Allow only alphanumeric, dash, underscore, dot
    return $filename;
}

function createPostFolder($postId, $suffix = '')
{
    $folder = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . intval($postId) . $suffix;
    if (!is_dir($folder)) {
        mkdir($folder, 0775, true);
    }
    return $folder;
}

// Process Create, Update, Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function acceptPost($link, $postId, $remarks)
    {
        $original_post_id = mysqli_fetch_row(mysqli_query($link, "SELECT Original_Post_Id FROM posts_requests WHERE Post_Id = $postId"))[0];
        $isNew =  $original_post_id == null ? true : false;
        if ($isNew) {
            $updateQuery = "INSERT INTO posts (Title, Description, Body, Cover_Photo, Media, Links, Author, Author_Type, Posted_On,Remarks) SELECT Title, Description, Body, Cover_Photo, Media, Links, Author, Author_Type, Posted_On,'$remarks' AS Remarks FROM posts_requests WHERE Post_Id = $postId;";
            if (mysqli_query($link, $updateQuery)) {
                $new_post_id = mysqli_insert_id($link);
                if (!rename($_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $postId . '_request', $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $new_post_id)) {
                    mysqli_query($link, "DELETE FROM posts WHERE Post_Id = $new_post_id");   //Delete Post to avoid files path issues
                    echo "<script>alert('Folder Rename Failed!');location.replace('manage_requests.php');</script>";
                    exit;
                }
                if (mysqli_query($link, "DELETE FROM posts_requests WHERE Post_Id = $postId")) {
                    echo "<script>alert('Post Published Successfully!');location.replace('manage_requests.php');</script>";
                } else {
                    mysqli_query($link, "DELETE FROM posts WHERE Post_Id = $new_post_id");
                    rename($_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $new_post_id . '_request', $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $postId);
                    echo "<script>alert('Failed to delete Request!');location.replace('manage_requests.php');</script>";
                    exit;
                }
            }
        } else {
            $updateQuery = "UPDATE posts p JOIN posts_requests pr ON pr.Original_Post_Id = p.Post_Id SET p.Title = pr.Title, p.Description = pr.Description, p.Body = pr.Body, p.Cover_Photo = pr.Cover_Photo, p.Media = pr.Media, p.Links = pr.Links, p.Author = pr.Author, p.Author_Type = pr.Author_Type, p.Posted_On = pr.Posted_On, p.Remarks = '$remarks' WHERE p.Post_Id = $original_post_id;";

            // Deleting Existing Post Media Folder
            $folderPath = $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $original_post_id;
            if (is_dir($folderPath)) {
                $files = array_diff(scandir($folderPath), ['.', '..']);
                foreach ($files as $file) {
                    $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
                    unlink($filePath); // Delete file
                }
                rmdir($folderPath);
            }

            // Renaming Request Folder to Post Folder
            if (!rename($_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $postId . '_request', $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $original_post_id)) {
                echo "<script>alert('Request Folder Rename Failed!');location.replace('manage_requests.php');</script>";
                exit;
            }
            if (mysqli_query($link, $updateQuery)) {
                if (mysqli_query($link, "DELETE FROM posts_requests WHERE Post_Id = $postId")) {
                    echo "<script>alert('Post Published Successfully!');location.replace('manage_requests.php');</script>";
                } else {
                    echo "<script>alert('Failed to delete Request!');location.replace('manage_requests.php');</script>";
                }
            } else {
                echo "<script>alert('Failed to Update Posts Table!');location.replace('manage_requests.php');</script>";
            }
            exit;
        }
    }

    if (isset($_POST['update'])) {
        if (!can('update', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to update this post');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        // === EDIT POST ===
        $postId = intval($_POST['Post_Id']);
        $title = mysqli_real_escape_string($link, trim($_POST['Title']));
        $desc = mysqli_real_escape_string($link, trim($_POST['Description']));
        $body = mysqli_real_escape_string($link, trim($_POST['Body']));
        //$postedOn = date("Y-m-d H:i:s"); // optional update timestamp

        if (!$title || !$desc || !$body) {
            echo "<script>alert('Title, Description and Body are mandatory.');
              location.replace('manage_requests.php');</script>";
            exit;
        }

        // Fetch existing post info
        $res = mysqli_query($link, "SELECT * FROM posts_requests WHERE Post_Id=$postId");
        $post = mysqli_fetch_assoc($res);
        if (!$post) {
            echo "<script>alert('Post not found.');
              location.replace('manage_requests.php');</script>";
            exit;
        }

        $postFolder = createPostFolder($postId, '_request'); // Your function to create folder

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

        // Update record in DB
        $query = "UPDATE posts_requests SET 
                Title = '{$title}',
                Description = '{$desc}',
                Body = '{$body}',
                Cover_Photo = '{$coverPhoto}',
                Media = '{$mediaJson}',
                Links = '{$linksStr}'
                WHERE Post_Id = {$postId}";

        $update = mysqli_query($link, $query);
        if ($update) {
            $remarks = mysqli_real_escape_string($link, trim($_POST['Remarks']));
            acceptPost($link, $postId, $remarks);
            //echo "<script>alert('Post updated successfully.'); location.replace('manage_requests.php');</script>";
        } else {
            echo "<script>alert('Failed to update post.'); location.replace('manage_requests.php');</script>";
        }
        exit;
    }

    if (isset($_POST['accept'])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to accept this post');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $postId = intval($_POST['Post_Id']);
        $remarks = $_POST['Remarks'];
        acceptPost($link, $postId, $remarks);
    }

    if (isset($_POST['reject'])) {
        if (!can('delete', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to reject this post');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $postId = intval($_POST['Post_Id']);
        $remarks = $_POST['Remarks'];
        if (mysqli_query($link, "UPDATE posts_requests SET Status = 'Rejected',Remarks = '$remarks' WHERE Post_Id = $postId")) {
            echo "<script>alert('Post Rejected Successfully!');location.replace('manage_requests.php');</script>";
        } else {
            echo "<script>alert('Post Rejection Failed!');location.replace('manage_requests.php');</script>";
        }
    }

    if (isset($_POST['block'])) {
        if (!can('custom1', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to block this post');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $postId = intval($_POST['Post_Id']);
        $remarks = $_POST['Remarks'];
        if (mysqli_query($link, "UPDATE posts_requests SET Status = 'Blocked',Remarks = '$remarks' WHERE Post_Id = $postId")) {
            echo "<script>alert('Post Blocked Successfully!');location.replace('manage_requests.php');</script>";
        } else {
            echo "<script>alert('Post Blocking Failed!');location.replace('manage_requests.php');</script>";
        }
    }

    if (isset($_POST['fetch'])) {
        if (!can('view', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to view blog post requests');
                location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
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
        if (!empty($_POST['filterStatus'])) {
            $status = mysqli_real_escape_string($link, $_POST['filterStatus']);
            $whereClauses[] = "Status = '$status'";
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

        $sql = "SELECT * FROM posts_requests $whereSql ORDER BY Posted_On DESC";

        $res = mysqli_query($link, $sql);

        if (mysqli_num_rows($res) == 0) {
            echo "
            <tr>
                <td class='text-center' colspan='8'>No " . (isset($status) && $status ? $status : '') . " Requests!</td>
            </tr>
            ";
        }

        while ($row = mysqli_fetch_assoc($res)) {
            // Output each row - keep your existing table row HTML here
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Post_Id']) . "</td>";
            echo "<td style='width: 200px; height: 100px'><img src='" . $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $row['Post_Id'] . "_request/" . htmlspecialchars($row['Cover_Photo']) . "' class='img-thumbnail rounded' style='width: 200px; height: 100px'></td>";
            echo "<td>" . htmlspecialchars($row['Title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Author']) . "</td>";
            echo "<td>" . date("d-m-Y H:i", strtotime($row['Posted_On'])) . "</td>";
            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
            echo '<td style="height:115px;display:flex;gap:20px;align-items:center;">';

            /* ===== EDIT ===== */
            echo '<div ' . (!can('update', MENU_ID) ? 'title="You don\'t have permission to edit"' : '') . '>';

            if (can('update', MENU_ID)) {
                echo '<button class="btn btn-sm btn-warning d-flex justify-content-center align-items-center" style="width:70px;height:40px;gap:8px;" data-bs-toggle="modal" data-bs-target="#editPostModal' . $row['Post_Id'] . '">
                    <i class="bx bx-edit"></i> <span>Edit</span>
                </button>';
            } else {
                echo '<button class="btn btn-sm btn-secondary d-flex justify-content-center align-items-center" style="width:70px;height:40px;gap:8px;" disabled>
                    <i class="bx bx-edit"></i> <span>Edit</span>
                </button>';
            }
            echo '</div>';

            /* ===== ACCEPT ===== */
            echo '<div ' . (!can('create', MENU_ID) ? 'title="You don\'t have permission to accept"' : '') . '>';

            if (can('create', MENU_ID)) {
                echo '<form method="POST" action="" style="display:inline;" onsubmit="return openRemarksModal(event,' . $row['Post_Id'] . ',\'accept\');">
                    <input type="hidden" name="Post_Id" value="' . $row['Post_Id'] . '" />
                    <button type="submit" name="accept" class="btn btn-sm btn-success d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;">
                        <i class="bx bx-check-circle"></i> <span>Accept</span>
                    </button>
                </form>';
            } else {
                echo '<button class="btn btn-sm btn-secondary d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;" disabled>
                    <i class="bx bx-check-circle"></i> <span>Accept</span>
                </button>';
            }
            echo '</div>';

            /* ===== REJECT ===== */
            echo '<div ' . (!can('delete', MENU_ID) ? 'title="You don\'t have permission to reject"' : '') . '>';

            if (can('delete', MENU_ID)) {
                echo '<form method="POST" action="" style="display:inline;" onsubmit="return openRemarksModal(event,' . $row['Post_Id'] . ',\'reject\');">
                    <input type="hidden" name="Post_Id" value="' . $row['Post_Id'] . '" />
                    <button type="submit" name="reject" class="btn btn-sm btn-danger d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;">
                        <i class="bx bx-x-circle"></i> <span>Reject</span>
                    </button>
                </form>';
            } else {
                echo '<button class="btn btn-sm btn-secondary d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;" disabled>
                    <i class="bx bx-x-circle"></i> <span>Reject</span>
                </button>';
            }
            echo '</div>';

            /* ===== BLOCK ===== */
            echo '<div ' . (!can('custom1', MENU_ID) ? 'title="You don\'t have permission to block"' : '') . '>';

            if (can('custom1', MENU_ID)) {
                echo '<form method="POST" action="" style="display:inline;" onsubmit="return openRemarksModal(event,' . $row['Post_Id'] . ',\'block\');">
                    <input type="hidden" name="Post_Id" value="' . $row['Post_Id'] . '" />
                    <button type="submit" name="block" class="btn btn-sm btn-danger d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;">
                        <i class="bx bx-block"></i> <span>Block</span>
                    </button>
                </form>';
            } else {
                echo '<button class="btn btn-sm btn-secondary d-flex justify-content-center align-items-center" style="width:80px;height:40px;gap:8px;" disabled>
                    <i class="bx bx-block"></i> <span>Block</span>
                </button>';
            }
            echo '</div>
                </td>
            </tr>';
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
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
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
        <h2 class="mb-4">Manage Blog Requests</h2>

        <form id="filterForm" class="row g-3 mb-3 align-items-end">
            <div class="col-md-3">
                <label for="filterTitle" class="form-label">Search Title</label>
                <input type="text" class="form-control" id="filterTitle" name="filterTitle" placeholder="Search Title...">
            </div>
            <div class="col-md-3">
                <label for="filterAuthor" class="form-label">Search Author</label>
                <input type="text" class="form-control" id="filterAuthor" name="filterAuthor" placeholder="Search Author...">
            </div>
            <div class="col-md-3">
                <label for="filterAuthorType" class="form-label">Author Type</label>
                <select class="form-select" id="filterAuthorType" name="filterAuthorType">
                    <option value="">All</option>
                    <option value="Admin">Admin</option>
                    <option value="Faculty">Faculty</option>
                    <option value="Student">Student</option>
                    <option value="Public">Public</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterStatus" class="form-label">Status</label>
                <select class="form-select" id="filterStatus" name="filterStatus">
                    <option value="Pending">Pending</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Blocked">Blocked</option>
                    <option value="">All</option>
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsBody">

                </tbody>

            </table>
        </div>
        <?php
        $res = mysqli_query($link, "SELECT * FROM posts_requests ORDER BY Posted_On DESC");
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
                                    <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/blog/posts_images/post_<?= $row['Post_Id']; ?>_request/<?= htmlspecialchars($row['Cover_Photo']); ?>" class="img-thumbnail mb-2" style="width: 200px;height:100px" />
                                    <input type="file" name="Cover_Photo" accept="image/*" class="form-control" id="editcoverPhotoInput<?= $row['Post_Id']; ?>" onchange="handleEditCoverPhotoInput(event,<?= $row['Post_Id']; ?>)" />
                                    <div id="editcoverPhotoPreview<?= $row['Post_Id']; ?>" class="mt-2"></div>
                                </div>
                                <div class="mb-3">
                                    <label>Media (images/videos/pdfs only)</label>
                                    <div class="d-flex flex-wrap mb-2">
                                        <?php
                                        foreach ($mediaFiles as $mediaFile) {
                                            $ext = strtolower(pathinfo($mediaFile, PATHINFO_EXTENSION));
                                            $mediaPath = $_SESSION['school_db']['Media_Root_Dir'] . "/blog/posts_images/post_" . $row['Post_Id'] . "/" . htmlspecialchars($mediaFile);
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
                                    <label>Remarks <span style="color:red;">*</span></label>
                                    <textarea name="Remarks" required class="form-control" id="Remarks<?= $row['Post_Id']; ?>"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <div class="btn-wrapper"
                                    <?php if (!can('update', MENU_ID)) { ?>
                                    title="You don't have permission to update this post"
                                    <?php } ?>>
                                    <button type="submit" name="update" class="btn btn-success" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Update and Publish Post</button>
                                </div>
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

    <!-- Remarks Modal -->
    <div class="modal fade" id="remarksModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="remarksForm" action="" method="post" onsubmit="return submitRemarksForm(event)">
                    <div class="modal-header">
                        <h5 class="modal-title">Enter Remarks</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="Post_Id" id="Post_Id" value="">
                        <input type="hidden" name="Type" id="Type" value="">
                        <textarea name="Remarks" id="remarksTextarea" class="form-control" rows="5" placeholder="Enter remarks here..." required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="remarksSubmitBtn">Submit</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Scripts -->
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
        let editselectedMediaFiles = [];

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

            formData.append('Remarks', document.querySelector(`#Remarks${post_id}`).value.trim());
            // Update action flag
            formData.append('update', '1');

            try {
                const response = await fetch('manage_requests.php', {
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
        function loadFilteredPosts() {
            const params = {
                fetch: 1,
                filterTitle: $('#filterTitle').val(),
                filterAuthor: $('#filterAuthor').val(),
                filterAuthorType: $('#filterAuthorType').val(),
                filterStatus: $('#filterStatus').val(),
                filterDateFrom: $('#filterDateFrom').val(),
                filterDateTo: $('#filterDateTo').val(),
                filterSpecificDate: $('#filterSpecificDate').val(),
            };

            $.ajax({
                url: '',
                method: 'POST',
                data: params,
                beforeSend: function() {
                    $('#requestsBody').html('<p>Loading...</p>');
                },
                success: function(data) {
                    $('#requestsBody').html(data);
                },
                error: function() {
                    $('#requestsBody').html('<p>Error loading data.</p>');
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

        function openRemarksModal(event, postId, type) {
            event.preventDefault();
            document.getElementById('Post_Id').value = postId;
            document.getElementById('Type').value = type;
            document.getElementById('remarksTextarea').value = '';
            document.getElementById('remarksSubmitBtn').name = type;
            var remarksModal = new bootstrap.Modal(document.getElementById('remarksModal'));
            remarksModal.show();
            document.getElementById('remarksTextarea').focus();
        }
        // Form submission handler to confirm and validate remarks
        function submitRemarksForm(event) {
            const type = document.getElementById('Type').value;
            const remarks = document.getElementById('remarksTextarea').value.trim();

            if (!remarks) {
                alert('Please enter remarks.');
                return false;
            }

            if (!confirm('Are you sure to ' + type + ' this request?')) {
                return false;
            }
            return true;
        }
    </script>
</body>

</html>