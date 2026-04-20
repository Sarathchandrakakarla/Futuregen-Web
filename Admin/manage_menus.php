<?php
include_once('../link.php');
include_once('includes/rbac_helper.php');

define('MENU_ID', 105);

requireLogin();
requireMenuAccess(MENU_ID);

error_reporting(0);
?>
<?php

function compactSequenceOnDeactivate($link, $isParent, $loginType, $platformType, $oldSeq, $parMenuId = null)  //Works for Both Parent & Child on Deactivation
{

    if ($isParent == 1) {
        // Parent domain
        mysqli_query(
            $link,
            "UPDATE menus
             SET Sequence_Id = Sequence_Id - 1
             WHERE Parent_Flag = 1
               AND Login_Type = '$loginType'
               AND Platform_Type = '$platformType'
               AND Active_Flag = 1
               AND Sequence_Id > $oldSeq"
        );
    } else {
        // Child domain
        mysqli_query(
            $link,
            "UPDATE menus
             SET Sequence_Id = Sequence_Id - 1
             WHERE Par_Menu_Id = $parMenuId
               AND Platform_Type = '$platformType'
               AND Active_Flag = 1
               AND Sequence_Id > $oldSeq"
        );
    }
}

function compactChildDomain($link, $parentId, $platformType, $oldSeq)  //Used in Hard Child Deletion(No need to handle Parent)
{
    mysqli_query(
        $link,
        "UPDATE menus
         SET Sequence_Id = Sequence_Id - 1
         WHERE Par_Menu_Id = $parentId
           AND Platform_Type = '$platformType'
           AND Active_Flag = 1
           AND Sequence_Id > $oldSeq"
    );
}

function compactParentDomain($link, $loginType, $platformType, $oldSeq)  //Used in Hard Parent Deletion(No need to handle Child-Children also deletes)
{
    mysqli_query(
        $link,
        "UPDATE menus
         SET Sequence_Id = Sequence_Id - 1
         WHERE Parent_Flag = 1
           AND Login_Type = '$loginType'
           AND Platform_Type = '$platformType'
           AND Active_Flag = 1
           AND Sequence_Id > $oldSeq"
    );
}

