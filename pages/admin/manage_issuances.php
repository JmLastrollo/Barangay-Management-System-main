<?php
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../login.php"); exit();
}
require_once '../../backend/db_connect.php';

// Fetch Requests
$sql = "SELECT i.*, r.first_name, r.last_name, r.contact_no, 
        u.first_name as staff_fname, u.last_name as staff_lname
        FROM document_issuances i 
        JOIN resident_profiles r ON i.resident_id = r.resident_id 
        LEFT JOIN users u ON i.processed_by = u.user_id
        ORDER BY i.requested_at DESC";
$stmt = $conn->query($sql);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Issuance Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <style>
        /* Status Badges */
        .badge-status { padding: 6px 12px; border-radius: 30px; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; }
        .bg-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .bg-verified { background-color: #cff4fc; color: #055160; border: 1px solid #b6effb; }
        .bg-ready { background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; } 
        .bg-released { background-color: #cfe2ff; color: #084298; border: 1px solid #b6d4fe; } 
        .bg-rejected { background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        
        /* Action Buttons */
        .action-btn { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; border: none; transition: all 0.2s; margin: 0 2px; }
        .btn-view { background-color: #eef2ff; color: #4361ee; } .btn-view:hover { background-color: #4361ee; color: white; }
        .btn-print { background-color: #ecfdf5; color: #10b981; } .btn-print:hover { background-color: #10b981; color: white; }
        .btn-del { background-color: #fff1f2; color: #e11d48; } .btn-del:hover { background-color: #e11d48; color: white; }
    </style>
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">ISSUANCE <span class="green">MASTERLIST</span></h1>
        </div>

        <div class="content container-fluid pb-5">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="search-box position-relative" style="width: 300px;">
                    <i class="bi bi-search position-absolute text-muted" style="top: 10px; left: 15px;"></i>
                    <input type="text" id="searchInput" class="form-control rounded-pill ps-5" placeholder="Search resident...">
                </div>
                <button class="btn btn-primary rounded-pill shadow-sm px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#addWalkinModal">
                    <i class="bi bi-plus-lg me-2"></i>Add Walk-in
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="issuanceTable">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-4">Control No.</th>
                                    <th>Resident</th>
                                    <th>Document</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Processed By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($requests)): ?>
                                    <tr><td colspan="7" class="text-center py-5 text-muted">No records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($requests as $row): 
                                        $badgeClass = match($row['status']) {
                                            'Pending' => 'bg-pending',
                                            'Payment Verified' => 'bg-verified',
                                            'Ready for Pickup' => 'bg-ready',
                                            'Released' => 'bg-released',
                                            'Rejected', 'Expired' => 'bg-rejected',
                                            default => 'bg-light text-dark'
                                        };
                                        $controlNo = $row['request_control_no'] ?? 'REQ-'.str_pad($row['issuance_id'], 4, '0', STR_PAD_LEFT);
                                        $processor = $row['staff_fname'] ? $row['staff_fname'].' '.$row['staff_lname'] : '--';
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary font-monospace small"><?= htmlspecialchars($controlNo) ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($row['contact_no']) ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($row['document_type']) ?></div>
                                            <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                                                <?= htmlspecialchars($row['purpose']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-secondary">₱<?= number_format($row['amount'], 2) ?></div>
                                            <small class="badge bg-light text-dark border"><?= $row['payment_method'] ?></small>
                                        </td>
                                        <td><span class="badge-status <?= $badgeClass ?>"><?= $row['status'] ?></span></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($processor) ?></small></td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="action-btn btn-view" onclick='openProcessModal(<?= json_encode($row) ?>)' title="Update">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                
                                                <?php if(!in_array($row['status'], ['Pending', 'Rejected'])): ?>
                                                <a href="admin_print.php?id=<?= $row['issuance_id'] ?>" target="_blank" class="action-btn btn-print" title="Print">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                                <?php endif; ?>

                                                <button class="action-btn btn-del" onclick="deleteIssuance(<?= $row['issuance_id'] ?>)" title="Delete">
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
        </div>
    </div>

    <div class="modal fade" id="addWalkinModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Walk-in Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="walkinForm">
                        <input type="hidden" name="action" value="add_walkin">
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small">FIRST NAME</label>
                                <input type="text" name="first_name" class="form-control" placeholder="Juan" required autocomplete="off">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small">LAST NAME</label>
                                <input type="text" name="last_name" class="form-control" placeholder="Dela Cruz" required autocomplete="off">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">DOCUMENT TYPE</label>
                            <select name="document_type" id="w_doc_type" class="form-select" required onchange="updateWalkinPrice()">
                                <option value="" disabled selected>Select Document</option>
                                <option value="Barangay Clearance" data-price="50.00">Barangay Clearance</option>
                                <option value="Certificate of Residency" data-price="50.00">Certificate of Residency</option>
                                <option value="Certificate of Indigency" data-price="0.00">Certificate of Indigency</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">AMOUNT TO PAY</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">₱</span>
                                <input type="text" id="w_amount_display" class="form-control fw-bold text-success" value="0.00" readonly>
                                <input type="hidden" name="amount" id="w_amount" value="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">PURPOSE</label>
                            <textarea name="purpose" class="form-control" rows="2" placeholder="Reason for request..." required></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary fw-bold">Submit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="processModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold">Update Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="updateForm">
                        <input type="hidden" id="p_issuance_id" name="issuance_id">
                        <input type="hidden" name="action" value="update_status"> 

                        <div class="bg-light p-3 rounded border mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted fw-bold">RESIDENT:</small>
                                <span class="fw-bold text-dark small" id="p_resident"></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted fw-bold">PAYMENT:</small>
                                <span class="fw-bold text-success small" id="p_payment"></span>
                            </div>
                        </div>

                        <div class="mb-3 text-center" id="p_proof_container" style="display:none;">
                            <label class="form-label small fw-bold text-muted">PROOF OF PAYMENT</label>
                            <img id="p_proof_img" src="" class="img-fluid rounded border shadow-sm" style="max-height: 200px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">STATUS ACTION</label>
                            <select class="form-select" id="p_status" name="status" required>
                                <option value="Pending">Pending</option>
                                <option value="Payment Verified">Payment Verified</option>
                                <option value="Ready for Pickup">Ready for Pickup</option>
                                <option value="Released">Released</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                        <div class="mb-3" id="remarks_div" style="display:none;">
                            <label class="form-label fw-bold small text-danger">REASON FOR REJECTION</label>
                            <textarea class="form-control" name="remarks" rows="2"></textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark fw-bold">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Search
        document.getElementById('searchInput').addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll('#issuanceTable tbody tr').forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });

        // Price Update
        function updateWalkinPrice() {
            const select = document.getElementById('w_doc_type');
            const price = select.options[select.selectedIndex].getAttribute('data-price') || 0;
            document.getElementById('w_amount_display').value = parseFloat(price).toFixed(2);
            document.getElementById('w_amount').value = price;
        }

        // Add Walkin
        document.getElementById('walkinForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAction(new FormData(this));
        });

        // Open Modal
        function openProcessModal(data) {
            document.getElementById('p_issuance_id').value = data.issuance_id;
            document.getElementById('p_resident').innerText = data.first_name + ' ' + data.last_name;
            document.getElementById('p_payment').innerText = '₱' + parseFloat(data.amount).toFixed(2) + ' (' + data.payment_method + ')';
            document.getElementById('p_status').value = data.status;

            const proofDiv = document.getElementById('p_proof_container');
            if (data.payment_method === 'Online' && data.proof_of_payment) {
                proofDiv.style.display = 'block';
                document.getElementById('p_proof_img').src = '../../uploads/payments/' + data.proof_of_payment;
            } else {
                proofDiv.style.display = 'none';
            }
            toggleRemarks();
            new bootstrap.Modal(document.getElementById('processModal')).show();
        }

        function toggleRemarks() {
            const status = document.getElementById('p_status').value;
            const div = document.getElementById('remarks_div');
            const txt = div.querySelector('textarea');
            if(status === 'Rejected') {
                div.style.display = 'block';
                txt.required = true;
            } else {
                div.style.display = 'none';
                txt.required = false;
            }
        }
        document.getElementById('p_status').addEventListener('change', toggleRemarks);

        // Update Submit
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitAction(new FormData(this));
        });

        // Delete Action
        function deleteIssuance(id) {
            if(confirm("Are you sure you want to permanently delete this record?")) {
                let fd = new FormData();
                fd.append('action', 'delete');
                fd.append('issuance_id', id);
                submitAction(fd);
            }
        }

        // Generic Submit
        function submitAction(formData) {
            fetch('../../backend/admin_issuance_action.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    </script>
</body>
</html>