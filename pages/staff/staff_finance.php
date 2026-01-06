<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../login.php"); exit();
}
require_once '../../backend/db_connect.php';

// Stats
$today = date('Y-m-d');
$colToday = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type='Collection' AND DATE(transaction_date)='$today'")->fetchColumn() ?: 0;
$expToday = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type='Expense' AND DATE(transaction_date)='$today'")->fetchColumn() ?: 0;

// Fetch Transactions
$sql = "SELECT * FROM financial_records ORDER BY transaction_date DESC";
$trans = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance - Staff</title>
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
            <h1 class="header-title">FINANCE <span class="green">COLLECTION</span></h1>
        </div>

        <div class="content container-fluid pb-5">
            
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-3 border-start border-5 border-success">
                        <h6 class="text-muted text-uppercase mb-1">Collection Today</h6>
                        <h2 class="fw-bold text-success">₱<?= number_format($colToday, 2) ?></h2>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-3 border-start border-5 border-danger">
                        <h6 class="text-muted text-uppercase mb-1">Expenses Today</h6>
                        <h2 class="fw-bold text-danger">₱<?= number_format($expToday, 2) ?></h2>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold m-0 text-dark">Transaction History</h5>
                <button class="btn btn-primary rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addTransModal">
                    <i class="bi bi-plus-lg me-2"></i>New Entry
                </button>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase small text-muted">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($trans as $row): 
                                    $isCol = $row['transaction_type'] == 'Collection';
                                    $color = $isCol ? 'text-success' : 'text-danger';
                                    $sign  = $isCol ? '+' : '-';
                                    $badge = $isCol ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted small"><?= date('M d, Y h:i A', strtotime($row['transaction_date'])) ?></td>
                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['description']) ?></td>
                                    <td><span class="badge <?= $badge ?> border border-opacity-10 rounded-pill"><?= $row['transaction_type'] ?></span></td>
                                    <td class="text-end pe-4 fw-bold <?= $color ?>">
                                        <?= $sign ?> ₱<?= number_format($row['amount'], 2) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="addTransModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="../../backend/staff_finance_action.php">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Type</label>
                        <select name="type" class="form-select" required>
                            <option value="Collection">Collection (Income)</option>
                            <option value="Expense">Expense (Gastos)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Barangay Clearance Fee" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>