<?php
// pages/staff/staff_dashboard.php

// 1. SESSION & SECURITY
require_once '../../backend/auth_staff.php'; // Security: Staff Only
require_once '../../backend/db_connect.php'; // Database Connection (PDO)

// --- ARRAYS & VARS (Para sa Admin-Style Layout) ---
$issuanceTypes = [
    'Barangay Clearance', 'Certificate of Residency', 'Certificate of Indigency', 
    'Certificate of Endorsement', 'Solicitations', 'Lupon', 'Others'
];

$issuanceIcons = [
    'Barangay Clearance' => 'bi-file-earmark-text',
    'Certificate of Residency' => 'bi-award',
    'Certificate of Indigency' => 'bi-cash-coin',
    'Certificate of Endorsement' => 'bi-file-earmark-check',
    'Solicitations' => 'bi-envelope',
    'Lupon' => 'bi-hourglass-split',
    'Others' => 'bi-plus-lg'
];

$pendingAccounts = 0;
$activeBlotter = 0;
$totalIssuancesPending = 0;
$issuanceCounts = array_fill_keys($issuanceTypes, 0);

try {
    // A. PENDING ACCOUNT APPROVALS (Priority 1)
    // NOTE: Check kung 'users' o 'residents' table ang gamit mo. Base sa login, 'users' table ang may status.
    $accStmt = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'Pending' AND role = 'Resident'");
    $pendingAccounts = $accStmt->fetchColumn();

    // B. ACTIVE BLOTTER CASES (Priority 2)
    $blotStmt = $conn->query("SELECT COUNT(*) FROM blotter_records WHERE status = 'Active' OR status = 'Pending'");
    $activeBlotter = $blotStmt->fetchColumn();

    // C. TOTAL PENDING ISSUANCES (Red Banner)
    $pendingStmt = $conn->query("SELECT COUNT(*) FROM issuance_requests WHERE status = 'Pending'");
    $totalIssuancesPending = $pendingStmt->fetchColumn();

    // D. ISSUANCE COUNTS PER TYPE (Grid)
    $typeStmt = $conn->prepare("SELECT COUNT(*) FROM issuance_requests WHERE status = 'Pending' AND document_type = :type");
    foreach ($issuanceTypes as $type) {
        $typeStmt->execute([':type' => $type]);
        $issuanceCounts[$type] = $typeStmt->fetchColumn();
    }

} catch (PDOException $e) {
    // Silent error handling to prevent crash
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Staff Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" /> <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>

<body>

    <?php include '../../includes/staff_sidebar.php'; ?>

    <main id="main-content">
        
        <div class="header">
            <h1 class="header-title">STAFF <span class="green">DASHBOARD</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <section class="top-info">
                <section class="left-column">
                    
                    <div class="stats-boxes">
                        <article class="stats-box">
                            <i class="bi bi-person-check-fill"></i>
                            <div class="details">
                                <div class="info"><?php echo number_format($pendingAccounts); ?></div>
                                <div class="label">Pending Accounts</div>
                            </div>
                        </article>

                        <article class="stats-box" style="border-bottom-color: #ef5350;">
                            <i class="bi bi-gavel" style="color: #ef5350;"></i>
                            <div class="details">
                                <div class="info"><?php echo number_format($activeBlotter); ?></div>
                                <div class="label">Active Cases</div>
                            </div>
                        </article>
                    </div>

                    <div class="red-banner">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> 
                        Total Issuances Pending Release: <?php echo number_format($totalIssuancesPending); ?>
                    </div>

                    <div class="issuances-grid">
                        <?php foreach($issuanceTypes as $type): ?>
                            <div class="issuance-tile">
                                <i class="bi <?= $issuanceIcons[$type] ?? 'bi-file-earmark-text' ?>"></i>
                                <div class="label"><?= htmlspecialchars($type) ?></div>
                                <div class="count"><?= $issuanceCounts[$type] ?? 0 ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <section class="right-column">
                    <section class="calendar-container">
                        <div><h3 class="calendar-header">BARANGAY <span class="green">CALENDAR</span></h3></div>
                        <div class="calendar-header">
                            <button id="prev-month">&lt;</button>
                            <h3 id="month-year"></h3>
                            <button id="next-month">&gt;</button>
                        </div>
                        <div class="calendar-grid" id="calendar-grid"></div>
                    </section>

                    <article class="timeline" id="timeline-events">
                        <div class="timeline-message"><p>Loading upcoming events...</p></div>
                    </article>
                </section>
            </section>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/calendar.js"></script>
    
    <?php if (isset($_SESSION['login_welcome'])): ?>
    <script>
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast'; 
            toast.classList.add(type); 
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        document.addEventListener('DOMContentLoaded', function() {
            showToast("Welcome back, Staff!", "success");
        });
    </script>
    <?php unset($_SESSION['login_welcome']); endif; ?>

</body>
</html>