<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

try {
    // Get Resident ID
    $stmtRes = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = :uid");
    $stmtRes->execute([':uid' => $user_id]);
    $resProfile = $stmtRes->fetch(PDO::FETCH_ASSOC);
    $resident_id = $resProfile['resident_id'];

    // Get Requests + Payment Details (JOIN)
    // Note: Make sure 'approved_date' and 'print_attempts' are in your 'issuance' table
    $sql = "SELECT i.*, p.payment_method AS method, p.status AS pay_audit_status 
            FROM issuance i 
            LEFT JOIN payments p ON i.issuance_id = p.issuance_id 
            WHERE i.resident_id = :rid 
            ORDER BY i.request_date DESC";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':rid' => $resident_id]);
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $requests = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Transactions - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <style>
        .status-badge { font-size: 0.75rem; font-weight: 700; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; }
        .status-Pending { background: #fff3cd; color: #856404; }
        .status-ReadyforPickup { background: #cce5ff; color: #004085; } /* Updated Class Name */
        .status-Received { background: #d4edda; color: #155724; }
        .status-Rejected { background: #f8d7da; color: #721c24; }

        .pay-badge { font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: 4px; }
        .pay-Unpaid { background: #f8d7da; color: #721c24; }
        .pay-Paid { background: #d4edda; color: #155724; }
        .pay-ForVerification { background: #e2e3e5; color: #383d41; }
        .pay-Free { background: #d1e7dd; color: #0f5132; }
    </style>
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">MY <span class="green">TRANSACTIONS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0"><i class="bi bi-clock-history me-2 text-primary"></i>History</h3>
                <a href="resident_rqs_service.php" class="btn btn-primary rounded-pill fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> New Request
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="requestsTable" class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Control No.</th>
                                    <th>Document</th>
                                    <th>Price</th>
                                    <th>Payment Status</th>
                                    <th class="text-center">Req Status</th>
                                    <th class="text-center">Action</th> </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($requests)): ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $req): 
                                        // 1. Prepare Variables
                                        $status = $req['status'];
                                        $payMethod = $req['method'] ?? 'Cash'; // galing sa query: p.payment_method AS method
                                        $price = $req['price'];
                                        $printAttempts = $req['print_attempts'] ?? 0;
                                        $approvedDate = $req['approved_date'];

                                        // 2. Check Expiration (48 hours)
                                        $isExpired = false;
                                        if ($approvedDate) {
                                            $appTime = new DateTime($approvedDate);
                                            $currTime = new DateTime();
                                            $diff = $currTime->diff($appTime);
                                            $hours = ($diff->days * 24) + $diff->h;
                                            if ($hours > 48) $isExpired = true;
                                        }

                                        // 3. Determine if Print Button should show
                                        // Show IF: Ready for Pickup AND (Online Payment OR Free) AND Not Expired AND Not Printed Yet
                                        $showPrint = false;
                                        if ($status === 'Ready for Pickup' && 
                                            ($payMethod === 'Online Payment' || $price == 0) && 
                                            !$isExpired && 
                                            $printAttempts < 1) {
                                            $showPrint = true;
                                        }

                                        // Styles
                                        $statusClass = 'status-' . str_replace(' ', '', $status);
                                        $payClass = 'pay-' . str_replace(' ', '', $req['payment_status'] ?? 'Unpaid');
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary small">
                                            #<?= htmlspecialchars($req['request_control_no']) ?>
                                            <div class="small text-muted fw-normal"><?= date('M d, Y', strtotime($req['request_date'])) ?></div>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($req['document_type']) ?></td>
                                        <td>₱ <?= number_format($price, 2) ?></td>
                                        
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small fw-bold text-dark"><?= htmlspecialchars($req['method'] ?? 'Cash / Pickup') ?></span>
                                                <span class="pay-badge <?= $payClass ?> w-auto d-inline-block mt-1 text-center">
                                                    <?= htmlspecialchars($req['payment_status'] ?? 'Unpaid') ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
                                        </td>

                                        <td class="text-center">
                                            <?php if($showPrint): ?>
                                                <a href="check_print_limit.php?id=<?= $req['issuance_id'] ?>" 
                                                target="_blank" 
                                                class="btn btn-primary btn-sm rounded-pill px-3"
                                                onclick="return confirm('Reminder: You can only print/download this ONCE. The link expires in 48 hours. Ensure your printer is ready. Proceed?');">
                                                <i class="bi bi-printer-fill"></i> Print
                                                </a>
                                                <div class="text-danger fw-bold mt-1" style="font-size:10px;">Valid: 48hrs</div>

                                            <?php elseif($status === 'Ready for Pickup' && $payMethod === 'Cash' && $price > 0): ?>
                                                <span class="badge bg-info text-dark border border-info">Pick up at Barangay</span>

                                            <?php elseif($printAttempts >= 1 && $payMethod === 'Online Payment'): ?>
                                                <span class="badge bg-secondary"><i class="bi bi-check-circle"></i> Printed</span>

                                            <?php elseif($isExpired && $status === 'Ready for Pickup' && $payMethod === 'Online Payment'): ?>
                                                <span class="badge bg-danger">Link Expired</span>

                                            <?php elseif($status === 'Received'): ?>
                                                <span class="text-success small fw-bold"><i class="bi bi-check-all"></i> Completed</span>

                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
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

        <?php include '../../includes/resident_footer.php'; ?>

    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
    
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            <?php if(!empty($requests)): ?>
            $('#requestsTable').DataTable({ "order": [[ 0, "desc" ]] });
            <?php endif; ?>

            // --- TOAST LOGIC ---
            <?php if(isset($_SESSION['toast'])): ?>
                const toastEl = document.getElementById('liveToast');
                const toastBody = document.getElementById('toastMessage');
                
                toastBody.innerText = "<?= $_SESSION['toast']['msg'] ?>";
                toastEl.classList.remove('bg-success', 'bg-danger');
                toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
                
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            <?php unset($_SESSION['toast']); endif; ?>
        });
    </script>
</body>
</html>