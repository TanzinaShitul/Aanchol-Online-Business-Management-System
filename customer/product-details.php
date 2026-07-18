<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = $_GET['id'];
$product = getProductById($product_id);
$product_image_url = getProductImageUrl($product['image'] ?? null);

if (!$product) {
    redirect('products.php');
}

$pricing = getEffectivePrice($product);
$rating_summary = getProductRatingSummary($product_id);
$reviews = getProductReviews($product_id);
$in_wishlist = isLoggedIn() ? isInWishlist($_SESSION['user_id'], $product_id) : false;
$can_review = isLoggedIn() ? canReviewProduct($_SESSION['user_id'], $product_id) : false;

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

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    if (!$can_review) {
        $review_error = "You can only review products you've purchased and received.";
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $review_error = "Please select a rating.";
        } elseif (addReview($product_id, $_SESSION['user_id'], $rating, $comment)) {
            $review_success = "Thank you for your review!";
            $can_review = false;
            $rating_summary = getProductRatingSummary($product_id);
            $reviews = getProductReviews($product_id);
        } else {
            $review_error = "Failed to submit review. Please try again.";
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
                <div class="card position-relative">
                    <?php if (isLoggedIn()): ?>
                    <form method="POST" action="toggle-wishlist.php" class="position-absolute" style="top:12px; right:12px; z-index:2;">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="redirect" value="product-details.php?id=<?= $product['id'] ?>">
                        <button type="submit" class="btn btn-light rounded-circle shadow-sm" title="<?= $in_wishlist ? 'Remove from wishlist' : 'Add to wishlist' ?>">
                            <i class="<?= $in_wishlist ? 'fas' : 'far' ?> fa-heart" style="color: var(--color-pink, #B23A62);"></i>
                        </button>
                    </form>
                    <?php endif; ?>
                    <?php if ($pricing['has_discount']): ?>
                        <span class="badge bg-danger position-absolute" style="top:12px; left:12px; z-index:2; font-size:0.9rem;">
                            -<?= rtrim(rtrim(number_format($pricing['percent'], 2), '0'), '.') ?>% OFF
                        </span>
                    <?php endif; ?>
                    <img src="<?= htmlspecialchars($product_image_url) ?>" 
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

                        <?php if ($rating_summary['count'] > 0): ?>
                            <div class="mt-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= round($rating_summary['average']) ? 'fas' : 'far' ?> fa-star text-warning"></i>
                                <?php endfor; ?>
                                <span class="text-muted ms-1"><?= $rating_summary['average'] ?> (<?= $rating_summary['count'] ?> review<?= $rating_summary['count'] != 1 ? 's' : '' ?>)</span>
                            </div>
                        <?php else: ?>
                            <div class="mt-2 text-muted small">No reviews yet</div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <?php if ($pricing['has_discount']): ?>
                                <span class="text-muted text-decoration-line-through">৳<?= number_format($pricing['original_price'], 2) ?></span>
                                <h2 class="text-primary d-inline ms-2">৳<?= number_format($pricing['final_price'], 2) ?></h2>
                            <?php else: ?>
                                <h2 class="text-primary">৳<?= number_format($pricing['final_price'], 2) ?></h2>
                            <?php endif; ?>
                            
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews & Ratings -->
        <div class="mt-5">
            <div class="row">
                <div class="col-lg-8">
                    <h3>Customer Reviews</h3>

                    <?php if (isset($review_success)): ?>
                        <div class="alert alert-success"><?= $review_success ?></div>
                    <?php endif; ?>
                    <?php if (isset($review_error)): ?>
                        <div class="alert alert-danger"><?= $review_error ?></div>
                    <?php endif; ?>

                    <?php if (count($reviews) == 0): ?>
                        <p class="text-muted">No reviews yet. Be the first to share your experience!</p>
                    <?php endif; ?>

                    <?php foreach ($reviews as $review): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($review['customer_name']) ?></strong>
                                <small class="text-muted"><?= date('d M, Y', strtotime($review['created_at'])) ?></small>
                            </div>
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star text-warning small"></i>
                                <?php endfor; ?>
                                <?php if ($review['order_id']): ?>
                                    <span class="badge bg-success ms-1">Verified Purchase</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($review['comment'])): ?>
                                <p class="mb-0 mt-1"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="col-lg-4">
                    <?php if (isLoggedIn() && $can_review): ?>
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Write a Review</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Your Rating *</label>
                                        <select name="rating" class="form-select" required>
                                            <option value="">Select rating</option>
                                            <option value="5">★★★★★ Excellent</option>
                                            <option value="4">★★★★ Good</option>
                                            <option value="3">★★★ Average</option>
                                            <option value="2">★★ Poor</option>
                                            <option value="1">★ Very Poor</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Your Review</label>
                                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience with this product..."></textarea>
                                    </div>
                                    <button type="submit" name="submit_review" class="btn btn-primary w-100">Submit Review</button>
                                </form>
                            </div>
                        </div>
                    <?php elseif (isLoggedIn()): ?>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> You can review this product once you've received a delivered order containing it, or you've already reviewed it.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info small">
                            <a href="login.php">Log in</a> to write a review.
                        </div>
                    <?php endif; ?>
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
                
                foreach ($related_products as $related):
                    $related_pricing = getEffectivePrice($related);
                ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card product-card h-100">
                        <img src="<?= htmlspecialchars(getProductImageUrl($related['image'] ?? null)) ?>"
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($related['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($related['name']) ?></h6>
                            <p class="card-text text-primary">
                                <?php if ($related_pricing['has_discount']): ?>
                                    <span class="text-muted text-decoration-line-through small">৳<?= number_format($related_pricing['original_price'], 2) ?></span>
                                <?php endif; ?>
                                ৳<?= number_format($related_pricing['final_price'], 2) ?>
                            </p>
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
