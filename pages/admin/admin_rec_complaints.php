<?php 
session_start();
require_once '../../backend/auth_admin.php';
require_once '../../backend/db_connect.php';

$searchQuery = $_GET["search"] ?? "";
$params = [];

// Base Query
$sql = "SELECT c.*, r.email, r.contact_no 
        FROM complaints c
        LEFT JOIN resident_profiles r ON c.resident_id = r.resident_id
        WHERE c.status != 'Archived'";

if (!empty($searchQuery)) {
    $sql .= " AND (c.respondent_name LIKE :search OR c.complaint_type LIKE :search OR c.complainant_name LIKE :search)";
    $params[':search'] = "%$searchQuery%";
}

$sql .= " ORDER BY c.date_filed DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>BMS - Admin Complaints</title>
    <link href="../../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../css/admin.css" />
    <link rel="stylesheet" href="../../css/sidebar.css" />
    <link rel="stylesheet" href="../../css/toast.css" />
    
    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }
        .message-bubble {
            max-width: 75%;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
            position: relative;
        }
        .message-bubble.admin {
            align-self: flex-end;
            background-color: #0d6efd; /* Bootstrap Primary */
            color: white;
            border-bottom-right-radius: 2px;
        }
        .message-bubble.resident {
            align-self: flex-start;
            background-color: #e9ecef; /* Light Gray */
            color: #212529;
            border-bottom-left-radius: 2px;
        }
        .sender-info {
            font-size: 0.75rem;
            margin-bottom: 2px;
            opacity: 0.8;
        }
        .message-time {
            font-size: 0.7rem;
            margin-top: 5px;
            opacity: 0.7;
            text-align: right;
        }
    </style>
</head>

