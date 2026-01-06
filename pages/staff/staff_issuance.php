<?php
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php"); 
    exit();
}

require_once '../../backend/db_connect.php';

// Fetch Active Requests with payments and resident info
try {
    $sql = "SELECT i.*, 
                   CONCAT(rp.first_name, ' ', COALESCE(rp.middle_name, ''), ' ', rp.last_name) as current_resident_name,
                   p.amount, 
                   p.payment_method, 
                   p.reference_no, 
                   p.payment_status 
            FROM issuance i
            LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
            LEFT JOIN payments p ON i.issuance_id = p.issuance_id
            WHERE i.status != 'Archived'
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
    <title>BMS - Issuance</title>
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
        <div id="toast" class="toast"></div>

        <div class="header">
            <h1 class="header-title">ISSUANCE <span class="green">RECORDS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo">
            </div>
        </div>

        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="search-box d-flex align-items-center">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search Resident or Doc Type" aria-label="Search Resident or Document Type">
                    <button type="button" class="btn btn-outline-secondary ms-2"><i class="bi bi-search"></i></button>
                </div>

                <div class="action-buttons">
                    <a href="staff_issuance_archive.php" class="btn btn-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history"></i> Archives
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
                                    <th>Purpose / Details</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                    <th>Payment</th> 
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="issuanceTable">
                                <?php if (empty($requests)): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No pending requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($requests as $r): 
                                        $name = htmlspecialchars(trim($r['current_resident_name']));
                                        $date = date('M d, Y h:i A', strtotime($r['request_date']));
                                        $price = isset($r['price']) ? number_format($r['price'], 2) : '0.00';
                                        $status = htmlspecialchars($r['status']);
                                        $statusColor = 'secondary';
                                        if($status === 'Pending') $statusColor = 'warning text-dark';
                                        elseif($status === 'Ready for Pickup') $statusColor = 'primary';
                                        elseif($status === 'Received') $statusColor = 'success';
                                        elseif($status === 'Rejected') $statusColor = 'danger';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= $name ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span><?= htmlspecialchars($r['document_type']) ?></span>
                                                <small class="text-muted">Price: ₱<?= $price ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <small><?= htmlspecialchars(mb_strimwidth($r['purpose'], 0, 40, '...')) ?></small>
                                                <?php if(!empty($r['business_name'])): ?>
                                                    <small class="text-primary"><i class="bi bi-shop"></i> <?= htmlspecialchars($r['business_name']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="small text-muted"><?= $date ?></td>
                                        <td>
                                            <span class="badge bg-<?= $statusColor ?>-subtle text-<?= str_replace('text-dark', 'dark', $statusColor) ?> border border-<?= str_replace('text-dark', 'warning', $statusColor) ?> rounded-pill px-3">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['amount']) && floatval($r['amount']) > 0): ?>
                                                <div class="d-flex flex-column small">
                                                    <span class="fw-bold">₱<?= number_format($r['amount'], 2) ?></span>
                                                    <span class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($r['payment_method']) ?></span>
                                                    <span class="badge mt-1 <?= ($r['payment_status'] == 'Paid') ? 'bg-success' : 'bg-warning text-dark' ?>">
                                                        <?= htmlspecialchars($r['payment_status']) ?>
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border">Free</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn-action view" onclick='viewRequest(<?= json_encode($r, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>)' title="View">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                <button class="btn-action edit" onclick="editStatus(<?= intval($r['issuance_id']) ?>, '<?= htmlspecialchars(addslashes($status)) ?>')" title="Update Status">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn-action archive" onclick="openArchiveModal(<?= intval($r['issuance_id']) ?>)" title="Archive">
                                                    <i class="bi bi-archive-fill"></i>
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

    <!-- Modals -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold" id="viewModalLabel">Request Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-0" id="viewBody"></div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="printLink" target="_blank" class="btn btn-dark d-flex align-items-center gap-2">
                        <i class="bi bi-printer-fill"></i> Print
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="statusModalLabel">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary" for="edit_status">NEW STATUS</label>
                        <select id="edit_status" class="form-select">
                            <option value="Pending">Pending</option>
                            <option value="Ready for Pickup">Ready for Pickup</option>
                            <option value="Received">Received</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveStatus()">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="text-danger mb-3">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Archive Request?</h4>
                    <p class="text-muted">This will move the request to the archive list.</p>
                    <form action="../../backend/admin_issuance_update.php" method="POST">
                        <input type="hidden" name="issuance_id" id="archive_id" required>
                        <input type="hidden" name="status" value="Archived"> 
                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4">Yes, Archive</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/staff/staff_issuance.js?v=<?= time(); ?>"></script>

</body>
</html>