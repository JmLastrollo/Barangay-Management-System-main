<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

// TAB 1: GET APPOINTMENTS (Pending & Approved)
// Join with resident_profiles to get BIRTHDATE for age calculation
$sqlAppt = "SELECT a.*, r.first_name, r.last_name, r.birthdate 
            FROM health_appointments a 
            JOIN resident_profiles r ON a.resident_id = r.resident_id 
            WHERE a.status IN ('Pending', 'Approved') 
            ORDER BY a.appointment_date ASC, a.appointment_time ASC";
$appointments = $conn->query($sqlAppt)->fetchAll(PDO::FETCH_ASSOC);

// TAB 2: GET HISTORY (Completed)
$sqlHist = "SELECT * FROM health_records ORDER BY date_visit DESC";
$history = $conn->query($sqlHist)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Records - BMS</title>
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
            <h1 class="header-title">HEALTH <span class="green">RECORDS</span></h1>
        </div>

        <div class="content">
            
            <ul class="nav nav-tabs mb-4" id="healthTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold" id="appt-tab" data-bs-toggle="tab" data-bs-target="#appt-content">
                        <i class="bi bi-calendar-check me-2"></i>Appointments Queue
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold" id="hist-tab" data-bs-toggle="tab" data-bs-target="#hist-content">
                        <i class="bi bi-journal-medical me-2"></i>Consultation History
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="healthTabsContent">
                
                <div class="tab-pane fade show active" id="appt-content">
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0 bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th>Schedule</th>
                                    <th>Patient Name</th>
                                    <th>Age & Category</th>
                                    <th>Service / Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($appointments)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No appointments found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($appointments as $row): 
                                        
                                        // AUTOMATIC AGE CALCULATION
                                        $bday = new DateTime($row['birthdate']);
                                        $now = new DateTime();
                                        $age = $now->diff($bday)->y;
                                        $is_senior = ($age >= 60);

                                        // Append Age info to row data for JS
                                        $row['calculated_age'] = $age; 
                                        $data = json_encode($row);

                                        $statusBadge = ($row['status'] == 'Approved') ? 'bg-primary' : 'bg-warning text-dark';
                                    ?>
                                    <tr class="<?= $is_senior ? 'table-warning' : '' ?>">
                                        <td>
                                            <div class="fw-bold"><?= date('M d, Y', strtotime($row['appointment_date'])) ?></div>
                                            <small class="text-muted"><?= $row['appointment_time'] ?></small>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?= $row['first_name'] . ' ' . $row['last_name'] ?></div>
                                        </td>
                                        <td>
                                            <span class="fw-bold"><?= $age ?> y/o</span>
                                            <?php if($is_senior): ?>
                                                <span class="badge bg-danger ms-1">Senior Citizen</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small fw-bold"><?= $row['service_type'] ?></div>
                                            <small class="text-muted"><?= htmlspecialchars(substr($row['reason'], 0, 30)) ?>...</small>
                                        </td>
                                        <td><span class="badge rounded-pill <?= $statusBadge ?>"><?= $row['status'] ?></span></td>
                                        <td>
                                            <?php if($row['status'] == 'Pending'): ?>
                                                <form action="../../backend/health_action.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                                                </form>
                                                <form action="../../backend/health_action.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="appointment_id" value="<?= $row['appointment_id'] ?>">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Decline"><i class="bi bi-x-lg"></i></button>
                                                </form>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-info text-white fw-bold" onclick='openConsultModal(<?= $data ?>)'>
                                                    <i class="bi bi-clipboard-pulse"></i> Consult
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="hist-content">
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0 bg-white">
                            <thead class="table-light">
                                <tr>
                                    <th>Date Visit</th>
                                    <th>Patient Name</th>
                                    <th>Age</th>
                                    <th>Diagnosis</th>
                                    <th>Treatment</th>
                                    <th>Attended By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($history)): ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($history as $h): 
                                        $is_senior_hist = ($h['age'] >= 60);
                                    ?>
                                    <tr class="<?= $is_senior_hist ? 'table-light border-start border-5 border-warning' : '' ?>">
                                        <td><?= date('M d, Y', strtotime($h['date_visit'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($h['resident_name']) ?></td>
                                        <td>
                                            <?= $h['age'] ?>
                                            <?php if($is_senior_hist): ?><span class="text-warning small fw-bold"> (SC)</span><?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($h['diagnosis']) ?></td>
                                        <td><?= htmlspecialchars($h['treatment']) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($h['attended_by']) ?></span></td>
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

    <div class="modal fade" id="consultModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="../../backend/health_action.php" method="POST" class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-pulse me-2"></i>Medical Consultation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="complete">
                    <input type="hidden" name="appointment_id" id="c_appid">
                    <input type="hidden" name="resident_id" id="c_resid">

                    <div class="alert alert-light border d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Patient:</strong> <span id="c_name"></span> <br>
                            <strong>Age:</strong> <span id="c_age"></span> <span id="c_senior_badge" class="badge bg-warning text-dark d-none">Senior Citizen</span>
                        </div>
                        <div class="text-end">
                            <strong>Service:</strong> <span id="c_service" class="text-primary"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Complaint / Reason</label>
                        <textarea class="form-control bg-light" id="c_reason" rows="2" readonly></textarea>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Diagnosis / Findings</label>
                        <textarea name="diagnosis" class="form-control" rows="3" required placeholder="Enter medical findings..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Treatment / Prescription / Advice</label>
                        <textarea name="treatment" class="form-control" rows="3" required placeholder="Enter medicines or advice given..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save & Complete Record</button>
                </div>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function openConsultModal(data) {
            document.getElementById('c_appid').value = data.appointment_id;
            document.getElementById('c_resid').value = data.resident_id;
            
            // UI Display
            document.getElementById('c_name').innerText = data.first_name + ' ' + data.last_name;
            document.getElementById('c_service').innerText = data.service_type;
            document.getElementById('c_reason').value = data.reason;
            document.getElementById('c_age').innerText = data.calculated_age + " y/o";

            // Senior Badge Logic in Modal
            if (data.calculated_age >= 60) {
                document.getElementById('c_senior_badge').classList.remove('d-none');
            } else {
                document.getElementById('c_senior_badge').classList.add('d-none');
            }
            
            new bootstrap.Modal(document.getElementById('consultModal')).show();
        }

        // TOAST LOGIC
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.innerText = "<?= $_SESSION['toast']['msg'] ?>";
            
            const type = "<?= $_SESSION['toast']['type'] ?>";
            toastEl.className = `toast align-items-center text-white border-0 ${type === 'error' ? 'bg-danger' : 'bg-success'}`;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>