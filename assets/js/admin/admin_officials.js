/**
 * BMS - Admin Officials Script
 * Updated to match Resident Script structure with Toast signals
 */

document.addEventListener('DOMContentLoaded', function() {
    checkUrlParams();      // 1. Check URL signals for floating toast
    setupSearch();         // 2. Real-time table filter
    setupImagePreview();   // 3. Add Modal Image Preview
});

// --- 1. TOAST NOTIFICATION LOGIC ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    // Ginagamit ang classes base sa css: toast.success, toast.error, toast.warning
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
        badge.className = 'badge rounded-pill px-4 mb-4 bg-success';
        if (data.status !== 'Active') {
            badge.classList.replace('bg-success', 'bg-secondary');
        }
    }

    const imgEl = document.getElementById('v_image');
    const imgContainer = document.getElementById('v_image_container'); // ID ng div wrapper
    const defaultIcon = '../../assets/img/profile.jpg';
    const initial = data.full_name.charAt(0).toUpperCase();

    if (imgEl && imgContainer) {
        if (data.image && data.image.trim() !== "") {
            imgEl.style.display = 'block';
            imgEl.src = `../../uploads/officials/${data.image}`;
            // Tanggalin ang initials kung may image
            const oldInitial = imgContainer.querySelector('.initials-placeholder');
            if(oldInitial) oldInitial.remove();
        } else {
            // Itago ang img tag at ipakita ang Initials gaya ng sa table
            imgEl.style.display = 'none';
            imgContainer.innerHTML = `<span class="initials-placeholder fw-bold text-secondary" style="font-size: 50px;">${initial}</span>`;
        }
        imgEl.onerror = function() { this.src = defaultIcon; };
    }

    new bootstrap.Modal(document.getElementById('viewModal')).show();
};

// --- 3. EDIT OFFICIAL ---
window.editOfficial = function (data) {
    setValue('edit_id', data.official_id);
    setValue('edit_name', data.full_name);
    setValue('edit_position', data.position);
    setValue('edit_term_start', data.term_start);
    
    // Handle Term End (Empty if 0000-00-00 or null)
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
            const rows = document.querySelectorAll('#officialsTable tr'); // Siguraduhing tama ang ID ng tbody/table

            rows.forEach(row => {
                // Skip placeholder rows (e.g., "No data found")
                if (row.cells.length <= 1) return;

                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

// --- 5. IMAGE PREVIEW (Add Modal) ---
function setupImagePreview() {
    const addInput = document.getElementById('add-photo'); // Base sa admin_officials.php mo
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