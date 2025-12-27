<?php
require_once '../../backend/auth_resident.php';
require_once '../../backend/config.php';

$email = $_SESSION['email'];
$user = $usersCollection->findOne(['email' => $email]);

if (!$user) {
    die("Error: User not found.");
}

$resident = $residentsCollection->findOne(['user_id' => $user['_id']]);

if (!$resident) {
    die("Error: Resident record not found.");
}

$userId = (string)$user['_id'];
$residentId = isset($resident['_id']) ? (string)$resident['_id'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Resident Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
    <link rel="stylesheet" href="../../css/toast.css" />
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <?php
            $profileImg = isset($resident['profile_image']) && $resident['profile_image'] !== ""
              ? "../../uploads/residents/" . $resident['profile_image']
              : "../../assets/img/profile.jpg";
            ?>
            <img src="<?= $profileImg ?>" alt="">

        <div>
            <h3><?= $resident['first_name'] . " " . $resident['last_name'] ?></h3>
            <small><?= $resident['email'] ?></small>
        </div>
    </div>

    <div class="sidebar-menu">
        <a href="resident_dashboard.php" class="active"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="resident_rqs_service.php"><i class="bi bi-newspaper"></i> Request Service</a>
        <a href="../../index.php"><i class="bi bi-arrow-down-left"></i> Return to Homepage</a>
    </div>

    <div class="sidebar-bottom">
      <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">

    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">RESIDENT <span class="green">DASHBOARD</span></h1>

        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="grid">

            <div class="card">
                <h3>Profile</h3><br>

                <?php
                    $profileImg = isset($resident['profile_image']) && $resident['profile_image'] !== ""
                        ? "../../uploads/residents/" . $resident['profile_image']
                        : "../../assets/img/profile.jpg";
                    ?>
                    <img src="<?= $profileImg ?>" alt="" style="width: auto; height: 300px; object-fit: cover;border-radius: 20px; margin-top: -25px">


                <strong style="margin-top:8px;"><?= $resident['first_name'] . " " . $resident['last_name'] ?></strong><br>

                <p><b>Email:</b> <?= $resident['email'] ?></p>
                <p><b>Occupation:</b> <?= $resident['occupation'] ?></p>
                <p><b>Family Income:</b> <?= $resident['income'] ?></p>
                <p><b>Resident Since:</b> <?= $resident['resident_since'] ?></p>

                <strong style="color:#2E9F43;">Account
                    <?= $user['status'] ?>
                </strong>

                <button class="update-btn" data-bs-toggle="modal" data-bs-target="#updateModal">
                    Update
                </button>

            </div>

            <div>
                <section class="calendar-container">
                    <div class="calendar-header">
                        <button id="prev-month">&lt;</button>
                        <h2 id="month-year"></h2>
                        <button id="next-month">&gt;</button>
                    </div>

                    <div class="calendar-grid" id="calendar-grid"></div>
                </section>

                <article class="timeline" id="timeline-events" style="margin-top:30px">
                    <p>Loading events...</p>
                </article>
            </div>

        </div>
    </div>

</div>

<!-- UPDATE MODAL -->
<div class="modal fade" id="updateModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">

      <div class="modal-header">
        <h5>Update Your Information</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="../../backend/update_resident_self.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body">

          <p class="text-muted">
            Only profile picture, name, occupation, civil, email, and contact can be updated. <br>
            Please contact the admin if other information is incorrect.
          </p>

          <input type="hidden" name="user_id" value="<?= $resident->_id ?>">
          <input type="hidden" name="existing_image" value="<?= $resident['profile_image'] ?? '' ?>">

          <!-- Profile Picture -->
          <div class="mb-3">
            <label>Profile Picture:</label>
            <input type="file" class="form-control" name="profile_image" accept="image/*">
            <img id="edit-preview" class="preview-img mt-2" src="" style="display:none; max-width: 200px;">
          </div>

          <!-- Editable fields -->
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <label>First Name:</label>
              <input class="form-control" name="fname" value="<?= $resident->first_name ?>">
            </div>
            <div class="col-md-6">
              <label>Middle Name:</label>
              <input class="form-control" name="mname" value="<?= $resident->middle_name ?>">
            </div>
            <div class="col-md-6">
              <label>Last Name:</label>
              <input class="form-control" name="lname" value="<?= $resident->last_name ?>">
            </div>
            <div class="col-md-6">
              <label>Occupation:</label>
              <input class="form-control" name="occupation" value="<?= $resident->occupation ?>">
            </div>
            <div class="col-md-6">
              <label>Email:</label>
              <input class="form-control" name="email" value="<?= $resident->email ?>">
            </div>
            <div class="col-md-6">
              <label>Contact:</label>
              <input class="form-control" name="contact" value="<?= $resident->contact ?>">
            </div>
            <div class="col-md-6">
              <label>Civil Status:</label>
              <select class="form-control" name="civil_status">
                <option value="Single"     <?= ($resident->civil_status == "Single") ? "selected" : "" ?>>Single</option>
                <option value="Married"    <?= ($resident->civil_status == "Married") ? "selected" : "" ?>>Married</option>
                <option value="Separated"  <?= ($resident->civil_status == "Separated") ? "selected" : "" ?>>Separated</option>
                <option value="Widowed"    <?= ($resident->civil_status == "Widowed") ? "selected" : "" ?>>Widowed</option>
              </select>
            </div>
          </div>

          <hr>

          <!-- Read-only preview in grid -->
          <h5>Other Information (Preview)</h5>
          <div class="row g-2">
            <div class="col-md-6"><b>Suffix:</b> <?= $resident->suffix ?></div>
            <div class="col-md-6"><b>Gender:</b> <?= $resident->gender ?></div>
            <div class="col-md-6"><b>Birthdate:</b> <?= date("F j, Y", strtotime($resident->birthdate)) ?></div>
            <div class="col-md-6"><b>Birthplace:</b> <?= $resident->birthplace ?></div>
            <div class="col-md-6"><b>Purok:</b> <?= $resident->purok ?></div>
            <div class="col-md-6"><b>Resident Since:</b> <?= $resident->resident_since ?></div>
            <div class="col-md-6"><b>Voter:</b> <?= $resident->voter ?></div>
            <div class="col-md-6"><b>Income:</b> <?= $resident->income ?></div>
            <div class="col-md-6"><b>Family Head:</b> <?= $resident->family_head ?></div>
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

<div id="toast" class="toast"></div>
<script src="../../assets/js/calendar.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
fetch("../../backend/announcement_get_dashboard.php")
    .then(res => res.json())
    .then(data => {
        let timelineHTML = "";

        data.forEach(event => {
            timelineHTML += `
                <div class="timeline-event mb-3">
                    <strong class="event-title">${event.title}</strong><br>
                    <span class="event-location"><i class="bi bi-geo-alt-fill me-1"></i>${event.location}</span><br>
                    <span class="event-datetime"><i class="bi bi-calendar-event me-1"></i>${event.time} | ${event.date}</span>
                </div>
            `;
        });

        document.getElementById("timeline-events").innerHTML =
            timelineHTML || "<p>No upcoming announcements.</p>";
    });
function openUpdateModal(data) {
    document.getElementById("u_resident_id").value = data._id;
    document.getElementById("u_fname").value = data.first_name;
    document.getElementById("u_mname").value = data.middle_name;
    document.getElementById("u_lname").value = data.last_name;
    document.getElementById("u_sname").value = data.suffix;
    document.getElementById("u_gender").value = data.gender;
    document.getElementById("u_bdate").value = data.birthdate;
    document.getElementById("u_bplace").value = data.birthplace;
    document.getElementById("u_purok").value = data.purok;
    document.getElementById("u_contact").value = data.contact;
    document.getElementById("u_occupation").value = data.occupation;
    document.getElementById("u_resident_since").value = data.resident_since;
    document.getElementById("u_email").value = data.email;
    document.getElementById("u_voter").value = data.voter;
    document.getElementById("u_income").value = data.income;
    document.getElementById("u_family_head").value = data.family_head;
}

document.querySelector('#updateModal form').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault(); // prevent Enter from submitting
    }
});

const updateModal = document.getElementById('updateModal');
updateModal.addEventListener('hidden.bs.modal', () => {
    const fileInput = updateModal.querySelector("input[name='profile_image']");
    const preview = document.getElementById("edit-preview");

    fileInput.value = "";       // clear file input
    preview.src = "";           // remove preview
    preview.style.display = "none";
});

document.querySelector("input[name='profile_image']").addEventListener("change", function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById("edit-preview");

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    } else {
        preview.style.display = "none";
    }
});

function showToast(message, type = "error") {
    const t = document.getElementById("toast");

    // Reset classes (VERY IMPORTANT)
    t.className = "toast";

    t.textContent = message;
    t.classList.add(type);
    t.classList.add("show");

    setTimeout(() => {
        t.classList.remove("show");
    }, 3000);
}
</script>
<?php if (isset($_SESSION['toast'])): ?>
<script>
    showToast("<?= $_SESSION['toast']['msg'] ?>", "<?= $_SESSION['toast']['type'] ?>");
</script>
<?php unset($_SESSION['toast'], $_SESSION['toast_type']); endif; ?>

</body>
</html>