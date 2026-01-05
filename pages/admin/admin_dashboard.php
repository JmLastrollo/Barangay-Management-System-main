<?php
session_start();

// --- 1. SESSION CHECK ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php"); 
    exit();
}

// --- 2. DATABASE CONNECTION ---
require_once '../../backend/db_connect.php'; 

// --- ARRAYS & VARS ---
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

$totalPopulation = 0;
$totalHouseholds = 0;
$totalIssuancesPending = 0;
$issuanceCounts = array_fill_keys($issuanceTypes, 0);

try {
    // A. POPULATION
    $popStmt = $conn->query("SELECT COUNT(*) FROM resident_profiles WHERE status = 'Active'");
    $totalPopulation = $popStmt->fetchColumn();

    // B. HOUSEHOLDS
    $totalHouseholds = floor($totalPopulation / 4); 

    // C. PENDING ISSUANCES
    $pendingStmt = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE status = :status");
    $pendingStmt->execute([':status' => 'Pending']);
    $totalIssuancesPending = $pendingStmt->fetchColumn();

    // D. ISSUANCE COUNTS
    $typeStmt = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE status = 'Pending' AND document_type = :type");
    foreach ($issuanceTypes as $type) {
        $typeStmt->execute([':type' => $type]);
        $issuanceCounts[$type] = $typeStmt->fetchColumn();
    }

} catch (PDOException $e) {

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>

<body>

    <?php include '../../includes/sidebar.php'; ?>

    <main id="main-content">
        
        <div class="header">
            <h1 class="header-title">ADMIN <span class="green">DASHBOARD</span></h1>
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
                            <i class="bi bi-people-fill"></i>
                            <div class="details">
                                <div class="info"><?php echo number_format($totalPopulation); ?></div>
                                <div class="label">Total Population</div>
                            </div>
                        </article>

                        <article class="stats-box">
                            <i class="bi bi-house-door-fill"></i>
                            <div class="details">
                                <div class="info"><?php echo number_format($totalHouseholds); ?></div>
                                <div class="label">Est. Households</div>
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

    <script src="assets/js/bootstrap.bundle.min.js"></script>
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
            // Kunin ang pangalan mula sa session (kung naka-set) o default
            var name = "<?= htmlspecialchars($_SESSION['fname'] ?? 'Admin') ?>";
            showToast("Welcome back, " + name + "!", "success");
        });
    </script>
    <?php 
        // Unset agad para hindi lumabas ulit pag ni-refresh
        unset($_SESSION['login_welcome']); 
    endif; 
    ?>

    <?php if (isset($_SESSION['toast'])): ?>
    <script>
        showToast("<?= htmlspecialchars($_SESSION['toast']) ?>", "<?= htmlspecialchars($_SESSION['toast_type'] ?? 'success') ?>");
    </script>
    <?php unset($_SESSION['toast'], $_SESSION['toast_type']); endif; ?>

</body>
</html>