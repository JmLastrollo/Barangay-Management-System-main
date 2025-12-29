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
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/cdologo.png" class="banner-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>OFFICIALS</h3>
    </div>
    <img src="assets/img/barangaygusalogo.png" class="banner-logo" alt="right logo">
</section>

<section class="py-5 bg-light main-content-section">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">Elected <span class="text-success">Officials</span></h3>
        
        <div class="row g-4 justify-content-center">
            <?php if (count($officials) > 0): ?>
                <?php foreach ($officials as $off): ?>
                    <div class="col-md-6 col-lg-3 d-flex">
                        <div class="card official-card h-100 w-100">
                            <div class="card-body d-flex flex-column align-items-center text-center p-4">
                                <div class="official-img-wrapper mb-3">
                                    <?php 
                                        $imgSrc = !empty($off->image) ? "uploads/officials/" . $off->image : "assets/img/profile_placeholder.png";
                                    ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="official-img" alt="Official">
                                </div>
                                <h5 class="official-name mb-1"><?= htmlspecialchars($off->full_name) ?></h5>
                                <p class="official-position mb-0 small"><?= htmlspecialchars($off->position) ?></p>
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

<?php include('includes/footer.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>