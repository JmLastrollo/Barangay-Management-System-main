<?php 
require_once '../../backend/auth_admin.php'; 
require_once "../../backend/config.php";

$searchQuery = $_GET["search"] ?? "";

/* FILTER ARCHIVED ONLY */
$filter = ["status" => "archived"];

if (!empty($searchQuery)) {
    $filter['$or'] = [
        ["title" => ['$regex' => $searchQuery, '$options' => 'i']],
        ["details" => ['$regex' => $searchQuery, '$options' => 'i']],
    ];
}

// Convert cursor to array to allow multiple loops safely
$announcements = iterator_to_array($announcementCollection->find($filter));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>BMS - Archived Announcements</title>
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
                <a href="admin_announcement_archive.php" class="active"><i class="bi bi-megaphone"></i> Announcement</a>
                <a href="admin_officials_archive.php"><i class="bi bi-people"></i> Officials</a>
                <a href="admin_issuance_archive.php"><i class="bi bi-file-earmark-text"></i> Issuance</a>
                <a href="admin_rec_complaints_archive.php"><i class="bi bi-file-earmark-text"></i> Complaints</a>
                <a href="admin_rec_blotter_archive.php"><i class="bi bi-file-earmark-text"></i> Blotter</a>
            </div>
            <a href="admin_announcement.php"><i class="bi bi-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

<div style="width:100%">
    <div class="header">
        <h1 class="header-title">ARCHIVED <span class="green">ANNOUNCEMENTS</span></h1>
    </div>

    <div class="content">
        <form method="GET" class="search-box d-flex mb-3">
            <input type="text" name="search" class="form-control"
                   placeholder="Search by Title or Details..."
                   value="<?= htmlspecialchars($searchQuery) ?>">
            <button class="search-btn"><i class="bi bi-search"></i></button>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($announcements)): ?>
                    <tr><td colspan="7" class="text-center">No archived announcements found.</td></tr>
                <?php else: ?>
                    <?php foreach ($announcements as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item->image)): ?>
                                <img src="../../uploads/announcements/<?= htmlspecialchars($item->image) ?>" style="width:150px;height:auto;border-radius:5px;">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($item->title) ?></td>
                        <td>
                            <?php 
                                $maxLength = 30;
                                $details = $item->details ?? '';
                                echo strlen($details) > $maxLength 
                                    ? htmlspecialchars(substr($details, 0, $maxLength)) . '...' 
                                    : htmlspecialchars($details);
                            ?>
                        </td>
                        <td>
                            <?php
                                $maxLengthLoc = 20;
                                $location = $item->location ?? '-';
                                echo strlen($location) > $maxLengthLoc 
                                    ? htmlspecialchars(substr($location, 0, $maxLengthLoc)) . '...' 
                                    : htmlspecialchars($location);
                            ?>
                        </td>
                        <td><?= htmlspecialchars($item->date) ?></td>
                        <td><?= htmlspecialchars($item->time) ?></td>
                        <td>
                            <button class="btn btn-sm btn-info me-1 text-white"
                                onclick='openViewModal(<?= json_encode($item) ?>)'>
                                <i class="bi bi-eye"></i>
                            </button>

                            <button class="btn btn-sm btn-success me-1"
                                onclick='openRestoreModal("<?= $item->_id ?>")'>
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </button>

                            <button class="btn btn-sm btn-danger"
                                onclick='openDeleteModal("<?= $item->_id ?>")'>
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                    <?php endforeach; ?>
                <?php endif; ?>
            
        </table>
    </div>
</div>

<!-- ======================== VIEW MODAL ======================== -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content p-3">
      <div class="modal-header">
        <h4 class="modal-title" id="v_title">Announcement Details</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">

        <!-- Date & Time -->
        <p class="text-muted mb-1">
          <i class="bi bi-calendar"></i> <span id="v_date"></span>
          &nbsp; | &nbsp; <i class="bi bi-clock"></i> <span id="v_time"></span>
        </p>

        <!-- Location -->
        <p class="text-muted mb-3">
          <i class="bi bi-geo-alt"></i> <span id="v_location"></span>
        </p>

        <!-- Image -->
        <div class="text-center mb-3">
          <img id="v_image" src="" class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
        </div>

        <!-- Details -->
        <p id="v_details" style="white-space: pre-wrap;"></p>

      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<!-- ======================== RESTORE MODAL ======================== -->
<div class="modal fade" id="restoreModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Restore Announcement</h4>
        <p>Are you sure you want to restore this announcement?</p>

        <form action="../../backend/announcement_update.php" method="POST">
            <input type="hidden" name="id" id="r_id">
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
        <p>Are you sure you want to permanently delete this announcement?</p>

        <form action="../../backend/announcement_delete.php" method="POST">
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
    document.getElementById('v_title').textContent = data.title;
    document.getElementById('v_details').textContent = data.details;
    document.getElementById('v_location').textContent = data.location;
    document.getElementById('v_date').textContent = data.date;
    document.getElementById('v_time').textContent = data.time;
    document.getElementById('v_image').src = data.image ? `../../uploads/announcements/${data.image}` : '';
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
