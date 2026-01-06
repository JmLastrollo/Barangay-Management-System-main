function viewRequest(data) {
    // 1. Populate Modal Fields (Existing logic ito malamang)
    // Example construction ng table sa loob ng modal
    let content = `
        <table class="table table-bordered">
            <tr><th>Resident</th><td>${data.current_resident_name}</td></tr>
            <tr><th>Document</th><td>${data.document_type}</td></tr>
            <tr><th>Purpose</th><td>${data.purpose}</td></tr>
            <tr><th>Date</th><td>${data.request_date}</td></tr>
            <tr><th>Price</th><td>₱${data.amount || '0.00'}</td></tr>
        </table>
    `;
    
    // Kung may Proof of Payment
    if(data.proof_image) {
        content += `<div class="mt-3"><strong>Proof of Payment:</strong><br>
                    <img src="../../uploads/payments/${data.proof_image}" class="img-fluid mt-2" style="max-height:300px"></div>`;
    }

    document.getElementById('viewBody').innerHTML = content;

    // 2. LOGIC PARA SA PRINT BUTTON (ITO ANG MAHALAGA)
    let printBtn = document.getElementById('printLink');
    let baseUrl = 'pdf_files/';
    
    // Itama ang filename base sa document type
    if (data.document_type === 'Barangay Clearance') {
        printBtn.href = baseUrl + 'pdf_clearance.php?id=' + data.issuance_id;
        printBtn.style.display = 'inline-block';
    } else if (data.document_type === 'Certificate of Residency') {
        printBtn.href = baseUrl + 'pdf_residency.php?id=' + data.issuance_id;
        printBtn.style.display = 'inline-block';
    } else if (data.document_type === 'Certificate of Indigency') {
        printBtn.href = baseUrl + 'pdf_indigency.php?id=' + data.issuance_id;
        printBtn.style.display = 'inline-block';
    } else {
        // Hide print button kung unknown document
        printBtn.style.display = 'none';
    }

    // Show Modal
    var myModal = new bootstrap.Modal(document.getElementById('viewModal'));
    myModal.show();
}

// Function para sa Status Update (Existing logic)
function editStatus(id, currentStatus) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_status').value = currentStatus;
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

function saveStatus() {
    let id = document.getElementById('edit_id').value;
    let status = document.getElementById('edit_status').value;

    let formData = new FormData();
    formData.append('issuance_id', id);
    formData.append('status', status);

    fetch('../../backend/admin_issuance_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            location.reload();
        } else {
            alert('Error updating status');
        }
    });
}