<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Forgot Password</title>
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
                    <h2>Account Recovery</h2>
                    <p>Lost your password? Verify your email address to securely reset your access credentials.</p>
                </div>
                <div class="footer-text">
                    <small>System Version 1.0</small>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="form-content">
                <h2 class="text-primary fw-bold">Forgot Password?</h2>
                <p class="text-muted mb-4 small">Enter your registered email address to verify your identity.</p>

                <form action="backend/forgot_process.php" method="POST">
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control border-start-0" placeholder="juandelacruz@example.com" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Verify Email</button>
                </form>

                <div class="text-center mt-4">
                    <a href="login.php" class="small text-muted text-decoration-none hover-link">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<div id="toast" class="toast"></div>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.innerHTML = `<div class="toast-body">${message}</div>`;
        toast.className = 'toast ' + type;
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
</script>

<?php if (isset($_GET['error'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("Email address not found in our records.", "danger");
    });
</script>
<?php endif; ?>

</body>
</html>