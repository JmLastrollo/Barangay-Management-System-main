<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../login.php");
    exit();
}

require_once '../../backend/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Resident Accounts</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">RESIDENT <span class="green">ACCOUNTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search Name..." class="form-control" aria-label="Search Resident">
                        <button type="button" aria-label="Search"><i class="bi bi-search"></i></button>
                    </div>
                    <select id="phaseFilter" class="form-select" style="width: auto; border-radius: 20px; cursor: pointer;" aria-label="Filter Phase">
                        <option value="">All Phases</option>
                        <option value="Phase 1">Phase 1</option>
                        <option value="Phase 2">Phase 2</option>
                        <option value="Phase 3">Phase 3</option>
                        <option value="Phase 4">Phase 4</option>
                        <option value="Phase 5">R 5</option>
                    </select>
                    <select id="statusFilter" class="form-select" style="width: auto; border-radius: 20px; cursor: pointer;" aria-label="Filter Status">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                
                <div class="mt-2 mt-md-0 d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Add New
                    </button>
                    <a href="resident_archive.php" class="btn btn-secondary">
                        <i class="bi bi-archive-fill me-1"></i> Archives
                    </a>
                    <button class="btn btn-outline-primary" onclick="loadResidents()" title="Refresh List">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Resident Name</th>
                            <th>Phase / Address</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="residentTableBody">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Loading residents...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addResidentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered"> <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Resident (Walk-in)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/resident_add.php" method="POST" enctype="multipart/form-data" id="addResidentForm">
                        
                        <div class="row">
                            <div class="col-lg-3 text-center border-end">
                                <div class="mb-4">
                                     <div class="d-inline-block position-relative">
                                        <img id="add-preview" src="../../assets/img/profile.jpg" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid #eee;">
                                     </div>
                                     <div class="mt-3">
                                        <label for="add-photo" class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-camera"></i> Upload Photo</label>
                                        <input type="file" name="photo" id="add-photo" class="d-none" accept="image/*">
                                     </div>
                                     <small class="text-muted d-block mt-2" style="font-size: 0.8rem;">Optional. Default image will be used if skipped.</small>
                                </div>

                                <hr class="my-3">
                                
                                <h6 class="text-start fw-bold text-primary mb-3">Login Credentials</h6>
                                <div class="mb-3 text-start">
                                    <label class="form-label small fw-bold">Email Address</label>
                                    <input type="email" name="email" class="form-control form-control-sm" required>
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label small fw-bold">Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="password" id="add_password" class="form-control" placeholder="Min. 8 chars" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('add_password', 'icon-add-pass')"><i class="bi bi-eye" id="icon-add-pass"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3 text-start">
                                    <label class="form-label small fw-bold">Confirm Password</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="cpassword" id="add_cpassword" class="form-control" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePass('add_cpassword', 'icon-add-cpass')"><i class="bi bi-eye" id="icon-add-cpass"></i></button>
                                    </div>
                                    <span id="password-match-msg" class="text-danger small fw-bold"></span>
                                </div>
                            </div>

                            <div class="col-lg-9 ps-lg-4">
                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-person-lines-fill me-2"></i>Personal Information</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">First Name</label>
                                        <input type="text" name="first_name" id="add_fname" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Middle Name</label>
                                        <input type="text" name="middle_name" id="add_mname" class="form-control">
                                    
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Last Name</label>
                                        <input type="text" name="last_name" id="add_lname" class="form-control" required>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Birthdate</label>
                                        <input type="date" name="birthdate" class="form-control" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Civil Status</label>
                                        <select name="civil_status" class="form-select">
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Widowed">Widowed</option>
                                            <option value="Separated">Separated</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Occupation</label>
                                        <input type="text" name="occupation" class="form-control">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Monthly Income</label>
                                        <select name="monthly_income" class="form-select">
                                            <option value="" disabled selected>Select Range</option>
                                            <option value="Below PHP 10,000">Below PHP 10,000</option>
                                            <option value="PHP 10,000 - 20,000">PHP 10,000 - 20,000</option>
                                            <option value="PHP 20,000+">PHP 20,000+</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Contact No.</label>
                                        <input type="text" name="contact_no" class="form-control" required>
                                    </div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Address & Residence</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-8">
                                            <label class="form-label small fw-bold">Street / Block / Lot</label>
                                        <input type="text" name="address" class="form-control" required>
                                    </div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Phase</label>
                                        <select name="purok" class="form-select" required>
                                            <option value="" disabled selected>Select</option>
                                            <option value="Phase 1">Phase 1</option>
                                            <option value="Phase 2">Phase 2</option>
                                            <option value="Phase 3">Phase 3</option>
                                            <option value="Phase 4">Phase 4</option>
                                            <option value="R 5">R 5</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">City/Municipality</label><input type="text" name="city" class="form-control" required></div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Province</label><input type="text" name="province" class="form-control" required></div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Household No.</label><input type="text" name="household_no" class="form-control" required></div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Resident Since (Year)</label><input type="number" name="resident_since" id="add_res_year" class="form-control" min="1900" max="2099" required></div>
                                </div>

                                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle-fill me-2"></i>Other Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4"><label class="form-label small fw-bold">PWD?</label>
                                        <select name="is_pwd" class="form-select">\
                                            <option value="" disabled selected>Select</option>
                                            <option value="No">No</option>
                                            <option value="Yes">Yes</option></select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Voter Status</label>
                                        <select name="voter_status" class="form-select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="Registered">Registered</option>
                                            <option value="Not Registered">Not Registered</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label class="form-label small fw-bold">Family Head?</label>
                                        <select name="is_family_head" class="form-select">
                                            <option value="" disabled selected>Select</option>
                                            <option value="No">No</option><option value="Yes">Yes</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold" id="btnAddSubmit">Create Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
                <div class="modal-profile-header">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="profile-img-container">
                        <img id="v_image" src="../../assets/img/profile.jpg" alt="Profile">
                    </div>
                </div>
                <div class="modal-body pb-4 px-4" style="margin-top: 50px;">
                    <div class="text-center mb-4">
                        <h3 class="fw-bold mb-1" id="v_name"></h3>
                        <p class="text-muted mb-2" id="v_email"></p>
                        <span id="v_status_badge" class="badge rounded-pill px-3 py-2 border"></span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-3 col-6"><div class="info-label">Age / Senior</div><div class="info-value" id="v_age"></div></div>
                        <div class="col-md-3 col-6"><div class="info-label">PWD Status</div><div class="info-value" id="v_pwd"></div></div>
                        <div class="col-md-3 col-6"><div class="info-label">Voter Status</div><div class="info-value" id="v_voter"></div></div>
                        <div class="col-md-3 col-6"><div class="info-label">Gender</div><div class="info-value" id="v_gender"></div></div>
                        <div class="col-md-6"><div class="info-label">Phase (Purok)</div><div class="info-value" id="v_phase"></div></div>
                        <div class="col-md-6"><div class="info-label">Contact Number</div><div class="info-value" id="v_contact"></div></div>
                        <div class="col-12"><div class="info-label">Full Address</div><div class="info-value" id="v_address"></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Birthdate</div><div class="info-value" id="v_bday"></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Occupation</div><div class="info-value" id="v_occupation"></div></div>
                        <div class="col-md-4 col-6"><div class="info-label">Civil Status</div><div class="info-value" id="v_civil"></div></div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0 justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary px-4" id="btnManageStatus"><i class="bi bi-shield-lock me-1"></i> Manage Account Status</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editResidentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Resident Info</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/resident_update.php" method="POST">
                        <input type="hidden" name="resident_id" id="edit_id">
                        <div class="row g-3">
                            <div class="col-md-4"><label class="form-label">First Name</label><input type="text" name="first_name" id="edit_fname" class="form-control" required></div>
                            <div class="col-md-4"><label class="form-label">Middle Name</label><input type="text" name="middle_name" id="edit_mname" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">Last Name</label><input type="text" name="last_name" id="edit_lname" class="form-control" required></div>
                            <div class="col-md-6"><label class="form-label">Contact No.</label><input type="text" name="contact_no" id="edit_contact" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                            <div class="col-md-6"><label class="form-label">Phase</label>
                                <select name="purok" id="edit_purok" class="form-select">
                                    <option value="Phase 1">Phase 1</option><option value="Phase 2">Phase 2</option><option value="Phase 3">Phase 3</option><option value="Phase 4">Phase 4</option><option value="Phase 5">Phase 5</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" id="edit_address" class="form-control"></div>
                            <div class="col-md-4"><label class="form-label">PWD?</label><select name="is_pwd" id="edit_pwd" class="form-select"><option value="Yes">Yes</option><option value="No">No</option></select></div>
                            <div class="col-md-4"><label class="form-label">Voter?</label><select name="voter_status" id="edit_voter" class="form-select"><option value="Registered">Registered</option><option value="Not Registered">Not Registered</option></select></div>
                            <div class="col-md-4"><label class="form-label">Occupation</label><input type="text" name="occupation" id="edit_occupation" class="form-control"></div>
                        </div>
                        <div class="text-end mt-4"><button type="submit" class="btn btn-success px-4">Save Changes</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Manage Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/resident_update_status.php" method="POST">
                        <input type="hidden" name="resident_id" id="status_id">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Account Status</label>
                            <select name="status" id="status_val" class="form-select">
                                <option value="Active">Active (Allowed)</option>
                                <option value="Archived">Archived (Inactive)</option>
                                <option value="Rejected">Rejected (Blocked)</option>
                            </select>
                        </div>
                        <div class="d-grid"><button type="submit" class="btn btn-primary">Update Status</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning-subtle">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-shield-lock-fill me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-3"><i class="bi bi-exclamation-circle text-warning" style="font-size: 3rem;"></i></div>
                    <h5 class="fw-bold">Are you sure?</h5>
                    <p class="text-muted">This will reset the password for <br><span id="reset_name" class="fw-bold text-dark"></span></p>
                    <div class="alert alert-secondary d-inline-block px-4 py-2">New Default Password: <strong>12345678</strong></div>
                </div>
                <div class="modal-footer border-0 justify-content-center">
                    <form action="../../backend/resident_reset_password.php" method="POST">
                        <input type="hidden" name="resident_id" id="reset_id">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning px-4 fw-bold">Confirm Reset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="toast" class="toast"></div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/admin/admin_resident.js"></script>

</body>
</html>