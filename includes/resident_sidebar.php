<?php
$current_page = basename($_SERVER['PHP_SELF']);
$issuance_pages = ['resident_rqs_service.php', 'issuance_table.php'];
$is_issuance_active = in_array($current_page, $issuance_pages);

if (!isset($resident) && isset($_SESSION['user_id'])) {
    require_once '../../backend/db_connect.php';
    $uid = $_SESSION['user_id'];
    $stmtSide = $conn->prepare("SELECT first_name, last_name, image FROM resident_profiles WHERE user_id = :uid");
    $stmtSide->execute([':uid' => $uid]);
    $resSide = $stmtSide->fetch(PDO::FETCH_ASSOC);
    $sideName = $resSide ? $resSide['first_name'] . " " . $resSide['last_name'] : "Resident";
    $sideImg = ($resSide && !empty($resSide['image'])) 
        ? "../../uploads/residents/" . $resSide['image'] 
        : "../../assets/img/profile.jpg";
} else {
    $sideName = isset($fullName) ? $fullName : "Resident";
    $sideImg = isset($profileImg) ? $profileImg : "../../assets/img/profile.jpg";
}
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div class="admin-profile-img">
            <img src="<?= $sideImg ?>" alt="User Image">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white text-truncate" style="max-width: 140px;">
                <?= htmlspecialchars($sideName) ?>
            </h6>
            <span class="text-white-50 small">Resident</span>
        </div>
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
                            <a class="nav-link small <?= $current_page == 'resident_rqs_service.php' ? 'active' : '' ?>" href="resident_rqs_service.php">
                                Request Document
                            </a>
                        </li>
                        <li>
                            <a class="nav-link small <?= $current_page == 'issuance_table.php' ? 'active' : '' ?>" href="issuance_table.php">
                                My Requests
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= ($current_page == 'resident_health_appointment.php' || $current_page == 'resident_health_history.php') ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#healthSubmenu" 
                   role="button"
                   aria-expanded="false"
                   aria-controls="healthSubmenu">
                    <div><i class="bi bi-heart-pulse-fill me-2"></i> Health Center</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= ($current_page == 'resident_health_appointment.php' || $current_page == 'resident_health_history.php') ? 'show' : '' ?>" id="healthSubmenu" data-bs-parent="#accordionSidebar">
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