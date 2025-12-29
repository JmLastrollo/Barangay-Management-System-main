<?php
session_start();
require_once '../../backend/db_connect.php';

try {
    // FIX: Matches DB columns official_id and 'Active' status
    $stmt = $conn->prepare("SELECT * FROM barangay_officials WHERE status = 'Active' ORDER BY official_id ASC");
    $stmt->execute();
    $officials = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $officials = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BMS - Admin Officials</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css?v=1">
<link rel="stylesheet" href="../../css/toast.css">
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
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php" class="active"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php"><i class="bi bi-bookmark"></i> Issuance</a>
        
        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-file-earmark-text"></i> Records</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php">Blotter</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title"><span class="green">OFFICIALS</span></h1>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for Name" class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            <div class="mt-2 mt-md-0">
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
                <a href="admin_officials_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archive
                </a>
            </div>
        </div>

        <table class="table officials-table">
            <thead>
                <tr>
                    <th style="width: 150px;">Image</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th style="width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody id="officialsTableBody">
                <?php if (!empty($officials)): ?>
                    <?php foreach ($officials as $row): ?>
                        <tr class="official-row">
                            <td>
                                <?php $imgSrc = !empty($row['image']) ? "../../uploads/officials/" . $row['image'] : "../../assets/img/profile_placeholder.png"; ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:5px;">
                            </td>
                            <td class="official-name"><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['position']) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm text-white" onclick="openViewModal('<?= htmlspecialchars($row['full_name']) ?>', '<?= htmlspecialchars($row['position']) ?>', '<?= htmlspecialchars($imgSrc) ?>')"><i class="bi bi-eye"></i></button>
                                
                                <button class="btn btn-primary btn-sm me-1" onclick="openEditModal('<?= $row['official_id'] ?>', '<?= htmlspecialchars($row['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['position'], ENT_QUOTES) ?>', '<?= $row['image'] ?>')"><i class="bi bi-pencil-square"></i></button>
                                
                                <button class="btn btn-sm btn-secondary archive-btn" onclick="openArchiveModal('<?= $row['official_id'] ?>')"><i class="bi bi-archive"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">No officials found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="../../backend/officials_add.php" method="POST" enctype="multipart/form-data"><div class="modal-header"><h5 class="modal-title">Add Official</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label>Photo</label><input type="file" name="photo" class="form-control"></div><div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div><div class="mb-3"><label>Position</label><input type="text" name="position" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>

<div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="../../backend/officials_update.php" method="POST" enctype="multipart/form-data"><input type="hidden" name="id" id="edit-id"><div class="modal-header"><h5 class="modal-title">Edit Official</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label>Photo</label><input type="file" name="photo" class="form-control"></div><div class="mb-3"><label>Name</label><input type="text" name="name" id="edit-name" class="form-control" required></div><div class="mb-3"><label>Position</label><input type="text" name="position" id="edit-position" class="form-control" required></div></div><div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div></form></div></div></div>

<div class="modal fade" id="archiveModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form action="../../backend/officials_delete.php" method="POST"><input type="hidden" name="id" id="o_id"><div class="modal-body"><p>Archive this official?</p></div><div class="modal-footer"><button type="submit" class="btn btn-warning">Archive</button></div></form></div></div></div>

<div class="modal fade" id="viewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center"><img id="v_image" src="" style="width:150px;height:150px;object-fit:cover;border-radius:50%;margin-bottom:15px;"><h4 id="v_name"></h4><p id="v_position"></p></div></div></div></div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
function openEditModal(id, name, pos, img) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-position').value = pos;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
function openArchiveModal(id) {
    document.getElementById('o_id').value = id;
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}
function openViewModal(name, pos, img) {
    document.getElementById('v_name').innerText = name;
    document.getElementById('v_position').innerText = pos;
    document.getElementById('v_image').src = img;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}
// Search
document.getElementById("searchInput").addEventListener("keyup", function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#officialsTableBody tr");
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
});

// Toast Function
function showToast(message, type) {
    const t = document.getElementById("toast");
    t.className = "toast " + type + " show"; 
    t.textContent = message;
    setTimeout(() => { t.classList.remove("show"); }, 3000);
}
</script>

<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= htmlspecialchars($_SESSION['toast']['msg']) ?>", "<?= htmlspecialchars($_SESSION['toast']['type']) ?>");
</script>
<?php unset($_SESSION['toast']); endif; ?>

</body>
</html>