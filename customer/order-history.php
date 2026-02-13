<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get user orders
$sql = "SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Order History - Aanchol</title>
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
                        <li class="list-group-item active"><a href="order-history.php" class="text-decoration-none text-white">My Orders</a></li>
                        <li class="list-group-item"><a href="cart.php" class="text-decoration-none">Shopping Cart</a></li>
                        <li class="list-group-item"><a href="logout.php" class="text-decoration-none text-danger">Logout</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Order History</h4>
                    </div>
                    <div class="card-body">
                        <?php if (count($orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order No</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): 
                                            // Count items in order
                                            $sql = "SELECT COUNT(*) as item_count FROM order_items WHERE order_id = :order_id";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bindParam(':order_id', $order['id']);
                                            $stmt->execute();
                                            $item_count = $stmt->fetch(PDO::FETCH_ASSOC)['item_count'];
                                        ?>
                                        <tr>
                                            <td><?= $order['order_number'] ?></td>
                                            <td><?= date('d M, Y', strtotime($order['order_date'])) ?></td>
                                            <td><?= $item_count ?> item(s)</td>
                                            <td>৳<?= number_format($order['total_amount'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $order['status'] == 'pending' ? 'warning' : 
                                                    ($order['status'] == 'confirmed' ? 'info' : 
                                                    ($order['status'] == 'processing' ? 'primary' : 
                                                    ($order['status'] == 'shipped' ? 'secondary' : 
                                                    ($order['status'] == 'delivered' ? 'success' : 'danger')))) 
                                                ?>">
                                                    <?= ucfirst($order['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order-details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="display-1 text-muted">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h3 class="mt-3">No orders yet</h3>
                                <p class="text-muted">You haven't placed any orders yet.</p>
                                <a href="products.php" class="btn btn-primary">Start Shopping</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>