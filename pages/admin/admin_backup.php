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
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css"> 
    <link rel="stylesheet" href="../../css/admin.css"> </head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <div class="d-flex align-items-center">
                <h1 class="header-title">SYSTEM <span class="green">BACKUP</span></h1>
            </div>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-5">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-lg-7 col-md-9">
                        
                        <div class="card backup-card shadow-lg">
                            <div class="card-header">
                                <h5 class="mb-0 fw-bold"><i class="bi bi-database-fill-gear me-2"></i>Database Backup & Restore</h5>
                            </div>
                            
                            <div class="card-body p-5 text-center">
                                
                                <div class="backup-icon-wrapper mb-4">
                                    <i class="bi bi-cloud-download-fill"></i>
                                </div>

                                <h3 class="fw-bold text-success mb-3">Create Full System Backup</h3>
                                <p class="text-muted mb-4 px-3">
                                    This will generate a SQL file containing all resident data, issuance records, blotter cases, financial logs, and system settings. 
                                    <br>Please save this file in a secure location.
                                </p>

                                <div class="alert alert-warning text-start shadow-sm mb-4" role="alert">
                                    <div class="d-flex">
                                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                                        <div>
                                            <strong>Important:</strong> 
                                            This process falls under <em>Archive Management</em> protocols. Regular backups are recommended to prevent data loss.
                                        </div>
                                    </div>
                                </div>

                                <form action="../../backend/backup_process.php" method="POST">
                                    <button type="submit" class="btn btn-success btn-lg px-5 py-3 shadow rounded-pill fw-bold">
                                        <i class="bi bi-download me-2"></i> Download Database (.SQL)
                                    </button>
                                </form>

                            </div>
                            <div class="card-footer text-muted text-center py-3 small">
                                Server Date: <?= date('F d, Y h:i A') ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>