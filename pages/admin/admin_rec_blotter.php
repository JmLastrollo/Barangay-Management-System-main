<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

$searchQuery = $_GET["search"] ?? "";
$params = [];

// Get Active Blotters
$sql = "SELECT * FROM blotter_records WHERE status_archive = 'Active'";

if (!empty($searchQuery)) {
    $sql .= " AND (respondent LIKE :search OR complainant LIKE :search OR incident_type LIKE :search OR blotter_id LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY incident_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>BMS - Admin Blotter</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">RECORD <span class="green">BLOTTER</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <form method="GET" class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search Case ID, Name..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="search-btn"><i class="bi bi-search"></i></button>
                </form>
                <div>
                    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-circle"></i> Add New</button>
                    <a href="admin_rec_blotter_archive.php" class="btn btn-secondary"><i class="bi bi-archive"></i> Archive</a>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Case ID</th>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Hearing Sched</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($incidents)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($incidents as $row): 
                                $data = json_encode($row);
                                $badge = match($row['status']) {
                                    'Settled' => 'bg-success',
                                    'Pending' => 'bg-warning text-dark',
                                    'Hearing' => 'bg-info text-dark',
                                    'Unsettled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                
                                // Format Hearing Schedule if exists
                                $hearingDisplay = ($row['status'] == 'Hearing' && !empty($row['hearing_schedule'])) 
                                    ? date("M d, h:i A", strtotime($row['hearing_schedule'])) 
                                    : '<span class="text-muted">-</span>';
                            ?>
                            <tr>
                                <td class="fw-bold">#<?= $row['blotter_id'] ?></td>
                                <td><?= htmlspecialchars($row['complainant']) ?></td>
                                <td><?= htmlspecialchars($row['respondent']) ?></td>
                                <td><?= date("M d, Y", strtotime($row['incident_date'])) ?></td>
                                <td><span class="badge rounded-pill <?= $badge ?>"><?= $row['status'] ?></span></td>
                                <td class="text-primary fw-bold"><?= $hearingDisplay ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick='openEditModal(<?= $data ?>)'>
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="openArchiveModal('<?= $row['blotter_id'] ?>')">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="../../backend/blotter_add.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">New Blotter Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Complainant</label>
                            <input type="text" name="complainant" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Respondent</label>
                            <input type="text" name="respondent" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Incident Type</label>
                            <select name="incident_type" class="form-select">
                                <option>Amicable Settlement</option>
                                <option>Noise Complaint</option>
                                <option>Physical Injury</option>
                                <option>Theft</option>
                                <option>Property Damage</option>
                                <option>Others</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Incident</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Incident Location</label>
                            <input type="text" name="incident_location" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Narrative</label>
                            <textarea name="details" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="../../backend/blotter_update.php" method="POST" class="modal-content">
                <input type="hidden" name="blotter_id" id="e_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Blotter Case</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Complainant</label>
                            <input type="text" name="complainant" id="e_complainant" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Respondent</label>
                            <input type="text" name="respondent" id="e_respondent" class="form-control" readonly>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="e_status" class="form-select" onchange="toggleHearingField()">
                                <option value="Pending">Pending</option>
                                <option value="Hearing">Hearing (Set Schedule)</option>
                                <option value="Settled">Settled</option>
                                <option value="Unsettled">Unsettled</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="hearingDiv">
                            <div class="p-3 bg-light border rounded">
                                <label class="form-label text-primary fw-bold">Set Hearing Schedule</label>
                                <input type="datetime-local" name="hearing_schedule" id="e_hearing" class="form-control">
                                <div class="form-text">Required if status is "Hearing"</div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Narrative / Update</label>
                            <textarea name="narrative" id="e_narrative" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header text-warning">
                    <h5 class="modal-title fw-bold">Archive Record?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">Are you sure you want to archive this case?</div>
                <div class="modal-footer">
                    <form action="../../backend/blotter_update.php" method="POST">
                        <input type="hidden" name="blotter_id" id="archive_id">
                        <input type="hidden" name="action" value="archive">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Archive</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleHearingField() {
            const status = document.getElementById('e_status').value;
            const hearingDiv = document.getElementById('hearingDiv');
            const hearingInput = document.getElementById('e_hearing');

            if (status === 'Hearing') {
                hearingDiv.classList.remove('d-none');
                hearingInput.required = true;
            } else {
                hearingDiv.classList.add('d-none');
                hearingInput.required = false;
                hearingInput.value = ""; 
            }
        }

        function openEditModal(data) {
            document.getElementById('e_id').value = data.blotter_id;
            document.getElementById('e_complainant').value = data.complainant;
            document.getElementById('e_respondent').value = data.respondent;
            document.getElementById('e_status').value = data.status;
            document.getElementById('e_narrative').value = data.narrative;
            
            if(data.hearing_schedule) {
                let dt = new Date(data.hearing_schedule);
                dt.setMinutes(dt.getMinutes() - dt.getTimezoneOffset());
                document.getElementById('e_hearing').value = dt.toISOString().slice(0,16);
            }

            toggleHearingField();
            new bootstrap.Modal(document.getElementById('editModal')).show();
        }

        function openArchiveModal(id) {
            document.getElementById('archive_id').value = id;
            new bootstrap.Modal(document.getElementById('archiveModal')).show();
        }

        // --- TOAST NOTIFICATION LOGIC ---
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.innerText = "<?= $_SESSION['toast']['msg'] ?? '' ?>";
            
            const type = "<?= $_SESSION['toast']['type'] ?? 'success' ?>";
            toastEl.className = `toast align-items-center text-white border-0 ${type === 'error' ? 'bg-danger' : 'bg-success'}`;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>