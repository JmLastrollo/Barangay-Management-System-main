<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// Get Resident ID
$stmtRes = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = :uid");
$stmtRes->execute([':uid' => $user_id]);
$resProfile = $stmtRes->fetch(PDO::FETCH_ASSOC);
$resident_id = $resProfile['resident_id'];

// Get Complaints
try {
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE resident_id = :rid ORDER BY date_filed DESC");
    $stmt->execute([':rid' => $resident_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $complaints = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reports - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">MY <span class="green">REPORTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo">
            </div>
        </div>

        <div class="content pb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0"><i class="bi bi-folder-fill me-2 text-danger"></i>Complaint History</h3>
                <a href="resident_file_complaint.php" class="btn btn-danger rounded-pill fw-bold"><i class="bi bi-plus-lg"></i> File Complaint</a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date Filed</th>
                                    <th>Respondent</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($complaints)): ?>
                                    <tr><td colspan="4" class="text-center py-5 text-muted">No complaints found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($complaints as $row): 
                                        $statusClass = match($row['status']) {
                                            'Pending' => 'bg-warning text-dark',
                                            'Active' => 'bg-primary text-white',
                                            'Settled' => 'bg-success text-white',
                                            'Dismissed' => 'bg-secondary text-white',
                                            default => 'bg-light text-dark'
                                        };
                                    ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($row['date_filed'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['respondent_name']) ?></td>
                                        <td><?= htmlspecialchars($row['complaint_type']) ?></td>
                                        <td><span class="badge rounded-pill <?= $statusClass ?>"><?= $row['status'] ?></span></td>
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

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            document.getElementById('toastMessage').innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            new bootstrap.Toast(toastEl).show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>