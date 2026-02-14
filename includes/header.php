<?php
require_once '../config/database.php';
require_once __DIR__ . '/functions.php';
// Use $cart_items if provided by the caller, otherwise fetch from DB when logged in
if (isset($cart_items)) {
    $cart_items_array = $cart_items;
} elseif (function_exists('isLoggedIn') && isLoggedIn() && isset($_SESSION['user_id'])) {
    $cart_items_array = getCartItems($_SESSION['user_id']);
} else {
    $cart_items_array = [];
}
$cartCount = is_array($cart_items_array) ? count($cart_items_array) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="../index.php">
                <img src="../images/uploads/logo/logo.png" class="navbar-logo me-2" alt="Aanchol Logo">
                <span class="navbar-brand-text">আঞ্চল-Aanchol</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <?php if (isLoggedIn() && isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link text-danger" href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <?php if (isLoggedIn()): ?>
                       
                        <a href="cart.php" class="btn btn-outline-primary me-2 position-relative">
                            <i class="fas fa-shopping-cart"></i> Cart
    
                             <?php if ($cartCount > 0): ?>
                             <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                             <?php echo $cartCount; ?>
                             </span>
                             <?php endif; ?>
                        </a>                        
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= $_SESSION['name'] ?>
                            </button>
                            <ul class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="index.php">My Account</a></li>
                                    <li><a class="dropdown-item" href="order-history.php">My Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item text-danger" href="<?= isAdmin() ? 'admin/logout.php' : 'customer/logout.php' ?>">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                    <a href="<?php echo (getWhereAmI() == 'products.php'|| getWhereAmI() == 'product-details.php') 
                        ? 'login.php' : 'customer/login.php'; ?>" class="btn btn-outline-primary me-2"> Login</a>
                     <a href="<?php echo (getWhereAmI() == 'products.php'|| getWhereAmI() == 'product-details.php') 
                        ? 'register.php' : 'customer/register.php'; ?>" class="btn btn-outline-primary me-2"> Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>