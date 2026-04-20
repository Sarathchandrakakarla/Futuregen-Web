<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 96);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>

<?php

$apiKey = 'AIzaSyDCqN_8pQmJsghZF3Zc4U9dx_N_nS1wuFs';

if (isset($_POST['add'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert youtube videos');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    if ($_POST['Video_Id']) {
        $video_id = $_POST['Video_Id'];
        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?id=$video_id&key=$apiKey&part=snippet,contentDetails,statistics";
        $check_sql = mysqli_query($link, "SELECT * FROM `youtube` WHERE Video_Id = '$video_id'");
        $response = file_get_contents($apiUrl);
        if ($response) {
            $data = json_decode($response, true);

            if (isset($data['items'][0])) {
                $video = $data['items'][0];

                // Extracting video details
                $title = $video['snippet']['title'];
                $publishedAt = $video['snippet']['publishedAt'];
                $publishedAt = new DateTime($publishedAt);
                $publishedAt = $publishedAt->format('d-m-Y H:i:s');
                if (mysqli_num_rows($check_sql) > 0) {
                    echo "<script>alert('Video Already Exists!!')</script>";
                } else {
                    $sql = mysqli_query($link, "INSERT INTO `youtube`(Video_Id,Video_Title,Published_Date) VALUES('$video_id','$title','$publishedAt')");

                    if ($sql) {
                        echo "<script>alert('New Video Inserted Successfully!!')</script>";
                    } else {
                        echo "<script>alert('New Video Insertion Failed!!')</script>";
                    }
                }
            } else {
                echo "<script>alert('No data found for the video ID.');</script>";
            }
        } else {
            echo "<script>alert('Error fetching data.');</script>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Tangerine" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    #inp,
    #add-btn {
        position: relative;
    }

    @keyframes mymove {
        from {
            opacity: 0;
            left: -100px;
        }

        to {
            left: 0;
            opacity: 1;
        }
    }

    @keyframes myrevmove {
        from {
            opacity: 1;
            left: 0;
        }

        to {
            left: -100px;
            opacity: 0;
        }
    }

    .delete {
        cursor: pointer;
        font-size: 20px;
        color: red;
    }

    .edit,
    .preview {
        cursor: pointer;
        font-size: 20px;
    }

    .close-btn {
        cursor: pointer;
        font-size: 20px;
        width: 30px;
        height: 30px;
        border-radius: 10%;
        background-color: #f00;
        border-color: transparent;
        color: #fff;
    }

    /* Wrapper controls cursor + tooltip */
    .btn-wrapper {
        display: inline-block;
    }

    /* Disabled state on wrapper */
    .btn-wrapper.disabled {
        cursor: not-allowed;
    }

    /* Icon appearance */
    .btn-wrapper i {
        font-size: 18px;
        cursor: pointer;
    }

    /* Disabled icon */
    .btn-wrapper.disabled i {
        opacity: 0.4;
        color: grey;
        pointer-events: none;
        /* blocks click */
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>
    <form action="" method="POST">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-2">
                    <label for=""><b>Add New Video</b></label>
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert youtube videos"
                        <?php } ?>>
                        <button class="btn btn-primary" style="border-radius: 50%;" id="plus" onclick="reveal();return false;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>> <i class="bx bx-plus" id="plus-icon"></i> </button>
                    </div>
                </div>
                <div class="col-lg-3">
                    <input type="text" class="form-control" id="inp" name="Video_Id" placeholder="Enter Video Id" value="<?php if (isset($video_id)) {
                                                                                                                                echo $video_id;
                                                                                                                            } else {
                                                                                                                                echo '';
                                                                                                                            } ?>" style="opacity: 0;">
                </div>
                <div class="col-lg-1">
                    <div class="btn-wrapper"
                        <?php if (!can('create', MENU_ID)) { ?>
                        title="You don't have permission to insert youtube videos"
                        <?php } ?>>
                        <button class="btn btn-warning" name="add" id="add-btn" style="opacity: 0;" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?>>Insert</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="reset" onclick="hideTable()">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Youtube Videos Report</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-striped table-hover" border="1">
            <thead class="bg-secondary text-light">
                <tr>
                    <th style="padding:5px;">S.No</th>
                    <th style="padding:5px;">Video Id</th>
                    <th style="padding:5px;">Video Title</th>
                    <th style="padding:5px;">Action</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                if (isset($_POST['show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $sql = "SELECT * FROM `youtube`";
                    $result = mysqli_query($link, $sql);
                    $i = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo '<tr>
                                    <td style="padding:5px;">' . $i . '</td>
                                    <td style="padding:5px;">' . $row['Video_Id'] . '</td>
                                    <td style="padding:5px;">' . $row['Video_Title'] . '</td>
                                    <td style="padding:5px;">';

                        /* ===== EDIT VIDEO ===== */
                        $canEdit = can('update', MENU_ID);
                        echo '<div class="btn-wrapper ' . (!$canEdit ? 'disabled' : '') . '" ' .
                            (!$canEdit ? 'title="You don\'t have permissioFn to edit videos"' : '') . '>
                                <i class="bx bx-edit edit"
                                data-allowed="' . ($canEdit ? '1' : '0') . '"></i>
                            </div>';

                        /* ===== PREVIEW VIDEO ===== */
                        $canView = can('view', MENU_ID);
                        echo '<div class="btn-wrapper ' . (!$canView ? 'disabled' : '') . '" ' .
                            (!$canView ? 'title="You don\'t have permission to view videos"' : '') . '>
                                <i class="bx bx-file-find preview"
                                data-allowed="' . ($canView ? '1' : '0') . '"></i>
                            </div>';

                        /* ===== DELETE VIDEO ===== */
                        $canDelete = can('delete', MENU_ID);
                        echo '<div class="btn-wrapper ' . (!$canDelete ? 'disabled' : '') . '" ' .
                            (!$canDelete ? 'title="You don\'t have permission to delete videos"' : '') . '>
                                <i class="bx bx-trash delete"
                                data-allowed="' . ($canDelete ? '1' : '0') . '"></i>
                            </div>';

                        echo '</td></tr>';

                        $i++;
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="modal" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Video Title</h5>
                    <button type="button" class="close-btn" aria-label="Close" onclick="$('#modal').modal('hide')">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="video_id">
                    <input type="text" class="form-control" id="video_title" required>
                </div>
                <div class="modal-footer">
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to update youtube videos"
                        <?php } ?>>
                        <button type="button" class="btn btn-primary" onclick="edit_title(document.getElementById('video_id').value,document.getElementById('video_title').value)" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Save changes</button>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="$('#modal').modal('hide')">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" tabindex="-1" role="dialog" id="modal1" aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Preview Video</h5>
                    <button type="button" class="close-btn" aria-label="Close" onclick="$('#modal1').modal('hide')">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe
                        src=""
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen
                        width="470"
                        height="300"
                        id="modal-video-player"></iframe>
                </div>
            </div>
        </div>
    </div>
    <iframe name="print_frame" width="0" height="0" frameborder="0" src="about:blank"></iframe>

    <!-- Scripts -->

    <!-- Global Const Variables for can_insert,can_update,can_soft_delete,can_hard_delete -->
    <script>
        const CAN_UPDATE = <?= can('update', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_DELETE = <?= can('delete', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_VIEW = <?= can('view', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <!-- Revealing Text Box -->
    <script type="text/javascript">
        function reveal() {
            button = document.getElementById('plus-icon');
            if (!button.classList.contains('open')) {
                button.style.transform = 'rotate(45deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "mymove 1s ease-in 1";
                txtinp.style.opacity = 1;

                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "mymove 1s ease-in 1";
                txtbtn.style.opacity = 1;
            } else {
                button.style.transform = 'rotate(90deg)';

                txtinp = document.getElementById('inp')
                txtinp.style.animation = "myrevmove 1s ease-out 1";


                txtbtn = document.getElementById('add-btn')
                txtbtn.style.animation = "myrevmove 1s ease-out 1";

                txtinp.style.opacity = 0;
                txtbtn.style.opacity = 0;
            }
            button.classList.toggle('open');
        }
    </script>

    <!-- Delete, Edit, Preview Row -->
    <script type="text/javascript">
        $(".delete").click(function(e) {
            if (!CAN_DELETE) {
                e.preventDefault();
                alert("You do not have permission to delete video");
                return;
            }
            video_id = $(this).parent().siblings().eq(1).text();
            if (!confirm('Confirm to delete Video: ' + video_id + '?')) {
                return;
            } else {
                $.ajax({
                    type: 'post',
                    url: 'temp.php',
                    data: {
                        Action: "Delete_Video",
                        Video_Id: video_id
                    },
                    success: function(data) {
                        alert('Video Deleted Successfully!! Refresh to get data updated!')
                    }
                });
            }
        });

        $(".edit").click(function(e) {
            if (!CAN_UPDATE) {
                e.preventDefault();
                alert("You do not have permission to edit video");
                return;
            }
            video_id = $(this).parent().siblings().eq(1).text();
            video_title = $(this).parent().siblings().eq(2).text();
            document.getElementById("video_id").value = video_id;
            document.getElementById("video_title").value = video_title;
            $('#modal').modal("show");
        });

        function edit_title(id, title) {
            console.log(id, title)
            $.ajax({
                type: 'post',
                url: 'temp.php',
                data: {
                    Action: "Edit_Title",
                    Video_Id: id,
                    Video_Title: title
                },
                success: function(data) {
                    console.log(data);
                    alert('Video Title Updated Successfully!! Refresh to get data updated!')
                    $('#modal').modal("hide")
                }
            });
        }
        $(".preview").click(function(e) {
            if (!CAN_VIEW) {
                e.preventDefault();
                alert("You do not have permission to preview video");
                return;
            }
            video_id = $(this).parent().parent().siblings().eq(1).text();
            video_title = $(this).parent().siblings().eq(2).text();
            document.getElementById("modal-video-player").src = "https://www.youtube.com/embed/" + video_id
            $("#modal1").modal("show");
        });
    </script>

    <!-- Print Table -->
    <script type="text/javascript">
        function printDiv() {
            window.frames["print_frame"].document.body.innerHTML = "<h2 style='text-align:center;'><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h2>";
            window.frames["print_frame"].document.body.innerHTML += document.querySelector('.table-container').innerHTML;
            window.frames["print_frame"].window.focus();
            window.frames["print_frame"].window.print();
        }
    </script>
</body>

</html>