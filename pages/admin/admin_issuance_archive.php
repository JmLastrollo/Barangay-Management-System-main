<?php
require_once '../../backend/auth_admin.php';
require_once '../../backend/config.php';

$searchQuery = $_GET["search"] ?? "";

// Filter archived only
$filter = ["status" => "Archived"];
if($searchQuery){
    $filter['$or'] = [
        ["resident_name"=> ['$regex'=>$searchQuery, '$options'=>'i']],
        ["document_type"=> ['$regex'=>$searchQuery, '$options'=>'i']],
    ];
}

$requests = iterator_to_array($issuanceCollection->find($filter));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BMS - Archived Issuance</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css" />
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="">
        <div>
            <h3>Anonymous 1</h3>
            <small>admin@email.com</small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="dropdown-container">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Archives
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_announcement_archive.php"><i class="bi bi-megaphone"></i> Announcement</a>
                <a href="admin_officials_archive.php"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php" class="active"><i class="bi bi-file-earmark-text"></i> Issuance</a>
                <a href="admin_rec_complaints_archive.php"><i class="bi bi-file-earmark-text"></i> Complaints</a>
                <a href="admin_rec_blotter_archive.php"><i class="bi bi-file-earmark-text"></i> Blotter</a>
            </div>
            <a href="admin_issuance.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">ISSUANCE</span></h1>
    </div>

    <div class="content">
        <form method="GET" class="search-box d-flex mb-3">
            <input type="text" id="searchInput" name="search" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search for Resident Name or Document Type...">
            <button class="search-btn"><i class="bi bi-search"></i></button>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Resident Name</th>
                    <th>Document Type</th>
                    <th>Request Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="issuanceTable">
            <?php if(empty($requests)): ?>
                <tr><td colspan="5" class="text-center">No archived requests found.</td></tr>
            <?php else: ?>
                <?php foreach($requests as $r): ?>
                    <tr>
                        <td class="resident-name"><?= htmlspecialchars($r->resident_name) ?></td>
                        <td class="document-type"><?= htmlspecialchars($r->document_type) ?></td>
                        <td><?= htmlspecialchars($r->request_date) ?></td>
                        <td><?= htmlspecialchars($r->status) ?></td>
                        <td class="d-flex gap-1">
                            <!-- VIEW BUTTON -->
                            <button class="btn btn-sm btn-info text-white" 
                                    onclick='viewArchived(<?= json_encode($r) ?>)'>
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- RESTORE BUTTON -->
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#restoreModal" 
                                onclick="document.getElementById('r_id').value='<?= $r->_id ?>'">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>

                            <!-- DELETE BUTTON -->
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                onclick="document.getElementById('d_id').value='<?= $r->_id ?>'">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>

        </table>
    </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h5 class="modal-title">Request Details</h5>
        <hr>
        <div class="modal-body">
            <p><strong>Resident Name:</strong> <span id="v_name"></span></p>
            <p><strong>Document Type:</strong> <span id="v_doc"></span></p>
            <p><strong>Request Date:</strong> <span id="v_date"></span></p>
            <p><strong>Status:</strong> <span id="v_status"></span></p>
            <div id="v_extra"></div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
    </div>
  </div>
</div>



<!-- Restore Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Restore Request</h4>
        <p>Are you sure you want to restore this request?</p>
        <form action="../../backend/admin_issuance_update.php" method="POST">
            <input type="hidden" name="issuance_id" id="r_id">
            <input type="hidden" name="status" value="Active">
            <button class="btn btn-success" type="submit">Restore</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
    </div>
  </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Delete Permanently</h4>
        <p>Are you sure you want to permanently delete this request?</p>
        <form action="../../backend/admin_issuance_delete.php" method="POST">
            <input type="hidden" name="issuance_id" id="d_id">
            <button class="btn btn-danger" type="submit">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<script>
const BASE_PATH = "/Barangay-Management-System/backend/";

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('ctive');
}

document.querySelectorAll('.dropdown-container').forEach(container => {
    if (container.querySelector('.dropdown-content a.active')) {
        container.classList.add('active');
    }
});

document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        this.parentElement.classList.toggle('active');
    });
});

// View archived request function
function viewArchived(data){
    document.getElementById('v_name').textContent = data.resident_name || 'N/A';
    document.getElementById('v_doc').textContent = data.document_type || 'N/A';
    document.getElementById('v_date').textContent = data.request_date || 'N/A';
    document.getElementById('v_status').textContent = data.status || 'Archived';

    let extraHTML = '';
    if(data.document_type === 'Certificate of Indigency'){
        extraHTML += `<p><strong>Certificate For:</strong> ${data.certificate_for || '-'}</p>`;
        extraHTML += `<p><strong>Purpose:</strong> ${data.purpose || '-'}</p>`;
        extraHTML += `<p><strong>Reason:</strong> ${data.reason || '-'}</p>`;
    } else if(data.document_type === 'Barangay Business Clearance'){
        extraHTML += `<p><strong>Business Name:</strong> ${data.business_name || '-'}</p>`;
        extraHTML += `<p><strong>Business Location:</strong> ${data.business_location || '-'}</p>`;
        extraHTML += `<p><strong>Reason:</strong> ${data.reason || '-'}</p>`;
    } else {
        extraHTML += `<p><strong>Reason:</strong> ${data.reason || '-'}</p>`;
    }

    document.getElementById('v_extra').innerHTML = extraHTML;
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}


function openDeleteModal(id) {
    document.getElementById('d_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function openRestoreModal(id) {
    document.getElementById('r_id').value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

document.getElementById("searchInput").addEventListener("keyup", function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#issuanceTable tr");

    rows.forEach(row => {
        if (!row.cells.length) return;

        let name = row.querySelector(".resident-name").textContent.toLowerCase();
        let docType = row.querySelector(".document-type").textContent.toLowerCase();

        row.style.display = (name.includes(filter) || docType.includes(filter)) ? "" : "none";
    });
});



</script>
</body>
</html>
