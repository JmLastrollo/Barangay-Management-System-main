<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../backend/db_connect.php';

// 2. Fetch Staff Data
try {
    $stmt = $conn->prepare("
        SELECT * FROM users 
        WHERE role IN ('Admin', 'Staff') 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $staffList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $staffList = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMS - Staff Management</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">STAFF <span class="green">MANAGEMENT</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search staff name..." class="form-control" autocomplete="off">
                    <button type="button"><i class="bi bi-search"></i></button>
                </div>
                
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="bi bi-person-plus-fill me-2"></i> Add New Staff
                </button>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Staff Name</th>
                            <th>Role</th>
                            <th>Email Address</th>
                            <th>Status</th>
                            <th>Date Created</th>
                            <th class="text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="staffTable">
                        <?php if(empty($staffList)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No staff records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($staffList as $staff): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 35px; height: 35px;">
                                            <?= strtoupper(substr($staff['first_name'] ?? 'U', 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($staff['role'] == 'Admin'): ?>
                                        <span class="badge bg-danger">Administrator</span>
                                    <?php else: ?>
                                        <span class="badge bg-info text-dark">Barangay Staff</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($staff['email']) ?></td>
                                <td>
                                    <?php if(($staff['status'] ?? '') == 'Active'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?= date("M d, Y", strtotime($staff['created_at'])) ?></td>
                                <td class="text-center">
                                    <div class="action-btn-container">
                                        <button class="btn-action edit" onclick='openEditStaffModal(<?= json_encode($staff) ?>)' title="Edit Details">
                                            <i class="bi bi-pencil-square"></i>
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

    <div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/staff_add.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Role</label>
                                <select name="role" class="form-select">
                                    <option value="Staff">Barangay Staff</option>
                                    <option value="Admin">Administrator</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Create password">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Create Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Staff Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/staff_update.php" method="POST">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Staff Name</label>
                            <input type="text" id="edit_name_display" class="form-control" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" readonly style="background-color: #f8f9fa;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Role</label>
                            <select name="role" id="edit_role" class="form-select">
                                <option value="Staff">Staff</option>
                                <option value="Admin">Admin</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="toast" class="toast"></div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/admin_staff.js"></script>
</body>
</html>