<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = $_GET['id'];

// Get order details
$sql = "SELECT o.*, u.name as customer_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('orders.php');
}

// Get order items
$sql = "SELECT oi.*, p.name as product_name, p.image, p.price as unit_price 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = :order_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle status + tracking update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    $courier_name = trim($_POST['courier_name'] ?? '');
    $tracking_number = trim($_POST['tracking_number'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $note = trim($_POST['note'] ?? '');

    $sql = "UPDATE orders SET status = :status, courier_name = :courier_name, tracking_number = :tracking_number WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':courier_name', $courier_name);
    $stmt->bindParam(':tracking_number', $tracking_number);
    $stmt->bindParam(':id', $order_id);
    
    if ($stmt->execute()) {
        addTrackingEvent($order_id, $status, $location ?: null, $note ?: null);
        $_SESSION['success'] = "Order status updated successfully!";
        redirect("order-details.php?id=$order_id");
    } else {
        $error = "Failed to update order status!";
    }
}

$tracking_events = getTrackingEvents($order_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Order Details</h1>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Order #<?= $order['order_number'] ?></h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Order Information</h5>
                                <table class="table table-sm table-bordered">
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
                                            <?php if (!empty($order['payment_reference'])): ?>
                                                <div class="small text-muted">Ref: <?= htmlspecialchars($order['payment_reference']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($order['coupon_code'])): ?>
                                    <tr>
                                        <th>Coupon Used:</th>
                                        <td><span class="badge bg-success"><?= htmlspecialchars($order['coupon_code']) ?></span>
                                            <span class="text-muted small">(&minus;৳<?= number_format($order['discount_amount'], 2) ?>)</span>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5>Customer Information</h5>
                                <table class="table table-sm table-bordered">
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
                                        <th>#</th>
                                        <th>Product</th>
                                        <th>Size</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $item_count = 1;
                                    foreach ($order_items as $item): 
                                    ?>
                                    <tr>
                                        <td><?= $item_count++ ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../uploads/<?= $item['image'] ?: 'default.jpg' ?>" 
                                                     class="me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                                <div>
                                                    <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= !empty($item['size']) ? htmlspecialchars($item['size']) : '-' ?></td>
                                        <td>৳<?= number_format($item['unit_price'], 2) ?></td>
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
                                        <td><strong class="text-primary">৳<?= number_format($order['total_amount'], 2) ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Update Status & Tracking -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-truck"></i> Update Status & Delivery Tracking</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="pending" <?= ($order['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= ($order['status'] == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="processing" <?= ($order['status'] == 'processing') ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= ($order['status'] == 'shipped') ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= ($order['status'] == 'delivered') ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= ($order['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Courier Name</label>
                                    <input type="text" name="courier_name" class="form-control" value="<?= htmlspecialchars($order['courier_name'] ?? '') ?>" placeholder="e.g. Pathao, Sundarban">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Tracking Number</label>
                                    <input type="text" name="tracking_number" class="form-control" value="<?= htmlspecialchars($order['tracking_number'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Current Location <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="location" class="form-control" placeholder="e.g. Dhaka Hub">
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Note for this update <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="note" class="form-control" placeholder="e.g. Package handed to courier">
                                </div>
                            </div>
                            <button type="submit" name="update_status" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </form>

                        <?php if (count($tracking_events) > 0): ?>
                            <hr>
                            <h6>Tracking History</h6>
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
                        
                <div class="text-center mt-4 mb-4">
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                    <a href="download-invoice.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download PDF Invoice
                    </a>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
