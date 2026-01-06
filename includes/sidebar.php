<?php
// Kunin ang current page name
$current_page = basename($_SERVER['PHP_SELF']);

// 1. DEFINE PAGE GROUPS
$staff_pages    = ['staff_list.php', 'staff_history.php', 'staff_add.php']; 
$account_pages  = ['resident_list.php', 'resident_history.php', 'resident_archive.php'];

// Updated: Issuance Group (Kasama pa rin ang archive/print para mag-highlight ang menu pag nasa pages na yun)
$issuance_pages = ['admin_issuance.php', 'admin_issuance_archive.php', 'admin_issuance_print.php'];

$blotter_pages  = ['admin_rec_blotter.php', 'admin_rec_complaints.php', 'admin_rec_blotter_archive.php', 'admin_rec_complaints_archive.php'];
$health_pages   = ['health_dashboard.php', 'patient_records.php'];
$archive_pages  = ['archives.php', 'admin_announcement_archive.php', 'admin_officials_archive.php', 'admin_rec_blotter_archive.php', 'admin_rec_complaints_archive.php'];

// 2. CHECK ACTIVE STATUS
$is_staff_active    = in_array($current_page, $staff_pages);
$is_account_active  = in_array($current_page, $account_pages);
$is_issuance_active = in_array($current_page, $issuance_pages);
$is_blotter_active  = in_array($current_page, $blotter_pages); 
$is_health_active   = in_array($current_page, $health_pages);
$is_archive_active  = in_array($current_page, $archive_pages);

// GET ADMIN INFO
if (isset($_SESSION['user_id'])) {
    if (!isset($admin_user)) {
        require_once '../../backend/db_connect.php';
        $uid = $_SESSION['user_id'];
        $stmtSide = $conn->prepare("SELECT first_name, last_name, position FROM users WHERE user_id = :uid");
        $stmtSide->execute([':uid' => $uid]);
        $resSide = $stmtSide->fetch(PDO::FETCH_ASSOC);
    } else {
        $resSide = $admin_user;
    }

    $sideName = ($resSide) ? $resSide['first_name'] . " " . $resSide['last_name'] : "Admin";
    $sidePos  = ($resSide) ? $resSide['position'] : "Administrator";
    $sideImg  = "../../assets/img/profile.jpg"; 
} else {
    $sideName = "Admin User";
    $sidePos  = "System Admin";
    $sideImg  = "../../assets/img/profile.jpg";
}
?>

<style>
    @media (max-width: 992px) {
        .header {
            /* Magdagdag ng space sa kaliwa para sa menu button sa mobile view */
            padding-left: 70px !important; 
        }
    }
</style>

<button class="btn text-white d-lg-none position-fixed top-0 start-0 mt-3 ms-3" 
        style="z-index: 1050; font-size: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.5);" 
        onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<nav id="sidebar">
    <div class="sidebar-header position-relative">
        <div class="admin-profile-img">
            <img src="<?= htmlspecialchars($sideImg) ?>" alt="Admin" style="object-fit: cover;">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white"><?= htmlspecialchars($sideName) ?></h6>
            <span class="text-white-50 small"><?= htmlspecialchars($sidePos) ?></span>
        </div>

        <button class="btn btn-sm text-white d-lg-none position-absolute top-50 end-0 translate-middle-y me-2" onclick="toggleSidebar()">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <div class="sidebar-sticky">
        <ul class="nav flex-column" id="accordionSidebar">
            
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>" href="admin_dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'admin_announcement.php' ? 'active' : '' ?>" href="admin_announcement.php">
                    <i class="bi bi-megaphone-fill me-2"></i> Announcement
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'account_approval.php' ? 'active' : '' ?>" href="account_approval.php">
                    <i class="bi bi-person-check-fill me-2"></i> Account Approval
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_staff_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#staffSubmenu" 
                   role="button"
                   aria-expanded="<?= $is_staff_active ? 'true' : 'false' ?>"
                   aria-controls="staffSubmenu">
                    <div><i class="bi bi-person-badge-fill me-2"></i> Staff Management</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_staff_active ? 'show' : '' ?>" id="staffSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'staff_list.php' ? 'active' : '' ?>" href="staff_list.php">Staff List</a></li>
                        <li><a class="nav-link small <?= $current_page == 'staff_history.php' ? 'active' : '' ?>" href="staff_history.php">History Session</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_account_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#accountSubmenu"
                   role="button"
                   aria-expanded="<?= $is_account_active ? 'true' : 'false' ?>"
                   aria-controls="accountSubmenu">
                    <div><i class="bi bi-people-fill me-2"></i> Acc. Management</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_account_active ? 'show' : '' ?>" id="accountSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'resident_list.php' ? 'active' : '' ?>" href="resident_list.php">Resident Accounts</a></li>
                        <li><a class="nav-link small <?= $current_page == 'resident_history.php' ? 'active' : '' ?>" href="resident_history.php">History Session</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'admin_officials.php' ? 'active' : '' ?>" href="admin_officials.php">
                    <i class="bi bi-people-fill me-2"></i> Officials
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $is_issuance_active ? 'active' : '' ?>" href="admin_issuance.php">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> Issuance
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_blotter_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#blotterSubmenu" 
                   role="button"
                   aria-expanded="<?= $is_blotter_active ? 'true' : 'false' ?>"
                   aria-controls="blotterSubmenu">
                    <div><i class="bi bi-shield-exclamation me-2"></i> Blotter & Complaints</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_blotter_active ? 'show' : '' ?>" id="blotterSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li>
                            <a class="nav-link small <?= $current_page == 'admin_rec_complaints.php' ? 'active' : '' ?>" href="admin_rec_complaints.php">
                                Complaints
                            </a>
                        </li>
                        <li>
                            <a class="nav-link small <?= $current_page == 'admin_rec_blotter.php' ? 'active' : '' ?>" href="admin_rec_blotter.php">
                                Blotter Records
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_health_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#healthSubmenu"
                   role="button"
                   aria-expanded="<?= $is_health_active ? 'true' : 'false' ?>"
                   aria-controls="healthSubmenu">
                    <div><i class="bi bi-heart-pulse-fill me-2"></i> Health Center</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_health_active ? 'show' : '' ?>" id="healthSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'health_dashboard.php' ? 'active' : '' ?>" href="health_dashboard.php">Dashboard</a></li>
                        <li><a class="nav-link small <?= $current_page == 'patient_records.php' ? 'active' : '' ?>" href="patient_records.php">Patient Records</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'finance_management.php' ? 'active' : '' ?>" href="finance_management.php">
                    <i class="bi bi-cash-coin me-2"></i> Finance
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'admin_backup.php' ? 'active' : '' ?>" href="admin_backup.php">
                    <i class="bi bi-database-fill-gear me-2"></i> Backup & Restore
                </a>
            </li>

            <li class="nav-item mt-4 border-top">
                <a class="nav-link text-danger fw-bold" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>

        </ul>
    </div>
</nav>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out from the system?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../../backend/logout.php" class="btn btn-danger">Yes, Logout</a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if(sidebar && overlay) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    }
</script>