<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 94);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
$disclosureMode = isset($_GET['mode']) && $_GET['mode'] === 'disclosures';

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

function getDisclosureById($db, $id)
{
    $stmt = mysqli_prepare($db, 'SELECT id, category, title, files, display_order, status FROM mandatory_disclosures WHERE id = ?');
    if (!$stmt) return null;
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return null;
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    if ($row) {
        $row['files'] = json_decode($row['files'], true);
        if (!is_array($row['files'])) $row['files'] = array();
    }
    return $row;
}

function validDisclosureFilename($filename)
{
    return is_string($filename) && preg_match('/^[A-Za-z0-9_-]+\.(pdf|jpg|jpeg|png)$/i', $filename) === 1;
}

function normalizeDisclosureFiles($files)
{
    if (!is_array($files)) return array();
    $normalized = array();
    foreach ($files as $entry) {
        if (is_string($entry)) {
            $normalized[] = array('file' => $entry, 'thumbnail' => null);
        } elseif (is_array($entry) && isset($entry['file']) && is_string($entry['file'])) {
            $normalized[] = array('file' => $entry['file'], 'thumbnail' => isset($entry['thumbnail']) && $entry['thumbnail'] !== '' ? $entry['thumbnail'] : null);
        }
    }
    return $normalized;
}

