<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

// 1. CALCULATE TOTALS
$colStmt = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type = 'Collection'");
$total_collection = $colStmt->fetchColumn() ?: 0;

$expStmt = $conn->query("SELECT SUM(amount) FROM financial_records WHERE transaction_type = 'Expense'");
$total_expense = $expStmt->fetchColumn() ?: 0;

$balance = $total_collection - $total_expense;

// 2. FILTER & SORT LOGIC
$filterType = $_GET['filter_type'] ?? 'All';
$sortOrder  = $_GET['sort_order'] ?? 'date_desc';

$sql = "SELECT f.*, u.first_name, u.last_name 
        FROM financial_records f 
        LEFT JOIN users u ON f.recorded_by = u.user_id";

$conditions = [];
if ($filterType != 'All') {
    $conditions[] = "transaction_type = '$filterType'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

switch ($sortOrder) {
    case 'amount_high': $sql .= " ORDER BY amount DESC"; break;
    case 'amount_low':  $sql .= " ORDER BY amount ASC"; break;
    case 'date_asc':    $sql .= " ORDER BY transaction_date ASC"; break;
    case 'date_desc':   default: $sql .= " ORDER BY transaction_date DESC"; break;
}

$transactions = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
        /* Professional Finance Card Styles */
        .balance-card {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            border-radius: 15px;
            padding: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.3);
        }
        .balance-card h1 { font-size: 3.5rem; font-weight: 800; margin: 0; font-family: 'Consolas', monospace; }
        .balance-card .label { text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem; opacity: 0.9; }
        .balance-card .icon-bg {
            position: absolute;
            right: -20px; bottom: -20px;
            font-size: 8rem; opacity: 0.15;
            transform: rotate(-10deg);
        }

        .mini-stat-card {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.2s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .mini-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.05); }
        .icon-box {
            width: 50px; height: 50px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-right: 15px;
        }
        .icon-income { background: #d1e7dd; color: #198754; }
        .icon-expense { background: #f8d7da; color: #dc3545; }
        
        .amount-small { font-weight: 700; font-size: 1.5rem; font-family: 'Consolas', monospace; }

        /* Action Buttons */
        .btn-action {
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            display: flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-income { background-color: #198754; color: white; border: none; }
        .btn-income:hover { background-color: #157347; box-shadow: 0 4px 12px rgba(25, 135, 84, 0.4); }
        
        .btn-expense { background-color: #dc3545; color: white; border: none; }
        .btn-expense:hover { background-color: #bb2d3b; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4); }

        .table-amount { font-family: 'Consolas', monospace; font-weight: 600; }
        .text-inc { color: #198754; }
        .text-exp { color: #dc3545; }
    </style>
</head>
<body class="bg-light">

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header mb-4">
            <h1 class="header-title">FINANCIAL <span class="green">OVERVIEW</span></h1>
        </div>

        <div class="content">
            
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="balance-card h-100 d-flex flex-column justify-content-center">
                        <span class="label">Total Available Fund</span>
                        <h1>₱ <?= number_format($balance, 2) ?></h1>
                        <div class="mt-2 small"><i class="bi bi-shield-check me-1"></i> Audited & Verified</div>
                        <i class="bi bi-wallet2 icon-bg"></i>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="mini-stat-card h-50">
                            <div class="icon-box icon-income"><i class="bi bi-graph-up-arrow"></i></div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold">Total Collections</small>
                                <div class="amount-small text-dark">₱ <?= number_format($total_collection, 2) ?></div>
                            </div>
                        </div>
                        <div class="mini-stat-card h-50">
                            <div class="icon-box icon-expense"><i class="bi bi-graph-down-arrow"></i></div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold">Total Expenses</small>
                                <div class="amount-small text-dark">₱ <?= number_format($total_expense, 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3 bg-white p-3 rounded-4 shadow-sm">
                <form method="GET" class="d-flex gap-2 w-100 w-md-auto">
                    <select name="filter_type" class="form-select border-light bg-light fw-bold" onchange="this.form.submit()">
                        <option value="All" <?= $filterType == 'All' ? 'selected' : '' ?>>All Transactions</option>
                        <option value="Collection" <?= $filterType == 'Collection' ? 'selected' : '' ?>>Collections</option>
                        <option value="Expense" <?= $filterType == 'Expense' ? 'selected' : '' ?>>Expenses</option>
                    </select>
                    <select name="sort_order" class="form-select border-light bg-light fw-bold" onchange="this.form.submit()">
                        <option value="date_desc" <?= $sortOrder == 'date_desc' ? 'selected' : '' ?>>Latest</option>
                        <option value="amount_high" <?= $sortOrder == 'amount_high' ? 'selected' : '' ?>>Highest Amount</option>
                    </select>
                </form>

                <div class="d-flex gap-2">
                    <button class="btn-action btn-income" onclick="openModal('Collection')">
                        <i class="bi bi-plus-lg"></i> Add Income
                    </button>
                    <button class="btn-action btn-expense" onclick="openModal('Expense')">
                        <i class="bi bi-dash-lg"></i> Add Expense
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
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No records found.</td></tr>
                                <?php else: ?>
                                    <?php foreach($transactions as $row): 
                                        $isCol = ($row['transaction_type'] == 'Collection');
                                        $sign = $isCol ? '+' : '-';
                                        $color = $isCol ? 'text-inc' : 'text-exp';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?= date('M d, Y', strtotime($row['transaction_date'])) ?></div>
                                            <small class="text-muted"><?= date('h:i A', strtotime($row['transaction_date'])) ?></small>
                                        </td>
                                        <td class="text-secondary fw-medium"><?= htmlspecialchars($row['description']) ?></td>
                                        <td>
                                            <?php if($isCol): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">COLLECTION</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">EXPENSE</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="table-amount <?= $color ?> fs-5">
                                            <?= $sign ?> ₱ <?= number_format($row['amount'], 2) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle text-center me-2" style="width:30px; height:30px; line-height:30px;">
                                                    <i class="bi bi-person-fill text-muted"></i>
                                                </div>
                                                <small class="text-muted">
                                                    <?= $row['first_name'] ? htmlspecialchars($row['first_name']) : 'Admin' ?>
                                                </small>
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

    <div class="modal fade" id="financeModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="../../backend/finance_add.php" method="POST" class="modal-content border-0 shadow">
                
                <div class="modal-header text-white" id="modalHeader">
                    <h5 class="modal-title fw-bold" id="modalTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <input type="hidden" name="transaction_type" id="transType">

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted text-uppercase small">Amount (PHP)</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light border-0 fw-bold">₱</span>
                            <input type="number" step="0.01" name="amount" class="form-control bg-light border-0 fw-bold" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-muted text-uppercase small">Description</label>
                        <textarea name="description" class="form-control bg-light border-0" rows="3" placeholder="Enter details..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted text-uppercase small">Date</label>
                        <input type="datetime-local" name="transaction_date" class="form-control bg-light border-0" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn rounded-pill px-5 fw-bold text-white" id="modalSubmitBtn">Save</button>
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
        function openModal(type) {
            const modalEl = document.getElementById('financeModal');
            const header = document.getElementById('modalHeader');
            const title = document.getElementById('modalTitle');
            const submitBtn = document.getElementById('modalSubmitBtn');
            const inputType = document.getElementById('transType');

            // Set Type
            inputType.value = type;

            if (type === 'Collection') {
                header.className = 'modal-header bg-success text-white';
                title.innerHTML = '<i class="bi bi-wallet-fill me-2"></i> Add Collection (Income)';
                submitBtn.className = 'btn btn-success rounded-pill px-5 fw-bold';
                submitBtn.innerText = 'Save Income';
            } else {
                header.className = 'modal-header bg-danger text-white';
                title.innerHTML = '<i class="bi bi-cart-x-fill me-2"></i> Add Expense';
                submitBtn.className = 'btn btn-danger rounded-pill px-5 fw-bold';
                submitBtn.innerText = 'Save Expense';
            }

            new bootstrap.Modal(modalEl).show();
        }

        // Toast
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.className = `toast align-items-center text-white border-0 <?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>`;
            new bootstrap.Toast(toastEl).show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>