<?php
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - System Backup</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <div class="dept">IT Department</div>
        </div>
    </div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_staff.php"><i class="bi bi-person-badge"></i> Staff Management</a>
        <a href="admin_accounts.php"><i class="bi bi-people"></i> Account Management</a>

        <div class="dropdown-container">
            <button class="dropdown-btn"><span><i class="bi bi-folder2-open"></i> Modules</span> <i class="bi bi-chevron-down"></i></button>
            <div class="dropdown-content">
                <a href="admin_profiling.php">Resident Profiling</a>
                <a href="admin_issuance.php">Certificates</a>
                <a href="admin_blotter.php">Peace & Order</a>
                <a href="admin_health.php">Health Center</a>
                <a href="admin_finance.php">Finance</a>
            </div>
        </div>

        <div class="dropdown-container active">
            <button class="dropdown-btn"><span><i class="bi bi-archive"></i> Archive Mgmt</span> <i class="bi bi-chevron-down dropdown-arrow"></i></button>
            <div class="dropdown-content" style="display:flex;">
                <a href="admin_archives.php">Archived Records</a>
                <a href="admin_backup.php" class="active">System Backup</a> </div>
        </div>
        
        <a href="admin_logs.php"><i class="bi bi-clock-history"></i> History Logs</a>
        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">SYSTEM <span class="green">BACKUP</span></h1>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    
                    <div class="card shadow-lg border-0 mt-4">
                        <div class="card-header bg-dark text-white p-3">
                            <h5 class="mb-0"><i class="bi bi-database-fill-gear"></i> Database Backup & Recovery</h5>
                        </div>
                        <div class="card-body p-5 text-center">
                            <img src="../../assets/img/backup_icon.png" alt="Backup" style="width: 120px; opacity: 0.8;" class="mb-4">
                            <h3 class="fw-bold text-success">Create a Full System Backup</h3>
                            <p class="text-muted mb-4">
                                This will generate a SQL file containing all resident data, issuance records, blotter cases, financial logs, and system settings. 
                                <br>Please save this file in a secure location.
                            </p>

                            <div class="alert alert-warning text-start" role="alert">
                                <i class="bi bi-exclamation-triangle-fill"></i> <strong>Important:</strong> 
                                This process falls under <em>Archive Management (2.12)</em> as part of data preservation and disaster recovery protocols defined in ISO 25010 Reliability Standards.
                            </div>

                            <form action="../../backend/backup_process.php" method="POST">
                                <button type="submit" class="btn btn-success btn-lg px-5 py-3 shadow">
                                    <i class="bi bi-download me-2"></i> Download Database (.SQL)
                                </button>
                            </form>
                        </div>
                        <div class="card-footer text-muted text-center py-3">
                            Current Date: <?= date('F d, Y h:i A') ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() { 
        document.querySelector('.sidebar').classList.toggle('active'); 
    }
    // Dropdown Logic (Existing)
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.classList.toggle('active');
        });
    });
</script>
</body>
</html>