if (isset($_POST['Clear'])) {
    echo "<script>Reset();</script>";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

/* ---------------- AJAX: GET PARENT MENUS ---------------- */
if (isset($_POST['Action']) && $_POST['Action'] === 'GetparentMenus') {
    $login_type = $_POST['Login_Type'];
    $platform_type = $_POST['Platform_Type'];
    $ChildId = isset($_POST['ChildId']) ? (int)$_POST['ChildId'] : null;
    $data = [];
    $q = mysqli_query($link, "SELECT Menu_Id, Menu_Name FROM menus WHERE Parent_Flag = 1 AND Active_Flag = 1 AND Login_Type = '$login_type' AND Platform_Type = '$platform_type' ORDER BY Sequence_Id");

    while ($r = mysqli_fetch_assoc($q)) {
        $data[$r['Menu_Id']] = $r['Menu_Name'];
    }
    if ($ChildId) {
        $parent_query = mysqli_query($link, "
                        SELECT a.Active_Flag
                        FROM menus a
                        JOIN menus b 
                            ON a.Menu_Id = b.Par_Menu_Id
                        WHERE b.Menu_Id = $ChildId
                        AND a.Platform_Type = '$platform_type'");
        $par_active = (int)mysqli_fetch_row($parent_query)[0];
        if ($par_active == 0) {
            echo json_encode(["success" => false, "data" => $data, "msg" => 'Parent Inactive']);
            return;
        }
    }
    echo json_encode(["success" => true, "data" => $data]);
    exit;
}

/* ---------------- AJAX: GET DELETE DETAILS ---------------- */
if (isset($_POST['Action']) && $_POST['Action'] === 'GetDeleteDetails') {

    $menuId = (int)$_POST['Menu_Id'];

    $menuQ = mysqli_query($link, "
        SELECT m.*, 
               CASE WHEN m.Parent_Flag = 1 THEN 'Parent' ELSE 'Child' END AS MenuType
        FROM menus m
        WHERE m.Menu_Id = $menuId
    ");

    if (!$menuQ || mysqli_num_rows($menuQ) === 0) {
        echo json_encode(['status' => 'error', 'msg' => 'Menu not found']);
        exit;
    }

    $menu = mysqli_fetch_assoc($menuQ);

    $children = [];
    if ((int)$menu['Parent_Flag'] === 1) {
        $childQ = mysqli_query($link, "
            SELECT Menu_Id, Display_Name, Active_Flag, Sequence_Id
            FROM menus
            WHERE Par_Menu_Id = $menuId
              AND Platform_Type = '{$menu['Platform_Type']}'
            ORDER BY Sequence_Id ASC
        ");
        while ($c = mysqli_fetch_assoc($childQ)) {
            $children[] = $c;
        }
    }

    echo json_encode([
        'status'   => 'success',
        'menu'     => $menu,
        'children' => $children
    ]);
    exit;
}

/* ---------------- INSERT LOGIC ---------------- */
if (isset($_POST['Insert'])) {
    if (!can('create', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to insert menu');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $Menu_Name = $_POST['Menu_Name'];
    $Display_Name = $_POST['Display_Name'];
    $Parent_Flag = isset($_POST['Parent_Flag']) && $_POST['Parent_Flag'] == "on" ? 1 : 0;
    $Route = isset($_POST['Route']) ? $_POST['Route'] : null;
    $Icon = isset($_POST['Icon']) ? $_POST['Icon'] : null;
    $Login_Type = $_POST['Login_Type'];
    $Platform_Type = $_POST['Platform_Type'];
    $Par_Menu_Id = isset($_POST['Par_Menu_Id']) ? (int)$_POST['Par_Menu_Id'] : 0;
    if (!$Parent_Flag) {

        $parentCheck = mysqli_query($link, "SELECT Platform_Type, Login_Type FROM menus WHERE Menu_Id = $Par_Menu_Id");

        if (mysqli_num_rows($parentCheck) == 0) {
            echo json_encode(["success" => false, "msg" => "Invalid Parent Menu"]);
            exit;
        }

        $parentData = mysqli_fetch_assoc($parentCheck);

        if ($parentData['Platform_Type'] !== $Platform_Type) {
            echo json_encode([
                "success" => false,
                "msg" => "Child must belong to same Platform as Parent"
            ]);
            exit;
        }

        if ($parentData['Login_Type'] !== $Login_Type) {
            echo json_encode([
                "success" => false,
                "msg" => "Child must belong to same Login Type as Parent"
            ]);
            exit;
        }
    }
    $Menu_Type = $_POST['Menu_Type'];
    $Sequence_Id = (int)$_POST['Sequence_Id'];

    $check_query = mysqli_query($link, "SELECT * FROM menus WHERE Menu_Name = '$Menu_Name' AND Login_Type = '$Login_Type' AND Platform_Type = '$Platform_Type'");
    if (mysqli_num_rows($check_query) > 0) {
        echo json_encode(["success" => false, "msg" => 'Menu_Name Already Exists!']);
        exit;
    }
    if ($Platform_Type != 'App' && !$Parent_Flag && $Route && !file_exists($_SERVER['DOCUMENT_ROOT'] . $Route)) {
        echo json_encode(["success" => false, "msg" => 'Route File does not exist!']);
        exit;
    }
    if ($Parent_Flag) {
        $sequence_check_query = mysqli_query($link, "SELECT Sequence_Id FROM `menus` WHERE Parent_Flag = 1 AND Login_Type = '$Login_Type' AND Platform_Type = '$Platform_Type' ORDER BY Sequence_Id DESC LIMIT 1");
        $sequence_update_query = "UPDATE `menus` SET Sequence_Id = Sequence_Id + 1 WHERE Parent_Flag = 1 AND Login_Type = '$Login_Type' AND Platform_Type = '$Platform_Type' AND Sequence_Id >= $Sequence_Id";
    } else {
        $sequence_check_query = mysqli_query($link, "SELECT Sequence_Id FROM `menus` WHERE Par_Menu_Id = $Par_Menu_Id AND Login_Type = '$Login_Type' AND Platform_Type = '$Platform_Type' ORDER BY Sequence_Id DESC LIMIT 1");
        $sequence_update_query = "UPDATE `menus` SET Sequence_Id = Sequence_Id + 1 WHERE Par_Menu_Id = $Par_Menu_Id AND Login_Type = '$Login_Type' AND Platform_Type = '$Platform_Type' AND Sequence_Id >= $Sequence_Id";
    }
    $last_sequence_id = mysqli_fetch_row($sequence_check_query)[0] ?? 0;
    if ($Sequence_Id > $last_sequence_id && ($Sequence_Id - $last_sequence_id) > 1) {
        echo json_encode(["success" => false, "msg" => 'Sequence No should be Continuous!<br>Last Sequence No: ' . $last_sequence_id]);
    } else {
        // Proceeding for Insertion
        // Updating Sequences if given sequence id is in middle of existing sequence
        if (mysqli_query($link, $sequence_update_query)) {
            // Inserting New Menu
            if ($Parent_Flag) {
                $main_insert_query = "INSERT INTO `menus`(Menu_Name,Display_Name,Parent_Flag,Icon,Menu_Type,Sequence_Id,Login_Type,Platform_Type) VALUES('$Menu_Name','$Display_Name',1,'$Icon','$Menu_Type',$Sequence_Id,'$Login_Type','$Platform_Type')";
            } else {
                $main_insert_query = "INSERT INTO `menus`(Menu_Name,Display_Name,Parent_Flag,Route,Par_Menu_Id,Menu_Type,Sequence_Id,Login_Type,Platform_Type) VALUES('$Menu_Name','$Display_Name',0,'$Route',$Par_Menu_Id,'$Menu_Type',$Sequence_Id,'$Login_Type','$Platform_Type')";
            }
            if (mysqli_query($link, $main_insert_query)) {
                echo json_encode(["success" => true, "msg" => 'New Menu Inserted Successfully!']);
            } else {
                echo json_encode(["success" => false, "msg" => 'New Menu Insertion Failed!']);
            }
        } else {
            echo json_encode(["success" => false, "msg" => 'Error in Sequence Updation!']);
        }
    }
    exit;
}

/* ---------------- UPDATE LOGIC ---------------- */

if (isset($_POST['Update'])) {
    if (!can('update', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to update this menu');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }
    $Menu_Id      = (int)$_POST['Menu_Id'];
    $old = mysqli_fetch_assoc(mysqli_query($link, "SELECT * FROM menus WHERE Menu_Id = $Menu_Id"));
    $old_parent = (int)$old['Parent_Flag'];
    $old_active = (int)$old['Active_Flag'];
    $old_seq    = (int)$old['Sequence_Id'];

    $Menu_Name = isset($_POST['Menu_Name']) ? trim($_POST['Menu_Name']) : $old['Menu_Name'];
    $Display_Name = isset($_POST['Display_Name']) ? trim($_POST['Display_Name']) : $old['Display_Name'];
    $Active_Flag = isset($_POST['Active_Flag']) ? 1 : 0;
    if ($Active_Flag == 0) {
        $Parent_Flag = $old_parent;
    } else {
        $Parent_Flag = isset($_POST['Parent_Flag']) ? 1 : 0;
    }
    $Route = isset($_POST['Route']) ? $_POST['Route'] : $old['Route'];
    $Icon = isset($_POST['Icon']) ? $_POST['Icon'] : $old['Icon'];
    $Par_Menu_Id = (isset($_POST['Par_Menu_Id']) && $_POST['Par_Menu_Id'] !== '') ? (int)$_POST['Par_Menu_Id'] : $old['Par_Menu_Id'];
    $Menu_Type = isset($_POST['Menu_Type']) ? $_POST['Menu_Type'] : $old['Menu_Type'];
    $Sequence_Id = isset($_POST['Sequence_Id']) ? (int)$_POST['Sequence_Id'] : (int)$old['Sequence_Id'];
    $Login_Type = $old['Login_Type'];
    $Platform_Type = $old['Platform_Type'];
    $Cascade = (isset($_POST['Cascade']) && $_POST['Cascade'] === "1") ? 1 : 0;

    /* ---------- CASCADE CHECK ---------- */
    if ($old_parent == 1 && $old_active == 1 && $Active_Flag == 0) {

        $cnt = mysqli_fetch_row(
            mysqli_query(
                $link,
                "SELECT COUNT(*) FROM menus
                    WHERE Par_Menu_Id=$Menu_Id
                    AND Platform_Type='$Platform_Type'
                    AND Active_Flag=1"
            )
        )[0];


        if ($cnt > 0 && !$Cascade) {
            echo json_encode([
                'confirm' => true,
                'msg' => 'This parent has active child menus. Confirm cascade?'
            ]);
            exit;
        }

        if ($cnt > 0 && $Cascade) {
            mysqli_query(
                $link,
                "UPDATE menus 
                    SET Active_Flag=0 
                    WHERE Par_Menu_Id=$Menu_Id
                    AND Platform_Type='$Platform_Type'"
            );
        }
    }

    if ($Parent_Flag == 0) {

        $parentCheck = mysqli_query($link, "
        SELECT Platform_Type, Login_Type
        FROM menus
        WHERE Menu_Id = $Par_Menu_Id
    ");

        $parentData = mysqli_fetch_assoc($parentCheck);

        if ($parentData['Platform_Type'] !== $Platform_Type) {
            echo json_encode(["success" => false, "msg" => "Cross-platform parent not allowed"]);
            exit;
        }

        if ($parentData['Login_Type'] !== $Login_Type) {
            echo json_encode(["success" => false, "msg" => "Cross-login parent not allowed"]);
            exit;
        }
    }

    /* ---------- SINGLE-ITEM DOMAIN VALIDATION (ACTIVE → ACTIVE ONLY) ---------- */
    if ($old_active == 1 && $Active_Flag == 1) {

        if ($Parent_Flag == 1) {
            $cntQ = mysqli_query(
                $link,
                "SELECT COUNT(*) FROM menus
             WHERE Parent_Flag=1
               AND Login_Type='$Login_Type'
               AND Platform_Type='$Platform_Type'
               AND Active_Flag=1"
            );
        } else {
            $cntQ = mysqli_query(
                $link,
                "SELECT COUNT(*) FROM menus
             WHERE Par_Menu_Id=$Par_Menu_Id
               AND Login_Type='$Login_Type'
               AND Platform_Type='$Platform_Type'
               AND Active_Flag=1"
            );
        }

        $domainCount = (int)mysqli_fetch_row($cntQ)[0];

        if ($domainCount === 1 && $Sequence_Id != 1) {
            echo json_encode([
                'success' => false,
                'msg' => 'Only one active item exists. Sequence must be 1.'
            ]);
            exit;
        }
    }


    /* ---------- LAST SEQUENCE CHECK ---------- */
    if ($Active_Flag == 1) {

        if ($Parent_Flag == 1) {
            $sq = mysqli_query(
                $link,
                "SELECT MAX(Sequence_Id)
                 FROM menus
                 WHERE Parent_Flag=1
                   AND Login_Type='$Login_Type'
                   AND Platform_Type='$Platform_Type'
                   AND Active_Flag=1"
            );
        } else {
            $sq = mysqli_query(
                $link,
                "SELECT MAX(Sequence_Id)
                 FROM menus
                 WHERE Par_Menu_Id=$Par_Menu_Id
                   AND Login_Type='$Login_Type'
                   AND Platform_Type='$Platform_Type'
                   AND Active_Flag=1"
            );
        }

        $last_seq = mysqli_fetch_row($sq)[0] ?? 0;

        if ($Sequence_Id > $last_seq + 1) {
            echo json_encode([
                'success' => false,
                'msg' => "Sequence must be continuous. Last allowed: $last_seq"
            ]);
            exit;
        }
    }

    /* ---------- ACTIVE → ACTIVE RANGE VALIDATION ---------- */
    if ($old_active == 1 && $Active_Flag == 1) {
        if ($Sequence_Id < 1 || $Sequence_Id > $last_seq) {
            echo json_encode([
                'success' => false,
                'msg' => "For active menus, sequence must be between 1 and $last_seq."
            ]);
            exit;
        }
    }

    /* ---------- CD-01: CHILD → PARENT CONVERSION ---------- */
    if (
        $old_active == 1 &&
        $Active_Flag == 1 &&
        $old_parent == 0 &&
        $Parent_Flag == 1
    ) {
        $oldParentId = (int)$old['Par_Menu_Id'];

        // STEP 1: CHILD DOMAIN COMPACTION
        compactChildDomain($link, $oldParentId, $Platform_Type, $old_seq);

        // STEP 2: PARENT DOMAIN INSERT + SHIFT
        mysqli_query(
            $link,
            "UPDATE menus
         SET Sequence_Id = Sequence_Id + 1
         WHERE Parent_Flag = 1
           AND Login_Type = '$Login_Type'
           AND Platform_Type = '$Platform_Type'
           AND Active_Flag = 1
           AND Sequence_Id >= $Sequence_Id"
        );

        // IMPORTANT: CD-01 handled explicitly, skip generic move logic
    }


    /* ---------- SEQUENCE COMPACTION ---------- */
    if ($old_active == 1 && $Active_Flag == 0) {
        compactSequenceOnDeactivate(
            $link,
            $old_parent,
            $Login_Type,
            $Platform_Type,
            $old_seq,
            $old['Par_Menu_Id']
        );
    }


    /* ---------- INSERT SHIFT ---------- */
    if ($old_active == 0 && $Active_Flag == 1) {
        if ($Parent_Flag == 1) {

            mysqli_query(
                $link,
                "UPDATE menus
                 SET Sequence_Id = Sequence_Id + 1
                 WHERE Parent_Flag=1
                   AND Login_Type='$Login_Type'
                   AND Platform_Type='$Platform_Type'
                   AND Active_Flag=1
                   AND Sequence_Id >= $Sequence_Id"
            );
        } else {

            mysqli_query(
                $link,
                "UPDATE menus
                 SET Sequence_Id = Sequence_Id + 1
                 WHERE Par_Menu_Id=$Par_Menu_Id
                   AND Login_Type='$Login_Type'
                   AND Platform_Type='$Platform_Type'
                   AND Active_Flag=1
                   AND Sequence_Id >= $Sequence_Id"
            );
        }
    }

    /* ---------- ACTIVE → ACTIVE SEQUENCE MOVE ---------- */
    if (
        $old_active == 1 &&
        $Active_Flag == 1 &&
        $old_seq != $Sequence_Id &&
        !($old_parent == 0 && $Parent_Flag == 1) // 🚫 NOT CD-01
    ) {

        // Determine domain
        if ($Parent_Flag == 1) {
            $domainWhere = "
            Parent_Flag=1
            AND Login_Type='$Login_Type'
            AND Platform_Type='$Platform_Type'
            AND Active_Flag=1
        ";
        } else {
            $domainWhere = "
            Par_Menu_Id=$Par_Menu_Id
            AND Login_Type='$Login_Type'
            AND Platform_Type='$Platform_Type'
            AND Active_Flag=1
        ";
        }

        // Moving UP (e.g. 15 → 13)
        if ($Sequence_Id < $old_seq) {
            mysqli_query(
                $link,
                "UPDATE menus
             SET Sequence_Id = Sequence_Id + 1
             WHERE $domainWhere
               AND Sequence_Id >= $Sequence_Id
               AND Sequence_Id < $old_seq"
            );
        }

        // Moving DOWN (e.g. 13 → 15)
        if ($Sequence_Id > $old_seq) {
            mysqli_query(
                $link,
                "UPDATE menus
             SET Sequence_Id = Sequence_Id - 1
             WHERE $domainWhere
               AND Sequence_Id <= $Sequence_Id
               AND Sequence_Id > $old_seq"
            );
        }
    }


    /* ---------- DUPLICATE CHECK ON UPDATE ---------- */
    $dupCheck = mysqli_query(
        $link,
        "SELECT Menu_Id FROM menus
     WHERE Menu_Name = '$Menu_Name'
       AND Login_Type = '$Login_Type'
       AND Platform_Type = '$Platform_Type'
       AND Menu_Id != $Menu_Id"
    );

    if (mysqli_num_rows($dupCheck) > 0) {
        echo json_encode([
            'success' => false,
            'msg' => 'Menu_Name already exists for this Login Type and Platform'
        ]);
        exit;
    }

    /* ---------- FINAL UPDATE ---------- */
    if ($Parent_Flag == 1) {
        $sql = "
            UPDATE menus SET
                Menu_Name='$Menu_Name',
                Display_Name='$Display_Name',
                Parent_Flag=1,
                Route=NULL,
                Icon='$Icon',
                Par_Menu_Id=NULL,
                Menu_Type='Parent',
                Sequence_Id=$Sequence_Id,
                Platform_Type='$Platform_Type',
                Active_Flag=$Active_Flag
            WHERE Menu_Id=$Menu_Id";
    } else {
        $sql = "
            UPDATE menus SET
                Menu_Name='$Menu_Name',
                Display_Name='$Display_Name',
                Parent_Flag=0,
                Route='$Route',
                Icon=NULL,
                Par_Menu_Id=$Par_Menu_Id,
                Menu_Type='$Menu_Type',
                Sequence_Id=$Sequence_Id,
                Platform_Type='$Platform_Type',
                Active_Flag=$Active_Flag
            WHERE Menu_Id=$Menu_Id";
    }

    if (mysqli_query($link, $sql)) {
        echo json_encode([
            'success' => true,
            'msg' => 'Menu Updated Successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'msg' => 'Menu Update Failed'
        ]);
    }
    exit;
}

/* ---------------- AJAX: DELETE MENU (STEP 3) ---------------- */
if (isset($_POST['Action']) && $_POST['Action'] === 'DELETE_MENU') {

    // Normalize to existing delete engine
    $_POST['Delete'] = 1;
    $_POST['Menu_Id'] = (int)$_POST['Menu_Id'];
    $_POST['Delete_Type'] = $_POST['Delete_Type'];
    $_POST['Cascade_Confirm'] = $_POST['Cascade_Confirm'] ?? 'NO';

    // Flag to indicate AJAX context
    define('IS_AJAX_DELETE', true);
}

/* ---------------- DELETE: SOFT DELETE (INACTIVATE) ---------------- */
if (isset($_POST['Delete']) && $_POST['Delete_Type'] === 'SOFT') {
    if (!can('delete', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to soft delete this menu');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $menuId  = (int)$_POST['Menu_Id'];
    $cascade = isset($_POST['Cascade_Confirm']) && $_POST['Cascade_Confirm'] === 'YES';

    mysqli_begin_transaction($link);

    try {

        // Fetch menu
        $menuQ = mysqli_query($link, "
            SELECT Menu_Id, Parent_Flag, Active_Flag, Sequence_Id, Par_Menu_Id, Login_Type, Platform_Type
            FROM menus
            WHERE Menu_Id = $menuId
            FOR UPDATE
        ");

        if (!$menuQ || mysqli_num_rows($menuQ) === 0) {
            throw new Exception('Menu not found');
        }

        $menu = mysqli_fetch_assoc($menuQ);

        // If already inactive → nothing to do
        if ((int)$menu['Active_Flag'] === 0) {
            throw new Exception('Menu already inactive');
        }

        $old_seq = (int)$menu['Sequence_Id'];

        /* ---------- PARENT SOFT DELETE ---------- */
        if ((int)$menu['Parent_Flag'] === 1) {

            if (!$cascade) {

                if (defined('IS_AJAX_DELETE')) {
                    echo json_encode([
                        'status' => 'confirm',
                        'msg' => 'This parent menu has active child menus. Confirm cascade inactivation?'
                    ]);
                    exit;
                }

                throw new Exception('Cascade confirmation required for parent');
            }



            // 1. Inactivate parent
            mysqli_query($link, "
                UPDATE menus 
                SET Active_Flag = 0 
                WHERE Menu_Id = $menuId
            ");

            // 2. Inactivate all children (Platform scoped)
            mysqli_query($link, "
                UPDATE menus 
                SET Active_Flag = 0 
                WHERE Par_Menu_Id = $menuId
                  AND Platform_Type = '{$menu['Platform_Type']}'
            ");

            // Sequence compaction for parent + child
            compactSequenceOnDeactivate(
                $link,
                1,
                $menu['Login_Type'],
                $menu['Platform_Type'],
                $old_seq
            );
        }
        /* ---------- CHILD SOFT DELETE ---------- */ else {

            // Inactivate child
            mysqli_query($link, "
                UPDATE menus 
                SET Active_Flag = 0 
                WHERE Menu_Id = $menuId
            ");

            // Child-domain sequence compaction is handled
            compactSequenceOnDeactivate(
                $link,
                0,
                $menu['Login_Type'],
                $menu['Platform_Type'],
                $old_seq,
                $menu['Par_Menu_Id']
            );
        }

        mysqli_commit($link);

        echo json_encode([
            'status' => 'success',
            'msg' => 'Menu inactivated successfully'
        ]);
        exit;
    } catch (Exception $e) {

        mysqli_rollback($link);
        echo json_encode([
            'status' => 'error',
            'msg' => $e->getMessage()
        ]);
        exit;
    }
}

/* ---------------- DELETE: HARD DELETE (CHILD / PARENT) ---------------- */
if (isset($_POST['Delete']) && $_POST['Delete_Type'] === 'HARD') {
    if (!can('custom1', MENU_ID)) {
        echo "<script>alert('You don\'t have permission to hard delete this menu');
            location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
        exit;
    }

    $menuId  = (int)$_POST['Menu_Id'];
    $cascade = isset($_POST['Cascade_Confirm']) && $_POST['Cascade_Confirm'] === 'YES';

    mysqli_begin_transaction($link);

    try {

        // Lock menu
        $menuQ = mysqli_query($link, "
            SELECT Menu_Id, Parent_Flag, Active_Flag, Sequence_Id, Par_Menu_Id, Login_Type, Platform_Type
            FROM menus
            WHERE Menu_Id = $menuId
            LIMIT 1
            FOR UPDATE
        ");

        if (!$menuQ || mysqli_num_rows($menuQ) === 0) {
            throw new Exception('Menu already deleted or does not exist');
        }

        $menu = mysqli_fetch_assoc($menuQ);

        /* ================= CHILD HARD DELETE ================= */
        if ((int)$menu['Parent_Flag'] === 0) {

            $old_seq  = (int)$menu['Sequence_Id'];
            $parentId = (int)$menu['Par_Menu_Id'];
            // 🔴 DELETE ROLE MAPPINGS (RBAC)
            if (!mysqli_query(
                $link,
                "DELETE FROM role_menu_map WHERE Menu_Id = $menuId"
            )) {
                throw new Exception('Failed to delete role-menu mappings');
            }

            if (!mysqli_query(
                $link,
                "DELETE FROM menus WHERE Menu_Id = $menuId"
            )) {
                throw new Exception('Failed to delete child menu');
            }

            compactChildDomain($link, $parentId, $menu['Platform_Type'], $old_seq);

            mysqli_commit($link);

            echo json_encode([
                'status' => 'success',
                'msg' => 'Child menu deleted permanently'
            ]);
            exit;
        }

        /* ================= PARENT HARD DELETE ================= */
        if ((int)$menu['Parent_Flag'] === 1) {

            if (!$cascade) {

                if (defined('IS_AJAX_DELETE')) {
                    echo json_encode([
                        'status' => 'confirm',
                        'msg' => 'This parent menu has child menus. Confirm cascade delete?'
                    ]);
                    exit;
                }

                throw new Exception('Cascade confirmation required for parent hard delete');
            }


            $old_seq   = (int)$menu['Sequence_Id'];
            $loginType = $menu['Login_Type'];
            $platformType = $menu['Platform_Type'];
            $wasActive = (int)$menu['Active_Flag'];

            // 🔴 DELETE ROLE MAPPINGS FOR CHILD MENUS (Platform scoped)
            if (!mysqli_query(
                $link,
                "DELETE rm
                    FROM role_menu_map rm
                    JOIN menus m ON rm.Menu_Id = m.Menu_Id
                    WHERE m.Par_Menu_Id = $menuId
                      AND m.Platform_Type = '$platformType'"
            )) {
                throw new Exception('Failed to delete child menu role mappings');
            }

            // 🔴 DELETE ROLE MAPPINGS FOR PARENT MENU
            if (!mysqli_query(
                $link,
                "DELETE FROM role_menu_map WHERE Menu_Id = $menuId"
            )) {
                throw new Exception('Failed to delete parent menu role mappings');
            }


            // Delete children (Platform scoped)
            if (!mysqli_query(
                $link,
                "DELETE FROM menus 
                 WHERE Par_Menu_Id = $menuId
                   AND Platform_Type = '$platformType'"
            )) {
                throw new Exception('Failed to delete child menus');
            }

            // Delete parent
            if (!mysqli_query(
                $link,
                "DELETE FROM menus WHERE Menu_Id = $menuId"
            )) {
                throw new Exception('Failed to delete parent menu');
            }

            // 🔒 COMPACT ONLY IF IT WAS ACTIVE
            if ($wasActive === 1) {
                compactParentDomain($link, $loginType, $platformType, $old_seq);
            }

            mysqli_commit($link);

            echo json_encode([
                'status' => 'success',
                'msg' => 'Parent menu and all its children deleted permanently'
            ]);
            exit;
        }
    } catch (Exception $e) {

        mysqli_rollback($link);

        echo json_encode([
            'status' => 'error',
            'msg' => $e->getMessage()
        ]);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Victory/css/sidebar-style.css" />
    <!-- Controlling Cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

    <!-- Boxiocns CDN Link -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />

    <!-- Bootstrap Links -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    body {
        overflow-x: scroll;
    }

    .table-container {
        max-width: 1350px;
        max-height: 500px;
        margin-left: 8%;
        overflow-x: scroll;
    }

    .icon {
        cursor: pointer;
    }

    #actions_row {
        text-align: end;
    }

    #actions-wrapper {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 20px;
    }

    @media print {
        * {
            display: none;
        }

        #table-container {
            display: block;
        }
    }

    @media screen and (max-width:576px) {
        .container {
            width: 80%;
            margin-left: 20%;
            overflow-x: scroll;
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

    .btn-wrapper[title],
    .btn-wrapper[title] * {
        cursor: not-allowed !important;
    }

    .form-check[title],
    .form-check[title] * {
        cursor: not-allowed !important;
    }

    .form-check input:disabled+label {
        opacity: 0.6;
    }
</style>

<body>

    <?php include 'sidebar.php'; ?>

    <form action="" method="post">
        <div class="container">
            <div class="row justify-content-center mt-5">
                <div class="col-lg-3 mt-3">
                    <select class="form-select" name="Platform_Type" id="platform_type">
                        <option selected disabled>-- Select Platform --</option>
                        <option value="Web">Web</option>
                        <option value="App">App</option>
                    </select>
                </div>
            </div>

            <div class="row justify-content-center mt-1">
                <div class="col-lg-3 rounded">
                    <select class="form-select" name="Type" id="type" aria-label="Default select example">
                        <option selected disabled>-- Select Login Type --</option>
                        <option value="Admin">Admin</option>
                        <option value="Faculty">Faculty</option>
                        <option value="Student">Student</option>
                        <option value="All">All</option>
                    </select>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-lg-2">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="menu_status" id="active" checked value="Active">
                        <label class="form-check-label" for="active">Active</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="menu_status" id="inactive" value="Inactive">
                        <label class="form-check-label" for="inactive">Inactive</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row justify-content-center mt-4">
                <div class="col-lg-2">
                    <div class="btn-wrapper"
                        <?php if (!can('view', MENU_ID)) { ?>
                        title="You don't have permission to view this report"
                        <?php } ?>>
                        <button class="btn btn-primary" type="submit" name="Show" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?>>Show</button>
                    </div>
                    <button class="btn btn-warning" type="submit">Clear</button>
                </div>
            </div>
        </div>
    </form>
    <div class="container">
        <div class="row justify-content-center mt-4">
            <div class="col-lg-4">
                <h3><b>Manage Organization Menus</b></h3>
            </div>
        </div>
    </div>
    <div class="container table-container" id="table-container">
        <table class="table table-bordered table-hover">
            <thead class="bg-secondary text-light">
                <tr>
                    <th colspan="13" id="actions_row">
                        <form action="" method="post">
                            <div id="actions-wrapper">
                                <input type="text" class="form-control" name="Query" id="query" placeholder="Enter Full SQL Query" style="width:50%;" hidden>
                                <!-- Execute Button -->
                                <div class="btn-wrapper"
                                    <?php if (!can('view', MENU_ID)) { ?>
                                    title="You don't have permission to use query mode"
                                    <?php } ?>
                                    <?php if (!can('view', MENU_ID)) { ?>
                                    style="cursor:not-allowed;"
                                    <?php } ?>>
                                    <button type="submit" class="bx bx-check btn btn-dark" name="Show" id="execute" style="font-size:18px;" <?php echo !can('view', MENU_ID) ? 'disabled' : ''; ?> hidden></button>
                                </div>
                                <!-- Search Button -->
                                <div class="btn-wrapper"
                                    <?php if (!can('view', MENU_ID)) { ?>
                                    title="You don't have permission to use query mode"
                                    <?php } ?>
                                    <?php if (!can('view', MENU_ID)) { ?>
                                    style="cursor:not-allowed;"
                                    <?php } ?>>
                                    <i class="bx bx-search icon"
                                        style="font-size:22px; <?php echo !can('view', MENU_ID) ? 'pointer-events:none; opacity:0.6;' : ''; ?>"
                                        <?php if (can('view', MENU_ID)) { ?>
                                        onclick="toggleQuery(this)"
                                        <?php } ?>></i>
                                </div>
                                <!-- Insert Button -->
                                <div class="btn-wrapper"
                                    <?php if (!can('create', MENU_ID)) { ?>
                                    title="You don't have permission to create menu"
                                    <?php } ?>
                                    <?php if (!can('create', MENU_ID)) { ?>
                                    style="cursor:not-allowed;"
                                    <?php } ?>>
                                    <i class="bx bx-plus icon"
                                        style="font-size:22px; <?php echo !can('create', MENU_ID) ? 'pointer-events:none; opacity:0.6;' : ''; ?>"
                                        <?php if (can('create', MENU_ID)) { ?>
                                        data-bs-toggle="modal"
                                        data-bs-target="#menuModal"
                                        data-mode="insert"
                                        <?php } ?>></i>
                                </div>
                            </div>
                        </form>
                    </th>
                </tr>
                <tr>
                    <th>Menu Id</th>
                    <th>Menu Name</th>
                    <th>Display Name</th>
                    <th>Parent</th>
                    <th>Route</th>
                    <th>Icon</th>
                    <th>Parent Menu Id</th>
                    <th>Menu Type</th>
                    <th>Sequence Id</th>
                    <th id="login_type_head">Login Type</th>
                    <th id="platform_type_head">Platform Type</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="tbody">
                <?php
                if (isset($_POST['Show'])) {
                    if (!can('view', MENU_ID)) {
                        echo "<script>alert('You don\'t have permission to view this report');
                        location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
                        exit;
                    }
                    $Platform_Type = $_POST['Platform_Type'];
                    echo "<script>document.getElementById('platform_type').value='" . $Platform_Type . "';</script>";
                    if ((isset($_POST['Type']) && isset($_POST['Platform_Type'])) || isset($_POST['Query'])) {
                        if (isset($_POST['Query'])) {
                            $query = $_POST['Query'];
                            echo "<script>document.getElementById('query').hidden = '';
                            document.getElementById('query').value = '" . $query . "';
                            document.getElementById('execute').hidden = '';</script>";
                            $query1 = mysqli_query($link, $query);
                        } else {
                            $Type = $_POST['Type'];
                            $menu_status = isset($_POST['menu_status']) ? $_POST['menu_status'] : "Active";
                            if (!isset($_POST['Query'])) {
                                echo "<script>document.getElementById('type').value='" . $Type . "';
                                document.getElementById('" . strtolower($menu_status) . "').checked = true;</script>";
                            }
                            $menu_status = $menu_status == "Active" ? 1 : 0;
                            if ($Type == "All") {
                                echo "<script>
                                login_type_head.hidden='';
                                platform_type_head.hidden='';
                                </script>";
                            } else {
                                echo "<script>
                                login_type_head.hidden='hidden';
                                platform_type_head.hidden='hidden';
                                </script>";
                            }
                            if ($Type == "All") {
                                $query1 = mysqli_query($link, "SELECT m.*, p.Display_Name AS Parent_Display_Name FROM menus m LEFT JOIN menus p ON p.Menu_Id = m.Par_Menu_Id WHERE m.Platform_Type = '$Platform_Type' AND m.Active_Flag = $menu_status ORDER BY FIELD(m.Login_Type,'Admin','Student','Faculty'), FIELD(m.Platform_Type,'Web','App'), CASE WHEN m.Parent_Flag=1 THEN m.Sequence_Id ELSE p.Sequence_Id END, m.Parent_Flag DESC, m.Sequence_Id ASC");
                            } else {
                                $query1 = mysqli_query($link, "SELECT m.*, p.Display_Name AS Parent_Display_Name FROM menus m LEFT JOIN menus p ON p.Menu_Id = m.Par_Menu_Id WHERE m.Login_Type = '$Type' AND m.Platform_Type = '$Platform_Type' AND m.Active_Flag = $menu_status ORDER BY CASE WHEN m.Parent_Flag=1 THEN m.Sequence_Id ELSE p.Sequence_Id END, m.Parent_Flag DESC, m.Sequence_Id ASC");
                            }
                        }

                        while ($r = mysqli_fetch_assoc($query1)) {
                            echo "
                            <tr " . ($r['Parent_Flag'] ? 'style="background-color:skyblue;"' : '') . ">
                                <td>{$r['Menu_Id']}</td>
                                <td style='width:300px;word-break: break-word; overflow-wrap: anywhere; white-space: normal;'>{$r['Menu_Name']}</td>
                                <td>{$r['Display_Name']}</td>
                                <td class='text-center'><input class='form-check-input' type='checkbox' " . ($r['Parent_Flag'] ? 'checked' : '') . " disabled></td>
                                <td style='width:300px;word-break: break-word; overflow-wrap: anywhere; white-space: normal;'>";
                            if ($Platform_Type != "App") {
                                echo "<a href='{$r['Route']}' target='_blank'>{$r['Route']}</a>";
                            } else {
                                echo $r['Route'];
                            }
                            echo "</td>
                                <td>{$r['Icon']}</td>
                                <td>" . (isset($r['Parent_Display_Name']) ? $r['Parent_Display_Name'] : '') . "</td>
                                <td>{$r['Menu_Type']}</td>
                                <td>{$r['Sequence_Id']}</td>";
                            if (isset($_POST['Query']) || (isset($Type) && $Type == "All")) {
                                echo '<td>' . $r['Login_Type'] . '</td>
                                <td>' . $r['Platform_Type'] . '</td>
                                ';
                            }
                            echo "<td class='text-center'>
                                    <input class='form-check-input' type='checkbox' " . ($r['Active_Flag'] ? 'checked' : '') . " disabled>
                                </td>
                                <td>
                                    <div style='display:flex; gap:12px; justify-content:center;'>";

                            /* ===== EDIT ===== */
                            echo "<div class='btn-wrapper' " . (!can('update', MENU_ID) ? 'title="You don\'t have permission to edit menu"' : "") . ">";

                            if (can('update', MENU_ID)) {
                                echo "<i class='bx bx-edit icon'
                                        data-bs-toggle='modal'
                                        data-bs-target='#menuModal'
                                        data-mode='edit'
                                        data-id='{$r['Menu_Id']}'
                                        data-logintype='{$r['Login_Type']}'
                                        data-platformtype='{$r['Platform_Type']}'
                                        data-menuname='{$r['Menu_Name']}'
                                        data-displayname='{$r['Display_Name']}'
                                        data-parentflag='" . ($r['Parent_Flag'] ? 'true' : 'false') . "'
                                        data-menutype='{$r['Menu_Type']}'
                                        data-route='{$r['Route']}'
                                        data-icon='{$r['Icon']}'
                                        data-parmenuid='{$r['Par_Menu_Id']}'
                                        data-sequenceid='{$r['Sequence_Id']}'
                                        data-activeflag='" . ($r['Active_Flag'] ? 'true' : 'false') . "'>
                                    </i>";
                            } else {
                                echo "<i class='bx bx-edit icon text-secondary'
                                            style='pointer-events:none;'>
                                    </i>";
                            }
                            echo "</div>";

                            /* ===== DELETE ===== */
                            echo "<div class='btn-wrapper' " . (!can('delete', MENU_ID) ? 'title="You don\'t have permission to delete menu"' : "") . ">";

                            if (can('delete', MENU_ID)) {
                                echo "<input type='hidden' id='deleteMenuId' value=''>
                                    <i class='bx bx-trash icon text-danger' data-id='{$r['Menu_Id']}' onclick='openDeleteModal(this)'>
                                    </i>";
                            } else {
                                echo "<i class='bx bx-trash icon text-secondary'
                                            style='pointer-events:none;'>
                                    </i>";
                            }
                            echo "</div>";

                            echo "  </div>
                                </td>
                                </tr>";
                        }
                    } else {
                        echo "<script>alert('Please Select Login Type and Platform');</script>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="menuModal">
        <div class="modal-dialog">
            <form method="post" id="modalForm">
                <input type="hidden" name="Menu_Id" id="menuId">
                <input type="hidden" name="Login_Type" id="login_type">
                <input type="hidden" name="Cascade" id="cascade">

                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="modalTitle"></h5>
                        <button class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
                        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                        </symbol>
                        <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                        </symbol>
                        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                        </symbol>
                    </svg>

                    <div class="modal-body">
                        <div class="alert alert-danger d-none align-items-center" role="alert">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                                <use id="error_icon" xlink:href="#exclamation-triangle-fill" />
                            </svg>
                            <div id="error_msg"></div>
                        </div>

                        <label>Platform Type</label>
                        <select id="platformType" name="Platform_Type" class="form-control" required onchange="getParentMenus(document.getElementById('loginType').value, this.value)">
                            <option value="" disabled selected>-- Select Platform --</option>
                            <option value="Web">Web</option>
                            <option value="App">App</option>
                        </select>

                        <label class="mt-2">Login Type</label>
                        <select id="loginType" name="Login_Type" class="form-control" required onchange="getParentMenus(this.value, document.getElementById('platformType').value)">
                            <option value="" disabled selected>-- Select Login Type --</option>
                            <option>Admin</option>
                            <option>Faculty</option>
                            <option>Student</option>
                        </select>

                        <label class="mt-2">Menu Name</label>
                        <input type="text" id="menuName" name="Menu_Name" class="form-control" onblur="menuNameHelp.style.color='grey'" oninput="validateMenuName(this.value)" aria-describedby="menuNameHelp" required>
                        <div id="menuNameHelp" class="form-text">Do not use space, use underscore(_)</div>

                        <label class="mt-2">Display Name</label>
                        <input type="text" id="displayName" name="Display_Name" class="form-control" required>

                        <div class="mt-2">
                            <input type="checkbox" class="form-check-input" id="parentFlag" name="Parent_Flag" onblur="document.querySelector('.alert').classList.remove('d-flex');document.querySelector('.alert').classList.add('d-none');" aria-describedby="parentFlagHelp" onclick="validateParent(event,this)" onchange="applyParentChanges(this)">
                            <label for="parentFlag" class="form-check-label">Parent</label>
                            <div id="parentFlagHelp" class="form-text">Parent cannot be changed to child </div>
                        </div>

                        <label class="mt-2">Menu Type</label>
                        <select name="Menu_Type" class="form-control" id="menuType" required>
                            <option value="" disabled selected>-- Select Menu Type --</option>
                            <option value="Parent" id="parent_option" disabled>Parent</option>
                            <option value="Entry" id="entry_option">Entry</option>
                            <option value="View" id="view_option">View</option>
                        </select>

                        <label class="mt-2">Route</label>
                        <input type="text" id="route" name="Route" class="form-control">

                        <label class="mt-2">Icon</label>
                        <input type="text" id="icon" name="Icon" class="form-control" disabled>

                        <label class="mt-2">Parent Menu</label>
                        <select id="parMenuId" name="Par_Menu_Id" class="form-control"></select>

                        <label class="mt-2">Sequence</label>
                        <input type="number" id="sequence_id" name="Sequence_Id" min="1" class="form-control" oninput="validateSequenceId(this.value)" aria-describedby="sequenceIdHelp" required>
                        <div id="sequenceIdHelp" class="form-text">Sequence Number should not be less than 1</div>

                        <div class="mt-2" id="activeFlagRow">
                            <input type="checkbox" class="form-check-input" id="activeFlag" name="Active_Flag">
                            <label class="form-check-label" for="activeFlag">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form method="post" id="deleteForm" onsubmit="return false;">
                <input type="hidden" name="Menu_Id" id="del_Menu_Id">
                <input type="hidden" name="Cascade" id="del_Cascade">

                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Delete Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <!-- MENU DETAILS -->
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Menu ID</label>
                                <input type="text" class="form-control" id="del_menu_id_view" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Menu Name</label>
                                <input type="text" class="form-control" id="del_menu_name" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-control" id="del_menu_type" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <input type="text" class="form-control" id="del_menu_status" readonly>
                            </div>
                        </div>

                        <!-- DELETE TYPE -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Delete Type <span class="text-danger">*</span>
                            </label>

                            <!-- SOFT DELETE -->
                            <div class="form-check"
                                <?php if (!can('delete', MENU_ID)) { ?>
                                title="You don't have permission to perform soft delete"
                                <?php } ?>>
                                <input type="radio"
                                    class="form-check-input"
                                    name="Delete_Type"
                                    value="SOFT"
                                    id="softDelete"
                                    <?php echo !can('delete', MENU_ID) ? 'disabled' : ''; ?>>
                                <label class="form-check-label" for="softDelete">
                                    Soft Delete (Inactivate)
                                </label>
                            </div>

                            <!-- HARD DELETE -->
                            <div class="form-check"
                                <?php if (!can('custom1', MENU_ID)) { ?>
                                title="You don't have permission to perform hard delete"
                                <?php } ?>>
                                <input type="radio"
                                    class="form-check-input"
                                    name="Delete_Type"
                                    value="HARD"
                                    id="hardDelete"
                                    <?php echo !can('custom1', MENU_ID) ? 'disabled' : ''; ?>>
                                <label class="form-check-label text-danger" for="hardDelete">
                                    Hard Delete (Permanent)
                                </label>
                            </div>
                        </div>

                        <!-- CASCADE CONFIRMATION -->
                        <div class="mb-3 d-none" id="cascadeSection" style="display:none;">
                            <label class="form-label fw-bold">Cascade Action <span class="text-danger">*</span></label>
                            <div>
                                <input type="radio" name="Cascade_Confirm" value="YES" id="cascadeYes">
                                <label for="cascadeYes">Yes, apply to child menus</label>
                            </div>
                            <div>
                                <input type="radio" name="Cascade_Confirm" value="NO" id="cascadeNo">
                                <label for="cascadeNo">No</label>
                            </div>
                        </div>

                        <!-- CHILD TABLE PLACEHOLDER -->
                        <div id="childTableSection" style="display:none;">
                            <hr>
                            <h6>Child Menus</h6>
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th>Seq</th>
                                    </tr>
                                </thead>
                                <tbody id="childMenuBody">
                                    <!-- Filled in next steps -->
                                </tbody>
                            </table>
                        </div>

                        <!-- FINAL CONFIRMATION -->
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="finalConfirm">
                            <label class="form-check-label text-danger fw-bold" for="finalConfirm">
                                I understand this action cannot be undone
                            </label>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" onclick="validateDeleteForm(this)" id="deleteSubmitBtn">Delete</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Global Const Variables for can_insert,can_update,can_soft_delete,can_hard_delete -->
    <script>
        const CAN_INSERT = <?= can('create', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_UPDATE = <?= can('update', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_SOFT_DELETE = <?= can('delete', MENU_ID) ? 'true' : 'false' ?>;
        const CAN_HARD_DELETE = <?= can('custom1', MENU_ID) ? 'true' : 'false' ?>;
    </script>

    <script>
        let initialEditState = null;
        // Visible/Hide Query input
        function toggleQuery(ele) {
            ele.classList.toggle('query-mode');
            ele.classList.toggle('bx-search');
            ele.classList.toggle('bx-x');
            const query = document.getElementById('query');
            const execute = document.getElementById('execute');
            const add_btn = document.getElementById('add_btn');
            query.hidden = !query.hidden;
            query.required = !query.required;
            execute.hidden = !execute.hidden;
            add_btn.hidden = !add_btn.hidden;
        }

        // Get Parent Menus based on Login Type
        async function getParentMenus(loginType, platformType, childId = null) {
            return $.post('', {
                Action: 'GetparentMenus',
                Login_Type: loginType,
                Platform_Type: platformType,
                ChildId: childId
            }, function(d) {
                let o = '<option value="" disabled selected>-- Select Parent Menu Id --</option>';
                const j = JSON.parse(d);
                if (!j.success && j.msg == "Parent Inactive") {
                    o = '<option value="Inactive_Parent" id="inactive_parent" selected>Parent Menu is Inactive</option>';
                    for (const k in j.data) {
                        o += `<option value="${k}">${k} - ${j.data[k]}</option>`
                    }
                } else {
                    for (const k in j.data) {
                        o += `<option value="${k}">${k} - ${j.data[k]}</option>`
                    }
                }

                $('#parMenuId').html(o);
            });
        }

        // DOM changes on Parent Flag change
        function applyParentChanges(ele) {

            if (ele.checked) {
                $('#menuType').val('Parent').trigger('change');
                $('#route').val('').prop('required', false).prop('disabled', true);
                $('#icon').val('').prop('required', true).prop('disabled', false);
                $('#parMenuId').val('').prop('required', false).prop('disabled', true);

                $('#entry_option, #view_option').prop('disabled', true);
                $('#parent_option').prop('disabled', false);
            } else {
                $('#menuType').val('').trigger('change');
                $('#route').val('').prop('required', true).prop('disabled', false);
                $('#icon').val('').prop('required', false).prop('disabled', true);
                $('#parMenuId').val('').prop('required', true).prop('disabled', false);

                $('#entry_option, #view_option').prop('disabled', false);
                $('#parent_option').prop('disabled', true);
            }
        }

        function applyParentStructure(isParent) {

            if (isParent) {
                route.disabled = true;
                parent_option.disabled = false;
                entry_option.disabled = true;
                view_option.disabled = true;
                parMenuId.disabled = true;
                icon.disabled = false;
            } else {
                route.disabled = false;
                parent_option.disabled = true;
                entry_option.disabled = false;
                view_option.disabled = false;
                parMenuId.disabled = false;
                icon.disabled = true;
            }
        }

        // Clear button function
        function Reset() {
            hideTable();
            document.getElementById('query').hidden = 'hidden';
            document.getElementById('query').value = '';
            document.getElementById('execute').hidden = 'hidden';
        }

        // Validates Sequence Id should be >= 1
        function validateMenuName(MenuName) {
            if (MenuName) {
                if (MenuName.includes(' ')) {
                    $('#menuNameHelp').css('color', 'red');
                    $('#menuName').val(MenuName.replace(' ', ''))
                } else {
                    $('#menuNameHelp').css('color', 'grey');
                }
            }
        }

        // Validates Sequence Id should be >= 1
        function validateSequenceId(sequenceId) {
            if (sequenceId) {
                if (sequenceId.toString().includes('-')) {
                    sequenceId = sequenceId.replace('-', '')
                    $('#sequence_id').val(sequenceId.replace('-', ''))
                }
                if (sequenceId < 1) {
                    $('#sequenceIdHelp').css('color', 'red');
                } else {
                    $('#sequenceIdHelp').css('color', 'grey');
                }
            }
        }

        function validateInsertForm(e) {
            if (!CAN_INSERT) {
                alert("You do not have permission to create menu");
                return;
            }
            e.preventDefault();

            const form = document.getElementById('modalForm');
            const alertDialogue = document.querySelector('.alert');
            const modalInstance = bootstrap.Modal.getOrCreateInstance(
                document.getElementById('menuModal')
            );

            const insertData = new FormData(form);
            insertData.append('Insert', '1');

            $.ajax({
                url: '',
                method: 'POST',
                data: insertData,
                processData: false,
                contentType: false,

                success: function(data) {
                    console.log(data)
                    try {
                        const res = JSON.parse(data);

                        if (res.success) {
                            window.alert(res.msg);
                            form.reset();
                            modalInstance.hide();

                            alertDialogue.classList.add('d-none');
                            alertDialogue.classList.remove('d-flex');
                        } else {
                            document.getElementById('error_msg').innerHTML = res.msg;
                            alertDialogue.classList.remove('d-none');
                            alertDialogue.classList.add('d-flex');
                            alertDialogue.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    } catch (e) {
                        console.error("JSON Parse Error:", e);
                        console.log(data);
                    }
                },

                error: function(xhr) {
                    console.error("AJAX Error:", xhr);
                }
            });
        }

        function validateUpdateForm(e) {
            if (!CAN_UPDATE) {
                alert("You do not have permission to update menu");
                return;
            }
            e.preventDefault();

            const form = document.getElementById('modalForm');
            const alertBox = document.querySelector('.alert');
            const modal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById('menuModal')
            );

            const fd = new FormData(form);
            fd.append('Update', '1');

            const obj = Object.fromEntries(fd.entries());
            if (obj.Par_Menu_Id == "Inactive_Parent") {
                document.getElementById('error_msg').innerText = "Parent Menu of this Child Menu is Inactive!\nPlease make Parent Active to make Child Active";
                alertBox.classList.remove('d-none');
                alertBox.scrollIntoView({
                    behavior: 'smooth'
                });
                return;
            }

            $.ajax({
                url: '',
                method: 'POST',
                data: fd,
                processData: false,
                contentType: false,

                success: function(res) {
                    let data;
                    try {
                        data = JSON.parse(res);
                    } catch (e) {
                        console.error('Invalid JSON:', res);
                        return;
                    }

                    /* ---------- SUCCESS ---------- */
                    if (data.success) {
                        alert(data.msg);
                        form.reset();
                        modal.hide();
                        alertBox.classList.add('d-none');
                        return;
                    }

                    /* ---------- CASCADE CONFIRM ---------- */
                    if (data.confirm === true) {
                        if (confirm(data.msg)) {
                            document.getElementById('cascade').value = '1';
                            form.dispatchEvent(new Event('submit'));
                        }
                        return;
                    }

                    /* ---------- ERROR ---------- */
                    document.getElementById('error_msg').innerText = data.msg;
                    alertBox.classList.remove('d-none');
                    alertBox.scrollIntoView({
                        behavior: 'smooth'
                    });
                },

                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                }
            });
        }

        function validateParent(event, ele) {
            const modal = document.getElementById('menuModal');
            const alert = document.querySelector('.alert');

            // intended new state
            const willBeChecked = !ele.checked;

            if (modal.classList.contains('edit') && willBeChecked) {
                event.preventDefault(); // ✅ checkbox never changes
                alert.classList.remove('d-none');
                alert.classList.add('d-flex');
                alert.classList.remove('alert-danger');
                alert.classList.add('alert-warning');
                error_msg.innerHTML = 'Parent Cannot be Changed to Child';
            } else {
                alert.classList.remove('d-flex');
                alert.classList.add('d-none');
                alert.classList.remove('alert-warning');
                alert.classList.add('alert-danger');
            }
        }

        function captureInitialEditState() {
            initialEditState = {
                menuName: menuName.value,
                displayName: displayName.value,
                active: activeFlag.checked,
                parent: parentFlag.checked,
                menuType: menuType.value,
                route: route.value,
                icon: icon.value,
                parMenuId: parMenuId.value,
                sequence: sequence_id.value
            };
        }

        function restoreInitialEditState() {
            if (!initialEditState) return;

            menuName.value = initialEditState.menuName;
            displayName.value = initialEditState.displayName;
            parentFlag.checked = initialEditState.parent;
            sequence_id.value = initialEditState.sequence;

            // Enforce structure first
            applyParentChanges(parentFlag);

            // Restore common
            menuType.value = initialEditState.menuType;
            parMenuId.value = initialEditState.parMenuId;

            // Restore route/icon ONLY for child
            if (!parentFlag.checked) {
                route.value = initialEditState.route;
            } else {
                icon.value = initialEditState.icon;
            }
        }

        function changeFormStatus(isInactive, isParent) {

            // Common fields
            menuName.disabled = isInactive;
            displayName.disabled = isInactive;
            menuType.disabled = isInactive;
            parMenuId.disabled = isInactive;
            route.disabled = isInactive;
            icon.disabled = isInactive;
            sequence_id.disabled = isInactive;

            // Parent Flag
            parentFlag.disabled = isInactive;

            // Parent Menu ID logic
            if (isInactive) {
                // Inactive → everything disabled
                if (!isParent) {
                    if (document.getElementById('inactive_parent')) {
                        parMenuId.value = "Inactive_Parent";
                    }
                }
            } else {
                // Active
                if (isParent) {
                    // Parent menu → no parent selection
                    parMenuId.disabled = true;
                } else {
                    // Child menu → parent selectable
                    parMenuId.disabled = false;
                }

                if (document.getElementById('inactive_parent')) {
                    parMenuId.value = "Inactive_Parent";
                }
            }
        }

        /* Insert/Edit Modal Control */
        document.getElementById('menuModal').addEventListener('show.bs.modal', async e => {

            const b = e.relatedTarget;
            const m = b.dataset.mode;
            const p = document.getElementById('parentFlag');
            let form = document.querySelector('#menuModal form');
            let modal = document.getElementById('menuModal');

            // -------- INSERT MODE --------
            if (m === 'insert') {
                if (!CAN_INSERT) {
                    e.preventDefault();
                    alert("You do not have permission to create menu");
                    return;
                }
                modal.classList.add('insert');
                form.onsubmit = (e) => validateInsertForm(e);

                // Title & submit button
                document.getElementById('modalTitle').innerText = 'Insert New Menu';
                const btn = document.getElementById('modalSubmitBtn');
                btn.name = 'Insert';
                btn.innerText = 'Insert';

                // Reset entire form safely
                document.querySelector('#menuModal form').reset();

                // Clear hidden fields
                document.getElementById('menuId').value = '';
                document.getElementById('login_type').value = '';
                document.getElementById('platformType').disabled = false;

                // Enable Parent checkbox
                p.checked = false;

                applyParentChanges(p);

                // Parent menu dropdown reset
                document.getElementById('parMenuId').innerHTML =
                    '<option value="" disabled selected>-- Select Parent Menu Id --</option>';

                // Hide Active flag row (insert = active by default)
                document.getElementById('activeFlagRow').hidden = true;

                return;
            }

            // -------- EDIT MODE --------
            if (m === 'edit') {
                if (!CAN_UPDATE) {
                    e.preventDefault();
                    alert("You do not have permission to update menu");
                    return;
                }
                modal.classList.add('edit');
                form.onsubmit = (e) => validateUpdateForm(e);

                // Title & submit button
                document.getElementById('modalTitle').innerText = 'Update Menu';
                const btn = document.getElementById('modalSubmitBtn');
                btn.name = 'Update';
                btn.innerText = 'Update';

                // Enable/Disable Parent checkbox
                let isparent = JSON.parse(b.dataset.parentflag);
                let isactive = JSON.parse(b.dataset.activeflag);
                p.checked = isparent;

                if (!p.checked) {
                    if (isactive) {
                        await getParentMenus(b.dataset.logintype, b.dataset.platformtype);
                    } else {
                        await getParentMenus(b.dataset.logintype, b.dataset.platformtype, b.dataset.id);
                    }
                }

                menuId.value = b.dataset.id;
                loginType.value = b.dataset.logintype;
                loginType.disabled = true;
                platformType.value = b.dataset.platformtype;
                platformType.disabled = true;
                login_type.value = b.dataset.logintype;
                menuName.value = b.dataset.menuname;
                displayName.value = b.dataset.displayname;
                sequence_id.value = b.dataset.sequenceid;
                activeFlag.checked = JSON.parse(b.dataset.activeflag);

                applyParentChanges(p);
                document.getElementById('activeFlagRow').hidden = false;
                menuType.value = b.dataset.menutype;
                route.value = b.dataset.route;
                icon.value = b.dataset.icon;
                parMenuId.value = b.dataset.parmenuid;

                // Capture immutable initial state (EDIT mode only)
                captureInitialEditState();
                // Initial disable if inactive
                if (!activeFlag.checked) {
                    changeFormStatus(true, parentFlag.checked);
                    if (document.getElementById('inactive_parent')) {
                        parMenuId.value = "Inactive_Parent";
                    } else {
                        parMenuId.value = b.dataset.parmenuid;
                    }
                } else {
                    parMenuId.value = b.dataset.parmenuid;
                }

                activeFlag.onchange = function() {

                    restoreInitialEditState();

                    if (!this.checked) {
                        changeFormStatus(true, parentFlag.checked);
                        return;
                    }

                    changeFormStatus(false, parentFlag.checked);

                    // 🔒 STRUCTURE ONLY — NO WIPE
                    applyParentStructure(parentFlag.checked);
                };

                return;
            }
        });

        document.getElementById('menuModal').addEventListener('hide.bs.modal', async e => {
            let modal = document.getElementById('menuModal');
            document.getElementById('modalForm').reset();
            const alertBox = document.querySelector('.alert');
            modal.classList.remove('insert');
            modal.classList.remove('edit');
            document.getElementById('error_msg').innerText = "";
            alertBox.classList.remove('d-flex');
            alertBox.classList.add('d-none');
            initialEditState = null;
            if (document.getElementById('inactive_parent')) {
                inactive_parent.remove();
            }
        });

        /* Delete Scripts */

        function openDeleteModal(ele) {
            if (!CAN_SOFT_DELETE && !CAN_HARD_DELETE) {
                alert("You do not have permission to delete menu");
                return;
            }
            const menuId = ele.getAttribute('data-id');

            // Reset modal state
            document.getElementById('deleteForm').reset();
            document.getElementById('deleteSubmitBtn').disabled = true;
            document.getElementById('childTableSection').style.display = 'none';
            document.getElementById('childMenuBody').innerHTML = '';

            fetchDeleteDetails(menuId);

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function fetchDeleteDetails(menuId) {
            $.post('', {
                Action: 'GetDeleteDetails',
                Menu_Id: menuId
            }, function(res) {
                const data = JSON.parse(res);
                if (data.status !== 'success') {
                    alert(data.msg || 'Failed to load delete details');
                    return;
                }

                const m = data.menu;

                // Hidden + visible IDs
                document.getElementById('del_Menu_Id').value = m.Menu_Id;
                document.getElementById('del_menu_id_view').value = m.Menu_Id;

                // Menu details
                document.getElementById('del_menu_name').value = m.Menu_Name;
                document.getElementById('del_menu_type').value = m.MenuType;
                document.getElementById('del_menu_status').value = m.Active_Flag == 1 ? 'Active' : 'Inactive';

                // Attach data to delete button
                const delBtn = document.getElementById('deleteSubmitBtn');
                delBtn.dataset.menuId = m.Menu_Id;
                delBtn.dataset.deleteType = $('input[name="Delete_Type"]:checked').val() || '';
                delBtn.dataset.loginType = ''; // optional for now

                // Track parent / child for validation
                deleteContext.isParent = (m.Parent_Flag == 1);

                // Reset cascade selection on load
                $('input[name="Cascade_Confirm"]').prop('checked', false);

                // If Parent → show children
                if (m.Parent_Flag == 1 && data.children.length > 0) {
                    let rows = '';
                    data.children.forEach(c => {
                        rows += `
                    <tr>
                        <td>${c.Menu_Id}</td>
                        <td>${c.Display_Name}</td>
                        <td>${c.Active_Flag == 1 ? 'Active' : 'Inactive'}</td>
                        <td>${c.Active_Flag == 1 ? c.Sequence_Id : '-'}</td>
                    </tr>
                `;
                    });
                    document.getElementById('childMenuBody').innerHTML = rows;
                    document.getElementById('childTableSection').style.display = 'block';
                }

            });
        }

        let deleteContext = {
            isParent: false
        };

        // Detect delete type & cascade requirement
        $(document).on('change', 'input[name="Delete_Type"]', function() {
            evaluateDeleteRules();
        });

        $(document).on('change', 'input[name="Cascade_Confirm"]', function() {
            evaluateDeleteRules();
        });

        $('#finalConfirm').on('change', function() {
            evaluateDeleteRules();
        });

        function evaluateDeleteRules() {

            if (!CAN_HARD_DELETE && !CAN_SOFT_DELETE) {
                $('#deleteSubmitBtn').prop('disabled', true);
                return;
            }

            const deleteType = $('input[name="Delete_Type"]:checked').val();
            const finalConfirm = $('#finalConfirm').is(':checked');

            let enableDelete = true;

            if (!deleteType) enableDelete = false;
            if (!finalConfirm) enableDelete = false;

            $('#deleteSubmitBtn').prop('disabled', !enableDelete);
        }

        function validateDeleteForm(btn) {

            if (btn.dataset.processing === "1") return;
            btn.dataset.processing = "1";

            const menuId = btn.dataset.menuId;
            const deleteType = $('input[name="Delete_Type"]:checked').val();

            if (!menuId || !deleteType) {
                alert('Delete data missing');
                btn.dataset.processing = "0";
                return;
            }

            if (deleteType == "SOFT" && !CAN_SOFT_DELETE) {
                alert("You do not have permission to soft delete menu");
                return;
            }
            if (deleteType == "HARD" && !CAN_HARD_DELETE) {
                alert("You do not have permission to hard delete menu");
                return;
            }

            $.ajax({
                url: '',
                method: 'POST',
                data: {
                    Action: 'DELETE_MENU',
                    Menu_Id: menuId,
                    Delete_Type: deleteType,
                },
                success: function(res) {

                    let data;
                    try {
                        data = JSON.parse(res);
                    } catch (e) {
                        alert('Invalid response');
                        console.error(res);
                        return;
                    }

                    /* ---------- SUCCESS ---------- */
                    if (data.status === 'success') {
                        alert(data.msg);
                        location.reload();
                        return;
                    }

                    /* ---------- BACKEND CONFIRM ---------- */
                    if (data.status === 'confirm') {

                        if (confirm(data.msg)) {
                            $.ajax({
                                url: '',
                                method: 'POST',
                                data: {
                                    Action: 'DELETE_MENU',
                                    Menu_Id: menuId,
                                    Delete_Type: deleteType,
                                    Cascade_Confirm: 'YES'
                                },
                                success: function(r) {
                                    const d = JSON.parse(r);
                                    if (d.status === 'success') {
                                        alert(d.msg);
                                        location.reload();
                                    } else {
                                        alert(d.msg);
                                    }
                                }
                            });
                        }
                        return;
                    }

                    /* ---------- ERROR ---------- */
                    if (data.status === 'error') {
                        alert(data.msg);
                    }
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('AJAX error');
                },
                complete: function() {
                    btn.dataset.processing = "0";
                }
            });
        }
    </script>
</body>

</html>