/**
 * Staff History Filtering Logic
 */
document.addEventListener('DOMContentLoaded', function() {
    const logSearch = document.getElementById('logSearch');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const resetButton = document.getElementById('resetFilters');
    const logRows = document.querySelectorAll('.log-row');

    /**
     * Master Filter Function
     */
    function applyFilters() {
        const searchTerm = logSearch.value.toLowerCase();
        const startDate = startDateInput.value; // Format: YYYY-MM-DD
        const endDate = endDateInput.value;     // Format: YYYY-MM-DD

        logRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowDate = row.getAttribute('data-date');
            
            let matchesSearch = rowText.includes(searchTerm);
            let matchesDate = true;

            // Date Range logic
            if (startDate && endDate) {
                matchesDate = (rowDate >= startDate && rowDate <= endDate);
            } else if (startDate) {
                matchesDate = (rowDate >= startDate);
            } else if (endDate) {
                matchesDate = (rowDate <= endDate);
            }

            // Show/Hide Row
            if (matchesSearch && matchesDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Optional: Show "No results" message if all hidden
        const visibleRows = Array.from(logRows).filter(r => r.style.display !== 'none');
        updateNoResultsMessage(visibleRows.length === 0);
    }

    function updateNoResultsMessage(show) {
        let existingMsg = document.querySelector('.filter-no-results');
        if (show) {
            if (!existingMsg) {
                const tbody = document.getElementById('logsTable');
                const tr = document.createElement('tr');
                tr.className = 'filter-no-results';
                tr.innerHTML = `<td colspan="4" class="text-center py-4 text-muted">No logs match your filters.</td>`;
                tbody.appendChild(tr);
            }
        } else {
            if (existingMsg) existingMsg.remove();
        }
    }

    function resetFilters() {
        logSearch.value = '';
        startDateInput.value = '';
        endDateInput.value = '';
        applyFilters();
    }

    // Listeners
    logSearch.addEventListener('keyup', applyFilters);
    startDateInput.addEventListener('change', applyFilters);
    endDateInput.addEventListener('change', applyFilters);
    resetButton.addEventListener('click', resetFilters);
});

// Sidebar Toggle (Global Function if needed)
window.toggleSidebar = function() {
    const sidebar = document.getElementById('sidebar');
    if(sidebar) sidebar.classList.toggle('active');
};