<?php require_once '../../backend/auth_admin.php'; ?>
<?php
require_once "../../backend/config.php";

$searchQuery = $_GET["search"] ?? "";

/* FILTER ARCHIVED ONLY */
$filter = ["status" => "archived"];

if (!empty($searchQuery)) {
    $filter['$or'] = [
        ["respondent" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["complainant" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["subject" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["case_no" => ['$regex' => $searchQuery, '$options' => 'i']],
    ];
}

$incidents = $incidentsCollection->find($filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Blotter</title>
    <link rel="icon" type="image/png" href="../../assets/img/BMS.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
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
        <div class="dropdown-container">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Archives
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_announcement_archive.php"><i class="bi bi-megaphone"></i> Announcement</a>
                <a href="admin_officials_archive.php"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>
                <a href="admin_rec_complaints_archive.php"><i class="bi bi-file-earmark-text"></i> Complaints</a>
                <a href="admin_rec_blotter_archive.php" class="active"><i class="bi bi-file-earmark-text"></i> Blotter</a>
            </div>
            <a href="admin_rec_blotter.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div style="width:100%">

    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">BLOTTER</span></h1>
    </div>

    <div class="content">

            <form method="GET" class="search-box d-flex">
                <input type="text" name="search" class="form-control"
                       placeholder="Search for Case No., Respondent, Complainant..."
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </form>

            

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Case No.</th>
                    <th>Respondent</th>
                    <th>Complainant</th>
                    <th>Date Filed</th>
                    <th>Subject</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidents as $item): ?>
                    <tr>
                        <td><?= $item->case_no ?></td>
                        <td><?= $item->respondent ?></td>
                        <td><?= $item->complainant ?></td>
                        <td><?= date("m/d/Y", strtotime($item->date_filed)) ?></td>
                        <td><?= $item->subject ?></td>

                        <td>
                            <!-- VIEW -->
                            <button class="btn btn-sm btn-info me-1 text-white"
                                onclick='openViewModal(<?= json_encode($item) ?>)'>
                                <i class="bi bi-eye"></i>
                            </button>

                            <!-- RESTORE -->
                            <button class="btn btn-sm btn-success me-1"
                                onclick='openRestoreModal("<?= $item->_id ?>")'>
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>

                            <!-- DELETE -->
                            <button class="btn btn-sm btn-danger"
                                onclick='openDeleteModal("<?= $item->_id ?>")'>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                <?php endforeach; ?>

        </table>

    </div>

</div>

<!-- ======================== VIEW MODAL ======================== -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
        <h4>Blotter Details</h4>
        <p><b>Case No:</b> <span id="v_case"></span></p>
        <p><b>Respondent:</b> <span id="v_res"></span></p>
        <p><b>Complainant:</b> <span id="v_comp"></span></p>
        <p><b>Date Filed:</b> <span id="v_date"></span></p>
        <p><b>Date Happened:</b> <span id="v_happened"></span></p>
        <p><b>Subject:</b> <span id="v_subject"></span></p>
        <p><b>Description:</b></p>
        <p id="v_desc" class="border p-2"></p>

        <button class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
    </div>
  </div>
</div>

<!-- ======================== RESTORE MODAL ======================== -->
<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Restore Record</h4>
        <p>Are you sure you want to restore this record?</p>

        <form action="../../backend/blotter_update.php" method="POST">
            <input type="hidden" name="blotter_id" id="r_id">
            <input type="hidden" name="status" value="active">
            <button class="btn btn-success" type="submit">Restore</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
    </div>
  </div>
</div>

<!-- ======================== DELETE MODAL ======================== -->
<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Delete Permanently</h4>
        <p>Are you sure you want to permanently delete this record?</p>

        <form action="../../backend/blotter_delete.php" method="POST">
            <input type="hidden" name="blotter_id" id="d_id">
            <button class="btn btn-danger" type="submit">Delete</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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

function openViewModal(data) {
    document.getElementById('v_case').textContent = data.case_no;
    document.getElementById('v_res').textContent = data.respondent;
    document.getElementById('v_comp').textContent = data.complainant;
    document.getElementById('v_date').textContent = data.date_filed;
    document.getElementById('v_happened').textContent = data.date_happened;
    document.getElementById('v_subject').textContent = data.subject;
    document.getElementById('v_desc').textContent = data.description;

    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function openRestoreModal(id) {
    document.getElementById('r_id').value = id.$oid ?? id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

function openDeleteModal(id) {
    document.getElementById('d_id').value = id.$oid ?? id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

</body>
</html>
