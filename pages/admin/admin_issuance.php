<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../admin_login.php");
    exit();
}

require_once '../../backend/db_connect.php';

// 2. Fetch Requests
$sql = "SELECT i.*, 
               CONCAT(rp.first_name, ' ', rp.last_name) as current_resident_name,
               p.amount, 
               p.payment_method, 
               p.reference_no, 
               p.payment_status 
        FROM issuance i
        LEFT JOIN resident_profiles rp ON i.resident_id = rp.resident_id
        LEFT JOIN payments p ON i.issuance_id = p.issuance_id
        WHERE i.status != 'Archived'
        ORDER BY i.request_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Issuance</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css?v=1">
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <small><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'admin@email.com' ?></small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php" class="active"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <span><i class="bi bi-file-earmark-text"></i> Records</span>
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php">Blotter</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">
    
    <div class="header">
        <div class="hamburger" onclick="document.querySelector('.sidebar').classList.toggle('active')">☰</div>
        <h1 class="header-title"><span class="green">ISSUANCE</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            
            <div class="d-flex align-items-center gap-2">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search Resident or Doc Type" class="form-control">
                    <button><i class="bi bi-search"></i></button>
                </div>
            </div>

            <div class="mt-2 mt-md-0">
                <a href="admin_issuance_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archives
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Resident Name</th>
                        <th>Document Type</th>
                        <th>Purpose / Details</th>
                        <th>Date Requested</th>
                        <th>Status</th>
                        <th>Payment</th> 
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="issuanceTable">
                    <?php if (empty($requests)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No pending requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach($requests as $r): 
                            $name = $r['current_resident_name'] ?? $r['resident_name'];
                            $date = date('M d, Y h:i A', strtotime($r['request_date']));
                        ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($name) ?></td>
                            <td>
                                <?= htmlspecialchars($r['document_type']) ?>
                                <br>
                                <small class="text-muted">Price: ₱<?= number_format($r['price'], 2) ?></small>
                            </td>
                            <td>
                                <small><?= htmlspecialchars(substr($r['purpose'], 0, 50)) ?>...</small>
                                <?php if(!empty($r['business_name'])): ?>
                                    <br><small class="text-primary"><i class="bi bi-shop"></i> <?= htmlspecialchars($r['business_name']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= $date ?></td>
                            <td>
                                <span class="badge rounded-pill 
                                    <?= $r['status'] == 'Pending' ? 'text-bg-warning' : '' ?>
                                    <?= $r['status'] == 'Ready for Pickup' ? 'text-bg-primary' : '' ?>
                                    <?= $r['status'] == 'Received' ? 'text-bg-success' : '' ?>
                                    <?= $r['status'] == 'Rejected' ? 'text-bg-danger' : '' ?>
                                ">
                                    <?= htmlspecialchars($r['status']) ?>
                                </span>
                            </td>
                            
                            <td>
                                <?php if ($r['amount'] > 0): ?>
                                    <div class="d-flex flex-column" style="font-size: 0.85rem;">
                                        <span class="fw-bold">₱<?= number_format($r['amount'], 2) ?></span>
                                        <small class="text-muted"><?= htmlspecialchars($r['payment_method']) ?></small>
                                        
                                        <div class="mt-1">
                                            <?php if($r['payment_status'] == 'Paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif($r['payment_status'] == 'Pending'): ?>
                                                <span class="badge bg-warning text-dark">Verify</span>
                                            <?php elseif($r['payment_status'] == 'Rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($r['payment_status']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">Unpaid / Free</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info text-white" onclick='viewRequest(<?= json_encode($r) ?>)' title="View"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-primary" onclick="editStatus(<?= $r['issuance_id'] ?>, '<?= $r['status'] ?>')" title="Update Status"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-warning" onclick="openArchiveModal(<?= $r['issuance_id'] ?>)" title="Archive"><i class="bi bi-archive"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBody">
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="printLink" target="_blank" class="btn btn-dark"><i class="bi bi-printer"></i> Print</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <label class="form-label fw-bold">Select New Status:</label>
                <select id="edit_status" class="form-select">
                    <option value="Pending">Pending</option>
                    <option value="Ready for Pickup">Ready for Pickup</option>
                    <option value="Received">Received</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="saveStatus()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Archive Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive this request?</p>
            </div>
            <div class="modal-footer">
                <form action="../../backend/admin_issuance_update.php" method="POST">
                    <input type="hidden" name="issuance_id" id="archive_id">
                    <input type="hidden" name="status" value="Archived"> 
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Archive</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- DROPDOWNS & SIDEBAR ---
    document.querySelector('.hamburger').addEventListener('click', () => {
        document.querySelector('.sidebar').classList.toggle('active');
    });
    
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.classList.toggle('active');
        });
    });

    // --- MODAL LOGIC ---
    function viewRequest(data) {
        let content = `
            <div class="mb-2"><strong>Resident:</strong> ${data.current_resident_name || data.resident_name}</div>
            <div class="mb-2"><strong>Document:</strong> ${data.document_type}</div>
            <div class="mb-2"><strong>Price:</strong> ₱${data.price}</div>
            <div class="mb-2"><strong>Status:</strong> ${data.status}</div>
            <hr>
            <div class="mb-2"><strong>Purpose:</strong></div>
            <div class="p-2 bg-light border rounded mb-2">${data.purpose}</div>
        `;
        
        if(data.business_name) {
            content += `
                <div class="mb-2"><strong>Business Name:</strong> ${data.business_name}</div>
                <div class="mb-2"><strong>Location:</strong> ${data.business_location}</div>
            `;
        }
        
        document.getElementById('viewBody').innerHTML = content;
        
        // Setup Print Link (kung meron kang print page)
        document.getElementById('printLink').href = `admin_issuance_print.php?id=${data.issuance_id}`;
        
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }

    function editStatus(id, status) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_status').value = status;
        new bootstrap.Modal(document.getElementById('statusModal')).show();
    }

    function openArchiveModal(id) {
        document.getElementById('archive_id').value = id;
        new bootstrap.Modal(document.getElementById('archiveModal')).show();
    }

    function saveStatus() {
        const id = document.getElementById('edit_id').value;
        const status = document.getElementById('edit_status').value;
        const formData = new FormData();
        formData.append('issuance_id', id);
        formData.append('status', status);

        // UPDATED: Nakaturo na sa existing file mo
        fetch('../../backend/admin_issuance_update.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') location.reload();
            else alert('Error updating status');
        });
    }

    // --- SEARCH ---
    document.getElementById('searchInput').addEventListener('keyup', function() {
        const value = this.value.toLowerCase();
        document.querySelectorAll('#issuanceTable tr').forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });
</script>

</body>
</html>