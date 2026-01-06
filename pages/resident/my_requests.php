<?php
session_start();
require_once '../../backend/auth_resident.php';
require_once '../../backend/db_connect.php';

$user_id = $_SESSION['user_id'];

// Get Resident ID
$stmt = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$resident_id = $stmt->fetchColumn();

// Fetch Requests
$stmt = $conn->prepare("SELECT * FROM document_issuances WHERE resident_id = ? ORDER BY requested_at DESC");
$stmt->execute([$resident_id]);
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Requests - BMS</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css">
    <link rel="stylesheet" href="../../css/toast.css">

    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .status-pending { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .status-ready { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .status-released { background: #cce5ff; color: #004085; border: 1px solid #b6d4fe; }
        .status-rejected { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        /* Action Buttons */
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            transition: all 0.2s;
        }
        .btn-action:hover { transform: translateY(-2px); }

        @media (min-width: 768px) {
            .btn-action {
                width: auto;
                height: auto;
                border-radius: 5px;
                padding: 4px 10px;
                font-size: 0.85rem;
            }
            .btn-text { display: inline; margin-left: 5px; }
        }
        .btn-text { display: none; }
    </style>
</head>
<body>

<?php include '../../includes/resident_sidebar.php'; ?>

<div id="main-content">
    <div class="header">
        <h1 class="header-title">MY <span class="green">REQUESTS</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo">
            <img src="../../assets/img/dasma logo-modified.png" alt="Logo">
        </div>
    </div>

    <div class="content pb-5">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark m-0">History</h4>
                <p class="text-muted small m-0 d-none d-md-block">Track your document requests</p>
            </div>
            
            <a href="request_document.php" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-bold">
                <i class="bi bi-plus-lg me-1"></i> New Request
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Control No.</th>
                                <th>Document</th>
                                <th class="d-none d-md-table-cell">Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($requests) > 0): ?>
                                <?php foreach($requests as $req): 
                                    $statusClass = match($req['status']) {
                                        'Pending' => 'status-pending',
                                        'Ready for Pickup' => 'status-ready',
                                        'Released' => 'status-released',
                                        'Rejected' => 'status-rejected',
                                        default => 'status-pending'
                                    };
                                ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-primary font-monospace small">
                                        <?= htmlspecialchars($req['request_control_no'] ?? '#'.$req['issuance_id']) ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($req['document_type']) ?></div>
                                        <div class="text-muted d-block d-md-none" style="font-size: 0.7rem;">
                                            <?= date('M d, Y', strtotime($req['requested_at'])) ?>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell fw-bold text-secondary small">
                                        ₱<?= number_format($req['amount'], 2) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge <?= $statusClass ?>"><?= $req['status'] ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <button class="btn btn-outline-primary btn-action" onclick='viewDetails(<?= json_encode($req) ?>)'>
                                                <i class="bi bi-eye-fill"></i> <span class="btn-text">View</span>
                                            </button>
                                            
                                            <?php if($req['status'] == 'Ready for Pickup' && $req['payment_method'] == 'Online'): ?>
                                                <a href="print_document.php?id=<?= $req['issuance_id'] ?>" target="_blank" class="btn btn-success btn-action">
                                                    <i class="bi bi-printer-fill"></i> <span class="btn-text">Print</span>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted small">No requests found.</td></tr>
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
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-uppercase">Request Details</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <ul class="list-group list-group-flush rounded border mt-2">
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Control No.</span>
                        <span class="fw-bold text-primary font-monospace small" id="v_id"></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Type</span>
                        <span class="fw-bold small" id="v_type"></span>
                    </li>
                    <li class="list-group-item">
                        <span class="text-muted small d-block mb-1">Purpose</span>
                        <div class="bg-light p-2 rounded small text-secondary" id="v_purpose"></div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span class="text-muted small">Payment</span>
                        <span class="fw-bold small"><span id="v_method"></span> - <span id="v_amount"></span></span>
                    </li>
                    <li class="list-group-item text-center" id="proof_row" style="display:none;">
                        <span class="text-muted small d-block mb-2">Proof of Payment</span>
                        <img id="v_proof" src="" class="img-fluid rounded border" style="max-height: 150px;">
                    </li>
                </ul>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
function viewDetails(data) {
    document.getElementById('v_id').innerText = data.request_control_no || '#' + data.issuance_id;
    document.getElementById('v_type').innerText = data.document_type;
    document.getElementById('v_purpose').innerText = data.purpose;
    document.getElementById('v_method').innerText = data.payment_method;
    document.getElementById('v_amount').innerText = '₱' + parseFloat(data.amount).toFixed(2);
    
    if(data.proof_of_payment) {
        document.getElementById('proof_row').style.display = 'block';
        document.getElementById('v_proof').src = '../../uploads/payments/' + data.proof_of_payment;
    } else {
        document.getElementById('proof_row').style.display = 'none';
    }
    
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}
</script>
</body>
</html>