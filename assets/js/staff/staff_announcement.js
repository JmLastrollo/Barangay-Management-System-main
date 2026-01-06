// --- TOAST FUNCTION ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.textContent = message;
    toast.className = 'toast';
    // Add specific classes for colors based on your CSS
    if (type === 'error') {
        toast.classList.add('danger'); // Assuming you have .danger class in CSS
    } else {
        toast.classList.add(type);
    }
    
    // Trigger animation/display
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// --- GLOBAL VARIABLES ---
let announcementsData = []; 

// --- INITIALIZATION ---
document.addEventListener('DOMContentLoaded', function() {
    loadAnnouncements();
    // checkUrlParams(); // Disable URL params logic since we use Session now
    setupImagePreviews();
    setupSearchAndSort();
});

// --- DATA LOADING ---
function loadAnnouncements() {
    fetch('../../backend/announcement_get.php')
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => {
            announcementsData = data || [];
            updateTableDisplay(); 
        })
        .catch(err => {
            console.error("Error loading data:", err);
            const table = document.getElementById('announcementTable');
            if(table) table.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data. Check console for details.</td></tr>';
        });
}

// --- HELPER: ESCAPE HTML ---
function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&")
        .replace(/</g, "<")
        .replace(/>/g, ">")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// --- HELPER: CONVERT TIME ---
function convertTime(timeString) {
    if(!timeString) return "";
    const [hour, minute] = timeString.split(':');
    const h = parseInt(hour);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const formattedHour = h % 12 || 12; 
    return `${formattedHour}:${minute} ${ampm}`;
}

// --- TABLE RENDERING & FILTERING ---
function updateTableDisplay() {
    const tbody = document.getElementById('announcementTable');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');

    if (!tbody || !searchInput || !sortSelect) return;

    const searchValue = searchInput.value.toLowerCase();
    const sortValue = sortSelect.value;
    
    let filteredData = announcementsData.filter(item => {
        return (item.title && item.title.toLowerCase().includes(searchValue)) || 
               (item.details && item.details.toLowerCase().includes(searchValue)) ||
               (item.location && item.location.toLowerCase().includes(searchValue));
    });

    filteredData.sort((a, b) => {
        if (sortValue === 'newest') {
            return new Date(b.date + ' ' + b.time) - new Date(a.date + ' ' + a.time);
        } else if (sortValue === 'oldest') {
            return new Date(a.date + ' ' + a.time) - new Date(b.date + ' ' + b.time);
        } else if (sortValue === 'title_asc') {
            return a.title.localeCompare(b.title);
        } else if (sortValue === 'title_desc') {
            return b.title.localeCompare(a.title);
        }
        return 0;
    });

    tbody.innerHTML = '';

    if (filteredData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No announcements found.</td></tr>';
        return;
    }

    filteredData.forEach(item => {
        const imgSrc = item.image ? `../../uploads/announcements/${item.image}` : '../../assets/img/announcement_placeholder.png';
        const shortDetails = item.details.length > 50 ? escapeHtml(item.details.substring(0, 50)) + '...' : escapeHtml(item.details);
        
        const dateObj = new Date(item.date);
        const formattedDate = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        
        // Escape quotes specifically for the JSON string
        const safeItem = JSON.stringify(item).replace(/'/g, "&#39;").replace(/"/g, "&quot;");

        const row = `
            <tr>
                <td>
                    <img src="${imgSrc}" 
                         onerror="this.onerror=null; this.src='../../assets/img/announcement_placeholder.png'" 
                         style="width:80px; height:60px; object-fit:cover; border-radius:6px; border: 1px solid #eee;">
                </td>
                <td class="fw-bold align-middle text-primary">${escapeHtml(item.title)}</td>
                <td class="align-middle text-muted small">${shortDetails}</td>
                <td class="align-middle">${escapeHtml(item.location)}</td>
                
                <td class="align-middle text-nowrap">
                    <div class="fw-bold text-dark">${formattedDate}</div>
                    <small class="text-muted"><i class="bi bi-clock"></i> ${convertTime(item.time)}</small>
                </td>

                <td class="align-middle text-center">
                    <div class="action-btn-container">
                        <button class="btn-action view" onclick='openViewModal(${safeItem})' title="View">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                        
                        <button class="btn-action edit" onclick='openEditModal(${safeItem})' title="Edit">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        
                        <button class="btn-action archive" onclick='openArchiveModal("${item.announcement_id}")' title="Archive">
                            <i class="bi bi-archive-fill"></i>
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

    if(searchInput) searchInput.addEventListener('keyup', updateTableDisplay);
    if(sortSelect) sortSelect.addEventListener('change', updateTableDisplay);
}

// --- IMAGE PREVIEWS ---
function setupImagePreview(inputId, imgId) {
    const input = document.getElementById(inputId);
    const img = document.getElementById(imgId);

    if (input && img) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    img.src = evt.target.result;
                    img.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    }
}

function setupImagePreviews() {
    setupImagePreview('add-photo', 'add-preview');
    setupImagePreview('edit-photo', 'edit-preview');
}

// --- MODAL TRIGGERS ---
window.openViewModal = function(data) {
    document.getElementById('v_title').innerText = data.title;
    document.getElementById('v_details').innerText = data.details;
    document.getElementById('v_location').innerText = data.location;
    document.getElementById('v_date_time').innerText = `${data.date} @ ${convertTime(data.time)}`;
    
    const img = document.getElementById('v_image');
    const src = data.image ? `../../uploads/announcements/${data.image}` : '../../assets/img/announcement_placeholder.png';
    
    img.src = src;
    img.onerror = function() { this.src = '../../assets/img/announcement_placeholder.png'; };
    
    const locationQuery = data.location || "";
    const fullQuery = encodeURIComponent(locationQuery + ", Barangay Langkaan II, Dasmariña City, Cavite");
    const mapUrl = `https://maps.google.com/maps?q=${fullQuery}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
    
    document.getElementById('v_map_frame').src = mapUrl;

    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

window.openEditModal = function(data) {
    document.getElementById('edit-id').value = data.announcement_id;
    document.getElementById('edit-title').value = data.title;
    document.getElementById('edit-details').value = data.details;
    document.getElementById('edit-location').value = data.location;
    document.getElementById('edit-date').value = data.date;
    document.getElementById('edit-time').value = data.time;

    const preview = document.getElementById('edit-preview');
    const src = data.image ? `../../uploads/announcements/${data.image}` : '../../assets/img/announcement_placeholder.png';
    
    preview.src = src;
    preview.style.display = 'block';
    preview.onerror = function() { this.src = '../../assets/img/announcement_placeholder.png'; };
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

window.openArchiveModal = function(id) {
    document.getElementById('archive-id').value = id;
    new bootstrap.Modal(document.getElementById('archiveModal')).show();
}