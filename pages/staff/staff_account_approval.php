<?php
// pages/staff/staff_account_approval.php
include '../../backend/auth_staff.php'; // Security: Staff Only
require_once '../../backend/db_connect.php'; 

// 2. Fetch Pending Accounts
try {
    $stmt = $conn->prepare("
        SELECT u.user_id, u.email, u.created_at, 
               r.first_name, r.last_name, r.contact_no, r.address, r.voter_status, r.image 
        FROM users u 
        JOIN resident_profiles r ON u.user_id = r.user_id 
        WHERE u.status = 'Pending' AND u.role = 'Resident'
        ORDER BY u.created_at ASC
    ");
    $stmt->execute();
    $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $pendingUsers = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff - Account Approval</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css"> <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>

    <?php include '../../includes/staff_sidebar.php'; ?>

    <main id="main-content">
        <div class="header">
            <div class="d-flex align-items-center">
                <h1 class="header-title">ACCOUNT <span class="green">APPROVAL</span></h1>
            </div>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4">
                    
                    <h5 class="mb-4 fw-bold text-primary">
                        <i class="bi bi-hourglass-split me-2"></i>Pending Registrations
                    </h5>

                    <?php if (empty($pendingUsers)): ?>
                        <div class="alert alert-light text-center py-5 border-0">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="mt-3 text-muted">No pending account requests at the moment.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Resident Name</th>
                                        <th scope="col">Email Address</th>
                                        <th scope="col">Date Registered</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-secondary-subtle rounded-circle d-flex align-items-center justify-content-center text-secondary fw-bold me-3" style="width: 40px; height: 40px;">
                                                    <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                                    <small class="text-muted"><?= htmlspecialchars($user['contact_no']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= date("M d, Y • h:i A", strtotime($user['created_at'])) ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewModal<?= $user['user_id'] ?>">
                                                <i class="bi bi-eye"></i> View
                                            </button>

                                            <form action="../../backend/account_action.php" method="POST" class="d-inline">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="bi bi-check-lg"></i>
                                                </button>
                                            </form>

                                            <form action="../../backend/account_action.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to reject this account?');">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                <input type="hidden" name="action" value="decline">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Reject">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="viewModal<?= $user['user_id'] ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Resident Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-center mb-3">
                                                        <div class="bg-light rounded-circle mx-auto d-flex align-items-center justify-content-center display-6 fw-bold text-primary" style="width: 80px; height: 80px;">
                                                            <?= substr($user['first_name'], 0, 1) ?>
                                                        </div>
                                                        <h5 class="mt-2 fw-bold"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                                                        <span class="badge bg-warning text-dark">Pending Approval</span>
                                                    </div>
                                                    <hr>
                                                    <div class="row g-2">
                                                        <div class="col-4 text-muted small">Email:</div>
                                                        <div class="col-8 fw-medium"><?= htmlspecialchars($user['email']) ?></div>
                                                        <div class="col-4 text-muted small">Contact:</div>
                                                        <div class="col-8 fw-medium"><?= htmlspecialchars($user['contact_no']) ?></div>
                                                        <div class="col-4 text-muted small">Address:</div>
                                                        <div class="col-8 fw-medium"><?= htmlspecialchars($user['address']) ?></div>
                                                        <div class="col-4 text-muted small">Voters:</div>
                                                        <div class="col-8 fw-medium"><?= htmlspecialchars($user['voter_status']) ?></div>
                                                        <div class="col-4 text-muted small">Registered:</div>
                                                        <div class="col-8 fw-medium"><?= date("F j, Y g:i a", strtotime($user['created_at'])) ?></div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <form action="../../backend/account_action.php" method="POST" class="w-100 d-flex gap-2">
                                                        <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                        <button type="submit" name="action" value="decline" class="btn btn-outline-danger flex-grow-1" onclick="return confirm('Reject this account?')">Reject</button>
                                                        <button type="submit" name="action" value="approve" class="btn btn-success flex-grow-1">Approve Account</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple Toast Script (If not reusing external file)
        function showToast(message, type = "success") {
            const t = document.getElementById("toast");
            let bgColor = type === 'success' ? '#198754' : '#dc3545';
            
            t.style.backgroundColor = bgColor;
            t.className = "toast show"; 
            t.innerHTML = `<div class="d-flex align-items-center text-white p-3"><span class="fw-bold">${message}</span></div>`;
            
            setTimeout(() => { t.classList.remove("show"); }, 3000);
        }
    </script>
    <?php if (isset($_SESSION['toast'])): ?>
    <script>
        showToast("<?= htmlspecialchars(is_array($_SESSION['toast']) ? $_SESSION['toast']['msg'] : $_SESSION['toast']) ?>", "<?= htmlspecialchars($_SESSION['toast']['type'] ?? 'success') ?>");
    </script>
    <?php unset($_SESSION['toast']); endif; ?>

</body>
</html>