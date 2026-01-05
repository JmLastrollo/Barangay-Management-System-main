<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// Fetch Resident Info
$stmt = $conn->prepare("SELECT resident_id, first_name, last_name FROM resident_profiles WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    die("Resident profile not found. Please complete your profile first.");
}

// PRICES CONFIG
$doc_prices = [
    'Barangay Clearance' => 50.00,
    'Certificate of Residency' => 50.00,
    'Certificate of Indigency' => 0.00
];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_request'])) {
    
    $doc_type = $_POST['document_type'];
    $purpose = trim($_POST['purpose']);
    $pay_method = $_POST['payment_method'];
    $resident_id = $resident['resident_id'];
    $price = $doc_prices[$doc_type] ?? 0.00;
    
    $pay_status = ($pay_method === 'Cash' || $price == 0) ? 'Unpaid' : 'For Verification';
    if ($price == 0) $pay_status = 'Free';

    $req_ctrl_no = "REQ-" . date("Ymd") . "-" . strtoupper(substr(md5(uniqid()), 0, 4));

    try {
        $conn->beginTransaction();

        // 1. INSERT ISSUANCE
        $sqlIssuance = "INSERT INTO issuance 
                        (resident_id, request_control_no, document_type, purpose, price, status, payment_status, request_date) 
                        VALUES (:rid, :ctrl_no, :dtype, :purp, :price, 'Pending', :pstat, NOW())";
        
        $stmtIso = $conn->prepare($sqlIssuance);
        $stmtIso->execute([
            ':rid' => $resident_id,
            ':ctrl_no' => $req_ctrl_no,
            ':dtype' => $doc_type,
            ':purp' => $purpose,
            ':price' => $price,
            ':pstat' => $pay_status
        ]);
        
        $issuance_id = $conn->lastInsertId();

        // 2. ONLINE PAYMENT HANDLING
        if ($pay_method === 'Online Payment' && $price > 0) {
            if (isset($_FILES['proof_file']) && $_FILES['proof_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['proof_file']['tmp_name'];
                $fileName = $_FILES['proof_file']['name'];
                $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];

                if (in_array($fileExt, $allowed)) {
                    $newFileName = 'PAY_' . $issuance_id . '_' . time() . '.' . $fileExt;
                    $uploadDir = '../../uploads/payments/';
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $sqlPay = "INSERT INTO payments (issuance_id, amount, payment_method, proof_image, status, payment_date) 
                                   VALUES (:iid, :amt, 'Online', :img, 'Pending', NOW())";
                        $stmtPay = $conn->prepare($sqlPay);
                        $stmtPay->execute([
                            ':iid' => $issuance_id,
                            ':amt' => $price,
                            ':img' => $newFileName
                        ]);
                    }
                }
            }
        } 

        $conn->commit();
        
        // SET SUCCESS MESSAGE AND REDIRECT
        $_SESSION['toast'] = ['msg' => 'Request submitted successfully!', 'type' => 'success'];
        header("Location: issuance_table.php"); 
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Request Document - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">REQUEST <span class="green">SERVICE</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                <div><strong>Note:</strong> Waiting time depends on admin approval.</div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5">
                            
                            <h4 class="fw-bold text-dark mb-4"><i class="bi bi-file-earmark-plus-fill text-primary me-2"></i>New Request Form</h4>
                            
                            <form action="" method="POST" enctype="multipart/form-data">
                                
                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Select Document</label>
                                    <select name="document_type" id="document_type" class="form-select form-select-lg" required onchange="updatePrice()">
                                        <option value="" disabled selected>Choose a document...</option>
                                        <option value="Barangay Clearance" data-price="50.00">Barangay Clearance</option>
                                        <option value="Certificate of Residency" data-price="50.00">Certificate of Residency</option>
                                        <option value="Certificate of Indigency" data-price="0.00">Certificate of Indigency</option>
                                    </select>
                                </div>
                                
                                <div class="mb-4 p-3 bg-light rounded border text-center" id="price_display_box" style="display:none;">
                                    <small class="text-muted text-uppercase fw-bold">Amount to Pay</small>
                                    <h2 class="text-primary fw-bold m-0" id="price_display">₱ 0.00</h2>
                                </div>

                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Purpose</label>
                                    <textarea name="purpose" class="form-control" rows="3" required></textarea>
                                </div>

                                <div class="mb-4" id="payment_section">
                                    <label class="fw-bold text-muted small text-uppercase">Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-select" required onchange="togglePayment()">
                                        <option value="" disabled selected>Select Payment Method</option>
                                        <option value="Cash">Cash (Pay at Barangay Hall)</option>
                                        <option value="Online Payment">Online Payment (GCash / Maya)</option>
                                    </select>
                                </div>

                                <div id="online_payment_section" class="mb-4 p-4 bg-light rounded border" style="display: none;">
                                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-qr-code-scan me-2"></i>Scan to Pay</h6>
                                    
                                    <div class="d-flex flex-column flex-md-row align-items-center gap-4 mb-4">
                                        <div class="bg-white p-2 rounded border shadow-sm">
                                            <img src="../../assets/img/gcash_qr.jpg" alt="GCash QR Code" style="width: 150px; height: 150px; object-fit: contain;">
                                        </div>

                                        <div>
                                            <div class="mb-3">
                                                <small class="text-muted fw-bold text-uppercase d-block">Account Name</small>
                                                <span class="fs-5 fw-bold text-dark">Juan Dela Cruz</span>
                                            </div>
                                            <div>
                                                <small class="text-muted fw-bold text-uppercase d-block">GCash / Maya Number</small>
                                                <span class="fs-4 fw-bold text-primary font-monospace">0912-345-6789</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="navigator.clipboard.writeText('09123456789')">
                                                    <i class="bi bi-clipboard"></i> Copy
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="text-muted">

                                    <label class="form-label fw-bold small text-danger">Upload Proof of Payment (Screenshot) *</label>
                                    <input type="file" name="proof_file" id="proof_file" class="form-control" accept="image/*">
                                    <div class="form-text">Please upload clear screenshot of the transaction receipt.</div>
                                </div>

                                <div class="d-grid gap-2 mt-5">
                                    <button type="submit" name="submit_request" class="btn btn-primary btn-lg rounded-pill fw-bold">Submit Request</button>
                                    <a href="resident_dashboard.php" class="btn btn-light rounded-pill text-muted">Cancel</a>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php include '../../includes/resident_footer.php'; ?>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // --- TOAST NOTIFICATION SCRIPT (For Error cases on this page) ---
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastBody = document.getElementById('toastMessage');
            
            toastBody.innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.remove('bg-success', 'bg-danger');
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php unset($_SESSION['toast']); endif; ?>

        function updatePrice() {
            var docSelect = document.getElementById("document_type");
            var selectedOption = docSelect.options[docSelect.selectedIndex];
            var price = parseFloat(selectedOption.getAttribute("data-price"));
            
            document.getElementById("price_display_box").style.display = "block";
            document.getElementById("price_display").innerText = "₱ " + price.toFixed(2);

            var paySection = document.getElementById("payment_section");
            var payMethod = document.getElementById("payment_method");

            if (price === 0) {
                paySection.style.display = "none";
                payMethod.removeAttribute("required");
                payMethod.value = "Cash";
                document.getElementById("online_payment_section").style.display = "none";
            } else {
                paySection.style.display = "block";
                payMethod.setAttribute("required", "required");
            }
        }

        function togglePayment() {
            var method = document.getElementById("payment_method").value;
            var section = document.getElementById("online_payment_section");
            var input = document.getElementById("proof_file");

            if (method === "Online Payment") {
                section.style.display = "block";
                input.setAttribute("required", "required");
            } else {
                section.style.display = "none";
                input.removeAttribute("required");
                input.value = "";
            }
        }
    </script>
</body>
</html>