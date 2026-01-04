let archiveData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadArchives();
    
    // Search Listener
    document.getElementById('searchInput').addEventListener('keyup', function() {
        renderArchiveTable(this.value.toLowerCase());
    });

    // Check URL for success message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === 'restored') {
        showToast('Resident account restored successfully!', 'success');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function loadArchives() {
    const tbody = document.getElementById('archiveTableBody');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Loading...</td></tr>';

    fetch('../../backend/resident_get.php')
        .then(res => res.json())
        .then(data => {
            // FILTER ONLY 'Archived' items
            archiveData = data.filter(item => item.status === 'Archived');
            renderArchiveTable();
        })
        .catch(err => {
            console.error(err);
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Error loading data.</td></tr>';
        });
}

function renderArchiveTable(searchText = '') {
    const tbody = document.getElementById('archiveTableBody');
    tbody.innerHTML = '';

    const filtered = archiveData.filter(item => {
        const name = `${item.first_name} ${item.last_name}`.toLowerCase();
        return name.includes(searchText);
    });

    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No archived residents found.</td></tr>';
        return;
    }

    filtered.forEach(item => {
        const defaultIcon = '../../assets/img/profile.jpg';
        const imgSrc = (item.image) ? `../../uploads/residents/${item.image}` : defaultIcon;
        
        const jsonItem = JSON.stringify(item).replace(/'/g, "&#39;");

        const row = `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <img src="${imgSrc}" class="resident-img-sm me-3" style="filter: grayscale(100%);" alt="Profile" onerror="this.src='${defaultIcon}'">
                        <div>
                            <div class="fw-bold text-secondary">${item.first_name} ${item.last_name}</div>
                            <div class="small text-muted">ID: ${item.resident_id}</div>
                        </div>
                    </div>
                </td>
                <td class="text-muted">
                    <div class="fw-bold" style="font-size: 14px;">${item.purok || 'N/A'}</div>
                    <div class="small text-truncate" style="max-width: 200px;">${item.address || ''}</div>
                </td>
                <td><span class="badge bg-secondary text-light">Archived</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-success" onclick='openRestoreModal(${jsonItem})' title="Restore Account">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                    </button>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

// Modal Trigger
window.openRestoreModal = function(data) {
    document.getElementById('restore_id').value = data.resident_id;
    document.getElementById('restore_name').innerText = `${data.first_name} ${data.last_name}`;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 3000);
}