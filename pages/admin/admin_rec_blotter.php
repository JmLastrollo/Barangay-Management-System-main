<?php require_once '../../backend/auth_admin.php'; ?>
<?php
require_once "../../backend/config.php";

$searchQuery = $_GET["search"] ?? "";

/* ALWAYS HIDE ARCHIVED RECORDS */
$filter = ["status" => ['$ne' => "archived"]];

if (!empty($searchQuery)) {
    $filter['$or'] = [
        ["respondent" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["complainant" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["subject" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["case_no" => ['$regex' => $searchQuery, '$options' => 'i']],
    ];
}

/* FIX: Use the correct collection name */
$incidents = $incidentsCollection->find($filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Admin Records Blotter</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/dashboard.css" />
</head>

<body>

<!-- SIDEBAR -->
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

        <div class="dropdown-container active">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Records
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
                <a href="admin_rec_complaints.php">Complaints</a>
                <a href="admin_rec_blotter.php" class="active">Blotter</a>
            </div>
        </div>

        <a href="../../backend/logout.php"><i class="bi bi-box-arrow-left"></i> Logout</a>
    </div>
</div>

<div style="width:100%">

    <div class="header">
        <div class="hamburger" onclick="toggleSidebar()">☰</div>
        <h1 class="header-title">RECORD <span class="green">BLOTTER</span></h1>

        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">

            <!-- SEARCH -->
            <form method="GET" class="search-box d-flex">
                <input type="text" name="search" class="form-control"
                    placeholder="Search for Case No., Respondent, Complainant..."
                    value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </form>

            <div class="add-archive-buttons">
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
                <a href="admin_rec_blotter_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archive
                </a>
            </div>
        </div>

        <!-- TABLE -->
        <table>
            <tr>
                <th>Case No.</th>
                <th>Respondent</th>
                <th>Complainant</th>
                <th>Date Filed</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php foreach ($incidents as $incident): ?>
                <?php 
                    $data = json_encode([
                        "_id" => (string)$incident->_id,
                        "case_no" => $incident->case_no,
                        "respondent" => $incident->respondent,
                        "complainant" => $incident->complainant,
                        "date_filed" => $incident->date_filed ?? "",
                        "date_happened" => $incident->date_happened ?? "",
                        "subject" => $incident->subject,
                        "description" => $incident->description,
                        "status" => $incident->status
                    ]);
                ?>
                <tr>
                    <td><?= $incident->case_no ?></td>
                    <td><?= $incident->respondent ?></td>
                    <td><?= $incident->complainant ?></td>
                    <td><?= !empty($incident->date_filed) ? date("Y-m-d", strtotime($incident->date_filed)) : "—" ?></td>
                    <td><?= $incident->subject ?></td>
                    <td><span class="status <?= strtolower($incident->status) ?>"><?= ucfirst($incident->status) ?></span></td>
                    <td>
                        <!-- VIEW -->
                        <a href="pdf_files/pdf_blotter.php?id=<?= $incident->_id ?>" 
                           target="_blank"
                           class="btn btn-sm btn-info text-white me-1">
                           <i class="bi bi-eye"></i>
                        </a>

                        <!-- EDIT -->
                        <button class="btn btn-sm btn-primary me-1 edit-btn" data-incident='<?= $data ?>'>
                            <i class="bi bi-pencil-square"></i>
                        </button>

                        <!-- ARCHIVE -->
                        <button class="btn btn-sm btn-secondary archive-btn" data-id="<?= (string)$incident->_id ?>">
                            <i class="bi bi-archive"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

    </div>

</div>

<!-- ADD MODAL (unchanged) -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">  
      <h4>Add New Blotter Record</h4>

      <form action="../../backend/blotter_add.php" method="POST"> 
        <div class="row">
          <div class="col-md-6">  
            <label>Case No.</label>
            <input type="text" name="case_no" class="form-control" required>
          </div>
        </div>  

        <div class="row mt-3">
          <div class="col-md-6">  
            <label>Date Filed</label>
            <input type="date" name="date_filed" class="form-control" required>
          </div>
          <div class="col-md-6">  
            <label>Date Happened</label>
            <input type="date" name="date_happened" class="form-control">
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">  
            <label>Complainant</label>
            <input type="text" name="complainant" class="form-control" required>
          </div>
          <div class="col-md-6">  
            <label>Respondent</label>
            <input type="text" name="respondent" class="form-control" required>
          </div>
        </div>

          <div class="mt-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required>
          </div>

          <div class="mt-3">
            <label>Description</label>
            <textarea name="description" class="form-control" required></textarea>
          </div>

          <div class="mt-3">
          <label>Status</label>
            <select name="status" class="form-control" required>
              <option value="open">Active</option>
              <option value="closed">Settled</option>
            </select>
          </div>

        <div class="mt-3 text-end">
          <button class="btn btn-success" type="submit">Save Changes</button>
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
      <h4>Edit Blotter Record</h4>

      <form action="../../backend/blotter_update.php" method="POST">

        <input type="hidden" name="blotter_id" id="e_id">

        <div class="row">
          <div class="col-md-6">
            <label>Case No.</label>
            <input type="text" name="case_no" id="e_case" class="form-control" required>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label>Date Filed</label>
            <input type="date" name="date_filed" id="e_date" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Date Happened</label>
            <input type="date" name="date_happened" id="e_happened" class="form-control" required>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-md-6">
            <label>Complainant</label>
            <input type="text" name="complainant" id="e_comp" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label>Respondent</label>
            <input type="text" name="respondent" id="e_res" class="form-control" required>
          </div>
        </div>

        <div class="mt-3">
          <label>Subject</label>
          <input type="text" name="subject" id="e_subject" class="form-control" required>
        </div>

        <div class="mt-3">
          <label>Description</label>
          <textarea name="description" id="e_desc" class="form-control" rows="4" required></textarea>
        </div>

        <div class="mt-3">
          <label>Status</label>
          <select name="status" id="e_status" class="form-select">
            <option value="active">Active</option>
            <option value="settled">Settled</option>
          </select>
        </div>

        <div class="mt-3 text-end">
          <button class="btn btn-success" type="submit">Save Changes</button>
          <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- ARCHIVE MODAL -->
<div class="modal fade" id="archiveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
      <h4>Archive Record</h4>
      <p>Are you sure you want to archive this blotter record?</p>

      <form action="../../backend/blotter_update.php" method="POST">
        <input type="hidden" name="blotter_id" id="archive_id">
        <input type="hidden" name="status" value="archived">
        <button type="submit" class="btn btn-warning">Yes, Archive</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </form>
    </div>
  </div>
</div>

<script src="assets/js/bootstrap.bundle.min.js"></script>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}

// Normalize dates to YYYY-MM-DD
function normalizeDate(val) {
    if (!val) return "";
    if (typeof val === "object" && val.$date) return val.$date.split("T")[0];
    if (typeof val === "string" && val.includes("T")) return val.split("T")[0];
    return val;
}

// EDIT MODAL
document.querySelectorAll('.edit-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = JSON.parse(this.dataset.incident);

        document.getElementById('e_id').value = d._id;
        document.getElementById('e_case').value = d.case_no;

        document.getElementById('e_date').value = normalizeDate(d.date_filed);
        document.getElementById('e_happened').value = normalizeDate(d.date_happened);

        document.getElementById('e_comp').value = d.complainant;
        document.getElementById('e_res').value = d.respondent;
        document.getElementById('e_subject').value = d.subject;
        document.getElementById('e_desc').value = d.description;
        document.getElementById('e_status').value = d.status;

        new bootstrap.Modal(document.getElementById('editModal')).show();
    });
});

// ARCHIVE MODAL
document.querySelectorAll('.archive-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('archive_id').value = this.dataset.id;
        new bootstrap.Modal(document.getElementById('archiveModal')).show();
    });
});
</script>

</body>
</html>
