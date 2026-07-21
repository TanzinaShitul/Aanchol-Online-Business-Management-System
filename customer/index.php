<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    redirect('../admin/dashboard.php');
}

$userId = (int) $_SESSION['user_id'];
$summaryStatement = $conn->prepare("SELECT COUNT(*) AS total_orders,
    COALESCE(SUM(status IN ('pending', 'confirmed', 'processing', 'shipped')), 0) AS active_orders
    FROM orders WHERE user_id = :user_id");
$summaryStatement->execute([':user_id' => $userId]);
$summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);

$cartStatement = $conn->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = :user_id');
$cartStatement->execute([':user_id' => $userId]);
$cartQuantity = (int) $cartStatement->fetchColumn();

$recentStatement = $conn->prepare('SELECT * FROM orders WHERE user_id = :user_id ORDER BY order_date DESC LIMIT 3');
$recentStatement->execute([':user_id' => $userId]);
$recentOrders = $recentStatement->fetchAll(PDO::FETCH_ASSOC);

$statusClasses = [
    'pending' => 'warning',
    'confirmed' => 'info',
    'processing' => 'primary',
    'shipped' => 'secondary',
    'delivered' => 'success',
    'cancelled' => 'danger',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>My Account - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .account-hero { background: linear-gradient(135deg, var(--color-indigo), var(--color-indigo-light)); border-radius: var(--radius-lg); color: #fff; overflow: hidden; }
        .account-hero h1, .account-hero p { color: #fff; }
        .account-hero::after { content: ''; position: absolute; width: 220px; height: 220px; border: 28px solid rgba(255,255,255,.09); border-radius: 50%; right: -55px; top: -95px; }
        .account-nav .list-group-item { border-color: var(--color-border); padding: .9rem 1rem; }
        .account-nav .list-group-item.active { background: var(--color-indigo); border-color: var(--color-indigo); }
        .stat-card, .dashboard-panel { border: 0; border-radius: var(--radius-md); box-shadow: var(--shadow-soft); }
        .stat-card { height: 100%; }
        .stat-icon { width: 44px; height: 44px; display: grid; place-items: center; border-radius: 12px; background: var(--color-ivory-dim); color: var(--color-indigo); }
        .recent-orders th { color: var(--color-charcoal-soft); font-size: .76rem; text-transform: uppercase; letter-spacing: .06em; white-space: nowrap; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main class="container py-4 py-lg-5">
        <section class="account-hero position-relative p-4 p-lg-5 mb-4">
            <div class="position-relative" style="z-index:1">
                <p class="text-uppercase small fw-bold mb-2" style="letter-spacing:.1em">My Account</p>
                <h1 class="h2 mb-2">Welcome back, <?= htmlspecialchars($_SESSION['name'] ?? 'Customer') ?>.</h1>
                <p class="mb-0 opacity-75">Manage your orders and shopping cart from one place.</p>
            </div>
        </section>

        <div class="row g-4">
            <aside class="col-lg-3">
                <div class="card account-nav border-0 shadow-sm">
                    <div class="card-header bg-primary text-white"><h5 class="mb-0">My Account</h5></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item active"><a href="index.php" class="text-decoration-none text-white"><i class="fas fa-house me-2"></i>Dashboard</a></li>
                        <li class="list-group-item"><a href="order-history.php" class="text-decoration-none"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li class="list-group-item"><a href="cart.php" class="text-decoration-none"><i class="fas fa-cart-shopping me-2"></i>Shopping Cart</a></li>
                        <li class="list-group-item"><a href="logout.php" class="text-decoration-none text-danger"><i class="fas fa-arrow-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </aside>

            <section class="col-lg-9">
                <div class="row g-3 mb-4">
                    <div class="col-md-4"><div class="card stat-card"><div class="card-body d-flex align-items-center gap-3"><div class="stat-icon"><i class="fas fa-bag-shopping"></i></div><div><div class="small text-muted">Orders</div><div class="h4 mb-0"><?= (int) $summary['total_orders'] ?></div></div></div></div></div>
                    <div class="col-md-4"><div class="card stat-card"><div class="card-body d-flex align-items-center gap-3"><div class="stat-icon"><i class="fas fa-truck-fast"></i></div><div><div class="small text-muted">Active orders</div><div class="h4 mb-0"><?= (int) $summary['active_orders'] ?></div></div></div></div></div>
                    <div class="col-md-4"><div class="card stat-card"><div class="card-body d-flex align-items-center gap-3"><div class="stat-icon"><i class="fas fa-cart-shopping"></i></div><div><div class="small text-muted">Items in cart</div><div class="h4 mb-0"><?= $cartQuantity ?></div></div></div></div></div>
                </div>

                <div class="card dashboard-panel">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                        <div><h4 class="mb-1">Recent Orders</h4><p class="text-muted small mb-0">Your latest purchases and their current status.</p></div>
                        <a href="order-history.php" class="btn btn-sm btn-outline-primary">View all</a>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <?php if ($recentOrders): ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle recent-orders mb-0">
                                    <thead><tr><th>Order</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($recentOrders as $order): ?>
                                            <tr>
                                                <td><a href="order-details.php?id=<?= (int) $order['id'] ?>" class="fw-bold text-decoration-none"><?= htmlspecialchars($order['order_number']) ?></a></td>
                                                <td><?= date('d M Y', strtotime($order['order_date'])) ?></td>
                                                <td>৳<?= number_format((float) $order['total_amount'], 2) ?></td>
                                                <td><span class="badge bg-<?= $statusClasses[$order['status']] ?? 'secondary' ?>"><?= htmlspecialchars(ucfirst($order['status'])) ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4"><i class="fas fa-bag-shopping fa-2x text-muted mb-3"></i><p class="text-muted mb-3">You have not placed an order yet.</p><a href="products.php" class="btn btn-primary">Start Shopping</a></div>
                        <?php endif; ?>
                        <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                            <a href="products.php" class="btn btn-primary"><i class="fas fa-store me-1"></i> Continue Shopping</a>
                            <a href="order-history.php" class="btn btn-outline-primary">View All Orders</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
