/**
 * BMS - Admin Officials Script
 * Updated to work with css/toast.css and PHP Sessions
 */

document.addEventListener('DOMContentLoaded', function() {
    checkUrlParams();      // Optional: Check URL signals (backward compatibility)
    setupSearch();         // Real-time table filter
    setupImagePreview();   // Add Modal Image Preview
});

// --- 1. TOAST NOTIFICATION LOGIC ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    // Set classes based on css/toast.css: .toast.show.success, etc.
    toast.className = `toast ${type} show`;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    if (success) {
        let msg = "Operation successful!";
        if (success === 'added') msg = "New official added successfully!";
        if (success === 'updated') msg = "Official details updated successfully!";
        if (success === 'archived') msg = "Official moved to archives successfully!";
        
        showToast(msg, 'success');
        // Clean the URL without refreshing
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    if (error) {
        let msg = "An error occurred.";
        if (error === 'db_error') msg = "Database error. Please try again.";
        showToast(msg, 'error');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// --- 2. VIEW OFFICIAL ---
window.viewOfficial = function (data) {
    setText('v_name', data.full_name);
    setText('v_position', data.position);
    setText('v_term_start', formatDate(data.term_start));
    setText('v_term_end', (data.term_end && data.term_end !== '0000-00-00') ? formatDate(data.term_end) : 'Present');

    const badge = document.getElementById('v_status_badge');
    if (badge) {
        badge.innerText = data.status;
        if (data.status === 'Active') {
            badge.className = 'status-badge-custom';
        } else {
            badge.className = 'status-badge-custom status-badge-inactive';
        }
    }

    const imgEl = document.getElementById('v_image');
    const imgContainer = document.getElementById('v_image_container');
    const defaultIcon = '../../assets/img/profile.jpg';
    const initial = data.full_name.charAt(0).toUpperCase();

    if (imgEl && imgContainer) {
        // Reset contents
        imgContainer.innerHTML = ''; 

        if (data.image && data.image.trim() !== "") {
            // Kung may image file
            const img = document.createElement('img');
            img.src = `../../uploads/officials/${data.image}`;
            img.className = 'profile-img-view';
            img.onerror = function() { this.src = defaultIcon; };
            imgContainer.appendChild(img);
        } else {
            // Kung walang image, Initials lang
            const div = document.createElement('div');
            div.className = 'profile-initials';
            div.innerText = initial;
            imgContainer.appendChild(div);
        }
    }

    new bootstrap.Modal(document.getElementById('viewModal')).show();
};

// --- 3. EDIT OFFICIAL ---
window.editOfficial = function (data) {
    setValue('edit_id', data.official_id);
    setValue('edit_name', data.full_name);
    setValue('edit_position', data.position);
    setValue('edit_term_start', data.term_start);
    
    const termEndVal = (data.term_end && data.term_end !== '0000-00-00') ? data.term_end : '';
    setValue('edit_term_end', termEndVal);

    new bootstrap.Modal(document.getElementById('editOfficialModal')).show();
};

// --- 4. SEARCH FUNCTIONALITY ---
function setupSearch() {
    const searchInput = document.getElementById('officialSearch');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#officialsTable tr');

            rows.forEach(row => {
                if (row.cells.length <= 1) return; // Skip "No data" rows

                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

// --- 5. IMAGE PREVIEW (Add Modal) ---
function setupImagePreview() {
    const addInput = document.getElementById('add-photo');
    const addPreview = document.getElementById('add-preview');
    const addPlaceholder = document.getElementById('add-placeholder');

    if (addInput) {
        addInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    if (addPreview) {
                        addPreview.src = evt.target.result;
                        addPreview.style.display = 'block';
                    }
                    if (addPlaceholder) addPlaceholder.style.display = 'none';
                }
                reader.readAsDataURL(file);
            }
        });
    }
}

// --- 6. ARCHIVE OFFICIAL (Active Page) ---
window.archiveOfficial = function(id, name) {
    setValue('archive_id', id);
    setText('archive_name_display', name);
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
};

// --- 7. RESTORE OFFICIAL (Archive Page) ---
window.restoreOfficial = function(id, name) {
    setValue('restore_id', id);
    setText('restore_name_display', name);
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
};

// --- 8. DELETE OFFICIAL PERMANENTLY (Archive Page) ---
window.deleteOfficial = function(id, name) {
    setValue('delete_id', id);
    setText('delete_name_display', name);
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
};

// --- 9. FILTER BY YEAR (Archive Page) ---
window.filterByYear = function(year) {
    if (year) {
        window.location.href = '?year=' + year;
    } else {
        window.location.href = window.location.pathname;
    }
};

// ... (Rest of utilities)

// --- UTILITIES / HELPERS ---
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value;
}

function formatDate(dateString) {
    if (!dateString || dateString === '0000-00-00') return 'Present';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}