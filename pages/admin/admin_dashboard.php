<?php
require_once '../../backend/auth_admin.php';
require __DIR__ . '/../../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->bms_db;

    $residentsCollection = $database->residents;
    $issuanceCollection = $database->issuances; 

    // Total population and households
    $totalPopulation = $residentsCollection->countDocuments([]);
    $totalHouseholds = $residentsCollection->countDocuments(['family_head' => 'Yes']);

    // Total pending issuance requests
    $totalIssuancesPending = $issuanceCollection->countDocuments(['status' => 'Pending']);

    // Count by document type
    $issuanceTypes = [
        'Barangay Clearance',
        'Certificate of Residency',
        'Certificate of Indigency',
        'Barangay Business Clearance',
        'Building Permit',
        'Solicitations',
        'Lupon',
        'Others'
    ];

    $issuanceIcons = [
    'Barangay Clearance' => 'bi-file-earmark-text',
    'Certificate of Residency' => 'bi-award',
    'Certificate of Indigency' => 'bi-cash-coin',
    'Barangay Business Clearance' => 'bi-briefcase',
    'Building Permit' => 'bi-wrench',
    'Solicitations' => 'bi-envelope',
    'Lupon' => 'bi-hourglass-split',
    'Others' => 'bi-plus-lg'
    ];

    $issuanceCounts = [];
    foreach ($issuanceTypes as $type) {
        $issuanceCounts[$type] = $issuanceCollection->countDocuments([
            'status' => 'Pending',  
            'document_type' => $type
        ]);
    }

} catch (Exception $e) {
    die("Error connecting to MongoDB: " . $e->getMessage());
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
                <h3>Anonymous 1</h3>
                <small>admin@email.com</small>
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
                    <i class="bi bi-file-earmark-text"></i> Records
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
                <img src="../../assets/img/barangaygusalogo.png">
                <img src="../../assets/img/cdologo.png">
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
                                <div class="label"><?= $type ?></div>
                                <div class="count"><?= $issuanceCounts[$type] ?? 0 ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </section>

                <section class="right-column">

                    <!-- Calendar -->
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

    <script src="../../assets/js/calendar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    function showToast(message, type='success', timeout=2500) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast show ${type}`;
        toast.innerHTML = `<div class="toast-body">${message}</div>`;
        container.appendChild(toast);
        setTimeout(()=>{ toast.remove() }, timeout);
    }

    fetch("../../backend/announcement_get_dashboard.php")
        .then(res => res.json())
        .then(data => {
            let timelineHTML = "";

            data.forEach(event => {
                timelineHTML += `
                    <div class="timeline-event mb-3">
                        <strong class="event-title">${event.title}</strong><br>
                        <span class="event-location"><i class="bi bi-geo-alt-fill me-1"></i>${event.location}</span><br>
                        <span class="event-datetime"><i class="bi bi-calendar-event me-1"></i>${event.time} | ${event.date}</span>
                    </div>
                `;
            });

            document.getElementById("timeline-events").innerHTML =
                timelineHTML || "<p>No upcoming announcements.</p>";
        });

    </script>
    <div id="toast" class="toast"></div>

    <script>
    function showToast(message, type = "error") {
        const t = document.getElementById("toast");

        // Reset classes (VERY IMPORTANT)
        t.className = "toast";
        t.textContent = message;
        t.classList.add(type);
        t.classList.add("show");

        setTimeout(() => {
            t.classList.remove("show");
        }, 3000);
    }

    document.querySelectorAll('.dropdown-btn').forEach(btn => {
            btn.addEventListener('click', function(){
                this.parentElement.classList.toggle('active');
            });
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_SESSION['toast'])): ?>
    <script>
        showToast("<?= $_SESSION['toast']['msg'] ?>", "<?= $_SESSION['toast']['type'] ?>");
    </script>
    <?php unset($_SESSION['toast'], $_SESSION['toast_type']); endif; ?>

    </body>
    </html>