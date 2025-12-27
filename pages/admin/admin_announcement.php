
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BMS - Admin Announcement</title>
<link rel="icon" type="image/png" href="../../assets/img/BMS.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../../css/dashboard.css?v=1">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
        <a href="admin_announcement.php" class="active"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php"><i class="bi bi-people"></i> Officials</a>
        <a href="admin_issuance.php"><i class="bi bi-bookmark"></i> Issuance</a>

        <div class="dropdown-container">
            <button class="dropdown-btn">
                <i class="bi bi-file-earmark-text"></i> Records
                <i class="bi bi-caret-down-fill dropdown-arrow"></i>
            </button>
            <div class="dropdown-content">
                <a href="admin_rec_residents.php">Residents</a>
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
        <h1 class="header-title"><span class="green">ANNOUNCEMENT</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for Title" class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            <div class="mt-2 mt-md-0">
                <!-- Add New Announcement -->
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
                <a href="admin_announcement_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archive
                </a>
            </div>
        </div>

        <!-- Announcement Table -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 320px;">Image</th>
                    <th>Title</th>
                    <th>Details</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th style="width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody id="announcementTable"></tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Add New Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <form action="../../backend/announcement_add.php" method="POST" enctype="multipart/form-data">
            <!-- Photo -->
            <div class="mb-3">
                <label class="fw-bold">Photo</label>
                <input type="file" name="photo" id="add-photo" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 preview-wrapper">
                <img id="add-preview" class="preview-img" src="" style="display:none; max-width: 300px;">
            </div>
            <hr>
            <div class="mb-3">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Details</label>
                <textarea name="details" class="form-control" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <label>Location</label>
                <input type="text" name="location" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Date</label>
                    <input type="date" id="add-date" name="date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Time</label>
                    <input type="time" id="add-time" name="time" class="form-control" style="width: 100%;" required>
                </div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Post</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
    </div>
</div>
</div>

<!-- View Modal -->
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

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Edit Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <form action="../../backend/announcement_update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit-id" name="id">

            <!-- Photo -->
            <div class="mb-3">
                <label class="fw-bold">Photo</label>
                <input type="file" name="photo" id="edit-photo" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 preview-wrapper">
                <img id="edit-preview" class="preview-img" src="" style="display:none; max-width: 300px;">
            </div>
            <hr>

            <div class="mb-3">
                <label>Title</label>
                <input type="text" id="edit-title" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Details</label>
                <textarea id="edit-details" name="details" class="form-control" rows="3" required></textarea>
            </div>

             <div class="mb-3">
                <label>Location</label>
                <input type="text" id="edit-location" name="location" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Date</label>
                    <input type="date" id="edit-date" name="date" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Time</label>
                    <input type="time" id="edit-time" name="time" class="form-control" style="width: 100%;" required>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
    </div>
</div>
</div>

<!--- Archive Modal !--->
<div class="modal fade" id="archiveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Archive Announcement</h4>
        <p>Are you sure you want to archive this announcement?</p>

        <form action="../../backend/announcement_update.php" method="POST">
            <input type="hidden" name="id" id="a_id">
            <input type="hidden" name="status" value="archived">
            <button class="btn btn-warning" type="submit">Archive</button>
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

function truncateText(text, maxLength = 40) {
    if (!text) return "";
    return text.length > maxLength ? text.substring(0, maxLength) + "..." : text;
}

function openViewModal(data) {
    document.getElementById('v_title').textContent = data.title;
    document.getElementById('v_details').textContent = data.details;
    document.getElementById('v_location').textContent = data.location;
    document.getElementById('v_date').textContent = data.date;
    document.getElementById('v_time').textContent = data.time;
    document.getElementById('v_image').src = data.image ? `../../uploads/announcements/${data.image}` : '';
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

function openEditModal(button) {
    document.getElementById('edit-id').value = button.dataset.id;
    document.getElementById('edit-title').value = button.dataset.title;
    document.getElementById('edit-details').value = button.dataset.details;
    document.getElementById('edit-location').value = button.dataset.location;
    document.getElementById('edit-date').value = button.dataset.date;
    document.getElementById('edit-time').value = button.dataset.time;
    

    const editPreview = document.getElementById('edit-preview');
    if(button.dataset.image){
        editPreview.src = `../../uploads/announcements/${button.dataset.image}`;
        editPreview.style.display = "block";
    } else {
        editPreview.style.display = "none";
    }
}

function openArchiveModal(id) {
    document.getElementById('a_id').value = id.$oid ?? id;
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}

// Add Modal Image Preview
document.getElementById("add-photo").addEventListener("change", function(event){
    const file = event.target.files[0];
    const preview = document.getElementById("add-preview");
    if(file){
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    } else {
        preview.style.display = "none";
    }
});

// Edit Modal Image Preview
document.getElementById("edit-photo").addEventListener("change", function(event){
    const file = event.target.files[0];
    const preview = document.getElementById("edit-preview");
    if(file){
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    }
});

fetch("../../backend/announcement_get.php")
.then(res => res.json())
.then(data => {
    let table = "";
    data.forEach(item => {
        table += `
        <tr>
            <td><img src="../../uploads/announcements/${item.image}"style="width:300px;height:120px;object-fit:cover;border-radius:5px"></td>
            <td>${item.title}</td>
            <td>${truncateText(item.details, 25)}</td>
            <td>${item.location}</td>
            <td>${item.date}</td>
            <td>${item.time}</td>
            <td>
                <div class="d-flex gap-1">
                    <button class="btn btn-info btn-sm text-white"
                        onclick='openViewModal(${JSON.stringify(item)})'>
                        <i class="bi bi-eye"></i>
                    </button>

                    <button class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal"
                        data-id="${item._id}"
                        data-title="${item.title.replace(/"/g,'&quot;')}"
                        data-details="${item.details.replace(/"/g,'&quot;')}"
                        data-location="${item.location.replace(/"/g,'&quot;')}"
                        data-date="${item.date}"
                        data-time="${item.time}"
                        data-image="${item.image}">
                        <i class="bi bi-pencil-square"></i>
                    </button>

                    <button class="btn btn-sm btn-secondary archive-btn"
                        onclick='openArchiveModal("${item._id}")'>
                        <i class="bi bi-archive"></i>
                    </button>
                </div>
                </td>

        </tr>`;
    });
    document.getElementById("announcementTable").innerHTML = table;

    // Attach Edit modal population dynamically
    document.querySelectorAll('button[data-bs-target="#editModal"]').forEach(btn => {
        btn.addEventListener('click', function(){
            openEditModal(this);
        });
    });

    // Disable typing for date/time inputs
    ['add-date','add-time','edit-date','edit-time'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('keydown', e => e.preventDefault());
    });

    // Dropdown toggle logic
    document.querySelectorAll('.dropdown-container').forEach(container => {
        if(container.querySelector('.dropdown-content a.active')){
            container.classList.add('active');
        }
    });

    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            this.parentElement.classList.toggle('active');
        });
    });
});
</script>
</body>
</html>
