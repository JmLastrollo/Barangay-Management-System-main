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

// 2. DASHBOARD ANALYTICS
$stmtPending = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE resident_id = :rid AND status = 'Pending'");
$stmtPending->execute([':rid' => $resID]);
$pendingCount = $stmtPending->fetchColumn();

$stmtPay = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE resident_id = :rid AND status = 'Approved' AND payment_status = 'Unpaid'");
$stmtPay->execute([':rid' => $resID]);
$toPayCount = $stmtPay->fetchColumn();

$stmtDone = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE resident_id = :rid AND status = 'Released'");
$stmtDone->execute([':rid' => $resID]);
$doneCount = $stmtDone->fetchColumn();

// 3. RECENT TRANSACTIONS
$stmtRecent = $conn->prepare("SELECT * FROM issuance WHERE resident_id = :rid ORDER BY request_date DESC LIMIT 3");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
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
                            <p>To Pay</p>
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
                            <a href="issuance_table.php" class="action-card h-100 flex-row text-start p-3">
                                <div class="action-icon mb-0 me-3 fs-3"><i class="bi bi-clock-history"></i></div>
                                <div>
                                    <div class="action-title">Transaction History</div>
                                    <div class="action-desc small">View status of all requests</div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="#" class="action-card h-100 flex-row text-start p-3">
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
                            <p class="text-center text-muted my-3">No recent transactions found.</p>
                        <?php else: ?>
                            <?php foreach($recentTrans as $trans): 
                                $statusColor = match($trans['status']) {
                                    'Pending' => 'dot-pending',
                                    'Approved' => 'dot-approved',
                                    'Released' => 'dot-approved',
                                    'Rejected' => 'dot-rejected',
                                    default => 'dot-pending'
                                };
                            ?>
                            <div class="activity-item">
                                <div class="act-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="act-details flex-grow-1">
                                    <h6><?= htmlspecialchars($trans['document_type']) ?></h6>
                                    <small><?= date('M j, Y h:i A', strtotime($trans['request_date'])) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-light text-dark border">
                                        <span class="status-dot <?= $statusColor ?>"></span> <?= $trans['status'] ?>
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div class="text-center mt-3 pt-2 border-top">
                                <a href="issuance_table.php" class="text-decoration-none small fw-bold">View All Transactions <i class="bi bi-arrow-right"></i></a>
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
                                    <a href="../../see-more-announcement.php" class="btn btn-outline-primary btn-sm w-100 rounded-pill">View All Updates</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include '../../includes/resident_footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>