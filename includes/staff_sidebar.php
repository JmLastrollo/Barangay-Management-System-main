<?php
// Kunin ang current page name
$current_page = basename($_SERVER['PHP_SELF']);

// 1. DEFINE PAGE GROUPS (Staff Version)
$account_pages  = ['staff_resident_list.php', 'staff_resident_view.php'];
$issuance_pages = ['staff_issuance.php', 'staff_issuance_approved.php', 'staff_issuance_print.php'];
$records_pages  = ['staff_rec_blotter.php', 'staff_rec_complaints.php', 'staff_rec_residents.php'];
$health_pages   = ['staff_health_dashboard.php', 'staff_patient_records.php'];

// 2. CHECK ACTIVE STATUS
$is_account_active  = in_array($current_page, $account_pages);
$is_issuance_active = in_array($current_page, $issuance_pages);
$is_records_active  = in_array($current_page, $records_pages);
$is_health_active   = in_array($current_page, $health_pages);
?>

<nav id="sidebar">
    <div class="sidebar-header">
        <div class="admin-profile-img">
            <img src="../../assets/img/profile-staff.jpg" alt="Staff" style="object-fit: cover;" onerror="this.src='../../assets/img/default-user.png'">
        </div>
        <div class="profile-info">
            <h6 class="mb-0 fw-bold text-white">Barangay Staff</h6>
            <span class="text-white-50 small">Brgy. Langkaan II</span>
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
                    <i class="bi bi-megaphone-fill me-2"></i> Announcement
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'staff_account_approval.php' ? 'active' : '' ?>" href="staff_account_approval.php">
                    <i class="bi bi-person-check-fill me-2"></i> Account Approval
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_account_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#accountSubmenu"
                   role="button"
                   aria-expanded="<?= $is_account_active ? 'true' : 'false' ?>"
                   aria-controls="accountSubmenu">
                    <div><i class="bi bi-people-fill me-2"></i> Residents</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_account_active ? 'show' : '' ?>" id="accountSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'staff_resident_list.php' ? 'active' : '' ?>" href="staff_resident_list.php">Master List</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'staff_officials.php' ? 'active' : '' ?>" href="staff_officials.php">
                    <i class="bi bi-person-badge-fill me-2"></i> Officials
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
                        <li><a class="nav-link small <?= $current_page == 'staff_issuance.php' ? 'active' : '' ?>" href="staff_issuance.php">Pending Requests</a></li>
                        <li><a class="nav-link small <?= $current_page == 'staff_issuance_approved.php' ? 'active' : '' ?>" href="staff_issuance_approved.php">Released / History</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link d-flex justify-content-between align-items-center <?= $is_records_active ? 'active' : 'collapsed' ?>" 
                   data-bs-toggle="collapse" 
                   href="#recordsSubmenu"
                   role="button"
                   aria-expanded="<?= $is_records_active ? 'true' : 'false' ?>"
                   aria-controls="recordsSubmenu">
                    <div><i class="bi bi-folder-fill me-2"></i> Records</div>
                    <i class="bi bi-chevron-down small"></i>
                </a>
                <div class="collapse <?= $is_records_active ? 'show' : '' ?>" id="recordsSubmenu" data-bs-parent="#accordionSidebar">
                    <ul class="nav flex-column ms-3 submenu">
                        <li><a class="nav-link small <?= $current_page == 'staff_rec_blotter.php' ? 'active' : '' ?>" href="staff_rec_blotter.php">Blotter</a></li>
                        <li><a class="nav-link small <?= $current_page == 'staff_rec_complaints.php' ? 'active' : '' ?>" href="staff_rec_complaints.php">Complaints</a></li>
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
                        <li><a class="nav-link small <?= $current_page == 'staff_health_dashboard.php' ? 'active' : '' ?>" href="staff_health_dashboard.php">Dashboard</a></li>
                        <li><a class="nav-link small <?= $current_page == 'staff_patient_records.php' ? 'active' : '' ?>" href="staff_patient_records.php">Patient Records</a></li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?= $current_page == 'staff_finance.php' ? 'active' : '' ?>" href="staff_finance.php">
                    <i class="bi bi-cash-coin me-2"></i> Finance (Collection)
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