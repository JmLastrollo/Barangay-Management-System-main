<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

try {
    $stmtRes = $conn->prepare("SELECT resident_id, first_name, last_name FROM resident_profiles WHERE user_id = :uid");
    $stmtRes->execute([':uid' => $user_id]);
    $resProfile = $stmtRes->fetch(PDO::FETCH_ASSOC);
    $resident_id = $resProfile['resident_id'];

    // 1. Get Appointments
    $stmtAppt = $conn->prepare("SELECT * FROM health_appointments WHERE resident_id = :rid ORDER BY appointment_date DESC");
    $stmtAppt->execute([':rid' => $resident_id]);
    $appointments = $stmtAppt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Medical Records (Matching Last Name)
    $stmtRec = $conn->prepare("SELECT * FROM health_records WHERE resident_name LIKE :rname ORDER BY date_visit DESC");
    $stmtRec->execute([':rname' => "%" . $resProfile['last_name'] . "%"]);
    $medical_records = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $appointments = [];
    $medical_records = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Health History - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">HEALTH <span class="green">RECORDS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark m-0"><i class="bi bi-calendar-week me-2 text-danger"></i>My Appointments</h4>
                <a href="resident_health_appointment.php" class="btn btn-danger rounded-pill fw-bold btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Book New
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="apptTable" class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Reason</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($appointments)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No appointments booked.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appt): 
                                        $statusClass = 'status-' . ($appt['status'] ?? 'Pending');
                                    ?>
                                    <tr>
                                        <td class="fw-bold text-danger"><?= htmlspecialchars($appt['service_type']) ?></td>
                                        <td>
                                            <div class="fw-bold"><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars($appt['appointment_time']) ?></div>
                                        </td>
                                        <td><small class="text-muted"><?= htmlspecialchars($appt['reason']) ?></small></td>
                                        <td class="text-center">
                                            <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($appt['status']) ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark m-0"><i class="bi bi-file-earmark-medical me-2 text-primary"></i>Medical History</h4>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="recordsTable" class="table table-hover align-middle">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date of Visit</th>
                                    <th>Diagnosis</th>
                                    <th>Treatment / Medicine</th>
                                    <th>Attended By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($medical_records)): ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No past medical records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($medical_records as $rec): ?>
                                    <tr>
                                        <td class="fw-bold"><?= date('M d, Y', strtotime($rec['date_visit'])) ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($rec['diagnosis']) ?></td>
                                        <td class="text-secondary"><?= htmlspecialchars($rec['treatment']) ?></td>
                                        <td class="small text-muted fst-italic"><?= htmlspecialchars($rec['attended_by']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        <?php include '../../includes/resident_footer.php'; ?>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            <?php if(!empty($appointments)): ?>
                $('#apptTable').DataTable({ "order": [[ 1, "desc" ]], searching: false, lengthChange: false });
            <?php endif; ?>
            <?php if(!empty($medical_records)): ?>
                $('#recordsTable').DataTable({ "order": [[ 0, "desc" ]] });
            <?php endif; ?>

            // Check for Session Toast (Success Message from Booking)
            <?php if(isset($_SESSION['toast'])): ?>
                const toastEl = document.getElementById('liveToast');
                const toastBody = document.getElementById('toastMessage');
                
                toastBody.innerText = "<?= $_SESSION['toast']['msg'] ?>";
                toastEl.classList.remove('bg-success', 'bg-danger');
                toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
                
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            <?php unset($_SESSION['toast']); endif; ?>
        });
    </script>
</body>
</html>