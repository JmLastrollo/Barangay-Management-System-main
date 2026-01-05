<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// 1. GET RESIDENT INFO & CALCULATE AGE
// Note: Siguraduhing may 'birthdate' at 'gender' column sa resident_profiles table mo
$stmt = $conn->prepare("SELECT resident_id, first_name, last_name, birthdate, gender FROM resident_profiles WHERE user_id = :uid");
$stmt->execute([':uid' => $user_id]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    die("Profile not found.");
}

// Calculate Age
$birthDate = new DateTime($resident['birthdate']);
$today = new DateTime();
$age = $today->diff($birthDate)->y;
$is_senior = ($age >= 60); // Senior Citizen Check (60 pataas)

// HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    
    $service = $_POST['service_type'];
    $date = $_POST['app_date'];
    $time = $_POST['app_time']; 
    $reason = trim($_POST['reason']);
    $resident_id = $resident['resident_id'];

    // Auto-append "Senior Citizen" tag sa reason kung senior para makita agad ng admin/staff
    if ($is_senior) {
        $reason = "[SENIOR PRIORITY] " . $reason;
    }

    try {
        // Validation: Bawal ang past dates
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            throw new Exception("Invalid date. You cannot book a past date.");
        }

        // Insert into health_appointments table
        $sql = "INSERT INTO health_appointments (resident_id, service_type, appointment_date, appointment_time, reason, status) 
                VALUES (:rid, :stype, :adate, :atime, :reason, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':rid' => $resident_id,
            ':stype' => $service,
            ':adate' => $date,
            ':atime' => $time,
            ':reason' => $reason
        ]);

        // SUCCESS TOAST (Redirect to History)
        $_SESSION['toast'] = ['msg' => 'Appointment booked successfully!', 'type' => 'success'];
        header("Location: resident_health_history.php");
        exit();

    } catch (Exception $e) {
        // ERROR TOAST (Stay on Page)
        $_SESSION['toast'] = ['msg' => 'Error: ' . $e->getMessage(), 'type' => 'error'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Appointment - Health Center</title>
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
            <h1 class="header-title">HEALTH <span class="green">CENTER</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-5">
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold text-dark m-0"><i class="bi bi-calendar-plus-fill text-danger me-2"></i>Book Appointment</h4>
                                
                                <?php if($is_senior): ?>
                                    <span class="badge bg-warning text-dark border border-warning px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-person-wheelchair me-1"></i> Senior Citizen (Priority)
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if($is_senior): ?>
                                <div class="alert alert-warning border-0 d-flex align-items-center mb-4 shadow-sm" role="alert">
                                    <i class="bi bi-info-circle-fill fs-4 me-3 text-warning"></i>
                                    <div>
                                        Hello, <strong><?= htmlspecialchars($resident['first_name']) ?></strong>! As a Senior Citizen, you are entitled to priority lanes and specialized geriatric services.
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <form action="" method="POST">
                                
                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Service Type</label>
                                    <select name="service_type" class="form-select form-select-lg" required>
                                        <option value="" disabled selected>Select Service...</option>
                                        
                                        <?php if($is_senior): ?>
                                            <option value="Geriatric Check-up (Senior Wellness)" class="fw-bold text-danger">★ Geriatric Check-up (Senior Wellness)</option>
                                            <option value="Maintenance Medicine Request">Maintenance Medicine Request</option>
                                        <?php endif; ?>

                                        <option value="General Check-up">General Check-up</option>
                                        <option value="Blood Pressure Monitoring">Blood Pressure Monitoring</option>
                                        <option value="Dental Check-up">Dental Check-up</option>
                                        
                                        <?php if(!$is_senior && $resident['gender'] == 'Female'): ?>
                                            <option value="Prenatal Check-up">Prenatal Check-up</option>
                                        <?php endif; ?>
                                        
                                        <option value="Pediatric Check-up">Pediatric Check-up (For Child)</option>
                                        <option value="Immunization">Immunization / Vaccination</option>
                                    </select>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="fw-bold text-muted small text-uppercase">Preferred Date</label>
                                        <input type="date" name="app_date" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-bold text-muted small text-uppercase">Preferred Time</label>
                                        <select name="app_time" class="form-select form-select-lg" required>
                                            <option value="" disabled selected>Select Time Slot</option>
                                            <option value="Morning (8:00 AM - 12:00 PM)">Morning (8:00 AM - 12:00 PM)</option>
                                            <option value="Afternoon (1:00 PM - 5:00 PM)">Afternoon (1:00 PM - 5:00 PM)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="fw-bold text-muted small text-uppercase">Reason / Symptoms</label>
                                    <textarea name="reason" class="form-control" rows="3" placeholder="Briefly describe your concern..." required></textarea>
                                </div>

                                <div class="d-grid gap-2 mt-5">
                                    <button type="submit" name="book_appointment" class="btn btn-danger btn-lg rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-2"></i> Confirm Booking
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Check for Session Toast (Error Messages)
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastBody = document.getElementById('toastMessage');
            
            toastBody.innerText = "<?= $_SESSION['toast']['msg'] ?>";
            toastEl.classList.remove('bg-success', 'bg-danger');
            toastEl.classList.add("<?= $_SESSION['toast']['type'] == 'success' ? 'bg-success' : 'bg-danger' ?>");
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>