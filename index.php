<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">
                <img src="images/uploads/logo/logo.png" class="navbar-logo me-2" alt="Aanchol Logo">
                <span class="navbar-brand-text">আঞ্চল-Aanchol</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button"
                            data-bs-toggle="dropdown">
                            Categories
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            $categories = getCategories();
                            foreach ($categories as $category): ?>
                                <li><a class="dropdown-item" href="customer/products.php?category=<?= $category['id'] ?>">
                                        <?= htmlspecialchars($category['name']) ?>
                                    </a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="customer/products.php">All Products</a></li>
                </ul>
                <div class="d-flex">
                    <?php if (isLoggedIn()): ?>
                        <a href="customer/cart.php" class="btn btn-outline-primary me-2">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <?php if (isLoggedIn()): ?>
                                <span class="badge bg-danger cart-count"></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?= $_SESSION['name'] ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="customer/order-history.php">My Orders</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="customer/logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="customer/login.php" class="btn btn-outline-primary me-2">Login</a>
                        <a href="customer/register.php" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="display-4 fw-bold mb-3">Handmade Fashion from Bangladesh</h1>
                    <p class="lead mb-4">Discover beautiful handmade bangles, sarees, panjabis, dresses, and bags.
                        Traditional craftsmanship with modern elegance.</p>
                    <a href="customer/products.php" class="btn btn-primary btn-lg">Shop Now</a>
                </div>
                <div class="col-md-6">
                    <img src="images/uploads/banners/banner.jpg" alt="Aanchol Products"
                        class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Featured Products</h2>
            <div class="row">
                <?php
                $products = getProducts();
                $featured = array_slice($products, 0, 4);
                foreach ($featured as $product): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card product-card h-100">
                            <img src="uploads/<?= $product['image'] ?: 'default.jpg' ?>" class="card-img-top"
                                alt="<?= htmlspecialchars($product['name']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                <p class="card-text text-muted"><?= substr($product['description'], 0, 60) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 text-primary">৳<?= number_format($product['price'], 2) ?></span>
                                    <a href="customer/product-details.php?id=<?= $product['id'] ?>"
                                        class="btn btn-sm btn-outline-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="customer/products.php" class="btn btn-primary">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Categories -->

    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold">Shop by Category</h2>
            <div class="row justify-content-center">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-2 col-6 mb-3">
                        <a href="customer/products.php?category=<?= $category['id'] ?>" class="text-decoration-none">
                            <div class="category-card text-center p-3 bg-white rounded shadow-sm">
                                <div class="category-icon mb-2">
                                    <i class="<?= categoryIcon($category['name']) ?> fa-2x text-primary"></i>
                                </div>
                                <h6 class="mb-0">
                                    <?= htmlspecialchars($category['name']) ?>
                                </h6>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h4>আঞ্চল-Aanchol</h4>
                    <p>Handmade fashion items crafted with love and tradition. Bringing Bangladeshi craftsmanship to
                        your doorstep.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="customer/products.php" class="text-white text-decoration-none">Products</a></li>
                        <li><a href="customer/order-history.php" class="text-white text-decoration-none">My Orders</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-phone"></i> +880 1849-110904</p>
                    <p><i class="fas fa-envelope"></i> aanchol20@gmail.com</p>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; 2026 আঞ্চল-Aanchol. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>

</html>