<?php
session_start();
require_once 'backend/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Contact Us</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css?v=2" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<?php include 'includes/nav.php'; ?>

    <section class="header-banner">
        <img src="assets/img/dasma logo-modified.png" class="left-logo" alt="LGU Logo">
        
        <div class="header-text">
            <h1>Barangay</h1> 
            <h3>Contact Us</h3>
        </div>
        
        <img src="assets/img/Langkaan 2 Logo-modified.png" class="right-logo" alt="Barangay Logo">
    </section>

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <div class="card shadow-sm border-0 mb-5 text-center">
                    <div class="card-body p-4">
                        <h2 class="fw-bold text-primary mb-4">GET IN TOUCH</h2>
                        
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <i class="bi bi-geo-alt-fill fs-2 text-warning mb-2"></i>
                                    <h6 class="fw-bold">Address</h6>
                                    <p class="small text-muted mb-0">Barangay Langkaan II, Dasmariñas City, Cavite</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <i class="bi bi-envelope-fill fs-2 text-warning mb-2"></i>
                                    <h6 class="fw-bold">Email</h6>
                                    <p class="small text-muted mb-0">info@langkaan2.gov.ph</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <i class="bi bi-telephone-fill fs-2 text-warning mb-2"></i>
                                    <h6 class="fw-bold">Phone</h6>
                                    <p class="small text-muted mb-0">0909-000-0000</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-form mt-0 p-4 p-md-5">
                    <h4 class="text-center fw-bold mb-4">Send Us a Message / Complaint</h4>
                    
                    <form action="backend/complaint_process.php" method="POST">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Full Name</label>
                                <input type="text" class="form-control" name="fullname" placeholder="Your Full Name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-muted">Email Address</label>
                                <input type="email" class="form-control" name="email" placeholder="Your Email" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Subject</label>
                                <input type="text" class="form-control" name="subject" placeholder="Subject of your concern" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Message / Complaint</label>
                                <textarea class="form-control" name="message" rows="6" placeholder="Type your complaint or concern here..." required></textarea>
                            </div>
                        </div>

                        <div class="alert alert-info mt-4 d-flex align-items-center" role="alert">
                            <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                            <div class="small">
                                Please be informed that your information will be handled confidentially. We may contact you via email regarding your concern.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold mt-2">SEND MESSAGE</button>
                    </form>

                    <div class="text-center mt-4">
                        <a href="index.php" class="text-decoration-none fw-bold text-muted small">
                            <i class="bi bi-arrow-left me-1"></i> Back to Homepage
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <button onclick="topFunction()" id="backToTop" title="Go to top">
        <i class="bi bi-arrow-up"></i>
    </button>
    <?php include('includes/footer.php'); ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>
<script>
    // Get the button
    let mybutton = document.getElementById("backToTop");

    // Listen to scroll event
    window.onscroll = function() { scrollFunction() };

    function scrollFunction() {
        // Show button if scrolled down 300px
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // Scroll to top when clicked
    function topFunction() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
</body>
</html>