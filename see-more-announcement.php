<?php
require_once "backend/config.php";

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid announcement ID.");
}

// Get announcement by ID
$id = $_GET['id'];
$announcement = $announcementCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

if (!$announcement) {
    die("Announcement not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title><?= htmlspecialchars($announcement->title) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="icon" type="image/png" href="assets/img/BMS.png">
<link rel="stylesheet" href="css/style.css?v=2" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/cdologo.png" class="left-logo" alt="left logo">
    <div class="header-text">
        <h1>Announcement</h1> 
        <h3>Article</h3>
    </div>
    <img src="assets/img/barangaygusalogo.png" class="right-logo" alt="right logo">
</section>

<section class="see-more-announcement-container">

  <!-- Back Button Outside Card -->
  <a href="announcement.php" class="see-more-announcement-button">
  <i class="bi bi-arrow-left"></i> Back to Announcements
    </a>

  <div class="see-more-card shadow-sm">
    <div class="card-body">

      <!-- Title -->
      <h2 class="card-title see-more-announcement-title"><?= htmlspecialchars($announcement->title) ?></h2>

      <!-- Date & Location -->
      <p class="card-subtitle mb-2 text-muted see-more-announcement-date">
        <i class="bi bi-calendar"></i> <?= date("F d, Y", strtotime($announcement->date)) ?>
        <?php if (!empty($announcement->location)): ?>
            &nbsp; | &nbsp; <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($announcement->location) ?>
        <?php endif; ?>
      </p>

      <!-- Badges -->
      <div class="see-more-announcement-badges mb-3">
        <?php if (!empty($announcement->tags)): ?>
          <?php foreach($announcement->tags as $tag): ?>
            <span class="badge bg-info"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Images -->
        <?php if (!empty($announcement->image) || (!empty($announcement->images) && is_array($announcement->images))): ?>
        <div class="see-more-announcement-images mb-3 text-center">
            <?php if (!empty($announcement->image)): ?>
            <img src="uploads/announcements/<?= $announcement->image ?>" class="mx-auto d-block" alt="Announcement Image">
            <?php endif; ?>
            <?php if (!empty($announcement->images) && is_array($announcement->images)):
            foreach ($announcement->images as $img): ?>
                <img src="uploads/announcements/<?= $img ?>" class="mx-auto d-block mb-2" alt="Announcement Image">
            <?php endforeach; endif; ?>
        </div>
        <?php endif; ?>



      <!-- Full Details -->
      <p class="card-text see-more-announcement-text"><?= nl2br(htmlspecialchars($announcement->details)) ?></p>

    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
