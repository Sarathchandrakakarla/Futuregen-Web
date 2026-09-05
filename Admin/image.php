<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 94);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);

function getImageUploadDirectory($type)
{
    $directories = array(
        'Home' => '../Images/slides/',
        'Gallery' => '../Gallery/Images/',
        'Student' => '../Images/stu_img/',
        'Employee' => '../Images/emp_img/',
        'Parent_Male' => '../Images/parent_img_male/',
        'Parent_Female' => '../Images/parent_img_female/',
    );

    return isset($directories[$type]) ? $directories[$type] : null;
}

function getImageTypeFileLimit($type)
{
    $limits = array(
        'Home' => 5,
        'Gallery' => 21,
    );

    return isset($limits[$type]) ? $limits[$type] : null;
}

function outputUploadResponse($statusCode, $payload)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

if (isset($_POST['ajax_upload'])) {
    if (!can('create', MENU_ID)) {
        outputUploadResponse(403, array(
            'success' => false,
            'message' => "You don't have permission to upload images",
        ));
    }

    $type = isset($_POST['img_type']) ? trim($_POST['img_type']) : '';
    $destinationDirectory = getImageUploadDirectory($type);

    if (!$destinationDirectory) {
        outputUploadResponse(422, array(
            'success' => false,
            'message' => 'Invalid image type selected.',
        ));
    }

    if (!isset($_FILES['img']) || !is_array($_FILES['img']['name']) || count($_FILES['img']['name']) === 0) {
        outputUploadResponse(422, array(
            'success' => false,
            'message' => 'No files were received for this batch.',
        ));
    }

    $fileKeys = isset($_POST['file_keys']) && is_array($_POST['file_keys']) ? $_POST['file_keys'] : array();

    $uploadedFiles = array();
    $failedFiles = array();
    $successfulCount = 0;
    $fileCount = count($_FILES['img']['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        $originalName = isset($_FILES['img']['name'][$i]) ? $_FILES['img']['name'][$i] : 'unknown.jpg';
        $fileKey = isset($fileKeys[$i]) ? $fileKeys[$i] : '';
        $tmpName = isset($_FILES['img']['tmp_name'][$i]) ? $_FILES['img']['tmp_name'][$i] : '';
        $errorCode = isset($_FILES['img']['error'][$i]) ? (int) $_FILES['img']['error'][$i] : UPLOAD_ERR_NO_FILE;

        if ($errorCode !== UPLOAD_ERR_OK) {
            $failedFiles[] = array(
                'name' => $originalName,
                'key' => $fileKey,
                'reason' => 'Upload error code: ' . $errorCode,
            );
            continue;
        }

        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            $failedFiles[] = array(
                'name' => $originalName,
                'key' => $fileKey,
                'reason' => 'Temporary upload file is missing.',
            );
            continue;
        }

        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        if (!in_array($extension, array('jpg', 'jpeg'), true)) {
            $failedFiles[] = array(
                'name' => $originalName,
                'key' => $fileKey,
                'reason' => 'Unsupported file type.',
            );
            continue;
        }

        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $targetFileName = $baseName . '.jpg';
        $targetPath = $destinationDirectory . $targetFileName;

        if (move_uploaded_file($tmpName, $targetPath)) {
            $uploadedFiles[] = array(
                'name' => $originalName,
                'key' => $fileKey,
                'stored_name' => $targetFileName,
            );
            $successfulCount++;
            continue;
        }

        $failedFiles[] = array(
            'name' => $originalName,
            'key' => $fileKey,
            'reason' => 'move_uploaded_file() failed.',
        );
    }

    outputUploadResponse(200, array(
        'success' => count($failedFiles) === 0,
        'message' => count($failedFiles) === 0 ? 'Batch uploaded successfully.' : 'Batch completed with some failures.',
        'uploaded_count' => $successfulCount,
        'failed_count' => count($failedFiles),
        'uploaded_files' => $uploadedFiles,
        'failed_files' => $failedFiles,
    ));
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../css/sidebar-style.css" />
    <!-- Bootstrap Links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
    body {
        height: 1000px;
    }

    #choose {
        position: relative;
        overflow: hidden;
    }

    .file {
        cursor: pointer;
        position: absolute;
        transform: scale(3);
        opacity: 0;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .img-row {
        padding: 5%;
        border: 4px dashed grey;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }

    .img-row.drag-active {
        border-color: #0d6efd;
        background-color: rgba(13, 110, 253, 0.08);
    }

    .img-container i {
        font-size: 5rem;
    }

    .selection-summary,
    .manage-panel,
    .progress-panel,
    .result-panel {
        display: none;
    }

    .selection-meta {
        font-size: 0.95rem;
    }

    .file-list {
        max-height: 320px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
    }

    .file-list-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #f1f3f5;
    }

    .file-details {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .file-preview {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 0.35rem;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
        flex-shrink: 0;
    }

    .file-preview-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .file-list-item:last-child {
        border-bottom: 0;
    }

    .file-name {
        word-break: break-word;
        min-width: 0;
    }

    .failed-file-list {
        max-height: 220px;
        overflow-y: auto;
    }

    @media screen and (max-width:600px) {
        .img-container i {
            margin-left: 150px;
            font-size: 3rem;
        }

        #choose {
            margin-left: 120px;
        }

        .btn-container {
            margin-left: 150px;
        }

        #img_type {
            width: 200px;
            margin-left: 30%;
        }

        .instruction-container {
            width: 220px;
        }
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>
    <form id="imageUploadForm" action="" method="POST" enctype="multipart/form-data">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="text-light col-lg-4 rounded">
                    <select class="form-select" id="img_type" name="img_type" aria-label="Default select example">
                        <option value="" selected disabled>-- Select Image Type --</option>
                        <option value="Home">Home Images</option>
                        <option value="Gallery">Gallery Images</option>
                        <option value="Student">Student Images</option>
                        <option value="Employee">Employee Images</option>
                        <option value="Parent_Male">Male Parent Images</option>
                        <option value="Parent_Female">Female Parent Images</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="container instruction-container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <strong style="color:red;">Don't Close or Refresh.Please Wait While Processing Till Alert!!</strong>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-lg-5">
                    <h5><strong>Instructions</strong></h5>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <label for="home">For Home Images</label>
                    <ul>
                        <li>Maximum Files = 5</li>
                        <li>File Dimensions : Width:2000px, Height: 843px</li>
                        <li>Name Convention: event1,event2</li>
                    </ul>
                </div>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-5">
                    <label for="home">For Gallery Images</label>
                    <ul>
                        <li>Maximum Files = 21</li>
                        <li>File Dimensions : Width:900px, Height: Proportional to Width</li>
                        <li>Name Convention: event1,event2</li>
                    </ul>
                </div>
            </div>
        </div>
        <?php $canCreate = can('create', MENU_ID); ?>
        <div class="container img-container">
            <div class="row img-row justify-content-center mt-5" id="dropZone">
                <div class="col-lg-3 text-center">
                    <div class="btn-wrapper <?= !$canCreate ? 'disabled-wrapper' : '' ?>"
                        <?= !$canCreate ? 'title="You don\'t have permission to upload images"' : '' ?>>
                        <i class="bx bx-image-add"></i>
                        <p class="mb-2">Drag &amp; Drop JPG files here</p>
                        <p class="mb-2">OR</p>
                        <p>
                            <button class="btn btn-primary" id="choose" type="button" <?= !$canCreate ? 'disabled' : '' ?>>Choose Files
                                <input type="file" class="file" id="fileInput" name="img[]" <?= !$canCreate ? 'disabled' : '' ?>
                                    accept=".jpg,.jpeg,image/jpeg" multiple>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container selection-summary mt-4" id="selectionSummary">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-info mb-2" id="selectionMessage">No files selected yet.</div>
                    <div class="selection-meta text-dark" id="selectionMeta"></div>
                </div>
            </div>
        </div>
        <div class="container manage-panel mt-3" id="managePanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h5 class="mb-0">Selected Files</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="toggleManageFiles">Hide</button>
                                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearAllFiles">Clear All</button>
                                </div>
                            </div>
                            <div class="file-list" id="fileList"></div>
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="previousPage">Previous</button>
                                <span id="paginationInfo" class="text-muted"></span>
                                <div class="d-flex align-items-center gap-2">
                                    <label for="goToPageInput" class="text-muted mb-0">Go to Page</label>
                                    <input type="number" class="form-control form-control-sm" id="goToPageInput" min="1" step="1" style="width: 90px;">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="goToPageButton">Go</button>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="nextPage">Next</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container btn-container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-4 text-center">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <button class="btn btn-outline-dark" type="button" id="manageFilesButton" <?= !$canCreate ? 'disabled' : '' ?>>View / Manage Selected Files</button>
                        <button class="btn btn-primary upload" type="submit" id="uploadButton" name="upload" <?= !$canCreate ? 'disabled' : '' ?>><i class="bx bx-upload"></i>Upload</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="container progress-panel mt-4" id="progressPanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 id="progressTitle" class="mb-3">Uploading Images...</h5>
                            <div class="progress">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <div class="mt-3" id="progressText">0 / 0 processed | 0 uploaded</div>
                            <div class="text-muted small mt-2" id="progressBatchText"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container result-panel mt-4 mb-5" id="resultPanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 id="resultTitle" class="mb-3">Upload Result</h5>
                            <div id="resultSummary"></div>
                            <div class="mt-3 failed-file-list" id="failedFileList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(function() {
            const canCreate = <?= $canCreate ? 'true' : 'false' ?>;
            const batchSize = 15;
            const pageSize = 50;
            const selectedFiles = [];
            const selectedFileKeys = new Set();
            let totalSelectedCount = 0;
            let totalInvalidRejectedCount = 0;
            let currentPage = 1;
            let isUploading = false;

            const limitsByType = {
                Home: <?= (int) getImageTypeFileLimit('Home') ?>,
                Gallery: <?= (int) getImageTypeFileLimit('Gallery') ?>
            };

            const $form = $('#imageUploadForm');
            const $fileInput = $('#fileInput');
            const $imgType = $('#img_type');
            const $dropZone = $('#dropZone');
            const $selectionSummary = $('#selectionSummary');
            const $selectionMessage = $('#selectionMessage');
            const $selectionMeta = $('#selectionMeta');
            const $managePanel = $('#managePanel');
            const $manageFilesButton = $('#manageFilesButton');
            const $toggleManageFiles = $('#toggleManageFiles');
            const $fileList = $('#fileList');
            const $paginationInfo = $('#paginationInfo');
            const $previousPage = $('#previousPage');
            const $nextPage = $('#nextPage');
            const $goToPageInput = $('#goToPageInput');
            const $goToPageButton = $('#goToPageButton');
            const $clearAllFiles = $('#clearAllFiles');
            const $uploadButton = $('#uploadButton');
            const $progressPanel = $('#progressPanel');
            const $progressBar = $('#progressBar');
            const $progressText = $('#progressText');
            const $progressBatchText = $('#progressBatchText');
            const $resultPanel = $('#resultPanel');
            const $resultTitle = $('#resultTitle');
            const $resultSummary = $('#resultSummary');
            const $failedFileList = $('#failedFileList');
            let activePreviewUrls = [];

            function getFileKey(file) {
                return [file.name, file.size, file.lastModified, file.type].join('::');
            }

            function formatBytes(bytes) {
                if (!bytes) {
                    return '0 Bytes';
                }

                const units = ['Bytes', 'KB', 'MB', 'GB'];
                const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                const value = bytes / Math.pow(1024, unitIndex);
                return value.toFixed(unitIndex === 0 ? 0 : 2) + ' ' + units[unitIndex];
            }

            function getCurrentLimit() {
                const type = $imgType.val();
                return Object.prototype.hasOwnProperty.call(limitsByType, type) ? limitsByType[type] : null;
            }

            function revokeActivePreviewUrls() {
                activePreviewUrls.forEach(function(url) {
                    URL.revokeObjectURL(url);
                });
                activePreviewUrls = [];
            }

            function getTotalPages() {
                return Math.max(1, Math.ceil(selectedFiles.length / pageSize));
            }

            function syncCurrentPage() {
                const totalPages = getTotalPages();
                currentPage = Math.min(currentPage, totalPages);
                currentPage = Math.max(currentPage, 1);
                return totalPages;
            }

            function renderSelectionSummary(message, stats) {
                const totalSize = selectedFiles.reduce(function(sum, file) {
                    return sum + file.size;
                }, 0);
                const parts = [
                    'Total selected: ' + totalSelectedCount,
                    'Total size: ' + formatBytes(totalSize),
                    'Accepted: ' + selectedFiles.length
                ];

                if (stats.duplicates > 0) {
                    parts.push('Duplicates ignored: ' + stats.duplicates);
                }
                if (totalInvalidRejectedCount > 0) {
                    parts.push('Invalid files rejected: ' + totalInvalidRejectedCount);
                }
                if (stats.limitRejected > 0) {
                    parts.push('Rejected by limit: ' + stats.limitRejected);
                }

                $selectionMessage
                    .removeClass('alert-info alert-warning alert-success')
                    .addClass(selectedFiles.length ? 'alert-success' : 'alert-info')
                    .text(message);
                $selectionMeta.text(parts.join(' | '));
                $selectionSummary.show();
            }

            function renderFileList() {
                revokeActivePreviewUrls();

                const totalFiles = selectedFiles.length;
                const totalPages = syncCurrentPage();

                if (!totalFiles) {
                    $fileList.html('<div class="p-3 text-muted">No files selected.</div>');
                    $paginationInfo.text('0 files');
                    $previousPage.prop('disabled', true);
                    $nextPage.prop('disabled', true);
                    $goToPageInput.val('').attr('max', 1).prop('disabled', true);
                    $goToPageButton.prop('disabled', true);
                    return;
                }

                const start = (currentPage - 1) * pageSize;
                const visibleFiles = selectedFiles.slice(start, start + pageSize);
                const rows = visibleFiles.map(function(file) {
                    let previewMarkup = '<div class="file-preview file-preview-fallback">JPG</div>';

                    try {
                        const previewUrl = URL.createObjectURL(file);
                        activePreviewUrls.push(previewUrl);
                        previewMarkup = '<img src="' + escapeAttribute(previewUrl) + '" alt="' + escapeAttribute(file.name) + '" class="file-preview" loading="lazy">';
                    } catch (error) {
                        previewMarkup = '<div class="file-preview file-preview-fallback">JPG</div>';
                    }

                    return '<div class="file-list-item">' +
                        '<div class="file-details">' +
                        previewMarkup +
                        '<span class="file-name">' + escapeHtml(file.name) + '</span>' +
                        '</div>' +
                        '<button type="button" class="btn btn-outline-danger btn-sm remove-file" data-key="' + escapeAttribute(file.key) + '">Remove</button>' +
                        '</div>';
                });

                $fileList.html(rows.join(''));
                $paginationInfo.text('Page ' + currentPage + ' of ' + totalPages + ' | ' + totalFiles + ' files');
                $previousPage.prop('disabled', currentPage === 1);
                $nextPage.prop('disabled', currentPage === totalPages);
                $goToPageInput.val(currentPage).attr('max', totalPages).prop('disabled', false);
                $goToPageButton.prop('disabled', false);
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function escapeAttribute(value) {
                return escapeHtml(value);
            }

            function updateManageVisibility() {
                const hasFiles = selectedFiles.length > 0;
                $manageFilesButton.prop('disabled', !hasFiles || isUploading || !canCreate);
                $clearAllFiles.prop('disabled', !hasFiles || isUploading);
            }

            function showSelectionState(message, stats) {
                renderSelectionSummary(message, stats);
                renderFileList();
                updateManageVisibility();
            }

            function addFiles(fileList) {
                const stats = {
                    duplicates: 0,
                    limitRejected: 0
                };

                const limit = getCurrentLimit();
                const incomingFiles = Array.from(fileList || []);
                totalSelectedCount += incomingFiles.length;

                incomingFiles.forEach(function(file) {
                    const isJpeg = /image\/jpeg/i.test(file.type) || /\.(jpe?g)$/i.test(file.name);

                    if (!isJpeg) {
                        totalInvalidRejectedCount++;
                        return;
                    }

                    const key = getFileKey(file);
                    if (selectedFileKeys.has(key)) {
                        stats.duplicates++;
                        return;
                    }

                    if (limit !== null && selectedFiles.length >= limit) {
                        stats.limitRejected++;
                        return;
                    }

                    file.key = key;
                    selectedFiles.push(file);
                    selectedFileKeys.add(key);
                });

                currentPage = Math.max(1, Math.ceil(selectedFiles.length / pageSize));

                const message = selectedFiles.length ?
                    selectedFiles.length + ' image' + (selectedFiles.length === 1 ? '' : 's') + ' selected' :
                    'No valid files selected.';

                showSelectionState(message, stats);

                if (limit !== null && selectedFiles.length >= limit && stats.limitRejected > 0) {
                    alert('Only ' + limit + ' files are allowed for ' + $imgType.val() + '.');
                }
            }

            function removeFileByKey(key) {
                const index = selectedFiles.findIndex(function(file) {
                    return file.key === key;
                });

                if (index === -1) {
                    return;
                }

                selectedFileKeys.delete(selectedFiles[index].key);
                selectedFiles.splice(index, 1);

                showSelectionState(
                    selectedFiles.length ?
                    selectedFiles.length + ' image' + (selectedFiles.length === 1 ? '' : 's') + ' selected' :
                    'No files selected yet.', {
                        duplicates: 0,
                        limitRejected: 0
                    }
                );
            }

            function clearAllFiles() {
                revokeActivePreviewUrls();
                selectedFiles.length = 0;
                selectedFileKeys.clear();
                totalSelectedCount = 0;
                totalInvalidRejectedCount = 0;
                currentPage = 1;
                showSelectionState('No files selected yet.', {
                    duplicates: 0,
                    limitRejected: 0
                });
                $managePanel.hide();
            }

            function replaceSelectedFiles(files, message) {
                revokeActivePreviewUrls();
                selectedFiles.length = 0;
                selectedFileKeys.clear();

                files.forEach(function(file) {
                    if (!file.key) {
                        file.key = getFileKey(file);
                    }

                    selectedFiles.push(file);
                    selectedFileKeys.add(file.key);
                });

                currentPage = 1;
                $fileInput.val('');
                showSelectionState(message, {
                    duplicates: 0,
                    limitRejected: 0
                });
            }

            function resetAfterSuccessfulUpload() {
                revokeActivePreviewUrls();
                selectedFiles.length = 0;
                selectedFileKeys.clear();
                totalSelectedCount = 0;
                totalInvalidRejectedCount = 0;
                currentPage = 1;
                $fileInput.val('');
                $managePanel.hide();
                $fileList.empty();
                showSelectionState('No files selected yet.', {
                    duplicates: 0,
                    limitRejected: 0
                });
                $progressPanel.hide();
            }

            function retainFailedFilesForRetry(failedBatchFiles) {
                replaceSelectedFiles(
                    failedBatchFiles,
                    failedBatchFiles.length ?
                    failedBatchFiles.length + ' image' + (failedBatchFiles.length === 1 ? '' : 's') + ' remaining for retry' :
                    'No files selected yet.'
                );
                $progressPanel.hide();
            }

            function validateBeforeUpload() {
                if (!canCreate) {
                    alert("You don't have permission to upload images");
                    return false;
                }

                if (isUploading) {
                    alert('An upload is already in progress.');
                    return false;
                }

                if (!$imgType.val()) {
                    alert('Select Image Type');
                    return false;
                }

                if (!selectedFiles.length) {
                    alert('Select at least one JPG/JPEG image before uploading.');
                    return false;
                }

                const limit = getCurrentLimit();
                if (limit !== null && selectedFiles.length > limit) {
                    alert('You are only allowed to upload a maximum of ' + limit + ' files');
                    return false;
                }

                return true;
            }

            function setUploadingState(uploading) {
                isUploading = uploading;
                $uploadButton.prop('disabled', uploading || !canCreate);
                $imgType.prop('disabled', uploading);
                $fileInput.prop('disabled', uploading || !canCreate);
                $manageFilesButton.prop('disabled', uploading || !selectedFiles.length || !canCreate);
                $clearAllFiles.prop('disabled', uploading || !selectedFiles.length);
                $('.remove-file').prop('disabled', uploading);
            }

            function updateProgress(processedCount, successCount, totalCount, batchNumber, totalBatches) {
                const percent = totalCount ? ((processedCount / totalCount) * 100) : 0;
                const safePercent = Math.min(100, Math.round(percent * 10) / 10);

                $progressBar.css('width', safePercent + '%').text(safePercent + '%');
                $progressText.text(processedCount + ' / ' + totalCount + ' processed | ' + successCount + ' uploaded');
                $progressBatchText.text('Batch ' + batchNumber + ' of ' + totalBatches);
            }

            async function uploadInBatches() {
                const filesToUpload = selectedFiles.slice();
                const totalFiles = filesToUpload.length;
                const totalBatches = Math.ceil(totalFiles / batchSize);
                let processedCount = 0;
                let confirmedCount = 0;
                const failedFiles = [];
                const retryFiles = [];
                const retryFileKeys = new Set();

                $resultPanel.hide();
                $progressPanel.show();
                updateProgress(0, 0, totalFiles, 0, totalBatches);
                setUploadingState(true);

                function queueRetryFile(file) {
                    if (!file || retryFileKeys.has(file.key)) {
                        return;
                    }

                    retryFiles.push(file);
                    retryFileKeys.add(file.key);
                }

                function buildBatchFileMap(batchFiles) {
                    const fileMap = new Map();

                    batchFiles.forEach(function(file) {
                        fileMap.set(file.key, file);
                    });

                    return fileMap;
                }

                function recordFailedBatchFiles(batchFiles, batchFailedFiles, fallbackReason) {
                    const fileMap = buildBatchFileMap(batchFiles);

                    if (Array.isArray(batchFailedFiles) && batchFailedFiles.length) {
                        batchFailedFiles.forEach(function(item) {
                            const matchedFile = item && item.key ? fileMap.get(item.key) : null;

                            if (matchedFile) {
                                queueRetryFile(matchedFile);
                            }

                            failedFiles.push({
                                name: item && item.name ? item.name : (matchedFile ? matchedFile.name : 'unknown.jpg'),
                                reason: item && item.reason ? item.reason : fallbackReason
                            });
                        });
                        return;
                    }

                    batchFiles.forEach(function(file) {
                        queueRetryFile(file);
                        failedFiles.push({
                            name: file.name,
                            reason: fallbackReason
                        });
                    });
                }

                try {
                    for (let batchIndex = 0; batchIndex < totalBatches; batchIndex++) {
                        const start = batchIndex * batchSize;
                        const batchFiles = filesToUpload.slice(start, start + batchSize);
                        const formData = new FormData();

                        formData.append('ajax_upload', '1');
                        formData.append('img_type', $imgType.val());
                        formData.append('batch_number', String(batchIndex + 1));
                        formData.append('batch_size', String(batchSize));

                        batchFiles.forEach(function(file) {
                            formData.append('img[]', file, file.name);
                            formData.append('file_keys[]', file.key);
                        });

                        let response;

                        try {
                            response = await fetch(window.location.href, {
                                method: 'POST',
                                body: formData,
                                credentials: 'same-origin'
                            });
                        } catch (error) {
                            recordFailedBatchFiles(batchFiles, [], 'Network/request failure.');
                            processedCount += batchFiles.length;
                            updateProgress(processedCount, confirmedCount, totalFiles, batchIndex + 1, totalBatches);
                            continue;
                        }

                        let data;
                        try {
                            data = await response.json();
                        } catch (error) {
                            recordFailedBatchFiles(batchFiles, [], 'Unexpected server response.');
                            processedCount += batchFiles.length;
                            updateProgress(processedCount, confirmedCount, totalFiles, batchIndex + 1, totalBatches);
                            continue;
                        }

                        if (!response.ok || !data || typeof data.uploaded_count === 'undefined') {
                            const reason = data && data.message ? data.message : 'Batch request failed.';
                            recordFailedBatchFiles(batchFiles, data && Array.isArray(data.failed_files) ? data.failed_files : [], reason);
                            processedCount += batchFiles.length;
                            updateProgress(processedCount, confirmedCount, totalFiles, batchIndex + 1, totalBatches);
                            continue;
                        }

                        confirmedCount += Number(data.uploaded_count) || 0;
                        processedCount += batchFiles.length;

                        if (Array.isArray(data.failed_files) && data.failed_files.length) {
                            recordFailedBatchFiles(batchFiles, data.failed_files, 'Upload failed.');
                        }

                        updateProgress(processedCount, confirmedCount, totalFiles, batchIndex + 1, totalBatches);
                    }
                } finally {
                    setUploadingState(false);
                }

                updateProgress(processedCount, confirmedCount, totalFiles, totalBatches, totalBatches);
                renderFinalResult(confirmedCount, totalFiles, failedFiles);

                if (failedFiles.length === 0 && confirmedCount === totalFiles) {
                    resetAfterSuccessfulUpload();
                } else if (failedFiles.length > 0) {
                    retainFailedFilesForRetry(retryFiles);
                }
            }

            function renderFinalResult(successCount, totalCount, failedFiles) {
                const failedCount = failedFiles.length;
                const allSuccessful = failedCount === 0 && successCount === totalCount;

                $resultTitle.text(allSuccessful ? 'Upload Completed' : 'Upload Completed With Issues');
                $resultSummary.html(
                    '<div class="alert ' + (allSuccessful ? 'alert-success' : 'alert-warning') + '">' +
                    successCount + ' / ' + totalCount + ' images uploaded successfully' +
                    (failedCount ? '<br>' + failedCount + ' image' + (failedCount === 1 ? '' : 's') + ' failed' : '') +
                    '</div>'
                );

                if (failedCount) {
                    const failedMarkup = failedFiles.map(function(file) {
                        return '<div class="border rounded p-2 mb-2">' +
                            '<strong>' + escapeHtml(file.name) + '</strong><br>' +
                            '<span class="text-muted">' + escapeHtml(file.reason) + '</span>' +
                            '</div>';
                    }).join('');
                    $failedFileList.html(failedMarkup);
                } else {
                    $failedFileList.empty();
                }

                $resultPanel.show();
            }

            $fileInput.on('change', function(event) {
                addFiles(event.target.files);
                event.target.value = '';
            });

            $dropZone.on('dragenter dragover', function(event) {
                event.preventDefault();
                event.stopPropagation();
                if (!isUploading && canCreate) {
                    $dropZone.addClass('drag-active');
                }
            });

            $dropZone.on('dragleave dragend drop', function(event) {
                event.preventDefault();
                event.stopPropagation();
                $dropZone.removeClass('drag-active');
            });

            $dropZone.on('drop', function(event) {
                if (isUploading || !canCreate) {
                    return;
                }

                const files = event.originalEvent.dataTransfer ? event.originalEvent.dataTransfer.files : [];
                addFiles(files);
            });

            $manageFilesButton.on('click', function() {
                if (!selectedFiles.length) {
                    return;
                }
                renderFileList();
                $managePanel.toggle();
            });

            $toggleManageFiles.on('click', function() {
                $managePanel.hide();
            });

            $clearAllFiles.on('click', function() {
                if (isUploading || !selectedFiles.length) {
                    return;
                }

                clearAllFiles();
            });

            $previousPage.on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    renderFileList();
                }
            });

            $nextPage.on('click', function() {
                const totalPages = getTotalPages();
                if (currentPage < totalPages) {
                    currentPage++;
                    renderFileList();
                }
            });

            function goToPage() {
                const totalPages = getTotalPages();
                const requestedPage = parseInt($goToPageInput.val(), 10);

                if (Number.isNaN(requestedPage) || requestedPage < 1 || requestedPage > totalPages) {
                    alert('Enter a page number between 1 and ' + totalPages + '.');
                    $goToPageInput.val(currentPage);
                    return;
                }

                currentPage = requestedPage;
                renderFileList();
            }

            $goToPageButton.on('click', function() {
                if (!selectedFiles.length) {
                    return;
                }

                goToPage();
            });

            $goToPageInput.on('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();

                    if (!selectedFiles.length) {
                        return;
                    }

                    goToPage();
                }
            });

            $fileList.on('click', '.remove-file', function() {
                if (isUploading) {
                    return;
                }
                removeFileByKey($(this).data('key'));
            });

            $imgType.on('change', function() {
                const limit = getCurrentLimit();
                if (limit !== null && selectedFiles.length > limit) {
                    alert('Current selection exceeds the limit for ' + $imgType.val() + '. Remove extra files before uploading.');
                }
            });

            $form.on('submit', async function(event) {
                event.preventDefault();

                if (!validateBeforeUpload()) {
                    return;
                }

                await uploadInBatches();
            });

            showSelectionState('No files selected yet.', {
                duplicates: 0,
                limitRejected: 0
            });
            $progressPanel.hide();
            $resultPanel.hide();
            updateManageVisibility();

            $(window).on('beforeunload', function() {
                revokeActivePreviewUrls();
            });
        });
    </script>
</body>

</html>
