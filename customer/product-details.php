<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = $_GET['id'];
$product = getProductById($product_id);

if (!$product) {
    redirect('products.php');
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_to'] = "product-details.php?id=$product_id";
        redirect('login.php');
    }
    
    $quantity = $_POST['quantity'] ?? 1;
    $size = isset($_POST['size']) ? trim($_POST['size']) : null;

    // Category-specific size rules
    $cat = strtolower($product['category_name'] ?? '');
    $bangleSizes = ['22','24','26'];
    $clothingSizes = ['M','L','XL','XXL'];

    if (in_array($cat, ['bangles'])) {
        if (empty($size) || !in_array($size, $bangleSizes)) {
            $error = "Please select a valid bangle size.";
        }
    } elseif (in_array($cat, ['dress','panjabi'])) {
        if (empty($size) || !in_array(strtoupper($size), $clothingSizes)) {
            $error = "Please select a valid size (M, L, XL, XXL).";
        } else {
            $size = strtoupper($size);
        }
    } else {
        // Bags and Sarees - no size
        $size = null;
    }

    if (!isset($error)) {
        if (addToCart($_SESSION['user_id'], $product_id, $quantity, $size)) {
            $success = "Product added to cart successfully!";
        } else {
            $error = "Failed to add product to cart!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title><?= htmlspecialchars($product['name']) ?> - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4 mb-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?= $product['category_id'] ?>"><?= $product['category_name'] ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="../uploads/<?= $product['image'] ?: 'default.jpg' ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         style="max-height: 500px; object-fit: contain;">
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title"><?= htmlspecialchars($product['name']) ?></h1>
                        <span class="badge bg-primary"><?= $product['category_name'] ?></span>
                        
                        <div class="mt-3">
                            <h2 class="text-primary">৳<?= number_format($product['price'], 2) ?></h2>
                            
                            <div class="mt-3">
                                <h5>Stock Status:</h5>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="badge bg-success">In Stock (<?= $product['stock'] ?> available)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-4">
                                <h5>Description:</h5>
                                <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                            </div>
                            
                            <?php if ($product['stock'] > 0): ?>
                            <form method="POST" class="mt-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-3">
                                        <label for="quantity" class="form-label">Quantity:</label>
                                        <input type="number" 
                                               id="quantity" 
                                               name="quantity" 
                                               class="form-control" 
                                               value="1" 
                                               min="1" 
                                               max="<?= $product['stock'] ?>">
                                    </div>
                                    <?php
                                    $cat = strtolower($product['category_name'] ?? '');
                                    if (in_array($cat, ['bangles'])): ?>
                                    <div class="col-md-3 mb-3">
                                        <label for="size" class="form-label">Size:</label>
                                        <select name="size" id="size" class="form-select">
                                            <option value="">Select size</option>
                                            <option value="22">22</option>
                                            <option value="24">24</option>
                                            <option value="26">26</option>
                                        </select>
                                    </div>
                                    <?php elseif (in_array($cat, ['dress','panjabi'])): ?>
                                    <div class="col-md-3 mb-3">
                                        <label for="size" class="form-label">Size:</label>
                                        <select name="size" id="size" class="form-select">
                                            <option value="">Select size</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                            <option value="XXL">XXL</option>
                                        </select>
                                    </div>
                                    <?php else: ?>
                                        <input type="hidden" name="size" value="">
                                    <?php endif; ?>

                                    <div class="col-md-9 mb-3">
                                        <button type="submit" 
                                                name="add_to_cart" 
                                                class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="mt-5">
            <h3>Related Products</h3>
            <div class="row">
                <?php
                $sql = "SELECT * FROM products 
                        WHERE category_id = :category_id 
                        AND id != :product_id 
                        AND status = 'active' 
                        LIMIT 4";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':category_id', $product['category_id']);
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
                $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($related_products as $related): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <img src="../uploads/<?= $related['image'] ?: 'default.jpg' ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($related['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($related['name']) ?></h6>
                            <p class="card-text text-primary">৳<?= number_format($related['price'], 2) ?></p>
                            <a href="product-details.php?id=<?= $related['id'] ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>