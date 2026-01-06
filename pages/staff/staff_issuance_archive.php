<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php"); 
    exit();
}

require_once '../../backend/db_connect.php';

// 2. Fetch Archived Requests (MySQL)
try {
    $sql = "SELECT i.*, 
                   CONCAT(rp.first_name, ' ', rp.last_name) as current_resident_name
            FROM issuance i
            LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
            WHERE i.status = 'Archived'
            ORDER BY i.request_date DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $requests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Issuance</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../css/sidebar.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../css/toast.css?v=<?= time(); ?>"> 
</head>

<body>

    <?php include '../../includes/staff_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">ARCHIVED <span class="green">ISSUANCE</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                
                <div class="search-box">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search Archived Requests...">
                    <button type="button"><i class="bi bi-search"></i></button>
                </div>

                <div class="action-buttons">
                    <a href="staff_issuance.php" class="btn btn-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Back to Active
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Resident Name</th>
                                    <th>Document Type</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="issuanceTable">
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No archived requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($requests as $r): 
                                        $name = $r['current_resident_name'] ?? $r['resident_name'] ?? 'Unknown';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($name) ?></td>
                                        <td><?= htmlspecialchars($r['document_type']) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($r['request_date'])) ?></td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-3">
                                                Archived
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn-action view" onclick='viewArchived(<?= json_encode($r) ?>)' title="View">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>

                                                <button class="btn-action edit" onclick="openRestoreModal(<?= $r['issuance_id'] ?>)" title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>

                                                <button class="btn-action archive" onclick="openDeleteModal(<?= $r['issuance_id'] ?>)" title="Delete Permanently">
                                                    <i class="bi bi-trash-fill"></i>
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
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Archived Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0" id="viewBody"></div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="text-success mb-3">
                        <i class="bi bi-arrow-counterclockwise" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Restore Request?</h4>
                    <p class="text-muted">This will move the request back to the active list.</p>
                    <form action="../../backend/admin_issuance_update.php" method="POST">
                        <input type="hidden" name="issuance_id" id="r_id">
                        <input type="hidden" name="status" value="Pending">
                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">Yes, Restore</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-trash-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Delete Permanently?</h4>
                    <p class="text-muted">This action cannot be undone.</p>
                    <form action="../../backend/admin_issuance_delete.php" method="POST">
                        <input type="hidden" name="issuance_id" id="d_id">
                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4">Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/staff/staff_issuance_archive.js?v=<?= time(); ?>"></script>

</body>
</html>