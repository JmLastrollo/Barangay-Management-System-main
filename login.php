<?php 
session_start(); 

// --- UNIFIED SESSION CHECK ---
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role'])) {
        if (in_array($_SESSION['role'], ['Admin', 'Staff'])) {
            header("Location: pages/admin/admin_dashboard.php");
            exit();
        } elseif ($_SESSION['role'] === 'Resident') {
            header("Location: pages/resident/resident_dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Account Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/toast.css"> 
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
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
                    <h2>Welcome Back!</h2>
                    <p>Secure access to Barangay Langkaan II's digital ecosystem. Please authenticate to continue.</p>
                </div>
                
                <div class="footer-text">
                    <small>System Version 1.0</small>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-content">
                <h2>Account Login</h2>
                <p class="text-muted mb-4">Enter your credentials to access the panel.</p>

                <form action="backend/login_process.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="sec.juan@langkaan2.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Password</label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" placeholder="secJuan2026" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label small text-muted" for="remember">Remember me</label>
                        </div>
                        <a href="forgot_password.php" class="small text-primary text-decoration-none fw-bold">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
                </form>

                <div class="text-center mt-4">
                    <p class="small text-muted">New Resident? <a href="register.php" class="text-primary fw-bold text-decoration-none">Create Account</a></p>
                    <a href="index.php" class="small text-muted text-decoration-none">← Back to Home Page</a>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="toast" class="toast"></div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const icon = togglePassword.querySelector('i');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    // UPDATED Toast Function
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.innerHTML = `<div class="toast-body">${message}</div>`; // Changed to innerHTML to wrap content
        
        // Reset classes
        toast.className = 'toast'; 
        toast.classList.add(type); // 'success' or 'error' (css usually defines .error or .danger)

        // Show animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Hide after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }
</script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Correctly accessing the array structure set in register.php
        showToast(
            "<?= htmlspecialchars($_SESSION['toast']['msg']) ?>", 
            "<?= htmlspecialchars($_SESSION['toast']['type']) ?>"
        );
    });
</script>
<?php 
    // Clean up session
    unset($_SESSION['toast']); 
endif; 
?>

</body>
</html>