<?php
include_once('../../link.php');
include_once('../includes/rbac_helper.php');

define('MENU_ID', 3);

requireLogin();
requireMenuAccess(MENU_ID);

$central_link = $central ?? connectCentralDB();
$branches = [];
$branch_query = mysqli_query($central_link, "SELECT school_code AS School_Code, display_name AS Display_Name, db_name AS Db_Name FROM school_master WHERE active_flag = 1");

while ($row = mysqli_fetch_assoc($branch_query)) {
    $branches[] = $row;
}

if (isset($_POST['action']) && $_POST['action'] === 'fetch_users') {

    $branch = mysqli_real_escape_string($link, $_POST['branch']);
    $type = mysqli_real_escape_string($link, $_POST['type']);
    $branch_db = '';

    foreach ($branches as $b) {
        if ($b['School_Code'] === $branch) {
            $branch_db = $b['Db_Name'];
            break;
        }
    }

    if ($branch_db === '') {
        echo json_encode([]);
        exit;
    }

    if ($type === 'Admin') {
        $sql = "SELECT Admin_Id_No AS Id_No, Admin_Name AS Name FROM {$branch_db}.admin";
    } else {
        $sql = "SELECT Emp_Id AS Id_No, Emp_First_Name AS Name FROM {$branch_db}.employee_master_data WHERE Status = 'Working'";
    }

    $result = mysqli_query($link, $sql);
    $users = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = $row;
        }
    }

    echo json_encode($users);
    exit;
}

$app_data = null;

