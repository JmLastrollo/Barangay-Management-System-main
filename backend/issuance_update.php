<?php 
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/config.php'; 

$email = $_SESSION['email'];
$user = $usersCollection->findOne(['email' => $email]);
if (!$user) die("Error: User not found.");

$resident = $residentsCollection->findOne(['user_id' => $user['_id']]);
if (!$resident) die("Error: Resident record not found.");

$residentId = (string)$resident['_id'];
$fullname = $resident['first_name'] . ' ' . $resident['last_name'];

// Fetch all requests for this resident
$requests = iterator_to_array($issuanceCollection->find(
    ['resident_id' => $residentId],
    ['sort' => ['request_date' => -1]]
));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Resident Requests</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css">
<link rel="stylesheet" href="../../css/toast.css">
<style>
td { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
</style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <?php
        $profileImg = isset($resident['profile_image']) && $resident['profile_image'] !== ""
            ? "../../uploads/residents/" . $resident['profile_image']
            : "../../assets/img/profile.jpg";
        ?>
        <img src="<?= $profileImg ?>" alt="">
        <div>
            <h3><?= $fullname ?></h3>
            <small><?= $resident['email'] ?></small>
        </div>
    </div>
    <div class="sidebar-menu">
        <a href="resident_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="resident_rqs_service.php" class="active"><i class="bi bi-newspaper"></i> Request Service</a>
        <a href="../../index.php"><i class="bi bi-arrow-down-left"></i> Return to Homepage</a>
    </div>
    <div class="sidebar-bottom">
        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">REQUEST <span class="green">SERVICE</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
            <div class="d-flex gap-2 mb-2">
                <input type="text" id="searchInput" placeholder="Search for Document Type..." class="form-control">
                <select id="statusFilter" class="form-select form-select-sm w-auto">
                    <option value="">All</option>
                    <option value="Active">Active</option>
                    <option value="Archived">Archived</option>
                </select>
            </div>
            <button class="btn btn-success mb-2" data-bs-toggle="modal" data-bs-target="#requestModal">
                <i class="bi bi-plus-circle"></i> New Request
            </button>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Purpose</th>
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="requestTable">
                    <tr><td colspan="5" class="text-center">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- NEW REQUEST MODAL -->
<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content rounded-3">
      <div class="modal-header bg-light">
        <h5 class="modal-title">Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="requestForm">
        <div class="modal-body">
          <p><strong>Full Name:</strong> <?= $fullname ?></p>
          <p><strong>Email:</strong> <?= $resident['email'] ?></p>
          <label class="form-label mt-3 fw-semibold">Document Type</label>
          <select class="form-select" id="docType" name="document_type" required>
            <option value="">-- Select Document --</option>
            <option value="Barangay Clearance">Barangay Clearance</option>
            <option value="Certificate of Indigency">Certificate of Indigency</option>
            <option value="Certificate of Residency">Certificate of Residency</option>
            <option value="Barangay Business Clearance">Barangay Business Clearance</option>
          </select>
          <label class="form-label mt-3 fw-semibold">Purpose</label>
          <textarea class="form-control" name="purpose" rows="3" placeholder="Enter purpose..." required></textarea>
        </div>
        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-success w-100">Submit Request</button>
          <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- VIEW MODAL -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Request Details</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Document Type:</strong> <span id="v-doc-type"></span></p>
        <p><strong>Date:</strong> <span id="v-date"></span></p>
        <p><strong>Status:</strong> <span id="v-status"></span></p>
        <p><strong>Purpose:</strong> <span id="v-purpose"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Edit Request</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="editForm">
        <div class="modal-body">
          <input type="hidden" id="e-id">
          <p><strong>Document Type:</strong> <span id="e-doc-type"></span></p>
          <label><b>Purpose:</b></label>
          <textarea class="form-control" id="e-purpose" rows="3"></textarea>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Save Changes</button>
          <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CANCEL MODAL -->
<div class="modal fade" id="cancelModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5>Cancel Request</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to cancel <strong id="c-doc-type"></strong> request?</p>
        <input type="hidden" id="c-id">
      </div>
      <div class="modal-footer">
        <button id="confirmCancel" class="btn btn-danger w-100">Yes, Cancel</button>
        <button class="btn btn-secondary w-100" data-bs-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<div id="toastContainer" class="position-fixed top-0 end-0 m-3" style="z-index:3000"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }
