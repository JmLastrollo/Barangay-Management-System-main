<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../login.php"); exit();
}
require_once '../../backend/db_connect.php';

// Fetch Appointments
$sqlApp = "SELECT ha.*, r.first_name, r.last_name, r.contact_no 
           FROM health_appointments ha 
           JOIN resident_profiles r ON ha.resident_id = r.resident_id 
           ORDER BY ha.appointment_date DESC";
$resApp = $conn->query($sqlApp)->fetchAll(PDO::FETCH_ASSOC);

// Fetch History/Records
$sqlRec = "SELECT hr.*, r.first_name, r.last_name 
           FROM health_records hr
           LEFT JOIN resident_profiles r ON hr.resident_name = CONCAT(r.first_name, ' ', r.last_name)
           ORDER BY hr.date_visit DESC";
$resRec = $conn->query($sqlRec)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health Center - Staff</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
</head>
<body>

    <?php include '../../includes/staff_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">HEALTH <span class="green">CENTER</span></h1>
        </div>

        <div class="content container-fluid pb-5">
            
            <ul class="nav nav-pills mb-4" id="healthTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold px-4" id="app-tab" data-bs-toggle="tab" data-bs-target="#appointments" type="button">
                        <i class="bi bi-calendar-check me-2"></i>Appointments
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold px-4" id="rec-tab" data-bs-toggle="tab" data-bs-target="#records" type="button">
                        <i class="bi bi-journal-medical me-2"></i>Patient Records
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                
                <div class="tab-pane fade show active" id="appointments">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-uppercase small text-muted">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Patient Name</th>
                                            <th>Service</th>
                                            <th>Status</th>
                                            <th class="text-end pe-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($resApp as $row): 
                                            $badge = match($row['status']) {
                                                'Pending' => 'bg-warning text-dark',
                                                'Approved' => 'bg-primary',
                                                'Completed' => 'bg-success',
                                                'Cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-bold text-secondary">
                                                <?= date('M d, Y', strtotime($row['appointment_date'])) ?>
                                                <div class="small fw-normal"><?= $row['appointment_time'] ?></div>
                                            </td>
                                            <td>
                                                <span class="fw-bold"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></span>
                                                <div class="small text-muted"><?= $row['contact_no'] ?></div>
                                            </td>
                                            <td><?= htmlspecialchars($row['service_type']) ?></td>
                                            <td><span class="badge rounded-pill <?= $badge ?>"><?= $row['status'] ?></span></td>
                                            <td class="text-end pe-4">
                                                <?php if($row['status'] == 'Pending'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="updateStatus(<?= $row['appointment_id'] ?>, 'Approved')">Approve</button>
                                                <?php elseif($row['status'] == 'Approved'): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="markCompleted(<?= $row['appointment_id'] ?>, '<?= $row['first_name'].' '.$row['last_name'] ?>')">Complete</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="records">
                    <div class="d-flex justify-content-end mb-3">
                        <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addRecordModal">
                            <i class="bi bi-plus-lg me-2"></i>Add New Record
                        </button>
                    </div>
                    
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-uppercase small text-muted">
                                        <tr>
                                            <th class="ps-4">Date</th>
                                            <th>Patient</th>
                                            <th>Concern/Findings</th>
                                            <th>Attended By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($resRec as $rec): ?>
                                        <tr>
                                            <td class="ps-4 text-muted"><?= date('M d, Y', strtotime($rec['date_visit'])) ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($rec['resident_name']) ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($rec['concern']) ?></div>
                                                <small class="text-muted">Dx: <?= htmlspecialchars($rec['diagnosis']) ?></small>
                                            </td>
                                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($rec['attended_by']) ?></span></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="addRecordModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="../../backend/staff_health_action.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">New Health Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_record">
                    <div class="mb-3">
                        <label>Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" required placeholder="Full Name">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label>Age</label>
                            <input type="number" name="age" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label>Date</label>
                            <input type="date" name="date_visit" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Complaint / Concern</label>
                        <textarea name="concern" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Diagnosis & Treatment</label>
                        <textarea name="diagnosis" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Record</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(id, status) {
            if(!confirm('Update status to ' + status + '?')) return;
            const form = new FormData();
            form.append('action', 'update_status');
            form.append('id', id);
            form.append('status', status);

            fetch('../../backend/staff_health_action.php', { method:'POST', body:form })
            .then(r=>r.json()).then(d => { if(d.success) location.reload(); else alert(d.message); });
        }

        function markCompleted(id, name) {
            // Open modal and pre-fill name to record finding immediately
            document.querySelector('#addRecordModal input[name="patient_name"]').value = name;
            // You can add logic here to link it to the appointment ID if needed
            new bootstrap.Modal(document.getElementById('addRecordModal')).show();
            // Optional: Auto update status to Completed in background
            updateStatus(id, 'Completed');
        }
    </script>
</body>
</html>