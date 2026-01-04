<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Registration</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/toast.css" />
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
        
        <form action="backend/register_resident.php" method="POST">
          
          <h5 class="section-header"><i class="bi bi-person-vcard-fill me-2"></i>Personal Information</h5>
          <div class="row">
              <div class="col-md-3 mb-3">
                  <input type="text" name="fname" placeholder="First Name" required onkeyup="checkPassword()">
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="mname" placeholder="Middle Name (optional)" onkeyup="checkPassword()">
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="lname" placeholder="Last Name" required onkeyup="checkPassword()">
              </div>
              <div class="col-md-3 mb-3">
                  <input type="text" name="sname" placeholder="Suffix (e.g. Jr.)">
              </div>
          </div>

          <div class="row">
              <div class="col-md-4 mb-3">
                  <label class="small text-muted ms-1">Birth Date</label>
                  <input type="date" id="bdate" name="bdate" required onchange="validateAge()">
                  <span id="ageMessage" class="d-block small fw-bold mt-1" style="font-size: 0.75rem;"></span>
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
                  <select name="is_pwd" class="form-select" required>
                    <option value="" disabled selected></option>
                      <option value="No" >No</option>
                      <option value="Yes">Yes</option>
                  </select>
              </div>
              <div class="col-md-8 d-flex align-items-center">
                  <small class="text-muted fst-italic">
                      <i class="bi bi-info-circle me-1"></i> Senior Citizen status is automatically determined based on your Birth Date.
                  </small>
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
                      <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" onkeyup="checkPassword();" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePass('password', 'icon-pass')">
                          <i class="bi bi-eye" id="icon-pass"></i>
                      </button>
                  </div>
              </div>
              <div class="col-md-6 mb-3">
                  <div class="input-group">
                      <input type="password" name="cpassword" id="cpassword" class="form-control" placeholder="Confirm Password" onkeyup="checkPassword();" required>
                      <button class="btn btn-outline-secondary" type="button" onclick="togglePass('cpassword', 'icon-cpass')">
                          <i class="bi bi-eye" id="icon-cpass"></i>
                      </button>
                  </div>
                  <span id="message" class="mt-1 d-block" style="font-size: 0.85rem;"></span>
              </div>
          </div>

          <p class="text-muted small mt-2 fst-italic text-center">
            <span class="text-danger">*</span> Please ensure all information is accurate. We will contact you via email for verification.
          </p>

          <button type="submit" id="submitBtn">REGISTER NOW</button>
          
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/register.js"></script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= htmlspecialchars($_SESSION['toast']['msg']) ?>", "<?= htmlspecialchars($_SESSION['toast']['type']) ?>");
</script>
<?php unset($_SESSION['toast']); endif; ?>

</body>
</html>