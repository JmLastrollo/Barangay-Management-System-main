<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

// 1. CALCULATE TOTALS (Overview Stats)
$colStmt = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type = 'Collection'");
$total_collection = $colStmt->fetchColumn() ?: 0;

$expStmt = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type = 'Expense'");
$total_expense = $expStmt->fetchColumn() ?: 0;

$balance = $total_collection - $total_expense;

// 2. FILTER & SORT LOGIC
$filterType = $_GET['filter_type'] ?? 'All';
$sortOrder  = $_GET['sort_order'] ?? 'date_desc';

// Base Query
$sql = "SELECT f.*, u.first_name, u.last_name 
        FROM financial_records f 
        LEFT JOIN users u ON f.recorded_by = u.user_id";

$conditions = [];
$params = [];

// Apply Filter (Collection or Expense)
if ($filterType != 'All') {
    $conditions[] = "transaction_type = :type";
    $params[':type'] = $filterType;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Apply Sorting
switch ($sortOrder) {
    case 'amount_high':
        $sql .= " ORDER BY amount DESC";
        break;
    case 'amount_low':
        $sql .= " ORDER BY amount ASC";
        break;
    case 'date_asc':
        $sql .= " ORDER BY transaction_date ASC";
        break;
    case 'date_desc':
    default:
        $sql .= " ORDER BY transaction_date DESC";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Management - BMS</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/toast.css">
    
    <style>
        /* Finance Styles */
        .stat-card {
            border: none;
            border-radius: 15px;
            color: white;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            min-height: 140px;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card .card-body { position: relative; z-index: 2; padding: 25px; }
        .stat-card h2 { font-size: 2.2rem; font-weight: 800; margin: 0; }
        .stat-card p { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; opacity: 0.9; font-weight: 600; }
        .stat-card .icon-bg {
            position: absolute;
            right: -10px;
            bottom: -15px;
            font-size: 6rem;
            opacity: 0.2;
            transform: rotate(-15deg);
            z-index: 1;
        }
        
        .bg-collection { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .bg-expense { background: linear-gradient(135deg, #ff416c, #ff4b2b); }
        .bg-balance { background: linear-gradient(135deg, #2193b0, #6dd5ed); }

        .amount-text { font-family: 'Consolas', monospace; font-weight: bold; }
        .text-income { color: #198754; }
        .text-expense { color: #dc3545; }
    </style>
</head>
<body class="bg-light">

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header mb-4">
            <h1 class="header-title">FINANCIAL <span class="green">MANAGEMENT</span></h1>
        </div>

        <div class="content">
            
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card stat-card bg-balance h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <p>Current Balance</p>
                                <h2>₱ <?= number_format($balance, 2) ?></h2>
                            </div>
                            <small class="mt-2"><i class="bi bi-wallet2"></i> Available Funds</small>
                        </div>
                        <i class="bi bi-bank icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-collection h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <p>Total Collections</p>
                                <h2>₱ <?= number_format($total_collection, 2) ?></h2>
                            </div>
                            <small class="mt-2"><i class="bi bi-graph-up-arrow"></i> Revenue / Income</small>
                        </div>
                        <i class="bi bi-cash-coin icon-bg"></i>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card bg-expense h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div>
                                <p>Total Expenses</p>
                                <h2>₱ <?= number_format($total_expense, 2) ?></h2>
                            </div>
                            <small class="mt-2"><i class="bi bi-graph-down-arrow"></i> Disbursements</small>
                        </div>
                        <i class="bi bi-cart-x icon-bg"></i>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-body bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    
                    <form method="GET" class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-funnel-fill text-muted"></i></span>
                            <select name="filter_type" class="form-select border-0 bg-light fw-bold" style="width: 150px;" onchange="this.form.submit()">
                                <option value="All" <?= $filterType == 'All' ? 'selected' : '' ?>>All Records</option>
                                <option value="Collection" <?= $filterType == 'Collection' ? 'selected' : '' ?>>Collections Only</option>
                                <option value="Expense" <?= $filterType == 'Expense' ? 'selected' : '' ?>>Expenses Only</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-sort-down text-muted"></i></span>
                            <select name="sort_order" class="form-select border-0 bg-light fw-bold" style="width: 170px;" onchange="this.form.submit()">
                                <option value="date_desc" <?= $sortOrder == 'date_desc' ? 'selected' : '' ?>>Newest First</option>
                                <option value="date_asc" <?= $sortOrder == 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
                                <option value="amount_high" <?= $sortOrder == 'amount_high' ? 'selected' : '' ?>>Highest Amount</option>
                                <option value="amount_low" <?= $sortOrder == 'amount_low' ? 'selected' : '' ?>>Lowest Amount</option>
                            </select>
                        </div>
                    </form>

                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addFinanceModal">
                        <i class="bi bi-plus-lg me-2"></i> Add Record
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small text-muted">
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($transactions)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No transactions found matching your filter.</td></tr>
                                <?php else: ?>
                                    <?php foreach($transactions as $row): 
                                        $isCollection = ($row['transaction_type'] == 'Collection');
                                        $badgeClass = $isCollection ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
                                        $amountClass = $isCollection ? 'text-income' : 'text-expense';
                                        $sign = $isCollection ? '+' : '-';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= date('M d, Y', strtotime($row['transaction_date'])) ?></div>
                                            <small class="text-muted"><?= date('h:i A', strtotime($row['transaction_date'])) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($row['description']) ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $badgeClass ?> px-3 border border-<?= $isCollection ? 'success' : 'danger' ?>">
                                                <?= strtoupper($row['transaction_type']) ?>
                                            </span>
                                        </td>
                                        <td class="amount-text <?= $amountClass ?> fs-6">
                                            <?= $sign ?> ₱ <?= number_format($row['amount'], 2) ?>
                                        </td>
                                        <td>
                                            <small class="text-muted fst-italic">
                                                <?= $row['first_name'] ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) : 'System' ?>
                                            </small>
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

    <div class="modal fade" id="addFinanceModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="../../backend/finance_add.php" method="POST" class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-wallet-fill me-2"></i>Add Financial Record</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="alert alert-info border-0 d-flex align-items-center small p-2 mb-3">
                        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                        <div>To add funds, select <strong>Collection</strong>. To record spending, select <strong>Expense</strong>.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Type</label>
                        <select name="transaction_type" class="form-select form-select-lg" required>
                            <option value="" disabled selected>Select Type...</option>
                            <option value="Collection">Collection (Add Fund / Income)</option>
                            <option value="Expense">Expense (Disbursement / Gastos)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Amount (PHP)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">₱</span>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description / Particulars</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="e.g. IRA Budget, Brgy Clearance Fee, Office Supplies..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Date</label>
                        <input type="datetime-local" name="transaction_date" class="form-control" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Save Record</button>
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