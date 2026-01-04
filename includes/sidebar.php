<?php
// Kunin ang current page name
$current_page = basename($_SERVER['PHP_SELF']);

// I-define kung anong mga pages ang kabilang sa bawat Group
$staff_pages    = ['staff_list.php', 'staff_history.php', 'staff_add.php']; 
$account_pages  = ['resident_list.php', 'resident_history.php'];
$issuance_pages = ['admin_issuance.php', 'admin_issuance_approved.php'];
$health_pages   = ['health_dashboard.php', 'patient_records.php'];

// Check kung active ang group
$is_staff_active    = in_array($current_page, $staff_pages);
$is_account_active  = in_array($current_page, $account_pages);
$is_issuance_active = in_array($current_page, $issuance_pages);
$is_health_active   = in_array($current_page, $health_pages);
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div class="admin-profile-img">
            <img src="../../assets/img/profile.jpg" alt="Admin">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white">Administrator</h6>
            <span class="text-white-50 small">IT Department</span>
        </div>
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
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_issuance_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#issuanceSubmenu"
                   role="button"
                   aria-expanded="<?= $is_issuance_active ? 'true' : 'false' ?>"
                   aria-controls="issuanceSubmenu">
                    <div><i class="bi bi-file-earmark-text-fill me-2"></i> Issuance</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_issuance_active ? 'show' : '' ?>" id="issuanceSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'admin_issuance.php' ? 'active' : '' ?>" href="admin_issuance.php">Docs. Requests</a></li>
                        <li><a class="nav-link small <?= $current_page == 'admin_issuance_approved.php' ? 'active' : '' ?>" href="admin_issuance_approved.php">Approved / History</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'records_management.php' ? 'active' : '' ?>" href="records_management.php">
                    <i class="bi bi-folder-fill me-2"></i> Records
                </a>
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
                <a class="nav-link <?= $current_page == 'archives.php' ? 'active' : '' ?>" href="archives.php">
                    <i class="bi bi-archive-fill me-2"></i> Archives
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
                Are you sure you want to log out from the system?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="../../backend/logout.php" class="btn btn-danger">Yes, Logout</a>
            </div>
        </div>
    </div>
</div>