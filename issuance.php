<?php
session_start();
require_once 'backend/db_connect.php'; 

$fullname = 'Resident';
$email = $_SESSION['email'] ?? '';

if ($email) {
    // MySQL Query
    $stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $resident = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resident) {
        $fullname = trim($resident['first_name'] . ' ' . $resident['last_name']);
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
</head>
<body>

<?php include 'includes/nav.php'; ?>

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

          <p><strong>Document Type:</strong> <span id="docTypeDisplay"></span></p>
          <input type="hidden" id="docType" name="document_type">
          <input type="hidden" id="docPrice" name="price">

          <div id="indigencyExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Purpose (Indigency Only)</label>
            <input type="text" id="indigencyPurpose" name="purpose" class="form-control" placeholder="E.g. hospital, scholarship, etc.">
          </div>

          <div id="businessExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Business Name</label>
            <input type="text" id="businessName" name="business_name" class="form-control" placeholder="Enter business name">
            <label class="form-label mt-2 fw-semibold">Business Location</label>
            <input type="text" id="businessLocation" name="business_location" class="form-control" placeholder="Enter business location">
          </div>

          <label class="form-label mt-3 fw-semibold">Purpose / Reason</label>
          <textarea class="form-control" id="reasonField" name="reason" rows="3" placeholder="Enter reason..." required></textarea>
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
// --- FIXED JAVASCRIPT ---
const docTypeSelect = document.getElementById('docType');
const docPriceInput = document.getElementById('docPrice');
const docTypeDisplay = document.getElementById('docTypeDisplay');
const indigencyExtras = document.getElementById('indigencyExtras');
const businessExtras = document.getElementById('businessExtras');
const requestForm = document.getElementById('requestForm');

function showToast(message, type = "success") {
    const t = document.getElementById("toast");
    t.className = "toast"; 
    t.textContent = message;
    t.classList.add(type === 'success' ? 'success' : 'error'); // Mapping to your CSS classes
    t.classList.add("show");
    setTimeout(() => { t.classList.remove("show"); }, 3000);
}

// Open modal logic
document.querySelectorAll('.openRequestModal').forEach(btn => {
    btn.addEventListener('click', function () {
        const docType = this.dataset.doc;
        let price = 0;

        // Set Price based on Type (Base sa Issuance Cards mo)
        if (docType === 'Barangay Clearance') price = 50;
        else if (docType === 'Certificate of Indigency') price = 0; // Usually free
        else if (docType === 'Certificate of Residency') price = 50;
        else if (docType === 'Barangay Business Clearance') price = 500; // Updated logic based on usual pricing

        docTypeSelect.value = docType;
        docPriceInput.value = price;
        docTypeDisplay.innerText = docType + (price > 0 ? ` (₱${price})` : " (Free)");

        // Reset Fields
        indigencyExtras.classList.add('d-none');
        businessExtras.classList.add('d-none');
        requestForm.reset();
        
        // Re-set values after reset
        docTypeSelect.value = docType;
        docPriceInput.value = price;

        if(docType === 'Certificate of Indigency') {
            indigencyExtras.classList.remove('d-none');
            document.getElementById('indigencyPurpose').required = true;
        } else if(docType === 'Barangay Business Clearance') {
            businessExtras.classList.remove('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('requestModal'));
        modal.show();
    });
});

// SUBMIT FORM using FormData (Matched to PHP $_POST)
requestForm.addEventListener('submit', async e => {
    e.preventDefault();

    const formData = new FormData(requestForm);
    
    // Manual mapping for 'purpose' field logic
    // If not Indigency, use the general 'reason' as purpose
    const docType = docTypeSelect.value;
    const generalReason = document.getElementById('reasonField').value;
    
    if (docType !== 'Certificate of Indigency') {
        formData.append('purpose', generalReason);
    } 
    // If Indigency, 'purpose' input is already in formData, we append reason as extra info if needed or just leave it.

    try {
        const res = await fetch("backend/issuance_request.php", {
            method: 'POST',
            body: formData // Sends as multipart/form-data, PHP $_POST will work directly
        });
        
        const data = await res.json();

        if(data.status === 'success'){
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('requestModal')).hide();
            setTimeout(() => {
                window.location.href = "pages/resident/resident_rqs_service.php";
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