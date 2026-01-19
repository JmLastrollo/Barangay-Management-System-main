<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

if (isset($_GET['read_id'])) {
    $id = $_GET['read_id'];
    try {
        $stmt = $conn->prepare("UPDATE messages SET status = 'Read' WHERE message_id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error']);
    }
    exit();
}

$searchQuery = $_GET["search"] ?? "";
$params = [];

$sql = "SELECT * FROM messages WHERE 1=1";

if (!empty($searchQuery)) {
    $sql .= " AND (sender_name LIKE :search OR email LIKE :search OR subject LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY date_sent DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>BMS - Admin Messages</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css" />
    
    <style>
        .message-content-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            font-size: 0.95rem;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap;
        }
        .sender-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .table-row-unread {
            font-weight: 600;
            background-color: #fff !important;
        }
        .table-row-read {
            font-weight: 400;
            background-color: #fcfcfc !important;
            color: #555;
        }
    </style>
</head>

<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">INQUIRIES & <span class="green">MESSAGES</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <form method="GET" class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search sender, subject..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="search-btn"><i class="bi bi-search"></i></button>
                </form>
                
                <div class="add-archive-buttons">
                    <button class="btn btn-outline-secondary disabled" title="Feature coming soon">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
            </div>    

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Sender Name</th>
                            <th>Email Address</th>
                            <th>Subject</th>
                            <th>Date Received</th>
                            <th>Status</th>
                            <th style="min-width: 150px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No messages found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): 
                                $data = json_encode($msg);
                                $badgeClass = ($msg['status'] == 'Unread') ? 'bg-danger' : 'bg-secondary';
                                $rowClass = ($msg['status'] == 'Unread') ? 'table-row-unread' : 'table-row-read';
                            ?>
                            <tr id="row-<?= $msg['message_id'] ?>" class="<?= $rowClass ?>">
                                
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border me-2 text-primary fw-bold" 
                                             style="width: 35px; height: 35px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($msg['sender_name'], 0, 1)) ?>
                                        </div>
                                        <span><?= htmlspecialchars($msg['sender_name']) ?></span>
                                    </div>
                                </td>
                                
                                <td><a href="mailto:<?= htmlspecialchars($msg['email']) ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($msg['email']) ?></a></td>
                                
                                <td class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($msg['subject']) ?>">
                                    <?= htmlspecialchars($msg['subject']) ?>
                                </td>
                                
                                <td>
                                    <small><?= date("M d, Y", strtotime($msg['date_sent'])) ?></small> <br>
                                    <small class="text-muted" style="font-size: 0.75rem;"><?= date("h:i A", strtotime($msg['date_sent'])) ?></small>
                                </td>
                                
                                <td id="badge-cell-<?= $msg['message_id'] ?>">
                                    <span class="badge rounded-pill <?= $badgeClass ?>"><?= $msg['status'] ?></span>
                                </td>
                                
                                <td>
                                    <button class="btn btn-sm btn-info text-white mb-1 me-1" 
                                            onclick='openMessageModal(<?= $data ?>)' 
                                            title="Read Message">
                                        <i class="bi bi-envelope-open"></i> Read
                                    </button>

                                    <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['subject']) ?>" 
                                       class="btn btn-sm btn-primary mb-1" 
                                       title="Reply via Email">
                                        <i class="bi bi-reply-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="messageModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-envelope-paper me-2"></i>Message Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                
                <div class="sender-meta d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-bold text-dark mb-1" id="m_subject"></h5>
                        <p class="mb-0">From: <strong id="m_sender"></strong> &lt;<span id="m_email"></span>&gt;</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark border" id="m_date"></span>
                    </div>
                </div>

                <label class="small text-muted fw-bold mb-2">MESSAGE CONTENT:</label>
                <div class="message-content-box" id="m_body">
                    </div>

            </div>
            <div class="modal-footer bg-light">
                <a href="#" id="m_reply_btn" class="btn btn-primary px-4">
                    <i class="bi bi-reply-fill me-1"></i> Reply via Email
                </a>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
      </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function openMessageModal(data) {
            document.getElementById('m_subject').innerText = data.subject;
            document.getElementById('m_sender').innerText = data.sender_name;
            document.getElementById('m_email').innerText = data.email;
            document.getElementById('m_date').innerText = new Date(data.date_sent).toLocaleString();
            document.getElementById('m_body').innerText = data.message;
            const replyLink = `mailto:${data.email}?subject=Re: ${encodeURIComponent(data.subject)}`;
            document.getElementById('m_reply_btn').href = replyLink;
            new bootstrap.Modal(document.getElementById('messageModal')).show();
            if (data.status === 'Unread') {
                markAsRead(data.message_id);
            }
        }

        function markAsRead(id) {
            fetch(`?read_id=${id}`)
                .then(response => response.json())
                .then(result => {
                    if(result.status === 'success') {
                        const row = document.getElementById(`row-${id}`);
                        const badgeCell = document.getElementById(`badge-cell-${id}`);
                        badgeCell.innerHTML = '<span class="badge rounded-pill bg-secondary">Read</span>';
                        row.classList.remove('table-row-unread');
                        row.classList.add('table-row-read');
                    }
                })
                .catch(error => console.error('Error marking as read:', error));
        }
    </script>
</body>
</html>