<?php 
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../admin_login.php");
    exit();
}

require_once '../../backend/db_connect.php'; 

// 2. Fetch Archived Officials (Ordered by who finished term last)
$sql = "SELECT * FROM barangay_officials WHERE status = 'Archived' ORDER BY term_end DESC";

try {
    $stmt = $conn->prepare($sql);
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
    <title>BMS - Past Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
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
                <a href="admin_officials.php">Active Officials</a>
                <a href="admin_officials_archive.php" class="active">Past Officials</a>
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
        <h1 class="header-title">PAST <span class="green">OFFICIALS</span></h1>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search by Name or Year..." class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Image</th>
                        <th>Name</th>
                        <th>Position</th>
                        <th>Term Served</th>
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="archiveTableBody">
                    <?php if (count($officials) > 0): ?>
                        <?php foreach ($officials as $item): 
                            $startYear = $item['term_start'] ? date('Y', strtotime($item['term_start'])) : 'Unknown';
                            $endYear = $item['term_end'] ? date('Y', strtotime($item['term_end'])) : 'Present';
                        ?>
                        <tr>
                            <td>
                                <?php $imgSrc = !empty($item['image']) ? "../../uploads/officials/" . $item['image'] : "../../assets/img/profile_placeholder.png"; ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:50%; border: 2px solid #ddd;">
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($item['full_name']) ?></td>
                            <td><?= htmlspecialchars($item['position']) ?></td>
                            
                            <td>
                                <span class="badge bg-secondary">
                                    <?= $startYear ?> - <?= $endYear ?>
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-1">
                                    <button class="btn btn-sm btn-info text-white" title="View"
                                        onclick="openViewModal('<?= htmlspecialchars($item['full_name']) ?>', '<?= htmlspecialchars($item['position']) ?>', '<?= htmlspecialchars($imgSrc) ?>', '<?= $startYear ?> - <?= $endYear ?>')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    
                                    <button class="btn btn-sm btn-success" onclick="openRestoreModal('<?= $item['official_id'] ?>')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>

                                    <button class="btn btn-sm btn-danger" onclick="openDeleteModal('<?= $item['official_id'] ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted p-4">No past officials found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
                <img id="v_image" src="" style="width:150px;height:150px;object-fit:cover;border-radius:50%;margin-bottom:15px; border: 3px solid #6c757d;">
                <h4 id="v_name" class="fw-bold"></h4>
                <p id="v_position" class="text-muted mb-2"></p>
                <div class="mb-3">
                    <span class="badge bg-secondary" id="v_term"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-success">Restore Official</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Restore this official to the active list?</p>
                <small class="text-danger">This will clear their Term End date.</small>
            </div>
            <div class="modal-footer">
                <form action="../../backend/officials_update.php" method="POST">
                    <input type="hidden" name="id" id="r_id">
                    <input type="hidden" name="status" value="Active">
                    <input type="hidden" name="clear_term_end" value="true"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Yes, Restore</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Delete Permanently</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>This action cannot be undone. Are you sure you want to permanently delete this record?</p>
            </div>
            <div class="modal-footer">
                <form action="../../backend/officials_delete.php" method="POST">
                    <input type="hidden" name="id" id="d_id">
                    <input type="hidden" name="permanent" value="true">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
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
    function openViewModal(name, pos, img, term) {
        document.getElementById('v_name').innerText = name;
        document.getElementById('v_position').innerText = pos;
        document.getElementById('v_image').src = img;
        document.getElementById('v_term').innerText = "Term: " + term;
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }
    function openRestoreModal(id) {
        document.getElementById('r_id').value = id;
        new bootstrap.Modal(document.getElementById('restoreModal')).show();
    }
    function openDeleteModal(id) {
        document.getElementById('d_id').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
    document.getElementById("searchInput").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll("#archiveTableBody tr");
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