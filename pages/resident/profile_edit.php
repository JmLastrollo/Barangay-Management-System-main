<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// 1. FETCH DATA
try {
    // Get User Account Info
    $stmtUser = $conn->prepare("SELECT email, password, status FROM users WHERE user_id = :uid");
    $stmtUser->execute([':uid' => $user_id]);
    $userAcc = $stmtUser->fetch(PDO::FETCH_ASSOC);

    // Get Resident Profile
    $stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE user_id = :uid");
    $stmt->execute([':uid' => $user_id]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resident) {
        // Fallback empty data (Based on your table structure)
        $resident = [
            'first_name' => '', 'middle_name' => '', 'last_name' => '',
            'birthdate' => '', 'birthplace' => '', 'age' => '', 'civil_status' => '',
            'gender' => '', 'voter_status' => '', 'contact_no' => '', 'address' => '', 'image' => '',
            'occupation' => '' 
        ];
    }
} catch (PDOException $e) {
    die("Error fetching data.");
}

// 2. HANDLE PROFILE UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    
    // Existing Fields
    $fname      = trim($_POST['first_name']);
    $mname      = trim($_POST['middle_name']);
    $lname      = trim($_POST['last_name']);
    $bdate      = $_POST['birthdate'];
    $bplace     = trim($_POST['birthplace']);
    $phone      = trim($_POST['contact_no']);
    $address    = trim($_POST['address']);

    // --- EDITABLE FIELDS ---
    $civil_stat = $_POST['civil_status']; 
    $occupation = trim($_POST['occupation']); 
    // ----------------------------

    // Auto-calculate Age
    $age = ($bdate) ? date_diff(date_create($bdate), date_create('today'))->y : 0;

    // Image Upload Logic
    $imagePath = $resident['image'];
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['profile_img']['tmp_name'];
        $fileName = $_FILES['profile_img']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];

        if (in_array($fileExt, $allowed)) {
            $newFileName = 'RES_' . $user_id . '_' . time() . '.' . $fileExt;
            $uploadDir = '../../uploads/residents/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                $imagePath = $newFileName;
            }
        }
    }

    try {
        // UPDATE QUERY: Removed Emergency Contact fields
        $sql = "UPDATE resident_profiles SET 
                first_name = :fname, middle_name = :mname, last_name = :lname,
                birthdate = :bdate, birthplace = :bplace, age = :age,
                contact_no = :phone, address = :addr, image = :img,
                
                civil_status = :civil,
                occupation = :occ

                WHERE user_id = :uid";

        $stmtUpd = $conn->prepare($sql);
        $stmtUpd->execute([
            ':fname' => $fname, ':mname' => $mname, ':lname' => $lname,
            ':bdate' => $bdate, ':bplace' => $bplace, ':age' => $age,
            ':phone' => $phone, ':addr' => $address, 
            ':img' => $imagePath, 
            
            ':civil'   => $civil_stat,
            ':occ'     => $occupation,

            ':uid' => $user_id
        ]);

        $_SESSION['toast'] = ['msg' => 'Profile details updated successfully.', 'type' => 'success'];
        header("Location: profile_edit.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
}

// 3. HANDLE PASSWORD CHANGE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $passwordPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

    if (password_verify($current_pass, $userAcc['password'])) {
        if ($new_pass === $confirm_pass) {
            if (preg_match($passwordPattern, $new_pass)) {
                $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmtPass = $conn->prepare("UPDATE users SET password = :pass WHERE user_id = :uid");
                $stmtPass->execute([':pass' => $hashed_password, ':uid' => $user_id]);

                $_SESSION['toast'] = ['msg' => 'Password updated securely.', 'type' => 'success'];
                header("Location: profile_edit.php");
                exit();
            } else {
                $_SESSION['toast'] = ['msg' => 'Password must be at least 8 characters with uppercase, lowercase, number, and symbol.', 'type' => 'error'];
            }
        } else {
            $_SESSION['toast'] = ['msg' => 'New passwords do not match.', 'type' => 'error'];
        }
    } else {
        $_SESSION['toast'] = ['msg' => 'Incorrect current password.', 'type' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Profile - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">MY <span class="green">PROFILE</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo">
            </div>
        </div>

        <div class="content pb-5">
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                    
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm rounded-4 text-center p-4 h-100 bg-white">
                            <div class="mb-3 position-relative d-inline-block">
                                <?php 
                                    $imgSrc = (!empty($resident['image'])) 
                                            ? "../../uploads/residents/" . $resident['image'] 
                                            : "../../assets/img/profile.jpg";
                                ?>
                                <img src="<?= $imgSrc ?>" alt="Profile" class="profile-img-preview" id="previewImg">
                                
                                <div class="upload-btn-wrapper position-absolute bottom-0 end-0">
                                    <button type="button" class="btn btn-sm btn-primary rounded-circle shadow-sm" style="width:35px; height:35px;">
                                        <i class="bi bi-camera-fill"></i>
                                    </button>
                                    <input type="file" name="profile_img" accept="image/*" onchange="previewFile(this)">
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($userAcc['email']) ?></p>

                            <div class="mb-4">
                                <?php 
                                    $statusClass = ($userAcc['status'] === 'Active') ? 'success' : 'danger';
                                    $statusIcon  = ($userAcc['status'] === 'Active') ? 'check-circle-fill' : 'exclamation-circle-fill';
                                ?>
                                <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> border border-<?= $statusClass ?> px-3 py-1 rounded-pill">
                                    <i class="bi bi-<?= $statusIcon ?> me-1"></i> <?= $userAcc['status'] ?> Account
                                </span>
                            </div>

                            <hr class="my-3 text-muted opacity-25">

                            <button type="button" class="btn btn-outline-danger w-100 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#changePassModal">
                                <i class="bi bi-shield-lock-fill me-2"></i> Security Settings
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 pb-0">
                                <h5 class="fw-bold text-primary m-0"><i class="bi bi-person-vcard-fill me-2"></i>Personal Details</h5>
                            </div>
                            <div class="card-body p-4">
                                
                                <div class="section-title">Identity Information (Read-Only)</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">First Name</label>
                                        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($resident['first_name']) ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Middle Name</label>
                                        <input type="text" name="middle_name" class="form-control" value="<?= htmlspecialchars($resident['middle_name']) ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Last Name</label>
                                        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($resident['last_name']) ?>" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Birthdate</label>
                                        <input type="date" name="birthdate" class="form-control" value="<?= $resident['birthdate'] ?>" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted">Place of Birth</label>
                                        <input type="text" name="birthplace" class="form-control" value="<?= htmlspecialchars($resident['birthplace']) ?>" readonly>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Gender</label>
                                        <input type="text" class="form-control" value="<?= $resident['gender'] ?>" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Age</label>
                                        <input type="text" class="form-control" value="<?= $resident['age'] ?>" disabled>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Voter Status</label>
                                        <select class="form-select" disabled>
                                            <option value="Registered" <?= $resident['voter_status'] == 'Registered' ? 'selected' : '' ?>>Registered Voter</option>
                                            <option value="Not Registered" <?= $resident['voter_status'] == 'Not Registered' ? 'selected' : '' ?>>Not Registered</option>
                                        </select>
                                    </div>
                                </div>

                                <hr class="my-3 text-muted opacity-25">

                                <div class="section-title">Editable Information</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Civil Status</label>
                                        <select class="form-select" name="civil_status">
                                            <option value="Single" <?= $resident['civil_status'] == 'Single' ? 'selected' : '' ?>>Single</option>
                                            <option value="Married" <?= $resident['civil_status'] == 'Married' ? 'selected' : '' ?>>Married</option>
                                            <option value="Widowed" <?= $resident['civil_status'] == 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                            <option value="Separated" <?= $resident['civil_status'] == 'Separated' ? 'selected' : '' ?>>Separated</option>
                                        </select>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold text-muted">Occupation</label>
                                        <input type="text" name="occupation" class="form-control" value="<?= isset($resident['occupation']) ? htmlspecialchars($resident['occupation']) : '' ?>" placeholder="Enter Work/Job">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">Contact Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted">+63</span>
                                            <input type="text" name="contact_no" class="form-control" value="<?= htmlspecialchars(str_replace('+63', '', $resident['contact_no'])) ?>" required maxlength="10">
                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold text-muted">Complete Address</label>
                                        <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($resident['address']) ?>" required>
                                    </div>
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="submit" name="update_profile" class="btn btn-primary px-4 rounded-pill fw-bold shadow hover-scale">
                                        <i class="bi bi-check-lg me-1"></i> Save Changes
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>

        <?php include '../../includes/resident_footer.php'; ?>
    </div>

    <div class="modal fade" id="changePassModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark">Change Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted small mb-4">Protect your account with a strong password.</p>
                    
                    <form action="" method="POST" id="passForm">
                        <div class="form-floating mb-3">
                            <input type="password" name="current_password" class="form-control" id="currentPass" placeholder="Current Password" required>
                            <label for="currentPass">Current Password</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="password" name="new_password" class="form-control" id="newPass" placeholder="New Password" required onkeyup="checkPasswordStrength()">
                            <label for="newPass">New Password</label>
                        </div>

                        <div class="form-floating mb-2">
                            <input type="password" name="confirm_password" class="form-control" id="confirmPass" placeholder="Confirm New Password" required>
                            <label for="confirmPass">Confirm New Password</label>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="showPassToggle" onclick="togglePassword()">
                            <label class="form-check-label text-muted small" for="showPassToggle">
                                Show Password
                            </label>
                        </div>

                        <div class="password-requirements mb-3 bg-light p-3 rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="fw-bold text-uppercase">Security Check</small>
                                <span id="strengthText" class="badge bg-secondary">Weak</span>
                            </div>
                            <ul>
                                <li id="len" class="invalid"><i class="bi bi-circle"></i> At least 8 characters</li>
                                <li id="upper" class="invalid"><i class="bi bi-circle"></i> At least 1 uppercase letter</li>
                                <li id="lower" class="invalid"><i class="bi bi-circle"></i> At least 1 lowercase letter</li>
                                <li id="num" class="invalid"><i class="bi bi-circle"></i> At least 1 number</li>
                                <li id="special" class="invalid"><i class="bi bi-circle"></i> At least 1 symbol (!@#$...)</li>
                            </ul>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="change_password" class="btn btn-dark rounded-pill fw-bold py-2" id="savePassBtn" disabled>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Image Preview
        function previewFile(input){
            var file = input.files[0];
            if(file){
                var reader = new FileReader();
                reader.onload = function(){ document.getElementById('previewImg').src = reader.result; }
                reader.readAsDataURL(file);
            }
        }

        // Toggle Password Visibility
        function togglePassword() {
            var inputs = [document.getElementById("currentPass"), document.getElementById("newPass"), document.getElementById("confirmPass")];
            var checkBox = document.getElementById("showPassToggle");
            
            inputs.forEach(function(field) {
                if (checkBox.checked) {
                    field.type = "text";
                } else {
                    field.type = "password";
                }
            });
        }

        // Modal Cleanup: Clear inputs when modal is closed (via 'X' or clicking outside)
        var changePassModal = document.getElementById('changePassModal');
        changePassModal.addEventListener('hidden.bs.modal', function () {
            // Reset the form fields
            document.getElementById("passForm").reset();
            
            // Reset visibility toggle
            document.getElementById("showPassToggle").checked = false;
            var inputs = [document.getElementById("currentPass"), document.getElementById("newPass"), document.getElementById("confirmPass")];
            inputs.forEach(input => input.type = "password");

            // Reset Password Strength UI
            document.getElementById("strengthText").innerText = "Weak";
            document.getElementById("strengthText").className = "badge bg-secondary";
            document.getElementById("savePassBtn").setAttribute("disabled", "true");
            
            // Reset validation list UI
            const reqIds = ['len', 'upper', 'lower', 'num', 'special'];
            reqIds.forEach(id => {
                const el = document.getElementById(id);
                const icon = el.querySelector("i");
                el.classList.replace("valid", "invalid");
                icon.classList.replace("bi-check-circle-fill", "bi-circle");
            });
        });

        // PROFESSIONAL PASSWORD CHECKER
        function checkPasswordStrength() {
            const password = document.getElementById("newPass").value;
            const btn = document.getElementById("savePassBtn");
            const badge = document.getElementById("strengthText");

            const minLength = /.{8,}/;
            const hasUpper  = /[A-Z]/;
            const hasLower  = /[a-z]/;
            const hasNum    = /[0-9]/;
            const hasSpecial= /[\W_]/;

            const checks = {
                len: minLength.test(password),
                upper: hasUpper.test(password),
                lower: hasLower.test(password),
                num: hasNum.test(password),
                special: hasSpecial.test(password)
            };

            let validCount = 0;
            for (const [id, isValid] of Object.entries(checks)) {
                const el = document.getElementById(id);
                const icon = el.querySelector("i");
                if (isValid) {
                    el.classList.replace("invalid", "valid");
                    icon.classList.replace("bi-circle", "bi-check-circle-fill");
                    validCount++;
                } else {
                    el.classList.replace("valid", "invalid");
                    icon.classList.replace("bi-check-circle-fill", "bi-circle");
                }
            }

            if (validCount === 5) {
                btn.removeAttribute("disabled");
                badge.className = "badge bg-success";
                badge.innerText = "Strong";
            } else {
                btn.setAttribute("disabled", "true");
                badge.className = "badge bg-secondary";
                badge.innerText = "Weak";
            }
        }

        // Toast Logic
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            document.getElementById('toastMessage').innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            new bootstrap.Toast(toastEl).show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>