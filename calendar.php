<?php
session_start();
require_once 'backend/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Calendar</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
    
    <style>
        /* Custom Timeline Styles */
        .timeline-event {
            border-left: 4px solid #3cbf4c;
            padding-left: 20px;
            margin-bottom: 25px;
            position: relative;
        }
        .timeline-event::before {
            content: '';
            width: 12px;
            height: 12px;
            background: #3cbf4c;
            border-radius: 50%;
            position: absolute;
            left: -8px;
            top: 5px;
            border: 2px solid white;
            box-shadow: 0 0 0 1px #3cbf4c;
        }
        .event-title {
            font-size: 1.1rem;
            color: #2c3e50;
            display: block;
            margin-bottom: 5px;
        }
        .header-banner {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }
        /* Calendar Specifics */
        .calendar-nav .btn {
            width: 40px;
            height: 40px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        /* Grid Layout */
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px; /* Agwat sa pagitan ng mga araw */
        margin-top: 20px;
    }

    /* Style ng Bawat Araw */
    .calendar-day {
        height: 45px; /* Taas ng bilog/kahon */
        width: 45px;  /* Lapad para maging pantay */
        margin: 0 auto; /* Center sa loob ng grid cell */
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 50%; /* Gawing bilog */
        position: relative; /* Importante para sa position ng dot */
        transition: background 0.2s, color 0.2s;
        font-size: 0.9rem;
    }

    .calendar-day:hover {
        background-color: #e9ecef; /* Hover effect */
    }

    /* 1. HIGHLIGHT PARA SA PRESENT / TODAY */
    .calendar-day.active {
        background-color: #3cbf4c; /* Kulay Green ng Barangay */
        color: white;
        font-weight: bold;
        box-shadow: 0 3px 6px rgba(60, 191, 76, 0.4);
    }

    /* 2. DOT PARA SA EVENT */
    .calendar-day.event::after {
        content: '';
        width: 6px;
        height: 6px;
        background-color: #dc3545; /* Pulang tuldok para mapansin agad (o gawing orange) */
        border-radius: 50%;
        position: absolute;
        bottom: 4px; /* Ilagay sa ilalim ng numero */
    }

    /* Kung ang present day ay may event din, gawing puti ang tuldok para makita sa green background */
    .calendar-day.active.event::after {
        background-color: white;
    }
    </style>
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner text-center py-5">
    <div class="container d-flex justify-content-center align-items-center gap-3 gap-md-5">
        <img src="assets/img/dasma logo-modified.png" class="img-fluid" style="height: 80px;" alt="logo left">
        <div class="header-text">
            <h1 class="fw-bold m-0 text-uppercase" style="letter-spacing: 2px;">Barangay Calendar</h1> 
            <h5 class="text-muted m-0">Schedule of Events & Activities</h5>
        </div>
        <img src="assets/img/Langkaan 2 Logo-modified.png" class="img-fluid" style="height: 80px;" alt="logo right">
    </div>
</section>

<section class="calendar-container container py-5">
    <div class="row g-5">
        
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0 text-success">
                            <i class="bi bi-calendar3 me-2"></i><span id="month-year"></span>
                        </h3>
                        <div class="calendar-nav">
                            <button id="prev-month" class="btn btn-outline-success rounded-circle me-1"><i class="bi bi-chevron-left"></i></button>
                            <button id="next-month" class="btn btn-outline-success rounded-circle"><i class="bi bi-chevron-right"></i></button>
                        </div>
                    </div>
                    
                    <div class="calendar-grid" id="calendar-grid"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4 pb-2 border-bottom">
                        UPCOMING <span class="text-success">EVENTS</span>
                    </h4>

                    <div id="timeline-events">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border text-success" role="status"></div>
                            <p class="mt-2 small">Loading events...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<button onclick="topFunction()" id="backToTop" class="btn btn-success rounded-circle shadow" title="Go to top" 
        style="display: none; position: fixed; bottom: 30px; right: 30px; z-index: 1000; width: 50px; height: 50px;">
    <i class="bi bi-arrow-up fs-5"></i>
</button>

<?php include('includes/footer.php'); ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/calendar.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetchEvents();
    });

    function fetchEvents() {
        // Direct path since this file is in root
        const timelinePath = "backend/announcement_get_dashboard.php";

        fetch(timelinePath)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                let timelineHTML = "";

                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(event => {
                        // Format Date and Time
                        const dateObj = new Date(event.date + 'T' + event.time);
                        const dateStr = dateObj.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
                        const timeStr = dateObj.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

                        timelineHTML += `
                            <div class="timeline-event">
                                <strong class="event-title">${event.title}</strong>
                                <div class="text-muted small mb-1">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> ${event.location}
                                </div>
                                <div class="text-success fw-bold small">
                                    <i class="bi bi-clock-fill me-1"></i> ${dateStr} • ${timeStr}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    timelineHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-3 opacity-25"></i>
                            <p>No upcoming events at the moment.</p>
                        </div>
                    `;
                }

                document.getElementById("timeline-events").innerHTML = timelineHTML;
            })
            .catch(err => {
                console.error("Error loading events:", err);
                document.getElementById("timeline-events").innerHTML = `
                    <div class="alert alert-warning border-0 small">
                        <i class="bi bi-exclamation-triangle me-2"></i> Failed to load events list.
                    </div>
                `;
            });
    }

    // Scroll to Top Logic
    const mybutton = document.getElementById("backToTop");
    window.onscroll = function() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    };

    function topFunction() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>