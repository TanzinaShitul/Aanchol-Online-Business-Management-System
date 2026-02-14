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
        <h1 class="mb-4">Shopping Cart</h1>
        
        <?php if (count($cart_items) > 0): ?>
        <form method="POST">
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Cart Items (<?= count($cart_items) ?>)</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-md-2">
                                    <img src="../uploads/<?= $item['image'] ?: 'default.jpg' ?>" 
                                         class="img-fluid" 
                                         alt="<?= htmlspecialchars($item['name']) ?>"
                                         style="height: 80px; object-fit: cover;">
                                </div>
                                <div class="col-md-4">
                                    <h6><?= htmlspecialchars($item['name']) ?></h6>
                                    <p class="text-muted mb-0">৳<?= number_format($item['price'], 2) ?></p>
                                    <?php if (!empty($item['size'])): ?>
                                        <small class="text-muted">Size: <?= htmlspecialchars($item['size']) ?></small><br>
                                    <?php endif; ?>
                                    <?php if ($item['quantity'] > $item['stock']): ?>
                                        <small class="text-danger">Only <?= $item['stock'] ?> available!</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-3">
                                    <input type="number" 
                                           name="quantities[<?= $item['id'] ?>]" 
                                           class="form-control" 
                                           value="<?= $item['quantity'] ?>" 
                                           min="1" 
                                           max="<?= $item['stock'] ?>">
                                </div>
                                <div class="col-md-2">
                                    <h6 class="text-primary">৳<?= number_format($item['price'] * $item['quantity'], 2) ?></h6>
                                </div>
                                <div class="col-md-1">
                                    <a href="?remove=<?= $item['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Remove this item?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" name="update_cart" class="btn btn-outline-primary">
                                        <i class="fas fa-sync-alt"></i> Update Cart
                                    </button>
                                    <a href="products.php" class="btn btn-outline-success">
                                        <i class="fas fa-shopping-bag"></i> Continue Shopping
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <td>Subtotal</td>
                                    <td class="text-end">৳<?= number_format($cart_total, 2) ?></td>
                                </tr>
                                <tr>
                                    <td>Shipping</td>
                                    <td class="text-end">৳50.00</td>
                                </tr>
                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td class="text-end text-primary">৳<?= number_format($cart_total + 50, 2) ?></td>
                                </tr>
                            </table>
                            
                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-shopping-cart"></i> Proceed to Checkout
                                </a>
                            </div>
                            
                            <div class="mt-4">
                                <h6>Delivery Information:</h6>
                                <ul class="small">
                                    <li>Cash on Delivery available</li>
                                    <li>Delivery within 3-5 business days</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
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
</body>
</html>