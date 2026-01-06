<?php
session_start();
require_once '../../backend/db_connect.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Resident') {
    header("Location: ../../login.php");
    exit();
}

// Get Resident ID
$user_id = $_SESSION['user_id'];
// Updated to use generic select to avoid column name errors
$stmtRes = $conn->prepare("SELECT * FROM resident_profiles WHERE user_id = ?");
$stmtRes->execute([$user_id]);
$resident = $stmtRes->fetch(PDO::FETCH_ASSOC);

if ($resident) {
    $resident_id = $resident['resident_id']; // Assuming column is resident_id
} else {
    // Fallback if profile not found
    $resident_id = 0; 
}

// Fetch Requests
$stmtReq = $conn->prepare("
    SELECT r.*, p.payment_method, p.payment_status 
    FROM issuance_requests r 
    LEFT JOIN payments p ON r.request_id = p.request_id 
    WHERE r.resident_id = ? 
    ORDER BY r.date_requested DESC
");
$stmtReq->execute([$resident_id]);
$requests = $stmtReq->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Documents - BMS</title>
    
    <!-- Bootstrap & Icons -->
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/resident.css">
    <link rel="stylesheet" href="../../css/toast.css">

    <style>
        /* --- CUSTOM STYLES PARA SA ISSUANCE PAGE --- */
        
        /* 1. Main Layout Fix (Para hindi matakpan ng Sidebar) */
        body {
            background-color: #f4f6f9; /* Light gray background */
        }
        
        /* Ito ang mag-aadjust ng content para hindi matakpan ng sidebar */
        .main-content {
            margin-left: 260px; /* Adjust kung gaano kalapad ang sidebar mo */
            padding: 30px;
            transition: all 0.3s;
            min-height: 100vh;
        }

        /* Mobile Responsiveness */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-top: 80px; /* Space para sa mobile navbar toggle */
            }
        }

        /* 2. Card Styling */
        .card-issuance {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }
        .card-header-custom {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* 3. Table Styling */
        .table-custom thead th {
            background-color: #f8f9fa;
            color: #555;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            border-bottom: 2px solid #eee;
            padding: 15px;
            white-space: nowrap;
        }
        .table-custom tbody td {
            padding: 15px;
            vertical-align: middle;
            color: #333;
            font-size: 0.95rem;
        }
        .table-custom tbody tr:hover {
            background-color: #fcfcfc;
        }

        /* 4. Status Badges */
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-completed { background: #cce5ff; color: #004085; }
        .status-expired { background: #e2e3e5; color: #383d41; }

        /* 5. Action Buttons */
        .btn-print {
            background-color: #0d6efd;
            color: white;
            border-radius: 6px;
            padding: 6px 16px;
            font-size: 0.85rem;
            transition: all 0.2s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-print:hover {
            background-color: #0b5ed7;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(13, 110, 253, 0.2);
            color: white;
        }
        .btn-disabled {
            background-color: #e9ecef;
            color: #6c757d;
            border: none;
            cursor: not-allowed;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<?php include '../../includes/resident_sidebar.php'; ?>

<!-- MAIN CONTENT WRAPPER -->
<div class="main-content">
    
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold text-dark mb-1">Document Issuance</h3>
            <p class="text-muted small mb-0">Request and manage your barangay documents.</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#requestModal">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </button>
    </div>

    <!-- Requests Table Card -->
    <div class="card card-issuance">
        <div class="card-header-custom">
            <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Request History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>Reference No.</th>
                            <th>Document Type</th>
                            <th>Date Requested</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $row): ?>
                                <?php 
                                    // Logic para sa Expiration at Printing
                                    $current_date = new DateTime();
                                    $expiration_date = $row['expiration_date'] ? new DateTime($row['expiration_date']) : null;
                                    $is_expired = $expiration_date && ($current_date > $expiration_date);
                                    $is_printed = $row['print_attempts'] > 0;
                                ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?php echo htmlspecialchars($row['reference_number']); ?></td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($row['document_type']); ?></div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                            <?php echo htmlspecialchars($row['purpose']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo date("M d, Y", strtotime($row['date_requested'])); ?>
                                        <small class="text-muted d-block"><?php echo date("h:i A", strtotime($row['date_requested'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="small fw-bold"><?php echo $row['payment_method']; ?></span>
                                            <?php if($row['payment_status'] == 'Paid'): ?>
                                                <span class="badge bg-success rounded-pill" style="width: fit-content;">Paid</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark rounded-pill" style="width: fit-content;">Pending</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = 'status-pending';
                                            $statusText = $row['status'];

                                            if ($row['status'] == 'Approved') $statusClass = 'status-approved';
                                            elseif ($row['status'] == 'Rejected') $statusClass = 'status-rejected';
                                            elseif ($row['status'] == 'Completed') $statusClass = 'status-completed';
                                            
                                            if ($is_expired && $row['status'] == 'Approved') {
                                                $statusClass = 'status-expired';
                                                $statusText = 'Expired';
                                            }
                                        ?>
                                        <span class="badge-status <?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <!-- ACTION BUTTONS LOGIC -->
                                        <?php if ($row['status'] == 'Approved'): ?>
                                            
                                            <?php if ($is_expired): ?>
                                                <button class="btn-disabled" disabled>
                                                    <i class="bi bi-x-circle"></i> Expired
                                                </button>
                                            
                                            <?php elseif ($is_printed): ?>
                                                <button class="btn-disabled" disabled>
                                                    <i class="bi bi-check2-all"></i> Printed
                                                </button>
                                            
                                            <?php else: ?>
                                                <a href="../../backend/print_document.php?ref=<?php echo $row['reference_number']; ?>" 
                                                   target="_blank" 
                                                   class="btn-print text-decoration-none"
                                                   onclick="return confirm('Warning: You can only print this ONCE. Make sure your printer is ready. Proceed?');">
                                                   <i class="bi bi-printer-fill"></i> Print Now
                                                </a>
                                                <div class="mt-1">
                                                    <small class="text-danger fw-bold" style="font-size: 10px;">
                                                        Valid until: <?php echo $expiration_date->format('M d, h:i A'); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>

                                        <?php elseif ($row['status'] == 'Pending'): ?>
                                            <span class="text-muted small fst-italic">Waiting for approval</span>
                                        <?php elseif ($row['status'] == 'Rejected'): ?>
                                            <span class="text-danger small">Request Rejected</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-folder2-open display-4 d-block mb-2"></i>
                                    No document requests found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- REQUEST MODAL -->
<div class="modal fade" id="requestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="../../backend/issuance_add.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-plus me-2"></i>Request Document</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Document Type -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="" disabled selected>Select Document</option>
                            <option value="Barangay Clearance">Barangay Clearance</option>
                            <option value="Certificate of Indigency">Certificate of Indigency</option>                            
                            <option value="Certificate of Residency">Certificate of Residency</option>
                        </select>
                    </div>

                    <!-- Purpose -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Purpose</label>
                        <textarea name="purpose" class="form-control" rows="3" placeholder="Reason for request (e.g., Employment, Scholarship)" required></textarea>
                    </div>

                    <!-- Payment Method -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary">Payment Method</label>
                        <select name="payment_method" id="payment_method" class="form-select" required onchange="togglePaymentProof()">
                            <option value="Cash">Cash (Walk-in Payment)</option>
                            <option value="Online">Online (GCash / Maya)</option>
                        </select>
                    </div>

                    <!-- Online Payment Section (Hidden by default) -->
                    <div id="online_payment_section" class="d-none bg-light p-3 rounded border">
                        <p class="mb-2 fw-bold text-dark">Scan QR Code to Pay:</p>
                        <div class="text-center mb-3">
                            <!-- Placeholder QR Code - Palitan mo ng actual image mo -->
                            <img src="../../assets/images/gcash_qr.jpg" alt="GCash QR" class="img-fluid border shadow-sm" style="max-width: 150px; border-radius: 8px;">
                            <p class="small text-muted mt-1">GCash: 0912-345-6789 (Brgy Treasurer)</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Upload Proof of Payment (Screenshot)</label>
                            <input type="file" name="proof_payment" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted" style="font-size: 11px;">Required for online transactions.</small>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification Script -->
<?php if (isset($_SESSION['toast'])): ?>
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;">
  <div id="liveToast" class="toast align-items-center text-white bg-<?php echo $_SESSION['toast']['type'] == 'success' ? 'success' : 'danger'; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <?php echo $_SESSION['toast']['msg']; ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php unset($_SESSION['toast']); endif; ?>

<script src="../../js/bootstrap.bundle.min.js"></script>
<script>
    // Initialize Toast if it exists
    var toastEl = document.getElementById('liveToast');
    if (toastEl) {
        var toast = new bootstrap.Toast(toastEl);
        toast.show();
    }

    // Script para ipakita/itago ang upload field base sa payment method
    function togglePaymentProof() {
        var method = document.getElementById("payment_method").value;
        var section = document.getElementById("online_payment_section");
        var fileInput = section.querySelector("input[type='file']");

        if (method === "Online") {
            section.classList.remove("d-none");
            fileInput.setAttribute("required", "required");
        } else {
            section.classList.add("d-none");
            fileInput.removeAttribute("required");
            fileInput.value = ""; // Clear file if switched back to cash
        }
    }
</script>

</body>
</html>