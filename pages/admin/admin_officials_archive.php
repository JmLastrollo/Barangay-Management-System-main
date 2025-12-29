<?php 
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../resident_login.php");
    exit();
}

require_once '../../backend/db_connect.php'; 

$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : "";
$params = [];

// Base Query: barangay_officials, status is 'Archived' (Capital A based on your delete script)
// Note: Even if DB active is 'Active', your delete script sets it to 'Archived'.
$sql = "SELECT * FROM barangay_officials WHERE status = 'Archived'";

if (!empty($searchQuery)) {
    $sql .= " AND (full_name LIKE :search OR position LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY official_id DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $officials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $officials = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>BMS - Archived Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="">
        <div><h3>Anonymous 1</h3><small>admin@email.com</small></div>
    </div>
    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <div class="dropdown-container active">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Archives <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content" style="display: block;">
                <a href="admin_announcement_archive.php"><i class="bi bi-megaphone"></i> Announcement</a>
                <a href="admin_officials_archive.php" class="active"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>
            </div>
        </div>
        <a href="admin_officials.php"><i class="bi bi-arrow-left"></i> Back to Officials</a>
    </div>
</div>

<div style="width:100%">
    <div class="header"><h1 class="header-title">ARCHIVED <span class="green">OFFICIALS</span></h1></div>
    <div class="content">
        <form method="GET" class="search-box d-flex mb-3">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button class="search-btn ms-2"><i class="bi bi-search"></i></button>
        </form>

        <table class="table table-striped align-middle">
            <thead>
                <tr><th>Image</th><th>Name</th><th>Position</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php if (count($officials) > 0): ?>
                    <?php foreach ($officials as $item): ?>
                    <tr>
                        <td>
                            <?php $imgSrc = !empty($item['image']) ? "../../uploads/officials/" . $item['image'] : "../../assets/img/profile_placeholder.png"; ?>
                            <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:5px;">
                        </td>
                        <td><?= htmlspecialchars($item['full_name']) ?></td>
                        <td><?= htmlspecialchars($item['position']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info text-white" onclick="openViewModal('<?= htmlspecialchars($item['full_name']) ?>', '<?= htmlspecialchars($item['position']) ?>', '<?= htmlspecialchars($imgSrc) ?>')"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-sm btn-success" onclick="openRestoreModal('<?= $item['official_id'] ?>')"><i class="bi bi-arrow-counterclockwise"></i> Restore</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center text-muted">No archived officials found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content p-3"><h4 id="v_name"></h4><p id="v_position"></p><img id="v_image" src="" style="max-height:300px;"><div class="text-end"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div></div></div></div>

<div class="modal fade" id="restoreModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content p-3"><h4>Restore Official</h4><form action="../../backend/officials_update.php" method="POST"><input type="hidden" name="id" id="r_id"><input type="hidden" name="status" value="Active"><div class="text-end"><button class="btn btn-success" type="submit">Restore</button><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div></form></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
function openViewModal(name, pos, img) {
    document.getElementById('v_name').innerText = name;
    document.getElementById('v_position').innerText = pos;
    document.getElementById('v_image').src = img;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}
function openRestoreModal(id) {
    document.getElementById('r_id').value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}
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