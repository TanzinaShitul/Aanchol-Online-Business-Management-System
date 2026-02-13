<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('order-history.php');
}

$order_id = $_GET['id'];

// Get order details
$sql = "SELECT o.*, u.name as customer_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = :id AND o.user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $order_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('order-history.php');
}

// Get order items
$sql = "SELECT oi.*, p.name as product_name, p.image 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = :order_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Order #<?= $order['order_number'] ?> - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="order-history.php">Order History</a></li>
                <li class="breadcrumb-item active">Order #<?= $order['order_number'] ?></li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Order Details</h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5>Order Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Order Number:</th>
                                <td><?= $order['order_number'] ?></td>
                            </tr>
                            <tr>
                                <th>Order Date:</th>
                                <td><?= date('F d, Y h:i A', strtotime($order['order_date'])) ?></td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td><?= $order['payment_method'] ?></td>
                            </tr>
                            <tr>
                                <th>Status:</th>
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
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Customer Information</h5>
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?= htmlspecialchars($order['email']) ?></td>
                            </tr>
                            <tr>
                                <th>Phone:</th>
                                <td><?= htmlspecialchars($order['phone']) ?></td>
                            </tr>
                            <tr>
                                <th>Shipping Address:</th>
                                <td><?= nl2br(htmlspecialchars($order['detailed_address'])) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h5>Order Items</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../uploads/<?= $item['image'] ?: 'default.jpg' ?>" class="me-3"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                            <div>
                                                <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>৳<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>৳<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>৳<?= number_format($order['total_amount'] - 50, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Shipping:</strong></td>
                                <td><strong>৳50.00</strong></td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                <td><strong
                                        class="text-primary">৳<?= number_format($order['total_amount'], 2) ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Order Status Guide</h6>
                            <ul class="mb-0">
                                <li><span class="badge bg-warning">Pending</span> - Order received, awaiting
                                    confirmation</li>
                                <li><span class="badge bg-info">Confirmed</span> - Order confirmed by admin</li>
                                <li><span class="badge bg-primary">Processing</span> - Order is being prepared</li>
                                <li><span class="badge bg-secondary">Shipped</span> - Order is on the way</li>
                                <li><span class="badge bg-success">Delivered</span> - Order delivered successfully</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="order-history.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Continue Shopping
                    </a>
                    <a href="download-voucher.php?id=<?= $order['id'] ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Voucher
                    </a>

                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>