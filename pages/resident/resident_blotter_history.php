<?php
session_start();
require_once '../../backend/auth_resident.php'; 
require_once '../../backend/db_connect.php'; 

$user_id = $_SESSION['user_id'];

// Get Resident ID
$stmtRes = $conn->prepare("SELECT resident_id FROM resident_profiles WHERE user_id = :uid");
$stmtRes->execute([':uid' => $user_id]);
$resProfile = $stmtRes->fetch(PDO::FETCH_ASSOC);
$resident_id = $resProfile['resident_id'];

// Get Complaints
try {
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE resident_id = :rid ORDER BY date_filed DESC");
    $stmt->execute([':rid' => $resident_id]);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $complaints = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Reports - BMS</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="../../css/resident.css"> 
    <style>
        /* Chat Styles */
        .chat-box { height: 350px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6; }
        .message { margin-bottom: 10px; padding: 10px; border-radius: 10px; max-width: 80%; }
        .message.admin { background-color: #e9ecef; align-self: flex-start; margin-right: auto; }
        .message.resident { background-color: #d1e7dd; align-self: flex-end; margin-left: auto; text-align: right; }
        .sender-name { font-weight: bold; font-size: 0.8rem; display: block; margin-bottom: 2px; }
        .time { font-size: 0.7rem; color: #6c757d; display: block; margin-top: 3px; }
    </style>
</head>
<body>

    <?php include '../../includes/resident_sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">MY <span class="green">REPORTS</span></h1>
        </div>

        <div class="content pb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold text-dark m-0">Complaint History</h3>
                <a href="resident_file_complaint.php" class="btn btn-danger rounded-pill fw-bold">
                    <i class="bi bi-plus-lg"></i> File Complaint
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date Filed</th>
                                    <th>Respondent</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Conversation</th> </tr>
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
                                            'Processed' => 'bg-info text-dark',
                                            default => 'bg-light text-dark'
                                        };
                                    ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($row['date_filed'])) ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['respondent_name']) ?></td>
                                        <td><?= htmlspecialchars($row['complaint_type']) ?></td>
                                        <td><span class="badge rounded-pill <?= $statusClass ?>"><?= $row['status'] ?></span></td>
                                        
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick="openChatModal(<?= $row['complaint_id'] ?>)">
                                                <i class="bi bi-chat-dots-fill"></i> View / Reply
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
    </div>

    <div class="modal fade" id="chatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-chat-square-text-fill me-2"></i>Complaint Discussion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="chatBox" class="chat-box d-flex flex-column">
                        <div class="text-center text-muted mt-5">Loading conversation...</div>
                    </div>
                    
                    <form id="chatForm" class="mt-3 d-flex gap-2">
                        <input type="hidden" id="chat_complaint_id" name="complaint_id">
                        <input type="text" id="chat_message" name="message" class="form-control" placeholder="Type your reply here..." required autocomplete="off">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send-fill"></i></button>
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
                    chatBox.innerHTML = '<div class="text-center text-muted mt-5">No messages yet. Start the conversation.</div>';
                } else {
                    data.forEach(msg => {
                        const isResident = msg.sender_role === 'Resident';
                        const alignClass = isResident ? 'resident' : 'admin';
                        const senderDisplay = isResident ? 'You' : msg.sender_name + ' (' + msg.sender_role + ')';
                        
                        const html = `
                            <div class="message ${alignClass}">
                                <span class="sender-name">${senderDisplay}</span>
                                <div>${msg.message}</div>
                                <span class="time">${msg.created_at}</span>
                            </div>
                        `;
                        chatBox.innerHTML += html;
                    });
                    // Scroll to bottom
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
                    document.getElementById('chat_message').value = ""; // Clear input
                    loadMessages(); // Reload chat
                } else {
                    alert("Error sending message");
                }
            });
        });

        // Optional: Auto-refresh chat every 5 seconds while modal is open
        setInterval(() => {
            if(document.getElementById('chatModal').classList.contains('show')) {
                loadMessages();
            }
        }, 5000);
    </script>
</body>
</html>