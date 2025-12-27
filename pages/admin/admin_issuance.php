<?php
require_once '../../backend/auth_admin.php';
require_once '../../backend/config.php';

$requests = iterator_to_array(
    $issuanceCollection->aggregate([
        [
            '$match' => [
                'status' => ['$ne' => 'Archived']
            ]
        ],
        [
            '$addFields' => [
                'normalized_time' => [
                    '$cond' => [
                        [
                            '$eq' => [
                                [ '$strLenCP' => '$request_time' ],
                                8
                            ]
                        ],
                        '$request_time',
                        [
                            '$concat' => [
                                '$request_time',
                                ':00'
                            ]
                        ]
                    ]
                ]
            ]
        ],
        [
            '$addFields' => [
                'full_request_datetime' => [
                    '$toDate' => [
                        '$concat' => [
                            '$request_date',
                            "T",
                            '$normalized_time'
                        ]
                    ]
                ]
            ]
        ],
        [
            '$sort' => [
                'full_request_datetime' => -1
            ]
        ]
    ])
);


?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BMS - Admin Issuance</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css?v=1">
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
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php" class="active"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Records
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
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title"><span class="green">ISSUANCE</span></h1>

        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="search-box d-flex">
                <input type="text" id="searchInput" placeholder="Search for Resident Name or Document Type" class="form-control">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </div>
            <a href="admin_issuance_archive.php" class="btn btn-secondary">
                <i class="bi bi-archive"></i> Archive
            </a>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Resident Name</th>
                    <th>Document Type</th>
                    <th>Request Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="issuanceTable">
                <?php if(empty($requests)): ?>
                    <tr><td colspan="5" class="text-center">No requests found.</td></tr>
                <?php else: ?>
                    <?php foreach($requests as $r): ?>
                        <tr data-id="<?= $r->_id ?>"
                            data-document_type="<?= htmlspecialchars($r->document_type) ?>"
                            data-status="<?= htmlspecialchars($r->status) ?>"
                            data-purpose="<?= htmlspecialchars($r->purpose ?? '') ?>"
                            data-certificate_for="<?= htmlspecialchars($r->certificate_for ?? '') ?>"
                            data-certificate_for_fullname="<?= htmlspecialchars($r->certificate_for_fullname ?? '') ?>"
                            data-business_name="<?= htmlspecialchars($r->business_name ?? '') ?>"
                            data-business_location="<?= htmlspecialchars($r->business_location ?? '') ?>"
                        >
                            <td><?= htmlspecialchars($r->resident_name) ?></td>
                            <td><?= htmlspecialchars($r->document_type) ?></td>
                            <td><?= htmlspecialchars($r->request_date . ' ' . $r->request_time) ?></td>
                            <td>
                                <span class="status 
                                    <?= strtolower($r->status) === 'pending' ? 'pending' : '' ?>
                                    <?= strtolower($r->status) === 'ready for pickup' ? 'ready' : '' ?>
                                    <?= strtolower($r->status) === 'rejected' ? 'decline' : '' ?>
                                    <?= strtolower($r->status) === 'received' ? 'received' : '' ?>
                                ">
                                    <?= ucwords($r->status) ?>
                                </span>
                            </td>
                            <td class="d-flex gap-1">
                                <a href="admin_issuance_print.php?id=<?= $r->_id ?>" target="_blank" class="btn btn-sm btn-warning"><i class="bi bi-printer"></i></a>
                                <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#viewModal"
                                    onclick="viewRequest('<?= $r->_id ?>')"><i class="bi bi-eye"></i></button>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                    onclick="editRequest('<?= $r->_id ?>')"><i class="bi bi-pencil-square"></i></button>
                                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#archiveModal"
                                    onclick="document.getElementById('a_id').value='<?= $r->_id ?>'"><i class="bi bi-archive"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5 class="modal-title">Request Details</h5>
            <hr>
            <div class="modal-body">
                <p><strong>Resident Name:</strong> <span id="v_name"></span></p>
                <p><strong>Document Type:</strong> <span id="v_doc"></span></p>
                <p><strong>Request Date:</strong> <span id="v_date"></span></p>
                <p><strong>Request Time:</strong> <span id="v_time"></span></p>
                <p><strong>Status:</strong> <span id="v_status"></span></p>
                <div id="v_extra"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5 class="modal-title">Edit Issuance</h5>
            <div class="modal-body">
                <input type="hidden" id="edit_id">
                <p><strong>Resident Name:</strong> <span id="e_name"></span></p>
                <p><strong>Document Type:</strong> <span id="e_doc"></span></p>

                <div class="mb-3" id="edit_certificate_for_div" style="display:none;">
                    <label class="form-label">Certificate For</label>
                    <input type="text" id="e_certificate_for" class="form-control">
                </div>

                <div class="mb-3" id="edit_certificate_purpose_div" style="display:none;">
                    <label class="form-label">Purpose</label>
                    <input type="text" id="e_certificate_purpose" class="form-control">
                </div>

                <div class="mb-3" id="edit_business_name_div" style="display:none;">
                    <label class="form-label">Business Name</label>
                    <input type="text" id="e_business_name" class="form-control">
                </div>

                <div class="mb-3" id="edit_business_location_div" style="display:none;">
                    <label class="form-label">Business Location</label>
                    <input type="text" id="e_business_location" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="statusSelect" class="form-label">Update Status</label>
                    <select id="statusSelect" class="form-select">
                        <option value="Pending">Pending</option>
                        <option value="Ready for Pickup">Ready For Pickup</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Received">Received</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="updateStatus()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content p-3">
            <h5>Archive Request</h5>
            <p>Are you sure you want to archive this request?</p>
            <form action="../../backend/admin_issuance_update.php" method="POST">
                <input type="hidden" name="issuance_id" id="a_id">
                <input type="hidden" name="status" value="archived">
                <button type="submit" class="btn btn-secondary">Archive</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_PATH = "/Barangay-Management-System/backend/";

