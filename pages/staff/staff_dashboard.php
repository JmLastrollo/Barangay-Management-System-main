<?php
// 1. SECURITY & SESSION CHECK
session_start();
include '../../backend/db_connection.php'; // Siguraduhin tama ang path

// Check kung naka-login at kung 'Staff' ang role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../index.php"); // Ibalik sa login pag unauthorized
    exit();
}

// 2. DATA COUNT QUERIES (Para sa Cards)

// A. Pending Certificates/Issuance
$query_issuance = "SELECT COUNT(*) as total FROM issuance_requests WHERE status = 'Pending'";
$res_issuance = mysqli_query($conn, $query_issuance);
$pending_issuance = mysqli_fetch_assoc($res_issuance)['total'];

// B. Pending Account Approvals
$query_accounts = "SELECT COUNT(*) as total FROM residents WHERE account_status = 'Pending'"; // Check column name sa DB mo
$res_accounts = mysqli_query($conn, $query_accounts);
$pending_accounts = mysqli_fetch_assoc($res_accounts)['total'];

// C. Active Blotter Cases
$query_blotter = "SELECT COUNT(*) as total FROM blotter_records WHERE status = 'Active' OR status = 'Pending'";
$res_blotter = mysqli_query($conn, $query_blotter);
$active_blotter = mysqli_fetch_assoc($res_blotter)['total'];

// D. Total Residents (Reference)
$query_total_res = "SELECT COUNT(*) as total FROM residents";
$res_total_res = mysqli_query($conn, $query_total_res);
$total_residents = mysqli_fetch_assoc($res_total_res)['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Dashboard | Brgy. Langkaan II</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/style.css"> <link rel="stylesheet" href="../../assets/css/sidebar.css"> <style>
        /* Custom Dashboard Card Styles */
        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="d-flex">
        
        <?php include '../../includes/staff_sidebar.php'; ?>

        <div class="main-content flex-grow-1 p-4" style="background-color: #f8f9fa; min-height: 100vh;">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark">Staff Dashboard</h2>
                    <p class="text-muted">Welcome back, Staff! Here's your summary for today.</p>
                </div>
                <div class="date-display">
                    <span class="fw-bold text-secondary"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </div>

            <div class="row g-4 mb-4">
                
                <div class="col-md-3">
                    <div class="card card-stat bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold">Pending Issuance</h6>
                                <h2 class="fw-bold text-warning mb-0"><?php echo $pending_issuance; ?></h2>
                            </div>
                            <div class="icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-file-earmark-text-fill"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="staff_issuance.php" class="text-decoration-none small text-warning">View Requests &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stat bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold">Account Approval</h6>
                                <h2 class="fw-bold text-primary mb-0"><?php echo $pending_accounts; ?></h2>
                            </div>
                            <div class="icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-person-check-fill"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="staff_account_approval.php" class="text-decoration-none small text-primary">Verify Accounts &rarr;</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-stat bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold">Active Cases</h6>
                                <h2 class="fw-bold text-danger mb-0"><?php echo $active_blotter; ?></h2>
                            </div>
                            <div class="icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="bi bi-gavel"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="staff_rec_blotter.php" class="text-decoration-none small text-danger">Manage Records &rarr;</a>
                        </div>
                    </div>
                </div>

                 <div class="col-md-3">
                    <div class="card card-stat bg-white p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted text-uppercase small fw-bold">Total Residents</h6>
                                <h2 class="fw-bold text-success mb-0"><?php echo number_format($total_residents); ?></h2>
                            </div>
                            <div class="icon-box bg-success bg-opacity-10 text-success">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="staff_resident_list.php" class="text-decoration-none small text-success">View Masterlist &rarr;</a>
                        </div>
                    </div>
                </div>

            </div> <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 fw-bold text-primary"><i class="bi bi-clock-history me-2"></i>Recent Issuance Requests</h6>
                    <a href="staff_issuance.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Resident Name</th>
                                    <th>Document Type</th>
                                    <th>Date Requested</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Fetch latest 5 requests
                                $latest_sql = "SELECT * FROM issuance_requests ORDER BY request_date DESC LIMIT 5";
                                $latest_res = mysqli_query($conn, $latest_sql);

                                if(mysqli_num_rows($latest_res) > 0){
                                    while($row = mysqli_fetch_assoc($latest_res)){
                                        $status_color = ($row['status'] == 'Pending') ? 'bg-warning text-dark' : 'bg-secondary';
                                        
                                        echo "<tr>";
                                        echo "<td class='ps-4 fw-bold'>" . $row['resident_name'] . "</td>";
                                        echo "<td>" . $row['document_type'] . "</td>";
                                        echo "<td>" . date('M d, Y', strtotime($row['request_date'])) . "</td>";
                                        echo "<td><span class='badge rounded-pill $status_color'>" . $row['status'] . "</span></td>";
                                        echo "<td><a href='staff_issuance_view.php?id=".$row['id']."' class='btn btn-sm btn-primary'><i class='bi bi-eye'></i></a></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center py-4 text-muted'>No recent requests found.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div> </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>