<?php if (!isAdmin()) {
    header("Location: login.php");
    exit();
} ?>
<!-- Mobile sidebar toggle button (hidden on desktop via CSS) -->
<button type="button" id="adminSidebarToggle" aria-label="Toggle menu" aria-expanded="false" aria-controls="adminSidebar">
    <i class="fas fa-bars"></i>
</button>

<!-- Backdrop shown behind the sidebar on mobile when open -->
<div class="admin-sidebar-backdrop" id="adminSidebarBackdrop"></div>

<!-- Sidebar -->
<nav class="col-md-2 d-md-block bg-dark sidebar vh-100" id="adminSidebar">
    <div class="position-sticky pt-3">
         <a class="navbar-brand fw-bold text-primary" href="dashboard.php">
                <img src="../images/uploads/logo/logo.png" class="navbar-logo me-2" alt="Aanchol Logo">
                <span class="navbar-brand-text">আঞ্চল-Aanchol</span>
         </a>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'add-product.php' || basename($_SERVER['PHP_SELF']) == 'edit-product.php' ? 'active' : '' ?>" href="products.php">
                    <i class="fas fa-box"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'orders.php' || basename($_SERVER['PHP_SELF']) == 'order-details.php' ? 'active' : '' ?>" href="orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= in_array(basename($_SERVER['PHP_SELF']), ['coupons.php', 'add-coupon.php', 'edit-coupon.php']) ? 'active' : '' ?>" href="coupons.php">
                    <i class="fas fa-tags"></i> Coupons
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>" href="reviews.php">
                    <i class="fas fa-star"></i> Reviews
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
            <li class="nav-item mt-5">
                <a class="nav-link text-white" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<script>
(function () {
    var toggleBtn = document.getElementById('adminSidebarToggle');
    var sidebar = document.getElementById('adminSidebar');
    var backdrop = document.getElementById('adminSidebarBackdrop');
    if (!toggleBtn || !sidebar || !backdrop) return;

    function closeSidebar() {
        sidebar.classList.remove('sidebar-open');
        document.body.classList.remove('admin-sidebar-open');
        toggleBtn.setAttribute('aria-expanded', 'false');
    }

    function openSidebar() {
        sidebar.classList.add('sidebar-open');
        document.body.classList.add('admin-sidebar-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
    }

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
    });

    backdrop.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
        link.addEventListener('click', closeSidebar);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 768) closeSidebar();
    });
})();
</script>
