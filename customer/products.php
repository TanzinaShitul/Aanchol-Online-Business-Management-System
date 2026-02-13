<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$category_id = $_GET['category'] ?? null;
$search = $_GET['search'] ?? null;

// Get products based on filter
if ($search) {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' AND p.name LIKE :search 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
} elseif ($category_id) {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' AND p.category_id = :category_id 
            ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category_id', $category_id);
} else {
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" placeholder="Search products..." value="<?= htmlspecialchars($search ?? '') ?>">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <select name="category" class="form-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="row">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100">
                        <img src="../uploads/<?= $product['image'] ?: 'default.jpg' ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <span class="badge bg-secondary mb-2"><?= $product['category_name'] ?></span>
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <?= substr($product['description'], 0, 80) ?>
                                <?= strlen($product['description']) > 80 ? '...' : '' ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="h5 text-primary">৳<?= number_format($product['price'], 2) ?></span>
                                    <?php if ($product['stock'] < 5): ?>
                                        <small class="text-danger d-block">Only <?= $product['stock'] ?> left!</small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($product['stock'] > 0): ?>
                                        <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <form method="POST" action="add-to-cart.php" class="d-inline">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h4>No products found!</h4>
                        <p>Try different search or category.</p>
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination (if needed) -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>