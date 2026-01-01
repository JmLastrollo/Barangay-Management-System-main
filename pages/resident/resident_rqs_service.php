<?php 
session_start(); 
require_once '../../backend/db_connect.php'; 

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT * FROM resident_profiles WHERE email = :email");
$stmt->execute([':email' => $email]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);
$fullname = $resident['first_name'] . ' ' . $resident['last_name'];

// FETCH HISTORY mula sa bagong table
$stmtHist = $conn->prepare("SELECT * FROM issuance WHERE resident_id = :rid ORDER BY request_date DESC");
$stmtHist->execute([':rid' => $resident['resident_id']]);
$history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Request Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/dashboard.css">
</head>
<body>

<div class="content">
    <div class="d-flex justify-content-between mb-3">
        <h3>My Requests</h3>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestModal">
            + New Request
        </button>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $h): ?>
                <tr>
                    <td><?= htmlspecialchars($h['document_type']) ?></td>
                    <td><?= date('M d, Y', strtotime($h['request_date'])) ?></td>
                    <td>
                        <span class="badge bg-secondary"><?= htmlspecialchars($h['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="requestModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Request Certificate</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="requestForm">
        <div class="modal-body">
            
            <div class="mb-3">
                <label class="form-label">Select Document</label>
                <select class="form-select" id="document_type" name="document_type" required onchange="toggleFields()">
                    <option value="" data-price="0">-- Select --</option>
                    <option value="Barangay Clearance" data-price="50">Barangay Clearance (₱50)</option>
                    <option value="Certificate of Indigency" data-price="0">Certificate of Indigency (Free)</option>
                    <option value="Certificate of Residency" data-price="50">Certificate of Residency (₱50)</option>
                    <option value="Barangay Business Clearance" data-price="500">Barangay Business Clearance (₱500)</option>
                </select>
                <input type="hidden" id="price" name="price">
            </div>

            <div id="business_fields" style="display:none;" class="mb-3 bg-light p-2 border rounded">
                <label>Business Name</label>
                <input type="text" id="business_name" name="business_name" class="form-control mb-2">
                <label>Business Location</label>
                <input type="text" id="business_location" name="business_location" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Purpose</label>
                <textarea class="form-control" name="purpose" rows="3" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleFields() {
        const select = document.getElementById('document_type');
        const price = select.options[select.selectedIndex].getAttribute('data-price');
        document.getElementById('price').value = price;

        const val = select.value;
        const busDiv = document.getElementById('business_fields');
        if(val.includes("Business")) {
            busDiv.style.display = 'block';
            document.getElementById('business_name').required = true;
        } else {
            busDiv.style.display = 'none';
            document.getElementById('business_name').required = false;
        }
    }

    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch('../../backend/issuance_request.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if(data.status === 'success') location.reload();
        });
    });
</script>
</body>
</html>