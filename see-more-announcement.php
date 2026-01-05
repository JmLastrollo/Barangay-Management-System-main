<?php
// 1. Connect to MySQL Database
require_once "backend/db_connect.php"; 

// 2. Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid announcement ID.");
}

$id = $_GET['id'];

try {
    // A. Main Query: Get the current announcement details
    $sql = "SELECT * FROM announcements WHERE announcement_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $id]);
    $announcement = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$announcement) {
        die("Announcement not found.");
    }

    // B. Sidebar Query: Get 5 other recent announcements (excluding the current one)
    $sideSql = "SELECT * FROM announcements WHERE status = 'active' AND announcement_id != :id ORDER BY date DESC, time DESC LIMIT 5";
    $sideStmt = $conn->prepare($sideSql);
    $sideStmt->execute([':id' => $id]);
    $recentAnnouncements = $sideStmt->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?= htmlspecialchars($announcement->title) ?></title>
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
<link rel="stylesheet" href="css/style.css?v=2" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="banner-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>ANNOUNCEMENT</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="banner-logo" alt="right logo">
</section>

<section class="see-more-announcement-container">
    
    <a href="announcement.php" class="text-decoration-none text-primary fw-bold mb-3 d-inline-block">
        <i class="bi bi-arrow-left"></i> Back to Announcements
    </a>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="see-more-card shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="fw-bold text-primary mb-3"><?= htmlspecialchars($announcement->title) ?></h2>

                    <p class="text-muted mb-4 border-bottom pb-3">
                        <i class="bi bi-calendar-event me-2"></i> 
                        <?= !empty($announcement->date) ? date("F d, Y", strtotime($announcement->date)) : "No Date" ?>
                        
                        <?php if (!empty($announcement->time)): ?>
                             &nbsp; | &nbsp; <i class="bi bi-clock me-2"></i> <?= htmlspecialchars($announcement->time) ?>
                        <?php endif; ?>

                        <?php if (!empty($announcement->location)): ?>
                             &nbsp; | &nbsp; <i class="bi bi-geo-alt-fill me-2"></i> <?= htmlspecialchars($announcement->location) ?>
                        <?php endif; ?>
                    </p>

                    <?php if (!empty($announcement->image)): ?>
                        <div class="text-center mb-4">
                            <img src="uploads/announcements/<?= htmlspecialchars($announcement->image) ?>" 
                                 class="img-fluid rounded shadow-sm" 
                                 style="max-height: 500px; width: 100%; object-fit: cover;" 
                                 alt="Announcement Image">
                        </div>
                    <?php endif; ?>

                    <div class="card-text" style="font-size: 1.1rem; line-height: 1.8; color: #333; text-align: justify;">
                        <?= nl2br(htmlspecialchars($announcement->details)) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="sidebar-card shadow-sm mb-4"> <div class="sidebar-header">
                    <i class="bi bi-megaphone-fill me-2"></i> Recent Announcements
                </div>
                
                <div class="d-flex flex-column">
                    <?php if (count($recentAnnouncements) > 0): ?>
                        <?php foreach ($recentAnnouncements as $recent): ?>
                            <a href="see-more-announcement.php?id=<?= $recent->announcement_id ?>" class="recent-item">
                                <?php 
                                    $thumbSrc = !empty($recent->image) ? "uploads/announcements/" . $recent->image : "assets/img/announcement_placeholder.png";
                                ?>
                                <img src="<?= htmlspecialchars($thumbSrc) ?>" class="recent-img" alt="Thumbnail">
                                <div class="recent-info">
                                    <h6><?= htmlspecialchars($recent->title) ?></h6>
                                    <small><i class="bi bi-calendar3"></i> <?= date("M d, Y", strtotime($recent->date)) ?></small>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <small>No other announcements found.</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="p-3 text-center border-top bg-light">
                    <a href="announcement.php" class="btn btn-sm btn-outline-primary w-100 rounded-pill">View All</a>
                </div>
            </div>

            <?php if (!empty($announcement->location)): ?>
            <div class="sidebar-card shadow-sm">
                <div class="sidebar-header">
                    <i class="bi bi-geo-alt-fill me-2"></i> Event Location
                </div>
                
                <div class="p-3">
                    <div class="map-container shadow-sm">
                        <iframe 
                            width="100%" 
                            height="400" 
                            frameborder="0" 
                            scrolling="no" 
                            marginheight="0" 
                            marginwidth="0" 
                            src="https://maps.google.com/maps?q=<?= urlencode($announcement->location) ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                        </iframe>
                    </div>
                    
                    <div class="mt-3 d-flex align-items-start text-muted">
                        <i class="bi bi-info-circle-fill me-2 mt-1 text-primary"></i>
                        <small><strong>Address:</strong> <?= htmlspecialchars($announcement->location) ?></small>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div> </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>