<?php
session_start();

// --- 1. DIRECT SESSION CHECK ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff', 'Barangay Staff'])) {
    header("Location: ../../login.php"); 
    exit();
}

// --- 2. DATABASE CONNECTION ---
require_once '../../backend/db_connect.php'; 

// --- ARRAYS ---
$issuanceTypes = [
    'Barangay Clearance',
    'Certificate of Residency',
    'Certificate of Indigency',
    'Building Permit',
    'Solicitations',
    'Lupon',
    'Others'
];

$issuanceIcons = [
    'Barangay Clearance' => 'bi-file-earmark-text',
    'Certificate of Residency' => 'bi-award',
    'Certificate of Indigency' => 'bi-cash-coin',
    'Building Permit' => 'bi-wrench',
    'Solicitations' => 'bi-envelope',
    'Lupon' => 'bi-hourglass-split',
    'Others' => 'bi-plus-lg'
];

try {
    // --- A. GET TOTAL POPULATION ---
    $popStmt = $conn->query("SELECT COUNT(*) FROM resident_profiles WHERE status = 'Active'");
    $totalPopulation = $popStmt->fetchColumn();

    // --- B. GET TOTAL HOUSEHOLDS ---
    // Note: Assuming 'family_head' logic exists in resident_profiles or related table
    // Adjust column name if necessary based on your register_resident.php structure
    $houseStmt = $conn->query("SELECT COUNT(*) FROM resident_profiles WHERE status = 'Active'"); 
    $totalHouseholds = floor($totalPopulation / 4); // Estimate if no direct column, or replace with real query

    // --- C. GET TOTAL PENDING ISSUANCES ---
    $pendingStmt = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE status = :status");
    $pendingStmt->execute([':status' => 'Pending']);
    $totalIssuancesPending = $pendingStmt->fetchColumn();

    // --- D. COUNT PER DOCUMENT TYPE ---
    $issuanceCounts = [];
    $typeStmt = $conn->prepare("SELECT COUNT(*) FROM issuance WHERE status = 'Pending' AND document_type = :type");

    foreach ($issuanceTypes as $type) {
        $typeStmt->execute([':type' => $type]);
        $issuanceCounts[$type] = $typeStmt->fetchColumn();
    }

} catch (PDOException $e) {
    $totalPopulation = 0;
    $totalHouseholds = 0;
    $totalIssuancesPending = 0;
    $issuanceCounts = array_fill_keys($issuanceTypes, 0);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-people"></i> Officials</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_officials.php">Active Officials</a>
                <a href="admin_officials_archive.php">Past Officials</a>
            </div>
        </div>

        <a href="admin_issuance.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-folder2-open"></i> Records</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_blotter.php">Blotter Records</a>
                <a href="admin_rec_complaints.php">Complaints</a>
            </div>
        </div>

        <a href="admin_health.php"><i class="bi bi-heart-pulse"></i> Health Center</a>
        <a href="admin_finance.php"><i class="bi bi-cash-coin"></i> Finance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-archive"></i> Archives</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_archives.php">Archived Documents</a>
                <a href="admin_backup.php">System Backup</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>
<div style="width:100%">

    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
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
                    <div>
                        <h3 class="calendar-header">BARANGAY <span class="green">CALENDAR</span></h3>
                    </div>
                    <div class="calendar-header">
                        <button id="prev-month">&lt;</button>
                        <h3 id="month-year"></h3>
                        <button id="next-month">&gt;</button>
                    </div>
                    <div class="calendar-grid" id="calendar-grid"></div>
                </section>

                <article class="timeline" id="timeline-events">
                    <div class="timeline-message">
                        <p>Loading upcoming events...</p>
                    </div>
                </article>
            </section>
        </section>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/js/calendar.js"></script>

<script>
    // Sidebar Toggle
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    // Dropdown Logic (Matched with new CSS)
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            // Toggle 'active' on the container, CSS handles the rest
            this.parentElement.classList.toggle('active');
        });
    });

    // Toast Function
    function showToast(message, type = "success") {
        const t = document.getElementById("toast");
        t.className = "toast"; 
        t.innerHTML = `<div class="toast-body">${message}</div>`;
        
        t.classList.add(type);
        t.classList.add("show");

        setTimeout(() => {
            t.classList.remove("show");
        }, 3000);
    }

    // Fetch Announcements for Timeline
    fetch("../../backend/announcement_get_dashboard.php")
        .then(res => res.json())
        .then(data => {
            let timelineHTML = "";

            if(data && data.length > 0) {
                data.forEach(event => {
                    timelineHTML += `
                        <div class="timeline-event mb-3 p-3 border-start border-4 border-success bg-white shadow-sm rounded">
                            <strong class="event-title text-success">${event.title}</strong><br>
                            <small class="event-location text-muted"><i class="bi bi-geo-alt-fill me-1"></i>${event.location}</small><br>
                            <small class="event-datetime text-dark"><i class="bi bi-calendar-event me-1"></i>${event.date} | ${event.time}</small>
                        </div>
                    `;
                });
                document.getElementById("timeline-events").innerHTML = timelineHTML;
            } else {
                document.getElementById("timeline-events").innerHTML = "<div class='timeline-message text-center p-3 text-muted'>No upcoming announcements.</div>";
            }
        })
        .catch(err => {
            console.error(err);
            document.getElementById("timeline-events").innerHTML = "<div class='timeline-message text-center p-3 text-muted'>No upcoming announcements.</div>";
        });

</script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= htmlspecialchars($_SESSION['toast']) ?>", "<?= htmlspecialchars($_SESSION['toast_type'] ?? 'success') ?>");
</script>
<?php 
    unset($_SESSION['toast']); 
    unset($_SESSION['toast_type']); 
endif; 
?>

</body>
</html>