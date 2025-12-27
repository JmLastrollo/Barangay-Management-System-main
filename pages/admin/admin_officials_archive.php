<?php require_once '../../backend/auth_admin.php'; ?>
<?php
require_once "../../backend/config.php";

$searchQuery = $_GET["search"] ?? "";

/* FILTER ARCHIVED ONLY */
$filter = ["status" => "archived"];

if (!empty($searchQuery)) {
    $filter['$or'] = [
        ["name" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["position" => ['$regex' => $searchQuery, '$options' => 'i']],
    ];
}

$officials = $officialsCollection->find($filter);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Officials</title>
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
                <a href="admin_officials_archive.php" class="active"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>
                <a href="admin_rec_complaints_archive.php"><i class="bi bi-file-earmark-text"></i> Complaints</a>
                <a href="admin_rec_blotter_archive.php"><i class="bi bi-file-earmark-text"></i> Blotter</a>
            </div>
            <a href="admin_officials.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">OFFICIALS</span></h1>
    </div>

    <div class="content">

            <form method="GET" class="search-box d-flex">
                <input type="text" name="search" class="form-control"
                       placeholder="Search by Name or Position..."
                       value="<?= htmlspecialchars($searchQuery) ?>">
                <button class="search-btn"><i class="bi bi-search"></i></button>
            </form>

        <table class="table officials-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($officials as $item): ?>
                <tr>
                    <td>
                        <?php if (!empty($item->image)): ?>
                            <img src="../../uploads/officials/<?= $item->image ?>" style="width:300px;height:200px;object-fit:cover;border-radius:5px;">
                        <?php endif; ?>
                    </td>
                    <td><?= $item->name ?></td>
                    <td><?= $item->position ?></td>
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

<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
        <h4>Official Details</h4>
        <p><b>Name:</b> <span id="v_name"></span></p>
        <p><b>Position:</b> <span id="v_position"></span></p>
        <p>
            <b>Image:</b> 
            <img id="v_image" src="" style="width:100%;height:auto;border-radius:5px;">
        </p>
        <button class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
    </div>
  </div>
</div>

<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Restore Officials</h4>
        <p>Are you sure you want to restore this official?</p>

        <form action="../../backend/officials_update.php" method="POST">
            <input type="hidden" name="id" id="r_id">
            <input type="hidden" name="status" value="active">
            <button class="btn btn-success" type="submit">Restore</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Delete Permanently</h4>
        <p>Are you sure you want to permanently delete this official?</p>

        <form action="../../backend/officials_delete.php" method="POST">
            <input type="hidden" name="id" id="d_id">
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
    document.getElementById('v_name').textContent = data.name;
    document.getElementById('v_position').textContent = data.position;
    document.getElementById('v_image').src = data.image ? `../../uploads/officials/${data.image}` : '';
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
