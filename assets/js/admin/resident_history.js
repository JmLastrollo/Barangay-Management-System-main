/**
 * Resident History Filtering Logic
 */
document.addEventListener('DOMContentLoaded', function() {
    const logSearch = document.getElementById('residentLogSearch');
    const startDateInput = document.getElementById('startDate');
    const endDateInput = document.getElementById('endDate');
    const resetButton = document.getElementById('resetFilters');
    const logRows = document.querySelectorAll('.log-row');
    const tbody = document.getElementById('residentLogsTable');

    function applyFilters() {
        const searchTerm = logSearch.value.toLowerCase();
        const startDate = startDateInput.value;
        const endDate = endDateInput.value;

        logRows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const rowDate = row.getAttribute('data-date');
            
            let matchesSearch = rowText.includes(searchTerm);
            let matchesDate = true;

            if (startDate && endDate) {
                matchesDate = (rowDate >= startDate && rowDate <= endDate);
            } else if (startDate) {
                matchesDate = (rowDate >= startDate);
            } else if (endDate) {
                matchesDate = (rowDate <= endDate);
            }

            row.style.display = (matchesSearch && matchesDate) ? '' : 'none';
        });

        // Show "No results" message if all rows are hidden
        const visibleRows = Array.from(logRows).filter(r => r.style.display !== 'none');
        updateNoResultsMessage(visibleRows.length === 0);
    }

    function updateNoResultsMessage(show) {
        let existingMsg = document.querySelector('.filter-no-results');
        if (show) {
            if (!existingMsg) {
                const tr = document.createElement('tr');
                tr.className = 'filter-no-results';
                tr.innerHTML = `<td colspan="3" class="text-center py-4 text-muted">No logs match your filters.</td>`;
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

    // Event Listeners
    logSearch.addEventListener('keyup', applyFilters);
    startDateInput.addEventListener('change', applyFilters);
    endDateInput.addEventListener('change', applyFilters);
    resetButton.addEventListener('click', resetFilters);
});