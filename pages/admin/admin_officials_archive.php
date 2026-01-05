<?php
session_start();
require_once '../../backend/db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

// 1. GET DISTINCT YEARS (Mula sa lahat ng hindi Active)
try {
    $yearStmt = $conn->query("SELECT DISTINCT YEAR(term_end) as year 
                              FROM barangay_officials 
                              WHERE status != 'Active' 
                              AND term_end IS NOT NULL 
                              AND term_end != '0000-00-00' 
                              ORDER BY year DESC");
    $years = $yearStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $years = [];
}

// 2. FETCH INACTIVE OFFICIALS (Lahat ng hindi Active)
$selected_year = isset($_GET['year']) ? $_GET['year'] : '';

try {
    // UPDATED QUERY: Kinukuha lahat ng HINDI 'Active' (Inactive, Resigned, Archived)
    $sql = "SELECT * FROM barangay_officials WHERE status != 'Active'";
    $params = [];

    if (!empty($selected_year)) {
        $sql .= " AND YEAR(term_end) = :year";
        $params[':year'] = $selected_year;
    }

    $sql .= " ORDER BY term_end DESC";

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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMS - Past Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png"> 
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/officials.css">
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>
    
    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div id="toast" class="toast"></div>

        <div class="header">
            <h1 class="header-title">PAST <span class="green">OFFICIALS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="search-box">
                        <input type="text" id="officialSearch" class="form-control" placeholder="Search past official..." aria-label="Search">
                        <button type="button"><i class="bi bi-search"></i></button>
                    </div>

                    <select class="form-select shadow-sm" style="width: auto; height: 45px; border-radius: 20px; border: 1px solid #ddd;" onchange="filterByYear(this.value)">
                        <option value="">All Years</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= $year ?>" <?= ($selected_year == $year) ? 'selected' : '' ?>>
                                Year <?= $year ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mt-2 mt-md-0">
                    <a href="admin_officials.php" class="btn btn-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left"></i> Back to Active List
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
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
                            <tbody id="officialsTable">
                                <?php if(empty($officials)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No past officials found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($officials as $off): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle me-3 bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px; font-size: 14px; overflow: hidden;">
                                                    <?php if(!empty($off['image'])): ?>
                                                        <img src="../../uploads/officials/<?= htmlspecialchars($off['image']) ?>" alt="Img" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <?= substr(htmlspecialchars($off['full_name']), 0, 1) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="fw-bold text-secondary"><?= htmlspecialchars($off['full_name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($off['position']) ?></td>
                                        <td class="small text-muted">
                                            <?= ($off['term_end'] && $off['term_end'] != '0000-00-00') ? date("M d, Y", strtotime($off['term_end'])) : '---' ?>
                                        </td>
                                        
                                        <td>
                                            <?php if($off['status'] == 'Inactive'): ?>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary rounded-pill px-3">Inactive</span>
                                            <?php elseif($off['status'] == 'Resigned'): ?>
                                                <span class="badge bg-warning-subtle text-warning border border-warning rounded-pill px-3">Resigned</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-3"><?= htmlspecialchars($off['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn-action view" onclick='viewOfficial(<?= json_encode($off) ?>)' title="View">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                
                                                <button class="btn-action edit" onclick="restoreOfficial(<?= $off['official_id'] ?>, '<?= htmlspecialchars($off['full_name'], ENT_QUOTES) ?>')" title="Restore" style="color: #198754;">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>

                                                <button class="btn-action archive" onclick="deleteOfficial(<?= $off['official_id'] ?>, '<?= htmlspecialchars($off['full_name'], ENT_QUOTES) ?>')" title="Delete Permanently">
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
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-profile-header bg-secondary">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                </div>
                <div class="profile-img-container" id="v_image_container">
                    <img id="v_image" src="" class="profile-img-view">
                </div>
                <div class="modal-body text-center pt-5 pb-4">
                    <h3 class="fw-bold mb-1 text-center" id="v_name"></h3>
                    <div class="text-center mb-4">
                        <span id="v_status_badge" class="status-badge-custom status-badge-inactive"></span>
                    </div>
                    <div class="row text-start px-3 g-3">
                        <div class="col-12">
                            <label class="info-label">Position</label>
                            <span class="info-value" id="v_position"></span>
                        </div>
                        <div class="col-6">
                            <label class="info-label">Term Start</label>
                            <span class="info-value" id="v_term_start"></span>
                        </div>
                        <div class="col-6">
                            <label class="info-label">Term End</label>
                            <span class="info-value" id="v_term_end"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 5;"></button>
                <div class="modal-body text-center p-4">
                    <div class="mt-2 mb-3 text-success">
                        <i class="bi bi-arrow-counterclockwise" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Restore Official?</h4>
                    <p class="text-muted mb-4">
                        Are you sure you want to restore <br>
                        <span id="restore_name_display" class="fw-bold text-dark"></span> to the active list?
                    </p>
                    <form action="../../backend/officials_restore.php" method="POST">
                        <input type="hidden" name="id" id="restore_id">
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success px-4">Yes, Restore</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 5;"></button>
                <div class="modal-body text-center p-4">
                    <div class="mt-2 mb-3 text-danger">
                        <i class="bi bi-trash-fill" style="font-size: 3.5rem;"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Delete Permanently?</h4>
                    <p class="text-muted mb-4">
                        This action cannot be undone. <br>
                        Delete <span id="delete_name_display" class="fw-bold text-dark"></span> from records?
                    </p>
                    <form action="../../backend/officials_delete_permanent.php" method="POST">
                        <input type="hidden" name="id" id="delete_id">
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4">Delete Permanently</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/admin_officials.js"></script>

    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast("<?= $_SESSION['toast']['msg'] ?>", "<?= $_SESSION['toast']['type'] ?>");
            });
        </script>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

</body>
</html>