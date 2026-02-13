<?php
// admin/dashboard.php

// Use absolute path to include config file
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Get statistics
$sql = "SELECT 
        (SELECT COUNT(*) FROM products) as total_products,
        (SELECT COUNT(*) FROM orders WHERE MONTH(order_date) = MONTH(CURDATE())) as monthly_orders,
        (SELECT SUM(total_amount) FROM orders WHERE MONTH(order_date) = MONTH(CURDATE())) as monthly_revenue,
        (SELECT COUNT(*) FROM orders WHERE status = 'pending') as pending_orders";

$stmt = $conn->query($sql);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Low stock alert
$low_stock = getLowStockProducts(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Products</h5>
                                <h2><?= $stats['total_products'] ?? 0 ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Orders</h5>
                                <h2><?= $stats['monthly_orders'] ?? 0 ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Revenue</h5>
                                <h2>৳<?= number_format($stats['monthly_revenue'] ?? 0, 2) ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">Pending Orders</h5>
                                <h2><?= $stats['pending_orders'] ?? 0 ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Alert -->
                <?php if (!empty($low_stock)): ?>
                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h5>
                    <ul class="mb-0">
                        <?php foreach ($low_stock as $product): ?>
                            <li><?= htmlspecialchars($product['name']) ?> - Only <?= $product['stock'] ?> left in stock</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="add-product.php" class="btn btn-primary me-2">
                                    <i class="fas fa-plus"></i> Add New Product
                                </a>
                                <a href="orders.php" class="btn btn-success me-2">
                                    <i class="fas fa-eye"></i> View Orders
                                </a>
                                <a href="reports.php" class="btn btn-info">
                                    <i class="fas fa-download"></i> Download Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>