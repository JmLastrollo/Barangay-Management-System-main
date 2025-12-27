<?php
session_start();
require_once 'backend/config.php';

$announcementFilter = ['status' => 'active'];

$announcements = $announcementCollection->find(
    $announcementFilter,
    [
        'sort' => [
            'date' => -1,
            'time' => -1
        ]
    ]
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Announcements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css?v=1" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/cdologo.png" class="left-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>Announcements</h3>
    </div>
    <img src="assets/img/barangaygusalogo.png" class="right-logo" alt="right logo">
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <?php foreach ($announcements as $item): 
                $modalId = "seeMoreAnnouncement" . (string)$item->_id;
            ?>
            <div class="col-md-4 d-flex">
                <div class="card announcement-page-card p-3 h-100 w-100 d-flex flex-column">

                    <!-- Image -->
                    <?php if (!empty($item->image)): ?>
                        <img src="uploads/announcements/<?= $item->image ?>" class="mb-3 w-100 announcement-page-img" />
                    <?php else: ?>
                        <img src="assets/img/announcement_placeholder.png" class="mb-3 w-100 announcement-page-img" />
                    <?php endif; ?>

                    <!-- TEXT CONTENT -->
                    <div class="d-flex flex-column flex-grow-1 text-start">

                        <!-- Date + Time -->
                        <span class="badge bg-success mb-2 announcement-page-date">
                            <?= date("d M Y", strtotime($item->date)) ?> 
                            <?= !empty($item->time) ? " | " . date("h:i A", strtotime($item->time)) : "" ?>
                        </span>

                        <!-- Location -->
                        <?php if (!empty($item->location)): ?>
                        <div class="mb-2 text-secondary announcement-page-location">
                            <i class="bi bi-geo-alt-fill"></i>
                            <?= htmlspecialchars($item->location) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Title -->
                        <h6 class="fw-bold announcement-page-title mt-3">
                            <?= htmlspecialchars($item->title) ?>
                        </h6>

                        <!-- Details -->
                        <p class="announcement-page-details flex-grow-1">
                            <?= strlen($item->details) > 80 ? substr($item->details, 0, 80) . "..." : htmlspecialchars($item->details) ?>
                        </p>

                        <!-- See More Button links to separate page -->
                        <a href="see-more-announcement.php?id=<?= $item->_id ?>" class="text-success mt-auto">
                            See More
                        </a>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
