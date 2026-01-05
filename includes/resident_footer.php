<footer class="footer py-3 bg-white border-top">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between small">
            <div class="text-muted">Copyright &copy; Barangay Langkaan II Management System 2025</div>
            <div>
                <a href="#" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                <a href="#" class="text-decoration-none text-muted">Terms &amp; Conditions</a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Hanapin ang mga elements
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('main-content');
        
        // Gumawa ng Overlay element at i-append sa body
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        // Function para i-toggle ang sidebar
        window.toggleSidebar = function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        };

        // Kapag kinlick ang overlay, isara ang sidebar
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });
    });
</script>