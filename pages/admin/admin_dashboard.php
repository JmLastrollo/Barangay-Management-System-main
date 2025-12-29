<?php
session_start();

// --- 1. DIRECT SESSION CHECK ---
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff', 'Barangay Staff'])) {
    header("Location: ../../admin_login.php"); 
    exit();
}

// --- 2. DATABASE CONNECTION ---
require_once '../../backend/db_connect.php'; 

// --- MOVE ARRAYS HERE (BEFORE TRY BLOCK) ---
// Defining these here ensures they exist even if the Database connection fails
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
    $popStmt = $conn->query("SELECT COUNT(*) FROM residents");
    $totalPopulation = $popStmt->fetchColumn();

    // --- B. GET TOTAL HOUSEHOLDS ---
    $houseStmt = $conn->prepare("SELECT COUNT(*) FROM residents WHERE family_head = :status");
    $houseStmt->execute([':status' => 'Yes']);
    $totalHouseholds = $houseStmt->fetchColumn();

    // --- C. GET TOTAL PENDING ISSUANCES ---
    $pendingStmt = $conn->prepare("SELECT COUNT(*) FROM issuances WHERE status = :status");
    $pendingStmt->execute([':status' => 'Pending']);
    $totalIssuancesPending = $pendingStmt->fetchColumn();

    // --- D. COUNT PER DOCUMENT TYPE ---
    $issuanceCounts = [];
    
    // Prepare statement once for efficiency
    $typeStmt = $conn->prepare("SELECT COUNT(*) FROM issuances WHERE status = 'Pending' AND document_type = :type");

    foreach ($issuanceTypes as $type) {
        $typeStmt->execute([':type' => $type]);
        $issuanceCounts[$type] = $typeStmt->fetchColumn();
    }

} catch (PDOException $e) {
    // Kapag nagka-error (e.g., table not found), set defaults to 0
    $totalPopulation = 0;
    $totalHouseholds = 0;
    $totalIssuancesPending = 0;
    
    // Now this will work because $issuanceTypes is defined above the try block
    $issuanceCounts = array_fill_keys($issuanceTypes, 0);
    
    // Optional: Log the error silently or show it
    // error_log($e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <small><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'admin@email.com' ?></small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-file-earmark-text"></i> Records</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php">Blotter</a>
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
                            <div class="label">Households</div>
                        </div>
                    </article>
                </div>

                <div class="red-banner">Total Issuances Pending Release: <?php echo number_format($totalIssuancesPending); ?></div>

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
                        <p>Loading events...</p>
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

    // Dropdown Logic
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(){
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

    // Fetch Announcements
    fetch("../../backend/announcement_get_dashboard.php")
        .then(res => res.json())
        .then(data => {
            let timelineHTML = "";

            if(data && data.length > 0) {
                data.forEach(event => {
                    timelineHTML += `
                        <div class="timeline-event mb-3">
                            <strong class="event-title">${event.title}</strong><br>
                            <span class="event-location"><i class="bi bi-geo-alt-fill me-1"></i>${event.location}</span><br>
                            <span class="event-datetime"><i class="bi bi-calendar-event me-1"></i>${event.time} | ${event.date}</span>
                        </div>
                    `;
                });
                document.getElementById("timeline-events").innerHTML = timelineHTML;
            } else {
                document.getElementById("timeline-events").innerHTML = "<div class='timeline-message'><p>No upcoming announcements.</p></div>";
            }
        })
        .catch(err => {
            console.error(err);
            // Hide error silently or show simple message
            document.getElementById("timeline-events").innerHTML = "<div class='timeline-message'><p>No upcoming announcements.</p></div>";
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