// View request function
function viewRequest(id){
    fetch(`${BASE_PATH}admin_issuance_get_single.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('v_name').textContent = data.resident_name || 'N/A';
        document.getElementById('v_doc').textContent = data.document_type || 'N/A';
        document.getElementById('v_date').textContent = data.request_date || 'N/A';
        document.getElementById('v_time').textContent = data.request_time || 'N/A';
        document.getElementById('v_status').textContent = data.status || 'Pending';

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
    });
}

// Edit request function
function editRequest(id){
    fetch(`${BASE_PATH}admin_issuance_get_single.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        document.getElementById('edit_id').value = id;
        document.getElementById('e_name').textContent = data.resident_name || 'N/A';
        document.getElementById('e_doc').textContent = data.document_type || 'N/A';
        document.getElementById('statusSelect').value = data.status || 'Pending';

        // show/hide relevant fields
        document.getElementById('edit_certificate_for_div').style.display = data.document_type === 'Certificate of Indigency' ? 'block' : 'none';
        document.getElementById('edit_certificate_purpose_div').style.display = data.document_type === 'Certificate of Indigency' ? 'block' : 'none';
        document.getElementById('edit_business_name_div').style.display = data.document_type === 'Barangay Business Clearance' ? 'block' : 'none';
        document.getElementById('edit_business_location_div').style.display = data.document_type === 'Barangay Business Clearance' ? 'block' : 'none';

        document.getElementById('e_certificate_for').value = data.certificate_for || '';
        document.getElementById('e_certificate_purpose').value = data.purpose || '';
        document.getElementById('e_business_name').value = data.business_name || '';
        document.getElementById('e_business_location').value = data.business_location || '';
    });
}

// Update status function (also saves other fields)
function updateStatus(){
    const id = document.getElementById('edit_id').value;
    const status = document.getElementById('statusSelect').value;
    const payload = new FormData();
    payload.append('ajax', 1);
    payload.append('issuance_id', id);
    payload.append('status', status);

    // add editable fields
    payload.append('certificate_for', document.getElementById('e_certificate_for').value);
    payload.append('purpose', document.getElementById('e_certificate_purpose').value);
    payload.append('business_name', document.getElementById('e_business_name').value);
    payload.append('business_location', document.getElementById('e_business_location').value);

    fetch(BASE_PATH + 'admin_issuance_update.php', {method:'POST', body: payload})
    .then(res => res.json())
    .then(data => {
        if(data.status==='success'){
            const row = document.querySelector(`#issuanceTable tr[data-id='${id}']`);
            if(row) row.querySelector('span.status').textContent = status; 
            const statusSpan = row.querySelector('span.status');
            statusSpan.textContent = status;

            statusSpan.classList.remove("pending", "ready", "received", "decline");


            if(status === "Pending") statusSpan.classList.add("pending");
            if(status === "Ready for Pickup") statusSpan.classList.add("ready");
            if(status === "Rejected") statusSpan.classList.add("decline");
            if(status === "Received") statusSpan.classList.add("received");

            bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
        }
    });
}

// Sidebar toggle
document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', () => btn.parentElement.classList.toggle('active'));
});
</script>
</body>
</html>