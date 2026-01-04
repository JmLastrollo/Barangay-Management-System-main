<?php
require_once "db_connect.php";

// 1. Get Parameters
$limit = 6;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $limit;

// Get Filter Inputs
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$sort = isset($_POST['sort']) ? $_POST['sort'] : 'newest';

// 2. Build Query
try {
    // Base SQL
    $sql = "SELECT * FROM announcements WHERE status = 'active'";
    $params = [];

    // Add Search Condition
    if (!empty($search)) {
        $sql .= " AND title LIKE :search";
        $params[':search'] = "%$search%";
    }

    // Add Sorting Condition
    if ($sort === 'oldest') {
        $sql .= " ORDER BY date ASC, time ASC";
    } else {
        // Default: Newest first
        $sql .= " ORDER BY date DESC, time DESC";
    }

    // Add Pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    
    // Prepare & Bind
    $stmt = $conn->prepare($sql);
    
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_OBJ);

    // 3. Output HTML
    if ($announcements) {
        foreach ($announcements as $item) {
            // Image Logic
            $imgSrc = !empty($item->image) ? "uploads/announcements/" . $item->image : "assets/img/announcement_placeholder.png";
            
            // Date Logic
            $dateDisplay = date("M d, Y", strtotime($item->date));
            $timeDisplay = !empty($item->time) ? date("h:i A", strtotime($item->time)) : "";
            
            // Location Logic
            $locDisplay = "";
            if (!empty($item->location)) {
                $locDisplay = '<span class="ms-3"><i class="bi bi-geo-alt-fill me-1 text-danger"></i> ' . htmlspecialchars($item->location) . '</span>';
            }

            // Description Logic (Truncate)
            $details = strlen($item->details) > 100 ? substr($item->details, 0, 100) . "..." : htmlspecialchars($item->details);

            echo '
            <div class="col-md-6 col-lg-4 fade-in-item">
                <div class="card announcement-page-card">
                    
                    <img src="' . htmlspecialchars($imgSrc) . '" class="card-img-top announcement-page-img" alt="Announcement">
                    
                    <div class="card-body">
                        <h5 class="card-title">' . htmlspecialchars($item->title) . '</h5>
                        
                        <p class="card-text">
                            ' . $details . '
                        </p>
                        
                        <div class="announcement-meta">
                            <span><i class="bi bi-calendar-event me-1"></i> ' . $dateDisplay . '</span>
                            ' . $locDisplay . '
                        </div>

                        <a href="see-more-announcement.php?id=' . $item->announcement_id . '" class="btn-read-more w-100">
                            Read Details <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                </div>
            </div>';
        }
    }
} catch (PDOException $e) {
    // Fail silently or log error
}
?>