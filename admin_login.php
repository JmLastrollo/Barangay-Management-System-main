<?php 
session_start(); 

// --- CHECK LOGIN STATUS (Auto-Redirect) ---
// Kapag naka-login na, bawal na bumalik dito, idiretso na sa dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['Admin', 'Staff', 'Barangay Staff'])) {
        header("Location: pages/admin/admin_dashboard.php");
        exit();
    } else if (isset($_SESSION['role']) && $_SESSION['role'] === 'Resident') {
        header("Location: pages/resident/resident_dashboard.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Access</title>
  
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="css/toast.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
  
  <style>
    .password-wrapper { position: relative; width: 100%; margin: 10px 0; }
    .password-wrapper input { width: 100%; padding-right: 45px !important; margin: 0 !important; height: 45px; }
    .password-wrapper i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #666; font-size: 1.2rem; z-index: 10; line-height: 1; }
    .password-wrapper i:hover { color: #000; }
  </style>
</head>
<body>

<div class="login-container">
  <div class="left">
    <img src="assets/img/Langkaan 2 Logo-modified.png" alt="Barangay Logo">
  </div>
  
  <div class="right">
    <h2><b>Admin</b> Access</h2>
    
    <form action="backend/login_process.php" method="POST">
      
      <input type="email" name="email" placeholder="Your Email" required>
      
      <div class="password-wrapper">
          <input type="password" name="password" id="password" placeholder="Your Password" required>
          <i class="bi bi-eye-slash" id="togglePassword"></i>
      </div>

      <label><input type="checkbox" name="remember"> Remember me</label>
      
      <a href="resident_login.php" class="alt-login">Resident Login</a>

      <button type="submit">Login</button>
    </form>
    
    <a href="index.php">Back to Homepage</a>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
// Toggle Password Script
const togglePassword = document.querySelector('#togglePassword');
const password = document.querySelector('#password');
togglePassword.addEventListener('click', function (e) {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('bi-eye');
    this.classList.toggle('bi-eye-slash');
});

// Toast Function
function showToast(message, type = "error") {
    const t = document.getElementById("toast");
    t.className = "toast"; 
    t.textContent = message;
    t.classList.add(type);
    t.classList.add("show");
    setTimeout(() => { t.classList.remove("show"); }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= htmlspecialchars($_SESSION['toast']) ?>", "<?= htmlspecialchars($_SESSION['toast_type'] ?? 'error') ?>");
</script>
<?php 
    // Burahin na ang message para hindi paulit-ulit lumabas pag refresh
    unset($_SESSION['toast']); 
    unset($_SESSION['toast_type']); 
endif; 
?>

</body>
</html>