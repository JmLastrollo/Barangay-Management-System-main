<?php
session_start();
require_once 'backend/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Officials</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" type="image/png" href="assets/img/Langkaan 2 Logo-modified.png">
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/cdologo.png" class="left-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>Officials</h3>
    </div>
    <img src="assets/img/barangaygusalogo.png" class="right-logo" alt="right logo">
</section>

<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Elected <span class="text-success">Officials</span></h3>
        <div class="row g-2" id="barangay_officials">
        </div>
    </div>
</section>

<?php include('includes/footer.php'); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
fetch('backend/officials_get.php')
.then(res => res.json())
.then(data => {
    const container = document.getElementById('barangay_officials');
    container.innerHTML = '';

    if(data.length === 0){
        container.innerHTML = '<p class="text-center">No officials found.</p>';
        return;
    }

    data.forEach(official => {
    container.innerHTML += `
    <div class="col-md-3 d-flex justify-content-center">
        <div class="official-card">
            <div class="official-img-box">
                <img src="uploads/officials/${official.image}" alt="">
            </div>
            <h6 class="fw-bold official-page-title mt-3">${official.name}</h6>
            <p>${official.position}</p>
        </div>
    </div>
    `;
});

});
</script>
</body>
</html>