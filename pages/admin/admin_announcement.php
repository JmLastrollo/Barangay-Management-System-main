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
    <title>BMS - Admin Announcement</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css"> 
</head>

<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        
        <div class="header">
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
                        <input type="text" id="searchInput" placeholder="Search for Title..." class="form-control" aria-label="Search announcements" autocomplete="off">
                        <button type="button" aria-label="Search"><i class="bi bi-search"></i></button>
                    </div>
                    
                    <select id="sortSelect" class="form-select" style="width: auto; height: 45px; border-radius: 25px; cursor: pointer;" aria-label="Sort announcements">
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

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 100px;">Image</th>
                            <th>Title</th>
                            <th>Details</th>
                            <th>Location</th>
                            <th>Event Date</th>
                            <th style="width: 140px;" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody id="announcementTable">
                        <tr><td colspan="6" class="text-center py-4 text-muted">Loading announcements...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/announcement_add.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3 text-center">
                             <div class="d-inline-block position-relative">
                                <img id="add-preview" src="../../assets/img" 
                                     style="max-height: 150px; width: 100%; object-fit: cover; border-radius: 8px; border: 1px solid #ddd;">
                             </div>
                             <div class="mt-2">
                                <label for="add-photo" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-upload"></i> Upload Photo
                                </label>
                                <input type="file" name="photo" id="add-photo" class="d-none" accept="image/*">
                             </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add-title">Title</label>
                            <input type="text" id="add-title" name="title" class="form-control" required placeholder="Ex. General Assembly">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add-details">Details</label>
                            <textarea id="add-details" name="details" class="form-control" rows="4" required placeholder="Enter complete announcement details..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="add-location">Location</label>
                            <input type="text" id="add-location" name="location" class="form-control" required placeholder="Ex. Barangay Hall">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" for="add-date">Date</label>
                                <input type="date" id="add-date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" for="add-time">Time</label>
                                <input type="time" id="add-time" name="time" class="form-control" required>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Post</button>
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
                    <h5 class="modal-title fw-bold">Edit Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../../backend/announcement_update.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="edit-id" name="id">

                        <div class="mb-3 text-center">
                             <div class="d-inline-block">
                                <img id="edit-preview" src="" style="max-height: 150px; border-radius: 8px; border: 1px solid #ddd;">
                             </div>
                             <div class="mt-2">
                                 <label for="edit-photo" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-camera"></i> Change Photo
                                 </label>
                                <input type="file" name="photo" id="edit-photo" class="d-none" accept="image/*">
                             </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="edit-title">Title</label>
                            <input type="text" id="edit-title" name="title" class="form-control" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="edit-details">Details</label>
                            <textarea id="edit-details" name="details" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold" for="edit-location">Location</label>
                            <input type="text" id="edit-location" name="location" class="form-control" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" for="edit-date">Date</label>
                                <input type="date" id="edit-date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold" for="edit-time">Time</label>
                                <input type="time" id="edit-time" name="time" class="form-control" required>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="viewModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold text-primary" id="v_title"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-muted mb-3 small text-uppercase fw-bold">
                        <i class="bi bi-calendar-event me-1"></i> <span id="v_date_time"></span>  |  
                        <i class="bi bi-geo-alt-fill me-1"></i> <span id="v_location"></span>
                    </div>

                    <div class="text-center mb-4 bg-light rounded p-2"> 
                        <img id="v_image" src="" class="img-fluid rounded shadow-sm" style="max-height: 400px; width: auto; object-fit: contain;">
                    </div>

                    <div class="p-3 bg-white border rounded mb-4">
                        <h6 class="fw-bold text-dark mb-2">Details:</h6> 
                        <p id="v_details" class="text-secondary" style="white-space: pre-wrap; margin-bottom: 0; line-height: 1.6;"></p>
                    </div>

                    <div class="mb-2 rounded overflow-hidden border shadow-sm" style="height: 250px; background: #eee;">
                        <iframe 
                            id="v_map_frame" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Archive Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to archive this announcement?</p>
                    <small class="text-muted">It will be hidden from the public timeline but can be restored later.</small>
                </div>
                <div class="modal-footer">
                    <form action="../../backend/announcement_update.php" method="POST">
                        <input type="hidden" name="id" id="archive-id">
                        <input type="hidden" name="status" value="archived"> 
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Yes, Archive</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="../../assets/js/admin/admin_announcement.js"></script>

</body>
</html>