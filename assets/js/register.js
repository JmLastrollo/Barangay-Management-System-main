// --- 1. INITIAL SETUP ---
document.addEventListener("DOMContentLoaded", function() {
    // Set default value for Resident Since to current year
    const yearInput = document.getElementById("YearInput");
    if(yearInput) yearInput.value = new Date().getFullYear();
});

// --- 2. VALIDATE AGE FUNCTION ---
function validateAge() {
    const inputDate = document.getElementById("bdate").value;
    const message = document.getElementById("ageMessage");
    const btn = document.getElementById("submitBtn");

    if (!inputDate) return;

    const dateNow = new Date();
    const birthDate = new Date(inputDate);

    let age = dateNow.getFullYear() - birthDate.getFullYear();
    const m = dateNow.getMonth() - birthDate.getMonth();
    
    // Adjust age if not yet birthday
    if (m < 0 || (m === 0 && dateNow.getDate() < birthDate.getDate())) {
        age--;
    }

    // Logic: Must be at least 16 years old
    if (age < 16) {
        message.style.color = "#dc3545"; 
        message.innerHTML = "<i class='bi bi-exclamation-circle-fill'></i> You must be at least 16 years old.";
        
        btn.disabled = true;
        btn.style.opacity = "0.6";
    } else {
        message.innerHTML = ""; 
        // Re-check password to potentially enable button
        checkPassword();
    }
}

// --- 3. SHOW/HIDE PASSWORD FUNCTION ---
function togglePass(inputId, iconId) {
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

// --- 4. CHECK PASSWORD MATCH + NAME RESTRICTION ---
function checkPassword() {
    let password = document.getElementById("password").value;
    let cpassword = document.getElementById("cpassword").value;
    let message = document.getElementById("message");
    let btn = document.getElementById("submitBtn");
    
    // Check if there is an existing age error
    let ageError = document.getElementById("ageMessage").innerText;

    let fname = document.querySelector('input[name="fname"]').value.trim().toLowerCase();
    let mname = document.querySelector('input[name="mname"]').value.trim().toLowerCase();
    let lname = document.querySelector('input[name="lname"]').value.trim().toLowerCase();
    
    let passLower = password.toLowerCase();

    // Default state
    btn.disabled = true;
    btn.style.opacity = "0.6";
    message.style.color = "#dc3545"; 

    // CHECK 1: Min length
    if (password.length < 8) {
        message.innerHTML = "<i class='bi bi-exclamation-circle'></i> c";
        return;
    }

    // CHECK 2: Name restriction
    let nameParts = [];
    if (fname) nameParts = nameParts.concat(fname.split(" "));
    if (mname) nameParts = nameParts.concat(mname.split(" "));
    if (lname) nameParts = nameParts.concat(lname.split(" "));

    let containsName = false;
    
    for (let part of nameParts) {
        if (part.length > 2 && passLower.includes(part)) {
            containsName = true;
            break;
        }
    }

    if (containsName) {
        message.innerHTML = "<i class='bi bi-exclamation-circle'></i> Password cannot contain your name.";
        return; 
    }

    // CHECK 3: Confirm Match
    if (cpassword.length > 0) {
        if (password === cpassword) {
            message.style.color = "#198754"; 
            message.innerHTML = "<i class='bi bi-check-circle-fill'></i> Password Valid & Matched";
            
            // FINAL CHECK: Only enable if no age error
            if (ageError === "") {
                btn.disabled = false;
                btn.style.opacity = "1";
            }
        } else {
            message.innerHTML = "<i class='bi bi-x-circle-fill'></i> Passwords Do Not Match";
        }
    } else {
        message.style.color = "#fd7e14"; 
        message.innerHTML = "<i class='bi bi-info-circle'></i> Please confirm your password.";
    }
}

// --- 5. BACK TO TOP FUNCTIONALITY ---
window.onscroll = function() {
    let mybutton = document.getElementById("backToTop");
    if (mybutton) {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }
};

document.getElementById("backToTop")?.addEventListener("click", function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// --- 6. TOAST HELPER ---
function showToast(message, type = "error") {
    const t = document.getElementById("toast");
    if (t) {
        t.className = "toast";
        t.innerHTML = `<div class="toast-body">${message}</div>`;
        t.classList.add(type);
        t.classList.add("show");
        setTimeout(() => { t.classList.remove("show"); }, 3000);
    }
}