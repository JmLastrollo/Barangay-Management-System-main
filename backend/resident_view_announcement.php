<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

// Check ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: resident_announcements.php");
    exit();
}

$id = $_GET['id'];

try {
    // Get Announcement Details
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE announcement_id = :id");
    $stmt->execute([':id' => $id]);
    $announcement = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$announcement) {
        die("Announcement not found.");
    }

    // Get Recent (Sidebar)
    $stmtSide = $conn->prepare("SELECT * FROM announcements WHERE status = 'Active' AND announcement_id != :id ORDER BY date DESC LIMIT 5");
    $stmtSide->execute([':id' => $id]);
    $recents = $stmtSide->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($announcement['title']) ?> - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">ANNOUNCEMENT <span class="green">DETAILS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <a href="resident_announcements.php" class="text-decoration-none text-muted fw-bold mb-4 d-inline-block hover-link">
                <i class="bi bi-arrow-left me-1"></i> Back to Announcements
            </a>

            <div class="row g-4">
                
                <div class="col-lg-8">
                    <div class="announcement-detail-card p-4 h-100">
                        
                        <h2 class="fw-bold text-dark mb-3"><?= htmlspecialchars($announcement['title']) ?></h2>
                        
                        <div class="meta-info">
                            <div class="meta-item">
                                <i class="bi bi-calendar-event"></i> <?= date("F d, Y", strtotime($announcement['date'])) ?>
                            </div>
                            <?php if (!empty($announcement['time'])): ?>
                            <div class="meta-item">
                                <i class="bi bi-clock"></i> <?= date("h:i A", strtotime($announcement['time'])) ?>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($announcement['location'])): ?>
                            <div class="meta-item">
                                <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($announcement['location']) ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($announcement['image'])): ?>
                            <img src="../../uploads/announcements/<?= htmlspecialchars($announcement['image']) ?>" class="detail-hero-img shadow-sm" alt="Event Image">
                        <?php endif; ?>

                        <div class="detail-content">
                            <?= nl2br(htmlspecialchars($announcement['details'])) ?>
                        </div>

                        <?php if (!empty($announcement['location'])): ?>
                        <div class="mt-5 pt-4 border-top">
                            <h5 class="fw-bold mb-3"><i class="bi bi-map me-2 text-primary"></i>Event Location</h5>
                            <div class="ratio ratio-21x9 shadow-sm rounded overflow-hidden">
                                <iframe 
                                    src="https://maps.google.com/maps?q=<?= urlencode($announcement['location']) ?>&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                    allowfullscreen>
                                </iframe>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bg-white rounded-4 shadow-sm p-4 sticky-top" style="top: 110px; z-index: 1;">
                        <h5 class="fw-bold mb-4">Other Recent Updates</h5>
                        
                        <?php if(empty($recents)): ?>
                            <p class="text-muted small">No other announcements.</p>
                        <?php else: ?>
                            <?php foreach($recents as $recent): 
                                $thumb = !empty($recent['image']) ? "../../uploads/announcements/" . $recent['image'] : "../../assets/img/announcement_placeholder.png";
                            ?>
                            <a href="resident_view_announcement.php?id=<?= $recent['announcement_id'] ?>" class="recent-list-item">
                                <img src="<?= $thumb ?>" class="recent-thumb" alt="Thumb">
                                <div class="recent-details">
                                    <h6><?= htmlspecialchars($recent['title']) ?></h6>
                                    <span><i class="bi bi-calendar3 me-1"></i> <?= date("M d, Y", strtotime($recent['date'])) ?></span>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="mt-4 pt-3 border-top text-center">
                            <a href="resident_announcements.php" class="btn btn-light btn-sm w-100 fw-bold text-primary">View All</a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <?php include '../../includes/resident_footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>