function disclosureExtension($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

function saveDisclosureUpload($upload, $destination)
{
    return isset($upload['error'], $upload['tmp_name']) && (int)$upload['error'] === UPLOAD_ERR_OK && $upload['tmp_name'] !== '' && is_uploaded_file($upload['tmp_name']) && move_uploaded_file($upload['tmp_name'], $destination);
}

$disclosureAction = isset($_POST['disclosure_action']) ? $_POST['disclosure_action'] : '';
if ($disclosureAction !== '') {
    $permission = in_array($disclosureAction, array('disclosure_delete', 'disclosure_delete_file'), true) ? 'delete' : 'update';
    if (!in_array($disclosureAction, array('disclosure_update', 'disclosure_delete_file', 'disclosure_delete', 'disclosure_reorder', 'disclosure_reorder_files', 'disclosure_replace_thumbnail'), true)) {
        outputUploadResponse(400, array('success' => false, 'message' => 'Unknown disclosure action.'));
    }
    if (!can($permission, MENU_ID)) {
        outputUploadResponse(403, array('success' => false, 'message' => 'You do not have permission to perform this action.'));
    }

    if ($disclosureAction === 'disclosure_reorder') {
        $orders = json_decode(isset($_POST['orders']) ? $_POST['orders'] : '', true);
        $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
        if (!is_array($orders) || !$orders || $category === '') outputUploadResponse(422, array('success' => false, 'message' => 'Invalid disclosure order.'));
        $ids = array();
        foreach ($orders as $position => $item) {
            $orderedId = is_array($item) && isset($item['id']) ? filter_var($item['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
            if (!is_array($item) || $orderedId === false || !isset($item['display_order']) || (int)$item['display_order'] !== $position) {
                outputUploadResponse(422, array('success' => false, 'message' => 'Invalid disclosure order.'));
            }
            $ids[] = (int)$orderedId;
        }
        if (count(array_unique($ids)) !== count($ids)) outputUploadResponse(422, array('success' => false, 'message' => 'Duplicate disclosure IDs are not allowed.'));
        $check = mysqli_prepare($link, 'SELECT category, status FROM mandatory_disclosures WHERE id = ?');
        if (!$check) outputUploadResponse(500, array('success' => false, 'message' => 'Could not validate disclosure order.'));
        foreach ($ids as $idToCheck) {
            mysqli_stmt_bind_param($check, 'i', $idToCheck);
            if (!mysqli_stmt_execute($check)) {
                mysqli_stmt_close($check);
                outputUploadResponse(500, array('success' => false, 'message' => 'Could not validate disclosure order.'));
            }
            $checkResult = mysqli_stmt_get_result($check);
            $checkRow = $checkResult ? mysqli_fetch_assoc($checkResult) : null;
            if (!$checkRow || (int)$checkRow['status'] !== 1 || $checkRow['category'] !== $category) {
                mysqli_stmt_close($check);
                outputUploadResponse(422, array('success' => false, 'message' => 'All disclosures must be active and belong to the same category.'));
            }
        }
        mysqli_stmt_close($check);
        $categoryStmt = mysqli_prepare($link, 'SELECT id FROM mandatory_disclosures WHERE category = ? AND status = 1');
        if (!$categoryStmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not validate disclosure order.'));
        mysqli_stmt_bind_param($categoryStmt, 's', $category);
        mysqli_stmt_execute($categoryStmt);
        $categoryResult = mysqli_stmt_get_result($categoryStmt);
        $categoryIds = array();
        if ($categoryResult) while ($categoryRow = mysqli_fetch_assoc($categoryResult)) $categoryIds[] = (int)$categoryRow['id'];
        mysqli_stmt_close($categoryStmt);
        $submittedIds = $ids;
        sort($categoryIds, SORT_NUMERIC);
        sort($submittedIds, SORT_NUMERIC);
        if ($submittedIds !== $categoryIds) outputUploadResponse(422, array('success' => false, 'message' => 'The order must include every active disclosure in this category exactly once.'));
        if (!mysqli_begin_transaction($link)) outputUploadResponse(500, array('success' => false, 'message' => 'Could not begin saving the order.'));
        $update = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET display_order = ? WHERE id = ? AND category = ? AND status = 1');
        if (!$update) {
            mysqli_rollback($link);
            outputUploadResponse(500, array('success' => false, 'message' => 'Could not save the order.'));
        }
        foreach ($ids as $position => $orderedId) {
            mysqli_stmt_bind_param($update, 'iis', $position, $orderedId, $category);
            if (!mysqli_stmt_execute($update) || mysqli_stmt_affected_rows($update) < 0) {
                mysqli_stmt_close($update);
                mysqli_rollback($link);
                outputUploadResponse(500, array('success' => false, 'message' => 'Could not save the order.'));
            }
        }
        mysqli_stmt_close($update);
        if (!mysqli_commit($link)) {
            mysqli_rollback($link);
            outputUploadResponse(500, array('success' => false, 'message' => 'Could not save the order.'));
        }
        outputUploadResponse(200, array('success' => true, 'message' => 'Order updated successfully.'));
    }

    if ($disclosureAction === 'disclosure_reorder_files') {
        $id = filter_var(isset($_POST['disclosure_id']) ? $_POST['disclosure_id'] : null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
        if ($id === false || $id === null) outputUploadResponse(422, array('success' => false, 'message' => 'Invalid disclosure ID.'));
        $record = getDisclosureById($link, (int)$id);
        if (!$record) outputUploadResponse(404, array('success' => false, 'message' => 'Disclosure not found.'));
        $orderedFiles = json_decode(isset($_POST['files']) ? $_POST['files'] : '', true);
        if (!is_array($orderedFiles) || count($orderedFiles) !== count($record['files'])) outputUploadResponse(422, array('success' => false, 'message' => 'The submitted order must include every existing file exactly once.'));
        $existingFiles = normalizeDisclosureFiles($record['files']);
        $existingByName = array();
        foreach ($existingFiles as $item) $existingByName[$item['file']] = $item;
        $reorderedFiles = array();
        foreach ($orderedFiles as $identifier) {
            $filename = is_array($identifier) && isset($identifier['file']) ? $identifier['file'] : $identifier;
            if (!validDisclosureFilename($filename) || !isset($existingByName[$filename])) outputUploadResponse(422, array('success' => false, 'message' => 'Invalid or unknown file in order.'));
            $reorderedFiles[] = $existingByName[$filename];
        }
        $submittedNames = array_map(function ($item) {
            return $item['file'];
        }, $reorderedFiles);
        if (count(array_unique($submittedNames)) !== count($submittedNames) || count($existingByName) !== count($existingFiles)) outputUploadResponse(422, array('success' => false, 'message' => 'Duplicate files are not allowed.'));
        $existingNames = array_keys($existingByName);
        $checkNames = $submittedNames;
        sort($existingNames, SORT_STRING);
        sort($checkNames, SORT_STRING);
        if ($existingNames !== $checkNames) outputUploadResponse(422, array('success' => false, 'message' => 'The file order contains unknown or missing files.'));
        $jsonFiles = json_encode(array_values($reorderedFiles));
        $update = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET files = ? WHERE id = ?');
        if (!$update) outputUploadResponse(500, array('success' => false, 'message' => 'Could not prepare file order update.'));
        mysqli_stmt_bind_param($update, 'si', $jsonFiles, $id);
        $success = mysqli_stmt_execute($update);
        mysqli_stmt_close($update);
        outputUploadResponse($success ? 200 : 500, array('success' => $success, 'message' => $success ? 'File order updated successfully.' : 'Could not save file order.'));
    }

    $id = filter_var(isset($_POST['disclosure_id']) ? $_POST['disclosure_id'] : null, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));
    if ($id === false || $id === null) outputUploadResponse(422, array('success' => false, 'message' => 'Invalid disclosure ID.'));
    $record = getDisclosureById($link, (int)$id);
    if (!$record) outputUploadResponse(404, array('success' => false, 'message' => 'Disclosure not found.'));
    $disclosuresDirectory = __DIR__ . '/../Images/mandatory_disclosures/';
    $directory = $disclosuresDirectory . (int)$id;
    if (is_link($disclosuresDirectory) || is_link($directory)) {
        outputUploadResponse(500, array('success' => false, 'message' => 'Disclosure file storage is unavailable.'));
    }

    if ($disclosureAction === 'disclosure_update') {
        $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
        $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
        if ($category === '' || $title === '' || strlen($category) > 255 || strlen($title) > 255) {
            outputUploadResponse(422, array('success' => false, 'message' => 'Category and title are required and must be at most 255 characters.'));
        }
        $stmt = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET category = ?, title = ? WHERE id = ?');
        if (!$stmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not update disclosure.'));
        mysqli_stmt_bind_param($stmt, 'ssi', $category, $title, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        outputUploadResponse($success ? 200 : 500, array('success' => $success, 'message' => $success ? 'Disclosure updated.' : 'Could not update disclosure.'));
    }

    if ($disclosureAction === 'disclosure_replace_thumbnail') {
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $normalized = normalizeDisclosureFiles($record['files']);
        $position = null;
        foreach ($normalized as $index => $item) if ($item['file'] === $filename) {
            $position = $index;
            break;
        }
        if ($position === null || disclosureExtension($filename) !== 'pdf' || !isset($_FILES['thumbnail'])) outputUploadResponse(422, array('success' => false, 'message' => 'Invalid PDF thumbnail request.'));
        $upload = $_FILES['thumbnail'];
        $extension = disclosureExtension($upload['name']);
        $imageInfo = isset($upload['tmp_name']) && is_uploaded_file($upload['tmp_name']) ? @getimagesize($upload['tmp_name']) : false;
        if ((int)$upload['error'] !== UPLOAD_ERR_OK || !in_array($extension, array('jpg', 'jpeg', 'png'), true) || !$imageInfo || !in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) outputUploadResponse(422, array('success' => false, 'message' => 'Choose a valid JPG, JPEG or PNG thumbnail.'));
        $base = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($upload['name'], PATHINFO_FILENAME)), '_');
        if ($base === '') $base = 'thumbnail';
        $newThumbnail = $base . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
        if (!saveDisclosureUpload($upload, $directory . '/' . $newThumbnail)) outputUploadResponse(500, array('success' => false, 'message' => 'Could not store replacement thumbnail.'));
        $oldThumbnail = $normalized[$position]['thumbnail'];
        $normalized[$position]['thumbnail'] = $newThumbnail;
        $jsonFiles = json_encode($normalized);
        $stmt = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET files = ? WHERE id = ?');
        if (!$stmt) {
            @unlink($directory . '/' . $newThumbnail);
            outputUploadResponse(500, array('success' => false, 'message' => 'Could not update thumbnail metadata.'));
        }
        mysqli_stmt_bind_param($stmt, 'si', $jsonFiles, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if (!$success) {
            @unlink($directory . '/' . $newThumbnail);
            outputUploadResponse(500, array('success' => false, 'message' => 'Could not update thumbnail metadata.'));
        }
        if ($oldThumbnail && validDisclosureFilename($oldThumbnail) && is_file($directory . '/' . $oldThumbnail) && !is_link($directory . '/' . $oldThumbnail)) @unlink($directory . '/' . $oldThumbnail);
        outputUploadResponse(200, array('success' => true, 'message' => 'Thumbnail replaced.'));
    }

    if ($disclosureAction === 'disclosure_delete_file') {
        $filename = isset($_POST['filename']) ? $_POST['filename'] : '';
        $normalized = normalizeDisclosureFiles($record['files']);
        $target = null;
        foreach ($normalized as $item) if ($item['file'] === $filename) {
            $target = $item;
            break;
        }
        if (!validDisclosureFilename($filename) || !$target) {
            outputUploadResponse(422, array('success' => false, 'message' => 'Invalid file for this disclosure.'));
        }
        if (count($record['files']) <= 1) {
            outputUploadResponse(422, array('success' => false, 'message' => 'This is the last file. Delete the complete disclosure instead.'));
        }
        foreach (array($filename, $target['thumbnail']) as $deleteName) {
            if (!$deleteName || !validDisclosureFilename($deleteName)) continue;
            $filePath = $directory . '/' . $deleteName;
            if ((file_exists($filePath) || is_link($filePath)) && (!is_file($filePath) || is_link($filePath) || !unlink($filePath))) outputUploadResponse(500, array('success' => false, 'message' => 'Could not delete this file.'));
        }
        $newFiles = array_values(array_filter($normalized, function ($file) use ($filename) {
            return $file['file'] !== $filename;
        }));
        $jsonFiles = json_encode($newFiles);
        $stmt = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET files = ? WHERE id = ?');
        if (!$stmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not update disclosure files.'));
        mysqli_stmt_bind_param($stmt, 'si', $jsonFiles, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        outputUploadResponse($success ? 200 : 500, array('success' => $success, 'message' => $success ? 'File deleted.' : 'Could not update disclosure files.'));
    }

    // Delete only direct files within this disclosure's ID directory.
    if (is_dir($directory)) {
        $entries = scandir($directory);
        if ($entries === false) outputUploadResponse(500, array('success' => false, 'message' => 'Could not read disclosure files.'));
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $path = $directory . '/' . $entry;
            if (is_link($path) || (!is_file($path) && !is_dir($path)) || is_dir($path) || !unlink($path)) {
                outputUploadResponse(500, array('success' => false, 'message' => 'Could not remove disclosure files.'));
            }
        }
        if (!rmdir($directory)) outputUploadResponse(500, array('success' => false, 'message' => 'Could not remove disclosure folder.'));
    }
    $stmt = mysqli_prepare($link, 'DELETE FROM mandatory_disclosures WHERE id = ?');
    if (!$stmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not delete disclosure.'));
    mysqli_stmt_bind_param($stmt, 'i', $id);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    outputUploadResponse($success ? 200 : 500, array('success' => $success, 'message' => $success ? 'Disclosure deleted.' : 'Could not delete disclosure.'));
}

// Isolated endpoint for Mandatory Disclosures; existing image upload handling below is unchanged.
if (isset($_POST['disclosure_upload'])) {
    if (!can('create', MENU_ID)) {
        outputUploadResponse(403, array('success' => false, 'message' => "You don't have permission to upload disclosures."));
    }
    $category = trim(isset($_POST['category']) ? $_POST['category'] : '');
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $disclosureId = isset($_POST['disclosure_id']) ? (int) $_POST['disclosure_id'] : 0;
    if ($disclosureId === 0 && ($category === '' || $title === '')) {
        outputUploadResponse(422, array('success' => false, 'message' => 'Category and title are required.'));
    }
    $uploadItems = json_decode(isset($_POST['disclosure_items']) ? $_POST['disclosure_items'] : '', true);
    if (!is_array($uploadItems) || !$uploadItems) {
        outputUploadResponse(422, array('success' => false, 'message' => 'No files were received.'));
    }
    $validatedItems = array();
    $seenUploadKeys = array();
    $expectedUploadFields = array();
    foreach ($uploadItems as $item) {
        if (!is_array($item) || !isset($item['key']) || !preg_match('/^[A-Za-z0-9_-]{1,80}$/', $item['key'])) outputUploadResponse(422, array('success' => false, 'message' => 'Malformed file pairing metadata.'));
        if (isset($seenUploadKeys[$item['key']])) outputUploadResponse(422, array('success' => false, 'message' => 'Duplicate file pairing metadata.'));
        $seenUploadKeys[$item['key']] = true;
        $fileKey = 'document_' . $item['key'];
        $thumbKey = 'thumbnail_' . $item['key'];
        $expectedUploadFields[$fileKey] = true;
        if (!isset($_FILES[$fileKey])) outputUploadResponse(422, array('success' => false, 'message' => 'A disclosure file is missing.'));
        $document = $_FILES[$fileKey];
        $extension = disclosureExtension($document['name']);
        if ((int)$document['error'] !== UPLOAD_ERR_OK || !isset($document['tmp_name']) || !is_uploaded_file($document['tmp_name']) || !in_array($extension, array('pdf', 'jpg', 'jpeg', 'png'), true)) outputUploadResponse(422, array('success' => false, 'message' => 'Invalid disclosure file.'));
        $thumbnail = null;
        if ($extension === 'pdf') {
            if (!isset($_FILES[$thumbKey])) outputUploadResponse(422, array('success' => false, 'message' => 'Every PDF requires its own thumbnail.'));
            $expectedUploadFields[$thumbKey] = true;
            $thumbnail = $_FILES[$thumbKey];
            $thumbExtension = disclosureExtension($thumbnail['name']);
            $imageInfo = isset($thumbnail['tmp_name']) && is_uploaded_file($thumbnail['tmp_name']) ? @getimagesize($thumbnail['tmp_name']) : false;
            if ((int)$thumbnail['error'] !== UPLOAD_ERR_OK || !in_array($thumbExtension, array('jpg', 'jpeg', 'png'), true) || !$imageInfo || !in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) outputUploadResponse(422, array('success' => false, 'message' => 'Every PDF requires a valid JPG, JPEG or PNG thumbnail.'));
            $pdfHeader = @file_get_contents($document['tmp_name'], false, null, 0, 5);
            if ($pdfHeader !== '%PDF-') outputUploadResponse(422, array('success' => false, 'message' => 'The selected PDF is not a valid PDF file.'));
        } else {
            if (isset($_FILES[$thumbKey])) outputUploadResponse(422, array('success' => false, 'message' => 'Image documents must not include a separate thumbnail.'));
            $imageInfo = @getimagesize($document['tmp_name']);
            if (!$imageInfo || !in_array($imageInfo[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG), true)) outputUploadResponse(422, array('success' => false, 'message' => 'The selected image is invalid.'));
        }
        $validatedItems[] = array('document' => $document, 'extension' => $extension, 'thumbnail' => $thumbnail);
    }
    foreach (array_keys($_FILES) as $fieldName) {
        if ((strpos($fieldName, 'document_') === 0 || strpos($fieldName, 'thumbnail_') === 0) && !isset($expectedUploadFields[$fieldName])) outputUploadResponse(422, array('success' => false, 'message' => 'Unexpected file pairing metadata.'));
    }
    if ($disclosureId === 0) {
        $orderStmt = mysqli_prepare($link, 'SELECT COALESCE(MAX(display_order), -1) + 1 FROM mandatory_disclosures WHERE category = ?');
        if (!$orderStmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not determine disclosure order.'));
        mysqli_stmt_bind_param($orderStmt, 's', $category);
        mysqli_stmt_execute($orderStmt);
        mysqli_stmt_bind_result($orderStmt, $newDisplayOrder);
        mysqli_stmt_fetch($orderStmt);
        mysqli_stmt_close($orderStmt);
        $newDisplayOrder = (int)$newDisplayOrder;
        $stmt = mysqli_prepare($link, 'INSERT INTO mandatory_disclosures (category, title, files, display_order, status) VALUES (?, ?, ?, ?, 1)');
        if (!$stmt) outputUploadResponse(500, array('success' => false, 'message' => 'Could not prepare disclosure record. Create the table using Admin/mandatory_disclosures.sql.'));
        $emptyFiles = '[]';
        mysqli_stmt_bind_param($stmt, 'sssi', $category, $title, $emptyFiles, $newDisplayOrder);
        if (!mysqli_stmt_execute($stmt)) outputUploadResponse(500, array('success' => false, 'message' => 'Could not create disclosure record. Create the table using Admin/mandatory_disclosures.sql.'));
        $disclosureId = mysqli_insert_id($link);
        mysqli_stmt_close($stmt);
    } else {
        $stmt = mysqli_prepare($link, 'SELECT id FROM mandatory_disclosures WHERE id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $disclosureId);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_stmt_get_result($stmt);
        $found = $exists && mysqli_num_rows($exists) > 0;
        mysqli_stmt_close($stmt);
        if (!$found) outputUploadResponse(404, array('success' => false, 'message' => 'Disclosure record not found for retry.'));
    }
    $disclosuresDirectory = __DIR__ . '/../Images/mandatory_disclosures/';
    $relativeDir = $disclosuresDirectory . $disclosureId;
    if (is_link($disclosuresDirectory) || is_link($relativeDir)) {
        outputUploadResponse(500, array('success' => false, 'disclosure_id' => $disclosureId, 'message' => 'Disclosure file storage is unavailable.'));
    }
    if (!is_dir($relativeDir) && !mkdir($relativeDir, 0755, true)) {
        outputUploadResponse(500, array('success' => false, 'disclosure_id' => $disclosureId, 'message' => 'Could not create disclosure directory.'));
    }
    $stmt = mysqli_prepare($link, 'SELECT files FROM mandatory_disclosures WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $disclosureId);
    mysqli_stmt_execute($stmt);
    $fileResult = mysqli_stmt_get_result($stmt);
    $storedFiles = $fileResult ? json_decode(mysqli_fetch_assoc($fileResult)['files'], true) : array();
    mysqli_stmt_close($stmt);
    $storedFiles = normalizeDisclosureFiles($storedFiles);
    $uploaded = 0;
    $batchStoredFiles = array();
    foreach ($validatedItems as $item) {
        $document = $item['document'];
        $safeBase = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($document['name'], PATHINFO_FILENAME)), '_');
        if ($safeBase === '') $safeBase = 'document';
        $storedName = $safeBase . '_' . bin2hex(random_bytes(5)) . '.' . $item['extension'];
        if (!saveDisclosureUpload($document, $relativeDir . '/' . $storedName)) {
            foreach ($batchStoredFiles as $savedName) @unlink($relativeDir . '/' . $savedName);
            outputUploadResponse(500, array('success' => false, 'disclosure_id' => $disclosureId, 'message' => 'Could not store disclosure files.'));
        }
        $batchStoredFiles[] = $storedName;
        $storedThumbnail = null;
        if ($item['thumbnail']) {
            $thumb = $item['thumbnail'];
            $thumbExt = disclosureExtension($thumb['name']);
            $thumbBase = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', pathinfo($thumb['name'], PATHINFO_FILENAME)), '_');
            if ($thumbBase === '') $thumbBase = 'thumbnail';
            $storedThumbnail = $thumbBase . '_' . bin2hex(random_bytes(5)) . '.' . $thumbExt;
            if (!saveDisclosureUpload($thumb, $relativeDir . '/' . $storedThumbnail)) {
                foreach ($batchStoredFiles as $savedName) @unlink($relativeDir . '/' . $savedName);
                outputUploadResponse(500, array('success' => false, 'disclosure_id' => $disclosureId, 'message' => 'Could not store PDF thumbnail.'));
            }
            $batchStoredFiles[] = $storedThumbnail;
        }
        $storedFiles[] = array('file' => $storedName, 'thumbnail' => $storedThumbnail);
        $uploaded++;
    }
    $jsonFiles = json_encode(array_values($storedFiles));
    $stmt = mysqli_prepare($link, 'UPDATE mandatory_disclosures SET files = ? WHERE id = ?');
    mysqli_stmt_bind_param($stmt, 'si', $jsonFiles, $disclosureId);
    $saved = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if (!$saved) {
        foreach ($batchStoredFiles as $storedName) {
            @unlink($relativeDir . '/' . $storedName);
        }
        $uploaded = 0;
    }
    outputUploadResponse($saved ? 200 : 500, array(
        'success' => $saved,
        'disclosure_id' => $disclosureId,
        'uploaded_count' => $uploaded,
        'failed_files' => array(),
        'message' => $saved ? 'Disclosure batch processed.' : 'Files uploaded but disclosure metadata could not be saved.'
    ));
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
    <?php if ($disclosureMode): ?>
        <?php $canCreate = can('create', MENU_ID); ?>
        <div class="container py-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="text-dark mb-0">Mandatory Disclosures</h2>
                <a class="btn btn-outline-dark" href="image.php">Image Upload</a>
            </div>
            <div class="card">
                <div class="card-body">
                    <form id="disclosureForm">
                        <div class="mb-3"><label class="form-label" for="disclosureCategory">Category</label><input class="form-control" id="disclosureCategory" maxlength="255" required></div>
                        <div class="mb-3"><label class="form-label" for="disclosureTitle">Document Title</label><input class="form-control" id="disclosureTitle" maxlength="255" required></div>
                        <div class="mb-3">
                            <label class="form-label">Disclosure files (PDF / JPG / JPEG / PNG)</label>
                            <div id="disclosureDrop" class="border border-2 border-secondary rounded p-5 text-center">
                                <p>Drag and drop files here, or</p>
                                <input class="form-control" type="file" id="disclosureInput" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple <?= !$canCreate ? 'disabled' : '' ?>>
                            </div>
                        </div>
                        <div id="disclosureItems" class="list-group mb-3"></div>
                        <div class="d-flex gap-2 mb-2"><strong>Selected items</strong><button class="btn btn-sm btn-outline-danger" type="button" id="disclosureClear">Clear all</button></div>
                        <div id="disclosureStatus" class="alert alert-info">No files selected yet.</div>
                        <button class="btn btn-primary" id="disclosureUpload" type="submit" <?= !$canCreate ? 'disabled' : '' ?>>Upload Disclosure</button>
                        <a class="btn btn-secondary" href="image.php">Back to Image Upload</a>
                    </form>
                </div>
            </div>
            <div class="card mt-4">
                <div class="card-body">
                    <h4 class="mb-3">Existing Mandatory Disclosures</h4>
                    <?php
                    $disclosureRows = mysqli_query($link, 'SELECT id, category, title, files, display_order, status FROM mandatory_disclosures WHERE status = 1 ORDER BY category ASC, display_order ASC, id ASC');
                    $disclosuresByCategory = array();
                    if ($disclosureRows) {
                        while ($disclosureRow = mysqli_fetch_assoc($disclosureRows)) {
                            $decodedFiles = json_decode($disclosureRow['files'], true);
                            $disclosureRow['files'] = is_array($decodedFiles) ? $decodedFiles : array();
                            $disclosuresByCategory[$disclosureRow['category']][] = $disclosureRow;
                        }
                    }
                    if (!$disclosuresByCategory): ?>
                        <p class="text-muted mb-0">No active disclosures yet.</p>
                        <?php else: foreach ($disclosuresByCategory as $categoryName => $categoryDisclosures): ?>
                            <h5 class="mt-3"><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></h5>
                            <div class="table-responsive">
                                <table class="table table-striped align-middle">
                                    <thead>
                                        <tr>
                                            <th>Order</th>
                                            <th>Document Title</th>
                                            <th>Files</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="disclosure-order-list" data-category="<?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?>">
                                        <?php foreach ($categoryDisclosures as $disclosureRow): ?>
                                            <tr data-id="<?= (int)$disclosureRow['id'] ?>">
                                                <td><button type="button" class="btn btn-sm btn-light disclosure-drag-handle" draggable="true" aria-label="Drag to reorder">↕</button></td>
                                                <td><?= htmlspecialchars($disclosureRow['title'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= count($disclosureRow['files']) ?></td>
                                                <td class="text-nowrap">
                                                    <?php if (can('update', MENU_ID)): ?>
                                                        <button type="button" class="btn btn-sm btn-primary disclosure-edit" data-id="<?= (int)$disclosureRow['id'] ?>" data-category="<?= htmlspecialchars($disclosureRow['category'], ENT_QUOTES, 'UTF-8') ?>" data-title="<?= htmlspecialchars($disclosureRow['title'], ENT_QUOTES, 'UTF-8') ?>" data-files="<?= htmlspecialchars(json_encode($disclosureRow['files']), ENT_QUOTES, 'UTF-8') ?>">Edit</button>
                                                    <?php endif; ?>
                                                    <?php if (can('delete', MENU_ID)): ?>
                                                        <button type="button" class="btn btn-sm btn-danger disclosure-delete" data-id="<?= (int)$disclosureRow['id'] ?>">Delete</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="small text-muted disclosure-order-status" aria-live="polite"></div>
                    <?php endforeach;
                    endif; ?>
                </div>
            </div>
            <div class="card mt-4 d-none" id="disclosureEditPanel">
                <div class="card-body">
                    <h4>Edit Disclosure</h4>
                    <div class="mb-3"><label class="form-label" for="editCategory">Category</label><input class="form-control" id="editCategory" maxlength="255"></div>
                    <div class="mb-3"><label class="form-label" for="editTitle">Document Title</label><input class="form-control" id="editTitle" maxlength="255"></div>
                    <h5>Existing Files</h5>
                    <p class="small text-muted">Drag files to change their display order.</p>
                    <div id="editExistingFiles" class="list-group mb-3"></div>
                    <div id="fileOrderStatus" class="small mb-2" aria-live="polite"></div>
                    <div class="mb-3"><label class="form-label" for="editAddFiles">Add More Files</label><input class="form-control" type="file" id="editAddFiles" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple <?= !$canCreate ? 'disabled' : '' ?>>
                        <div id="editNewItems" class="list-group mt-2"></div>
                    </div>
                    <div id="editStatus" class="small mb-3"></div>
                    <button type="button" class="btn btn-primary" id="saveDisclosure" <?= !can('update', MENU_ID) ? 'disabled' : '' ?>>Save Changes</button>
                    <button type="button" class="btn btn-secondary" id="cancelDisclosureEdit">Cancel</button>
                </div>
            </div>
        </div>
        <script>
            (function() {
                const input = document.getElementById('disclosureInput'),
                    status = document.getElementById('disclosureStatus'),
                    form = document.getElementById('disclosureForm');
                let files = [],
                    disclosureId = null,
                    busy = false,
                    itemCounter = 0,
                    editNewFiles = [];
                const allowed = ['pdf', 'jpg', 'jpeg', 'png'];

                function isPdf(file) { return file.name.split('.').pop().toLowerCase() === 'pdf' }
                function makeItem(file) { return {file: file, thumbnail: null, key: 'item_' + (++itemCounter) + '_' + Math.random().toString(36).slice(2)} }

                function renderItems(target, items, changed) {
                    target.innerHTML = '';
                    items.forEach((item, index) => {
                        const row = document.createElement('div'); row.className = 'list-group-item';
                        const name = document.createElement('strong'); name.textContent = item.file.name; row.appendChild(name);
                        if (isPdf(item.file)) {
                            const label = document.createElement('label'); label.className = 'small d-block mt-2'; label.textContent = 'Thumbnail (required)'; row.appendChild(label);
                            const picker = document.createElement('input'); picker.type = 'file'; picker.accept = '.jpg,.jpeg,.png,image/jpeg,image/png'; picker.className = 'form-control-file';
                            picker.onchange = () => { const selected = picker.files[0]; if (!selected) return; if (!['jpg','jpeg','png'].includes(selected.name.split('.').pop().toLowerCase())) { picker.value = ''; return; } item.thumbnail = selected; renderItems(target, items, changed); changed(); };
                            row.appendChild(picker);
                            if (item.thumbnail) { const info = document.createElement('div'); info.className = 'small text-success'; info.textContent = 'Selected: ' + item.thumbnail.name; row.appendChild(info); const preview = document.createElement('img'); preview.src = URL.createObjectURL(item.thumbnail); preview.alt = 'Thumbnail preview'; preview.style.maxWidth = '120px'; preview.style.maxHeight = '80px'; row.appendChild(preview); }
                            else { const missing = document.createElement('div'); missing.className = 'small text-danger'; missing.textContent = 'Choose a thumbnail before upload.'; row.appendChild(missing); }
                        } else { const note = document.createElement('div'); note.className = 'small text-muted'; note.textContent = 'No thumbnail required'; row.appendChild(note); }
                        const remove = document.createElement('button'); remove.type = 'button'; remove.className = 'btn btn-sm btn-outline-danger mt-2'; remove.textContent = 'Remove'; remove.onclick = () => { items.splice(index, 1); renderItems(target, items, changed); changed(); }; row.appendChild(remove); target.appendChild(row);
                    });
                }

                function render() {
                    renderItems(document.getElementById('disclosureItems'), files, () => { status.className = 'alert alert-info'; status.textContent = files.length + ' file(s) selected.'; });
                }

                function add(incoming) {
                    Array.from(incoming).forEach(file => {
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (allowed.includes(ext)) files.push(makeItem(file))
                    });
                    render();
                    status.className = 'alert alert-info';
                    status.textContent = files.length + ' file(s) selected. Total size: ' + (files.reduce((n, f) => n + f.size, 0) / 1024 / 1024).toFixed(2) + ' MB'
                }
                function addToEdit(incoming) {
                    Array.from(incoming).forEach(file => { if (allowed.includes(file.name.split('.').pop().toLowerCase())) editNewFiles.push(makeItem(file)); });
                    renderItems(document.getElementById('editNewItems'), editNewFiles, () => {});
                }
                input.addEventListener('change', e => {
                    add(e.target.files);
                    input.value = ''
                });
                const drop = document.getElementById('disclosureDrop');
                ['dragenter', 'dragover'].forEach(name => drop.addEventListener(name, e => {
                    e.preventDefault();
                    drop.classList.add('bg-dark')
                }));
                ['dragleave', 'drop'].forEach(name => drop.addEventListener(name, e => {
                    e.preventDefault();
                    drop.classList.remove('bg-dark')
                }));
                drop.addEventListener('drop', e => add(e.dataTransfer.files));
                document.getElementById('disclosureClear').onclick = () => {
                    files = [];
                    disclosureId = null;
                    render()
                };
                const editPanel = document.getElementById('disclosureEditPanel'),
                    editFiles = document.getElementById('editExistingFiles'),
                    editStatus = document.getElementById('editStatus');
                let editingId = null;
                async function sendDisclosureAction(action, values) {
                    const body = new FormData();
                    body.append('disclosure_action', action);
                    Object.keys(values).forEach(key => body.append(key, values[key]));
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body,
                        credentials: 'same-origin'
                    });
                    let result;
                    try {
                        result = await response.json()
                    } catch (e) {
                        throw new Error('Unexpected server response.')
                    }
                    if (!response.ok || !result.success) throw new Error(result.message || 'Request failed.');
                    return result
                }

                function enableDragSort(container, itemSelector, handleSelector, onDrop) {
                    let dragged = null;
                    container.addEventListener('dragstart', event => {
                        if (!event.target.closest(handleSelector)) return;
                        dragged = event.target.closest(itemSelector);
                        if (!dragged) return;
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', 'reorder');
                        dragged.classList.add('opacity-50')
                    });
                    container.addEventListener('dragover', event => {
                        if (!dragged) return;
                        event.preventDefault();
                        const target = event.target.closest(itemSelector);
                        if (!target || target === dragged) return;
                        const rect = target.getBoundingClientRect();
                        container.insertBefore(dragged, event.clientY < rect.top + rect.height / 2 ? target : target.nextSibling)
                    });
                    container.addEventListener('drop', event => {
                        if (!dragged) return;
                        event.preventDefault();
                        dragged.classList.remove('opacity-50');
                        dragged = null;
                        onDrop()
                    });
                    container.addEventListener('dragend', () => {
                        if (dragged) dragged.classList.remove('opacity-50');
                        dragged = null
                    })
                }

                function showFiles(fileItems) {
                    editFiles.innerHTML = '';
                    fileItems = fileItems.map(item => typeof item === 'string' ? {file:item, thumbnail:null} : item);
                    fileItems.forEach(item => {
                        const name = item.file;
                        const row = document.createElement('div');
                        row.className = 'list-group-item d-flex justify-content-between align-items-center sortable-disclosure-file';
                        row.dataset.filename = name;
                        const left = document.createElement('div');
                        left.className = 'd-flex align-items-center gap-2';
                        const handle = document.createElement('button');
                        handle.type = 'button';
                        handle.className = 'btn btn-sm btn-light file-drag-handle';
                        handle.draggable = true;
                        handle.textContent = '↕';
                        handle.setAttribute('aria-label', 'Drag to reorder file');
                        left.appendChild(handle);
                        const label = document.createElement('span');
                        label.textContent = name + (item.thumbnail ? ' — Thumbnail: ' + item.thumbnail : ' — No thumbnail');
                        left.appendChild(label);
                        row.appendChild(left);
                        const actions = document.createElement('div');
                        <?php if (can('update', MENU_ID)): ?>
                        if (name.toLowerCase().endsWith('.pdf')) {
                            const replace = document.createElement('button'); replace.type = 'button'; replace.className = 'btn btn-sm btn-outline-secondary mr-2'; replace.textContent = 'Replace Thumbnail';
                            replace.onclick = () => { const picker = document.createElement('input'); picker.type = 'file'; picker.accept = '.jpg,.jpeg,.png,image/jpeg,image/png'; picker.onchange = async () => { if (!picker.files.length) return; const body = new FormData(); body.append('disclosure_action','disclosure_replace_thumbnail'); body.append('disclosure_id',String(editingId)); body.append('filename',name); body.append('thumbnail',picker.files[0]); try { const response = await fetch(window.location.href,{method:'POST',body,credentials:'same-origin'}); const result = await response.json(); if (!response.ok || !result.success) throw new Error(result.message || 'Could not replace thumbnail.'); location.reload(); } catch(error) { alert(error.message); } }; picker.click(); };
                            actions.appendChild(replace);
                        }
                        <?php endif; ?>
                        <?php if (can('delete', MENU_ID)): ?>const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'btn btn-sm btn-outline-danger';
                        button.textContent = 'Delete';
                        button.onclick = async () => {
                            if (!confirm('Are you sure you want to delete this file?')) return;
                            try {
                                await sendDisclosureAction('disclosure_delete_file', {
                                    disclosure_id: String(editingId),
                                    filename: name
                                });
                                location.reload()
                            } catch (error) {
                                alert(error.message)
                            }
                        };
                        actions.appendChild(button);
                        <?php endif; ?>
                        row.appendChild(actions);
                        editFiles.appendChild(row)
                    })
                }
                enableDragSort(editFiles, '.sortable-disclosure-file', '.file-drag-handle', () => {
                    document.getElementById('fileOrderStatus').textContent = 'File order changed. Save Changes to apply.'
                });
                document.getElementById('editAddFiles').addEventListener('change', e => {
                    addToEdit(e.target.files);
                    e.target.value = '';
                });
                document.querySelectorAll('.disclosure-order-list').forEach(list => enableDragSort(list, 'tr', '.disclosure-drag-handle', async () => {
                    const status = list.parentElement.parentElement.querySelector('.disclosure-order-status');
                    status.className = 'small text-muted disclosure-order-status';
                    status.textContent = 'Saving order...';
                    const orders = Array.from(list.querySelectorAll('tr')).map((row, index) => ({
                        id: Number(row.dataset.id),
                        display_order: index
                    }));
                    try {
                        const result = await sendDisclosureAction('disclosure_reorder', {
                            category: list.dataset.category,
                            orders: JSON.stringify(orders)
                        });
                        status.className = 'small text-success disclosure-order-status';
                        status.textContent = result.message || 'Order updated successfully.';
                        setTimeout(() => location.reload(), 500)
                    } catch (error) {
                        status.className = 'small text-danger disclosure-order-status';
                        status.textContent = error.message || 'Could not save the new order.'
                    }
                }));
                document.querySelectorAll('.disclosure-edit').forEach(button => button.addEventListener('click', () => {
                    editingId = button.dataset.id;
                    document.getElementById('editCategory').value = button.dataset.category;
                    document.getElementById('editTitle').value = button.dataset.title;
                    document.getElementById('editAddFiles').value = '';
                    editNewFiles = [];
                    document.getElementById('editNewItems').innerHTML = '';
                    let names = [];
                    try {
                        names = JSON.parse(button.dataset.files || '[]')
                    } catch (e) {}
                    showFiles(names);
                    editStatus.textContent = '';
                    editPanel.classList.remove('d-none');
                    editPanel.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    })
                }));
                document.getElementById('cancelDisclosureEdit').onclick = () => {
                    editPanel.classList.add('d-none');
                    editingId = null
                };
                document.querySelectorAll('.disclosure-delete').forEach(button => button.addEventListener('click', async () => {
                    if (!confirm('Are you sure you want to delete this disclosure and all its uploaded files?')) return;
                    try {
                        await sendDisclosureAction('disclosure_delete', {
                            disclosure_id: button.dataset.id
                        });
                        location.reload()
                    } catch (error) {
                        alert(error.message)
                    }
                }));
                async function uploadDisclosureItems(items, id, category, title, onId) {
                    let uploaded = 0;
                    while (items.length) {
                        const batch = items.slice(0, 15), data = new FormData(), metadata = [];
                        data.append('disclosure_upload','1'); data.append('category',category); data.append('title',title); if (id) data.append('disclosure_id',String(id));
                        batch.forEach(item => { if (isPdf(item.file) && !item.thumbnail) throw new Error('Choose a thumbnail for every PDF before uploading.'); const key = item.key; metadata.push({key:key}); data.append('document_' + key,item.file,item.file.name); if (item.thumbnail) data.append('thumbnail_' + key,item.thumbnail,item.thumbnail.name); });
                        data.append('disclosure_items',JSON.stringify(metadata));
                        const response = await fetch(window.location.href,{method:'POST',body:data,credentials:'same-origin'}), result = await response.json();
                        if (result.disclosure_id) { id = result.disclosure_id; if (onId) onId(id); }
                        if (!response.ok || !result.success) throw new Error(result.message || 'Upload failed.');
                        uploaded += Number(result.uploaded_count) || 0;
                        items.splice(0, batch.length);
                    }
                    return {id:id,uploaded:uploaded};
                }
                document.getElementById('saveDisclosure').onclick = async () => {
                    if (!editingId) return;
                    const button = document.getElementById('saveDisclosure');
                    button.disabled = true;
                    editStatus.textContent = 'Saving changes...';
                    try {
                        if (editNewFiles.some(item => isPdf(item.file) && !item.thumbnail)) throw new Error('Choose a thumbnail for every PDF before saving.');
                        await sendDisclosureAction('disclosure_update', {
                            disclosure_id: String(editingId),
                            category: document.getElementById('editCategory').value.trim(),
                            title: document.getElementById('editTitle').value.trim()
                        });
                        const orderedFiles = Array.from(editFiles.querySelectorAll('.sortable-disclosure-file')).map(row => row.dataset.filename);
                        await sendDisclosureAction('disclosure_reorder_files', {
                            disclosure_id: String(editingId),
                            files: JSON.stringify(orderedFiles)
                        });
                        if (editNewFiles.length) await uploadDisclosureItems(editNewFiles, editingId, document.getElementById('editCategory').value.trim(), document.getElementById('editTitle').value.trim());
                        location.reload()
                    } catch (error) {
                        editStatus.className = 'text-danger';
                        editStatus.textContent = error.message;
                        renderItems(document.getElementById('editNewItems'), editNewFiles, () => {});
                        button.disabled = false
                    }
                };
                form.addEventListener('submit', async e => {
                    e.preventDefault();
                    if (busy) return;
                    const category = document.getElementById('disclosureCategory').value.trim(),
                        title = document.getElementById('disclosureTitle').value.trim();
                    if (!disclosureId && (!category || !title)) {
                        status.className = 'alert alert-danger';
                        status.textContent = 'Enter a category and document title.';
                        return
                    }
                    if (!files.length) {
                        status.className = 'alert alert-danger';
                        status.textContent = 'Select at least one file.';
                        return
                    }
                    if (files.some(item => isPdf(item.file) && !item.thumbnail)) {
                        status.className = 'alert alert-danger';
                        status.textContent = 'Choose a thumbnail for every PDF.';
                        return
                    }
                    busy = true;
                    document.getElementById('disclosureUpload').disabled = true;
                    try {
                        const result = await uploadDisclosureItems(files, disclosureId, category, title, value => { disclosureId = value; });
                        disclosureId = result.id;
                        files = [];
                        disclosureId = null;
                        form.reset();
                        render();
                        status.className = 'alert alert-success';
                        status.textContent = result.uploaded + ' disclosure item(s) uploaded successfully.';
                    } catch (err) {
                        render();
                        status.className = 'alert alert-danger';
                        status.textContent = err.message || 'Upload failed.';
                    } finally {
                        busy = false;
                        document.getElementById('disclosureUpload').disabled = false
                    }
                });
            })();
        </script>
    <?php else: ?>
        <div class="container text-end mt-4"><a class="btn btn-outline-dark" href="image.php?mode=disclosures">Mandatory Disclosures Upload</a></div>
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
    <?php endif; ?>

    <?php if (!$disclosureMode): ?>
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
    <?php endif; ?>
</body>

</html>
