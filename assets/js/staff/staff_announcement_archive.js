// --- GLOBAL VARIABLES ---
// Kukunin ang data mula sa PHP variable na 'initialData' kung meron, or empty array
let announcementsData = typeof initialData !== 'undefined' ? initialData : [];

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function() {
    // Kung walang initial data, try mag-fetch (optional backup)
    if (announcementsData.length === 0) {
        // loadAnnouncements(); // Uncomment if you have a specific backend endpoint
    } else {
        updateTableDisplay();
    }
    
    setupSearchAndSort();
    checkUrlParams();
});

// --- URL PARAM CHECKER (Toast) ---
function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    if (success === 'restored') showToast('Announcement restored successfully!', 'success');
    else if (success === 'deleted') showToast('Announcement deleted permanently.', 'error'); // Red toast for delete

    if (success) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// --- TOAST FUNCTION ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className = `toast ${type} show`; // Uses your toast.css classes
    
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// --- TABLE RENDERING & FILTERING ---
function updateTableDisplay() {
    const tbody = document.getElementById('archiveTableBody');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const monthFilter = document.getElementById('monthFilter');

    if (!tbody) return;

    const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
    const sortValue = sortSelect ? sortSelect.value : 'newest';
    const monthValue = monthFilter ? monthFilter.value : ''; // Format: YYYY-MM

    // 1. FILTER
    let filteredData = announcementsData.filter(item => {
        // Search Filter
        const matchesSearch = (item.title && item.title.toLowerCase().includes(searchValue)) || 
                              (item.details && item.details.toLowerCase().includes(searchValue));
        
        // Month Filter
        let matchesMonth = true;
        if (monthValue) {
            const itemDate = item.date.substring(0, 7); // YYYY-MM
            matchesMonth = itemDate === monthValue;
        }

        return matchesSearch && matchesMonth;
    });

    // 2. SORT
    filteredData.sort((a, b) => {
        const dateA = new Date(a.date + ' ' + (a.time || '00:00'));
        const dateB = new Date(b.date + ' ' + (b.time || '00:00'));

        if (sortValue === 'newest') return dateB - dateA;
        if (sortValue === 'oldest') return dateA - dateB;
        return 0;
    });

    // 3. RENDER
    tbody.innerHTML = '';

    if (filteredData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No archived items found.</td></tr>';
        return;
    }

    filteredData.forEach(item => {
        // Format Date
        const dateObj = new Date(item.date);
        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Escape HTML to prevent XSS
        const title = escapeHtml(item.title);
        const details = escapeHtml(item.details).substring(0, 60) + (item.details.length > 60 ? '...' : '');

        // --- NEW BUTTON STYLE (Officials Style) ---
        // Restore = Green (.edit class sa officials CSS)
        // Delete = Red (.archive class sa officials CSS)
        
        const row = `
            <tr>
                <td class="ps-4 fw-bold text-secondary">${title}</td>
                <td class="text-muted small">${details}</td>
                <td>${formattedDate}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn-action edit" onclick="openRestoreModal('${item.announcement_id}')" title="Restore">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        
                        <button class="btn-action archive" onclick="openDeleteModal('${item.announcement_id}')" title="Delete Permanently">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function setupSearchAndSort() {
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const monthFilter = document.getElementById('monthFilter');

    if(searchInput) searchInput.addEventListener('keyup', updateTableDisplay);
    if(sortSelect) sortSelect.addEventListener('change', updateTableDisplay);
    if(monthFilter) monthFilter.addEventListener('change', updateTableDisplay);
}

// --- MODAL TRIGGERS ---
window.openRestoreModal = function(id) {
    const input = document.getElementById('r_id');
    if(input) input.value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

window.openDeleteModal = function(id) {
    const input = document.getElementById('d_id');
    if(input) input.value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// --- HELPER ---
function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}