<body>

    <?php include '../../includes/sidebar.php'; ?>

    <div id="main-content">
        <div class="header">
            <h1 class="header-title">RECORD <span class="green">COMPLAINTS</span></h1>
            <div class="header-logos">
                <img src="../../assets/img/Langkaan 2 Logo-modified.png">
                <img src="../../assets/img/dasma logo-modified.png">
            </div>
        </div>

        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <form method="GET" class="search-box">
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($searchQuery) ?>">
                    <button class="search-btn"><i class="bi bi-search"></i></button>
                </form>
                <div class="add-archive-buttons">
                    <a href="admin_rec_complaints_archive.php" class="btn btn-secondary">
                        <i class="bi bi-archive"></i> Archive
                    </a>
                </div>
            </div>    

            <div class="table-responsive shadow-sm rounded">
                <table class="table table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th>Complainant</th>
                            <th>Respondent</th>
                            <th>Type</th>
                            <th>Details</th>
                            <th>Date Filed</th>
                            <th>Status</th>
                            <th style="min-width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($complaints)): ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No complaints found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($complaints as $c): 
                                $data = json_encode($c);
                                $shortDetails = strlen($c['details']) > 30 ? substr($c['details'], 0, 30) . "..." : $c['details'];
                                
                                $badgeClass = match($c['status']) {
                                    'Pending' => 'bg-warning text-dark',
                                    'Active' => 'bg-primary',
                                    'Resolved' => 'bg-success',
                                    'Processed' => 'bg-info text-dark',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($c['complainant_name']) ?></td>
                                <td><?= htmlspecialchars($c['respondent_name']) ?></td>
                                <td><?= htmlspecialchars($c['complaint_type']) ?></td>
                                <td title="<?= htmlspecialchars($c['details']) ?>"><?= htmlspecialchars($shortDetails) ?></td>
                                <td><?= date("M d, Y", strtotime($c['date_filed'])) ?></td>
                                <td><span class="badge rounded-pill <?= $badgeClass ?>"><?= $c['status'] ?></span></td>
                                <td>
                                    <button class="btn btn-sm btn-info text-white mb-1" onclick='openViewModal(<?= $data ?>)' title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <button class="btn btn-sm btn-primary mb-1" onclick="openChatModal(<?= $c['complaint_id'] ?>)" title="Chat / Respond">
                                        <i class="bi bi-chat-dots-fill"></i>
                                    </button>

                                    <?php if($c['status'] == 'Pending'): ?>
                                    <button class="btn btn-sm btn-danger text-white mb-1" onclick="openFileToBlotterModal('<?= $c['complaint_id'] ?>')" title="File to Official Blotter">
                                        <i class="bi bi-folder-plus"></i>
                                    </button>
                                    <?php endif; ?>

                                    <button class="btn btn-sm btn-secondary mb-1" onclick="openArchiveModal('<?= $c['complaint_id'] ?>')" title="Archive">
                                        <i class="bi bi-archive"></i>
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

    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header fw-bold">
                Complaint Details
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Complainant:</strong> <span id="v_cname"></span></p>
                <p><strong>Contact Info:</strong> <span id="v_contact"></span></p>
                <hr>
                <p><strong>Respondent:</strong> <span id="v_rname"></span></p>
                <p><strong>Type:</strong> <span id="v_type"></span></p>
                <p><strong>Date of Incident:</strong> <span id="v_date"></span></p>
                <p><strong>Place of Incident:</strong> <span id="v_place"></span></p>
                <div class="p-3 bg-light border rounded mt-2">
                    <strong>Narrative / Details:</strong><br>
                    <span id="v_details"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    <div id="chatBox" class="chat-box">
                        <div class="text-center text-muted mt-5">Loading conversation...</div>
                    </div>
                    
                    <form id="chatForm" class="mt-3">
                        <input type="hidden" id="chat_complaint_id" name="complaint_id">
                        <div class="input-group">
                            <input type="text" id="chat_message" name="message" class="form-control" placeholder="Type a message to the resident..." required autocomplete="off">
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send-fill"></i> Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="fileBlotterModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
              <h5 class="modal-title fw-bold">File as Official Blotter?</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <p>This action will escalate the complaint into an <strong>Official Blotter Record</strong>.</p>
              <ul class="text-muted small">
                  <li>The data will be copied to the Blotter Records.</li>
                  <li>The complaint status will be updated to "Processed".</li>
                  <li>The "File to Blotter" button will be disabled after this.</li>
              </ul>
          </div>
          <div class="modal-footer">
              <form action="../../backend/complaint_to_blotter.php" method="POST">
                <input type="hidden" name="complaint_id" id="blotter_complaint_id">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, File to Blotter</button>
              </form>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="archiveModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-warning">
              <h5 class="modal-title fw-bold">Archive Complaint?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              Are you sure you want to archive this record?
          </div>
          <div class="modal-footer">
              <form action="../../backend/complaint_process.php" method="POST">
                <input type="hidden" name="complaint_id" id="archive_id">
                <input type="hidden" name="action" value="archive">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Yes, Archive</button>
              </form>
          </div>
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function openViewModal(data) {
            document.getElementById('v_cname').innerText = data.complainant_name;
            document.getElementById('v_contact').innerText = data.contact_no ? 'Contact: ' + data.contact_no : 'Email: ' + data.email;
            document.getElementById('v_rname').innerText = data.respondent_name;
            document.getElementById('v_type').innerText = data.complaint_type;
            document.getElementById('v_date').innerText = data.incident_date;
            document.getElementById('v_place').innerText = data.incident_place;
            document.getElementById('v_details').innerText = data.details;
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }

        let currentComplaintId = null;
        let chatInterval = null;

        function openChatModal(id) {
            currentComplaintId = id;
            document.getElementById('chat_complaint_id').value = id;
            
            const modalEl = document.getElementById('chatModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            loadMessages();
            
            if (chatInterval) clearInterval(chatInterval);
            chatInterval = setInterval(loadMessages, 3000); 

            modalEl.addEventListener('hidden.bs.modal', function () {
                clearInterval(chatInterval);
            });
        }

        function loadMessages() {
            if(!currentComplaintId) return;
            const chatBox = document.getElementById('chatBox');
            
            fetch(`../../backend/complaint_chat.php?complaint_id=${currentComplaintId}`)
            .then(res => res.json())
            .then(data => {
                chatBox.innerHTML = "";
                
                if(data.length === 0) {
                    chatBox.innerHTML = '<div class="text-center text-muted mt-auto mb-auto">No messages yet. Start the conversation.</div>';
                } else {
                    data.forEach(msg => {
                        const isAdmin = (msg.sender_role === 'Admin' || msg.sender_role === 'Staff');
                        const bubbleClass = isAdmin ? 'admin' : 'resident';
                        const senderName = isAdmin ? 'You (' + msg.sender_role + ')' : msg.sender_name;
                        
                        const html = `
                            <div class="message-bubble ${bubbleClass}">
                                <div class="sender-info">${senderName}</div>
                                <div>${msg.message}</div>
                                <div class="message-time">${msg.created_at}</div>
                            </div>
                        `;
                        chatBox.innerHTML += html;
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                }
            })
            .catch(err => console.error("Chat Error:", err));
        }

        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = this.querySelector('button');
            const input = this.querySelector('input[name="message"]');
            
            btn.disabled = true;

            fetch('../../backend/complaint_chat.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    input.value = ""; 
                    loadMessages(); 
                } else {
                    alert("Error: " + data.message);
                }
                btn.disabled = false;
            })
            .catch(err => {
                console.error("Send Error:", err);
                btn.disabled = false;
            });
        });

        function openFileToBlotterModal(id) {
            document.getElementById('blotter_complaint_id').value = id;
            new bootstrap.Modal(document.getElementById('fileBlotterModal')).show();
        }

        function openArchiveModal(id) {
            document.getElementById('archive_id').value = id;
            new bootstrap.Modal(document.getElementById('archiveModal')).show();
        }

        // --- TOAST NOTIFICATION LOGIC ---
        <?php if(isset($_SESSION['toast'])): ?>
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');
            toastMsg.innerText = "<?= $_SESSION['toast']['msg'] ?? '' ?>";
            
            const type = "<?= $_SESSION['toast']['type'] ?? 'success' ?>";
            toastEl.className = `toast align-items-center text-white border-0 ${type === 'error' ? 'bg-danger' : 'bg-success'}`;
            
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        <?php unset($_SESSION['toast']); endif; ?>
    </script>
</body>
</html>