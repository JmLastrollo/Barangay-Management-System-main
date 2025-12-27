<?php
session_start();
require_once 'backend/config.php';
?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/cdologo.png" class="left-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>Calendar</h3>
    </div>
    <img src="assets/img/barangaygusalogo.png" class="right-logo" alt="right logo">
</section>

<section class="calendar-container container py-5">

    <div class="row">

        <div class="col-lg-7 col-md-12 mb-5">

            <h3 class="fw-bold mb-3">
                BARANGAY <span style="color:#3cbf4c;">CALENDAR</span>
                <span style="color:#8dc63f;">2025</span>
            </h3>

            <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                <button id="prev-month" class="nav-btn">&lt;</button>
                <h2 id="month-year" class="fw-bold"></h2>
                <button id="next-month" class="nav-btn">&gt;</button>
            </div>

            <div class="calendar-grid" id="calendar-grid"></div>

        </div>

        <div class="col-lg-5 col-md-12">
            <h3 class="fw-bold mb-3">TIMELINE OF <span style="color:#3cbf4c;">EVENTS</span></h3>

            <article class="timeline" id="timeline-events" style="margin-top:30px">
                    <p>Loading events...</p>
            </article>
        </div>

    </div>

</section>

<?php include('includes/footer.php'); ?>

<script src="assets/js/calendar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let timelinePath;
if (window.location.pathname.includes("/pages/resident/") || 
    window.location.pathname.includes("/pages/admin/")) {
    timelinePath = "../../backend/announcement_get_dashboard.php";
} else {
    timelinePath = "backend/announcement_get_dashboard.php";
}

fetch(timelinePath)
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
</body>
</html>