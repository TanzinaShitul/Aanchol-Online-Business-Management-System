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

$tracking_events = getTrackingEvents($order_id);
$status_steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
$can_download_invoice = ($order['payment_method'] !== 'Cash on Delivery' && $order['payment_status'] === 'paid');
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

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (!empty($order['payment_method']) && $order['payment_method'] !== 'Cash on Delivery' && $order['payment_status'] !== 'paid'): ?>
            <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span>
                    <i class="fas fa-exclamation-triangle"></i>
                    Payment for this order is <strong><?= htmlspecialchars($order['payment_status']) ?></strong>.
                </span>
                <a href="retry-payment.php?order=<?= urlencode($order['order_number']) ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-credit-card"></i> Retry Payment
                </a>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
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
                                <td><?= htmlspecialchars($order['payment_method']) ?></td>
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    <span class="badge bg-<?= $order['payment_status'] == 'paid' ? 'success' : ($order['payment_status'] == 'failed' ? 'danger' : 'secondary') ?>">
                                        <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php if (!empty($order['payment_reference'])): ?>
                            <tr>
                                <th>Payment Reference:</th>
                                <td><?= htmlspecialchars($order['payment_reference']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($order['coupon_code'])): ?>
                            <tr>
                                <th>Coupon Used:</th>
                                <td><span class="badge bg-success"><?= htmlspecialchars($order['coupon_code']) ?></span></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($order['courier_name'])): ?>
                            <tr>
                                <th>Courier:</th>
                                <td><?= htmlspecialchars($order['courier_name']) ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if (!empty($order['tracking_number'])): ?>
                            <tr>
                                <th>Tracking Number:</th>
                                <td><?= htmlspecialchars($order['tracking_number']) ?></td>
                            </tr>
                            <?php endif; ?>
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
                                <th>Size</th>
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
                                    <td><?= !empty($item['size']) ? htmlspecialchars($item['size']) : '-' ?></td>
                                    <td>৳<?= number_format($item['price'], 2) ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td>৳<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <?php
                            $subtotal = 0;
                            foreach ($order_items as $item) {
                                $subtotal += $item['price'] * $item['quantity'];
                            }
                            $discount_amount = (float)($order['discount_amount'] ?? 0);
                            $shipping = $order['total_amount'] - $subtotal + $discount_amount;
                            ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>৳<?= number_format($subtotal, 2) ?></strong></td>
                            </tr>
                            <?php if ($discount_amount > 0): ?>
                            <tr class="text-success">
                                <td colspan="4" class="text-end"><strong>Coupon (<?= htmlspecialchars($order['coupon_code']) ?>):</strong></td>
                                <td><strong>&minus;৳<?= number_format($discount_amount, 2) ?></strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Shipping:</strong></td>
                                <td><strong>৳<?= number_format($shipping, 2) ?></strong></td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td><strong
                                        class="text-primary">৳<?= number_format($order['total_amount'], 2) ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Delivery Tracking -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-truck"></i> Delivery Tracking</h5>
            </div>
            <div class="card-body">
                <?php if ($order['status'] === 'cancelled'): ?>
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-times-circle"></i> This order has been cancelled.
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between text-center mb-4">
                        <?php $current_index = array_search($order['status'], $status_steps); ?>
                        <?php foreach ($status_steps as $i => $step): ?>
                            <div class="flex-fill">
                                <div class="mx-auto d-flex align-items-center justify-content-center rounded-circle"
                                     style="width:36px;height:36px;background-color: <?= $i <= $current_index ? 'var(--color-indigo, #1E3A5F)' : '#dee2e6' ?>; color:#fff;">
                                    <i class="fas fa-check"></i>
                                </div>
                                <small class="d-block mt-1 text-capitalize"><?= $step ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (count($tracking_events) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach (array_reverse($tracking_events) as $event): ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between">
                                    <strong class="text-capitalize"><?= htmlspecialchars($event['status']) ?></strong>
                                    <small class="text-muted"><?= date('d M, Y h:i A', strtotime($event['created_at'])) ?></small>
                                </div>
                                <?php if (!empty($event['location'])): ?>
                                    <div class="small text-muted"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($event['note'])): ?>
                                    <div class="small"><?= htmlspecialchars($event['note']) ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="alert alert-info mb-3">
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

                <div class="text-center">
                    <a href="order-history.php" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Continue Shopping
                    </a>
                    <?php if ($can_download_invoice): ?>
                        <a href="download-voucher.php?id=<?= $order['id'] ?>" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Invoice
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary" disabled>
                            <i class="fas fa-download"></i> Invoice unavailable
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
