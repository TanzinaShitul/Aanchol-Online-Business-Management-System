<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['order'])) {
    redirect('order-history.php');
}

$order = getOrderByNumber($_GET['order']);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    redirect('order-history.php');
}

if ($order['payment_status'] === 'paid') {
    redirect('order-details.php?id=' . $order['id']);
}

$method = $order['payment_method'];

$themes = [
    'bKash'  => ['bg' => '#E2136E', 'label' => 'bKash'],
    'Nagad'  => ['bg' => '#EC1D25', 'label' => 'Nagad'],
    'Rocket' => ['bg' => '#8C3494', 'label' => 'Rocket'],
    'Card'   => ['bg' => '#1E3A5F', 'label' => 'Card Payment'],
];
$theme = $themes[$method] ?? ['bg' => '#1E3A5F', 'label' => $method];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title><?= htmlspecialchars($theme['label']) ?> Payment - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body style="background-color:#f2f2f2;">
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="text-center mb-3">
                    <span class="badge bg-warning text-dark px-3 py-2">
                        <i class="fas fa-flask"></i> SANDBOX / TEST MODE — No real money is involved
                    </span>
                </div>

                <div class="card shadow border-0" style="border-radius: 16px; overflow: hidden;">
                    <div class="text-white text-center py-4" style="background-color: <?= $theme['bg'] ?>;">
                        <h3 class="mb-0"><?= htmlspecialchars($theme['label']) ?></h3>
                        <small>Simulated Payment Gateway</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="text-muted small">Amount to pay</div>
                            <h2 class="text-primary">৳<?= number_format($order['total_amount'], 2) ?></h2>
                            <div class="text-muted small">Order #<?= htmlspecialchars($order['order_number']) ?></div>
                        </div>

                        <?php if ($method === 'Card'): ?>
                            <div class="mb-3">
                                <label class="form-label small">Card Number</label>
                                <input type="text" class="form-control" value="4242 4242 4242 4242" disabled>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small">Expiry</label>
                                    <input type="text" class="form-control" value="12/29" disabled>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small">CVC</label>
                                    <input type="text" class="form-control" value="123" disabled>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label small"><?= htmlspecialchars($theme['label']) ?> Account Number</label>
                                <input type="text" class="form-control" value="01XXXXXXXXX" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small">PIN</label>
                                <input type="password" class="form-control" value="••••" disabled>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted small text-center">This is a simulated checkout screen for demo purposes. Use the buttons below to test both outcomes.</p>

                        <form method="POST" action="payment-callback.php">
                            <input type="hidden" name="order_number" value="<?= htmlspecialchars($order['order_number']) ?>">
                            <div class="d-grid gap-2">
                                <button type="submit" name="result" value="success" class="btn btn-lg text-white" style="background-color: <?= $theme['bg'] ?>;">
                                    <i class="fas fa-check-circle"></i> Simulate Successful Payment
                                </button>
                                <button type="submit" name="result" value="failed" class="btn btn-outline-danger">
                                    <i class="fas fa-times-circle"></i> Simulate Failed Payment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="order-details.php?id=<?= $order['id'] ?>" class="small text-muted">Cancel and view order</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
