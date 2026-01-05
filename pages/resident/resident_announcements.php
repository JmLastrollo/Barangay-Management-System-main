<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

// 1. HANDLE SORTING LOGIC
$sort = $_GET['sort'] ?? 'newest'; // Default is 'newest'
$order = ($sort === 'oldest') ? 'ASC' : 'DESC'; // ASC = Oldest, DESC = Newest

// 2. FETCH ANNOUNCEMENTS
try {
    // Ginagamit ang $order variable sa SQL query
    $sql = "SELECT * FROM announcements WHERE status = 'Active' ORDER BY date $order, time $order";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $announcements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Announcements - BMS</title>
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
            <h1 class="header-title">BARANGAY <span class="green">UPDATES</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                
                <div>
                    <h3 class="fw-bold text-dark m-0"><i class="bi bi-megaphone-fill me-2 text-primary"></i>Announcements</h3>
                    <p class="text-muted small m-0">Stay updated with the latest news and events.</p>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <form method="GET" class="d-flex align-items-center">
                        <label class="me-2 text-muted small fw-bold text-uppercase" style="font-size: 0.75rem;">Sort By:</label>
                        <select name="sort" class="form-select form-select-sm rounded-pill border-secondary" onchange="this.form.submit()" style="width: 140px; font-weight: 600;">
                            <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Newest First</option>
                            <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Oldest First</option>
                        </select>
                    </form>

                    <span class="badge bg-primary rounded-pill px-3 py-2">Total: <?= count($announcements) ?></span>
                </div>
            </div>

            <?php if(empty($announcements)): ?>
                <div class="alert alert-light text-center py-5 shadow-sm border-0" role="alert">
                    <i class="bi bi-inbox-fill text-muted" style="font-size: 3rem;"></i>
                    <p class="mt-3 text-muted fw-bold">No announcements found.</p>
                </div>
            <?php else: ?>
                
                <div class="row g-4">
                    <?php foreach ($announcements as $ann): 
                        $imgSrc = !empty($ann['image']) ? "../../uploads/announcements/" . $ann['image'] : "../../assets/img/announcement_placeholder.png";
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="news-card">
                            <div class="news-img-container">
                                <img src="<?= htmlspecialchars($imgSrc) ?>" class="news-img" alt="Announcement">
                                <div class="news-date-badge">
                                    <?= date('M d, Y', strtotime($ann['date'])) ?>
                                </div>
                            </div>
                            <div class="news-body">
                                <h5 class="news-title"><?= htmlspecialchars($ann['title']) ?></h5>
                                <p class="news-excerpt">
                                    <?= htmlspecialchars(substr($ann['details'], 0, 100)) ?>...
                                </p>
                                <a href="resident_view_announcement.php?id=<?= $ann['announcement_id'] ?>" class="btn btn-outline-primary w-100 rounded-pill mt-auto fw-bold">
                                    Read More <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

        </div>

        <?php include '../../includes/resident_footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>