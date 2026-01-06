<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

// Stats Logic
$today = date('Y-m-d');
$pending = $conn->query("SELECT COUNT(*) FROM health_appointments WHERE status = 'Pending'")->fetchColumn();
$today_count = $conn->query("SELECT COUNT(*) FROM health_appointments WHERE appointment_date = '$today' AND status = 'Approved'")->fetchColumn();
$total_served = $conn->query("SELECT COUNT(*) FROM health_appointments WHERE status = 'Completed'")->fetchColumn();

// Get Today's Schedule + Birthdate for Age Calculation
$sqlToday = "SELECT a.*, r.first_name, r.last_name, r.contact_no, r.birthdate 
             FROM health_appointments a 
             JOIN resident_profiles r ON a.resident_id = r.resident_id 
             WHERE a.appointment_date = '$today' AND a.status = 'Approved' 
             ORDER BY a.appointment_time ASC";
$todays = $conn->query($sqlToday)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Health Dashboard - BMS</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <style>
        .stat-card {
            border: none;
            border-radius: 12px;
            color: white;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .card-body { position: relative; z-index: 2; padding: 25px; }
        .stat-card h3 { font-size: 2.5rem; font-weight: 700; margin: 0; }
        .stat-card p { font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; opacity: 0.9; font-weight: 600; }
        .stat-card .icon-bg {
            position: absolute;
            right: -15px;
            bottom: -20px;
            font-size: 6rem;
            opacity: 0.25;
            transform: rotate(-10deg);
            z-index: 1;
        }
        .bg-orange { background: linear-gradient(135deg, #ff9800, #ff5722); }
        .bg-blue { background: linear-gradient(135deg, #2196f3, #03a9f4); }
        .bg-green { background: linear-gradient(135deg, #4caf50, #8bc34a); }
    </style>
    </style>
</head>
<body class="bg-light">

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header mb-4">
            <h1 class="header-title">HEALTH <span class="green">CENTER DASHBOARD</span></h1>
            <p class="text-muted">Overview of today's clinic operations and statistics.</p>
        </div>

        <div class="content">
            
            <<div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card stat-card bg-orange h-100">
                        <div class="card-body">
                            <p>Pending Requests</p>
                            <h3><?= $pending ?></h3>
                            <div class="mt-2 small"><i class="bi bi-exclamation-circle me-1"></i> Waiting for approval</div>
                        </div>
                        <i class="bi bi-hourglass-split icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-blue h-100">
                        <div class="card-body">
                            <p>Scheduled Today</p>
                            <h3><?= $today_count ?></h3>
                            <div class="mt-2 small"><i class="bi bi-calendar-check me-1"></i> <?= date('F d, Y') ?></div>
                        </div>
                        <i class="bi bi-calendar2-day icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-green h-100">
                        <div class="card-body">
                            <p>Total Patients Served</p>
                            <h3><?= $total_served ?></h3>
                            <div class="mt-2 small"><i class="bi bi-check2-all me-1"></i> Completed Consultations</div>
                        </div>
                        <i class="bi bi-people-fill icon-bg"></i>
                    </div>
                </div>
            </div>

            <div class="card table-card">
                <div class="table-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold text-dark m-0">Today's Schedule</h5>
                        <small class="text-muted"><?= date('l, F d, Y') ?></small>
                    </div>
                    <a href="patient_records.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All Records <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
                
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th width="15%">Time Slot</th>
                                    <th width="30%">Patient Name</th>
                                    <th width="20%">Age / Category</th>
                                    <th width="25%">Service</th>
                                    <th width="10%" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($todays)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50 mb-2">
                                                <i class="bi bi-calendar-x display-4"></i>
                                            </div>
                                            <h6 class="text-muted">No appointments scheduled for today.</h6>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($todays as $row): 
                                        // Age Logic
                                        $bday = new DateTime($row['birthdate']);
                                        $now = new DateTime();
                                        $age = $now->diff($bday)->y;
                                        $is_senior = ($age >= 60);
                                        
                                        // Initials for Avatar
                                        $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
                                    ?>
                                    <tr class="<?= $is_senior ? 'senior-row' : '' ?>">
                                        <td>
                                            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">
                                                <i class="bi bi-clock me-1"></i> <?= $row['appointment_time'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar shadow-sm text-primary bg-white">
                                                    <?= $initials ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?= $row['first_name'] . ' ' . $row['last_name'] ?></div>
                                                    <small class="text-muted"><?= $row['contact_no'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?= $age ?> years old</span>
                                            <?php if($is_senior): ?>
                                                <div class="mt-1">
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Senior Citizen</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="text-secondary fw-medium"><?= $row['service_type'] ?></span>
                                        </td>
                                        <td class="text-end">
                                            <a href="patient_records.php" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                                Process
                                            </a>
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

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>