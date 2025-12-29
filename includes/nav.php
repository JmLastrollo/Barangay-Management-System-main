<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <?php 
                // Simple logic para mahanap ang tamang path ng image kahit nasaan folder ka man
                $path = file_exists('assets/img/Langkaan 2 Logo-modified.png') ? 'assets/img/Langkaan 2 Logo-modified.png' : '../../assets/img/Langkaan 2 Logo-modified.png';
            ?>
            <img src="<?= $path ?>" alt="Logo" width="40" height="40" class="d-inline-block align-text-top me-2 rounded-circle">
            <span class="fw-bold d-md-none">BMS</span>
            <span class="fw-bold d-none d-md-inline">Barangay Management System</span>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="announcement.php">Announcement</a></li>
                <li class="nav-item"><a class="nav-link" href="officials.php">Officials</a></li>
                <li class="nav-item"><a class="nav-link" href="issuance.php">Issuance</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="calendar.php">Calendar</a></li>
                
                <?php if (isset($_SESSION['email'])): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle btn btn-success text-white px-3 ms-2 rounded-pill" href="#" role="button" data-bs-toggle="dropdown">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Staff')): ?>
                                <li><a class="dropdown-item" href="pages/admin/admin_dashboard.php">Dashboard</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="pages/resident/resident_dashboard.php">My Profile</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="backend/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-light rounded-pill px-4" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>