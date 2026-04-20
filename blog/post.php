<?php
include '../link.php';
if (!isset($_SESSION['school_db'])) {
    header('Location: /Victory/Welcome/preindex.php');
    exit;
}
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />

    <!-- Links for Carousel -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous" />
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Magnific Popup CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" />

    <!-- jQuery is required -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Magnific Popup JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

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
        background-color: whitesmoke;
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
        }

        ul li .sub-menu li {
            padding: 0 100px 0 0;
            margin-left: 30px;
        }
    }

    .not-found {
        max-width: 417px;
        mix-blend-mode: multiply;
    }

    @media screen and (max-width:574px) {
        .not-found {
            max-width: 252px;
        }

        .media-container img {
            margin-bottom: 5%;
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
            <li><a href="/Victory/Gallery/gallery.php">Gallery</a></li>
            <li><a href="<?= $_SESSION['school_db']['Root_Dir'] ?>/contact.php">Contact</a></li>
            <li><a href="/Victory/youtube.php" id="link">Our Stories</a></li>
            <li><a class="active" href="/Victory/blog/blog_index.php" id="link">Our Blog</a></li>
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

    <div class="container">
        <?php
        $post_id = $_GET['id'] ?? null;

        // Fetch post only if id is present
        $post = null;
        if ($post_id) {
            $query = mysqli_query($link, "SELECT * FROM `posts` WHERE Post_Id = " . intval($post_id));
            if ($query && mysqli_num_rows($query) > 0) {
                $post = mysqli_fetch_assoc($query);
            }
        }

        if (!$post) {
            // Reusable "Not Found" block
            echo '
            <div class="container my-5">
                <div class="text-center">
                    <img src="' . $_SESSION['school_db']['Media_Root_Dir'] . '/blog/not_found.jpg" alt="Not Found" class="img-fluid not-found mb-4">
                    <h2 class="fw-bold">Post Not Found</h2>
                    <p class="text-muted mb-4">
                        The post you are looking for doesn\'t exist or may have been removed.
                    </p>
                    <a href="blog_index.php" class="btn btn-primary">← Back to Blog</a>
                </div>
            </div>';
        } else {
            $author = htmlspecialchars($post['Author']);
            if (str_contains($post['Author'], 'VHST')) {
                $name = mysqli_fetch_row(mysqli_query($link, "SELECT First_Name FROM `student_master_data` WHERE Id_No = '$author'"))[0];
                $author .= ', ' . $name;
            } else if (str_contains($post['Author'], 'VHEM')) {
                $name = mysqli_fetch_row(mysqli_query($link, "SELECT Emp_First_Name FROM `employee_master_data` WHERE Emp_Id = '$author'"))[0];
                $author .= ', ' . $name;
            }
        ?>
            <div class="container my-5">
                <!-- Title -->
                <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($post['Title']); ?></h1>

                <!-- Meta -->
                <div class="text-muted mb-4">
                    By <span class="fw-semibold"><?php echo $author; ?></span>
                    | <?php echo date("F j, Y g:i A", strtotime($post['Posted_On'])); ?>
                </div>


                <!-- Description -->
                <p class="lead" style="text-align: justify;"><?php echo nl2br(htmlspecialchars($post['Description'])); ?></p>

                <?php
                if (!empty($post['Cover_Photo'])) {
                    echo '
                    <div class="mb-4 text-center">
                        <img src="' . $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $post_id . '/' . htmlspecialchars(trim($post['Cover_Photo'])) . '" 
                            class="img-fluid rounded shadow-sm w-100" 
                            alt="Cover Photo"
                            style="max-height: 400px; object-fit: contain;">
                    </div>';
                }
                ?>

                <!-- Body -->
                <div class="post-body mb-5" style="text-align:justify;">
                    <?php echo nl2br($post['Body']); ?>
                </div>

                <!-- Media  -->
                <?php
                $mediaJson = $post['Media'] ?? '[]';
                $media = json_decode($mediaJson, true);
                /* if (!empty($media) && is_array($media)) {
                    $count = count($media);
                    echo '<div class="row g-3 mb-4 magnific-gallery">';
                    foreach ($media as $file) {
                        $file = trim($file);
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $colClass = ($count == 1 ? 12 : ($count == 2 ? 6 : ($count == 3 ? 4 : 3)));

                        echo '<div class="col-12 col-md-' . $colClass . ' media-container">';

                        $filePath = '../Images/blog/posts_images/post_' . $post_id . '/' . htmlspecialchars($file);

                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                            // Image preview
                            //echo '<img src="' . $filePath . '" class="img-fluid rounded shadow-sm" alt="Post Image">';
                            echo "<a href='{$filePath}' class='popup-link' title='" . basename($file) . "'>";
                            echo "<img src='{$filePath}' class='img-fluid rounded shadow-sm' alt='Post Image'>";
                            echo "</a>";
                        } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                            // Video preview
                            echo '<video class="img-fluid rounded shadow-sm" controls>
                    <source src="' . $filePath . '" type="video/' . $ext . '">
                    Your browser does not support the video tag.
                  </video>';
                        } elseif ($ext === 'pdf') {
                            // PDF icon preview with link
                            echo '<a href="' . $filePath . '" target="_blank" class="d-flex flex-column align-items-center text-decoration-none" title="Open PDF">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; color: #d9534f;"></i>
                    <span class="mt-2 text-truncate" style="max-width: 100%;">' . basename($file) . '</span>
                  </a>';
                        } else {
                            // Fallback: link to file
                            echo '<a href="' . $filePath . '" target="_blank">' . htmlspecialchars(basename($file)) . '</a>';
                        }

                        echo '</div>';
                    }
                    echo '</div>';
                } */
                ?>
                <?php if (!empty($media)): ?>
                    <div class="row g-3 mb-4 magnific-gallery">
                        <?php
                        $count = count($media);
                        foreach ($media as $file):
                            $file = trim($file);
                            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $colClass = ($count == 1) ? 'col-12' : (($count == 2) ? 'col-6' : (($count == 3) ? 'col-4' : 'col-3'));
                            $fileUrl = $_SESSION['school_db']['Media_Root_Dir'] . '/blog/posts_images/post_' . $post_id . '/' . htmlspecialchars($file);

                            echo "<div class='{$colClass}'>";

                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'])) {
                                // Image
                                echo "<a href='{$fileUrl}' class='popup-link' title='" . basename($file) . "'>";
                                echo "<img src='{$fileUrl}' class='img-fluid rounded shadow-sm' alt='Post Image'>";
                                echo "</a>";
                            } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                                // Video
                                echo "<a href='{$fileUrl}' class='popup-link popup-video' title='" . basename($file) . "'>";
                                echo "<video class='img-fluid rounded shadow-sm' controls preload='metadata' style='max-width:100%; cursor: pointer;'>";
                                echo "<source src='{$fileUrl}' type='video/{$ext}'>";
                                echo "Your browser does not support the video tag.";
                                echo "</video>";
                                echo "</a>";
                            } elseif ($ext === 'pdf') {
                                // PDF: link to open in new tab (popup not supported)
                                echo "<a href='{$fileUrl}' target='_blank' class='d-block text-center text-decoration-none popup-pdf' title='" . basename($file) . "'>";
                                echo "<i class='bi bi-file-earmark-pdf' style='font-size: 3rem; color: #d9534f;'></i><br>";
                                echo "<small>" . basename($file) . "</small>";
                                echo "</a>";
                            } else {
                                // Other files: provide download link
                                echo "<a href='{$fileUrl}' target='_blank'>" . basename($file) . "</a>";
                            }

                            echo "</div>";
                        endforeach;
                        ?>
                    </div>
                <?php endif; ?>



                <!-- References / Further Reading -->
                <?php
                $links = !empty($post['Links']) ? explode(",", $post['Links']) : [];
                if (!empty($links)) {
                    echo '<div class="references mb-5">';
                    echo '<h5 class="fw-bold">Further Reading</h5>';
                    echo '<ul class="list-unstyled">';
                    foreach ($links as $linkItem) {
                        $linkItem = trim($linkItem);
                        if (!empty($linkItem)) {
                            echo '<li class="mb-2">
                    <span class="me-2">•</span>
                    <a href="' . htmlspecialchars($linkItem) . '" target="_blank" rel="noopener noreferrer">
                        ' . htmlspecialchars($linkItem) . '
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                  </li>';
                        }
                    }
                    echo '</ul></div>';
                }
                ?>


                <!-- Back button -->
                <a href="blog_index.php" class="btn btn-outline-primary">← Back to Blog</a>
            </div>
        <?php
        }
        ?>

    </div>

    <footer class="bg-dark text-light py-3 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> <?= (isset($_SESSION['school_db']) && isset($_SESSION['school_db']['footer_msg'])) ? $_SESSION['school_db']['footer_msg'] : ''; ?>. All Rights Reserved.</p>
            <small>
                Developed by
                <a href="https://sarathtechgenics.netlify.app" target="_blank" class="text-info">Sarath Techgenics</a>
            </small>
        </div>
    </footer>

    <!-- Magnificpopup -->
    <script>
        $(document).ready(function() {
            $('.magnific-gallery').magnificPopup({
                delegate: 'a', // all anchors inside gallery
                type: 'image', // default type
                gallery: {
                    enabled: true
                },
                callbacks: {
                    elementParse: function(item) {
                        if (item.el.hasClass('popup-video')) {
                            item.type = 'iframe'; // load video in iframe
                        } else if (item.el.hasClass('popup-pdf')) {
                            item.type = 'iframe'; // load PDF in iframe too
                            item.iframe = {
                                patterns: {
                                    pdf: {
                                        index: '.pdf',
                                        src: '%id%',
                                    }
                                }
                            };
                        } else {
                            item.type = 'image';
                        }
                    }
                },
                closeBtnInside: true,
                showCloseBtn: true,
                closeOnContentClick: false
            });
        });
    </script>

</body>

</html>