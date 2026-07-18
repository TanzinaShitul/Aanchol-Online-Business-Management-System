<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $sql = "DELETE FROM cart WHERE id = :id AND user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['remove']);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    redirect('cart.php');
}

// Update quantity
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        if ($quantity > 0) {
            $sql = "UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':id', $cart_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
        }
    }
    redirect('cart.php');
}

$cart_items = getCartItems($_SESSION['user_id']);
$cart_total = getCartTotal($_SESSION['user_id']);
$available_coupons = getAvailableCouponsForUser($_SESSION['user_id'], $cart_total);

// Total product-discount savings (original price vs effective price)
$product_savings = 0;
foreach ($cart_items as $item) {
    if (!empty($item['has_discount'])) {
        $product_savings += ($item['original_price'] - $item['price']) * $item['quantity'];
    }
}

// Shipping estimate based on the customer's saved division
$sql = "SELECT division_id FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);
$shipping_estimate = getShippingCost($current_user['division_id'] ?? null);

// Re-validate any applied coupon against the current cart total
$applied_coupon = null;
$coupon_discount = 0;
if (!empty($_SESSION['coupon_code'])) {
    $validation = validateCoupon($_SESSION['coupon_code'], $_SESSION['user_id'], $cart_total);
    if ($validation['valid']) {
        $applied_coupon = $validation['coupon'];
        $coupon_discount = $validation['discount_amount'];
    } else {
        unset($_SESSION['coupon_code']);
        $_SESSION['error'] = $validation['message'];
    }
}

