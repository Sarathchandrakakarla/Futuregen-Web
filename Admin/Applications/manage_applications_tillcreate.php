<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 198);

requireLogin();
requireMenuAccess(MENU_ID);
$conn = connectCentralDB();

//error_reporting(0);
?>
<?php
$branches = [];
$branch_query = mysqli_query($link, "SELECT * FROM central.school_master WHERE parent_org = 'Victory' AND active_flag = 1");
while ($branch_row = mysqli_fetch_assoc($branch_query)) {
    $branches[$branch_row['school_code']] = $branch_row['display_name'];
}

if (isset($_POST['Action']) && $_POST['Action'] == "Get_User_List") {
    $branch = $_POST['Branch'];
    $type = $_POST['Type'];
    if ($branch == "VHS") {
        $db = "vtest";
    } else if ($branch == "FGS") {
        $db = "futuregen";
    }
    if ($type == "Admin") {
        $table = "admin";
        $users_query = "SELECT Admin_Id_No AS Id_No, Admin_Name AS Name FROM {$db}.{$table}";
    } else if ($type == "Faculty") {
        $table = "employee_master_data";
        $users_query = "SELECT Emp_Id AS Id_No, Emp_First_Name AS Name FROM {$db}.{$table} WHERE Status = 'Working'";
    }
    $users_query = mysqli_query($link, $users_query);
    $users = [];
    while ($users_row = mysqli_fetch_assoc($users_query)) {
        $users[] = ["Id_No" => $users_row['Id_No'], "Name" => $users_row['Name']];
    }
    echo json_encode(["User_List" => $users, "Owner_Table" => $db . "." . $table]);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (isset($input['Action']) && $input['Action'] === 'Search_Application') {

    $details = $input['details'] ?? [];

    if (!$details) {
        echo json_encode([
            "success" => false,
            "message" => "Invalid payload"
        ]);
        exit;
    }

    $conditions = [];
    $params = [];
    $types = "";

    // 🔹 Extract fields
    $App_No = $details['App_No'] ?? null;
    $First_Name = $details['First_Name'] ?? null;
    $Sur_Name = $details['Sur_Name'] ?? null;
    $Father_Name = $details['Father_Name'] ?? null;
    $Mobile = $details['Mobile'] ?? null;

    // 🔹 Build conditions (same logic as Node)

    if ($App_No) {
        $conditions[] = "app.App_No = ?";
        $params[] = $App_No;
        $types .= "s";
    }

    if ($First_Name) {
        $conditions[] = "app.First_Name LIKE ?";
        $params[] = "%" . $First_Name . "%";
        $types .= "s";
    }

    if ($Sur_Name) {
        $conditions[] = "app.Sur_Name LIKE ?";
        $params[] = "%" . $Sur_Name . "%";
        $types .= "s";
    }

    if ($Father_Name) {
        $conditions[] = "app.Father_Name LIKE ?";
        $params[] = "%" . $Father_Name . "%";
        $types .= "s";
    }

    if ($Mobile) {
        $conditions[] = "app.Mobile = ?";
        $params[] = $Mobile;
        $types .= "s";
    }

    // 🔹 At least one condition required
    if (count($conditions) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "At least one search criteria required"
        ]);
        exit;
    }

    // 🔹 Final Query (same as Node)
    $sql = "
        SELECT app.*, sm.Display_Name AS Branch_Name
        FROM applications app
        JOIN school_master sm ON app.Branch = sm.School_Code
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY app.Created_At DESC
    ";

    // 🔹 Prepare & Execute
    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "message" => $stmt->error
        ]);
        exit;
    }

    $result = $stmt->get_result();

    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

    exit;
}

if (isset($input['Action']) && $input['Action'] === 'Get_Application') {

    $appNo = $input['App_No'] ?? null;

    if (!$appNo) {
        echo json_encode(["success" => false, "message" => "Invalid App No"]);
        exit;
    }

    $sql = "
        SELECT app.*, sm.Display_Name AS Branch_Name
        FROM applications app
        JOIN school_master sm ON app.Branch = sm.School_Code
        WHERE app.App_No = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $appNo);
    $stmt->execute();

    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode([
        "success" => true,
        "data" => $data
    ]);

    exit;
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($_SESSION['school_db']['display_name']) ?></title>
    <link rel="shortcut icon" href="<?= $_SESSION['school_db']['Media_Root_Dir'] ?>/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="/Futuregen/css/sidebar-style.css" />
    <link rel="stylesheet" href="/Futuregen/css/form-style.css" />
    <!-- Boxiocns CDN Link -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<style>
    :root {
        --search-primary: #0f766e;
        --search-primary-dark: #115e59;
        --search-secondary: #e2e8f0;
        --search-text: #0f172a;
        --search-muted: #64748b;
        --search-border: #dbe4ee;
        --search-surface: #ffffff;
        --search-surface-soft: #f8fafc;
        --search-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
        --search-radius: 22px;
    }

    #sign-out {
        display: none;
    }

    @media screen and (max-width:920px) {
        #sign-out {
            display: block;
        }
    }

    .segmented-control {
        display: flex;
        gap: 6px;
        margin: 5% 8% 0 8%;
        padding: 6px;
        background: #e9edf3;
        border-radius: 12px;
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    .seg-btn {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 8px;
        background: transparent;
        cursor: pointer;
        font-weight: 600;
        color: #495057;
        transition: all 0.25s ease;
    }

    .seg-btn:hover {
        background: rgba(0, 0, 0, 0.05);
    }

    /* Active Tab */
    .seg-btn.active {
        background: #ffffff;
        color: #0d6efd;
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
    }

    /* Optional subtle press effect */
    .seg-btn:active {
        transform: scale(0.97);
    }

    /* 🔹 Primary Button (Select Staff / Modal Buttons) */
    .ref_btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        background: #4CAF50;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .ref_btn:hover {
        background: #43a047;
    }

    .ref_btn:active {
        transform: scale(0.97);
    }

    /* 🔹 Secondary Button (Cancel) */
    .ref_btn.cancel-btn {
        background: #e0e0e0;
        color: #333;
    }

    .ref_btn.cancel-btn:hover {
        background: #d5d5d5;
    }

    /* 🔹 Small Button (optional if needed) */
    .ref_btn.small-btn {
        padding: 6px 12px;
        font-size: 12px;
    }

    /* 🔹 Disabled Button */
    .ref_btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    /* 🔹 Modal Styling */
    #referral_modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
    }

    #referral_modal>div {
        background: #fff;
        padding: 20px;
        width: 320px;
        margin: 10% auto;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    /* 🔹 Modal Inputs */
    #referral_modal select {
        width: 100%;
        margin-top: 10px;
        padding: 8px;
        border-radius: 5px;
        border: 1px solid #ccc;
    }

    /* 🔹 Modal Buttons Layout */
    #referral_modal button {
        margin-right: 8px;
        margin-top: 10px;
    }

    #referral_modal .cancel-btn {
        padding: 8px 16px;
        border: none;
        border-radius: 6px;
        background: grey;
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #selected_user_display {
        background: #f5f5f5;
        padding: 6px 10px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 13px;
    }

    .view-section {
        background: #eef2f7;
        border: 1px solid #d0d7e2;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12);
    }

    .view-title {
        font-size: 14.5px;
        font-weight: 700;
        color: #d39e00;
        border-bottom: 2px solid #cbd5e1;
        padding-bottom: 5px;
        margin-bottom: 10px;
    }

    .view-table td {
        padding: 7px 12px;
        font-size: 13.5px;
        border-color: #dee2e6;
    }

    .view-label {
        color: #495057;
        font-weight: 600;
        width: 40%;
        background: #e9edf3;
    }

    .view-value {
        color: #212529;
        font-weight: 500;
        background: #ffffff;
    }

    @media screen and (max-width:900px) {
        .section {
            margin-left: 8%;
            width: 100%;
        }
    }
