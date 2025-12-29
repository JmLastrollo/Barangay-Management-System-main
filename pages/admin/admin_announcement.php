<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Admin', 'Staff'])) {
    header("Location: ../../admin_login.php");
    exit();
}

require_once '../../backend/db_connect.php';
?>

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
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>

<body>

<div class="sidebar">
    <div class="sidebar-header">
        <img src="../../assets/img/profile.jpg" alt="Profile">
        <div>
            <h3><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin' ?></h3>
            <small><?= isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'admin@email.com' ?></small>
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
                <span><i class="bi bi-file-earmark-text"></i> Records</span>
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
            <img src="../../assets/img/Langkaan 2 Logo-modified.png">
            <img src="../../assets/img/dasma logo-modified.png">
        </div>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            
            <div class="d-flex align-items-center gap-2">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search for Title" class="form-control">
                    <button><i class="bi bi-search"></i></button>
                </div>
                
                <select id="sortSelect" class="form-select" style="width: auto; height: 45px; border-radius: 25px; cursor: pointer;">
                    <option value="newest">Date: Newest First</option>
                    <option value="oldest">Date: Oldest First</option>
                    <option value="title_asc">Title: A-Z</option>
                    <option value="title_desc">Title: Z-A</option>
                </select>
            </div>

            <div class="mt-2 mt-md-0">
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addModal">
                    <i class="bi bi-plus-circle"></i> Add New
                </button>
                <a href="admin_announcement_archive.php" class="btn btn-secondary">
                    <i class="bi bi-archive"></i> Archives
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 120px;">Image</th>
                        <th>Title</th>
                        <th>Details</th>
                        <th>Location</th>
                        <th>Date & Time</th>
                        <th style="width: 150px;">Action</th>
                    </tr>
                </thead>
                <tbody id="announcementTable">
                    <tr><td colspan="6" class="text-center">Loading data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../../backend/announcement_add.php" method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label class="fw-bold">Upload Photo</label>
                        <input type="file" name="photo" id="add-photo" class="form-control" accept="image/*">
                        <div class="mt-2 text-center">
                            <img id="add-preview" src="" style="display:none; max-height: 200px; border-radius: 5px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="Ex. General Assembly">
                    </div>
                    
                    <div class="mb-3">
                        <label>Details</label>
                        <textarea name="details" class="form-control" rows="3" required placeholder="Enter announcement details..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Location</label>
                        <input type="text" name="location" class="form-control" required placeholder="Ex. Barangay Hall">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Time</label>
                            <input type="time" name="time" class="form-control" required>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Post Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="../../backend/announcement_update.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="edit-id" name="id">

                    <div class="mb-3">
                        <label class="fw-bold">Update Photo</label>
                        <input type="file" name="photo" id="edit-photo" class="form-control" accept="image/*">
                        <div class="mt-2 text-center">
                            <img id="edit-preview" src="" style="display:none; max-height: 200px; border-radius: 5px;">
                        </div>
                    </div>

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
                            <input type="time" id="edit-time" name="time" class="form-control" required>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3">
            <div class="modal-header border-0 pb-0">
                <h4 class="modal-title fw-bold" id="v_title"></h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-muted mb-3">
                    <i class="bi bi-calendar-event me-1"></i> <span id="v_date_time"></span> | 
                    <i class="bi bi-geo-alt-fill me-1"></i> <span id="v_location"></span>
                </div>
                
                <div class="d-flex justify-content-center mb-3"> 
                    <img id="v_image" src="" class="img-fluid rounded shadow-sm" style="max-height: 350px; width: auto; object-fit: contain;">
                </div>
                
                <div class="p-3 bg-light rounded border">
                    <p id="v_details" style="white-space: pre-wrap; margin-bottom: 0;"></p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">Archive Announcement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to archive this announcement? It will be hidden from the public timeline.</p>
            </div>
            <div class="modal-footer">
                <form action="../../backend/announcement_update.php" method="POST">
                    <input type="hidden" name="id" id="archive-id">
                    <input type="hidden" name="status" value="archived"> <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Yes, Archive</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- SIDEBAR ---
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }
    
    document.querySelectorAll('.dropdown-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            this.parentElement.classList.toggle('active');
        });
    });

    // --- TOAST FUNCTION (Custom Alert) ---
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        toast.textContent = message;
        toast.className = 'toast'; 
        toast.classList.add(type); 
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // --- CHECK URL PARAMS FOR ALERTS ---
    document.addEventListener('DOMContentLoaded', function() {
        // Load Data Table
        loadAnnouncements();

        // Check for success messages from Backend redirection
        const urlParams = new URLSearchParams(window.location.search);
        const success = urlParams.get('success');
        const archived = urlParams.get('archived');

        if (success === 'added') {
            showToast('Announcement posted successfully!', 'success');
        } else if (success === 'updated') {
            showToast('Changes saved successfully!', 'success');
        } else if (archived === 'true') {
            showToast('Announcement moved to archive.', 'warn');
        }
        if (success || archived) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    // ==========================================
    // --- UPDATED: SORTING & SEARCH LOGIC ---
    // ==========================================
    
    let announcementsData = []; // Store fetched data here

    function loadAnnouncements() {
        fetch('../../backend/announcement_get.php')
            .then(res => res.json())
            .then(data => {
                announcementsData = data || [];
                updateTableDisplay(); // Render initial table
            })
            .catch(err => {
                console.error("Error loading data:", err);
                document.getElementById('announcementTable').innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data.</td></tr>';
            });
    }

    // Combine Search and Sort
    function updateTableDisplay() {
        const tbody = document.getElementById('announcementTable');
        const searchValue = document.getElementById('searchInput').value.toLowerCase();
        const sortValue = document.getElementById('sortSelect').value;
        
        // 1. FILTER
        let filteredData = announcementsData.filter(item => {
            return item.title.toLowerCase().includes(searchValue) || 
                   item.details.toLowerCase().includes(searchValue) ||
                   item.location.toLowerCase().includes(searchValue);
        });

        // 2. SORT
        filteredData.sort((a, b) => {
            if (sortValue === 'newest') {
                return new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time);
            } else if (sortValue === 'oldest') {
                return new Date(a.date + ' ' + a.time) - new Date(b.date + ' ' + b.time);
            } else if (sortValue === 'title_asc') {
                return a.title.localeCompare(b.title);
            } else if (sortValue === 'title_desc') {
                return b.title.localeCompare(a.title);
            }
            return 0;
        });

        // 3. RENDER
        tbody.innerHTML = '';

        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No announcements found.</td></tr>';
            return;
        }

        filteredData.forEach(item => {
            const imgSrc = item.image ? `../../uploads/announcements/${item.image}` : '../../assets/img/officials';
            const shortDetails = item.details.length > 50 ? item.details.substring(0, 50) + '...' : item.details;
            const dateObj = new Date(item.date);
            const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

            const row = `
                <tr>
                    <td>
                        <img src="${imgSrc}" onerror="this.onerror=null; this.src='../../assets/img/announcement_placeholder.png'" style="width:80px; height:60px; object-fit:cover; border-radius:5px; border: 1px solid #ddd;">
                    </td>
                    <td class="fw-bold align-middle">${item.title}</td>
                    <td class="align-middle"><small>${shortDetails}</small></td>
                    <td class="align-middle">${item.location}</td>
                    
                    <td class="align-middle text-nowrap">
                        <div class="fw-bold text-dark">${formattedDate}</div>
                        <small class="text-muted"><i class="bi bi-clock"></i> ${convertTime(item.time)}</small>
                    </td>

                    <td class="align-middle">
                        <button class="btn btn-sm btn-info text-white" onclick='openViewModal(${JSON.stringify(item)})' title="View"><i class="bi bi-eye"></i></button>
                        <button class="btn btn-sm btn-primary" onclick='openEditModal(${JSON.stringify(item)})' title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-warning" onclick='openArchiveModal("${item.announcement_id}")' title="Archive"><i class="bi bi-archive"></i></button>
                    </td>
                </tr>
            `;
            tbody.innerHTML += row;
        });
    }

    // EVENT LISTENERS
    document.getElementById('searchInput').addEventListener('keyup', updateTableDisplay);
    document.getElementById('sortSelect').addEventListener('change', updateTableDisplay);


    // --- HELPERS ---
    function convertTime(timeString) {
        if(!timeString) return "";
        const [hour, minute] = timeString.split(':');
        const h = parseInt(hour);
        const ampm = h >= 12 ? 'PM' : 'AM';
        const formattedHour = h % 12 || 12; 
        return `${formattedHour}:${minute} ${ampm}`;
    }

    function setupImagePreview(inputId, imgId) {
        document.getElementById(inputId).addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    const img = document.getElementById(imgId);
                    img.src = evt.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
    setupImagePreview('add-photo', 'add-preview');
    setupImagePreview('edit-photo', 'edit-preview');

    // --- MODAL FUNCTIONS ---
    function openViewModal(data) {
        document.getElementById('v_title').innerText = data.title;
        document.getElementById('v_details').innerText = data.details;
        document.getElementById('v_location').innerText = data.location;
        document.getElementById('v_date_time').innerText = `${data.date} @ ${convertTime(data.time)}`;
        
        const img = document.getElementById('v_image');
        if(data.image) {
            img.src = `../../uploads/announcements/${data.image}`;
            img.style.display = 'block';
        } else {
            img.style.display = 'none';
        }
        new bootstrap.Modal(document.getElementById('viewModal')).show();
    }

    function openEditModal(data) {
        document.getElementById('edit-id').value = data.announcement_id;
        document.getElementById('edit-title').value = data.title;
        document.getElementById('edit-details').value = data.details;
        document.getElementById('edit-location').value = data.location;
        document.getElementById('edit-date').value = data.date;
        document.getElementById('edit-time').value = data.time;

        const preview = document.getElementById('edit-preview');
        if(data.image) {
            preview.src = `../../uploads/announcements/${data.image}`;
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }

    function openArchiveModal(id) {
        document.getElementById('archive-id').value = id;
        new bootstrap.Modal(document.getElementById('archiveModal')).show();
    }

</script>

</body>
</html>