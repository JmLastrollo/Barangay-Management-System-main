<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Registration</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/toast.css" />

    <style>
        /* Password Requirement List Style */
        .requirement-list {
            list-style: none;
            padding: 0;
            margin-bottom: 5px;
            font-size: 0.75rem;
        }
        .requirement-list li {
            margin-bottom: 2px;
            color: #6c757d;
            transition: color 0.3s;
            display: inline-block;
            margin-right: 10px;
        }
        .requirement-list li.valid { color: #198754; }
        .requirement-list li.valid i { color: #198754; }
        .requirement-list li i { margin-right: 5px; color: #dc3545; }
        
        /* PWD Upload Container Transition */
        #pwd_upload_container {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body>

  <section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="left-logo" alt="Dasma Logo">
    <div class="header-text">
        <h1>BARANGAY LANGKAAN II</h1> 
        <h3>RESIDENT REGISTRATION</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="right-logo" alt="Brgy Logo">
  </section>

  <div class="container">
      <div class="registration-form">
        
        <form action="backend/register_resident.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
          
          <h5 class="section-header"><i class="bi bi-person-vcard-fill me-2"></i>Personal Information</h5>
          <div class="row">
              <div class="col-md-3 mb-3">
                  <input type="text" name="fname" placeholder="First Name" required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="mname" placeholder="Middle Name (optional)">
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="lname" placeholder="Last Name" required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="sname" placeholder="Suffix (e.g. Jr.)">
              </div>
          </div>

          <div class="row">
              <div class="col-md-4 mb-3">
                  <label class="small text-muted ms-1">Birth Date</label>
                  <input type="date" id="bdate" name="bdate" required>
              </div>
              <div class="col-md-4 mb-3">
                  <label class="small text-muted ms-1">Birth Place</label>
                  <input type="text" name="bplace" placeholder="City/Municipality" required>
              </div>
              <div class="col-md-2 mb-3">
                  <label class="small text-muted ms-1">Gender</label>
                  <select name="gender" required>
                      <option value="" disabled selected>Select</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                  </select>
              </div>
              <div class="col-md-2 mb-3">
                  <label class="small text-muted ms-1">Civil Status</label>
                  <select name="civil_status" required>
                      <option value="" disabled selected>Select</option>
                      <option value="Single">Single</option>
                      <option value="Married">Married</option>
                      <option value="Widowed">Widowed</option>
                      <option value="Separated">Separated</option>
                  </select>
              </div>
          </div>

          <h5 class="section-header mt-2"><i class="bi bi-geo-alt-fill me-2"></i>Demographics & Residence</h5>
          
          <div class="row">
              <div class="col-md-6 mb-3">
                  <input type="text" name="address" placeholder="House No., Street, Block & Lot, Subdivision" required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="city" placeholder="City / Municipality" required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="province" placeholder="Province" required>
              </div>
          </div>

          <div class="row">
              <div class="col-md-3 mb-3">
                  <select name="purok" required>
                      <option value="" disabled selected>Select Phase/Area</option>
                      <option value="Phase 1">Phase 1</option>
                      <option value="Phase 2">Phase 2</option>
                      <option value="Phase 3">Phase 3</option>
                      <option value="Phase 4">Phase 4</option>
                      <option value="Phase 5">Phase 5</option>
                      <option value="R 5">R 5</option>
                  </select>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="household_no" placeholder="Household No." required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="number" id="YearInput" name="resident_since" placeholder="Resident Since (Year)" min="1900" max="2099" required>
              </div>
              <div class="col-md-3 mb-3">
                  <input type="tel" name="contact" placeholder="09xxxxxxxxx (11 digits)" 
                         pattern="[0-9]{11}" maxlength="11" 
                         oninput="this.value = this.value.replace(/[^0-9]/g, '');" 
                         title="Please enter exactly 11 numeric digits starting with 09" required>
              </div>
          </div>

          <div class="row">
              <div class="col-md-4 mb-3">
                  <input type="text" name="occupation" placeholder="Occupation">
              </div>
              <div class="col-md-4 mb-3">
                  <select name="income" required>
                      <option value="" disabled selected>Monthly Income</option>
                      <option value="Below PHP 10,000">Below PHP 10,000</option>
                      <option value="PHP 10,000 - 20,000">PHP 10,000 - 20,000</option>
                      <option value="PHP 20,000+">PHP 20,000+</option>
                  </select>
              </div>
              <div class="col-md-2 mb-3">
                  <select name="voter" required>
                      <option value="" disabled selected>Voter Status</option>
                      <option value="Registered">Registered</option>
                      <option value="Not Registered">Not Registered</option>
                  </select>
              </div>
              <div class="col-md-2 mb-3">
                  <select name="family_head" required>
                      <option value="" disabled selected>Family Head?</option>
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                  </select>
              </div>
          </div>

          <div class="row">
              <div class="col-md-4 mb-3">
                  <label class="small text-muted ms-1">Person with Disability (PWD)?</label>
                  <select name="is_pwd" id="is_pwd" class="form-select" required onchange="togglePwdUpload()">
                      <option value="" disabled selected>Select</option>
                      <option value="No">No</option>
                      <option value="Yes">Yes</option>
                  </select>
              </div>
              
              <div class="col-md-8 mb-3" id="pwd_upload_container" style="display: none;">
                  <label class="small text-muted ms-1 fw-bold text-danger">Upload PWD ID / Certificate *</label>
                  <input type="file" name="pwd_id_file" id="pwd_id_file" class="form-control" accept="image/*,.pdf">
                  <small class="text-muted" style="font-size: 0.7rem;">Please upload a clear copy for verification.</small>
              </div>
          </div>

          <h5 class="section-header mt-2"><i class="bi bi-shield-lock-fill me-2"></i>Account Access</h5>
          
          <div class="row">
              <div class="col-md-12 mb-3">
                  <input type="email" name="email" placeholder="Email Address (Used for Login)" required>
              </div>
          </div>

          <div class="row">
              <div class="col-md-6 mb-3">
                  <div class="input-group">
                      <input type="password" name="password" id="password" class="form-control" placeholder="Create Password" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password', this)">
                          <i class="bi bi-eye-slash"></i>
                      </button>
                  </div>
              </div>
              <div class="col-md-6 mb-3">
                  <div class="input-group">
                      <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Confirm Password" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePass('cpassword', this)">
                          <i class="bi bi-eye-slash"></i>
                      </button>
                  </div>
              </div>
          </div>

          <div class="p-2 bg-light rounded border mb-3">
              <small class="fw-bold text-muted text-uppercase d-block mb-1">Password Requirements:</small>
              <ul class="requirement-list">
                  <li id="req-len"><i class="bi bi-x-circle-fill"></i> 8+ Chars</li>
                  <li id="req-num"><i class="bi bi-x-circle-fill"></i> Number</li>
                  <li id="req-low"><i class="bi bi-x-circle-fill"></i> Lowercase</li>
                  <li id="req-up"><i class="bi bi-x-circle-fill"></i> Uppercase</li>
                  <li id="req-sym"><i class="bi bi-x-circle-fill"></i> Special Char</li>
              </ul>
              <div id="passMismatch" class="text-danger small fw-bold" style="display:none;">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i> Passwords do not match!
              </div>
          </div>

          <p class="text-muted small mt-2 fst-italic text-center">
            <span class="text-danger">*</span> Please ensure all information is accurate. We will contact you via email for verification.
          </p>

          <button type="submit" id="submitBtn" disabled>REGISTER NOW</button>
          
          <div class="text-center mt-4">
              <p class="mb-2">Already have an account? <a href="login.php" class="fw-bold text-decoration-none" style="color: #1565C0;">Login here</a></p>
              <hr class="w-50 mx-auto my-3 text-muted opacity-25">
              <a href="index.php" class="text-decoration-none text-secondary small">
                  <i class="bi bi-arrow-left"></i> Back to Homepage
              </a>
          </div>

        </form>
      </div>
  </div>
  <button type="button" id="backToTop" title="Go to top">
    <i class="bi bi-arrow-up"></i>
  </button>
  <?php include('includes/footer.php'); ?>
<div id="toast" class="toast"></div>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    // 1. Password Toggle
    function togglePass(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("bi-eye-slash", "bi-eye");
        } else {
            input.type = "password";
            icon.classList.replace("bi-eye", "bi-eye-slash");
        }
    }

    // 2. PWD Upload Logic
    function togglePwdUpload() {
        const status = document.getElementById('is_pwd').value;
        const container = document.getElementById('pwd_upload_container');
        const fileInput = document.getElementById('pwd_id_file');

        if (status === 'Yes') {
            container.style.display = 'block';
            fileInput.setAttribute('required', 'required');
        } else {
            container.style.display = 'none';
            fileInput.removeAttribute('required');
            fileInput.value = ''; // Clear file if switched back
        }
    }

    // 3. Password Strength & Validation (No Age Limit)
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('cpassword');
    const submitBtn = document.getElementById('submitBtn');
    const mismatchMsg = document.getElementById('passMismatch');

    const reqs = {
        len: { regex: /.{8,}/, el: document.getElementById('req-len') },
        num: { regex: /\d/, el: document.getElementById('req-num') },
        low: { regex: /[a-z]/, el: document.getElementById('req-low') },
        up:  { regex: /[A-Z]/, el: document.getElementById('req-up') },
        sym: { regex: /[!@#$%^&*(),.?":{}|<>]/, el: document.getElementById('req-sym') }
    };

    function checkValidity() {
        const val = passwordInput.value;
        let allValid = true;

        // Check Strength
        for (const key in reqs) {
            const item = reqs[key];
            const isValid = item.regex.test(val);
            const icon = item.el.querySelector('i');

            if (isValid) {
                item.el.classList.add('valid');
                icon.classList.replace('bi-x-circle-fill', 'bi-check-circle-fill');
            } else {
                item.el.classList.remove('valid');
                icon.classList.replace('bi-check-circle-fill', 'bi-x-circle-fill');
                allValid = false;
            }
        }

        // Check Match
        if (confirmInput.value !== '') {
            if (val !== confirmInput.value) {
                mismatchMsg.style.display = 'block';
                allValid = false;
            } else {
                mismatchMsg.style.display = 'none';
            }
        }

        // Enable/Disable Button
        submitBtn.disabled = !allValid;
        
        // Visual Styling for Button
        if (allValid) {
            submitBtn.style.opacity = "1";
            submitBtn.style.cursor = "pointer";
        } else {
            submitBtn.style.opacity = "0.6";
            submitBtn.style.cursor = "not-allowed";
        }
    }

    passwordInput.addEventListener('input', checkValidity);
    confirmInput.addEventListener('input', checkValidity);

    // Initial check (para reset kung nag refresh)
    window.onload = function() {
        document.getElementById('is_pwd').value = ""; // Reset dropdown
        togglePwdUpload();
    };

</script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    const toast = document.getElementById('toast');
    toast.innerHTML = `<div class="toast-body"><?= htmlspecialchars($_SESSION['toast']['msg']) ?></div>`;
    toast.className = 'toast <?= htmlspecialchars($_SESSION['toast']['type']) ?>';
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3000);
</script>
<?php unset($_SESSION['toast']); endif; ?>

</body>
</html>