<?php 
session_start(); 
if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BMS - Secure Password Reset</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="css/toast.css">
  <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
  
  <style>
      /* Password Requirement List Style */
      .requirement-list {
          list-style: none;
          padding: 0;
          margin-bottom: 15px;
          font-size: 0.85rem;
      }
      .requirement-list li {
          margin-bottom: 3px;
          color: #6c757d;
          transition: color 0.3s;
      }
      .requirement-list li.valid {
          color: #198754; /* Green */
      }
      .requirement-list li.valid i {
          color: #198754;
      }
      .requirement-list li i {
          margin-right: 8px;
          font-size: 0.8rem;
          color: #dc3545; /* Red Default */
      }
  </style>
</head>
<body>

<div class="main-container">
    <div class="login-card">
        
        <div class="left-panel">
            <div class="content">
                <div class="logo-header">
                    <img src="assets/img/Langkaan 2 Logo-modified.png" alt="Logo">
                    <div class="text">
                        <h3>BRGY. LANGKAAN II</h3>
                        <p>DASMARIÑAS CITY, CAVITE</p>
                    </div>
                </div>
                <div class="welcome-text">
                    <h2>Secure Reset</h2>
                    <p>Create a strong password to protect your account. Follow the security guidelines provided.</p>
                </div>
                <div class="footer-text">
                    <small>System Version 1.0</small>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-content">
                <h2 class="text-success fw-bold">Set New Password</h2>
                <p class="text-muted mb-4 small">For: <b><?= htmlspecialchars($_SESSION['reset_email']) ?></b></p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger py-2 small">
                        <?php 
                            if($_GET['error'] == 'weak') echo "Password does not meet security requirements.";
                            elseif($_GET['error'] == 'mismatch') echo "Passwords do not match.";
                            else echo "System error occurred.";
                        ?>
                    </div>
                <?php endif; ?>

                <form action="backend/new_password_process.php" method="POST" onsubmit="return validateForm()">
                    
                    <div class="mb-2">
                        <label class="form-label small fw-bold text-uppercase text-muted">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_pass" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('new_pass', this)">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <ul class="requirement-list p-2 bg-light rounded border mb-3">
                        <li id="req-len"><i class="bi bi-x-circle-fill"></i> At least 8 characters</li>
                        <li id="req-num"><i class="bi bi-x-circle-fill"></i> At least 1 number (0-9)</li>
                        <li id="req-low"><i class="bi bi-x-circle-fill"></i> At least 1 lowercase letter (a-z)</li>
                        <li id="req-up"><i class="bi bi-x-circle-fill"></i> At least 1 uppercase letter (A-Z)</li>
                        <li id="req-sym"><i class="bi bi-x-circle-fill"></i> At least 1 special char (!@#$%^&*)</li>
                    </ul>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_pass" class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePass('confirm_pass', this)">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div id="passMismatch" class="text-danger small mt-1" style="display:none;">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Passwords do not match!
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold" id="submitBtn" disabled>Update Password</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php" class="small text-muted text-decoration-none">Cancel</a>
                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
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

    // Live Validation Logic
    const passwordInput = document.getElementById('new_pass');
    const confirmInput = document.getElementById('confirm_pass');
    const submitBtn = document.getElementById('submitBtn');
    const mismatchMsg = document.getElementById('passMismatch');

    // Requirement Elements
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
        const match = (val === confirmInput.value) && val !== '';
        if(confirmInput.value !== '') {
            if(!match) {
                mismatchMsg.style.display = 'block';
                allValid = false;
            } else {
                mismatchMsg.style.display = 'none';
            }
        }

        submitBtn.disabled = !allValid;
        return allValid;
    }

    passwordInput.addEventListener('input', checkValidity);
    confirmInput.addEventListener('input', checkValidity);

    function validateForm() {
        return checkValidity();
    }
</script>

</body>
</html>