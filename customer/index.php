<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    redirect('../admin/dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">My Account</h5>
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="list-group-item"><a href="order-history.php" class="text-decoration-none">My Orders</a></li>
                        <li class="list-group-item"><a href="cart.php" class="text-decoration-none">Shopping Cart</a></li>
                        <li class="list-group-item"><a href="logout.php" class="text-decoration-none text-danger">Logout</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Welcome, <?= $_SESSION['name'] ?>!</h4>
                    </div>
                    <div class="card-body">
                        <p>Welcome to your Aanchol account dashboard. Here you can:</p>
                        <ul>
                            <li>View your order history</li>
                            <li>Track your orders</li>
                            <li>Manage your shopping cart</li>
                            <li>Update your account information</li>
                        </ul>
                        
                        <h5 class="mt-4">Recent Orders</h5>
                        <?php
                        $sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC LIMIT 3";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':user_id', $_SESSION['user_id']);
                        $stmt->execute();
                        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($recent_orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Order No</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><a href="order-details.php?id=<?= $order['id'] ?>"><?= $order['order_number'] ?></a></td>
                                            <td><?= date('d M, Y', strtotime($order['order_date'])) ?></td>
                                            <td>৳<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status'] == 'pending' ? 'warning' : 
                                                    ($order['status'] == 'confirmed' ? 'info' : 
                                                    ($order['status'] == 'delivered' ? 'success' : 'secondary')) 
                                                ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
                            <a href="order-history.php" class="btn btn-outline-primary">View All Orders</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>