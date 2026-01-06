<?php
session_start();
require_once '../../backend/db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

// --- AUTO-INACTIVE LOGIC ---
// Ito ang script na kusa naglilipat sa 'Inactive' kapag expired na ang term
try {
    $currentDate = date('Y-m-d');
    $updateStmt = $conn->prepare("UPDATE barangay_officials 
                                  SET status = 'Inactive' 
                                  WHERE term_end IS NOT NULL 
                                  AND term_end != '0000-00-00' 
                                  AND term_end < :currDate 
                                  AND status = 'Active'");
    $updateStmt->execute([':currDate' => $currentDate]);
} catch (PDOException $e) {
    // Silent fail
}

// Fetch Active Officials
try {
    $stmt = $conn->query("SELECT * FROM barangay_officials WHERE status = 'Active' ORDER BY term_start DESC");
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
    <title>BMS - Barangay Officials</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png"> 
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/officials.css">
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>
    
    <?php include '../../includes/staff_sidebar.php'; ?>

    <div id="main-content">
        <div id="toast" class="toast"></div>

        <div class="header">
            <h1 class="header-title">BARANGAY <span class="green">OFFICIALS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                
                <div class="search-box">
                    <input type="text" id="officialSearch" class="form-control" placeholder="Search official name..." aria-label="Search Official">
                    <button type="button" aria-label="Search"><i class="bi bi-search"></i></button>
                </div>

                <div class="action-buttons d-flex gap-2">
                    <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addOfficialModal">
                        <i class="bi bi-person-plus-fill"></i> Add Official
                    </button>
                    <a href="staff_officials_archive.php" class="btn btn-secondary d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history"></i> Past Officials
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
                                    <th>Term Period</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="officialsTable">
                                <?php if(empty($officials)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No active officials found.</td></tr>
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
                                                <span class="fw-bold"><?= htmlspecialchars($off['full_name']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($off['position']) ?></td>
                                        <td class="small text-muted">
                                            <?= date("M d, Y", strtotime($off['term_start'])) ?> - 
                                            <?= ($off['term_end'] && $off['term_end'] != '0000-00-00') ? date("M d, Y", strtotime($off['term_end'])) : '<span class="text-success fw-bold">Present</span>' ?>
                                        </td>
                                        <td><span class="badge bg-success-subtle text-success border border-success rounded-pill px-3">Active</span></td>
                                        
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center">
                                                <button class="btn-action view" onclick='viewOfficial(<?= json_encode($off) ?>)' title="View">
                                                    <i class="bi bi-eye-fill"></i>
                                                </button>
                                                
                                                <button class="btn-action edit" onclick='editOfficial(<?= json_encode($off) ?>)' title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                
                                                <button class="btn-action archive" onclick="archiveOfficial(<?= $off['official_id'] ?>, '<?= htmlspecialchars($off['full_name'], ENT_QUOTES) ?>')" title="Archive">
                                                    <i class="bi bi-archive-fill"></i>
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

    <div class="modal fade" id="addOfficialModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"> 
            <div class="modal-content">
                <form id="addOfficialForm" action="../../backend/officials_add.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Add New Official</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-4">
                        <div class="text-center mb-4">
                            <div class="mx-auto mb-2 d-flex align-items-center justify-content-center rounded-circle bg-light border" style="width: 100px; height: 100px; overflow: hidden;">
                                <img id="add-preview" src="../../assets/img/profile.jpg" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                <i id="add-placeholder" class="bi bi-person-fill text-secondary" style="font-size: 3rem;"></i>
                            </div>
                            <small class="text-muted d-block text-uppercase small fw-bold mt-1">Upload Photo</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Juan Dela Cruz" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase">Position</label>
                            <select name="position" class="form-select" required>
                                <option value="" selected disabled>Select Position</option>
                                <option>Barangay Captain</option>
                                <option>Kagawad</option>
                                <option>Secretary</option>
                                <option>Treasurer</option>
                                <option>SK Chairman</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Term Start</label>
                                <input type="date" name="term_start" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold text-secondary text-uppercase">Term End</label>
                                <input type="date" name="term_end" class="form-control">
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="file" name="photo" id="add-photo" class="form-control form-control-sm" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Save Official</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editOfficialModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="../../backend/officials_update.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Edit Official</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">FULL NAME</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">POSITION</label>
                            <select name="position" id="edit_position" class="form-select" required>
                                <option>Barangay Captain</option>
                                <option>Kagawad</option>
                                <option>Secretary</option>
                                <option>Treasurer</option>
                                <option>SK Chairman</option>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">TERM START</label>
                                <input type="date" name="term_start" id="edit_term_start" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">TERM END</label>
                                <input type="date" name="term_end" id="edit_term_end" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">STATUS</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Resigned">Resigned</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">UPDATE PHOTO</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-profile-header">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="profile-img-container" id="v_image_container">
                    <img id="v_image" src="" class="profile-img-view">
                </div>

                <div class="modal-body">
                    <h3 class="fw-bold mb-1 text-center" id="v_name">Official Name</h3>
                    <div class="text-center mb-4">
                        <span id="v_status_badge" class="status-badge-custom">Active</span>
                    </div>

                    <div class="row text-start px-3 g-3">
                        <div class="col-12">
                            <label class="info-label">Position</label>
                            <span class="info-value" id="v_position">Barangay Captain</span>
                        </div>
                        <div class="col-6">
                            <label class="info-label">Start of Term</label>
                            <span class="info-value" id="v_term_start">Jan 01, 2023</span>
                        </div>
                        <div class="col-6">
                            <label class="info-label">End of Term</label>
                            <span class="info-value" id="v_term_end">---</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative"> 
                <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 5;"></button>

                <div class="modal-body text-center p-4"> 
                    <div class="mt-2 mb-3 text-danger">
                        <i class="bi bi-exclamation-circle-fill" style="font-size: 3.5rem;"></i>
                    </div>
                    
                    <h4 class="fw-bold mb-2">Move to Inactive?</h4>
                    <p class="text-muted mb-4">
                        Are you sure you want to move <br>
                        <span id="archive_name_display" class="fw-bold text-dark"></span> to the inactive list?
                    </p>
                    
                    <form action="../../backend/officials_delete.php" method="POST">
                        <input type="hidden" name="id" id="archive_id">
                        <div class="d-flex justify-content-center gap-2">
                            <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger px-4">Yes, Move</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/staff/staff_officials.js"></script>

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