function showToast(message, type='success', timeout=2500) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast show ${type}`;
    toast.innerHTML = `<div class="toast-body">${message}</div>`;
    container.appendChild(toast);
    setTimeout(()=>{ toast.remove() }, timeout);
}
const truncate = (str, n=40) => str.length>n ? str.substring(0,n)+'...' : str;

// Load requests
async function loadRequests(){
    const table = document.getElementById('requestTable');
    table.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    try{
        const res = await fetch("../../backend/issuance_get.php");
        const data = await res.json();
        if(!data.length){
            table.innerHTML = '<tr><td colspan="5" class="text-center">No requests found.</td></tr>';
            return;
        }

        const rows = data.map(req=>`
            <tr
                data-id="${req._id}"
                data-doc-type="${req.document_type}"
                data-purpose="${req.purpose}"
                data-date="${req.request_date}"
                data-status="${req.status}"
            >
                <td>${req.document_type}</td>
                <td title="${req.purpose}">${truncate(req.purpose)}</td>
                <td>${req.request_date}</td>
                <td>${req.status}</td>
                <td>
                    <button class="btn btn-sm btn-info me-1 text-white view-btn"><i class="bi bi-eye"></i></button>
                    ${req.status==='Pending' ? `
                        <button class="btn btn-sm btn-primary edit-btn"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-danger cancel-btn"><i class="bi bi-x-circle"></i></button>` : '-'}
                </td>
            </tr>
        `).join('');
        table.innerHTML = rows;
        attachRowButtons();
        applyFilter();
    }catch(err){
        console.error(err);
        table.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
    }
}

// Attach dynamic buttons
function attachRowButtons(){
    document.querySelectorAll(".view-btn").forEach(btn=>{
        btn.addEventListener("click", ()=>{
            const tr = btn.closest("tr");
            document.getElementById("v-doc-type").innerText = tr.dataset.docType;
            document.getElementById("v-purpose").innerText = tr.dataset.purpose;
            document.getElementById("v-date").innerText = tr.dataset.date;
            document.getElementById("v-status").innerText = tr.dataset.status;
            new bootstrap.Modal(document.getElementById("viewModal")).show();
        });
    });
    document.querySelectorAll(".edit-btn").forEach(btn=>{
        btn.addEventListener("click", ()=>{
            const tr = btn.closest("tr");
            document.getElementById("e-id").value = tr.dataset.id;
            document.getElementById("e-doc-type").innerText = tr.dataset.docType;
            document.getElementById("e-purpose").value = tr.dataset.purpose;
            new bootstrap.Modal(document.getElementById("editModal")).show();
        });
    });
    document.querySelectorAll(".cancel-btn").forEach(btn=>{
        btn.addEventListener("click", ()=>{
            const tr = btn.closest("tr");
            document.getElementById("c-id").value = tr.dataset.id;
            document.getElementById("c-doc-type").innerText = tr.dataset.docType;
            new bootstrap.Modal(document.getElementById("cancelModal")).show();
        });
    });
}

// Status filter
const statusFilter = document.getElementById('statusFilter');
function applyFilter(){
    const val = statusFilter.value;
    document.querySelectorAll('#requestTable tr').forEach(tr=>{
        const st = tr.dataset.status;
        tr.style.display = (val==='' || (val==='Active' && st!=='Archived') || st===val) ? '' : 'none';
    });
}
statusFilter.addEventListener('change', applyFilter);

// Search filter
document.getElementById('searchInput').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#requestTable tr').forEach(tr=>{
        const doc = tr.children[0]?.innerText.toLowerCase()||'';
        const purpose = tr.children[1]?.innerText.toLowerCase()||'';
        tr.style.display = (doc.includes(q)||purpose.includes(q)) ? '' : 'none';
    });
});

// NEW request submit
document.getElementById("requestForm").addEventListener("submit", async e=>{
    e.preventDefault();
    const document_type = document.getElementById("docType").value;
    const purpose = document.querySelector("#requestForm textarea[name='purpose']").value;
    if(!document_type || !purpose) return showToast("Please fill in all fields","error");
    try{
        const res = await fetch('../../backend/issuance_request.php',{
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({document_type, purpose})
        });
        const data = await res.json();
        showToast(data.message, data.status==='success'?'success':'error');
        if(data.status==='success'){
            loadRequests();
            bootstrap.Modal.getInstance(document.getElementById("requestModal")).hide();
            document.getElementById("requestForm").reset();
        }
    }catch(err){
        console.error(err);
        showToast("Error submitting request","error");
    }
});

// EDIT submit
document.getElementById("editForm").addEventListener("submit", async e=>{
    e.preventDefault();
    const id = document.getElementById("e-id").value;
    const purpose = document.getElementById("e-purpose").value;
    const res = await fetch('../../backend/issuance_edit.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id,purpose})
    });
    const data = await res.json();
    showToast(data.message, data.status==='success'?'success':'error');
    if(data.status==='success') loadRequests();
    bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
});

// CANCEL submit
document.getElementById("confirmCancel").addEventListener("click", async ()=>{
    const id = document.getElementById("c-id").value;
    const res = await fetch('../../backend/issuance_cancel.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({id})
    });
    const data = await res.json();
    showToast(data.message, data.status==='success'?'success':'error');
    if(data.status==='success') loadRequests();
    bootstrap.Modal.getInstance(document.getElementById("cancelModal")).hide();
});

loadRequests();
</script>
</body>
</html>
