<?php
include $_SERVER['DOCUMENT_ROOT'] . '/Victory/link.php';

if (!isset($_SESSION['RBAC'])) {
  $_SESSION['RBAC'] = [];
}

function hasMenuAccess(int $menuId): bool
{
  return isset($_SESSION['RBAC'][$menuId]);
}
$parents = [];
$children = [];
$menu_query = mysqli_query($link, "SELECT Menu_Id, Display_Name, Parent_Flag, Par_Menu_Id, Route, Icon, Menu_Type, Sequence_Id FROM menus WHERE Active_Flag = 1 AND Login_Type = 'Faculty' AND Platform_Type = 'Web' ORDER BY (CASE WHEN Parent_Flag = 1 THEN Sequence_Id ELSE 999999 END), Par_Menu_Id, FIELD(Menu_Type, 'Entry', 'View'), Sequence_Id");
while ($menu_row = mysqli_fetch_assoc($menu_query)) {
  $menu_id = (int)$menu_row['Menu_Id'];

  if ((int)$menu_row['Parent_Flag'] === 1) {
    $parents[$menu_id] = $menu_row;
  } else {
    // RBAC FILTER HERE
    if (!hasMenuAccess($menu_id)) {
      continue;
    }

    $parId = $menu_row['Par_Menu_Id'] !== null ? (int)$menu_row['Par_Menu_Id'] : 0;
    if (!isset($children[$parId])) $children[$parId] = [];
    $children[$parId][] = $menu_row;
  }
}
?>
<nav>
  <div class="logo">
    <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/Victory Logo.png" alt="..." width="70px" />
  </div>
  <div class="heading">
    <h3><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></h3>
  </div>
  <input type="checkbox" id="click" />
  <label for="click" class="menu-btn">
    <i class="fas fa-bars"></i>
  </label>
  <ul>
    <li>
      <img src="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/emp_img/<?php echo $_SESSION['Id_No']; ?>.jpg" alt="Faculty Image">
    </li>
    <li>
      <a href="#"><?php echo $_SESSION['Id_No'] . '(' . $_SESSION['Role_Name'] . ')' ?></a>
      <ul class="login-sub-menu sub-menu">
        <li><a href="/Victory/php/logout.php">Sign Out</a></li>
      </ul>
    </li>
    <li id="sign-out"><a href="/Victory/php/logout.php">Sign Out</a></li>
  </ul>
</nav>
<div class="sidebar close">
  <div class="logo-details">
    <i class="bx bx-menu"></i>
    <span class="logo_name"><?= $_SESSION['Role_Name'] ?></span>
  </div>
  <ul class="nav-links">
    <?php foreach ($parents as $parent):
      // Parent visible ONLY if it has visible children
      $pid = (int)$parent['Menu_Id'];
      if ($parent['Display_Name'] != "Dashboard" && empty($children[$pid])) {
        continue;
      }
      $pname = htmlspecialchars($parent['Display_Name'], ENT_QUOTES, 'UTF-8');
      $proute = htmlspecialchars($parent['Route'] ?? '#', ENT_QUOTES, 'UTF-8');
      $picon  = htmlspecialchars($parent['Icon'] ?? 'question', ENT_QUOTES, 'UTF-8');
      $hasSub = !empty($children[$pid]);
    ?>
      <li>
        <div class="iocn-link">
          <a href="<?= $proute ?>">
            <i class="bx bx-<?= $picon ?>"></i>
            <span class="link_name"><?= $pname ?></span>
          </a>
          <?php if ($hasSub): ?>
            <i class="bx bxs-chevron-down arrow"></i>
          <?php endif; ?>
        </div>

        <?php if ($hasSub):
          // split children into Entry and View (DB ordering preserved)
          $entries = [];
          $views   = [];
          foreach ($children[$pid] as $c) {
            if (isset($c['Menu_Type']) && strcasecmp($c['Menu_Type'], 'View') === 0) {
              $views[] = $c;
            } else {
              $entries[] = $c;
            }
          }
        ?>
          <ul class="sub-menu">
            <li>
              <a class="link_name" href="#"><label><?= $pname ?></label></a>
            </li>

            <?php foreach ($entries as $e):
              $eroute = htmlspecialchars($e['Route'] ?? '#', ENT_QUOTES, 'UTF-8');
              $ename  = htmlspecialchars($e['Display_Name'] ?? '', ENT_QUOTES, 'UTF-8');
            ?>
              <li><a href="<?= $eroute ?>"><?= $ename ?></a></li>
            <?php endforeach; ?>

            <?php if (!empty($views)): ?>
              <li>
                <a class="link_name" href="#" id="view"><label>View</label></a>
              </li>
              <?php foreach ($views as $v):
                $vroute = htmlspecialchars($v['Route'] ?? '#', ENT_QUOTES, 'UTF-8');
                $vname  = htmlspecialchars($v['Display_Name'] ?? '', ENT_QUOTES, 'UTF-8');
              ?>
                <li><a href="<?= $vroute ?>"><?= $vname ?></a></li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        <?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<script src="/Victory/js/script.js"></script>