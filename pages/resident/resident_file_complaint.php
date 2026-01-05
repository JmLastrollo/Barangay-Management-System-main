<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// Get Resident Info
$stmt = $conn->prepare("SELECT resident_id, first_name, last_name FROM resident_profiles WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) { die("Resident profile not found."); }

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    
    $resident_id = $resident['resident_id'];
    $complainant = $resident['first_name'] . ' ' . $resident['last_name'];
    $respondent = trim($_POST['respondent_name']);
    $type = $_POST['complaint_type'];
    $date = $_POST['incident_date'];
    $place = trim($_POST['incident_place']);
    $details = trim($_POST['details']);

    try {
        $sql = "INSERT INTO complaints (resident_id, complainant_name, respondent_name, complaint_type, incident_date, incident_place, details, status, date_filed) 
                VALUES (:rid, :cname, :rname, :ctype, :idate, :iplace, :det, 'Pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':rid' => $resident_id,
            ':cname' => $complainant,
            ':rname' => $respondent,
            ':ctype' => $type,
            ':idate' => $date,
            ':iplace' => $place,
            ':det' => $details
        ]);

        $_SESSION['toast'] = ['msg' => 'Complaint submitted successfully. Please wait for the barangay to contact you.', 'type' => 'success'];
        header("Location: resident_blotter_history.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>File Complaint - BMS</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">FILE <span class="green">COMPLAINT</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center mb-4">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                <div>
                    <strong>Disclaimer:</strong> Filing a false complaint is punishable by law. Please ensure all details are accurate.
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5">
                            
                            <h4 class="fw-bold text-dark mb-4"><i class="bi bi-pencil-square text-danger me-2"></i>Complaint Form</h4>
                            
                            <form action="" method="POST">
                                
                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Respondent's Name (Sino ang nirereklamo?)</label>
                                    <input type="text" name="respondent_name" class="form-control form-control-lg" placeholder="Enter name of person involved" required>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="fw-bold text-muted small text-uppercase">Type of Complaint</label>
                                        <select name="complaint_type" class="form-select form-select-lg" required>
                                            <option value="" disabled selected>Select Type...</option>
                                            <option value="Noise Complaint">Noise Complaint</option>
                                            <option value="Neighborhood Dispute">Neighborhood Dispute</option>
                                            <option value="Property Damage">Property Damage</option>
                                            <option value="Harassment">Harassment</option>
                                            <option value="Theft">Theft</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold text-muted small text-uppercase">Date of Incident</label>
                                        <input type="date" name="incident_date" class="form-control form-control-lg" required>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Place of Incident</label>
                                    <input type="text" name="incident_place" class="form-control" placeholder="Specific location in Brgy. Langkaan II" required>
                                </div>

                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Narrative Details</label>
                                    <textarea name="details" class="form-control" rows="5" placeholder="Please describe what happened..." required></textarea>
                                </div>

                                <div class="d-grid gap-2 mt-5">
                                    <button type="submit" name="submit_complaint" class="btn btn-danger btn-lg rounded-pill fw-bold">
                                        Submit Complaint
                                    </button>
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
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            document.getElementById('toastMessage').innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            new bootstrap.Toast(toastEl).show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>