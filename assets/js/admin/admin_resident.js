let residentsData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadResidents();
    setupFilters();
    checkUrlParams();
    setupImagePreview();
    setupAddResidentValidations(); 
    
    // Set default year for Resident Since
    const yearInput = document.getElementById("add_res_year");
    if(yearInput) yearInput.value = new Date().getFullYear();
});

// --- TOGGLE PASSWORD ---
window.togglePass = function(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    }
}

// --- ADD RESIDENT VALIDATIONS ---
function setupAddResidentValidations() {
    const pass = document.getElementById('add_password');
    const cpass = document.getElementById('add_cpassword');
    const passMsg = document.getElementById('password-match-msg');
    
    const fnameInput = document.getElementById('add_fname');
    const mnameInput = document.getElementById('add_mname');
    const lnameInput = document.getElementById('add_lname');

    const bdate = document.querySelector('#addResidentForm input[name="birthdate"]');
    const btn = document.getElementById('btnAddSubmit');

    // Create Age Message Element
    let ageMsg = document.getElementById('add-age-msg');
    if (!ageMsg && bdate) {
        ageMsg = document.createElement('span');
        ageMsg.id = 'add-age-msg';
        ageMsg.className = 'small fw-bold d-block mt-1';
        bdate.parentNode.appendChild(ageMsg);
    }

    function validateForm() {
        let isValid = true;

        // 1. Password Validation
        if (pass && cpass && passMsg) {
            const pVal = pass.value;
            const cVal = cpass.value;
            const pLower = pVal.toLowerCase();

            // Check if name is inside password
            let nameParts = [];
            if(fnameInput && fnameInput.value) nameParts = nameParts.concat(fnameInput.value.trim().toLowerCase().split(" "));
            if(mnameInput && mnameInput.value) nameParts = nameParts.concat(mnameInput.value.trim().toLowerCase().split(" "));
            if(lnameInput && lnameInput.value) nameParts = nameParts.concat(lnameInput.value.trim().toLowerCase().split(" "));

            let containsName = false;
            for (let part of nameParts) {
                if (part.length > 2 && pLower.includes(part)) {
                    containsName = true;
                    break;
                }
            }

            if (pVal.length > 0 && pVal.length < 8) {
                passMsg.textContent = "Password must be at least 8 characters.";
                passMsg.className = "text-danger small fw-bold";
                isValid = false;
            } else if (containsName) {
                passMsg.textContent = "Password cannot contain your name.";
                passMsg.className = "text-danger small fw-bold";
                isValid = false;
            } else if (cVal.length > 0 && pVal !== cVal) {
                passMsg.textContent = "Passwords do not match!";
                passMsg.className = "text-danger small fw-bold";
                isValid = false;
            } else if (pVal.length >= 8 && pVal === cVal) {
                passMsg.textContent = "Password Valid & Matched";
                passMsg.className = "text-success small fw-bold";
            } else {
                passMsg.textContent = "";
            }
        }

        // 2. Age Validation
        if (bdate && ageMsg) {
            if (bdate.value) {
                const dob = new Date(bdate.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }

                if (age < 16) {
                    ageMsg.textContent = "You must be at least 16 years old.";
                    ageMsg.className = "text-danger small fw-bold d-block mt-1";
                    isValid = false;
                } else {
                    ageMsg.textContent = "";
                }
            }
        }

        // Enable/Disable Button
        if(btn) {
            btn.disabled = !isValid;
            btn.style.opacity = isValid ? "1" : "0.5";
        }
    }

    // Attach Listeners
    const inputsToWatch = [pass, cpass, bdate, fnameInput, mnameInput, lnameInput];
    inputsToWatch.forEach(el => {
        if(el) el.addEventListener('keyup', validateForm);
        if(el && el.tagName === 'INPUT' && el.type === 'date') el.addEventListener('change', validateForm);
    });
}

