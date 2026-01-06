<?php
session_start();
require_once '../../backend/auth_staff.php';
require_once '../../backend/db_connect.php';

// Get Request ID
if (!isset($_GET['id'])) {
    header("Location: all_requests.php");
    exit();
}

$issuance_id = $_GET['id'];

// Fetch Request Details
$stmt = $conn->prepare("
    SELECT di.*, 
           CONCAT(rp.first_name, ' ', rp.last_name) as resident_name,
           rp.contact_no,
           rp.address,
           rp.email,
           rp.birthdate
    FROM document_issuances di
    JOIN resident_profiles rp ON di.resident_id = rp.resident_id
    WHERE di.issuance_id = ?
");
$stmt->execute([$issuance_id]);
$request = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    $_SESSION['toast'] = ['msg' => 'Request not found.', 'type' => 'error'];
    header("Location: all_requests.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Request #<?= $request['issuance_id'] ?> - Staff BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/toast.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
</head>
<body>

<?php include '../../includes/staff_sidebar.php'; ?>

<div class="main-content">
    <div class="header">
            <div class="d-flex align-items-center">
                <h1 class="header-title">VIEW <span class="green">REQUEST</span></h1>
            </div>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>
    
    <div class="container-fluid p-4">
        
        <!-- Toast Notification -->
        <?php if (isset($_SESSION['toast'])): ?>
            <div id="toast" class="toast <?= $_SESSION['toast']['type'] ?> show">
                <?= htmlspecialchars($_SESSION['toast']['msg']) ?>
            </div>
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                
                <!-- Resident Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Resident Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Full Name</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($request['resident_name']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Contact Number</label>
                                <p class="fw-bold mb-0"><?= $request['contact_no'] ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Email Address</label>
                                <p class="fw-bold mb-0"><?= $request['email'] ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Date of Birth</label>
                                <p class="fw-bold mb-0"><?= date('F d, Y', strtotime($request['birthdate'])) ?></p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-muted small">Address</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($request['address']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Document Request Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Document Request Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Document Type</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($request['document_type']) ?></p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Request Date</label>
                                <p class="fw-bold mb-0"><?= date('F d, Y h:i A', strtotime($request['requested_at'])) ?></p>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="text-muted small">Purpose</label>
                                <p class="fw-bold mb-0"><?= htmlspecialchars($request['purpose']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Payment Method</label>
                                <p class="fw-bold mb-0">
                                    <span class="badge bg-secondary"><?= $request['payment_method'] ?></span>
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Amount</label>
                                <p class="fw-bold mb-0 text-success">₱<?= number_format($request['amount'], 2) ?></p>
                            </div>
                            
                            <?php if ($request['payment_method'] == 'Online' && $request['proof_of_payment']): ?>
                            <div class="col-12">
                                <label class="text-muted small">Proof of Payment</label><br>
                                <a href="../../uploads/payment_proofs/<?= $request['proof_of_payment'] ?>" target="_blank">
                                    <img src="../../uploads/payment_proofs/<?= $request['proof_of_payment'] ?>" 
                                         alt="Proof" 
                                         style="max-width: 100%; max-height: 400px; border: 2px solid #ddd; border-radius: 8px;">
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Right Column: Actions -->
            <div class="col-lg-4">
                
                <!-- Current Status -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Current Status</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $statusClass = 'warning';
                        $statusIcon = 'clock-history';
                        switch($request['status']) {
                            case 'Payment Verified': 
                                $statusClass = 'info'; 
                                $statusIcon = 'check-circle';
                                break;
                            case 'Ready for Pickup': 
                                $statusClass = 'success'; 
                                $statusIcon = 'file-earmark-check';
                                break;
                            case 'Released': 
                                $statusClass = 'primary'; 
                                $statusIcon = 'check2-all';
                                break;
                            case 'Expired': 
                                $statusClass = 'secondary'; 
                                $statusIcon = 'x-circle';
                                break;
                            case 'Rejected': 
                                $statusClass = 'danger'; 
                                $statusIcon = 'x-octagon';
                                break;
                        }
                        ?>
                        <i class="bi bi-<?= $statusIcon ?> display-1 text-<?= $statusClass ?>"></i>
                        <h4 class="mt-3 fw-bold text-<?= $statusClass ?>"><?= $request['status'] ?></h4>
                        
                        <?php if ($request['expires_at']): ?>
                            <p class="text-muted small mb-0">Expires: <?= date('M d, Y h:i A', strtotime($request['expires_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h5>
                    </div>
                    <div class="card-body">
                        
                        <?php if ($request['status'] == 'Pending' && $request['payment_method'] == 'Online'): ?>
                            <!-- Verify Online Payment -->
                            <button class="btn btn-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#verifyPaymentModal">
                                <i class="bi bi-check-circle me-2"></i>Verify Payment
                            </button>
                            <button class="btn btn-danger w-100 mb-2" data-bs-toggle                            <button class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="bi bi-x-circle me-2"></i>Reject Payment
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'Pending' && $request['payment_method'] == 'Cash'): ?>
                            <!-- Approve Cash Payment (Direct to Ready) -->
                            <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-check2-all me-2"></i>Approve & Set Ready
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'Payment Verified'): ?>
                            <!-- Approve Document (Set Ready for Pickup) -->
                            <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="bi bi-file-earmark-check me-2"></i>Set Ready for Pickup
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($request['status'] == 'Ready for Pickup'): ?>
                            <!-- Release Document -->
                            <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#releaseModal">
                                <i class="bi bi-hand-thumbs-up me-2"></i>Mark as Released
                            </button>
                        <?php endif; ?>
                        
                        <!-- Print Preview (Available for Ready/Released) -->
                        <?php if (in_array($request['status'], ['Ready for Pickup', 'Released'])): ?>
                            <a href="preview_document.php?id=<?= $request['issuance_id'] ?>" target="_blank" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="bi bi-printer me-2"></i>Preview Document
                            </a>
                        <?php endif; ?>
                        
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<!-- Verify Payment Modal -->
<div class="modal fade" id="verifyPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../backend/staff_process_request.php" method="POST">
                <input type="hidden" name="action" value="verify_payment">
                <input type="hidden" name="issuance_id" value="<?= $request['issuance_id'] ?>">
                
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Verify Online Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the online payment proof is valid and the amount is correct.</p>
                    <div class="alert alert-info">
                        <strong>Amount to Verify:</strong> ₱<?= number_format($request['amount'], 2) ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Verify Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve & Set Ready Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../backend/staff_process_request.php" method="POST">
                <input type="hidden" name="action" value="approve_document">
                <input type="hidden" name="issuance_id" value="<?= $request['issuance_id'] ?>">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Approve Document Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>This will set the document status to <strong>"Ready for Pickup"</strong> and set expiration to <strong>2 days from now</strong>.</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        The resident will be able to print the document <strong>ONCE</strong> within 2 days.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Document</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Release Document Modal -->
<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../backend/staff_process_request.php" method="POST">
                <input type="hidden" name="action" value="release_document">
                <input type="hidden" name="issuance_id" value="<?= $request['issuance_id'] ?>">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Release Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirm that the resident has picked up the document.</p>
                    <div class="form-group">
                        <label class="form-label">Remarks (Optional)</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="e.g., Received by John Doe"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Mark as Released</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Payment Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="../../backend/staff_process_request.php" method="POST">
                <input type="hidden" name="action" value="reject_request">
                <input type="hidden" name="issuance_id" value="<?= $request['issuance_id'] ?>">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please provide a reason for rejection:</p>
                    <div class="form-group">
                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="e.g., Invalid proof of payment" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../assets/js/bootstrap.bundle.min.js"></script>
<script>
// Toast Auto-Hide
setTimeout(() => {
    const toast = document.getElementById('toast');
    if (toast) toast.classList.remove('show');
}, 3000);
</script>

</body>
</html>