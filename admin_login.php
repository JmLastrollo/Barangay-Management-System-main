<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Access</title>
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="css/toast.css">
  <link rel="icon" type="image/png" href="assets/img/BMS.png">
</head>
<body>
<div class="login-container">
  <div class="left">
    <img src="assets/img/BMS.png" alt="Barangay Logo">
  </div>
  <div class="right">
    <h2><b>Admin</b> Access</h2>
    
    <form action="backend/login_process.php" method="POST">
      <input type="email" name="email" placeholder="Your Email" required>
      <input type="password" name="password" placeholder="Your Password" required>
      
      <input type="hidden" name="role" value="Barangay Staff">

      <label><input type="checkbox" name="remember"> Remember me</label>
      
      <a href="resident_login.php" class="alt-login">Resident Login</a>

      <button type="submit">Login</button>
    </form>
    
    <a href="index.php">Back to Homepage</a>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
function showToast(message, type = "error") {
    const t = document.getElementById("toast");
    t.className = "toast"; // Reset classes
    t.textContent = message;
    t.classList.add(type);
    t.classList.add("show");
    setTimeout(() => {
        t.classList.remove("show");
    }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= htmlspecialchars($_SESSION['toast']) ?>", "<?= htmlspecialchars($_SESSION['toast_type'] ?? 'error') ?>");
</script>
<?php unset($_SESSION['toast'], $_SESSION['toast_type']); endif; ?>

</body>
</html>