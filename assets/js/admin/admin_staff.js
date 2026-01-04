document.addEventListener('DOMContentLoaded', function() {
    setupSearch();
    checkUrlParams();
});

// --- SEARCH FILTER ---
function setupSearch() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll('#staffTable tr');
            
            rows.forEach(row => {
                if (row.cells.length > 1) {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(filter) ? '' : 'none';
                }
            });
        });
    }
}

// --- EDIT MODAL TRIGGER ---
window.openEditStaffModal = function(data) {
    document.getElementById('edit_user_id').value = data.user_id;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_role').value = data.role;
    document.getElementById('edit_name_display').value = data.first_name + ' ' + data.last_name;
    
    if(data.status) {
        document.getElementById('edit_status').value = data.status;
    }
    
    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}

// --- TOAST NOTIFICATIONS ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className = 'toast ' + type;
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3000);
}

function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // Success Messages
    if (urlParams.get('success') === 'added') showToast('Staff account created!', 'success');
    if (urlParams.get('success') === 'updated') showToast('Staff details updated!', 'success');
    
    // Error Messages
    if (urlParams.get('error') === 'exists') showToast('Email already in use!', 'danger');
    if (urlParams.get('error') === 'empty') showToast('Please fill all fields!', 'danger');
    if (urlParams.get('error') === 'failed') showToast('Database error occurred.', 'danger');
    
    // Clean URL by removing params without refresh
    window.history.replaceState({}, document.title, window.location.pathname);
}