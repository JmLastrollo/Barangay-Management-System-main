<?php
session_start();
require_once 'backend/db_connect.php';

try {
    // FIX: Changed 'id' to 'official_id' and 'active' to 'Active'
    $sql = "SELECT * FROM barangay_officials WHERE status = 'Active' ORDER BY official_id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $officials = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $officials = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Officials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/BMS.png">
    <link rel="stylesheet" href="css/style.css?v=7" />

    <style>
        /* Pinapalitan nito ang default na bilog na style */
        .official-img-wrapper {
            width: 100% !important;         /* Sakupin ang buong width ng card padding */
            max-width: 100% !important;     /* Tanggalin ang limit sa lapad */
            height: 350px !important;       /* Taasan ang height para maging Portrait/Half-body */
            border-radius: 8px !important;  /* Gawing rounded rectangle imbis na bilog */
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Optional: konting shadow para umangat */
        }

        .official-img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;       /* Siguraduhing napupuno ang box nang hindi nai-stretch */
            object-position: top center !important; /* FOCUS SA TAAS: Para laging kita ang mukha/ulo */
        }

        /* Optional: Ayusin ang card height */
        .official-card {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .official-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="banner-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>Officials</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="banner-logo" alt="right logo">
</section>

<section class="py-5 bg-light main-content-section">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">Elected <span class="text-success">Officials</span></h3>
        
        <div class="row g-2 justify-content-center">
            <?php if (count($officials) > 0): ?>
                <?php foreach ($officials as $off): ?>
                    <div class="col-md-6 col-lg-3 d-flex">
                        <div class="card official-card h-100 w-100">
                            <div class="card-body d-flex flex-column align-items-center text-center p-3">
                                
                                <div class="official-img-wrapper">
                                    <?php 
                                        $imgSrc = !empty($off->image) ? "uploads/officials/" . $off->image : "assets/img/profile_placeholder.png";
                                    ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="official-img" alt="Official">
                                </div>

                                <h5 class="official-name fw-bold mb-1"><?= htmlspecialchars($off->full_name) ?></h5>
                                <p class="official-position text-success mb-0 small text-uppercase fw-bold"><?= htmlspecialchars($off->position) ?></p>
                                
                                <?php if(!empty($off->committee)): ?>
                                    <small class="text-muted mt-2 fst-italic"><?= htmlspecialchars($off->committee) ?></small>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h5 class="text-muted">No officials data found.</h5>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<button onclick="topFunction()" id="backToTop" title="Go to top">
    <i class="bi bi-arrow-up"></i>
</button>
<?php include('includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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