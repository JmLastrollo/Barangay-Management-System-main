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
        .status-Approved { background: #cce5ff; color: #004085; }
        .status-Released { background: #d4edda; color: #155724; }
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
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($requests)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No requests found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $req): 
                                        $reqStatusClass = 'status-' . ($req['status'] ?? 'Pending');
                                        
                                        // Clean up Payment Status
                                        $payStat = str_replace(' ', '', $req['payment_status'] ?? 'Unpaid');
                                        $payStatusClass = 'pay-' . $payStat;
                                        
                                        $payMethod = $req['method'] ? $req['method'] : 'Cash / Pickup';
                                        if ($req['price'] == 0) $payMethod = 'Free';
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-primary small">
                                            #<?= htmlspecialchars($req['request_control_no']) ?>
                                            <div class="small text-muted fw-normal"><?= date('M d, Y', strtotime($req['request_date'])) ?></div>
                                        </td>
                                        <td class="fw-bold"><?= htmlspecialchars($req['document_type']) ?></td>
                                        <td>₱ <?= number_format($req['price'], 2) ?></td>
                                        
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="small fw-bold text-dark"><?= htmlspecialchars($payMethod) ?></span>
                                                <span class="pay-badge <?= $payStatusClass ?> w-auto d-inline-block mt-1 text-center" style="max-width: 120px;">
                                                    <?= htmlspecialchars($req['payment_status']) ?>
                                                </span>
                                            </div>
                                        </td>

                                        <td class="text-center">
                                            <span class="status-badge <?= $reqStatusClass ?>"><?= htmlspecialchars($req['status']) ?></span>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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