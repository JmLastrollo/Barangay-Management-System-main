<?php
session_start();
// Hindi na kailangan ng PHP fetch dito dahil JavaScript na ang bahala mag-load ng data.
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>BMS - Announcements</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="assets/img/BMS.png">
    <link rel="stylesheet" href="css/style.css?v=4" /> </head>
<body>

<?php include 'includes/nav.php'; ?>

<section class="header-banner">
    <img src="assets/img/dasma logo-modified.png" class="banner-logo" alt="left logo">
    <div class="header-text">
        <h1>Barangay</h1> 
        <h3>ANNOUNCEMENTS</h3>
    </div>
    <img src="assets/img/Langkaan 2 Logo-modified.png" class="banner-logo" alt="right logo">
</section>

<section class="bg-light main-content-section">
    <div class="container">
        
        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-8">
                    <div class="input-group h-100"> 
                        <span class="input-group-text"><i class="bi bi-search text-primary"></i></span>
                        <input type="text" id="searchInput" class="form-control" placeholder="Search event (e.g. Basketball, Medical Mission)...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-select-wrapper h-100"> 
                        <select id="sortSelect" class="form-select h-100"> 
                            <option value="newest">Sort by: Newest First</option>
                            <option value="oldest">Sort by: Oldest First</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4" id="announcement-container">
            </div>

        <div class="ajax-loader" id="loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted small mt-2">Loading announcements...</p>
        </div>
        
        <div id="end-message" class="text-center mt-4 text-muted" style="display: none;">
            <small>No more announcements found.</small>
        </div>
    </div>
</section>

<button type="button" class="btn" id="backToTop" title="Go to top">
    <i class="bi bi-arrow-up"></i>
</button>

<?php include('includes/footer.php'); ?>

<script src="assets/js/bootstrap.bundle.min.js"></script>

<script>
    // --- VARIABLES ---
    let page = 1;
    let isLoading = false;
    let hasMore = true;
    let searchTimer; // For debounce delay

    // --- ELEMENTS ---
    const container = document.getElementById('announcement-container');
    const loader = document.getElementById('loader');
    const endMessage = document.getElementById('end-message');
    const searchInput = document.getElementById('searchInput');
    const sortSelect = document.getElementById('sortSelect');
    const backToTopBtn = document.getElementById("backToTop");

    // --- INITIAL LOAD ---
    loadAnnouncements(true);

    // --- EVENT LISTENERS ---

    // 1. Infinite Scroll
    window.addEventListener('scroll', () => {
        // Load more when scrolled near bottom
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 300) {
            loadAnnouncements(false);
        }
        // Show/Hide Back to Top Button
        scrollFunction();
    });

    // 2. Search Input (with 500ms delay)
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            loadAnnouncements(true); // Reset list and load new results
        }, 500);
    });

    // 3. Sort Change
    sortSelect.addEventListener('change', () => {
        loadAnnouncements(true); // Reset list and load sorted results
    });

    // 4. Back to Top Click
    backToTopBtn.addEventListener("click", function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // --- FUNCTIONS ---

    // Show/Hide Back to Top Button
    function scrollFunction() {
        if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
            backToTopBtn.style.display = "block";
        } else {
            backToTopBtn.style.display = "none";
        }
    }

    // Main Function to Fetch Data
    function loadAnnouncements(reset = false) {
        if (isLoading) return;
        
        // If resetting (Search/Sort), clear everything first
        if (reset) {
            page = 1;
            hasMore = true;
            container.innerHTML = ""; 
            endMessage.style.display = 'none';
        }

        if (!hasMore) return;

        isLoading = true;
        loader.style.display = 'block';

        // Prepare Data
        const formData = new FormData();
        formData.append('page', page);
        formData.append('search', searchInput.value); 
        formData.append('sort', sortSelect.value);    

        // Fetch from Backend
        fetch('backend/fetch_announcements_scroll.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "") {
                hasMore = false;
                endMessage.style.display = 'block';
                
                // Customize message based on context
                if(page === 1) {
                    endMessage.innerHTML = "<small>No announcements found matching your search.</small>";
                } else {
                    endMessage.innerHTML = "<small>You've reached the end of the list.</small>";
                }
            } else {
                container.insertAdjacentHTML('beforeend', data);
                page++;
            }
            loader.style.display = 'none';
            isLoading = false;
        })
        .catch(err => {
            console.error('Error fetching data:', err);
            loader.style.display = 'none';
            isLoading = false;
        });
    }
</script>

</body>
</html>