function setupImagePreview() {
    const input = document.getElementById('add-photo');
    const preview = document.getElementById('add-preview');
    if (input && preview) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) { preview.src = evt.target.result; }
                reader.readAsDataURL(file);
            }
        });
    }
}

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
    const success = urlParams.get('success');
    
    if (success === 'updated') showToast('Resident updated successfully!', 'success');
    else if (success === 'added') showToast('New resident account created successfully!', 'success');
    else if (success === 'reset') showToast('Password reset to 12345678 successfully!', 'warning');
    
    if (success) window.history.replaceState({}, document.title, window.location.pathname);
}

function loadResidents() {
    const tableBody = document.getElementById('residentTableBody');
    if(tableBody) tableBody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Loading residents...</td></tr>';

    fetch('../../backend/resident_get.php')
        .then(res => res.json())
        .then(data => {
            residentsData = data;
            updateTableDisplay();
        })
        .catch(err => {
            console.error("Error:", err);
            if(tableBody) tableBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Error loading data.</td></tr>';
        });
}

function updateTableDisplay() {
    const tbody = document.getElementById('residentTableBody');
    if (!tbody) return;

    const searchText = document.getElementById('searchInput').value.toLowerCase();
    const phaseFilter = document.getElementById('phaseFilter').value; 
    const statusFilter = document.getElementById('statusFilter').value;

    let filtered = residentsData.filter(item => {
        // --- IMPORTANT: HIDE ARCHIVED FROM MAIN LIST ---
        if (item.status === 'Archived') {
            return false; 
        }

        const fullName = `${item.first_name} ${item.last_name}`.toLowerCase();
        const matchesSearch = fullName.includes(searchText);
        const itemPhase = item.purok || ""; 
        let matchesPhase = phaseFilter ? itemPhase.includes(phaseFilter) : true;
        const matchesStatus = statusFilter ? item.status === statusFilter : true;
        
        return matchesSearch && matchesPhase && matchesStatus;
    });

    tbody.innerHTML = '';
    if (filtered.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No active residents found.</td></tr>';
        return;
    }

    filtered.forEach(item => {
        const defaultIcon = '../../assets/img/profile.jpg'; 
        const imgSrc = (item.image && item.image !== "") ? `../../uploads/residents/${item.image}` : defaultIcon;

        let statusBadge = item.status === 'Active' ? '<span class="badge status-active">Active</span>' :
                          item.status === 'Rejected' ? '<span class="badge status-rejected">Rejected</span>' :
                          `<span class="badge bg-secondary">${item.status}</span>`;

        const jsonItem = JSON.stringify(item).replace(/'/g, "&#39;");

        const row = `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <img src="${imgSrc}" class="resident-img-sm me-3" alt="Profile" onerror="this.src='${defaultIcon}'">
                        <div>
                            <div class="fw-bold text-dark">${item.first_name} ${item.last_name}</div>
                            <div class="small text-muted" style="font-size: 11px;">ID: ${item.resident_id}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="fw-bold text-secondary" style="font-size: 14px;">${item.purok || 'N/A'}</div>
                    <div class="small text-muted text-truncate" style="max-width: 200px;">${item.address || ''}</div>
                </td>
                <td>${item.contact_no || 'N/A'}</td> 
                <td>${statusBadge}</td>
                <td class="text-center">
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-action view" onclick='openViewModal(${jsonItem})' title="View Profile">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-action edit" onclick='openEditProfileModal(${jsonItem})' title="Edit Info">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-action status" onclick='openStatusModal(${jsonItem})' title="Manage Status">
                            <i class="bi bi-shield-lock-fill"></i>
                        </button>
                        <button class="btn btn-sm btn-action reset" onclick='openResetModal(${jsonItem})' title="Reset Password">
                            <i class="bi bi-key-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
}

function setupFilters() {
    document.getElementById('searchInput').addEventListener('keyup', updateTableDisplay);
    document.getElementById('phaseFilter').addEventListener('change', updateTableDisplay);
    document.getElementById('statusFilter').addEventListener('change', updateTableDisplay);
}

// --- MODAL TRIGGERS ---
window.openViewModal = function(data) {
    document.getElementById('v_name').innerText = `${data.first_name} ${data.last_name} ${data.middle_name || ''} ${data.suffix_name || ''}`;
    document.getElementById('v_email').innerText = data.email || 'No Email';
    const badge = document.getElementById('v_status_badge');
    badge.className = 'badge rounded-pill px-3 py-2 border'; 
    if (data.status === 'Active') { badge.classList.add('bg-success-subtle', 'text-success', 'border-success'); badge.innerText = 'Active'; }
    else if (data.status === 'Rejected') { badge.classList.add('bg-danger-subtle', 'text-danger', 'border-danger'); badge.innerText = 'Rejected'; }
    else { badge.classList.add('bg-secondary-subtle', 'text-secondary'); badge.innerText = data.status; }
    document.getElementById('v_phase').innerText = data.purok || 'N/A';
    document.getElementById('v_address').innerText = data.address || 'N/A';
    document.getElementById('v_contact').innerText = data.contact_no || 'N/A';
    document.getElementById('v_gender').innerText = data.gender || 'N/A';
    document.getElementById('v_bday').innerText = data.birthdate || 'N/A';
    document.getElementById('v_occupation').innerText = data.occupation || 'N/A';
    document.getElementById('v_civil').innerText = data.civil_status || 'N/A';
    document.getElementById('v_pwd').innerHTML = (data.is_pwd === 'Yes') ? '<span class="badge bg-primary">Yes, PWD</span>' : 'No';
    document.getElementById('v_voter').innerText = data.voter_status || 'N/A';
    let age = 'N/A';
    if(data.birthdate) {
        const dob = new Date(data.birthdate);
        const diff = Date.now() - dob.getTime();
        const ageDate = new Date(diff);
        age = Math.abs(ageDate.getUTCFullYear() - 1970);
    }
    let ageDisplay = age;
    if(age !== 'N/A' && age >= 60) ageDisplay += ' <span class="badge bg-warning text-dark ms-2">Senior Citizen</span>';
    document.getElementById('v_age').innerHTML = ageDisplay;
    const defaultIcon = '../../assets/img/profile.jpg';
    const imgSrc = (data.image) ? `../../uploads/residents/${data.image}` : defaultIcon;
    const imgEl = document.getElementById('v_image');
    imgEl.src = imgSrc;
    imgEl.onerror = function() { this.src = defaultIcon; };
    document.getElementById('btnManageStatus').onclick = function() {
        bootstrap.Modal.getInstance(document.getElementById('viewModal')).hide();
        openStatusModal(data);
    };
    new bootstrap.Modal(document.getElementById('viewModal')).show();
}

window.openEditProfileModal = function(data) {
    document.getElementById('edit_id').value = data.resident_id;
    document.getElementById('edit_fname').value = data.first_name;
    document.getElementById('edit_mname').value = data.middle_name || '';
    document.getElementById('edit_lname').value = data.last_name;
    document.getElementById('edit_contact').value = data.contact_no;
    document.getElementById('edit_email').value = data.email;
    document.getElementById('edit_purok').value = data.purok; 
    document.getElementById('edit_address').value = data.address;
    document.getElementById('edit_pwd').value = data.is_pwd;
    document.getElementById('edit_voter').value = data.voter_status;
    document.getElementById('edit_occupation').value = data.occupation;
    new bootstrap.Modal(document.getElementById('editResidentModal')).show();
}

window.openStatusModal = function(data) {
    document.getElementById('status_id').value = data.resident_id;
    document.getElementById('status_val').value = data.status;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

window.openResetModal = function(data) {
    document.getElementById('reset_id').value = data.resident_id;
    document.getElementById('reset_name').textContent = `${data.first_name} ${data.last_name}`;
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}