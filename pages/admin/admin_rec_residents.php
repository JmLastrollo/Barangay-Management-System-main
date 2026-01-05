<?php require_once '../../backend/auth_admin.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Records Resident</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="">
        <div>
            <h3>Anonymous 1</h3>
            <small>admin@email.com</small>
            <div class="dept">IT Department</div>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="admin_dashboard.php"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Records
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php" class="active">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php">Blotter</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">

    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">RECORD <span class="green">RESIDENTS</span></h1>

        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

            <div class="search-box">
                <input type="text" id="search-input" placeholder="Search for Resident Name...">
                <button id="search-btn"><i class="bi bi-search"></i></button>
            </div>

            <div class="print-button">
                <a href="pdf_files/pdf_residents.php" target="_blank" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print
                </a>
            </div>  
        </div>

        <table id="residentTable">
            <tr>
                <th>Resident Name</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Purok</th>
                <th>Contact No.</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php
            require_once "../../backend/config.php";

            $residents = $residentsCollection->aggregate([
                [
                    '$lookup' => [
                        'from' => 'users',
                        'localField' => 'user_id',
                        'foreignField' => '_id',
                        'as' => 'user'
                    ]
                ],
                [ '$unwind' => '$user' ],
                [
                    '$sort' => [
                        'user.date_created' => -1
                    ]
                ]
            ]);




            foreach ($residents as $r) {

                // Fetch login info from USERS table
                $user = $usersCollection->findOne(["_id" => $r->user_id]);
                $status = $user->status ?? "Pending";

                ?>
                <tr
                    data-id="<?= $r->_id ?>"
                    data-fname="<?= $r->first_name ?>"
                    data-mname="<?= $r->middle_name ?? '' ?>"
                    data-lname="<?= $r->last_name ?>"
                    data-sname="<?= $r->suffix ?>"
                    data-gender="<?= $r->gender ?>"
                    data-bdate="<?= $r->birthdate ?>"
                    data-bplace="<?= $r->birthplace ?>"
                    data-occupation="<?= $r->occupation ?>"
                    data-voter="<?= $r->voter ?>"
                    data-purok="<?= $r->purok ?>"
                    data-email="<?= $r->email ?>"
                    data-contact="<?= $r->contact ?>"
                    data-income="<?= $r->income ?>"
                    data-family-head="<?= $r->family_head ?>"
                    data-status="<?= $status ?>"
                    data-civil-status="<?= $r->civil_status ?? '' ?>"
                    data-resident-since="<?= $r->resident_since ?? '' ?>"

                >

                    <td><?= $r->first_name . " " . $r->last_name ?></td>

                    <td>
                        <?php
                        $age = date_diff(date_create($r->birthdate), date_create('today'))->y;
                        echo $age;
                        ?>
                    </td>

                    <td><?= $r->gender ?></td>
                    <td><?= $r->purok ?></td>
                    <td><?= $r->contact ?></td>

                    <td>
                        <span class="status <?= $status == 'Pending' ? 'pending' : ($status == 'Approved' ? 'ready' : 'decline') ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </td>

                    <td>
                        <button class="btn btn-sm btn-info text-white view-btn"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-primary edit-btn"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
            <?php } ?>

        </table>

    </div>

</div>

<!-- ======================== VIEW MODAL ======================== -->
<div class="modal fade" id="viewModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resident Details</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-2">
                    <div class="col-md-6"><strong>Full Name:</strong> <span id="v-name"></span></div>
                    <div class="col-md-6"><strong>Gender:</strong> <span id="v-gender"></span></div>

                    <div class="col-md-6"><strong>Civil Status:</strong> <span id="v-civil-status"></span></div>

                    <div class="col-md-6"><strong>Birth Date:</strong> <span id="v-bdate"></span></div>
                    <div class="col-md-6"><strong>Birth Place:</strong> <span id="v-bplace"></span></div>

                    <div class="col-md-6"><strong>Purok:</strong> <span id="v-purok"></span></div>
                    <div class="col-md-6"><strong>Resident Since:</strong> <span id="v-resident-since"></span></div>

                    <div class="col-md-6"><strong>Occupation:</strong> <span id="v-occupation"></span></div>
                    <div class="col-md-6"><strong>Voter Status:</strong> <span id="v-voter"></span></div>

                    <div class="col-md-6"><strong>Email:</strong> <span id="v-email"></span></div>
                    <div class="col-md-6"><strong>Contact:</strong> <span id="v-contact"></span></div>

                    <div class="col-md-6"><strong>Income:</strong> <span id="v-income"></span></div>
                    <div class="col-md-6"><strong>Family Head:</strong> <span id="v-family-head"></span></div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================== EDIT MODAL ======================== -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Resident</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form action="../../backend/update_resident.php" method="POST">

                    <input type="hidden" name="user_id" id="e-user-id">

                    <div class="row g-2">

                        <div class="col-md-6">First Name:<input class="form-control" name="fname" id="e-fname"></div>
                        <div class="col-md-6">Middle Name:<input class="form-control" name="mname" id="e-mname"></div>

                        <div class="col-md-6">Last Name:<input class="form-control" name="lname" id="e-lname"></div>
                        <div class="col-md-6">Suffix:<input class="form-control" name="sname" id="e-sname"></div>

                        <div class="col-md-6">Birth Date:<input type="date" class="form-control" name="bdate" id="e-bdate"></div>
                        <div class="col-md-6">Birth Place:<input class="form-control" name="bplace" id="e-bplace"></div>

                        <div class="col-md-6">
                            Gender:
                            <select class="form-control" name="gender" id="e-gender">
                                <option>Male</option>
                                <option>Female</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            Purok:
                            <select class="form-control" name="purok" id="e-purok">
                                <option>Purok 1</option>
                                <option>Purok 2</option>
                                <option>Purok 3</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            Civil Status:
                            <select class="form-control" name="civil_status" id="e-civil-status">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Separated">Separated</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>


                        <div class="col-md-6">Occupation:<input class="form-control" name="occupation" id="e-occupation"></div>
                        <div class="col-md-6">Resident Since:<input class="form-control" name="resident_since" id="e-resident-since"></div>

                        <div class="col-md-6">Email:<input class="form-control" name="email" id="e-email"></div>
                        <div class="col-md-6">Contact:<input class="form-control" name="contact" id="e-contact"></div>

                        <div class="col-md-6">
                            Voter Status:
                            <select class="form-control" name="voter_status" id="e-voter">
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            Family Income:
                            <select class="form-control" name="income" id="e-income">
                                <option>Below PHP 10,000</option>
                                <option>PHP 10,000 - 20,000</option>
                                <option>PHP 20,000+</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            Family Head:
                            <select class="form-control" name="family_head" id="e-family-head">
                                <option>Yes</option>
                                <option>No</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            Account Status:
                            <select class="form-control" name="status" id="edit_status">
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save Changes</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