</style>

<body>
    <?php
    include '../sidebar.php';
    ?>

    <div class="segmented-control">
        <button class="seg-btn active" onclick="showSection('search')">🔍 Search</button>
        <button class="seg-btn" onclick="showSection('form')">✏️ Create/Edit</button>
        <button class="seg-btn" onclick="showSection('reports')">📊 Reports</button>
    </div>

    <!-- SEARCH -->
    <div id="search_section" class="section">
        <div class="container" style="background: transparent;padding: 0;">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Search Application</h5>
                </div>

                <div class="card-body">
                    <form id="searchForm">
                        <div class="row g-3">

                            <!-- Application No -->
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label">Application No</label>
                                <input type="text"
                                    class="form-control text-uppercase"
                                    name="App_No"
                                    id="App_No"
                                    placeholder="APPVHS2026001">
                            </div>

                            <!-- Student Name -->
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label">Student Name</label>
                                <input type="text"
                                    class="form-control"
                                    name="First_Name"
                                    id="First_Name"
                                    placeholder="Enter Student Name">
                            </div>

                            <!-- Surname -->
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label">Surname</label>
                                <input type="text"
                                    class="form-control"
                                    name="Sur_Name"
                                    id="Sur_Name"
                                    placeholder="Enter Surname">
                            </div>

                            <!-- Father Name -->
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label">Father Name</label>
                                <input type="text"
                                    class="form-control"
                                    name="Father_Name"
                                    id="Father_Name"
                                    placeholder="Enter Father Name">
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-4 col-sm-6">
                                <label class="form-label">Mobile</label>
                                <input type="text"
                                    class="form-control"
                                    name="Mobile"
                                    id="Mobile"
                                    placeholder="Enter Mobile Number">
                            </div>

                        </div>

                        <!-- Buttons -->
                        <div class="mt-4 d-flex gap-2">
                            <button type="button" class="btn btn-primary" id="searchBtn">
                                🔍 Search
                            </button>

                            <button type="reset" class="btn btn-outline-secondary" onclick="$('#searchResults').html('');">
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Results Container -->
        <div id="searchResults"></div>
    </div>

    <!-- CREATE / EDIT -->
    <div id="form_section" class="section" style="display:none;">
        <div class="container" style="max-width: 700px;padding: 25px 30px;">
            <div class="content">
                <div class="input-box" style="max-width:300px; margin-bottom:20px;">
                    <span class="details">Select Branch <span class="required">*</span></span>
                    <select id="branch_select" name="Branch" required>
                        <option value="" selected disabled>-- Select Branch --</option>
                        <?php
                        foreach ($branches as $branch_code => $branch_name) {
                            echo '<option value="' . $branch_code . '">' . $branch_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="content" id="application_form" style="display: none;">
                <div class="title">Student Personal Details</div>
                <form id="app_form" action="" method="POST" onsubmit="return validateAndConfirm()">
                    <input type="hidden" name="Branch_Hidden" id="branch_hidden">
                    <input type="hidden" name="force_insert" id="force_insert" value="0">
                    <input type="hidden" name="User_Table" id="created_user_table">
                    <div class="user-details">
                        <div class="input-box">
                            <span class="details">Full Name <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Fullname" id="first_name" name="First_Name" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Surname <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Surname" id="sur_name" name="Sur_Name" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Father Name <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Father Name" id="father_name" name="Father_Name" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Mother Name <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Mother Name" id="mother_name" name="Mother_Name" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Class Applied For <span class="required">*</span></span>
                            <select name="Class_Applied" id="class_applied" required>
                                <option value="" selected disabled>--Select Class Applied For --</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <span class="details">Previous Class <span class="required">*</span></span>
                            <select name="Prev_Class" id="prev_class">
                                <option value="" selected disabled>--Select Previous Class --</option>
                            </select>
                        </div>
                        <div class="gender-details">
                            <span class="gender-title">Gender <span class="required">*</span></span>
                            <div class="category">
                                <input type="radio" id="boy" value="Boy" name="Gender" required />
                                <span><label for="boy">Boy</label></span>
                                <input type="radio" id="girl" value="Girl" name="Gender" />
                                <span><label for="girl">Girl</label></span>
                            </div>
                        </div>
                        <div class="input-box">
                            <span class="details">Date Of Birth <span class="required">*</span></span>
                            <input type="date" name="DOB" id="dob" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Mobile Number <span class="required">*</span></span>
                            <input type="text" minlength="10" id="mobile" placeholder="Enter Mobile No." name="Mobile" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Aadhar Number
                                <input type="text" placeholder="Enter Aadhar No." id="aadhar" maxlength="12" name="Aadhar" />
                        </div>
                        <div class="input-box">
                            <span class="details">Mother Aadhar Number
                                <input type="text" placeholder="Enter Mother Aadhar No." id="mother_aadhar" maxlength="12" name="Mother_Aadhar" />
                        </div>
                        <div class="input-box">
                            <span class="details">Father Aadhar Number
                                <input type="text" placeholder="Enter Father Aadhar No." id="father_aadhar" maxlength="12" name="Father_Aadhar" />
                        </div>
                    </div>
                    <div class="title">Student Address Details</div>
                    <div class="user-details">
                        <div class="gender-details">
                            <span class="gender-title">Religion <span class="required">*</span></span>
                            <div class="category">
                                <input type="radio" id="indian-hindu" value="Indian-Hindu" name="Religion" required />
                                <span><label for="indian-hindu">Indian-Hindu</label></span>
                                <input type="radio" id="indian-islam" value="Indian-Islam" name="Religion" />
                                <span><label for="indian-islam">Indian-islam</label></span>
                                <input type="radio" id="indian-christian" value="Indian-Christian" name="Religion" />
                                <span><label for="indian-christian">Indian-Christian</label></span>
                            </div>
                        </div>
                        <div class="input-box">
                            <span class="details">Caste <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Caste" name="Caste" id="caste" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Category <span class="required">*</span></span>
                            <select name="Category" id="category" required>
                                <option value="" selected disabled>--Select Category--</option>
                                <option value="OC">OC</option>
                                <option value="BC">BC</option>
                                <option value="ST">ST</option>
                                <option value="SC">SC</option>
                                <option value="Mi">Mi</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <span class="details">House No.
                                <input type="text" placeholder="Enter House No." id="house_no" name="House_No" />
                        </div>
                        <div class="input-box">
                            <span class="details">Street<span class="required">*</span></span>
                            <input type="text" placeholder="Enter Area" name="Area" id="area" required />
                        </div>
                        <div class="input-box">
                            <span class="details">Village/Town <span class="required">*</span></span>
                            <input type="text" placeholder="Enter Village" name="Village" id="village" required />
                        </div>
                    </div>
                    <div class="title">Other Details</div>
                    <div class="user-details">
                        <div class="gender-details">
                            <span class="gender-title">Student Type <span class="required">*</span></span>
                            <div class="category">
                                <input type="radio" id="day_scholar" value="Day Scholar" name="Student_Type" required />
                                <span><label for="day_scholar">Day Scholar</label></span>
                                <input type="radio" id="hosteller" value="Hosteller" name="Student_Type" />
                                <span id="hosteller_label"><label for="hosteller">Hosteller</label></span>
                                <input type="radio" id="vanner" value="Vanner" name="Student_Type" />
                                <span><label for="vanner">Vanner</label></span>
                            </div>
                        </div>
                        <div class="gender-details">
                            <span class="gender-title">Referred By Type <span class="required">*</span></span>
                            <div class="category">
                                <input type="radio" id="staff" value="Staff" name="Referred_By_Type" checked />
                                <span><label for="staff">Staff</label></span>

                                <input type="radio" id="non-staff" value="Non-Staff" name="Referred_By_Type" />
                                <span><label for="non-staff">Non-Staff</label></span>
                            </div>
                        </div>
                        <div class="input-box">
                            <span class="details">Van Route</span>
                            <select class="form-control" name="Van_Route" id="van_route" disabled>
                                <option value="">-- Select Route --</option>
                                <?php
                                $van_sql = mysqli_query($link, "SELECT Van_Route FROM `van_route` ORDER BY Van_Route");
                                while ($van_row = mysqli_fetch_assoc($van_sql)) {
                                    echo '<option value="' . $van_row['Van_Route'] . '" >' . $van_row['Van_Route'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="input-box">
                            <span class="details">Previous School</span>
                            <input type="text" placeholder="Enter Previous School" id="previous_school" name="Previous_School" />
                        </div>
                        <!-- Staff Mode -->
                        <div class="input-box" id="staff_referral_box">
                            <span class="details">Referred By</span>

                            <button type="button" class="ref_btn" onclick="openReferralModal()">Select Staff</button>

                            <div id="selected_user_display" style="margin-top:8px; font-weight:bold; display:none;"></div>

                            <!-- ✅ ONLY this goes to backend -->
                            <input type="hidden" name="Referred_By" id="referred_by_hidden">
                        </div>

                        <!-- Non-Staff Mode -->
                        <div class="input-box" id="nonstaff_referral_box" style="display:none;">
                            <span class="details">Referred By</span>

                            <input type="text" placeholder="Enter Referred By" id="referred_by_text" name="Referred_By" disabled>
                        </div>
                    </div>
                    <div class="title">Payment Details</div>
                    <div class="user-details">
                        <div class="input-box">
                            <span class="details">Advance Amount</span>
                            <input type="number" placeholder="Enter Advance Amount" id="advance_amount" name="Advance_Amount" min="0" step="0.01" />
                        </div>
                        <div class="input-box">
                            <span class="details">DOP</span>
                            <input type="date" id="dop" name="DOP" disabled />
                        </div>
                        <div class="input-box">
                            <span class="details">Payment Type</span>
                            <select name="Payment_Type" id="payment_type" disabled>
                                <option value="" selected>--Select Payment Type--</option>
                                <option value="Cash">Cash</option>
                                <option value="UPI">UPI</option>
                            </select>
                        </div>
                        <div class="input-box">
                            <span class="details">Transaction Id</span>
                            <input type="text" placeholder="Enter Transaction Id" id="transaction_id" name="Transaction_Id" disabled />
                        </div>
                        <div class="input-box" id="show_qr_box" style="display:none;">
                            <span class="details">&nbsp;</span>
                            <button type="button" class="ref_btn" id="show_qr_btn">Show QR</button>
                        </div>
                        <div class="input-box" id="qr_image_box" style="display:none;">
                            <span class="details">UPI QR</span>
                            <img
                                src="https://victoryschools.in/Futuregen/App%20Files/Images/Victory%20Edu%20Society%20QR.jpg"
                                alt="Victory Edu Society QR"
                                style="max-width:100%; width:220px; border-radius:8px; border:1px solid #dbe4ee; padding:6px; background:#fff;">
                        </div>
                    </div>
                    <div class="button">
                        <div class="btn-wrapper"
                            <?php if (!can('create', MENU_ID)) { ?>
                            title="You don't have permission to insert student data"
                            <?php } ?>>
                            <input type="submit" name="add" value="Insert" <?php echo !can('create', MENU_ID) ? 'disabled' : ''; ?> />
                        </div>
                        <input type="reset" value="Clear" />
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- REPORTS -->
    <div id="reports_section" class="section" style="display:none;">
        <h3>Reports Section</h3>
    </div>

    <!-- Staff Selection Modal -->
    <div id="referral_modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:#00000080;">
        <div style="background:white; padding:20px; width:300px; margin:10% auto; border-radius:8px;">

            <h3>Select Staff</h3>

            <!-- Branch -->
            <select id="modal_branch">
                <option value="">Select Branch</option>
                <?php
                foreach ($branches as $branch_code => $branch_name) {
                    echo '<option value="' . $branch_code . '">' . $branch_name . '</option>';
                }
                ?>
            </select>

            <!-- User Type -->
            <select id="modal_user_type">
                <option value="">Select Type</option>
                <option value="Admin">Admin</option>
                <option value="Faculty">Faculty</option>
            </select>

            <!-- Users -->
            <select id="modal_user_list">
                <option value="">Select User</option>
            </select>

            <br><br>

            <input type="hidden" id="created_table">
            <button class="ref_btn" onclick="selectUser()">Select</button>
            <button class="cancel-btn" onclick="closeReferralModal()">Cancel</button>
        </div>
    </div>

    <!-- View Application Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Application Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" id="viewContent">
                    <!-- Filled dynamically -->
                </div>

            </div>
        </div>
    </div>

    <!-- Scripts -->

    <!-- Show Sections -->
    <script>
        function showSection(type) {
            // Hide all
            document.getElementById("search_section").style.display = "none";
            document.getElementById("form_section").style.display = "none";
            document.getElementById("reports_section").style.display = "none";

            // Remove active class
            document.querySelectorAll(".seg-btn").forEach(btn => btn.classList.remove("active"));

            // Show selected
            if (type === "search") {
                document.getElementById("search_section").style.display = "block";
            } else if (type === "form") {
                document.getElementById("form_section").style.display = "block";
            } else if (type === "reports") {
                document.getElementById("reports_section").style.display = "block";
            }

            // Highlight active button
            event.target.classList.add("active");
        }
    </script>

    <!-- On Branch Selection -->
    <script>
        const branchDropdown = document.getElementById("branch_select");
        const form = document.getElementById("application_form");
        const hiddenBranch = document.getElementById("branch_hidden");

        function loadClasses(branch) {
            class_applied.innerHTML = '<option value="" disabled selected>--Select Class Applied For --</option>';
            prev_class.innerHTML = '<option value="" disabled selected>--Select Previous Class --</option>';

            const baseClasses = ["PreKG", "LKG", "UKG"];

            baseClasses.forEach(cls => {
                let option1 = document.createElement("option");
                option1.value = cls;
                option1.textContent = cls;

                let option2 = document.createElement("option");
                option2.value = cls;
                option2.textContent = cls;

                class_applied.appendChild(option1);
                prev_class.appendChild(option2);
            });

            let maxClass = 0;

            if (branch === "VHS") {
                maxClass = 10;
            } else if (branch === "FGS") {
                maxClass = 8;
            }

            for (let i = 1; i <= maxClass; i++) {
                let option1 = document.createElement("option");
                option1.value = i + " CLASS";
                option1.textContent = i + " CLASS";

                let option2 = document.createElement("option");
                option2.value = i + " CLASS";
                option2.textContent = i + " CLASS";

                class_applied.appendChild(option1);
                prev_class.appendChild(option2);
            }
        }

        function handleStudentTypeByBranch(branch) {

            // Reset selection always on branch change
            document.querySelectorAll('input[name="Student_Type"]').forEach(r => r.checked = false);

            // Reset van route
            vanRoute.value = "";
            vanRoute.disabled = true;

            if (branch === "VHS") {
                // Hide Hosteller
                hosteller_label.style.display = "none";
                hosteller.style.display = "none";
            } else if (branch === "FGS") {
                // Show all
                hosteller_label.style.display = "inline-block";
                hosteller.style.display = "inline-block";
            }
        }

        branchDropdown.addEventListener("change", function() {
            if (this.value) {
                form.style.display = "block";
                hiddenBranch.value = this.value;
                branchDropdown.disabled = 'disabled';

                loadClasses(this.value);
                handleStudentTypeByBranch(this.value);
            } else {
                form.style.display = "none";
            }
        });
    </script>

    <!-- On Student Type Selection -->
    <script>
        const studentTypeRadios = document.getElementsByName("Student_Type");
        const vanRoute = document.getElementById("van_route");

        function handleStudentTypeChange() {
            let selected = document.querySelector('input[name="Student_Type"]:checked');

            if (selected && selected.value === "Vanner") {
                vanRoute.disabled = false;
                vanRoute.required = true;
            } else {
                vanRoute.value = ""; // reset
                vanRoute.disabled = true;
                vanRoute.required = false;
            }
        }

        studentTypeRadios.forEach(radio => {
            radio.addEventListener("change", handleStudentTypeChange);
        });
    </script>

    <!-- On Referred By Type Selection -->
    <script>
        const staffRadio = document.getElementById("staff");
        const nonStaffRadio = document.getElementById("non-staff");

        const staffBox = document.getElementById("staff_referral_box");
        const nonStaffBox = document.getElementById("nonstaff_referral_box");

        const hiddenInput = document.getElementById("referred_by_hidden");
        const textInput = document.getElementById("referred_by_text");
        const display = document.getElementById("selected_user_display");

        // Modal elements
        const modal = document.getElementById("referral_modal");
        const modalBranch = document.getElementById("modal_branch");
        const modalUserType = document.getElementById("modal_user_type");
        const modalUserList = document.getElementById("modal_user_list");


        // 🔹 Toggle Staff / Non-Staff
        function handleReferralType() {

            if (staffRadio.checked) {
                staffBox.style.display = "block";
                nonStaffBox.style.display = "none";

                // ✅ Enable staff hidden
                hiddenInput.disabled = false;

                // ❌ Disable non-staff input
                textInput.disabled = true;
                textInput.value = "";

            } else {
                staffBox.style.display = "none";
                nonStaffBox.style.display = "block";

                // ❌ Disable staff hidden
                hiddenInput.disabled = true;
                hiddenInput.value = "";

                // ✅ Enable non-staff input
                textInput.disabled = false;

                document.getElementById('created_user_table').value = "";
            }
        }

        staffRadio.addEventListener("change", handleReferralType);
        nonStaffRadio.addEventListener("change", handleReferralType);


        // 🔹 Modal
        function openReferralModal() {
            modal.style.display = "block";
        }

        function closeReferralModal() {
            modal.style.display = "none";
        }


        // 🔹 Load Users
        function loadUsers() {
            modalUserList.innerHTML = '<option value="">Select User</option>';

            const branch = modalBranch.value;
            const type = modalUserType.value;

            if (!branch || !type) return;

            $.ajax({
                url: '',
                method: 'post',
                data: {
                    Action: "Get_User_List",
                    Branch: branch,
                    Type: type
                },
                success: function(raw_data) {
                    try {
                        let data = JSON.parse(raw_data);
                        let usersList = data['User_List'];
                        let user_table = data['Owner_Table'];

                        if (usersList.length == 0) {
                            alert('No Users Found');
                        } else {
                            document.getElementById('created_table').value = user_table;
                            usersList.forEach(u => {
                                if (u.Id_No != "APPADMIN") {
                                    let opt = document.createElement("option");
                                    opt.value = u.Id_No;
                                    opt.textContent = u.Id_No + " - " + u.Name;
                                    modalUserList.appendChild(opt);
                                }
                            });
                        }

                    } catch (err) {
                        console.log(err);
                    }
                }
            });
        }

        modalBranch.addEventListener("change", loadUsers);
        modalUserType.addEventListener("change", loadUsers);


        // 🔹 Select User
        function selectUser() {
            const selectedOption = modalUserList.options[modalUserList.selectedIndex];
            const user_table = document.getElementById('created_table').value;

            if (!selectedOption.value) return alert("Select a user");

            const id = selectedOption.value;
            const name = selectedOption.textContent.split(" - ")[1];

            const combined = id + " - " + name;

            // ✅ Store
            hiddenInput.value = combined;

            // ✅ Show
            display.style.display = 'block';
            display.textContent = combined;
            created_user_table.value = user_table;

            closeReferralModal();
        }
    </script>

    <!-- Class_Applied,Prev_Class Validation -->
    <script>
        const classApplied = document.getElementById("class_applied");
        const prevClass = document.getElementById("prev_class");

        function handleClassDependency() {
            if (classApplied.value === "PreKG") {
                prevClass.value = "";
                prevClass.disabled = true;
                prevClass.removeAttribute("required");
            } else {
                prevClass.disabled = false;
                prevClass.setAttribute("required", true);
            }
        }

        classApplied.addEventListener("change", handleClassDependency);
    </script>

    <!-- Final Validation -->
    <script>
        const advanceAmountInput = document.getElementById("advance_amount");
        const dopInput = document.getElementById("dop");
        const paymentTypeInput = document.getElementById("payment_type");
        const transactionIdInput = document.getElementById("transaction_id");
        const showQrBox = document.getElementById("show_qr_box");
        const qrImageBox = document.getElementById("qr_image_box");
        const showQrBtn = document.getElementById("show_qr_btn");

        function resetPaymentFields() {
            dopInput.value = "";
            paymentTypeInput.value = "";
            transactionIdInput.value = "";
            dopInput.disabled = true;
            paymentTypeInput.disabled = true;
            transactionIdInput.disabled = true;
            transactionIdInput.removeAttribute("required");
            showQrBox.style.display = "none";
            qrImageBox.style.display = "none";
        }

        function syncPaymentSection() {
            const amount = parseFloat(advanceAmountInput.value);

            if (!advanceAmountInput.value || isNaN(amount) || amount <= 0) {
                resetPaymentFields();
                return;
            }

            dopInput.disabled = false;
            paymentTypeInput.disabled = false;

            if (paymentTypeInput.value === "UPI") {
                transactionIdInput.disabled = false;
                transactionIdInput.setAttribute("required", true);
                showQrBox.style.display = "block";
            } else {
                transactionIdInput.value = "";
                transactionIdInput.disabled = true;
                transactionIdInput.removeAttribute("required");
                showQrBox.style.display = "none";
                qrImageBox.style.display = "none";
            }

            if (paymentTypeInput.value === "Cash") {
                transactionIdInput.value = "";
            }
        }

        advanceAmountInput.addEventListener("input", syncPaymentSection);
        paymentTypeInput.addEventListener("change", syncPaymentSection);
        showQrBtn.addEventListener("click", function() {
            qrImageBox.style.display = qrImageBox.style.display === "none" ? "block" : "none";
        });

        syncPaymentSection();

        function validateAndConfirm() {
            if (staffRadio.checked && !hiddenInput.value) {
                alert("Please select staff");
                return false;
            }

            if (nonStaffRadio.checked && !textInput.value.trim()) {
                alert("Please enter referred by");
                return false;
            }

            const amount = parseFloat(advanceAmountInput.value);
            if (advanceAmountInput.value && !isNaN(amount) && amount > 0) {
                if (!dopInput.value) {
                    alert("Please select DOP");
                    return false;
                }

                if (!paymentTypeInput.value) {
                    alert("Please select Payment Type");
                    return false;
                }

                if (paymentTypeInput.value === "UPI" && !transactionIdInput.value.trim()) {
                    alert("Please enter Transaction Id");
                    return false;
                }
            }

            if (!confirm('Confirm to Add Student Data?')) {
                return false;
            }

            return true;
        }
    </script>

    <!-- Search Form Validation and get & load results -->
    <script>
        function formatDate(dateStr) {
            if (!dateStr) return "";

            let d = new Date(dateStr);

            // Convert to IST using locale
            let options = {
                timeZone: "Asia/Kolkata",
                day: "2-digit",
                month: "2-digit",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: true
            };

            let formatted = d.toLocaleString("en-GB", options);

            // Replace / with -
            return formatted.replace(/\//g, "-");
        }

        function viewApplication(data) {
            showViewModal(data);
        }

        function showViewModal(data) {

            function row(label, value) {
                return `
                        <tr>
                            <td class="view-label">${label}</td>
                            <td class="view-value">${value || '-'}</td>
                        </tr>
                    `;
            }

            let html = `
                        <!-- 🔹 Basic -->
                        <div class="view-section mb-3">
                            <div class="view-title">Basic Details</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('Student Name', data.First_Name)}
                                    ${row('Surname', data.Sur_Name)}
                                    ${row('Father Name', data.Father_Name)}
                                    ${row('Mother Name', data.Mother_Name)}
                                    ${row('Gender', data.Gender)}
                                    ${row('DOB', data.DOB)}
                                    ${row('Mobile', data.Mobile)}
                                </tbody>
                            </table>
                        </div>

                        <!-- 🔹 Academic -->
                        <div class="view-section mb-3">
                            <div class="view-title">Academic Details</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('Class Applied', data.Class_Applied)}
                                    ${row('Previous Class', data.Prev_Class)}
                                    ${row('Previous School', data.Previous_School)}
                                </tbody>
                            </table>
                        </div>

                        <!-- 🔹 Address -->
                        <div class="view-section mb-3">
                            <div class="view-title">Address</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('House No', data.House_No)}
                                    ${row('Area / Street', data.Area)}
                                    ${row('Village', data.Village)}
                                </tbody>
                            </table>
                        </div>

                        <!-- 🔹 Identity -->
                        <div class="view-section mb-3">
                            <div class="view-title">Identity Details</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('Student Aadhar', data.Aadhar)}
                                    ${row('Father Aadhar', data.Father_Aadhar)}
                                    ${row('Mother Aadhar', data.Mother_Aadhar)}
                                </tbody>
                            </table>
                        </div>

                        <!-- 🔹 Other -->
                        <div class="view-section mb-3">
                            <div class="view-title">Other Details</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('Religion', data.Religion)}
                                    ${row('Caste', data.Caste)}
                                    ${row('Category', data.Category)}
                                    ${row('Student Type', data.Student_Type)}
                                    ${row('Van Route', data.Van_Route)}
                                    ${row('Branch', data.Branch_Name)}
                                    ${row('Referred By', data.Referred_By)}
                                    ${row('Created', formatDate(data.Created_At))}
                                </tbody>
                            </table>
                        </div>

                        <!-- 🔹 Payment -->
                        <div class="view-section">
                            <div class="view-title success">Payment Details</div>
                            <table class="table table-sm view-table mb-0">
                                <tbody>
                                    ${row('Advance Amount', data.Advance_Amount)}
                                    ${row('Date of Payment', data.DOP)}
                                    ${row('Payment Type', data.Payment_Type)}
                                    ${
                                        data.Payment_Type === 'UPI'
                                        ? row('Transaction ID', data.Transaction_Id)
                                        : ''
                                    }
                                </tbody>
                            </table>
                        </div>

                    `;

            $("#viewContent").html(html);

            let modal = new bootstrap.Modal(document.getElementById('viewModal'));
            modal.show();
        }

        function shareApplication(branch, appNo) {

            let url = `https://victoryschools.in/Futuregen/Files/Applications/${branch}/Application_${appNo}.pdf`;

            window.open(url, "_blank");
        }

        $(document).ready(function() {

            $("#searchBtn").click(function() {

                // 🔹 Get values
                let App_No = $("#App_No").val().trim().toUpperCase();
                let First_Name = $("#First_Name").val().trim();
                let Sur_Name = $("#Sur_Name").val().trim();
                let Father_Name = $("#Father_Name").val().trim();
                let Mobile = $("#Mobile").val().trim();

                // 🔹 Validation: At least one field
                if (!App_No && !First_Name && !Sur_Name && !Father_Name && !Mobile) {
                    alert("Please enter at least one search field");
                    return;
                }

                // 🔹 App_No format validation (same as app)
                if (App_No) {
                    let regex = /^APP[A-Z]{3}\d{4}\d{3}$/;
                    if (!regex.test(App_No)) {
                        alert("Invalid Application No Format (APPVHS2026001)");
                        return;
                    }
                }

                // 🔹 Build payload (same structure)
                let payload = {
                    Action: "Search_Application",
                    details: {
                        App_No: App_No,
                        First_Name: First_Name,
                        Sur_Name: Sur_Name,
                        Father_Name: Father_Name,
                        Mobile: Mobile
                    }
                };

                $.ajax({
                    url: "",
                    method: "POST",
                    data: JSON.stringify(payload),
                    contentType: "application/json",
                    dataType: "json",

                    success: function(res) {

                        if (res.success) {

                            if (res.data.length === 0) {
                                alert("No Applications Found");
                                $("#searchResults").html("");
                                return;
                            }

                            let html = `
                                        <div class="container" style="background:transparent;padding:0;">
                                            <div class="card shadow-sm mt-4">
                                                <div class="card-header bg-secondary text-white">
                                                    <h6 class="mb-0">Application Records</h6>
                                                </div>

                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-striped mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>S.No</th>
                                                                    <th>App No</th>
                                                                    <th>Student Name</th>
                                                                    <th>Surname</th>
                                                                    <th>Father Name</th>
                                                                    <th>DOB</th>
                                                                    <th>Mobile</th>
                                                                    <th>Class Applied</th>
                                                                    <th>Branch</th>
                                                                    <th>Referred By</th>
                                                                    <th>Created Date</th>
                                                                    <th>Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                        `;

                            res.data.forEach(function(row, index) {

                                html += `
                                            <tr>
                                                <td>${index + 1}</td>
                                                <td>${row.App_No || ''}</td>
                                                <td>${row.First_Name || ''}</td>
                                                <td>${row.Sur_Name || ''}</td>
                                                <td>${row.Father_Name || ''}</td>
                                                <td>${row.DOB || ''}</td>
                                                <td>${row.Mobile || ''}</td>
                                                <td style="white-space:nowrap;">${row.Class_Applied || ''}</td>
                                                <td>${row.Branch_Name || ''}</td>
                                                <td>${row.Referred_By || ''}</td>
                                                <td style="white-space:nowrap;">${formatDate(row.Created_At)}</td>
                                                <td>
                                                    <div class="d-flex gap-2">

                                                        <!-- 👁 View -->
                                                        <button class="btn btn-sm btn-outline-dark"
                                                                onclick='viewApplication(${JSON.stringify(row)})'>
                                                            View
                                                        </button>

                                                        <!-- ✏️ Edit -->
                                                        <button class="btn btn-sm btn-outline-primary"
                                                                onclick="editApplication('${row.App_No}')">
                                                            Edit
                                                        </button>

                                                        <!-- 📤 Share -->
                                                        <button class="btn btn-sm btn-outline-success"
                                                                onclick="shareApplication('${row.Branch}', '${row.App_No}')">
                                                            PDF
                                                        </button>

                                                    </div>
                                                </td>
                                            </tr>
                                        `;
                            });

                            html += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;

                            $("#searchResults").html(html);

                        } else {
                            alert(res.message || "Something went wrong");
                            $("#searchResults").html("");
                        }
                    },

                    error: function(err) {
                        console.error(err);
                        alert("Server error");
                        $("#searchResults").html("");
                    }
                });

            });

        });
    </script>

    <?php
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    function formatDate($date, $format = "DD-MM-YYYY")
    {
        if (empty($date)) return '';

        // Detect format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            // YYYY-MM-DD
            $d = DateTime::createFromFormat('Y-m-d', $date);
        } elseif (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            // DD-MM-YYYY
            $d = DateTime::createFromFormat('d-m-Y', $date);
        } else {
            return $date; // unknown format, return as-is
        }

        if (!$d) return $date;

        // Output format
        switch ($format) {
            case "DD-MM-YYYY":
                return $d->format('d/m/Y');
            case "YYYY-MM-DD":
                return $d->format('Y/m/d');
            default:
                return $d->format('d/m/Y');
        }
    }
    function dbValue($val)
    {
        return ($val === null || $val === '') ? "NULL" : "'" . $val . "'";
    }
    if (isset($_POST['add'])) {
        $branch = validate($_POST['Branch'] ?? $_POST['Branch_Hidden'] ?? '');
        $first_name = validate($_POST['First_Name']);
        $sur_name = validate($_POST['Sur_Name']);
        $father_name = validate($_POST['Father_Name']);
        $mother_name = validate($_POST['Mother_Name']);
        $class_applied = validate($_POST['Class_Applied']);
        $prev_class = isset($_POST['Prev_Class']) ? validate($_POST['Prev_Class']) : null;
        $gender = validate($_POST['Gender']);
        $dob = validate($_POST['DOB']);
        $mobile = validate($_POST['Mobile']);
        $aadhar = isset($_POST['Aadhar']) && $_POST['Aadhar'] != "" ? validate($_POST['Aadhar']) : null;
        $mother_aadhar = isset($_POST['Mother_Aadhar']) && $_POST['Mother_Aadhar'] != "" ? validate($_POST['Mother_Aadhar']) : null;
        $father_aadhar = isset($_POST['Father_Aadhar']) && $_POST['Father_Aadhar'] != "" ? validate($_POST['Father_Aadhar']) : null;
        $religion = validate($_POST['Religion']);
        $caste = validate($_POST['Caste']);
        $category = validate($_POST['Category']);
        $house_no = isset($_POST['House_No']) && $_POST['House_No'] != "" ? validate($_POST['House_No']) : null;
        $area = validate($_POST['Area']);
        $village = validate($_POST['Village']);
        $student_type = validate($_POST['Student_Type']);
        $referred_by_type = validate($_POST['Referred_By_Type']);
        $previous_school = isset($_POST['Previous_School']) && $_POST['Previous_School'] != "" ? validate($_POST['Previous_School']) : null;
        $van_route = isset($_POST['Van_Route']) && $_POST['Van_Route'] != "" ? validate($_POST['Van_Route']) : null;
        $referred_by_raw = validate($_POST['Referred_By'] ?? '');
        $created_user_table = validate($_POST['User_Table']);
        $force_insert = $_POST['force_insert'] ?? '0';
        $advance_amount_raw = trim($_POST['Advance_Amount'] ?? '');
        $dop = isset($_POST['DOP']) && $_POST['DOP'] != "" ? validate($_POST['DOP']) : null;
        $payment_type = isset($_POST['Payment_Type']) && $_POST['Payment_Type'] != "" ? validate($_POST['Payment_Type']) : null;
        $transaction_id = isset($_POST['Transaction_Id']) && $_POST['Transaction_Id'] != "" ? validate($_POST['Transaction_Id']) : null;

        if ($advance_amount_raw === '' || (is_numeric($advance_amount_raw) && (float)$advance_amount_raw <= 0)) {
            $advance_amount = null;
            $dop = null;
            $payment_type = null;
            $transaction_id = null;
        } else {
            if (!is_numeric($advance_amount_raw) || (float)$advance_amount_raw <= 0) {
                echo "<script>alert('Advance Amount must be greater than 0');</script>";
                return;
            }

            $advance_amount = validate($advance_amount_raw);

            if (!$dop) {
                echo "<script>alert('DOP is required when Advance Amount is entered');</script>";
                return;
            }

            if (!$payment_type) {
                echo "<script>alert('Payment Type is required when Advance Amount is entered');</script>";
                return;
            }

            if (!in_array($payment_type, ['Cash', 'UPI'])) {
                echo "<script>alert('Invalid Payment Type');</script>";
                return;
            }

            if ($payment_type === 'UPI' && !$transaction_id) {
                echo "<script>alert('Transaction Id is required for UPI payment');</script>";
                return;
            }

            if ($payment_type === 'Cash') {
                $transaction_id = null;
            }

            $dop = str_replace('/', '-', formatDate($dop));
        }

        /* Populating dynamic inputs */
        // Class_Applied,Prev_Class
        echo "<script>
            document.getElementById('branch_select').disabled = 'disabled';
            loadClasses('{$branch}');
            handleStudentTypeByBranch('{$branch}');
        </script>";
        if ($student_type == "Vanner") {
            echo "<script>van_route.disabled='';</script>";
        }
        // Referred_By
        if ($referred_by_type == "Staff") {
            $parts = explode('-', $referred_by_raw, 2);
            $owner_id = trim($parts[0]);
            $referred_by_name = trim($parts[1]);
            $owner_table = $created_user_table;
            echo "<script>
                selected_user_display.style.display='block';
                selected_user_display.textContent='{$referred_by_raw}';
                document.getElementById('referred_by_hidden').value = '{$referred_by_raw}';
            </script>";
        } else {
            $owner_id = null;
            $owner_table = null;
            $referred_by_name = $referred_by_raw;
            echo "<script>
                selected_user_display.style.display='none';
                document.getElementById('referred_by_text').value = '{$referred_by_raw}';
            </script>";
        }

        echo "
        <script>
            document.getElementById('branch_select').value = '{$branch}';
            document.getElementById('branch_hidden').value = '{$branch}';
            document.getElementById('created_user_table').value = '{$created_user_table}';
            document.getElementById('force_insert').value = '{$force_insert}';
            document.getElementById('first_name').value = '{$first_name}';
            document.getElementById('sur_name').value = '{$sur_name}';
            document.getElementById('father_name').value = '{$father_name}';
            document.getElementById('mother_name').value = '{$mother_name}';
            document.getElementById('class_applied').value = '{$class_applied}';
            document.getElementById('prev_class').value = '{$prev_class}';
            document.getElementById('" . strtolower($gender) . "').checked = true;
            document.getElementById('dob').value = '{$dob}';
            document.getElementById('mobile').value = '{$mobile}';
            document.getElementById('aadhar').value = '{$aadhar}';
            document.getElementById('mother_aadhar').value = '{$mother_aadhar}';
            document.getElementById('father_aadhar').value = '{$father_aadhar}';
            document.getElementById('" . strtolower($religion) . "').checked = true;
            document.getElementById('caste').value = '{$caste}';
            document.getElementById('category').value = '{$category}';
            document.getElementById('house_no').value = '{$house_no}';
            document.getElementById('area').value = '{$area}';
            document.getElementById('village').value = '{$village}';
            document.getElementById('" . str_replace(' ', '_', strtolower($student_type)) . "').checked = true;
            document.getElementById('" . strtolower($referred_by_type) . "').checked = true;
            document.getElementById('previous_school').value = '{$previous_school}';
            document.getElementById('van_route').value = '{$van_route}';
            document.getElementById('advance_amount').value = '{$advance_amount}';
            document.getElementById('dop').value = '{$dop}';
            document.getElementById('payment_type').value = '{$payment_type}';
            document.getElementById('transaction_id').value = '{$transaction_id}';
            syncPaymentSection();
        </script>";

        // Opening Form On Error
        echo "<script>
                document.getElementById('search_section').style.display = 'none';
                document.getElementById('form_section').style.display = 'block';
                document.getElementById('reports_section').style.display = 'none';
                document.querySelectorAll('.seg-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.seg-btn')[1].classList.add('active');
                application_form.style.display = 'block';
                handleReferralType();
                syncPaymentSection();
            </script>";

        if ($class_applied != 'PreKG' && !$prev_class) {
            echo "<script>alert('Previous Class is Mandatory');</script>";
            return;
        }

        // Previous Class should be [Class Applied,Class Applied-1,Class Applied-2]
        $classList = ['PreKG', 'LKG', 'UKG', '1 CLASS', '2 CLASS', '3 CLASS', '4 CLASS', '5 CLASS', '6 CLASS', '7 CLASS', '8 CLASS', '9 CLASS', '10 CLASS'];

        if ($class_applied && $prev_class && $class_applied != 'PreKG') {

            $classIndex = array_search($class_applied, $classList);
            $prevIndex = array_search($prev_class, $classList);

            if ($classIndex === false || $prevIndex === false) {
                echo "<script>alert('Invalid Class Selection');</script>";
                return;
            }

            // Allowed: same, previous 1, previous 2
            if (!in_array($prevIndex, [$classIndex, $classIndex - 1, $classIndex - 2])) {
                echo "<script>alert('Invalid Previous Class selection');</script>";
                return;
            }
        }

        if (strlen($mobile) < 10) {
            echo "<script>alert('Invalid Mobile Number');</script>";
            return;
        }
        if ($aadhar && strlen($aadhar) != 12) {
            echo "<script>alert('Student Aadhar Number should be 12 digits!');</script>";
            return;
        }
        if ($mother_aadhar && strlen($mother_aadhar) != 12) {
            echo "<script>alert('Mother Aadhar Number should be 12 digits!');</script>";
            return;
        }
        if ($father_aadhar && strlen($father_aadhar) != 12) {
            echo "<script>alert('Father Aadhar Number should be 12 digits!');</script>";
            return;
        }
        $dob = formatDate($dob);

        $dup_check_query = mysqli_query($link, "SELECT * FROM central.applications WHERE First_Name = '$first_name' AND Sur_Name = '$sur_name' AND Father_Name = '$father_name' AND DOB = '$dob'");
        if (mysqli_num_rows($dup_check_query) != 0 && $force_insert != "1") {
            $dup = mysqli_fetch_assoc($dup_check_query);
            echo "<script>
                    setTimeout(function() {
                        let confirmMsg = 
                            'Duplicate Application Found!\\n\\n' +
                            'Student Name: {$dup['First_Name']}\\n' +
                            'Sur Name: {$dup['Sur_Name']}\\n' +
                            'Father Name: {$dup['Father_Name']}\\n' +
                            'DOB: {$dup['DOB']}\\n\\n' +
                            'Do you want to create another application?';

                        if (confirm(confirmMsg)) {
                            var form = document.getElementById('app_form');
                            form.force_insert.value = '1';
                            if (form.requestSubmit) {
                            let hiddenAdd = document.createElement('input');
                            hiddenAdd.type = 'hidden';
                            hiddenAdd.name = 'add';
                            hiddenAdd.value = 'Insert';
                            form.appendChild(hiddenAdd);
                                form.requestSubmit();
                            } else {
                                let hiddenAdd = document.createElement('input');
                                hiddenAdd.type = 'hidden';
                                hiddenAdd.name = 'add';
                                hiddenAdd.value = 'Insert';
                                form.appendChild(hiddenAdd);
                                form.submit();
                            }
                        }
                    }, 100);
                </script>";
            return;
        } else {
            mysqli_begin_transaction($link);
            $year = date('Y');

            $last_query = mysqli_query($link, "SELECT App_No FROM central.applications WHERE Branch = '$branch' AND YEAR(Created_At) = '$year' ORDER BY App_No DESC LIMIT 1 FOR UPDATE ");

            $newSeq = 1;

            if (mysqli_num_rows($last_query) > 0) {
                $last = mysqli_fetch_assoc($last_query);
                $lastSeq = (int)substr($last['App_No'], -3);
                $newSeq = $lastSeq + 1;
            }

            $seq = str_pad($newSeq, 3, "0", STR_PAD_LEFT);
            $appNo = "APP{$branch}{$year}{$seq}";

            $created_by_id = $_SESSION['Admin_Id_No'];
            $created_by_name = $_SESSION['Admin_Name'];
            $created_by_table = "victory_db.admin";
            $created_user_type = "Admin";
            $created_source = "Website";

            $insert_query = "INSERT INTO central.applications(App_No, First_Name, Sur_Name, Father_Name, Mother_Name, Class_Applied, Prev_Class, Gender, DOB, Mobile, Aadhar, Mother_Aadhar, Father_Aadhar, Religion, Caste, Category, House_No, Area, Village, Student_Type, Branch, Previous_School, Van_Route, Owner_Id, Owner_Table, Referred_By, Advance_Amount, DOP, Payment_Type, Transaction_Id, Created_By_Id, Created_By_Name, Created_By_Table, Created_User_Type, Created_Source, Created_At) VALUES ('$appNo','$first_name','$sur_name','$father_name','$mother_name','$class_applied'," . dbValue($prev_class) . ",'$gender','$dob','$mobile'," . dbValue($aadhar) . "," . dbValue($mother_aadhar) . "," . dbValue($father_aadhar) . ",'$religion','$caste','$category'," . dbValue($house_no) . ",'$area','$village','$student_type','$branch'," . dbValue($previous_school) . "," . dbValue($van_route) . "," . dbValue($owner_id) . "," . dbValue($owner_table) . ",'$referred_by_name'," . dbValue($advance_amount) . "," . dbValue($dop) . "," . dbValue($payment_type) . "," . dbValue($transaction_id) . ",'$created_by_id','$created_by_name','$created_by_table','$created_user_type','$created_source',NOW())";

            // Inserting Application
            if (mysqli_query($link, $insert_query)) {
                mysqli_commit($link);
                try {
                    $details = [
                        "First_Name" => $first_name,
                        "Sur_Name" => $sur_name,
                        "Father_Name" => $father_name,
                        "Mother_Name" => $mother_name,
                        "DOB" => $dob,
                        "Gender" => $gender,
                        "Mobile" => $mobile,
                        "Class_Applied" => $class_applied,
                        "Prev_Class" => $prev_class,
                        "Aadhar" => $aadhar,
                        "Mother_Aadhar" => $mother_aadhar,
                        "Father_Aadhar" => $father_aadhar,
                        "Religion" => $religion,
                        "Caste" => $caste,
                        "Category" => $category,
                        "House_No" => $house_no,
                        "Area" => $area,
                        "Village" => $village,
                        "Student_Type" => $student_type,
                        "Previous_School" => $previous_school,
                        "Van_Route" => $van_route,
                        "Branch" => $branch,
                        "Owner_Table" => $owner_table,
                        "Owner_Id" => $owner_id,
                        "Referred_By" => $referred_by_name,
                        "Advance_Amount" => $advance_amount,
                        "DOP" => $dop,
                        "Payment_Type" => $payment_type,
                        "Transaction_Id" => $transaction_id,
                    ];

                    $nodeUrl = "http://192.168.1.115:3000/generateapplicationpdf";

                    $payload = json_encode([
                        "details" => $details,
                        "App_No" => $appNo
                    ]);

                    $ch = curl_init($nodeUrl);

                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST => true,
                        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                        CURLOPT_POSTFIELDS => $payload,
                        CURLOPT_TIMEOUT => 30
                    ]);

                    $response = curl_exec($ch);

                    if (curl_errno($ch)) {
                        throw new Exception(curl_error($ch));
                    }

                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                    if ($httpCode !== 200) {
                        throw new Exception("Node API HTTP " . $httpCode);
                    }

                    $nodeResponse = json_decode($response, true);

                    if (!$nodeResponse || !$nodeResponse['success']) {
                        throw new Exception($nodeResponse['message'] ?? "PDF failed");
                    }
                } catch (Exception $e) {
                    error_log("PDF Generation Failed: " . $e->getMessage());
                }

                echo "<script>
                        alert('Application Created Successfully! App No: {$appNo}');
                        window.location = window.location.href;
                    </script>";
            } else {
                mysqli_rollback($link);

                echo "<script>alert('Application Creation Failed!');</script>";
            }
        }
    }
    ?>
</body>

</html>
