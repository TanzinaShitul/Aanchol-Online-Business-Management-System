<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$order = null;
$tracking_events = [];
$not_found = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_number = trim($_POST['order_number'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($order_number && $phone) {
        $order = getOrderByNumberAndPhone($order_number, $phone);
        if ($order) {
            $tracking_events = getTrackingEvents($order['id']);
        } else {
            $not_found = true;
        }
    }
}

$status_steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Track Your Order - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-truck"></i> Track Your Order</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-md-7">
                                <label class="form-label">Order Number *</label>
                                <input type="text" name="order_number" class="form-control" placeholder="e.g. ORD20260711123456" value="<?= htmlspecialchars($_POST['order_number'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" name="phone" class="form-control" placeholder="Used at checkout" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Track Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($not_found): ?>
                    <div class="alert alert-danger">
                        We couldn't find an order matching that order number and phone number. Please double-check and try again.
                    </div>
                <?php endif; ?>

                <?php if ($order): ?>
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Order #<?= htmlspecialchars($order['order_number']) ?></h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>Placed:</strong> <?= date('F d, Y h:i A', strtotime($order['order_date'])) ?></p>
                            <p class="mb-1"><strong>Total:</strong> ৳<?= number_format($order['total_amount'], 2) ?></p>
                            <?php if (!empty($order['courier_name'])): ?>
                                <p class="mb-1"><strong>Courier:</strong> <?= htmlspecialchars($order['courier_name']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($order['tracking_number'])): ?>
                                <p class="mb-1"><strong>Tracking No:</strong> <?= htmlspecialchars($order['tracking_number']) ?></p>
                            <?php endif; ?>

                            <?php if ($order['status'] === 'cancelled'): ?>
                                <div class="alert alert-danger mt-3 mb-0">
                                    <i class="fas fa-times-circle"></i> This order has been cancelled.
                                </div>
                            <?php else: ?>
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between text-center">
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
                                </div>
                            <?php endif; ?>

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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
