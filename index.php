<?php
session_start();
// Ensure DB connection is included
require_once "backend/db_connect.php"; 

try {
    // Select active announcements, sort by newest date
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE status = 'active' ORDER BY date DESC, time DESC LIMIT 3");
    $stmt->execute();
    
    // IMPORTANT: Fetch as OBJECT so $item->title works
    $announcements = $stmt->fetchAll(PDO::FETCH_OBJ);
    
} catch (PDOException $e) {
    // If error, set empty array to prevent crash
    $announcements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Brgy. Langkaan II</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css?v=6" /> 
    <link rel="stylesheet" href="css/toast.css">
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="hero">
    <h5>WELCOME TO</h5>
    <h1 class="fw-bold">BARANGAY <span class="title">LANGKAAN II</span></h1>
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
            <a href="login.php" class="btn btn-success">Login Now</a>
        <?php endif; ?>
    </div>
</section>

<section class="py-5 text-center">
    <div class="container">
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card card-custom h-100 p-4">
                    <img src="assets/img/officials.png" class="mx-auto mb-3" width="150" alt="Officials" />
                    <h5>Barangay Officials</h5>
                    <a href="officials.php" class="btn btn-success mt-auto">Learn More</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom h-100 p-4">
                    <img src="assets/img/announcements.png" class="mx-auto mb-3" width="150" alt="Announcements" />
                    <h5>Announcements</h5>
                    <a href="announcement.php" class="btn btn-success mt-auto">Learn More</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-custom h-100 p-4">
                    <img src="assets/img/issuance.png" class="mx-auto mb-3" width="150" alt="Issuance" />
                    <h5>Issuance</h5>
                    <a href="issuance.php" class="btn btn-success mt-auto">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4 text-center">Recent <span class="text-success">Announcements</span></h3>

        <div class="row g-4">
            <?php if (isset($announcements) && count($announcements) > 0): ?>
                <?php foreach ($announcements as $item): ?>
                <div class="col-md-4 d-flex">
                    <div class="card home-announce-card p-3 d-flex flex-column h-100 w-100 shadow-sm border-0">
                        <?php 
                            $imgSource = !empty($item->image) ? "uploads/announcements/" . $item->image : "assets/img";
                        ?>
                        <img src="<?= $imgSource ?>" class="mb-3 w-100 home-announce-img rounded" style="height: 200px; object-fit: cover;" alt="Announcement Image" />

                        <div class="d-flex flex-column flex-grow-1 text-start">
                            <span class="badge bg-warning text-dark mb-2 align-self-start">
                                <?php 
                                    $displayDate = !empty($item->date) ? date("M d, Y", strtotime($item->date)) : "No Date";
                                    echo $displayDate;
                                ?>
                            </span>

                            <h6 class="fw-bold mt-1 text-primary">
                                <?= htmlspecialchars($item->title) ?>
                            </h6>

                            <p class="text-muted small flex-grow-1">
                                <?php 
                                    $detailsText = $item->details ?? ''; 
                                    echo strlen($detailsText) > 100 ? substr($detailsText, 0, 100) . "..." : htmlspecialchars($detailsText);
                                ?>
                            </p>

                            <a href="see-more-announcement.php?id=<?= $item->announcement_id ?>" class="btn btn-outline-primary btn-sm mt-auto w-100 rounded-pill">
                                Read More
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

        <div class="text-center mt-5">
            <a href="announcement.php" class="btn btn-primary px-4 rounded-pill">View All Announcements</a>
        </div>
    </div>
</section>

<button onclick="topFunction()" id="backToTop" title="Go to top">
    <i class="bi bi-arrow-up"></i>
</button>

<?php include('includes/footer.php'); ?>

<div id="toast" class="toast"></div>

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