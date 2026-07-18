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
    'SSLCommerz' => ['bg' => '#1E3A5F', 'label' => 'SSLCommerz Sandbox'],
];
$theme = $themes[$method] ?? ['bg' => '#1E3A5F', 'label' => $method];
$gatewayMode = $_GET['gateway'] ?? 'local';
$gatewayCaption = $gatewayMode === 'sslcommerz'
    ? 'SSLCommerz Sandbox Demo Checkout'
    : 'Dummy Payment Demo Checkout';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title><?= htmlspecialchars($theme['label']) ?> - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(180deg, #f4f7fb 0%, #eef2f7 100%);
        }
        .gateway-shell {
            max-width: 980px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 20px 60px rgba(12, 38, 76, 0.12);
            overflow: hidden;
            border: 1px solid #d7e1ea;
        }
        .gateway-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            background: linear-gradient(90deg, #0f2c4a 0%, #113a6f 100%);
            color: #fff;
        }
        .gateway-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .gateway-brand-badge {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-weight: 800;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.35);
        }
        .gateway-brand small {
            display: block;
            opacity: 0.8;
        }
        .sandbox-chip {
            border: 1px solid rgba(255,255,255,0.4);
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            background: rgba(255,255,255,0.08);
        }
        .gateway-body {
            padding: 24px;
            background: #f8fbff;
        }
        .gateway-card {
            background: #fff;
            border: 1px solid #dfe8f1;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(14, 47, 90, 0.08);
        }
        .gateway-card-header {
            padding: 18px 20px;
            border-bottom: 1px solid #e4edf6;
            background: #f5f9fd;
        }
        .gateway-amount {
            font-size: 2rem;
            font-weight: 800;
            color: #12345e;
        }
        .gateway-helper {
            color: #64748b;
            font-size: 0.9rem;
        }
        .gateway-form .form-control,
        .gateway-form .form-select {
            border-radius: 12px;
            border: 1px solid #cad7e3;
            background: #fbfdff;
        }
        .gateway-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        .gateway-preview {
            background: linear-gradient(135deg, #112f4d 0%, #1a4b80 100%);
            color: #fff;
            border-radius: 16px;
            padding: 18px;
            margin-bottom: 18px;
        }
        .gateway-preview .mini-label {
            opacity: 0.78;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .gateway-preview .value {
            font-size: 1.4rem;
            font-weight: 700;
        }
        @media (max-width: 767.98px) {
            .gateway-row {
                grid-template-columns: 1fr;
            }
            .gateway-topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="gateway-shell">
            <div class="gateway-topbar">
                <div class="gateway-brand">
                    <div class="gateway-brand-badge">SSL</div>
                    <div>
                        <h5 class="mb-0">SSLCommerz</h5>
                        <small><?= htmlspecialchars($gatewayCaption) ?></small>
                    </div>
                </div>
                <span class="sandbox-chip">SANDBOX</span>
            </div>

            <div class="gateway-body">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-7">
                        <div class="gateway-card">
                            <div class="gateway-card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">Secure Checkout</div>
                                    <div class="gateway-helper">Demo payment screen for SSLCommerz sandbox</div>
                                </div>
                                <span class="badge rounded-pill bg-warning text-dark px-3 py-2">No real money</span>
                            </div>
                            <div class="p-4 gateway-form">
                                <div class="gateway-preview">
                                    <div class="mini-label">Payable Amount</div>
                                    <div class="value">৳<?= number_format($order['total_amount'], 2) ?></div>
                                    <div class="mini-label mt-2">Order ID</div>
                                    <div><?= htmlspecialchars($order['order_number']) ?></div>
                                </div>

                                <?php if ($method === 'Card'): ?>
                                    <div class="gateway-row mb-3">
                                        <div>
                                            <label class="form-label small">Card Number</label>
                                            <input type="text" class="form-control" value="4242 4242 4242 4242" disabled>
                                        </div>
                                        <div>
                                            <label class="form-label small">Card Holder</label>
                                            <input type="text" class="form-control" value="AANCHOL TEST" disabled>
                                        </div>
                                    </div>
                                    <div class="gateway-row mb-3">
                                        <div>
                                            <label class="form-label small">Expiry</label>
                                            <input type="text" class="form-control" value="12/29" disabled>
                                        </div>
                                        <div>
                                            <label class="form-label small">CVC</label>
                                            <input type="text" class="form-control" value="123" disabled>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label class="form-label small"><?= htmlspecialchars($theme['label']) ?> Account</label>
                                        <input type="text" class="form-control" value="01XXXXXXXXX" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small">PIN / OTP</label>
                                        <input type="password" class="form-control" value="••••" disabled>
                                    </div>
                                <?php endif; ?>

                                <p class="gateway-helper text-center mb-4">
                                    This demo mirrors the SSLCommerz checkout style. Use the actions below to confirm either success or failure.
                                </p>

                                <form method="POST" action="payment-callback.php">
                                    <input type="hidden" name="order_number" value="<?= htmlspecialchars($order['order_number']) ?>">
                                    <div class="d-grid gap-2">
                                        <button type="submit" name="result" value="success" class="btn btn-lg text-white" style="background-color: <?= $theme['bg'] ?>; border-radius: 12px; font-weight: 700;">
                                            <i class="fas fa-check-circle"></i> Simulate Successful Payment
                                        </button>
                                        <button type="submit" name="result" value="failed" class="btn btn-outline-danger btn-lg" style="border-radius: 12px; font-weight: 700;">
                                            <i class="fas fa-times-circle"></i> Simulate Failed Payment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="gateway-card h-100">
                            <div class="gateway-card-header">
                                <div class="fw-bold">Payment Summary</div>
                                <div class="gateway-helper">Sandbox demonstration</div>
                            </div>
                            <div class="p-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Merchant</span>
                                    <strong>Aanchol</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Channel</span>
                                    <strong><?= htmlspecialchars($theme['label']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Amount</span>
                                    <strong>৳<?= number_format($order['total_amount'], 2) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Status</span>
                                    <span class="badge bg-light text-dark border">Pending</span>
                                </div>
                                <hr>
                                <div class="alert alert-info small mb-0">
                                    <i class="fas fa-shield-alt"></i> SSLCommerz sandbox is for development and testing only.
                                </div>
                                <div class="text-center mt-4">
                                    <a href="order-details.php?id=<?= $order['id'] ?>" class="small text-muted">Cancel and view order</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
