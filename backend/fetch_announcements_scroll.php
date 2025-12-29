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
            $imgSrc = !empty($item->image) ? "uploads/announcements/" . $item->image : "assets/img/announcement_placeholder.png";
            $dateDisplay = date("d M Y", strtotime($item->date));
            $timeDisplay = !empty($item->time) ? " | " . date("h:i A", strtotime($item->time)) : "";
            
            $locDisplay = "";
            if (!empty($item->location)) {
                $locDisplay = '<div class="mb-2 text-secondary small"><i class="bi bi-geo-alt-fill text-danger"></i> ' . htmlspecialchars($item->location) . '</div>';
            }

            $details = strlen($item->details) > 80 ? substr($item->details, 0, 80) . "..." : htmlspecialchars($item->details);

            echo '
            <div class="col-md-4 d-flex fade-in-item">
                <div class="card announcement-page-card p-3 h-100 w-100 d-flex flex-column">
                    <img src="' . htmlspecialchars($imgSrc) . '" class="mb-3 w-100 announcement-page-img" alt="Announcement Image" />
                    
                    <div class="d-flex flex-column flex-grow-1 text-start">
                        <span class="badge bg-success mb-2 align-self-start">' . $dateDisplay . $timeDisplay . '</span>
                        ' . $locDisplay . '
                        
                        <h5 class="fw-bold mt-2 text-primary">' . htmlspecialchars($item->title) . '</h5>
                        <p class="text-muted flex-grow-1">' . $details . '</p>
                        
                        <a href="see-more-announcement.php?id=' . $item->announcement_id . '" class="btn btn-outline-primary rounded-pill w-100 mt-auto">Read More</a>
                    </div>
                </div>
            </div>';
        }
    }
} catch (PDOException $e) {
    // Fail silently
}
?>