if (isset($_POST['app_id'])) {

    $app_id = mysqli_real_escape_string($link, $_POST['app_id']);

    $app_query = mysqli_query($link, "
        SELECT * FROM central.applications 
        WHERE App_No = '$app_id'
    ");

    if ($app_row = mysqli_fetch_assoc($app_query)) {

        if ($app_row['Status'] === 'Joined') {
            echo "<script>alert('This application is already converted to student'); window.location.href='/Futuregen/Admin/Applications/manage_applications.php';</script>";
            exit;
        }

        $app_data = $app_row;
    }
}

error_reporting(0);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
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

    .page-context {
        display: inline-flex;
        align-items: center;
        gap: 10px;

        margin-top: 10px;

        padding: 8px 14px;

        background: #f4f6f9;
        border: 1px solid #dce3ea;
        border-radius: 8px;

        font-size: 14px;
        font-weight: 500;
    }

    .context-label {
        color: #6c757d;
    }

    .context-value {
        color: #0d6efd;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    .referral-modal {
        position: fixed;
        z-index: 9999;
        inset: 0;

        display: none;

        justify-content: center;
        align-items: center;

        padding: 15px;

        background: rgba(0, 0, 0, 0.5);

        overflow-y: auto;
    }

    .referral-modal-content {
        background: #fff;
        width: 100%;
        max-width: 500px;

        padding: 24px;
        border-radius: 10px;

        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);

        animation: modalFade 0.2s ease;
    }

    .referral-modal-content h3 {
        margin-bottom: 20px;
        font-size: 22px;
    }

    .modal-buttons {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .modal-buttons .btn {
        min-width: 100px;
    }

    @keyframes modalFade {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 600px) {

        .referral-modal {
            align-items: flex-start;
            padding-top: 80px;
        }

        .referral-modal-content {
            padding: 18px;
            border-radius: 8px;
            max-width: 350px;
            width: 100%;
            margin: 0 auto;
        }

        .referral-modal-content h3 {
            font-size: 18px;
        }

        .modal-buttons {
            flex-direction: column;
        }

        .modal-buttons .btn {
            width: 100%;
        }
    }

    .btn {
        display: inline-block;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
        text-align: center;
        text-decoration: none;
        white-space: nowrap;
        vertical-align: middle;
        cursor: pointer;
        user-select: none;
        border: 1px solid transparent;
        border-radius: 4px;
        transition: all 0.15s ease-in-out;
    }

    .btn-primary {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }

    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-secondary:hover {
        background-color: #5c636a;
        border-color: #565e64;
    }
</style>

<body>
    <?php
    include '../sidebar.php';
    ?>

    <div class="container">

        <div class="content">
            <div class="title">Student Personal Details</div>
            <?php if ($app_data) { ?>
                <div class="page-context">
                    <span class="context-label">Application No.:</span>
                    <span class="context-value">
                        <?= htmlspecialchars($app_data['App_No']) ?>
                    </span>
                </div>
            <?php } ?>
            <form action="" method="POST" onsubmit="return validateStudentSubmit();">
                <input type="hidden" name="app_id" id="app_id">
                <input type="hidden" name="Referred_By_Id" id="referred_by_id">
                <input type="hidden" name="selected_staff" id="selected_staff_value">
                <input type="hidden" name="Original_Referred_By" id="Original_Referred_By">
                <input type="hidden" name="Original_Student_Type" id="Original_Student_Type">
                <input type="hidden" name="Original_Van_Route" id="Original_Van_Route">
                <div class="user-details">
                    <div class="input-box">
                        <span class="details">Id No. <span class="required">*</span></span>
                        <input type="text" placeholder="Enter Id No" id="id_no" name="Id_No" oninput="this.value = this.value.toUpperCase()" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Admission No.
                            <input type="text" placeholder="Enter Admission No" id="adm_no" name="Adm_No" />
                    </div>
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
                        <span class="details">Class <span class="required">*</span></span>
                        <select name="Stu_Class" id="class">
                            <option value="selectclass" selected disabled>--Select Class --</option>
                            <option value="PreKG">PreKG</option>
                            <option value="LKG">LKG</option>
                            <option value="UKG">UKG</option>
                            <?php
                            for ($i = 1; $i <= 8; $i++) {
                                echo "<option value='" . $i . " CLASS" . "'>" . $i . " CLASS" . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="input-box">
                        <span class="details">Section <span class="required">*</span></span>
                        <select name="Stu_Section" id="section">
                            <option value="selectsection" selected disabled>--Select Section --</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                            <option value="E">E</option>
                        </select>
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">Gender <span class="required">*</span></span>
                        <div class="category">
                            <input type="radio" id="boy" value="Boy" name="Gender" />
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
                            <input type="radio" id="indian-hindu" value="Indian-Hindu" name="Religion" />
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
                        <select name="Category" id="category">
                            <option value="selectcategory" selected disabled>--Select Category--</option>
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
                            <span><label for="hosteller">Hosteller</label></span>
                            <input type="radio" id="vanner" value="Vanner" name="Student_Type" />
                            <span><label for="vanner">Vanner</label></span>
                        </div>
                    </div>
                    <div class="input-box">
                        <span class="details">Date of Join <span class="required">*</span></span>
                        <input type="date" name="DOJ" id="doj" value="<?php if (isset($doj)) {
                                                                            echo '';
                                                                        } else {
                                                                            echo date("Y-m-d");
                                                                        } ?>" required />
                    </div>
                    <div class="input-box">
                        <span class="details">Previous School</span>
                        <input type="text" placeholder="Enter Previous School" id="previous_school" name="Previous_School" />
                    </div>
                    <div class="input-box">
                        <span class="details">Van Route</span>
                        <select class="form-control" name="Van_Route" id="van_route">
                            <option value="">-- Select Route --</option>
                            <?php
                            $van_sql = mysqli_query($link, "SELECT Van_Route FROM `van_route` ORDER BY Van_Route");
                            while ($van_row = mysqli_fetch_assoc($van_sql)) {
                                echo '<option value="' . $van_row['Van_Route'] . '" >' . $van_row['Van_Route'] . '</option>';
                            }
                            ?>
                        </select>
                        <small id="vanRouteHelper" class="text-muted">Only applicable for Vanner students</small>
                    </div>
                    <div class="gender-details">
                        <span class="gender-title">Referred By Type <span class="required">*</span></span>
                        <div class="category">
                            <input type="radio" id="staff" value="Staff" name="Referred_By_Type" required />
                            <span><label for="staff">Staff</label></span>
                            <input type="radio" id="non-staff" value="Non-Staff" name="Referred_By_Type" required />
                            <span><label for="non-staff">Non-Staff</label></span>
                        </div>
                    </div>

                    <!-- Staff -->
                    <div class="input-box" id="staff_box">
                        <button type="button" class="btn btn-primary" onclick="openReferralModal()">Select Staff</button>
                        <div id="selected_staff" style="margin-top:8px; display:none;"></div>
                    </div>

                    <!-- Non Staff -->
                    <div class="input-box" id="nonstaff_box" style="display:none;">
                        <input type="text" name="Referred_By" id="referred_by_text" placeholder="Enter Referred By">
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

    <div id="referral_modal" class="referral-modal">

        <div class="referral-modal-content">

            <h3>Select Staff</h3>

            <div class="input-box">
                <span class="details">Branch</span>

                <select id="branch" name="Branch">
                    <option value="">-- Select Branch --</option>

                    <?php foreach ($branches as $b) { ?>
                        <option value="<?= $b['School_Code'] ?>">
                            <?= $b['Display_Name'] ?>
                        </option>
                    <?php } ?>

                </select>
            </div>

            <div class="input-box">
                <span class="details">User Type</span>

                <select id="user_type" name="User_Type">
                    <option value="">-- Select Type --</option>
                    <option value="Admin">Admin</option>
                    <option value="Faculty">Faculty</option>
                </select>
            </div>

            <div class="input-box">
                <span class="details">User</span>

                <select id="user" name="Owner_Id">
                    <option value="">-- Select User --</option>
                </select>
            </div>

            <div class="modal-buttons">
                <button type="button" class="btn btn-primary" onclick="selectUser()">
                    Select
                </button>

                <button type="button" class="btn btn-secondary" onclick="closeReferralModal()">
                    Cancel
                </button>
            </div>

        </div>
    </div>

    <script>
        const staffRadio = document.querySelector('input[value="Staff"]');
        const nonStaffRadio = document.querySelector('input[value="Non-Staff"]');
        const staff_box = document.getElementById('staff_box');
        const nonstaff_box = document.getElementById('nonstaff_box');
        const referred_by_text = document.getElementById('referred_by_text');
        const selected_staff = document.getElementById('selected_staff');
        const branch = document.getElementById('branch');
        const user_type = document.getElementById('user_type');
        const user = document.getElementById('user');
        const studentTypeRadios = document.querySelectorAll('input[name="Student_Type"]');
        const vanRoute = document.getElementById('van_route');
        const originalReferredBy = document.getElementById('Original_Referred_By');
        const originalVanRoute = document.getElementById('Original_Van_Route');

        function selectedStudentType() {
            const selected = document.querySelector('input[name="Student_Type"]:checked');
            return selected ? selected.value : '';
        }

        function isEmptyRoute(value) {
            return value === '' || value === 'NULL';
        }

        function rememberCurrentRoute() {
            if (!isEmptyRoute(vanRoute.value)) {
                originalVanRoute.value = vanRoute.value;
            }
        }

        function handleStudentTypeChange() {
            const studentType = selectedStudentType();

            if (studentType === 'Vanner') {
                vanRoute.disabled = false;
                vanRoute.required = true;

                if (isEmptyRoute(vanRoute.value) && !isEmptyRoute(originalVanRoute.value)) {
                    vanRoute.value = originalVanRoute.value;
                }
            } else {
                rememberCurrentRoute();
                vanRoute.value = '';
                vanRoute.disabled = true;
                vanRoute.required = false;
            }
        }

        function toggleReferral() {
            if (staffRadio.checked) {
                if (referred_by_text.value.trim() !== '') {
                    originalReferredBy.value = referred_by_text.value;
                }

                staff_box.style.display = 'block';
                nonstaff_box.style.display = 'none';
            } else {
                staff_box.style.display = 'none';
                nonstaff_box.style.display = 'block';
                document.getElementById('referred_by_id').value = '';
                document.getElementById('selected_staff_value').value = '';
                selected_staff.style.display = 'none';

                if (referred_by_text.value.trim() === '' && originalReferredBy.value.trim() !== '') {
                    referred_by_text.value = originalReferredBy.value;
                }
            }
        }

        staffRadio.addEventListener('change', toggleReferral);
        nonStaffRadio.addEventListener('change', toggleReferral);
        referred_by_text.addEventListener('input', function() {
            if (nonStaffRadio.checked) {
                originalReferredBy.value = referred_by_text.value;
            }
        });
        vanRoute.addEventListener('change', rememberCurrentRoute);
        studentTypeRadios.forEach(radio => radio.addEventListener('change', handleStudentTypeChange));

        function openReferralModal() {
            document.getElementById('referral_modal').style.display = 'flex';
        }

        function closeReferralModal() {
            document.getElementById('referral_modal').style.display = 'none';
        }

        function selectUser() {

            let selected = user.value;
            let text = user.options[user.selectedIndex].text;

            if (!selected) return alert("Select user");

            document.getElementById('referred_by_id').value = selected;
            document.getElementById('selected_staff_value').value = selected + " - " + text;

            let name = text.split(" - ")[1];

            document.getElementById('selected_staff').innerText = selected + " - " + text;
            document.getElementById('selected_staff').style.display = 'block';

            closeReferralModal();
        }

        function fetchReferralUsers(selectedUser = '') {

            let selectedBranch = branch.value;
            let selectedType = user_type.value;

            if (!selectedBranch || !selectedType) return;

            let data = new FormData();

            data.append('action', 'fetch_users');
            data.append('branch', selectedBranch);
            data.append('type', selectedType);

            fetch('', {
                    method: 'POST',
                    body: data
                })
                .then(res => res.json())
                .then(users => {

                    let options = '<option value="">-- Select User --</option>';

                    users.forEach(u => {
                        options += `<option value="${u.Id_No}">${u.Name}</option>`;
                    });

                    user.innerHTML = options;

                    if (selectedUser) {
                        user.value = selectedUser;
                    }
                });
        }

        branch.addEventListener('change', fetchReferralUsers);
        user_type.addEventListener('change', fetchReferralUsers);

        function validateStudentSubmit() {
            const studentType = selectedStudentType();

            if (!studentType) {
                alert('Please select Student Type');
                return false;
            }

            if (studentType === 'Vanner' && isEmptyRoute(vanRoute.value)) {
                alert('Please select Van Route for Vanner student');
                return false;
            }

            if (!staffRadio.checked && !nonStaffRadio.checked) {
                alert('Please select Referred By Type');
                return false;
            }

            if (staffRadio.checked && (!branch.value || !user_type.value || !user.value)) {
                alert('Please select Branch, Type and User');
                return false;
            }

            if (staffRadio.checked && !document.getElementById('referred_by_id').value) {
                alert("Select staff");
                return false;
            }

            if (nonStaffRadio.checked && !document.getElementById('referred_by_text').value.trim()) {
                alert("Enter referred by");
                return false;
            }

            if (!confirm('Confirm to Add Student Data?')) {
                return false;
            }

            return true;
        }

        toggleReferral();
        handleStudentTypeChange();
    </script>

    <?php
    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($app_data) {
        echo "<script>
            document.getElementById('app_id').value = " . json_encode($app_data['App_No']) . ";

            document.getElementById('first_name').value = " . json_encode($app_data['First_Name']) . ";
            document.getElementById('sur_name').value = " . json_encode($app_data['Sur_Name']) . ";
            document.getElementById('father_name').value = " . json_encode($app_data['Father_Name']) . ";
            document.getElementById('mother_name').value = " . json_encode($app_data['Mother_Name']) . ";
            document.getElementById('class').value = " . json_encode($app_data['Class_Applied']) . ";
            document.getElementById('" . str_replace('"', '', strtolower($app_data['Gender'])) . "').checked = true;
            document.getElementById('dob').value = " . json_encode(date('Y-m-d', strtotime(str_replace('/', '-', $app_data['DOB'])))) . ";
            document.getElementById('mobile').value = " . json_encode($app_data['Mobile']) . ";
            document.getElementById('aadhar').value = " . json_encode($app_data['Aadhar']) . ";
            document.getElementById('mother_aadhar').value = " . json_encode($app_data['Mother_Aadhar']) . ";
            document.getElementById('father_aadhar').value = " . json_encode($app_data['Father_Aadhar']) . ";
            document.getElementById('" . strtolower($app_data['Religion']) . "').checked = true;
            document.getElementById('caste').value = " . json_encode($app_data['Caste']) . ";
            document.getElementById('category').value = " . json_encode($app_data['Category']) . ";
            document.getElementById('house_no').value = " . json_encode($app_data['House_No']) . ";
            document.getElementById('area').value = " . json_encode($app_data['Area']) . ";
            document.getElementById('village').value = " . json_encode($app_data['Village']) . ";
            document.getElementById('previous_school').value = " . json_encode($app_data['Previous_School']) . ";
            document.getElementById('Original_Referred_By').value = " . json_encode($app_data['Referred_By'] ?? '') . ";
            document.getElementById('Original_Student_Type').value = " . json_encode($app_data['Student_Type'] ?? '') . ";
            document.getElementById('Original_Van_Route').value = " . json_encode($app_data['Van_Route'] ?? '') . ";
            if (" . json_encode(!empty($app_data['Student_Type'])) . ") {
                const appStudentType = " . json_encode($app_data['Student_Type'] ?? '') . ";
                const appStudentTypeRadio = document.querySelector('input[name=\"Student_Type\"][value=\"' + appStudentType + '\"]');
                if (appStudentTypeRadio) {
                    appStudentTypeRadio.checked = true;
                }
            }
            document.getElementById('van_route').value = " . json_encode($app_data['Van_Route'] ?? '') . ";
            handleStudentTypeChange();
        </script>";

        if (!empty($app_data['Owner_Id'])) {

            echo "<script>
                document.querySelector('input[value=\"Staff\"]').checked = true;
                toggleReferral();
                document.getElementById('referred_by_id').value = " . json_encode($app_data['Owner_Id']) . ";
                document.getElementById('selected_staff_value').value = " . json_encode($app_data['Owner_Id'] . ' - ' . $app_data['Referred_By']) . ";
                document.getElementById('selected_staff').innerText = " . json_encode($app_data['Owner_Id'] . ' - ' . $app_data['Referred_By']) . ";
                document.getElementById('selected_staff').style.display = 'block';
                document.getElementById('branch').value = " . json_encode(str_contains(($app_data['Owner_Table'] ?? ''), 'victory_db') ? 'VHS' : 'FGS')  . ";
                document.getElementById('user_type').value = " . json_encode(str_contains(($app_data['Owner_Table'] ?? ''), '.admin') ? 'Admin' : 'Faculty') . ";
                fetchReferralUsers(" . json_encode($app_data['Owner_Id']) . ");
                setTimeout(() => {
                    document.getElementById('user').value = " . json_encode($app_data['Owner_Id']) . ";
                }, 500);
            </script>";
        } else {

            echo "<script>
                document.querySelector('input[value=\"Non-Staff\"]').checked = true;
                toggleReferral();
                document.getElementById('referred_by_text').value = " . json_encode($app_data['Referred_By']) . ";
            </script>";
        }
    }

    if (isset($_POST["add"])) {
        if (!can('create', MENU_ID)) {
            echo "<script>alert('You don\'t have permission to insert student data');
                    location.replace('" . $_SERVER['PHP_SELF'] . "')</script>";
            exit;
        }
        $id = validate($_POST['Id_No']);
        $admno = validate($_POST['Adm_No']);
        $firstname = validate($_POST['First_Name']);
        $surname = validate($_POST['Sur_Name']);
        $fathername = validate($_POST['Father_Name']);
        $mothername = validate($_POST['Mother_Name']);
        $dob = validate($_POST['DOB']);
        $mobile = validate($_POST['Mobile']);
        $aadhar = validate($_POST['Aadhar']);
        $mother_aadhar = validate($_POST['Mother_Aadhar']);
        $father_aadhar = validate($_POST['Father_Aadhar']);
        $caste = validate($_POST['Caste']);
        $houseno = validate($_POST['House_No']);
        $area = validate($_POST['Area']);
        $village = validate($_POST['Village']);
        $doj = validate($_POST['DOJ']);
        $previous_school = validate($_POST['Previous_School']);
        $student_type = validate($_POST['Student_Type'] ?? '');
        $valid_student_types = ['Day Scholar', 'Hosteller', 'Vanner'];
        $route = isset($_POST['Van_Route']) ? validate($_POST['Van_Route']) : '';
        if ($route === 'NULL') {
            $route = '';
        }
        if ($student_type !== 'Vanner') {
            $route = '';
        }
        $referred_by_id = $_POST['Referred_By_Id'] ?? null;
        $referred_by_name = '';

        if (($_POST['Referred_By_Type'] ?? '') === 'Staff') {

            $selected_staff = $_POST['selected_staff'] ?? '';
            $selected_staff_parts = explode(' - ', $selected_staff, 2);
            $referred_by_name = validate($selected_staff_parts[1] ?? '');
            $referred_by_id = validate($referred_by_id);
        } else {

            $referred_by_name = validate($_POST['Referred_By']);
            $referred_by_id = null;
        }

        $referred_by = $referred_by_name;

        echo "<script>document.getElementById('id_no').value = '" . $id . "'</script>";
        echo "<script>document.getElementById('adm_no').value = '" . $admno . "'</script>";
        echo "<script>document.getElementById('first_name').value = '" . $firstname . "'</script>";
        echo "<script>document.getElementById('sur_name').value = '" . $surname . "'</script>";
        echo "<script>document.getElementById('father_name').value = '" . $fathername . "'</script>";
        echo "<script>document.getElementById('mother_name').value = '" . $mothername . "'</script>";
        echo "<script>document.getElementById('dob').value = '" . $dob . "'</script>";
        echo "<script>document.getElementById('mobile').value = '" . $mobile . "'</script>";
        echo "<script>document.getElementById('aadhar').value = '" . $aadhar . "'</script>";
        echo "<script>document.getElementById('mother_aadhar').value = '" . $mother_aadhar . "'</script>";
        echo "<script>document.getElementById('father_aadhar').value = '" . $father_aadhar . "'</script>";
        echo "<script>document.getElementById('caste').value = '" . $caste . "'</script>";
        echo "<script>document.getElementById('house_no').value = '" . $houseno . "'</script>";
        echo "<script>document.getElementById('area').value = '" . $area . "'</script>";
        echo "<script>document.getElementById('village').value = '" . $village . "'</script>";
        echo "<script>document.getElementById('doj').value = '" . $doj . "'</script>";
        echo "<script>document.getElementById('previous_school').value = '" . $previous_school . "'</script>";
        echo "<script>document.getElementById('van_route').value = '" . $route . "'</script>";
        echo "<script>document.getElementById('app_id').value = '" . ($_POST['app_id'] ?? '') . "'</script>";
        echo "<script>document.getElementById('referred_by_id').value = '" . ($referred_by_id ?? '') . "'</script>";
        echo "<script>document.getElementById('selected_staff_value').value = '" . ($_POST['selected_staff'] ?? '') . "'</script>";
        echo "<script>
            document.getElementById('Original_Referred_By').value = " . json_encode($_POST['Original_Referred_By'] ?? $referred_by) . ";
            document.getElementById('Original_Student_Type').value = " . json_encode($_POST['Original_Student_Type'] ?? $student_type) . ";
            document.getElementById('Original_Van_Route').value = " . json_encode($_POST['Original_Van_Route'] ?? $route) . ";
            const submittedStudentTypeRadio = document.querySelector('input[name=\"Student_Type\"][value=\"' + " . json_encode($student_type) . " + '\"]');
            if (submittedStudentTypeRadio) {
                submittedStudentTypeRadio.checked = true;
            }
            handleStudentTypeChange();
        </script>";
        if (($_POST['Referred_By_Type'] ?? '') === 'Staff') {
            echo "<script>document.querySelector('input[value=\"Staff\"]').checked = true; toggleReferral(); document.getElementById('selected_staff').innerText = '" . ($_POST['selected_staff'] ?? '') . "'; document.getElementById('selected_staff').style.display = 'block';</script>";
        } else {
            echo "<script>document.querySelector('input[value=\"Non-Staff\"]').checked = true; toggleReferral(); document.getElementById('referred_by_text').value = '" . $referred_by . "'</script>";
        }

        if ($_POST['Stu_Class']) {
            $class = validate($_POST['Stu_Class']);
            echo "<script>document.getElementById('class').value = '" . $class . "'</script>";
            if ($_POST['Stu_Section']) {
                $section = validate($_POST['Stu_Section']);
                echo "<script>document.getElementById('section').value = '" . $section . "'</script>";
                if ($_POST['Gender']) {
                    $gender = validate($_POST['Gender']);
                    if ($gender == "Boy") {
                        echo "<script>document.getElementById('boy').checked = true;</script>";
                    } else if ($gender == "Girl") {
                        echo "<script>document.getElementById('girl').checked = true;</script>";
                    }
                    if ($_POST['Religion']) {
                        $religion = validate($_POST['Religion']);
                        echo "<script>document.getElementById('" . strtolower($religion) . "').checked = true;</script>";
                        if ($_POST['Category']) {
                            $category = validate($_POST['Category']);
                            echo "<script>document.getElementById('category').value = '" . $category . "'</script>";

                            if (!in_array($student_type, $valid_student_types, true)) {
                                echo "<script>alert('Please Select Student Type!')</script>";
                            } else if ($student_type === 'Vanner' && $route === '') {
                                echo "<script>alert('Please Select Van Route for Vanner student!')</script>";
                            } else {
                                $student_type = mysqli_real_escape_string($link, $student_type);
                                $route = mysqli_real_escape_string($link, $route);

                                $d = explode('-', $dob);
                                $j = explode('-', $doj);
                                /* //Removing 20 from 2023
                            if (substr($j[0], 0, strlen("20")) == "20") {
                                $j[0] = substr($j[0], strlen("20"));
                            }
                            //Removing 19 from 1998
                            else if (substr($j[0], 0, strlen("19")) == "19") {
                                $j[0] = substr($j[0], strlen("19"));
                            } */
                                $dob = $d[2] . "-" . $d[1] . "-" . $d[0];
                                $doj = $j[2] . "-" . $j[1] . "-" . $j[0];

                                if (mysqli_num_rows(mysqli_query($link, "SELECT * FROM `student_master_data` WHERE Id_No = '$id'")) != 0) {
                                    echo "<script>alert('Student with Id: " . $id . " Already Exists!!')</script>";
                                } else {

                                    /* Student Creation(student_master_data) and Student Login Creation(student) */
                                    $studentCreated = false;

                                    mysqli_begin_transaction($link);

                                    try {
                                        $referred_by_id_sql = $referred_by_id
                                            ? "'$referred_by_id'"
                                            : "NULL";

                                        $route_sql = $route === '' ? "NULL" : "'$route'";

                                        /* ---------- Insert Student ---------- */
                                        $student_sql = mysqli_query(
                                            $link,
                                            "INSERT INTO student_master_data
                                        (S_No, Id_No, Adm_No, First_Name, Sur_Name, Father_Name, Mother_Name,
                                        DOB, Gender, Mobile, Aadhar, Mother_Aadhar, Father_Aadhar, Stu_Class,
                                        Stu_Section, Religion, Caste, Category, House_No, Area, Village, DOJ,
                                        Previous_School, Van_Route, Referred_By, Referred_By_Id, Student_Type, Siblings)
                                        VALUES (
                                        '', '$id', '$admno', '$firstname', '$surname',
                                        '$fathername', '$mothername', '$dob', '$gender',
                                        '$mobile', '$aadhar', '$mother_aadhar', '$father_aadhar',
                                        '$class', '$section', '$religion', '$caste', '$category',
                                        '$houseno', '$area', '$village', '$doj',
                                        '$previous_school', $route_sql, '$referred_by', $referred_by_id_sql,
                                        '$student_type', NULL)"
                                        );

                                        if (!$student_sql) {
                                            throw new Exception('Student insert failed');
                                        }

                                        /* ---------- Insert Login ---------- */
                                        $chk = mysqli_query(
                                            $link,
                                            "SELECT 1 FROM student WHERE Id_No = '$id' LIMIT 1"
                                        );

                                        if (mysqli_num_rows($chk)) {
                                            throw new Exception('Login already exists for this student');
                                        }

                                        $password = "FGST" . rand(1111, 9999);
                                        $hash = password_hash($password, PASSWORD_DEFAULT);

                                        $login_sql = mysqli_query(
                                            $link,
                                            "INSERT INTO student(Id_No, Stu_Name, Stu_Password, Stu_Hash)
                                        VALUES ('$id', '$firstname', '$password', '$hash')"
                                        );

                                        if (!$login_sql) {
                                            throw new Exception('Login insert failed');
                                        }

                                        mysqli_commit($link);
                                        $studentCreated = true;
                                    } catch (Exception $e) {

                                        mysqli_rollback($link);
                                        echo "<script>alert('" . $e->getMessage() . "');</script>";
                                    }

                                    if ($studentCreated) {

                                        if (!empty($_POST['app_id'])) {

                                            $app_id = mysqli_real_escape_string($link, $_POST['app_id']);
                                            $id_no = $id;
                                            $converted_by = mysqli_real_escape_string($link, $_SESSION['Admin_Id_No'] ?? '');

                                            $application_update = mysqli_query($link, "
                                            UPDATE central.applications
                                            SET 
                                                Status = 'Joined',
                                                Converted_By = '$converted_by',
                                                Converted_At = NOW(),
                                                Id_No = '$id_no'
                                            WHERE App_No = '$app_id'
                                        ");

                                            if (!$application_update) {
                                                mysqli_query($link, "
                                                UPDATE central.applications
                                                SET Status = 'Joined'
                                                WHERE App_No = '$app_id'
                                            ");
                                            }
                                        }

                                        //SMS Sending
                                        $text = "Dear sir/Madam, we thank you for your trust on our victory schools and joining your child " . $firstname . " in the class " . $class . " " . $section . " with ID No: " . $id . ".We promise you to take of your child to the best of your expectation. Principal, Victory schools,Kodur-Ph: 08566-244584";
                                        $sms_mobile = $mobile;
                                        if (str_contains($sms_mobile, ',')) {
                                            $sms_mobile = explode(',', $sms_mobile, 2)[0];
                                        } else if (str_contains($sms_mobile, ' ')) {
                                            $sms_mobile = explode(' ', $sms_mobile, 2)[0];
                                        } else {
                                            $sms_mobile = $sms_mobile;
                                        }
                                        $sms_mobile = trim($sms_mobile);
                                        echo '<a href="https://www.alots.in/sms-panel/api/http/index.php?username=victoryschool&apikey=2A26D-FA42A&apirequest=Text&sender=VICKDR&mobile=' . $sms_mobile . '&message=' . $text . '&route=TRANS&TemplateID=1707174971721622158&format=JSON" id="sms_link" hidden>' . $sms_mobile . '</a>';
                                        echo '<script>
                                            //Send Message API
                                            async function send(url){
                                                response = await fetch(url)
                                            }
                                            send(document.getElementById("sms_link").href);
                                        </script>';

                                        /* Student School,Vehicle Fee Insertion(stu_fee_master_data) */
                                        mysqli_begin_transaction($link);

                                        try {

                                            /* ---------- School Fee ---------- */
                                            $sfQ = mysqli_query(
                                                $link,
                                                "SELECT Fee FROM actual_fee
                                            WHERE Type='School Fee' AND Class='$class'
                                            LIMIT 1"
                                            );

                                            if (!$sfQ || mysqli_num_rows($sfQ) == 0) {
                                                throw new Exception('School fee not configured');
                                            }

                                            $school_fee = mysqli_fetch_assoc($sfQ)['Fee'];

                                            $sfInsert = mysqli_query(
                                                $link,
                                                "INSERT INTO stu_fee_master_data VALUES (
                                                '', '$id', '$firstname', '$class', '$section',
                                                '$area', 'School Fee',
                                                '$school_fee', '0', '$school_fee', '$school_fee', NULL)"
                                            );

                                            if (!$sfInsert) {
                                                throw new Exception('School fee insert failed');
                                            }

                                            /* ---------- Admission Fee ---------- */
                                            $afQ = mysqli_query(
                                                $link,
                                                "SELECT Fee FROM actual_fee
                                            WHERE Type='Admission Fee' AND Class='$class'
                                            LIMIT 1"
                                            );

                                            if (!$afQ || mysqli_num_rows($afQ) == 0) {
                                                throw new Exception('Admission fee not configured');
                                            }

                                            $admission_fee = mysqli_fetch_assoc($afQ)['Fee'];

                                            $afInsert = mysqli_query(
                                                $link,
                                                "INSERT INTO stu_fee_master_data VALUES (
                                                '', '$id', '$firstname', '$class', '$section',
                                                '$area', 'Admission Fee',
                                                '$admission_fee', '0', '$admission_fee', '$admission_fee', NULL)"
                                            );

                                            if (!$afInsert) {
                                                throw new Exception('Admission fee insert failed');
                                            }

                                            /* ---------- Vehicle Fee ---------- */
                                            if ($student_type === "Vanner" && $route !== "") {

                                                $vfQ = mysqli_query(
                                                    $link,
                                                    "SELECT Fee FROM actual_fee
                                                WHERE Type='Vehicle Fee' AND Route='$route'
                                                LIMIT 1"
                                                );

                                                if (!$vfQ || mysqli_num_rows($vfQ) == 0) {
                                                    throw new Exception('Vehicle fee not configured');
                                                }

                                                $vehicle_fee = mysqli_fetch_assoc($vfQ)['Fee'];

                                                $vfInsert = mysqli_query(
                                                    $link,
                                                    "INSERT INTO stu_fee_master_data VALUES (
                                                    '', '$id', '$firstname', '$class', '$section',
                                                    '$area', 'Vehicle Fee',
                                                    '$vehicle_fee', '0', '$vehicle_fee', '$vehicle_fee', '$route')"
                                                );

                                                if (!$vfInsert) {
                                                    throw new Exception('Vehicle fee insert failed');
                                                }
                                            }

                                            /* ---------- Hostel Fee ---------- */
                                            if ($student_type === "Hosteller") {

                                                $hfQ = mysqli_query(
                                                    $link,
                                                    "SELECT Fee FROM actual_fee
                                                WHERE Type='Hostel Fee' AND Class='$class'
                                                LIMIT 1"
                                                );

                                                if (!$hfQ || mysqli_num_rows($hfQ) == 0) {
                                                    throw new Exception('Hostel fee not configured');
                                                }

                                                $hostel_fee = mysqli_fetch_assoc($hfQ)['Fee'];

                                                $hfInsert = mysqli_query(
                                                    $link,
                                                    "INSERT INTO stu_fee_master_data VALUES (
                                                    '', '$id', '$firstname', '$class', '$section',
                                                    '$area', 'Hostel Fee',
                                                    '$hostel_fee', '0', '$hostel_fee', '$hostel_fee', NULL)"
                                                );

                                                if (!$hfInsert) {
                                                    throw new Exception('Hostel fee insert failed');
                                                }
                                            }

                                            mysqli_commit($link);

                                            $advance_query = !empty($_POST['app_id']) ? mysqli_query($link, "
                                            SELECT Advance_Amount, DOP, Payment_Type, Transaction_Id
                                            FROM central.applications
                                            WHERE App_No = '$app_id'
                                            LIMIT 1
                                        ") : false;

                                            $advance_data = $advance_query ? mysqli_fetch_assoc($advance_query) : null;
                                            $advance_amount = $advance_data['Advance_Amount'] ?? 0;

                                            if ($advance_data && $advance_amount > 0) {
                                                $transaction_row = ($advance_data['Payment_Type'] == 'UPI')
                                                    ? "<p><strong>Transaction Id :</strong> " . htmlspecialchars($advance_data['Transaction_Id']) . "</p>"
                                                    : "";

                                                echo "
                                                <div id='advance_payment_modal' class='referral-modal' style='display:flex;'>
                                                    <div class='referral-modal-content'>
                                                        <h3>Advance Payment Found</h3>
                                                        <p>This student paid advance amount during application creation.</p>
                                                        <p><strong>Advance Amount :</strong> &#8377;" . htmlspecialchars($advance_data['Advance_Amount']) . "</p>
                                                        <p><strong>Date Of Payment :</strong> " . htmlspecialchars($advance_data['DOP']) . "</p>
                                                        <p><strong>Payment Type :</strong> " . htmlspecialchars($advance_data['Payment_Type']) . "</p>
                                                        $transaction_row
                                                        <p>Do you want to enter the fee payment now?</p>

                                                        <form action='../Fee/stu_fee_pay.php' method='POST' id='advance_fee_form'>
                                                            <input type='hidden' name='Id_No' value='" . htmlspecialchars($id, ENT_QUOTES) . "'>
                                                            <input type='hidden' name='Advance_Amount' value='" . htmlspecialchars($advance_data['Advance_Amount'], ENT_QUOTES) . "'>
                                                            <input type='hidden' name='DOP' value='" . htmlspecialchars($advance_data['DOP'], ENT_QUOTES) . "'>
                                                            <input type='hidden' name='Payment_Type' value='" . htmlspecialchars($advance_data['Payment_Type'], ENT_QUOTES) . "'>
                                                            <input type='hidden' name='Transaction_Id' value='" . htmlspecialchars($advance_data['Transaction_Id'], ENT_QUOTES) . "'>
                                                        </form>

                                                        <div class='modal-buttons'>
                                                            <button type='submit' form='advance_fee_form' class='btn btn-primary'>
                                                                Enter Now
                                                            </button>
                                                            <button type='button' class='btn btn-secondary' onclick=\"document.getElementById('advance_payment_modal').style.display='none'; location.replace('');\">
                                                                Later
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <script>alert('Student created successfully!');</script>
                                            ";
                                            } else {
                                                echo "<script>
                                                    alert('Student created successfully!');
                                                    location.replace('');
                                                </script>";
                                            }
                                        } catch (Exception $e) {

                                            mysqli_rollback($link);

                                            echo "<script>
                                                alert('Student created, but fee setup failed. Configure later.');
                                                location.replace('');
                                            </script>";
                                        }
                                    }
                                }
                            }
                        } else {
                            echo "<script>alert('Please Select Category!')</script>";
                        }
                    } else {
                        echo "<script>alert('Please Select Religion!')</script>";
                    }
                } else {
                    echo "<script>alert('Please Select Gender!')</script>";
                }
            } else {
                echo "<script>alert('Please Select Section!')</script>";
            }
        } else {
            echo "<script>alert('Please Select Class!')</script>";
        }
    }
    ?>
</body>

</html>