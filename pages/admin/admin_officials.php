<?php
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../admin_login.php");
    exit();
}

require_once '../../backend/db_connect.php';

try {
    // --- AUTOMATIC ARCHIVING LOGIC ---
    // Checks if any active official has a term_end that is in the past.
    // If yes, automatically changes their status to 'Archived'.
    $currentDate = date('Y-m-d');
    $autoArchiveSql = "UPDATE barangay_officials 
                       SET status = 'Archived' 
                       WHERE status = 'Active' 
                       AND term_end IS NOT NULL 
                       AND term_end < :currDate";
    $stmtArchive = $conn->prepare($autoArchiveSql);
    $stmtArchive->execute([':currDate' => $currentDate]);

    // --- FETCH ACTIVE OFFICIALS ---
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
    <title>BMS - Active Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/toast.css">
</head>

<body>
<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        
        <div class="dropdown-container active">
            <button class="dropdown-btn">
                <span><i class="bi bi-people"></i> Officials</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content" style="display: block;">
                <a href="admin_officials.php" class="active">Active Officials</a>
                <a href="admin_officials_archive.php">Past Officials</a>
            </div>
        </div>
        
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
        <h1 class="header-title">ACTIVE <span class="green">OFFICIALS</span></h1>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search Active Officials..." class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            
            <div class="mt-2 mt-md-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Image</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Term Start</th>
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="officialsTableBody">
                    <?php if (!empty($officials)): ?>
                        <?php foreach ($officials as $row): ?>
                            <tr>
                                <td>
                                    <?php $imgSrc = !empty($row['image']) ? "../../uploads/officials/" . $row['image'] : "../../assets/img/profile_placeholder.png"; ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:50%; border: 2px solid #ddd;">
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></td>
                                <td><?= htmlspecialchars($row['position']) ?></td>
                                <td>
                                    <?= $row['term_start'] ? date('M d, Y', strtotime($row['term_start'])) : '-' ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-info text-white" onclick="openViewModal('<?= htmlspecialchars($row['full_name']) ?>', '<?= htmlspecialchars($row['position']) ?>', '<?= htmlspecialchars($imgSrc) ?>')" title="View"><i class="bi bi-eye"></i></button>
                                        
                                        <button class="btn btn-sm btn-primary" onclick="openEditModal('<?= $row['official_id'] ?>', '<?= htmlspecialchars($row['full_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['position'], ENT_QUOTES) ?>')" title="Edit"><i class="bi bi-pencil-square"></i></button>
                                        
                                        <button class="btn btn-sm btn-secondary" onclick="openArchiveModal('<?= $row['official_id'] ?>')" title="Move to Past"><i class="bi bi-archive"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted p-4">No active officials found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../backend/officials_add.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Add Official</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="Hon. Juan Dela Cruz">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Position</label>
                        <input type="text" name="position" class="form-control" required placeholder="Punong Barangay">
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Term Start</label>
                            <input type="date" name="term_start" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Term End</label>
                            <input type="date" name="term_end" class="form-control">
                            <small class="text-muted" style="font-size:11px;">If this date is past, official moves to Archive automatically.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../backend/officials_update.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Official</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Photo</label>
                        <input type="file" name="photo" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" id="edit-name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Position</label>
                        <input type="text" name="position" id="edit-position" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="../../backend/officials_update.php" method="POST">
                <input type="hidden" name="id" id="o_id">
                <input type="hidden" name="status" value="Archived">
                <div class="modal-header">
                    <h5 class="modal-title text-warning">Move to Past Officials</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>End term for this official?</p>
                    <div class="mb-3">
                        <label>Term End Date:</label>
                        <input type="date" name="term_end" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-4 text-center">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <img id="v_image" src="" style="width:150px;height:150px;object-fit:cover;border-radius:50%;margin-bottom:15px; border: 3px solid #28a745;">
                <h4 id="v_name" class="fw-bold"></h4>
                <p id="v_position" class="text-muted"></p>
                <span class="badge bg-success">Active Official</span>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.classList.toggle('active');
        });
    });

    function openEditModal(id, name, pos) {
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
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#officialsTableBody tr");
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(filter) ? "" : "none";
        });
    });
    function showToast(message, type) {
        const t = document.getElementById("toast");
        t.className = "toast " + type + " show"; 
        t.textContent = message;
        setTimeout(() => { t.classList.remove("show"); }, 3000);
    }
</script>
<?php if (isset($_SESSION['toast'])): ?>
<script>showToast("<?= htmlspecialchars($_SESSION['toast']['msg']) ?>", "<?= htmlspecialchars($_SESSION['toast']['type']) ?>");</script>
<?php unset($_SESSION['toast']); endif; ?>
</body>
</html>