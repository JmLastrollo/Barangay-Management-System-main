<?php
$current_page = basename($_SERVER['PHP_SELF']);

// GROUP PAGES FOR ACTIVE STATE
$issuance_group = ['staff_issuance.php', 'staff_print.php'];
$health_group   = ['staff_health.php'];
$finance_group  = ['staff_finance.php'];
$resident_group = ['staff_resident_list.php', 'view_resident.php'];

// CHECK ACTIVE
$is_issuance = in_array($current_page, $issuance_group);
$is_health   = in_array($current_page, $health_group);
$is_finance  = in_array($current_page, $finance_group);
$is_resident = in_array($current_page, $resident_group);
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div class="admin-profile-img">
            <img src="../../assets/img/profile.jpg" alt="Staff" style="object-fit: cover;">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white">Barangay Staff</h6>
            <span class="text-white-50 small">Staff Portal</span>
        </div>
    </div>

    <div class="sidebar-sticky">
        <ul class="nav flex-column" id="accordionSidebar">
            
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'staff_dashboard.php' ? 'active' : '' ?>" href="staff_dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'staff_announcement.php' ? 'active' : '' ?>" href="staff_announcement.php">
                    <i class="bi bi-megaphone-fill me-2"></i> Announcements
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $is_issuance ? 'active' : '' ?>" href="staff_issuance.php">
                    <i class="bi bi-file-earmark-text-fill me-2"></i> Issuance
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $is_resident ? 'active' : '' ?>" href="staff_resident_list.php">
                    <i class="bi bi-people-fill me-2"></i> Residents
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $is_health ? 'active' : '' ?>" href="staff_health.php">
                    <i class="bi bi-heart-pulse-fill me-2"></i> Health Center
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $is_finance ? 'active' : '' ?>" href="staff_finance.php">
                    <i class="bi bi-cash-coin me-2"></i> Finance
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

<div class="modal fade" id="logoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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