$grand_total = max($cart_total + $shipping_estimate - $coupon_discount, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol -Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h1 class="mb-1">Shopping Cart</h1>
                <p class="text-muted mb-0">Review your selected items, update quantities, and continue to checkout.</p>
            </div>
            <span class="badge rounded-pill bg-primary px-3 py-2 fs-6">
                <i class="fas fa-shopping-bag me-1"></i> <?= count($cart_items) ?> item<?= count($cart_items) != 1 ? 's' : '' ?>
            </span>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        
        <?php if (count($cart_items) > 0): ?>
        <div class="row">
            <div class="col-lg-8">
                <form method="POST" id="cart-form">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0">Cart Items</h5>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item row align-items-center mb-3 pb-3 border-bottom g-3">
                                <div class="col-md-2 col-4">
                                    <img src="../uploads/<?= $item['image'] ?: 'default.jpg' ?>" 
                                         class="img-fluid rounded-3 shadow-sm" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         style="height: 90px; width: 100%; object-fit: cover;">
                                </div>
                                <div class="col-md-4 col-8">
                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                    <?php if (!empty($item['has_discount'])): ?>
                                        <p class="mb-1">
                                            <span class="text-muted text-decoration-line-through small">৳<?= number_format($item['original_price'], 2) ?></span>
                                            <span class="text-danger fw-bold">৳<?= number_format($item['price'], 2) ?></span>
                                            <span class="badge bg-danger">-<?= rtrim(rtrim(number_format($item['discount_percent_applied'], 2), '0'), '.') ?>%</span>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-muted mb-1">৳<?= number_format($item['price'], 2) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($item['size'])): ?>
                                        <small class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['quantity'] > $item['stock']): ?>
                                        <small class="text-danger">Only <?= $item['stock'] ?> available!</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small text-muted mb-1">Quantity</label>
                                    <input type="number" 
                                           name="quantities[<?= $item['id'] ?>]" 
                                           class="form-control rounded-pill" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock'] ?>">
                                </div>
                                <div class="col-md-2 col-4 text-md-end">
                                    <h6 class="text-primary mb-0">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></h6>
                                </div>
                                <div class="col-md-1 col-2 text-end">
                                    <a href="?remove=<?= $item['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger rounded-circle" 
                                       onclick="return confirm('Remove this item?')" title="Remove item">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" name="update_cart" class="btn btn-outline-primary rounded-pill px-3">
                                    <i class="fas fa-sync-alt"></i> Update Cart
                                </button>
                                <a href="products.php" class="btn btn-outline-success rounded-pill px-3">
                                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                                </a>
                                <a href="wishlist.php" class="btn btn-outline-danger rounded-pill px-3">
                                    <i class="fas fa-heart"></i> View Wishlist
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-white border-0 pt-3 pb-2">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="bg-light rounded-4 p-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <strong>৳<?= number_format($cart_total, 2) ?></strong>
                            </div>
                            <?php if ($product_savings > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-danger">
                                <span>Discount savings</span>
                                <strong>-৳<?= number_format($product_savings, 2) ?></strong>
                            </div>
                            <?php endif; ?>
                            <?php if ($applied_coupon): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Coupon <?= htmlspecialchars($applied_coupon['code']) ?></span>
                                <strong>-৳<?= number_format($coupon_discount, 2) ?></strong>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Shipping</span>
                                <strong>৳<?= number_format($shipping_estimate, 2) ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">Total</span>
                                <span class="text-primary fw-bold fs-5">৳<?= number_format($grand_total, 2) ?></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-muted mb-2">
                                <i class="fas fa-tags"></i> All coupons (one at a time)
                            </div>
                            <?php if (!empty($available_coupons)): ?>
                                <div class="overflow-auto mb-3">
                                    <div class="d-flex flex-nowrap gap-2 pb-1" style="min-width: max-content;">
                                        <?php foreach ($available_coupons as $coupon): ?>
                                            <button type="button"
                                                    class="btn btn-sm rounded-pill border <?= $coupon['is_applicable'] ? 'border-primary text-primary coupon-chip' : 'border-secondary text-secondary opacity-75' ?>"
                                                    data-code="<?= htmlspecialchars($coupon['code']) ?>"
                                                    title="<?= htmlspecialchars($coupon['is_applicable'] ? 'Apply ' . $coupon['code'] : $coupon['reason']) ?>"
                                                    <?= $coupon['is_applicable'] ? '' : 'disabled' ?>
                                                    style="white-space: nowrap;">
                                                <span class="fw-semibold me-1"><?= htmlspecialchars($coupon['code']) ?></span>
                                                <small>
                                                    <?php
                                                    $coupon_label = $coupon['discount_type'] === 'percentage'
                                                        ? '-' . rtrim(rtrim(number_format($coupon['discount_value'], 2), '0'), '.') . '%'
                                                        : '৳' . number_format($coupon['discount_value'], 2);
                                                    echo htmlspecialchars($coupon_label);
                                                    ?>
                                                </small>
                                                <span class="ms-2 small text-muted">Min <?= number_format((float)$coupon['min_order_amount'], 2) ?>৳</span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-light border small mb-3">No coupons are available in the system.</div>
                            <?php endif; ?>

                            <label class="form-label">Coupon code</label>
                            <form method="POST" action="apply-coupon.php" class="d-flex gap-2">
                                <input type="text" name="coupon_code" class="form-control text-uppercase rounded-pill"
                                       placeholder="e.g. WELCOME10"
                                       value="<?= htmlspecialchars($_SESSION['coupon_code'] ?? '') ?>">
                                <button type="submit" class="btn btn-outline-primary rounded-pill flex-shrink-0">Apply</button>
                            </form>
                            <div class="small text-muted mt-2">Only one coupon can be used per order.</div>
                            <?php if ($applied_coupon): ?>
                                <div class="mt-2 d-flex justify-content-between align-items-center">
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($applied_coupon['code']) ?> applied
                                    </span>
                                    <a href="remove-coupon.php" class="small text-danger text-decoration-none">
                                        <i class="fas fa-times"></i> Remove
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="checkout.php" class="btn btn-primary btn-lg rounded-pill">
                                <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                            </a>
                        </div>
                        
                        <div class="mt-4 small text-muted">
                            <div class="fw-semibold text-dark mb-2">Delivery Information</div>
                            <ul class="mb-0 ps-3">
                                <li>Cash on Delivery, bKash, Nagad, Rocket & Card available</li>
                                <li>Delivery within 3-5 business days</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-5">
            <div class="display-1 text-muted">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <h3 class="mt-3">Your cart is empty</h3>
            <p class="text-muted">Add some products to your cart first.</p>
            <a href="products.php" class="btn btn-primary btn-lg">Browse Products</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.coupon-chip').forEach(function (button) {
            button.addEventListener('click', function () {
                const input = document.querySelector('input[name="coupon_code"]');
                if (input) {
                    input.value = this.dataset.code;
                    input.focus();
                }
            });
        });
    </script>
</body>
</html>
