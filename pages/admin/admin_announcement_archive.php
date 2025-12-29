<?php 
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../resident_login.php");
    exit();
}

require_once '../../backend/db_connect.php'; 

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";
$params = [];

// Base Query: announcements table, status is lowercase 'archived'
$sql = "SELECT * FROM announcements WHERE status = 'archived'";

if (!empty($searchQuery)) {
    $sql .= " AND (title LIKE :search OR details LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY date DESC, time DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $announcements = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Announcements</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="">
        <div>
            <h3>Anonymous 1</h3>
            <small>admin@email.com</small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <div class="dropdown-container active">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Archives
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content" style="display: block;">
                <a href="admin_announcement_archive.php" class="active"><i class="bi bi-megaphone"></i> Announcement</a>
                <a href="admin_officials_archive.php"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>
            </div>
        </div>
        <a href="admin_announcement.php"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">ANNOUNCEMENTS</span></h1>
    </div>

    <div class="content">
        <form method="GET" class="search-box d-flex mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button class="search-btn ms-2"><i class="bi bi-search"></i></button>
        </form>

        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No archived announcements found.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $item): ?>
                    <tr>
                        <td>
                            <?php $imgSrc = !empty($item['image']) ? "../../uploads/announcements/" . $item['image'] : "../../assets/img/announcement_placeholder.png"; ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:100px;height:60px;object-fit:cover;border-radius:5px;">
                        </td>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= strlen($item['details']) > 50 ? htmlspecialchars(substr($item['details'], 0, 50)) . '...' : htmlspecialchars($item['details']) ?></td>
                        <td><?= htmlspecialchars($item['date']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info text-white" onclick='openViewModal(<?= json_encode($item) ?>)'><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-success" onclick='openRestoreModal("<?= $item['announcement_id'] ?>")'><i class="bi bi-arrow-counterclockwise"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content p-3">
    <h4 id="v_title"></h4>
    <p id="v_details"></p>
    <div class="text-end"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
  </div></div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content p-3">
    <h4>Restore Announcement</h4>
    <p>Are you sure?</p>
    <form action="../../backend/announcement_update.php" method="POST">
        <input type="hidden" name="id" id="r_id">
        <input type="hidden" name="status" value="active"> 
        <div class="text-end">
            <button class="btn btn-success" type="submit">Restore</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
function openViewModal(data) {
    document.getElementById('v_title').innerText = data.title;
    document.getElementById('v_details').innerText = data.details;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}
function openRestoreModal(id) {
    document.getElementById('r_id').value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}
// Dropdown Logic
document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        this.parentElement.classList.toggle('active');
        let content = this.nextElementSibling;
        content.style.display = content.style.display === "block" ? "none" : "block";
    });
});
</script>
</body>
</html>