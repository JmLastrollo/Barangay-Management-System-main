<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../backend/db_connect.php';

// 2. Fetch Logs
try {
    $stmt = $conn->prepare("
        SELECT h.*, u.first_name, u.last_name, u.role 
        FROM history_logs h
        JOIN users u ON h.user_id = u.user_id
        WHERE u.role IN ('Admin', 'Staff')
        ORDER BY h.timestamp DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $logs = []; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BMS - Staff History</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css" />
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">STAFF <span class="green">HISTORY</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <div class="search-box">
                    <input type="text" id="logSearch" placeholder="Search activity..." class="form-control shadow-sm" style="border-radius: 20px; width: 300px;">
                    <button type="button"><i class="bi bi-search"></i></button>
                </div>
                
                <div class="d-flex align-items-center gap-2 bg-white p-2 rounded-4 shadow-sm border">
                    <span class="small fw-bold text-muted ps-2">From:</span>
                    <input type="date" id="startDate" class="form-control form-control-sm border-0">
                    <span class="small fw-bold text-muted">To:</span>
                    <input type="date" id="endDate" class="form-control form-control-sm border-0">
                    <button class="btn btn-light btn-sm rounded-circle border" id="resetFilters" title="Reset Filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">User</th>
                                    <th>Role</th>
                                    <th>Activity / Action</th>
                                    <th>Date & Time</th>
                                </tr>
                            </thead>
                            <tbody id="logsTable">
                                <?php if(empty($logs)): ?>
                                    <tr class="no-logs"><td colspan="4" class="text-center py-4 text-muted">No activity logs found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                    <tr class="log-row" data-date="<?= date("Y-m-d", strtotime($log['timestamp'])) ?>">
                                        <td class="ps-4 fw-bold">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 35px; height: 35px;">
                                                    <?= strtoupper(substr($log['first_name'], 0, 1)) ?>
                                                </div>
                                                <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                                $badgeClass = ($log['role'] == 'Admin') ? 'text-bg-danger' : 'text-bg-info';
                                            ?>
                                            <span class="badge rounded-pill <?= $badgeClass ?>"><?= htmlspecialchars($log['role']) ?></span>
                                        </td>
                                        <td class="text-muted"><?= htmlspecialchars($log['action']) ?></td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                                <?= date("M d, Y", strtotime($log['timestamp'])) ?>
                                            </div>
                                            <div class="small text-muted">
                                                <?= date("h:i A", strtotime($log['timestamp'])) ?>
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

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/staff_history.js"></script>
</body>
</html>