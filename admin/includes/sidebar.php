<?php if (!isAdmin()) {
    header("Location: login.php");
    exit();
} ?>
<!-- Sidebar -->
<nav class="col-md-2 d-md-block bg-dark sidebar vh-100">
    <div class="position-sticky pt-3">
        <!-- <h4 class="text-white text-center mb-4">আঞ্চল-Aanchol Admin</h4> -->
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