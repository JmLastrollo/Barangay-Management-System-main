<?php
// Kunin ang current page name
$current_page = basename($_SERVER['PHP_SELF']);

// --- FIXED PAGE GROUPS ---
$issuance_pages = ['request_document.php', 'my_requests.php', 'resident_issuance.php']; 
$health_pages   = ['resident_health_appointment.php', 'resident_health_history.php'];
$blotter_pages  = ['resident_file_complaint.php', 'resident_blotter_history.php'];

// Check Active Status
$is_issuance_active = in_array($current_page, $issuance_pages);
$is_health_active   = in_array($current_page, $health_pages);
$is_blotter_active  = in_array($current_page, $blotter_pages);

// GET RESIDENT INFO (Profile & Name)
if (isset($_SESSION['user_id'])) {
    // Check if $resident is already set from parent page to avoid double query
    if (!isset($resident)) {
        // Fallback connection if not included
        if (file_exists('../../backend/db_connect.php')) {
            require_once '../../backend/db_connect.php';
        } elseif (file_exists('../backend/db_connect.php')) {
            require_once '../backend/db_connect.php';
        }
        
        if (isset($conn)) {
            $uid = $_SESSION['user_id'];
            $stmtSide = $conn->prepare("SELECT * FROM resident_profiles WHERE user_id = :uid");
            $stmtSide->execute([':uid' => $uid]);
            $resSide = $stmtSide->fetch(PDO::FETCH_ASSOC);
        } else {
            $resSide = null;
        }
    } else {
        $resSide = $resident;
    }

    if ($resSide) {
        // Check First Name (handle different column naming conventions)
        $fname = $resSide['firstname'] ?? $resSide['first_name'] ?? $resSide['fname'] ?? 'Resident';
        $lname = $resSide['lastname'] ?? $resSide['last_name'] ?? $resSide['lname'] ?? '';
        $sideName = trim($fname . " " . $lname);
        
        // Image logic
        $img_name = $resSide['image'] ?? '';
        $sideImg = (!empty($img_name)) ? "../../uploads/residents/" . $img_name : "../../assets/img/profile.jpg";
    } else {
        $sideName = "Resident";
        $sideImg = "../../assets/img/profile.jpg";
    }
} else {
    $sideName = "Guest Resident";
    $sideImg = "../../assets/img/profile.jpg";
}
?>

<style>
    /* Responsive Header Adjustment */
    @media (max-width: 992px) {
        .header {
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
            <img src="<?= htmlspecialchars($sideImg) ?>" alt="User Image" style="object-fit: cover;">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($sideName) ?>">
                <?= htmlspecialchars($sideName) ?>
            </h6>
            <span class="text-white-50 small">Resident Account</span>
        </div>
        
        <button class="btn btn-sm text-white d-lg-none position-absolute top-50 end-0 translate-middle-y me-2" onclick="toggleSidebar()">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <div class="sidebar-sticky">
        <ul class="nav flex-column" id="accordionSidebar">
            
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'resident_dashboard.php' ? 'active' : '' ?>" href="resident_dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= ($current_page == 'resident_announcements.php' || $current_page == 'resident_view_announcement.php') ? 'active' : '' ?>" href="resident_announcements.php">
                    <i class="bi bi-megaphone-fill me-2"></i> Announcements
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_issuance_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#serviceSubmenu" 
                   role="button"
                   aria-expanded="<?= $is_issuance_active ? 'true' : 'false' ?>"
                   aria-controls="serviceSubmenu">
                    <div><i class="bi bi-file-earmark-text-fill me-2"></i> Services</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_issuance_active ? 'show' : '' ?>" id="serviceSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li>
                            <a class="nav-link small <?= $current_page == 'request_document.php' ? 'active' : '' ?>" href="request_document.php">
                                Request Document
                            </a>
                        </li>
                        <li>
                            <a class="nav-link small <?= $current_page == 'my_requests.php' ? 'active' : '' ?>" href="my_requests.php">
                                My Requests
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
                        <li>
                            <a class="nav-link small <?= $current_page == 'resident_health_appointment.php' ? 'active' : '' ?>" href="resident_health_appointment.php">
                                Book Appointment
                            </a>
                        </li>
                        <li>
                            <a class="nav-link small <?= $current_page == 'resident_health_history.php' ? 'active' : '' ?>" href="resident_health_history.php">
                                My Appointments
                            </a>
                        </li>
                    </ul>
                </div>
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
                            <a class="nav-link small <?= $current_page == 'resident_file_complaint.php' ? 'active' : '' ?>" href="resident_file_complaint.php">
                                File a Complaint
                            </a>
                        </li>
                        <li>
                            <a class="nav-link small <?= $current_page == 'resident_blotter_history.php' ? 'active' : '' ?>" href="resident_blotter_history.php">
                                My Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'profile_edit.php' ? 'active' : '' ?>" href="profile_edit.php">
                    <i class="bi bi-person-circle me-2"></i> My Profile
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
                Are you sure you want to log out from your account?
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
        
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
</script>