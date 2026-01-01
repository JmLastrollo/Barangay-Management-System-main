<?php 
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../admin_login.php");
    exit();
}

require_once '../../backend/db_connect.php'; 

// 2. Fetch Archived Announcements
$sql = "SELECT * FROM announcements WHERE status = 'archived' ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Announcements</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../css/dashboard.css" />
    <link rel="stylesheet" href="../../css/toast.css">

    <style>
        /* FIX SA WHITE SPACE PAG NAG-OPEN ANG MODAL */
        body.modal-open {
            padding-right: 0 !important;
            overflow-y: auto !important;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <small><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'admin@email.com' ?></small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-file-earmark-text"></i> Records</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php">Blotter</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">ARCHIVED <span class="green">ANNOUNCEMENTS</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/Langkaan 2 Logo-modified.png">
            <img src="../../assets/img/dasma logo-modified.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search Title or Details..." class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            
            <div class="mt-2 mt-md-0">
                <a href="admin_announcement.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Original Date</th>
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="archiveTableBody">
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No archived items found.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $item): 
                        // Format date
                        $formattedDate = date('M d, Y', strtotime($item['date']));
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($item['title']) ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars(substr($item['details'], 0, 50)) ?>...</small></td>
                        <td><?= $formattedDate ?></td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-success" title="Restore" 
                                    onclick='openRestoreModal("<?= $item['announcement_id'] ?>")'>
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-danger" title="Delete Permanently"
                                    onclick="openDeleteModal('<?= $item['announcement_id'] ?>')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title text-success">Restore Announcement</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to restore this announcement? It will be visible to the public again.</p>
        </div>
        <div class="modal-footer">
            <form action="../../backend/announcement_update.php" method="POST">
                <input type="hidden" name="id" id="r_id">
                <input type="hidden" name="status" value="active"> 
                <input type="hidden" name="from_page" value="archive">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="submit">Yes, Restore</button>
            </form>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Permanently</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This action cannot be undone. Are you sure you want to permanently delete this record?</p>
            </div>
            <div class="modal-footer">
                <form action="../../backend/announcement_delete.php" method="POST">
                    <input type="hidden" name="id" id="d_id">
                    <input type="hidden" name="permanent" value="true"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- SIDEBAR TOGGLE ---
    function toggleSidebar() { 
        document.querySelector('.sidebar').classList.toggle('active'); 
    }

    // --- MODAL FUNCTIONS (ADDED openDeleteModal) ---
    function openRestoreModal(id) {
        document.getElementById('r_id').value = id;
        new bootstrap.Modal(document.getElementById('restoreModal')).show();
    }

    function openDeleteModal(id) {
        document.getElementById('d_id').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // --- DROPDOWN FUNCTION ---
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.classList.toggle('active');
        });
    });

    // --- SEARCH FUNCTION (ADDED) ---
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#archiveTableBody tr");
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });

    // --- TOAST FUNCTION ---
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast'; 
        toast.classList.add(type); 
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // --- CHECK URL SUCCESS MESSAGE ---
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');

        if (success === 'restored') {
            showToast('Announcement restored successfully!', 'success');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
</body>
</html>