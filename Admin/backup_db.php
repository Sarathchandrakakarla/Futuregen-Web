<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 100);

requireLogin();
requireMenuAccess(MENU_ID);

//error_reporting(0);
?>
<?php
function Export_Database($host, $user, $pass, $name,  $tables = false, $backup_name = false)
{
    $mysqli = new mysqli($host, $user, $pass, $name);
    $mysqli->select_db($name);
    $mysqli->query("SET NAMES 'utf8'");

    $queryTables    = $mysqli->query('SHOW TABLES');
    while ($row = $queryTables->fetch_row()) {
        $target_tables[] = $row[0];
    }
    if ($tables === false) {
        $tables = $target_tables;   // export all tables
    }
    $target_tables = array_intersect($target_tables, $tables);
    foreach ($target_tables as $table) {
        $result         =   $mysqli->query('SELECT * FROM ' . $table);
        $fields_amount  =   $result->field_count;
        $rows_num = $mysqli->affected_rows;
        $res            =   $mysqli->query('SHOW CREATE TABLE ' . $table);
        $TableMLine     =   $res->fetch_row();
        $content        = (!isset($content) ?  '' : $content) . "\n\n" . $TableMLine[1] . ";\n\n";

        for ($i = 0, $st_counter = 0; $i < $fields_amount; $i++, $st_counter = 0) {
            while ($row = $result->fetch_row()) { //when started (and every after 100 command cycle):
                if ($st_counter % 100 == 0 || $st_counter == 0) {
                    $content .= "\nINSERT INTO " . $table . " VALUES";
                }
                $content .= "\n(";
                for ($j = 0; $j < $fields_amount; $j++) {
                    $row[$j] = str_replace("\n", "\\n", addslashes($row[$j]));
                    if (isset($row[$j])) {
                        $content .= '"' . $row[$j] . '"';
                    } else {
                        $content .= '""';
                    }
                    if ($j < ($fields_amount - 1)) {
                        $content .= ',';
                    }
                }
                $content .= ")";
                //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                if ((($st_counter + 1) % 100 == 0 && $st_counter != 0) || $st_counter + 1 == $rows_num) {
                    $content .= ";";
                } else {
                    $content .= ",";
                }
                $st_counter = $st_counter + 1;
            }
        }
        $content .= "\n\n\n";
    }
    $backup_name = $backup_name ? $backup_name : $name . "_" . date('d-m-Y-s') . ".sql";
    //$backup_name = $backup_name ? $backup_name : $name . ".sql";
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
    echo $content;
    exit;
}
function restoreDatabaseTables($dbHost, $dbUsername, $dbPassword, $dbName, $filePath)
{
    //Drop all Tables in old database
    $mysqli = new mysqli("localhost", "root", "", $_SESSION['school_db']['db_name']);
    $mysqli->query('SET foreign_key_checks = 0');
    if ($result = $mysqli->query("SHOW TABLES")) {
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            $mysqli->query('DROP TABLE IF EXISTS ' . $row[0]);
        }
    }
    $mysqli->query('SET foreign_key_checks = 1');
    $mysqli->close();

    // Connect & select the database
    $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

    // Temporary variable, used to store current query
    $templine = '';

    // Read in entire file
    $lines = file($filePath);

    $error = '';

    $db->query('SET foreign_key_checks = 0');
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '') {
            continue;
        }

        // Add this line to the current segment
        $templine .= $line;

        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            if (!$db->query($templine)) {
                $error .= 'Error performing query "<b>' . $templine . '</b>": ' . $db->error . '<br /><br />';
            }

            // Reset temp variable to empty
            $templine = '';
        }
    }
    $db->query('SET foreign_key_checks = 1');
    return !empty($error) ? $error : true;
}

?>
<?php
$mysqlUserName      = "root";
$mysqlPassword      = "";
$mysqlHostName      = "localhost";
$DbName             = $_SESSION['school_db']['db_name'];
$backup_name        = "mybackup.sql";
$tables             = "Your tables";
//or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

if (isset($_POST['Backup'])) {
    if (!can('view', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to download database backup');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    //Backing Up the Full Database

    $backup = Export_Database($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName,  $tables = false, $backup_name = false);
}

if (isset($_POST['Restore'])) {
    if (!can('update', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to restore database');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    //Restoring and Updating the Full Database
    $file = $_FILES["SQL"]["name"];
    $targetDirectory = "../backups/" . $file;
    move_uploaded_file($_FILES['SQL']['tmp_name'], $targetDirectory);
    $filePath = $targetDirectory;
    $restore_status = false;
    $restore = restoreDatabaseTables($mysqlHostName, $mysqlUserName, $mysqlPassword, $DbName, $filePath);
    if ($restore) {
        $restore_status = true;
    }
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.8/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<style>
    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    [title],
    [title] * {
        cursor: not-allowed !important;
    }

    input:disabled {
        opacity: 0.6;
    }
</style>

<body>
    <?php
    include 'sidebar.php';
    ?>

    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-lg-4">
                <ul style='color:red;list-style: square'>
                    <li>Please Wait while Processing</li>
                    <li>For Backup, Please Wait till File Download</li>
                    <li>For Restore, Please Wait till Alert Appears</li>
                </ul>
            </div>
        </div>
        <form action="" method="post" enctype="multipart/form-data">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <span <?= !can('update', MENU_ID) ? 'title="You don\'t have permission to import database"' : '' ?>>
                        <input type="file"
                            class="form-control"
                            accept=".sql"
                            name="SQL"
                            id="sql"
                            <?= !can('update', MENU_ID) ? 'disabled' : '' ?>>
                    </span>
                </div>
            </div>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to download database backup"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="Backup" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Backup Database</button>
                    </div>
                    <div class="btn-wrapper"
                        <?php if (!can('update', MENU_ID)) { ?>
                        title="You don't have permission to restore database"
                        <?php } ?>>
                        <button class="btn btn-success" type="submit" name="Restore" onclick="if(sql.value == ''){alert('Please Select Database File to Restore!');return false}else{return true;}" <?php echo !can('update', MENU_ID) ? 'disabled' : ''; ?>>Restore Database</button>
                    </div>
                </div>
            </div>
        </form>
        <?php if (isset($restore_status) && $restore_status) { ?>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <div class="alert alert-success d-flex align-items-center" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-check-circle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                        </svg>
                        <div>
                            Database Restored and Updated Successfully!!
                        </div>
                    </div>
                </div>
            </div>
        <?php } else if (isset($restore_status) && !$restore_status) { ?>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-4">
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                        </svg>
                        <div>
                            Database Restoration Failed!!
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    </div>
</body>

</html>