<?php
session_start();
require_once 'backend/db_connect.php'; 

// 1. Initialize variables (Default is guest/not logged in)
$is_logged_in = false;
$fullname = ''; 

// 2. Check if user is logged in (Do NOT redirect, just check status)
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'Resident') {
    $is_logged_in = true;
    $email = $_SESSION['email'] ?? '';

    if ($email) {
        $stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $resident = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resident) {
            $fullname = trim($resident['first_name'] . ' ' . $resident['last_name']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Issuance</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/toast.css" />
    <style>
        .service-card {
            transition: transform 0.3s;
            cursor: pointer;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
    </style>
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="left-logo" alt="LGU Logo">
    <div class="header-text">
        <h1>Barangay Services</h1> 
        <h3>Request Documents</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="right-logo" alt="Barangay Logo">
</section>

<section class="container py-5">
    
    <?php if($is_logged_in): ?>
        <div class="alert alert-success mb-4">
            Welcome back, <strong><?= htmlspecialchars($fullname) ?></strong>! Select a document below to request.
        </div>
    <?php endif; ?>

    <div class="row justify-content-center g-4">
        
        <div class="col-md-4">
            <div class="card h-100 shadow-sm service-card">
                <img src="assets/img/clearance.png" class="card-img-top" alt="Clearance">
                <div class="card-body text-center">
                    <h5 class="fw-bold text-primary">Barangay Clearance</h5>
                    <p class="text-muted small">For employment, postal ID, and other legal purposes.</p>
                    <button class="btn btn-primary w-100 openRequestModal" data-doc="Barangay Clearance">Request Now</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm service-card">
                <img src="assets/img/indigency.png" class="card-img-top" alt="Indigency">
                <div class="card-body text-center">
                    <h5 class="fw-bold text-primary">Certificate of Indigency</h5>
                    <p class="text-muted small">For medical assistance, scholarship, and financial aid.</p>
                    <button class="btn btn-primary w-100 openRequestModal" data-doc="Certificate of Indigency">Request Now</button>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm service-card">
                <img src="assets/img/residency.png" class="card-img-top" alt="Residency">
                <div class="card-body text-center">
                    <h5 class="fw-bold text-primary">Certificate of Residency</h5>
                    <p class="text-muted small">Proof of residency for bank opening, ID application, etc.</p>
                    <button class="btn btn-primary w-100 openRequestModal" data-doc="Certificate of Residency">Request Now</button>
                </div>
            </div>
        </div>

    </div>
</section>

<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content rounded-3">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="requestForm">
        <div class="modal-body px-4">
          <p><strong>Full Name:</strong> <?= htmlspecialchars($fullname) ?></p>

          <p><strong>Document Type:</strong> <span id="docTypeDisplay" class="fw-bold text-primary"></span></p>
          <input type="hidden" id="docType" name="document_type">
          <input type="hidden" id="docPrice" name="price">

          <div id="indigencyExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Specific Purpose (Required)</label>
            <select id="indigencyPurpose" name="purpose" class="form-select">
                <option value="" selected disabled>Select Purpose</option>
                <option value="Medical Assistance">Medical Assistance</option>
                <option value="Scholarship Application">Scholarship Application</option>
                <option value="Financial Assistance">Financial Assistance</option>
                <option value="Legal Assistance">Legal Assistance</option>
                <option value="Others">Others</option>
            </select>
          </div>

          <div id="generalReasonDiv">
              <label class="form-label mt-3 fw-semibold">Purpose / Reason</label>
              <textarea class="form-control" id="reasonField" name="reason" rows="3" placeholder="E.g. For Employment, Postal ID Requirement..."></textarea>
          </div>
          
        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-success w-100 mt-2">Proceed</button>
          <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<?php include('includes/footer.php'); ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>

<script>
// --- PASS PHP LOGIN STATUS TO JS ---
const isLoggedIn = <?php echo json_encode($is_logged_in); ?>;

// --- VARIABLES ---
const docTypeSelect = document.getElementById('docType');
const docPriceInput = document.getElementById('docPrice');
const docTypeDisplay = document.getElementById('docTypeDisplay');
const indigencyExtras = document.getElementById('indigencyExtras');
const indigencyPurpose = document.getElementById('indigencyPurpose');
const generalReasonDiv = document.getElementById('generalReasonDiv');
const reasonField = document.getElementById('reasonField');
const requestForm = document.getElementById('requestForm');

// --- TOAST FUNCTION ---
function showToast(message, type = "success") {
    const t = document.getElementById("toast");
    t.className = "toast"; 
    t.textContent = message;
    t.classList.add(type === 'success' ? 'success' : 'error');
    t.classList.add("show");
    setTimeout(() => { t.classList.remove("show"); }, 3000);
}

// --- OPEN MODAL LOGIC ---
document.querySelectorAll('.openRequestModal').forEach(btn => {
    btn.addEventListener('click', function (e) {
        
        // CHECK IF LOGGED IN FIRST
        if (!isLoggedIn) {
            e.preventDefault();
            // Redirect to Login if guest
            window.location.href = 'login.php'; 
            return;
        }

        // Standard Modal Logic (Only runs if logged in)
        const docType = this.dataset.doc;
        let price = 0;

        if (docType === 'Barangay Clearance') price = 50;
        else if (docType === 'Certificate of Indigency') price = 0;
        else if (docType === 'Certificate of Residency') price = 50;

        docTypeSelect.value = docType;
        docPriceInput.value = price;
        docTypeDisplay.innerText = docType + (price > 0 ? ` (₱${price}.00)` : " (FREE)");

        requestForm.reset();
        docTypeSelect.value = docType; 
        
        indigencyExtras.classList.add('d-none');
        generalReasonDiv.classList.remove('d-none');
        indigencyPurpose.required = false;
        reasonField.required = true;

        if(docType === 'Certificate of Indigency') {
            indigencyExtras.classList.remove('d-none');
            generalReasonDiv.classList.add('d-none'); 
            indigencyPurpose.required = true;
            reasonField.required = false;
        }

        const modal = new bootstrap.Modal(document.getElementById('requestModal'));
        modal.show();
    });
});

// --- SUBMIT LOGIC ---
requestForm.addEventListener('submit', async e => {
    e.preventDefault();

    const formData = new FormData(requestForm);
    const docType = docTypeSelect.value;
    
    if (docType !== 'Certificate of Indigency') {
        formData.set('purpose', reasonField.value); 
    }

    try {
        const res = await fetch("backend/issuance_request.php", {
            method: 'POST',
            body: formData
        });
        
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
             throw new Error("Server returned non-JSON response");
        }

        const data = await res.json();

        if(data.status === 'success'){
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('requestModal')).hide();
            
            setTimeout(() => {
                window.location.href = "pages/resident/my_requests.php";
            }, 1500);
        } else {
            showToast(data.message, 'error');
        }
    } catch(err) {
        console.error(err);
        showToast("System Error. Please try again.", 'error');
    }
});
</script>
</body>
</html>