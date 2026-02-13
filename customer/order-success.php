<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['order_success'])) {
    redirect('order-history.php');
}

$order_number = $_SESSION['order_success'];
unset($_SESSION['order_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Order Successful - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-check-circle"></i> Order Successful!</h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="display-1 text-success mb-4">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2 class="mb-3">Thank You for Your Order!</h2>
                        <p class="lead">Your order has been placed successfully.</p>
                        
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5>Order Number: <span class="text-primary"><?= $order_number ?></span></h5>
                                <p class="mb-0">We have sent an order confirmation to your email.</p>
                            </div>
                        </div>
                        
                        <div class="row text-start mb-4">
                            <div class="col-md-6">
                                <h5>What happens next?</h5>
                                <ul>
                                    <li>You will receive an order confirmation email</li>
                                    <li>We will process your order within 24 hours</li>
                                    <li>You will receive updates on your order status</li>
                                    <li>Delivery within 3-5 business days</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Need Help?</h5>
                                <ul>
                                    <li>Contact: +880 1XXX-XXXXXX</li>
                                    <li>Email: support@aanchol.com</li>
                                    <li>Check <a href="order-history.php">Order History</a> for updates</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-center">
                            <a href="order-history.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-box"></i> View My Orders
                            </a>
                            <a href="products.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>