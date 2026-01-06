<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// Get Resident Info
$stmt = $conn->prepare("SELECT resident_id, first_name, last_name, contact_no, email FROM resident_profiles WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) { die("Resident profile not found."); }

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_complaint'])) {
    
    $resident_id = $resident['resident_id'];
    $complainant = $resident['first_name'] . ' ' . $resident['last_name'];
    
    // Logic: Kung naka-check ang "Unknown", ang value ay "Unidentified Person"
    if (isset($_POST['is_unknown']) && $_POST['is_unknown'] == '1') {
        $respondent = "Unidentified Person";
    } else {
        $respondent = trim($_POST['respondent_name']);
        if (empty($respondent)) { $respondent = "Unidentified Person"; } // Fallback
    }

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
<body class="bg-light">

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
            <h1 class="header-title">FILE <span class="green">COMPLAINT</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-5">
            
            <div class="container">
                <div class="alert alert-primary border-0 shadow-sm rounded-3 d-flex align-items-center mb-4 p-3">
                    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                    <div>
                        <strong>Important:</strong> Filing a complaint is a serious matter. Please ensure all details are accurate. False reports may be subject to legal consequences.
                    </div>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="card border-0 shadow rounded-4">
                            <div class="card-header bg-white border-bottom p-4">
                                <h4 class="fw-bold text-dark m-0"><i class="bi bi-file-text-fill text-danger me-2"></i>Incident Report Form</h4>
                            </div>
                            <div class="card-body p-4 p-md-5">
                                
                                <form action="" method="POST">
                                    
                                    <h6 class="text-uppercase text-muted fw-bold mb-3 small">I. Respondent Details (Sino ang inirereklamo?)</h6>
                                    <div class="mb-4 bg-light p-3 rounded-3 border">
                                        <label class="form-label fw-bold text-dark">Name of Respondent / Suspect</label>
                                        
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><i class="bi bi-person-x-fill"></i></span>
                                            <input type="text" name="respondent_name" id="respondentInput" class="form-control form-control-lg" placeholder="Enter full name (if known)" required>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_unknown" value="1" id="unknownCheck">
                                            <label class="form-check-label text-muted" for="unknownCheck">
                                                I don't know the name / The suspect is unidentified (e.g., for Theft/Vandalism)
                                            </label>
                                        </div>
                                    </div>

                                    <h6 class="text-uppercase text-muted fw-bold mb-3 small mt-4">II. Incident Details (Ano, Kailan, at Saan?)</h6>
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Nature of Complaint</label>
                                            <select name="complaint_type" class="form-select form-select-lg" required>
                                                <option value="" disabled selected>Select Incident Type...</option>
                                                <option value="Theft">Theft / Robbery (Nakawan)</option>
                                                <option value="Physical Injury">Physical Injury (Pananakit)</option>
                                                <option value="Property Damage">Property Damage (Paninira)</option>
                                                <option value="Noise Complaint">Noise Complaint (Ingay)</option>
                                                <option value="Neighborhood Dispute">Neighborhood Dispute (Away Kapitbahay)</option>
                                                <option value="Harassment">Harassment / Threats</option>
                                                <option value="Others">Others</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold">Date of Incident</label>
                                            <input type="date" name="incident_date" class="form-control form-control-lg" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Place of Incident</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                                            <input type="text" name="incident_place" class="form-control form-control-lg" placeholder="Specific location (e.g. Near the Basketball Court, Block 5...)" required>
                                        </div>
                                    </div>

                                    <h6 class="text-uppercase text-muted fw-bold mb-3 small mt-4">III. Narrative (Salaysay)</h6>
                                    <div class="mb-4">
                                        <label class="form-label fw-bold">Detailed Description of Events</label>
                                        <textarea name="details" class="form-control" rows="6" placeholder="Please describe exactly what happened. Include specific times, actions, and any witnesses if available." required></textarea>
                                        <div class="form-text">Be as specific as possible. This will serve as your initial statement.</div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-5 pt-3 border-top">
                                        <a href="resident_dashboard.php" class="btn btn-light btn-lg px-4 rounded-pill">Cancel</a>
                                        <button type="submit" name="submit_complaint" class="btn btn-danger btn-lg px-5 rounded-pill fw-bold shadow-sm">
                                            <i class="bi bi-send-fill me-2"></i> Submit Report
                                        </button>
                                    </div>

                                </form>

                            </div>
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
        // --- LOGIC FOR UNKNOWN RESPONDENT ---
        const unknownCheck = document.getElementById('unknownCheck');
        const respondentInput = document.getElementById('respondentInput');

        unknownCheck.addEventListener('change', function() {
            if (this.checked) {
                respondentInput.value = ""; // Clear input
                respondentInput.disabled = true; // Disable input
                respondentInput.placeholder = "Marked as Unidentified";
                respondentInput.required = false; // Remove required attribute
            } else {
                respondentInput.disabled = false; // Enable input
                respondentInput.placeholder = "Enter full name (if known)";
                respondentInput.required = true; // Add required attribute back
            }
        });

        // --- TOAST NOTIFICATION ---
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            document.getElementById('toastMessage').innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            new bootstrap.Toast(toastEl).show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>