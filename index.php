<?php
session_start();
// Siguraduhing ito ang file na naglalaman ng $conn (PDO connection)
require_once 'backend/config.php'; 

$announcements = [];

try {
    // MySQL Query: Kunin ang mga active announcements, pinakabago muna, limit 3
    $sql = "SELECT * FROM announcements WHERE is_active = 1 ORDER BY date_posted DESC LIMIT 3";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    // Kunin ang results bilang Objects
    $announcements = $stmt->fetchAll(PDO::FETCH_OBJ);

} catch (PDOException $e) {
    error_log("Error fetching announcements: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Brgy. Langkaan II</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css?v=1" />
    <link rel="stylesheet" href="css/toast.css">
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="hero">
    <h5>WELCOME TO</h5>
    <h1 class="fw-bold">BARANGAY <span class="text-success">LANGKAAN II</span></h1>
    <p>Dasmariñas City, Cavite</p>
    <div class="mt-3">
        <a href="contact.php" class="btn btn-light me-2">Contact Us</a>
        
        <?php if (isset($_SESSION['email'])): ?>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'Staff')): ?>
                 <a href="pages/admin/admin_dashboard.php" class="btn btn-success">Admin Dashboard</a>
            <?php else: ?>
                 <a href="pages/resident/resident_dashboard.php" class="btn btn-success">My Account</a>
            <?php endif; ?>
        <?php else: ?>
            <a href="resident_login.php" class="btn btn-success">
                Login Now
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 text-center">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-custom p-4">
                    <img src="assets/img/officials.png" class="mx-auto mb-3" width="150" alt="Officials" />
                    <h5>Barangay Officials</h5>
                    <a href="officials.php" class="btn btn-success mt-3">Learn More</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-4">
                    <img src="assets/img/announcements.png" class="mx-auto mb-3" width="150" alt="Announcements" />
                    <h5>Announcements</h5>
                    <a href="announcement.php" class="btn btn-success mt-3">Learn More</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom p-4">
                    <img src="assets/img/issuance.png" class="mx-auto mb-3" width="150" alt="Issuance" />
                    <h5>Issuance</h5>
                    <a href="issuance.php" class="btn btn-success mt-3">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Recent <span class="text-announcement">Announcements</span></h3>

        <div class="row g-4">
            <?php if (count($announcements) > 0): ?>
                <?php foreach ($announcements as $item): ?>
                <div class="col-md-4 d-flex">
                    <div class="card home-announce-card p-3 d-flex flex-column h-100 w-100">

                        <?php 
                            $imgSource = !empty($item->image_path) ? "uploads/announcements/" . $item->image_path : "assets/img/announcement_placeholder.png";
                        ?>
                        <img src="<?= $imgSource ?>" class="mb-3 w-100 home-announce-img" style="height: 200px; object-fit: cover;" alt="Announcement Image" />

                        <div class="d-flex flex-column flex-grow-1 text-start">

                            <span class="badge bg-success mb-2 home-date">
                                <?= date("d M Y", strtotime($item->date_posted)) ?>
                                <?= date("h:i A", strtotime($item->date_posted)) ?>
                            </span>

                            <?php if (!empty($item->location)): ?>
                                <div class="mb-2 text-secondary home-location">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <?= htmlspecialchars($item->location) ?>
                                </div>
                            <?php endif; ?>

                            <h6 class="fw-bold mt-2 home-announce-title">
                                <?= htmlspecialchars($item->title) ?>
                            </h6>

                            <p class="home-announce-details flex-grow-1">
                                <?php 
                                    $details = $item->content ?? $item->details ?? ''; 
                                    echo strlen($details) > 80 ? substr($details, 0, 80) . "..." : htmlspecialchars($details);
                                ?>
                            </p>

                            <a href="see-more-announcement.php?id=<?= $item->announcement_id ?>" class="text-success mt-auto">
                                See More
                            </a>

                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No announcements found.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="text-center mt-4">
            <a href="announcement.php" class="btn btn-success">View All Announcements</a>
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

<div id="toast" class="toast"></div>

<script>
function showToast(message, type = "error") {
    const t = document.getElementById("toast");
    t.className = "toast"; 
    t.textContent = message;
    t.classList.add(type);
    t.classList.add("show");
    setTimeout(() => {
        t.classList.remove("show");
    }, 3000);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    <?php 
        $msg = is_array($_SESSION['toast']) ? $_SESSION['toast']['msg'] : $_SESSION['toast'];
        $type = is_array($_SESSION['toast']) ? $_SESSION['toast']['type'] : ($_SESSION['toast_type'] ?? 'info');
    ?>
    showToast("<?= htmlspecialchars($msg) ?>", "<?= htmlspecialchars($type) ?>");
</script>
<?php 
    unset($_SESSION['toast']);
    unset($_SESSION['toast_type']);
endif; 
?>

</body>
</html>