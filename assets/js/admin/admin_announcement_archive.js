function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.className = 'toast';
    if (type === 'error') toast.classList.add('danger');
    else toast.classList.add(type);
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
});

function setupFilters() {
    const searchInput = document.getElementById('searchInput');
    const monthFilter = document.getElementById('monthFilter');
    const sortSelect = document.getElementById('sortSelect');
    
    if(searchInput) searchInput.addEventListener('keyup', filterTable);
    if(monthFilter) monthFilter.addEventListener('change', filterTable);
    if(sortSelect) sortSelect.addEventListener('change', filterTable);
}

function filterTable() {
    const searchValue = document.getElementById('searchInput').value.toLowerCase();
    const monthValue = document.getElementById('monthFilter').value;
    const sortValue = document.getElementById('sortSelect').value;
    const tbody = document.getElementById('archiveTableBody');

    // Filter using initialData
    let filtered = initialData.filter(item => {
        // Text Search
        const matchesText = (item.title && item.title.toLowerCase().includes(searchValue)) ||
                            (item.details && item.details.toLowerCase().includes(searchValue));
        
        // Month Filter
        let matchesMonth = true;
        if (monthValue) {
            matchesMonth = item.date.startsWith(monthValue);
        }
        
        return matchesText && matchesMonth;
    });

    // Sort
    filtered.sort((a, b) => {
        if (sortValue === 'newest') return new Date(b.date) - new Date(a.date);
        if (sortValue === 'oldest') return new Date(a.date) - new Date(b.date);
        return 0;
    });

    // Render
    tbody.innerHTML = '';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No archived items found.</td></tr>';
        return;
    }

    filtered.forEach(item => {
        const dateObj = new Date(item.date);
        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        const shortDetails = item.details.length > 50 ? item.details.substring(0, 50) + '...' : item.details;

        const row = `
            <tr>
                <td class="ps-4 fw-bold text-primary">${item.title}</td>
                <td><small class="text-muted">${shortDetails}</small></td>
                <td>${formattedDate}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn-action edit" title="Restore" onclick='openRestoreModal("${item.announcement_id}")'>
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                        <button class="btn-action archive" title="Delete Permanently" onclick="openDeleteModal('${item.announcement_id}')">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

// Modal Triggers
window.openRestoreModal = function(id) {
    document.getElementById('r_id').value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

window.openDeleteModal = function(id) {
    document.getElementById('d_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}