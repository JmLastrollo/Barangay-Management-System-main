<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];
$stmtRes = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = :uid");
$stmtRes->execute([':uid' => $user_id]);
$resident_id = $stmtRes->fetchColumn();

try {
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE resident_id = :rid ORDER BY date_filed DESC");
    $stmt->execute([':rid' => $resident_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $complaints = []; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Reports - BMS</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <style>
        .chat-box { height: 60vh; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 8px; }
        .message { margin-bottom: 15px; padding: 12px; border-radius: 15px; max-width: 85%; font-size: 0.9rem; position: relative; }
        .message.admin { background-color: #e9ecef; align-self: flex-start; margin-right: auto; border-bottom-left-radius: 2px; }
        .message.resident { background-color: #0d6efd; color: white; align-self: flex-end; margin-left: auto; border-bottom-right-radius: 2px; }
        .sender-name { font-weight: bold; font-size: 0.7rem; margin-bottom: 4px; display: block; opacity: 0.8; }
        .time { font-size: 0.65rem; display: block; margin-top: 5px; opacity: 0.7; text-align: right; }
    </style>
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">MY <span class="green">REPORTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png" alt="Logo 1">
                <img src="../../assets/img/dasma logo-modified.png" alt="Logo 2">
            </div>
        </div>

        <div class="content pb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
                <div class="text-center text-md-start">
                    <h3 class="fw-bold text-dark m-0">Complaint History</h3>
                    <p class="text-muted small m-0">View reports and admin replies</p>
                </div>
                <a href="resident_file_complaint.php" class="btn btn-danger rounded-pill fw-bold shadow-sm w-100 w-md-auto">
                    <i class="bi bi-exclamation-circle me-2"></i> File Complaint
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-uppercase text-secondary small">
                                <tr>
                                    <th class="ps-4">Date Filed</th>
                                    <th>Respondent</th>
                                    <th class="d-none d-md-table-cell">Type</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th> 
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($complaints)): ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">No complaints found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($complaints as $row): 
                                        $statusClass = match($row['status']) {
                                            'Pending' => 'bg-warning text-dark',
                                            'Active' => 'bg-primary text-white',
                                            'Resolved' => 'bg-success text-white',
                                            default => 'bg-secondary text-white'
                                        };
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark"><?= date('M d, Y', strtotime($row['date_filed'])) ?></div>
                                            <small class="text-muted d-block d-md-none"><?= htmlspecialchars($row['complaint_type']) ?></small>
                                        </td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['respondent_name']) ?></td>
                                        <td class="d-none d-md-table-cell"><?= htmlspecialchars($row['complaint_type']) ?></td>
                                        <td><span class="badge rounded-pill <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                                        
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-outline-primary rounded-pill" onclick="openChatModal(<?= $row['complaint_id'] ?>)">
                                                <i class="bi bi-chat-dots-fill me-1"></i> <span class="d-none d-sm-inline">Chat</span>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php include '../../includes/resident_footer.php'; ?>
    </div>

    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white shadow-sm">
                    <h5 class="modal-title fs-6"><i class="bi bi-chat-square-text-fill me-2"></i>Complaint Discussion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0 bg-light">
                    <div id="chatBox" class="chat-box d-flex flex-column h-100 rounded-0 border-0">
                        <div class="text-center text-muted mt-5">Loading conversation...</div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top p-2">
                    <form id="chatForm" class="d-flex gap-2 w-100">
                        <input type="hidden" id="chat_complaint_id" name="complaint_id">
                        <input type="text" id="chat_message" name="message" class="form-control rounded-pill bg-light" placeholder="Type a message..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary rounded-circle" style="width: 40px; height: 40px;"><i class="bi bi-send-fill"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentComplaintId = null;

        function openChatModal(id) {
            currentComplaintId = id;
            document.getElementById('chat_complaint_id').value = id;
            const modal = new bootstrap.Modal(document.getElementById('chatModal'));
            modal.show();
            loadMessages();
        }

        function loadMessages() {
            if(!currentComplaintId) return;
            const chatBox = document.getElementById('chatBox');
            
            fetch(`../../backend/complaint_chat.php?complaint_id=${currentComplaintId}`)
            .then(res => res.json())
            .then(data => {
                chatBox.innerHTML = "";
                if(data.length === 0) {
                    chatBox.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted small">No messages yet.</div>';
                } else {
                    data.forEach(msg => {
                        const isResident = msg.sender_role === 'Resident';
                        const alignClass = isResident ? 'resident' : 'admin';
                        const senderDisplay = isResident ? 'You' : msg.sender_name + ' (Admin)';
                        
                        const html = `
                            <div class="message ${alignClass} shadow-sm">
                                <span class="sender-name">${senderDisplay}</span>
                                <div>${msg.message}</div>
                                <span class="time">${msg.created_at}</span>
                            </div>
                        `;
                        chatBox.innerHTML += html;
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            });
        }

        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../../backend/complaint_chat.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    document.getElementById('chat_message').value = "";
                    loadMessages();
                }
            });
        });

        setInterval(() => {
            if(document.getElementById('chatModal').classList.contains('show')) {
                loadMessages();
            }
        }, 5000);
    </script>
</body>
</html>