<!-- ======================== DELETE MODAL ======================== -->
<div class="modal fade" id="deleteModal">
    <div class="modal-dialog">
        <form action="../../backend/delete_resident.php" method="POST" class="modal-content">

            <div class="modal-header"><h5>Confirm Delete</h5></div>

            <div class="modal-body">
                Are you sure you want to delete <strong id="d-name"></strong>?
                <input type="hidden" name="resident_id" id="d-id">
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>

        </form>
    </div>
</div>


<script src="../../assets/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

document.querySelectorAll('.dropdown-container').forEach(container => {
    if (container.querySelector('.dropdown-content a.active')) {
        container.classList.add('active');
    }
});

document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        this.parentElement.classList.toggle('active');
    });
});

// ===== VIEW MODAL =====
document.querySelectorAll(".view-btn").forEach(btn => {
    btn.addEventListener("click", () => {
        let r = btn.closest("tr");

        document.getElementById("v-name").innerText =
            `${r.dataset.fname} ${r.dataset.mname} ${r.dataset.lname} ${r.dataset.sname}`.trim();

        document.getElementById("v-gender").innerText = r.dataset.gender;
        document.getElementById("v-bdate").innerText = r.dataset.bdate;
        document.getElementById("v-bplace").innerText = r.dataset.bplace;
        document.getElementById("v-civil-status").innerText = r.dataset.civilStatus;
        document.getElementById("v-occupation").innerText = r.dataset.occupation;
        document.getElementById("v-voter").innerText = r.dataset.voter;
        document.getElementById("v-purok").innerText = r.dataset.purok;
        document.getElementById("v-resident-since").innerText = r.dataset.residentSince;
        document.getElementById("v-email").innerText = r.dataset.email;
        document.getElementById("v-contact").innerText = r.dataset.contact;
        document.getElementById("v-income").innerText = r.dataset.income;
        document.getElementById("v-family-head").innerText = r.dataset.familyHead;

        new bootstrap.Modal(document.getElementById("viewModal")).show();
    });
});

// ===== EDIT MODAL =====
document.querySelectorAll(".edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {

        let r = btn.closest("tr");

        document.getElementById("e-user-id").value = r.dataset.id;

        document.getElementById("e-fname").value = r.dataset.fname;
        document.getElementById("e-mname").value = r.dataset.mname;
        document.getElementById("e-lname").value = r.dataset.lname;
        document.getElementById("e-sname").value = r.dataset.sname;

        document.getElementById("e-bdate").value = r.dataset.bdate;
        document.getElementById("e-bplace").value = r.dataset.bplace;

        document.getElementById("e-gender").value = r.dataset.gender;
        document.getElementById("e-purok").value = r.dataset.purok;

        document.getElementById("e-civil-status").value = r.dataset.civilStatus;

        document.getElementById("e-resident-since").value = r.dataset.residentSince;
        document.getElementById("e-occupation").value = r.dataset.occupation;

        document.getElementById("e-email").value = r.dataset.email;
        document.getElementById("e-contact").value = r.dataset.contact;

        document.getElementById("e-voter").value = r.dataset.voter;
        document.getElementById("e-income").value = r.dataset.income;
        document.getElementById("e-family-head").value = r.dataset.familyHead;

        document.getElementById("edit_status").value = r.dataset.status;

        new bootstrap.Modal(document.getElementById("editModal")).show();
    });
});

// ===== DELETE MODAL =====
document.querySelectorAll(".delete-btn").forEach(btn => {
    btn.addEventListener("click", () => {

        let r = btn.closest("tr");

        document.getElementById("d-name").innerText =
            `${r.dataset.fname} ${r.dataset.lname}`;

        document.getElementById("d-id").value = r.dataset.id;

        new bootstrap.Modal(document.getElementById("deleteModal")).show();
    });
});

document.getElementById("search-input").addEventListener("keyup", function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#residentTable tbody tr");

    rows.forEach(row => {
        let fullName = (
            row.dataset.fname + " " +
            row.dataset.mname + " " +
            row.dataset.lname + " " +
            row.dataset.sname
        ).toLowerCase();

        if (fullName.includes(value)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

document.getElementById("search-btn").addEventListener("click", () => {
    document.getElementById("search-input").dispatchEvent(new Event("keyup"));
});

</script>

</body>
</html>
