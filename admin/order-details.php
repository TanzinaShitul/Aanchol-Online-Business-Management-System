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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];
    
    $sql = "UPDATE orders SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Order status updated successfully!";
        redirect("order-details.php?id=$order_id");
    } else {
        $error = "Failed to update order status!";
    }
}
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
                
                <div class="card">
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
                                        <td><?= $order['payment_method'] ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <form method="POST" class="d-inline-flex">
                                                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                                <select class="form-select form-select-sm me-2" name="status" onchange="this.form.submit()">
                                                    <option value="pending" <?= ($order['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                    <option value="confirmed" <?= ($order['status'] == 'confirmed') ? 'selected' : '' ?>>Confirmed</option>
                                                    <option value="processing" <?= ($order['status'] == 'processing') ? 'selected' : '' ?>>Processing</option>
                                                    <option value="shipped" <?= ($order['status'] == 'shipped') ? 'selected' : '' ?>>Shipped</option>
                                                    <option value="delivered" <?= ($order['status'] == 'delivered') ? 'selected' : '' ?>>Delivered</option>
                                                    <option value="cancelled" <?= ($order['status'] == 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                            </form>
                                        </td>
                                    </tr>
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
                                    $shipping = $order['total_amount'] - $subtotal;
                                    ?>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                        <td><strong>৳<?= number_format($subtotal, 2) ?></strong></td>
                                    </tr>
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
                        
                        <div class="mt-4">
                            <h5>Order Timeline</h5>
                            <div class="timeline">
                                <div class="timeline-item <?= ($order['status'] == 'pending') ? 'active' : 'completed' ?>">
                                    <span class="timeline-marker"></span>
                                    <div class="timeline-content">
                                        <h6>Order Placed</h6>
                                        <p class="mb-0"><?= date('d M, Y h:i A', strtotime($order['order_date'])) ?></p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= ($order['status'] == 'confirmed') ? 'active' : (in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'completed' : '') ?>">
                                    <span class="timeline-marker"></span>
                                    <div class="timeline-content">
                                        <h6>Order Confirmed</h6>
                                        <p class="mb-0">Order confirmed by admin</p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= ($order['status'] == 'processing') ? 'active' : (in_array($order['status'], ['shipped', 'delivered']) ? 'completed' : '') ?>">
                                    <span class="timeline-marker"></span>
                                    <div class="timeline-content">
                                        <h6>Processing</h6>
                                        <p class="mb-0">Order is being prepared</p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= ($order['status'] == 'shipped') ? 'active' : ($order['status'] == 'delivered' ? 'completed' : '') ?>">
                                    <span class="timeline-marker"></span>
                                    <div class="timeline-content">
                                        <h6>Shipped</h6>
                                        <p class="mb-0">Order is on the way</p>
                                    </div>
                                </div>
                                <div class="timeline-item <?= ($order['status'] == 'delivered') ? 'active' : '' ?>">
                                    <span class="timeline-marker"></span>
                                    <div class="timeline-content">
                                        <h6>Delivered</h6>
                                        <p class="mb-0">Order delivered successfully</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <style>
                            .timeline {
                                position: relative;
                                padding-left: 30px;
                            }
                            .timeline-item {
                                position: relative;
                                margin-bottom: 20px;
                            }
                            .timeline-marker {
                                position: absolute;
                                left: -30px;
                                top: 0;
                                width: 20px;
                                height: 20px;
                                border-radius: 50%;
                                background: #dee2e6;
                                border: 3px solid white;
                            }
                            .timeline-item.active .timeline-marker {
                                background: #0d6efd;
                            }
                            .timeline-item.completed .timeline-marker {
                                background: #198754;
                            }
                        </style>
                        
                        <div class="text-center mt-4">
                            <a href="orders.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Orders
                            </a>
                            <a href="download-invoice.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                                <i class="fas fa-download"></i> Download PDF Invoice
                            </a>
                        
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>