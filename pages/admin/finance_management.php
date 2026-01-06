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
            box-shadow: 0 10px 25px rgba(13, 110, 253, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .balance-card h1 { font-size: 3.5rem; font-weight: 800; margin: 0; font-family: 'Segoe UI', sans-serif; letter-spacing: -1px; }
        .balance-card .label { text-transform: uppercase; letter-spacing: 2px; font-size: 0.85rem; opacity: 0.8; font-weight: 600; }
        .balance-card .icon-bg {
            position: absolute;
            right: -20px; bottom: -30px;
            font-size: 9rem; opacity: 0.1;
            transform: rotate(-15deg);
        }

        .mini-stat-card {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }
        .mini-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .icon-box {
            width: 55px; height: 55px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-right: 15px;
            flex-shrink: 0;
        }
        .icon-income { background: linear-gradient(135deg, #d1e7dd, #a3cfbb); color: #0f5132; }
        .icon-expense { background: linear-gradient(135deg, #f8d7da, #f1aeb5); color: #842029; }
        
        .amount-small { font-weight: 700; font-size: 1.6rem; color: #2c3e50; line-height: 1.2; }

        /* --- UPDATED BUTTON STYLES --- */
        .btn-action {
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Add Income Button */
        .btn-income { 
            background: linear-gradient(135deg, #198754, #20c997); 
            color: white; 
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
        }
        .btn-income:hover { 
            background: linear-gradient(135deg, #157347, #198754); 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
            color: white;
        }
        
        /* Add Expense Button */
        .btn-expense { 
            background: linear-gradient(135deg, #dc3545, #ef5350); 
            color: white; 
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-expense:hover { 
            background: linear-gradient(135deg, #bb2d3b, #dc3545); 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
            color: white;
        }

        .btn-action i { font-size: 1.1rem; }

        .table-amount { font-family: 'Consolas', monospace; font-weight: 700; letter-spacing: -0.5px; }
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

        <div class="content container-fluid pb-5">
            
            <div class="row g-4 mb-4">
                <div class="col-lg-6">
                    <div class="balance-card h-100 d-flex flex-column justify-content-center">
                        <span class="label">Total Available Fund</span>
                        <h1>₱ <?= number_format($balance, 2) ?></h1>
                        <div class="mt-2 small"><i class="bi bi-check-circle-fill me-1"></i> Updated Real-time</div>
                        <i class="bi bi-wallet-fill icon-bg"></i>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="d-flex flex-column gap-3 h-100">
                        <div class="mini-stat-card h-50">
                            <div class="icon-box icon-income"><i class="bi bi-arrow-down-left"></i></div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Collections</small>
                                <div class="amount-small">₱ <?= number_format($total_collection, 2) ?></div>
                            </div>
                        </div>
                        <div class="mini-stat-card h-50">
                            <div class="icon-box icon-expense"><i class="bi bi-arrow-up-right"></i></div>
                            <div>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Expenses</small>
                                <div class="amount-small">₱ <?= number_format($total_expense, 2) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-3 gap-3 bg-white p-3 rounded-4 shadow-sm">
                <form method="GET" class="d-flex gap-2 w-100 w-md-auto">
                    <select name="filter_type" class="form-select border-0 bg-light fw-bold" style="min-width: 150px;" onchange="this.form.submit()">
                        <option value="All" <?= $filterType == 'All' ? 'selected' : '' ?>>All Transactions</option>
                        <option value="Collection" <?= $filterType == 'Collection' ? 'selected' : '' ?>>Collections Only</option>
                        <option value="Expense" <?= $filterType == 'Expense' ? 'selected' : '' ?>>Expenses Only</option>
                    </select>
                    <select name="sort_order" class="form-select border-0 bg-light fw-bold" style="min-width: 150px;" onchange="this.form.submit()">
                        <option value="date_desc" <?= $sortOrder == 'date_desc' ? 'selected' : '' ?>>Latest First</option>
                        <option value="date_asc" <?= $sortOrder == 'date_asc' ? 'selected' : '' ?>>Oldest First</option>
                        <option value="amount_high" <?= $sortOrder == 'amount_high' ? 'selected' : '' ?>>Highest Amount</option>
                    </select>
                </form>

                <div class="d-flex gap-2 w-100 w-md-auto justify-content-end">
                    <button class="btn-action btn-income w-100 w-md-auto" onclick="openModal('Collection')">
                        <i class="bi bi-plus-circle-fill"></i> Add Income
                    </button>
                    <button class="btn-action btn-expense w-100 w-md-auto" onclick="openModal('Expense')">
                        <i class="bi bi-dash-circle-fill"></i> Add Expense
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="text-uppercase small text-muted">
                                    <th class="ps-4 py-3">Date</th>
                                    <th class="py-3">Description</th>
                                    <th class="py-3">Type</th>
                                    <th class="py-3">Amount</th>
                                    <th class="py-3">Recorded By</th>
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
                                        $bgBadge = $isCol ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                        $icon = $isCol ? 'bi-arrow-down-left' : 'bi-arrow-up-right';
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= date('M d, Y', strtotime($row['transaction_date'])) ?></div>
                                            <small class="text-muted"><?= date('h:i A', strtotime($row['transaction_date'])) ?></small>
                                        </td>
                                        <td class="text-secondary fw-medium"><?= htmlspecialchars($row['description']) ?></td>
                                        <td>
                                            <span class="badge <?= $bgBadge ?> rounded-pill px-3 py-2">
                                                <i class="bi <?= $icon ?> me-1"></i><?= strtoupper($row['transaction_type']) ?>
                                            </span>
                                        </td>
                                        <td class="table-amount <?= $color ?> fs-5">
                                            <?= $sign ?> ₱ <?= number_format($row['amount'], 2) ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded-circle text-center me-2 d-flex align-items-center justify-content-center" style="width:32px; height:32px;">
                                                    <i class="bi bi-person-fill text-secondary"></i>
                                                </div>
                                                <span class="small text-dark fw-bold">
                                                    <?= $row['first_name'] ? htmlspecialchars($row['first_name']) : 'Admin' ?>
                                                </span>
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
            <form action="../../backend/finance_add.php" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                
                <div class="modal-header text-white border-0" id="modalHeader">
                    <h5 class="modal-title fw-bold" id="modalTitle"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <input type="hidden" name="transaction_type" id="transType">

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small">AMOUNT (PHP)</label>
                        <div class="input-group input-group-lg shadow-sm rounded-3">
                            <span class="input-group-text bg-white border-end-0 text-muted">₱</span>
                            <input type="number" step="0.01" name="amount" class="form-control border-start-0 fw-bold fs-4" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary small">DESCRIPTION</label>
                        <textarea name="description" class="form-control shadow-sm" rows="3" placeholder="Enter transaction details..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">DATE</label>
                        <input type="datetime-local" name="transaction_date" class="form-control shadow-sm" value="<?= date('Y-m-d\TH:i') ?>" required>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn rounded-pill px-5 fw-bold text-white shadow-sm" id="modalSubmitBtn">Save Transaction</button>
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
                title.innerHTML = '<i class="bi bi-wallet2 me-2"></i> Add Income';
                submitBtn.className = 'btn btn-success rounded-pill px-5 fw-bold shadow-sm';
                submitBtn.innerText = 'Save Collection';
            } else {
                header.className = 'modal-header bg-danger text-white';
                title.innerHTML = '<i class="bi bi-cart-x me-2"></i> Add Expense';
                submitBtn.className = 'btn btn-danger rounded-pill px-5 fw-bold shadow-sm';
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