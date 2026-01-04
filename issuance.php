<?php
session_start();
require_once 'backend/config.php'; // include MongoDB or MySQL connection

$email = $_SESSION['email'] ?? '';

$fullname = 'Resident';

if ($email) {
    $resident = $residentsCollection->findOne(['email' => $email]); // MongoDB example
    if ($resident) {
        $fullname = trim(
            ($resident['first_name'] ?? '') . ' ' .
            ($resident['middle_name'] ?? '') . ' ' .
            ($resident['last_name'] ?? '') . ' ' .
            ($resident['suffix'] ?? '')
        );
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Issuance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/toast.css" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="left-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1>
        <h3>Issuance</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="right-logo" alt="right logo">
</section>

<div class="container" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: center; margin-bottom:50px; margin-top: 50px">
    
    <div class="card-custom announcement-card" style="width:250px; text-align:center; padding:20px; background:#fff;">
        <img src="assets/img/clearance.png">
        <h5>Barangay Clearance</h5>
        <p>Lorem Ipsum is simply dummy text...</p>
        <p><strong>Price: ₱50</strong></p>

        <?php if (isset($_SESSION['email'])): ?>
            <button class="btn btn-success openRequestModal" data-doc="Barangay Clearance">
                Request Now
            </button>
        <?php else: ?>
            <a href="login.php" class="btn btn-success">Request Now</a>
        <?php endif; ?>
    </div>

    <div class="card-custom announcement-card" style="width:250px; text-align:center; padding:20px; background:#fff;">
        <img src="assets/img/indigency.png">
        <h5>Certificate of Indigency</h5>
        <p>Lorem Ipsum is simply dummy text...</p>
        <p><strong>Price: ₱50</strong></p>

        <?php if (isset($_SESSION['email'])): ?>
            <button class="btn btn-success openRequestModal" data-doc="Certificate of Indigency">
                Request Now
            </button>
        <?php else: ?>
            <a href="login.php" class="btn btn-success">Request Now</a>
        <?php endif; ?>
    </div>

    <div class="card-custom announcement-card" style="width:250px; text-align:center; padding:20px; background:#fff;">
        <img src="assets/img/residency.png">
        <h5>Certificate of Residency</h5>
        <p>Lorem Ipsum is simply dummy text...</p>
        <p><strong>Price: ₱50</strong></p>

        <?php if (isset($_SESSION['email'])): ?>
            <button class="btn btn-success openRequestModal" data-doc="Certificate of Residency">
                Request Now
            </button>
        <?php else: ?>
            <a href="login.php" class="btn btn-success">Request Now</a>
        <?php endif; ?>
    </div>

    <div class="card-custom announcement-card" style="width:250px; text-align:center; padding:20px; background:#fff;">
        <img src="assets/img/business.png">
        <h5>Barangay Business Clearance</h5>
        <p>Lorem Ipsum is simply dummy text...</p>
        <p><strong>Price: ₱50</strong></p>

        <?php if (isset($_SESSION['email'])): ?>
            <button class="btn btn-success openRequestModal" data-doc="Barangay Business Clearance">
                Request Now
            </button>
        <?php else: ?>
            <a href="login.php" class="btn btn-success">Request Now</a>
        <?php endif; ?>
    </div>
</div>

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

          <!-- Indigency extras -->
          <div id="indigencyExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Purpose (Indigency Only)</label>
            <input type="text" id="indigencyPurpose" class="form-control" placeholder="E.g. hospital, scholarship, etc.">

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

            <input type="text" id="certificateOtherRelationship" class="form-control mt-2 d-none" placeholder="If 'Other', enter relationship">
            <input type="text" id="certificateForFullName" class="form-control mt-2 d-none" placeholder="Full name (include middle name)">
          </div>

          <!-- Business clearance extras -->
          <div id="businessExtras" class="mt-3 d-none">
            <label class="form-label fw-semibold">Business Name</label>
            <input type="text" id="businessName" class="form-control" placeholder="Enter business name">
            <label class="form-label mt-2 fw-semibold">Business Location</label>
            <input type="text" id="businessLocation" class="form-control" placeholder="Enter business location">
          </div>

          <label class="form-label mt-3 fw-semibold">Reason</label>
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

<div id="toast"></div>
<button onclick="topFunction()" id="backToTop" title="Go to top">
    <i class="bi bi-arrow-up"></i>
</button>
<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const docTypeSelect = document.getElementById('docType');
const docTypeDisplay = document.getElementById('docTypeDisplay');
const indigencyExtras = document.getElementById('indigencyExtras');
const businessExtras = document.getElementById('businessExtras');
const certForSelect = document.getElementById('certificateFor');
const certOtherRelationship = document.getElementById('certificateOtherRelationship');
const certForFullName = document.getElementById('certificateForFullName');
const requestForm = document.getElementById('requestForm');
const toastEl = document.getElementById('toast');

function showToast(message, type = "success") {
    toastEl.className = "toast show " + type;
    toastEl.innerHTML = message;
    setTimeout(() => {
        toastEl.className = toastEl.className.replace("show", "");
    }, 2500);
}

// Open modal buttons
document.querySelectorAll('.openRequestModal').forEach(btn => {
    btn.addEventListener('click', function () {
        const docType = this.dataset.doc;

        // Set hidden input and display
        docTypeSelect.value = docType;
        docTypeDisplay.innerText = docType;

        // Reset dynamic fields
        indigencyExtras.classList.add('d-none');
        businessExtras.classList.add('d-none');
        certOtherRelationship.classList.add('d-none');
        certForFullName.classList.add('d-none');
        requestForm.reset();

        // Show relevant extra fields
        if(docType === 'Certificate of Indigency') {
            indigencyExtras.classList.remove('d-none');
            document.getElementById('indigencyPurpose').required = true;
        } else if(docType === 'Barangay Business Clearance') {
            businessExtras.classList.remove('d-none');
        }

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('requestModal'));
        modal.show();
    });
});

// Show/hide full name / other relationship for indigency
certForSelect.addEventListener('change', e => {
    const val = e.target.value;
    certOtherRelationship.classList.add('d-none');
    certForFullName.classList.add('d-none');

    if(val && val !== 'Self') {
        certForFullName.classList.remove('d-none');
        if(val === 'Other') {
            certOtherRelationship.classList.remove('d-none');
        }
    }
});

// Form submission
requestForm.addEventListener('submit', async e => {
    e.preventDefault();

    <?php if(!isset($_SESSION['email'])): ?>
        showToast("Please login to request documents.", "danger");
        return;
    <?php endif; ?>

    const document_type = docTypeSelect.value;
    const reason = document.getElementById('reasonField').value.trim();
    const purpose = document.getElementById('indigencyPurpose').value.trim();
    const certificate_for = certForSelect.value;
    const certificateOtherRel = certOtherRelationship.value.trim();
    const certificateForFull = certForFullName.value.trim();
    const businessName = document.getElementById('businessName').value.trim();
    const businessLocation = document.getElementById('businessLocation').value.trim();

    if(!document_type || !reason) {
        return showToast("Please complete required fields.", "danger");
    }

    const payload = { document_type, reason };

    if(document_type === 'Certificate of Indigency'){
        if(!certificate_for) return showToast("Please select who the certificate is for.", "danger");
        if(certificate_for !== 'Self' && !certificateForFull) return showToast("Please enter full name.", "danger");
        if(certificate_for === 'Other' && !certificateOtherRel) return showToast("Please enter relationship.", "danger");
        if(!purpose) return showToast("Please enter purpose.", "danger");

        payload.certificate_for = certificate_for;
        payload.certificate_other_relationship = certificate_for==='Other' ? certificateOtherRel : '';
        payload.certificate_for_fullname = certificate_for==='Self' ? '' : certificateForFull;
        payload.purpose = purpose;
    }

    if(document_type === 'Barangay Business Clearance'){
        if(!businessName || !businessLocation) return showToast("Please complete business info.", "danger");
        payload.business_name = businessName;
        payload.business_location = businessLocation;
    }

    try {
        const res = await fetch("backend/issuance_request.php", {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        showToast(data.message, data.status==='success'?'success':'danger');

        if(data.status === 'success'){
            bootstrap.Modal.getInstance(document.getElementById('requestModal')).hide();
            requestForm.reset();
            indigencyExtras.classList.add('d-none');
            businessExtras.classList.add('d-none');

            // Redirect after short delay
            setTimeout(() => {
                window.location.href = "pages/resident/resident_rqs_service.php";
            }, 1200);
        }
    } catch(err) {
        console.error(err);
        showToast("Error submitting request.", "danger");
    }
});
</script>
<script>
    // Get the button
    let mybutton = document.getElementById("backToTop");

    // Listen to scroll event
    window.onscroll = function() { scrollFunction() };

    function scrollFunction() {
        // Show button if scrolled down 300px
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // Scroll to top when clicked
    function topFunction() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>

</body>
</html>