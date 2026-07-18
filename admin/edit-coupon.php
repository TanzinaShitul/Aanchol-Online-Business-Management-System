<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    redirect('coupons.php');
}

$coupon = getCouponById($_GET['id']);
if (!$coupon) {
    redirect('coupons.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = [
        'code' => trim($_POST['code']),
        'discount_type' => $_POST['discount_type'],
        'discount_value' => $_POST['discount_value'],
        'min_order_amount' => $_POST['min_order_amount'] !== '' ? $_POST['min_order_amount'] : 0,
        'max_discount_amount' => $_POST['max_discount_amount'] !== '' ? $_POST['max_discount_amount'] : null,
        'usage_limit' => $_POST['usage_limit'] !== '' ? $_POST['usage_limit'] : null,
        'starts_at' => $_POST['starts_at'] !== '' ? $_POST['starts_at'] : null,
        'expires_at' => $_POST['expires_at'] !== '' ? $_POST['expires_at'] : null,
        'status' => $_POST['status'],
    ];

    $existing = getCouponByCode($data['code']);

    if (empty($data['code'])) {
        $error = "Coupon code is required.";
    } elseif ($existing && $existing['id'] != $coupon['id']) {
        $error = "Another coupon already uses this code.";
    } elseif ($data['discount_type'] == 'percentage' && $data['discount_value'] > 100) {
        $error = "Percentage discount cannot exceed 100%.";
    } else {
        if (updateCoupon($coupon['id'], $data)) {
            $_SESSION['success'] = "Coupon updated successfully!";
            redirect('coupons.php');
        } else {
            $error = "Failed to update coupon!";
        }
        $coupon = array_merge($coupon, $data);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Edit Coupon - Aanchol</title>
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
                    <h1 class="h2">Edit Coupon</h1>
                    <a href="coupons.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Coupons
                    </a>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ((int)$coupon['used_count'] > 0): ?>
                    <div class="alert alert-warning small">
                        <i class="fas fa-exclamation-triangle"></i> This coupon has already been used <?= (int)$coupon['used_count'] ?> time(s). Changing its value won't affect past orders.
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">Coupon Code *</label>
                                    <input type="text" class="form-control text-uppercase" id="code" name="code"
                                           value="<?= htmlspecialchars($coupon['code']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active" <?= $coupon['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= $coupon['status'] == 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="discount_type" class="form-label">Discount Type *</label>
                                    <select class="form-control" id="discount_type" name="discount_type" required>
                                        <option value="percentage" <?= $coupon['discount_type'] == 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                        <option value="fixed" <?= $coupon['discount_type'] == 'fixed' ? 'selected' : '' ?>>Fixed Amount (৳)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="discount_value" class="form-label">Discount Value *</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="discount_value" name="discount_value" value="<?= htmlspecialchars($coupon['discount_value']) ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="min_order_amount" class="form-label">Minimum Order Amount (৳)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="min_order_amount" name="min_order_amount" value="<?= htmlspecialchars($coupon['min_order_amount']) ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="max_discount_amount" class="form-label">Maximum Discount Cap (৳) <small class="text-muted">— percentage coupons only</small></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="max_discount_amount" name="max_discount_amount" value="<?= htmlspecialchars($coupon['max_discount_amount'] ?? '') ?>" placeholder="No cap">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="usage_limit" class="form-label">Total Usage Limit</label>
                                    <input type="number" min="1" class="form-control" id="usage_limit" name="usage_limit" value="<?= htmlspecialchars($coupon['usage_limit'] ?? '') ?>" placeholder="Unlimited">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="starts_at" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="starts_at" name="starts_at" value="<?= htmlspecialchars($coupon['starts_at'] ?? '') ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="expires_at" class="form-label">Expiry Date</label>
                                    <input type="date" class="form-control" id="expires_at" name="expires_at" value="<?= htmlspecialchars($coupon['expires_at'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Coupon
                                </button>
                                <a href="coupons.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
