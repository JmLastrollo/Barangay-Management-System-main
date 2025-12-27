<?php 
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/config.php'; 

$email = $_SESSION['email'] ?? '';
if (!$email) die("Error: No session found.");

$user = $usersCollection->findOne(['email' => $email]);
if (!$user) die("Error: User not found.");

$resident = $residentsCollection->findOne(['user_id' => $user['_id']]);
if (!$resident) die("Error: Resident record not found.");

$fullname = trim($resident['first_name'] . ' ' . ($resident['middle_name'] ?? '') . ' ' . $resident['last_name'] . ' ' . ($resident['suffix'] ?? ''));

$residentId = (string)$resident['_id'];

$requests = iterator_to_array(
    $issuanceCollection->find(
        [
            'resident_id' => $residentId,
            'status' => ['$nin' => ['Archived','Cancelled','Active', 'Received']]
        ],
        [
            'sort' => [
                'request_date' => -1,
                'request_time' => -1
            ]
        ]
    )
);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>BMS - Resident Requests</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css">
<link rel="stylesheet" href="../../css/toast.css">

<style>
td { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.modal-small-field { max-width: 100%; }
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
            <h3><?= htmlspecialchars($resident['first_name'] . " " . $resident['last_name']) ?></h3>
            <small><?= htmlspecialchars($resident['email']) ?></small>
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
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for Document Type..." class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            <div class="text-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestModal">
                    <i class="bi bi-plus-circle"></i> New Request
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Reason</th>
                        <th>Request Date & Time</th>
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
          <p><strong>Full Name:</strong> <?= htmlspecialchars($fullname) ?></p>

          <label class="form-label mt-2 fw-semibold">Document Type</label>
          <select class="form-select" id="docType" name="document_type" required>
            <option value="">-- Select Document --</option>
            <option value="Barangay Clearance">Barangay Clearance</option>
            <option value="Certificate of Indigency">Certificate of Indigency</option>
            <option value="Certificate of Residency">Certificate of Residency</option>
            <option value="Barangay Business Clearance">Barangay Business Clearance</option>
          </select>

          <!-- Indigency extra: certificate_for + purpose -->
          <div id="indigencyExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Purpose (Indigency Only)</label>
            <input type="text" id="indigencyPurpose" class="form-control" placeholder="E.g. hospital requirement, scholarship, etc.">

            <label class="form-label mt-3 fw-semibold">Certificate For</label>
            <select class="form-select" id="certificateFor">
                <option value="">-- Select --</option>
                <option value="Self">Self</option>
                <option value="Son">Son</option>
                <option value="Daughter">Daughter</option>
                <option value="Father">Father</option>
                <option value="Mother">Mother</option>
                <option value="Spouse">Spouse</option>
                <option value="Relative">Relative</option>
                <option value="Other">Other</option>
            </select>

            <!-- when "Certificate For" is not Self we require full name -->
            <input type="text" id="certificateOtherRelationship" class="form-control mt-2 d-none" placeholder="If 'Other', enter relationship (e.g. cousin)">
            <input type="text" id="certificateForFullName" class="form-control mt-2 d-none" placeholder="Full name (include middle name)">
          </div>

          <!-- Business clearance extras -->
          <div id="businessExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Business Name</label>
            <input type="text" id="businessName" class="form-control" placeholder="Enter business name">
            <label class="form-label mt-2 fw-semibold">Business Location</label>
            <input type="text" id="businessLocation" class="form-control" placeholder="Enter business location">
          </div>

          <!-- Common: Reason (all docs) -->
          <div id="reasonWrapper" class="mt-3">
            <label class="form-label fw-semibold">Reason</label>
            <textarea class="form-control" id="reasonField" name="reason" rows="3" placeholder="Enter reason..." required></textarea>
          </div>

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
        <p><strong>Time:</strong> <span id="v-time"></span></p>
        <p><strong>Status:</strong> <span id="v-status"></span></p>
        <p id="v-reason-row"><strong>Reason:</strong> <span id="v-reason"></span></p>
        <div id="v-extra" class="mt-2"></div>
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

          <label class="form-label fw-semibold">Reason</label>
          <textarea class="form-control" id="e-reason" rows="3"></textarea>

          <!-- for indigency editable purpose -->
          <div id="e-indigencyExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Purpose (indigency only)</label>
            <input type="text" id="e-indigencyPurpose" class="form-control">
          </div>

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
        <p>Are you sure you want to cancel this <strong id="c-doc-type"></strong> request?</p>
        <p class="text-danger">This action cannot be undone.</p>
        <input type="hidden" id="c-id">
      </div>
      <div class="modal-footer">
        <button id="confirmCancel" class="btn btn-danger w-100">Yes, Cancel</button>
        <button class="btn btn-secondary w-100" data-bs-dismiss="modal">No</button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div id="toastContainer" class="position-fixed top-0 end-0 m-3" style="z-index:3000"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
function toggleSidebar() { document.querySelector('.sidebar').classList.toggle('active'); }

// Toast
function showToast(message, type='success', timeout=2500) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toast show ${type}`;
    toast.innerHTML = `<div class="toast-body">${message}</div>`;
    container.appendChild(toast);
    setTimeout(()=>{ toast.remove() }, timeout);
}

// Helper to truncate long text
const truncate = (str, n = 40) => str ? (str.length > n ? str.substring(0, n) + "..." : str) : '';

function getStatusBadge(status) {
    switch(status) {
        case "Pending":
            return `<span class="badge bg-warning text-dark">Pending</span>`;
        case "Ready For Pickup":
            return `<span class="badge bg-success">Ready For Pickup</span>`;
        case "Rejected":
            return `<span class="badge bg-danger">Rejected</span>`;
        default:
            return `<span class="badge bg-secondary">${status}</span>`;
    }
}

// Load requests
async function loadRequests() {
    const table = document.getElementById('requestTable');
    table.innerHTML = '<tr><td colspan="5" class="text-center">Loading...</td></tr>';
    try {
        const res = await fetch("../../backend/issuance_get.php");
        let data = await res.json();

        if (!data.length) {
            table.innerHTML = '<tr><td colspan="5" class="text-center">No requests found.</td></tr>';
            return;
        }

        // Filter out unwanted statuses
        let filtered = data.filter(r => !['Archived','Cancelled','Active','Received'].includes(r.status));

        // Sort by date + time descending
        filtered.sort((a, b) => {
            const dateA = new Date(`${a.request_date}T${a.request_time}`);
            const dateB = new Date(`${b.request_date}T${b.request_time}`);
            return dateB - dateA; // descending
        });

        // Generate table rows
        const rows = filtered.map(req => {
            let reasonDisplay = req.document_type === 'Certificate of Indigency'
                ? [req.purpose, req.reason].filter(Boolean).join(' — ')
                : req.reason || '';

            const businessName = req.business_name || '';
            const businessLocation = req.business_location || '';
            const certificateFor = req.certificate_for || '';
            const certificateOtherRelationship = req.certificate_other_relationship || '';
            const certificateForFullname = req.certificate_for_fullname || '';

            return `
            <tr
                data-id="${req._id}"
                data-doc-type="${req.document_type}"
                data-reason="${encodeURIComponent(req.reason || '')}"
                data-purpose="${encodeURIComponent(req.purpose || '')}"
                data-business-name="${encodeURIComponent(businessName)}"
                data-business-location="${encodeURIComponent(businessLocation)}"
                data-certificate-for="${encodeURIComponent(certificateFor)}"
                data-certificate-other-relationship="${encodeURIComponent(certificateOtherRelationship)}"
                data-certificate-for-fullname="${encodeURIComponent(certificateForFullname)}"
                data-date="${req.request_date}"
                data-time="${req.request_time}"
                data-status="${req.status}"
            >
                <td>${req.document_type}</td>
                <td title="${reasonDisplay}">${truncate(reasonDisplay, 60)}</td>
                <td>${req.request_date} ${req.request_time}</td>
                <td>${getStatusBadge(req.status)}</td>
                <td>
                    <button class="btn btn-sm btn-info me-1 text-white view-btn"><i class="bi bi-eye"></i></button>
                    ${req.status==='Pending' ? `
                        <button class="btn btn-sm btn-primary edit-btn"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-danger cancel-btn"><i class="bi bi-x-circle"></i></button>` : ''}
                </td>
            </tr>
            `;
        }).join('');

        table.innerHTML = rows;
        attachRowButtons();
    } catch(err) {
        console.error(err);
        table.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
    }
}


// Attach buttons dynamically
function attachRowButtons() {
    // VIEW
    document.querySelectorAll(".view-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            const doc = tr.dataset.docType;
            const reason = decodeURIComponent(tr.dataset.reason || '');
            const purpose = decodeURIComponent(tr.dataset.purpose || '');
            const businessName = decodeURIComponent(tr.dataset.businessName || '');
            const businessLocation = decodeURIComponent(tr.dataset.businessLocation || '');
            const certificateFor = decodeURIComponent(tr.dataset.certificateFor || '');
            const certificateOtherRelationship = decodeURIComponent(tr.dataset.certificateOtherRelationship || '');
            const certificateForFullname = decodeURIComponent(tr.dataset.certificateForFullname || '');

            document.getElementById("v-doc-type").innerText = doc;
            document.getElementById("v-date").innerText = tr.dataset.date;
            document.getElementById("v-time").innerText = tr.dataset.time;
            document.getElementById("v-status").innerText = tr.dataset.status;

            // clean extra area
            document.getElementById("v-extra").innerHTML = '';
            document.getElementById("v-reason").innerText = '';

            if (doc === 'Certificate of Indigency') {
                // Build certificate-for line
                let who = certificateFor || 'Self';
                let whoLine = (who === 'Self') ? `Certificate For: Self (requester)` : `Certificate For: ${who}`;
                if (who === 'Other' && certificateOtherRelationship) {
                    whoLine += ` — ${certificateOtherRelationship}`;
                }
                if (certificateForFullname) whoLine += ` — ${certificateForFullname}`;

                let extraHtml = `<p><strong>${whoLine}</strong></p>`;
                extraHtml += `<p><strong>Purpose:</strong> ${purpose || '-'}</p>`;
                extraHtml += `<p><strong>Reason:</strong> ${reason || '-'}</p>`;
                document.getElementById("v-extra").innerHTML = extraHtml;
                document.getElementById("v-reason-row").style.display = 'none';
            } else if (doc === 'Barangay Business Clearance') {
                let extraHtml = `<p><strong>Business Name:</strong> ${businessName || '-'}</p>`;
                extraHtml += `<p><strong>Business Location:</strong> ${businessLocation || '-'}</p>`;
                extraHtml += `<p><strong>Reason:</strong> ${reason || '-'}</p>`;
                document.getElementById("v-extra").innerHTML = extraHtml;
                document.getElementById("v-reason-row").style.display = 'none';
            } else {
                document.getElementById("v-reason").innerText = reason || '-';
                document.getElementById("v-reason-row").style.display = '';
            }

            new bootstrap.Modal(document.getElementById("viewModal")).show();
        });
    });

    // EDIT
    document.querySelectorAll(".edit-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            const doc = tr.dataset.docType;
            const reason = decodeURIComponent(tr.dataset.reason || '');
            const purpose = decodeURIComponent(tr.dataset.purpose || '');

            document.getElementById("e-id").value = tr.dataset.id;
            document.getElementById("e-doc-type").innerText = doc;
            // populate reason & purpose (purpose only if indigency)
            document.getElementById("e-reason").value = reason || '';

            const eInd = document.getElementById('e-indigencyExtras');
            if (doc === 'Certificate of Indigency') {
                eInd.classList.remove('d-none');
                document.getElementById('e-indigencyPurpose').value = purpose || '';
            } else {
                eInd.classList.add('d-none');
                document.getElementById('e-indigencyPurpose').value = '';
            }

            // show edit modal; edit modal doesn't include certificate_for fields (as requested)
            new bootstrap.Modal(document.getElementById("editModal")).show();
        });
    });

    // CANCEL
    document.querySelectorAll(".cancel-btn").forEach(btn => {
        btn.addEventListener("click", () => {
            const tr = btn.closest("tr");
            document.getElementById("c-id").value = tr.dataset.id;
            document.getElementById("c-doc-type").innerText = tr.dataset.docType;
            new bootstrap.Modal(document.getElementById("cancelModal")).show();
        });
    });
}

// --- Modal dynamic behavior for New Request ---
const docTypeSelect = document.getElementById('docType');
const indigencyExtras = document.getElementById('indigencyExtras');
const businessExtras = document.getElementById('businessExtras');
const certForSelect = document.getElementById('certificateFor');
const certOtherRelationship = document.getElementById('certificateOtherRelationship');
const certForFullName = document.getElementById('certificateForFullName');

docTypeSelect.addEventListener('change', () => {
    const val = docTypeSelect.value;
    indigencyExtras.classList.add('d-none');
    businessExtras.classList.add('d-none');

    // reset requirements
    document.getElementById('reasonField').required = true;
    document.getElementById('indigencyPurpose').required = false;
    certOtherRelationship.classList.add('d-none');
    certOtherRelationship.value = '';
    certForFullName.classList.add('d-none');
    certForFullName.value = '';

    if (val === 'Certificate of Indigency') {
        indigencyExtras.classList.remove('d-none');
        document.getElementById('indigencyPurpose').required = true;
    } else if (val === 'Barangay Business Clearance') {
        businessExtras.classList.remove('d-none');
    }
});

// certificateFor change: show relation & fullname as required (Option A)
certForSelect.addEventListener('change', (e) => {
    const v = e.target.value;
    // hide both by default
    certOtherRelationship.classList.add('d-none');
    certOtherRelationship.value = '';
    certForFullName.classList.add('d-none');
    certForFullName.value = '';

    if (!v || v === 'Self') {
        // nothing to show
        return;
    }

    // If "Other" selected -> show relationship input and full name
    if (v === 'Other') {
        certOtherRelationship.classList.remove('d-none');
        certForFullName.classList.remove('d-none');
        certOtherRelationship.setAttribute('placeholder', 'Enter relationship (e.g. cousin)');
        certForFullName.setAttribute('placeholder', 'Full name (include middle name)');
    } else {
        // For Son, Daughter, Father, Mother, Spouse, Relative -> show full name only
        certForFullName.classList.remove('d-none');
        certForFullName.setAttribute('placeholder', 'Full name (include middle name)');
    }
});

// NEW REQUEST submit
document.getElementById("requestForm").addEventListener("submit", async e => {
    e.preventDefault();
    const document_type = document.getElementById("docType").value;
    const reason = document.getElementById("reasonField").value.trim();

    if (!document_type) return showToast("Please select a document type", "error");
    if (!reason) return showToast("Please enter a reason", "error");

    const payload = { document_type, reason };

    if (document_type === 'Certificate of Indigency') {
        const certificate_for = document.getElementById('certificateFor').value;
        const certificateOtherRel = document.getElementById('certificateOtherRelationship').value.trim();
        const certificateForFull = document.getElementById('certificateForFullName').value.trim();
        const purpose = document.getElementById('indigencyPurpose').value.trim();

        if (!certificate_for) return showToast("Please select who the certificate is for", "error");
        // Option A: require fullname for ALL non-Self
        if (certificate_for !== 'Self' && !certificateForFull) {
            return showToast("Please enter full name (including middle name) for the selected person", "error");
        }
        // if Other -> require relationship and fullname
        if (certificate_for === 'Other' && !certificateOtherRel) {
            return showToast("Please specify relationship for 'Other'", "error");
        }
        if (!purpose) return showToast("Please provide purpose for indigency", "error");

        payload.certificate_for = certificate_for;
        payload.certificate_other_relationship = certificate_for === 'Other' ? certificateOtherRel : '';
        payload.certificate_for_fullname = certificate_for === 'Self' ? '' : certificateForFull;
        payload.purpose = purpose;
    }

    if (document_type === 'Barangay Business Clearance') {
        const businessName = document.getElementById('businessName').value.trim();
        const businessLocation = document.getElementById('businessLocation').value.trim();
        if (!businessName) return showToast("Please enter the business name", "error");
        if (!businessLocation) return showToast("Please enter the business location", "error");

        payload.business_name = businessName;
        payload.business_location = businessLocation;
    }

    try {
        const res = await fetch('../../backend/issuance_request.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        showToast(data.message, data.status==='success'?'success':'error');
        if (data.status==='success') {
            loadRequests();
            bootstrap.Modal.getInstance(document.getElementById("requestModal")).hide();
            document.getElementById("requestForm").reset();
            // hide extras
            indigencyExtras.classList.add('d-none');
            businessExtras.classList.add('d-none');
            certOtherRelationship.classList.add('d-none');
            certForFullName.classList.add('d-none');
        }
    } catch(err) {
        console.error(err);
        showToast("Error submitting request", "error");
    }
});

// EDIT submit
document.getElementById("editForm").addEventListener("submit", async e => {
    e.preventDefault();
    const id = document.getElementById("e-id").value;
    const reason = document.getElementById("e-reason").value.trim();
    const docType = document.getElementById("e-doc-type").innerText;
    const payload = { id };

    if (!reason) return showToast("Please enter a reason", "error");
    payload.reason = reason;

    if (docType === 'Certificate of Indigency') {
        const purpose = document.getElementById('e-indigencyPurpose').value.trim();
        if (!purpose) return showToast("Please provide purpose for indigency", "error");
        payload.purpose = purpose;
    }

    try {
        const res = await fetch('../../backend/issuance_edit.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        showToast(data.message, data.status==='success'?'success':'error');
        if (data.status==='success') {
            loadRequests();
            bootstrap.Modal.getInstance(document.getElementById("editModal")).hide();
        }
    } catch(err) {
        console.error(err);
        showToast("Error editing request", "error");
    }
});

// CANCEL submit
document.getElementById("confirmCancel").addEventListener("click", async () => {
    const id = document.getElementById("c-id").value;
    try {
        const res = await fetch('../../backend/issuance_cancel.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({id})
        });
        const data = await res.json();
        showToast(data.message, data.status==='success'?'success':'error');
        if (data.status==='success') {
            loadRequests();
            bootstrap.Modal.getInstance(document.getElementById("cancelModal")).hide();
        }
    } catch(err) {
        console.error(err);
        showToast("Error cancelling request", "error");
    }
});

// Search filter
document.getElementById('searchInput').addEventListener('input', function(){
    const q = this.value.toLowerCase();
    document.querySelectorAll('#requestTable tr').forEach(tr=>{
        const doc = tr.children[0]?.innerText.toLowerCase()||'';
        const reason = tr.children[1]?.innerText.toLowerCase()||'';
        tr.style.display = (doc.includes(q)||reason.includes(q)) ? '' : 'none';
    });
});

// Initial load
loadRequests();
</script>
</body>
</html>
