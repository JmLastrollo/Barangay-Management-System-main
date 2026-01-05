<?php
// pages/staff/staff_resident_list.php
include '../../backend/auth_staff.php'; // Staff Auth
require_once '../../backend/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Staff - Resident Masterlist</title>
    <link rel="icon" type="image/png" href="../../assets/img/Langkaan 2 Logo-modified.png">
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css">
</head>
<body>

    <?php include '../../includes/staff_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">RESIDENT <span class="green">ACCOUNTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search Name..." class="form-control" aria-label="Search Resident">
                        <button type="button" aria-label="Search"><i class="bi bi-search"></i></button>
                    </div>
                    <select id="phaseFilter" class="form-select" style="width: auto; border-radius: 20px; cursor: pointer;">
                        <option value="">All Phases</option>
                        <option value="Phase 1">Phase 1</option>
                        <option value="Phase 2">Phase 2</option>
                        <option value="Phase 3">Phase 3</option>
                        <option value="Phase 4">Phase 4</option>
                        <option value="Phase 5">R 5</option>
                    </select>
                    <select id="statusFilter" class="form-select" style="width: auto; border-radius: 20px; cursor: pointer;">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>
                
                <div class="mt-2 mt-md-0 d-flex gap-2">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Add New
                    </button>
                    <button class="btn btn-outline-primary" onclick="loadResidents()" title="Refresh List">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Resident Name</th>
                            <th>Phase / Address</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-center" style="width: 200px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="residentTableBody">
                        <tr><td colspan="5" class="text-center py-4 text-muted">Loading residents...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'staff_resident_modals.php'; // Or paste modal code directly ?>

    <div id="toast" class="toast"></div>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    
    <script src="../../assets/js/admin/admin_resident.js"></script> 

</body>
</html>