<?php
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Residents</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">

    <style>
        .resident-img-sm { width: 40px; height: 40px; object-fit: cover; border-radius: 50%; filter: grayscale(100%); }
        .text-archive { color: #6c757d; }
    </style>
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">ARCHIVED <span class="green">RESIDENTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search Archived..." class="form-control">
                    <button><i class="bi bi-search"></i></button>
                </div>
                
                <div class="mt-2 mt-md-0">
                <a href="resident_list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-secondary">
                        <tr>
                            <th class="ps-4">Resident Name</th>
                            <th>Phase / Address</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="archiveTableBody">
                        <tr><td colspan="4" class="text-center py-4 text-muted">Loading archives...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success-subtle">
                    <h5 class="modal-title fw-bold text-success">
                        <i class="bi bi-arrow-counterclockwise me-2"></i>Restore Account
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3"><i class="bi bi-person-check-fill text-success" style="font-size: 3rem;"></i></div>
                    <h5 class="fw-bold">Restore this resident?</h5>
                    <p class="text-muted">
                        This will move <strong id="restore_name" class="text-dark"></strong> <br>
                        back to the active resident list.
                    </p>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <form action="../../backend/resident_update_status.php" method="POST">
                        <input type="hidden" name="resident_id" id="restore_id">
                        <input type="hidden" name="status" value="Active"> 
                        <input type="hidden" name="redirect" value="archive"> 
                        
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">Confirm Restore</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/admin_resident_archive.js"></script>

</body>
</html>