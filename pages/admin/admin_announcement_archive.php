<?php 
session_start();
// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../backend/db_connect.php'; 

// 2. Fetch Archived Announcements
try {
    $sql = "SELECT * FROM announcements WHERE status = 'archived' ORDER BY date DESC";
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Announcements</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="../../css/admin.css?v=<?= time(); ?>" />
    <link rel="stylesheet" href="../../css/sidebar.css?v=<?= time(); ?>" />
    <link rel="stylesheet" href="../../css/toast.css?v=<?= time(); ?>">
</head>
<body>

<?php include '../../includes/sidebar.php'; ?>

<div id="main-content">
    
    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">ANNOUNCEMENTS</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/Langkaan 2 Logo-modified.png">
            <img src="../../assets/img/dasma logo-modified.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            
            <div class="d-flex align-items-center gap-2">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search Title or Details..." class="form-control">
                    <button><i class="bi bi-search"></i></button>
                </div>

                <input type="month" id="monthFilter" class="form-control" style="width: auto; border-radius: 20px; cursor: pointer;" title="Filter by Month & Year">

                <select id="sortSelect" class="form-select" style="width: auto; border-radius: 20px; cursor: pointer;">
                    <option value="newest">Date: Newest First</option>
                    <option value="oldest">Date: Oldest First</option>
                </select>
            </div>
            
            <div class="mt-2 mt-md-0">
                <a href="admin_announcement.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="table-responsive shadow-sm rounded">
            <table class="table table-hover align-middle mb-0 bg-white">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Title</th>
                        <th>Details</th>
                        <th>Original Date</th>
                        <th class="text-center" style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="archiveTableBody">
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No archived items found.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $item): 
                        $formattedDate = date('M d, Y', strtotime($item['date']));
                    ?>
                    <tr>
                        <td class="ps-4 fw-bold text-primary"><?= htmlspecialchars($item['title']) ?></td>
                        <td><small class="text-muted"><?= htmlspecialchars(substr($item['details'], 0, 50)) ?>...</small></td>
                        <td><?= $formattedDate ?></td>
                        
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn-action edit" title="Restore" 
                                    onclick='openRestoreModal("<?= $item['announcement_id'] ?>")'>
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </button>
                                
                                <button class="btn-action archive" title="Delete Permanently"
                                    onclick="openDeleteModal('<?= $item['announcement_id'] ?>')">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-body text-center p-4">
            <div class="text-success mb-3"><i class="bi bi-arrow-counterclockwise" style="font-size: 3rem;"></i></div>
            <h4 class="fw-bold">Restore?</h4>
            <p class="text-muted">Restore this announcement to public view?</p>
            <form action="../../backend/announcement_update.php" method="POST">
                <input type="hidden" name="id" id="r_id">
                <input type="hidden" name="status" value="active"> 
                <input type="hidden" name="from_page" value="archive">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-success" type="submit">Yes, Restore</button>
            </form>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3"><i class="bi bi-trash-fill" style="font-size: 3rem;"></i></div>
                <h4 class="fw-bold">Delete?</h4>
                <p class="text-muted">Permanently delete this record?</p>
                <form action="../../backend/announcement_delete.php" method="POST">
                    <input type="hidden" name="id" id="d_id">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script> const initialData = <?= json_encode($announcements) ?>; </script>

<script src="../../assets/js/admin/admin_announcement_archive.js?v=<?= time(); ?>"></script>

</body>
</html>