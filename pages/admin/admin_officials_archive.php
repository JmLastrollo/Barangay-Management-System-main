<?php
session_start();
require_once '../../backend/db_connect.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

// Fetch ARCHIVED Officials Only
try {
    $stmt = $conn->query("SELECT * FROM barangay_officials WHERE status = 'Archived' ORDER BY term_end DESC");
    $archives = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { 
    $archives = []; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMS - Archived Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/officials.css">
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">OFFICIALS <span class="text-secondary">ARCHIVES</span></h1>
        </div>

        <div class="content">
            <?php if(isset($_SESSION['toast'])): ?>
                <div class="alert alert-<?=$_SESSION['toast']['type'] == 'success' ? 'success' : 'danger'?> alert-dismissible fade show">
                    <?= $_SESSION['toast']['msg'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['toast']); ?>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="search-box w-50">
                    <input type="text" id="archiveSearch" placeholder="Search archived official..." class="form-control">
                </div>
                <div class="mt-2 mt-md-0">
                <a href="admin_officials.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded bg-white">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Official Name</th>
                            <th>Position</th>
                            <th>Term Ended</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($archives)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No archived officials found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($archives as $off): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-secondary"><?= htmlspecialchars($off['full_name']) ?></td>
                                <td class="text-secondary"><?= htmlspecialchars($off['position']) ?></td>
                                <td class="small text-secondary">
                                    <?= $off['term_end'] ? date("M d, Y", strtotime($off['term_end'])) : 'N/A' ?>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-3">Archived</span></td>
                                
                                <td class="text-center">
                                    <form action="../../backend/officials_update.php" method="POST" onsubmit="return confirm('Restore this official to active list?');">
                                        <input type="hidden" name="id" value="<?= $off['official_id'] ?>">
                                        <input type="hidden" name="status" value="Active"> 
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Restore">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>