// --- TOAST FUNCTION ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className = 'toast'; 
    toast.classList.add(type); 
    
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function() {
    setupFilters();
    setupSort();
    checkUrlParams();
});

// --- URL PARAM CHECKER ---
function checkUrlParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    if (success === 'restored') {
        showToast('Announcement restored successfully!', 'success');
    } else if (success === 'deleted') {
        showToast('Announcement permanently deleted.', 'success'); 
    }

    if (success) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

// --- UNIFIED FILTER FUNCTION (Search + Month & Year) ---
function setupFilters() {
    const searchInput = document.getElementById("searchInput");
    const monthFilter = document.getElementById("monthFilter"); // This is now <input type="month">

    function filterTable() {
        const searchText = searchInput ? searchInput.value.toLowerCase() : "";
        const monthValue = monthFilter ? monthFilter.value : ""; // Format returns "YYYY-MM" (e.g., "2026-01")
        
        let rows = document.querySelectorAll("#archiveTableBody tr");
        
        rows.forEach(row => {
            // Guard clause for empty/loading row
            if (row.cells.length <= 1) return;

            // 1. Text Matching
            const rowText = row.innerText.toLowerCase();
            const matchesText = rowText.includes(searchText);

            // 2. Month & Year Matching
            // Get date from 3rd column (Index 2). Text is "Jan 03, 2026"
            const rowDateText = row.cells[2].innerText; 
            const rowDateObj = new Date(rowDateText);
            
            // Format JS Date to "YYYY-MM" to match input value
            const yyyy = rowDateObj.getFullYear();
            // getMonth() is 0-indexed, so +1. padStart ensures "01", "02", etc.
            const mm = String(rowDateObj.getMonth() + 1).padStart(2, '0');
            const rowMonthYear = `${yyyy}-${mm}`;

            let matchesMonth = true;
            if (monthValue) {
                // Compare YYYY-MM
                matchesMonth = (rowMonthYear === monthValue);
            }

            // Show row ONLY if BOTH conditions are true
            if (matchesText && matchesMonth) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // Attach Listeners
    if (searchInput) searchInput.addEventListener("keyup", filterTable);
    if (monthFilter) monthFilter.addEventListener("change", filterTable);
}

// --- SORT FUNCTION ---
function setupSort() {
    const sortSelect = document.getElementById('sortSelect');
    const tableBody = document.getElementById('archiveTableBody');

    if (sortSelect && tableBody) {
        sortSelect.addEventListener('change', function() {
            const order = this.value;
            const rows = Array.from(tableBody.querySelectorAll('tr'));

            // Guard clause
            if(rows.length === 0 || (rows.length === 1 && rows[0].cells.length <= 1)) return;

            rows.sort((rowA, rowB) => {
                // Get Date text (e.g. "Jan 03, 2026")
                const dateTextA = rowA.cells[2].innerText;
                const dateTextB = rowB.cells[2].innerText;
                
                const dateA = new Date(dateTextA);
                const dateB = new Date(dateTextB);

                if (order === 'newest') {
                    return dateB - dateA; // Descending
                } else {
                    return dateA - dateB; // Ascending
                }
            });

            rows.forEach(row => tableBody.appendChild(row));
        });
    }
}

// --- MODAL FUNCTIONS (Global Scope) ---
window.openRestoreModal = function(id) {
    document.getElementById('r_id').value = id;
    new bootstrap.Modal(document.getElementById('restoreModal')).show();
}

window.openDeleteModal = function(id) {
    document.getElementById('d_id').value = id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}