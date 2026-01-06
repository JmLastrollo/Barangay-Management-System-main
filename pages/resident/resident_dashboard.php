<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

// 1. Fetch User & Profile
$email = $_SESSION['email'];
$stmtUser = $conn->prepare("SELECT * FROM users WHERE email = :email");
$stmtUser->execute([':email' => $email]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) { die("Error: User not found."); }

$stmtRes = $conn->prepare("SELECT * FROM resident_profiles WHERE user_id = :uid");
$stmtRes->execute([':uid' => $user['user_id']]);
$resident = $stmtRes->fetch(PDO::FETCH_ASSOC);

if (!$resident) { die("Error: Resident record not found."); }

$resID = $resident['resident_id'];

// 2. DASHBOARD ANALYTICS (FIXED: Using 'document_issuances' table)

// Pending Count
$stmtPending = $conn->prepare("SELECT COUNT(*) FROM document_issuances WHERE resident_id = :rid AND status = 'Pending'");
$stmtPending->execute([':rid' => $resID]);
$pendingCount = $stmtPending->fetchColumn();

// To Pay / Ready for Pickup Count
// Note: Since wala kang 'payment_status' column sa table na ito, ginamit ko ang 'Ready for Pickup' 
// bilang indicator na kailangan na itong asikasuhin o bayaran (kung Cash).
$stmtPay = $conn->prepare("SELECT COUNT(*) FROM document_issuances WHERE resident_id = :rid AND status = 'Ready for Pickup'");
$stmtPay->execute([':rid' => $resID]);
$toPayCount = $stmtPay->fetchColumn();

// Completed Count (Released)
// Note: Sa schema mo, 'Released' ang status kapag tapos na.
$stmtDone = $conn->prepare("SELECT COUNT(*) FROM document_issuances WHERE resident_id = :rid AND status = 'Released'");
$stmtDone->execute([':rid' => $resID]);
$doneCount = $stmtDone->fetchColumn();

// 3. RECENT TRANSACTIONS (FIXED: Using 'requested_at' instead of 'created_at')
$stmtRecent = $conn->prepare("SELECT * FROM document_issuances WHERE resident_id = :rid ORDER BY requested_at DESC LIMIT 3");
$stmtRecent->execute([':rid' => $resID]);
$recentTrans = $stmtRecent->fetchAll(PDO::FETCH_ASSOC);

