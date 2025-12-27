<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>BMS - Admin Officials</title>
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
        <a href="admin_announcement.php"><i class="bi bi-megaphone"></i> Announcement</a>
        <a href="admin_officials.php" class="active"><i class="bi bi-people"></i> Officials</a>
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
        <h1 class="header-title"><span class="green">OFFICIALS</span></h1>
        <div class="header-logos">
            <img src="../../assets/img/barangaygusalogo.png">
            <img src="../../assets/img/cdologo.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search for Name" class="form-control">
                <button><i class="bi bi-search"></i></button>
            </div>
            <div class="mt-2 mt-md-0">
              
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
                <a href="admin_officials_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archive
                </a>
            </div>
        </div>

        <table class="table officials-table">
            <thead>
                <tr>
                    <th style="width: 320px;">Image</th>
                    <th>Name</th>
                    <th>Position</th>
                    <th style="width: 150px;">Action</th>
                </tr>
            </thead>
            <tbody id="officialsTable"></tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Add New Official</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <form action="../../backend/officials_add.php" method="POST" enctype="multipart/form-data">
          
            <div class="mb-3">
                <label class="fw-bold">Photo</label>
                <input type="file" name="photo" id="add-photo" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 preview-wrapper">
                <img id="add-preview" class="preview-img" src="" style="display:none; max-width: 300px;">
            </div>
            <hr>
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Position</label>
                <input type="text" name="position" class="form-control" required>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary">Add</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
    </div>
</div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
        <h5 class="modal-title">Edit Official</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body">
        <form action="../../backend/officials_update.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit-id" name="id">

            <div class="mb-3">
                <label class="fw-bold">Photo</label>
                <input type="file" name="photo" id="edit-photo" class="form-control" accept="image/*">
            </div>
            <div class="mb-3 preview-wrapper">
                <img id="edit-preview" class="preview-img" src="" style="display:none; max-width: 300px;">
            </div>
            <hr>

            <div class="mb-3">
                <label>Name</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Position</label>
                <input type="text" id="edit-position" name="position" class="form-control" required>
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

<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content p-3">
        <h4>Official Details</h4>
        <p><b>Name:</b> <span id="v_name"></span></p>
        <p><b>Position:</b> <span id="v_position"></span></p>
        <p><b>Image:</b> <br>
            <img id="v_image" src="" style="width:100%;height:auto;">
        </p>
        <button class="btn btn-secondary mt-2" data-bs-dismiss="modal">Close</button>
    </div>
  </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content p-3">
        <h4>Archive Official</h4>
        <p>Are you sure you want to archive this official?</p>

        <form action="../../backend/officials_update.php" method="POST">
            <input type="hidden" name="id" id="o_id">
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

function openEditModal(button) {
    document.getElementById('edit-id').value = button.dataset.id;
    document.getElementById('edit-name').value = button.dataset.name;
    document.getElementById('edit-position').value = button.dataset.position;

    const editPreview = document.getElementById('edit-preview');
    if(button.dataset.image){
        editPreview.src = `../../uploads/officials/${button.dataset.image}`;
        editPreview.style.display = "block";
    } else {
        editPreview.style.display = "none";
    }
}

function openArchiveModal(id) {
    document.getElementById('o_id').value = id.$oid ?? id;
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}

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

document.getElementById("edit-photo").addEventListener("change", function(event){
    const file = event.target.files[0];
    const preview = document.getElementById("edit-preview");
    if(file){
        preview.src = URL.createObjectURL(file);
        preview.style.display = "block";
    }
});
</script>
<script>

let officialsData = [];

fetch("../../backend/officials_get.php")
.then(res => res.json())
.then(data => {
    officialsData = data;
    renderTable(data);
});

function renderTable(data) {
    let table = "";
    data.forEach(item => {
        table += `
        <tr>
            <td><img src="../../uploads/officials/${item.image}"style="width:300px;height:200px;object-fit:cover;border-radius:5px;"></td>
            <td>${item.name}</td>
            <td>${item.position}</td>
            <td>
                <button class="btn btn-info btn-sm text-white"
                    onclick='openViewModal(${JSON.stringify(item)})'>
                    <i class="bi bi-eye"></i>
                </button>

                <button class="btn btn-primary btn-sm me-1"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal"
                    data-id="${item._id}"
                    data-name="${item.name.replace(/"/g,'&quot;')}"
                    data-position="${item.position.replace(/"/g,'&quot;')}"
                    data-image="${item.image}">
                    <i class="bi bi-pencil-square"></i>
                </button>

                <button class="btn btn-sm btn-secondary archive-btn"
                    onclick='openArchiveModal("${item._id}")'>
                    <i class="bi bi-archive"></i>
                </button>
            </td>
        </tr>`;
    });
    document.getElementById("officialsTable").innerHTML = table;

    document.querySelectorAll('button[data-bs-target="#editModal"]').forEach(btn => {
        btn.addEventListener('click', function(){
            openEditModal(this);
        });
    });
}

document.getElementById("searchInput").addEventListener("keyup", function() {
    const query = this.value.toLowerCase();
    const filtered = officialsData.filter(item => item.name.toLowerCase().includes(query));
    renderTable(filtered);
});
</script>
</body>
</html>
