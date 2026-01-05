document.addEventListener('DOMContentLoaded', function() {
    // Search functionality for issuance table rows
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const value = this.value.toLowerCase();
            document.querySelectorAll('#issuanceTable tr').forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(value) ? '' : 'none';
            });
        });
    }
});

// --- VIEW REQUEST DETAILS ---
window.viewRequest = function(data) {
    const residentName = data.current_resident_name || data.resident_name || 'N/A';

    let content = `
        <div class="text-center mb-4">
            <div class="avatar-circle bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px; font-size: 24px;">
                ${residentName.charAt(0)}
            </div>
            <h5 class="fw-bold mb-0">${residentName}</h5>
            <span class="badge bg-secondary rounded-pill">${data.document_type}</span>
        </div>
        
        <div class="row g-3">
            <div class="col-6">
                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Price</small>
                <span>₱${data.price || '0.00'}</span>
            </div>
            <div class="col-6">
                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Current Status</small>
                <span>${data.status}</span>
            </div>
            <div class="col-12">
                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Purpose</small>
                <div class="p-2 bg-light border rounded small">${data.purpose}</div>
            </div>
    `;
    
    if(data.business_name) {
        content += `
            <div class="col-12">
                <small class="text-muted d-block fw-bold text-uppercase" style="font-size: 10px;">Business Details</small>
                <div class="p-2 bg-light border rounded small">
                    <strong>${data.business_name}</strong><br>
                    <span class="text-muted">${data.business_location}</span>
                </div>
            </div>
        `;
    }
    
    content += `</div>`;
    
    document.getElementById('viewBody').innerHTML = content;

    // Set print button URL
    const printBtn = document.getElementById('printLink');
    if(printBtn) {
        printBtn.href = `admin_issuance_print.php?id=${data.issuance_id}`;
    }
    
    new bootstrap.Modal(document.getElementById('viewModal')).show();
};

// --- EDIT STATUS MODAL ---
window.editStatus = function(id, status) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_status').value = status;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
};

// --- SAVE STATUS ---
window.saveStatus = function() {
    const id = document.getElementById('edit_id').value;
    const status = document.getElementById('edit_status').value;
    const formData = new FormData();
    formData.append('issuance_id', id);
    formData.append('status', status);

    fetch('../../backend/admin_issuance_update.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert('Error updating status.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Error updating status.');
    });
};

// --- ARCHIVE REQUEST MODAL ---
window.openArchiveModal = function(id) {
    document.getElementById('archive_id').value = id;
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
};