// 4. LATEST ANNOUNCEMENTS
$stmtAnnounce = $conn->query("SELECT * FROM announcements WHERE status = 'Active' ORDER BY date DESC LIMIT 3");
$announcements = $stmtAnnounce->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Resident Dashboard - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">RESIDENT <span class="green">DASHBOARD</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4"> 
            
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold text-dark m-0">Hello, <?= htmlspecialchars($resident['first_name']) ?>! 👋</h2>
                    <p class="text-muted m-0">Here's what's happening with your account today.</p>
                </div>
                <div class="text-end d-none d-md-block">
                    <h5 class="fw-bold m-0 text-primary"><?= date('l, F j, Y') ?></h5>
                    <small class="text-muted">Current Date</small>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="stat-card orange">
                        <div class="stat-info">
                            <h3><?= $pendingCount ?></h3>
                            <p>Pending Requests</p>
                        </div>
                        <div class="stat-icon-bg bg-orange-light"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card blue">
                        <div class="stat-info">
                            <h3><?= $toPayCount ?></h3>
                            <p>To Pay / Pickup</p>
                        </div>
                        <div class="stat-icon-bg bg-blue-light"><i class="bi bi-wallet2"></i></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card green">
                        <div class="stat-info">
                            <h3><?= $doneCount ?></h3>
                            <p>Completed</p>
                        </div>
                        <div class="stat-icon-bg bg-green-light"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-lg-8">
                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-grid-fill me-2"></i>Quick Services</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <a href="resident_rqs_service.php" class="action-card h-100 flex-row text-start p-3">
                                <div class="action-icon mb-0 me-3 fs-3"><i class="bi bi-file-earmark-text"></i></div>
                                <div>
                                    <div class="action-title">Request Document</div>
                                    <div class="action-desc small">Clearance, Indigency, Residency</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="my_requests.php" class="action-card h-100 flex-row text-start p-3">
                                <div class="action-icon mb-0 me-3 fs-3"><i class="bi bi-clock-history"></i></div>
                                <div>
                                    <div class="action-title">Transaction History</div>
                                    <div class="action-desc small">View status of all requests</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="resident_file_complaint.php" class="action-card h-100 flex-row text-start p-3">
                                <div class="action-icon mb-0 me-3 fs-3"><i class="bi bi-shield-exclamation"></i></div>
                                <div>
                                    <div class="action-title">File a Complaint</div>
                                    <div class="action-desc small">Submit report to barangay</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="profile_edit.php" class="action-card h-100 flex-row text-start p-3">
                                <div class="action-icon mb-0 me-3 fs-3"><i class="bi bi-person-circle"></i></div>
                                <div>
                                    <div class="action-title">My Profile</div>
                                    <div class="action-desc small">Update personal information</div>
                                </div>
                            </a>
                        </div>
                    </div>

                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-activity me-2"></i>Recent Transactions</h5>
                    <div class="activity-box">
                        <?php if(empty($recentTrans)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No recent transactions found.
                            </div>
                        <?php else: ?>
                            <?php foreach($recentTrans as $trans): 
                                $statusColor = match($trans['status']) {
                                    'Pending' => 'dot-pending',
                                    'Ready for Pickup', 'Payment Verified' => 'dot-approved',
                                    'Rejected', 'Expired' => 'dot-rejected',
                                    'Released' => 'dot-approved',
                                    default => 'dot-pending'
                                };
                            ?>
                            <div class="activity-item">
                                <div class="act-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="act-details flex-grow-1">
                                    <h6><?= htmlspecialchars($trans['document_type']) ?></h6>
                                    <small><?= date('M j, Y h:i A', strtotime($trans['requested_at'])) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-dark border">
                                        <span class="status-dot <?= $statusColor ?>"></span> <?= $trans['status'] ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3 pt-2 border-top">
                                <a href="my_requests.php" class="text-decoration-none small fw-bold">View All Transactions <i class="bi bi-arrow-right"></i></a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-megaphone-fill me-2"></i>Latest Updates</h5>
                    <div class="activity-box p-0 overflow-hidden">
                        <div class="list-group list-group-flush">
                            <?php if(empty($announcements)): ?>
                                <div class="p-4 text-center text-muted">No active announcements.</div>
                            <?php else: ?>
                                <?php foreach($announcements as $ann): ?>
                                <div class="list-group-item p-3 border-0 border-bottom">
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                        <small class="text-primary fw-bold text-uppercase" style="font-size: 0.7rem;">
                                            <?= date('M d, Y', strtotime($ann['date'])) ?>
                                        </small>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($ann['time'])) ?></small>
                                    </div>
                                    <h6 class="mb-1 fw-bold text-dark"><?= htmlspecialchars($ann['title']) ?></h6>
                                    <p class="mb-1 text-muted small text-truncate" style="max-width: 250px;">
                                        <?= htmlspecialchars($ann['details']) ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                                <div class="p-3 text-center bg-light">
                                    <a href="resident_announcements.php" class="btn btn-outline-primary btn-sm w-100 rounded-pill">View All Updates</a>
                                </div>
                            <?php endif; ?>
                        </div>
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
    
    <script>
        <?php if(isset($_SESSION['login_welcome'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastBody = document.getElementById('toastMessage');
            toastBody.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i> Welcome back, <strong><?= htmlspecialchars($resident['first_name']) ?></strong>!';
            toastEl.classList.add('bg-success'); 
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            <?php unset($_SESSION['login_welcome']); ?>
        <?php endif; ?>
    </script>

</body>
</html>