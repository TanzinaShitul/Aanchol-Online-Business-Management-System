<?php
$navBasePath = (strpos($_SERVER['PHP_SELF'], '/customer/') !== false || basename(dirname($_SERVER['PHP_SELF'])) === 'customer') ? '..' : '';
$navHomeHref = $navBasePath ? $navBasePath . '/index.php' : 'index.php';
$navProductsHref = $navBasePath ? 'products.php' : 'customer/products.php';
$navTrackOrderHref = $navBasePath ? 'track-order.php' : 'customer/track-order.php';
$navLoginHref = $navBasePath ? 'login.php' : 'customer/login.php';
$navRegisterHref = $navBasePath ? 'register.php' : 'customer/register.php';
$navCartHref = $navBasePath ? 'cart.php' : 'customer/cart.php';
$navWishlistHref = $navBasePath ? 'wishlist.php' : 'customer/wishlist.php';
$navOrderHistoryHref = $navBasePath ? 'order-history.php' : 'customer/order-history.php';
$navAccountHref = $navBasePath ? 'index.php' : 'customer/index.php';
$navLogoutHref = $navBasePath ? 'logout.php' : 'customer/logout.php';
$navLogoSrc = $navBasePath ? '../images/uploads/logo/logo.png' : 'images/uploads/logo/logo.png';

$categoryItems = function_exists('getCategories') ? getCategories() : [];
$cartCount = function_exists('isLoggedIn') && isLoggedIn() && function_exists('getCartItems') ? count(getCartItems($_SESSION['user_id'])) : 0;
$wishlistCount = function_exists('isLoggedIn') && isLoggedIn() && function_exists('getWishlistCount') ? getWishlistCount($_SESSION['user_id']) : 0;
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="<?= htmlspecialchars($navHomeHref) ?>">
            <img src="<?= htmlspecialchars($navLogoSrc) ?>" class="navbar-logo me-2" alt="Aanchol Logo">
            <span class="navbar-brand-text">আঞ্চল-Aanchol</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($navHomeHref) ?>">Home</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button"
                        data-bs-toggle="dropdown">
                        Categories
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($categoryItems as $category): ?>
                            <li>
                                <a class="dropdown-item" href="<?= htmlspecialchars($navProductsHref) ?>?category=<?= (int) $category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($navProductsHref) ?>">All Products</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= htmlspecialchars($navTrackOrderHref) ?>">Track Order</a></li>
                <?php if (function_exists('isLoggedIn') && isLoggedIn() && function_exists('isAdmin') && isAdmin()): ?>
                    <li class="nav-item"><a class="nav-link text-danger" href="admin/dashboard.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <?php if (function_exists('isLoggedIn') && isLoggedIn()): ?>
                    <a href="<?= htmlspecialchars($navWishlistHref) ?>" class="btn btn-outline-danger me-2 position-relative">
                        <i class="fas fa-heart"></i> Wishlist
                        <?php if ($wishlistCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int) $wishlistCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= htmlspecialchars($navCartHref) ?>" class="btn btn-outline-primary me-2 position-relative">
                        <i class="fas fa-shopping-cart"></i> Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= (int) $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['name'] ?? '') ?>
                        </button>
                        <ul class="dropdown-menu">
                            <?php if (function_exists('isAdmin') && isAdmin()): ?>
                                <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($navAccountHref) ?>">My Account</a></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($navOrderHistoryHref) ?>">My Orders</a></li>
                                <li><a class="dropdown-item" href="<?= htmlspecialchars($navWishlistHref) ?>">My Wishlist</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="<?= htmlspecialchars($navLogoutHref) ?>">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= htmlspecialchars($navLoginHref) ?>" class="btn btn-outline-primary me-2">Login</a>
                    <a href="<?= htmlspecialchars($navRegisterHref) ?>" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
