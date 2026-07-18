<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Toggle active/inactive
if (isset($_GET['toggle'])) {
    $coupon = getCouponById($_GET['toggle']);
    if ($coupon) {
        $new_status = ($coupon['status'] == 'active') ? 'inactive' : 'active';
        $sql = "UPDATE coupons SET status = :status WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':status', $new_status);
        $stmt->bindParam(':id', $_GET['toggle']);
        $stmt->execute();
        $_SESSION['success'] = "Coupon status updated!";
    }
    redirect('coupons.php');
}

// Delete
if (isset($_GET['delete'])) {
    if (deleteCoupon($_GET['delete'])) {
        $_SESSION['success'] = "Coupon deleted successfully!";
    } else {
        $_SESSION['error'] = "Cannot delete a coupon that has already been used in an order!";
    }
    redirect('coupons.php');
}

$coupons = getAllCoupons();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Coupons</title>
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
                    <h1 class="h2">Coupons</h1>
                    <a href="add-coupon.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Coupon
                    </a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $_SESSION['error'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Coupons (<?= count($coupons) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Code</th>
                                        <th>Discount</th>
                                        <th>Min Order</th>
                                        <th>Usage</th>
                                        <th>Validity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($coupons) == 0): ?>
                                        <tr><td colspan="7" class="text-center text-muted py-4">No coupons yet. Create your first one!</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($coupon['code']) ?></code></td>
                                        <td>
                                            <?php if ($coupon['discount_type'] == 'percentage'): ?>
                                                <?= rtrim(rtrim(number_format($coupon['discount_value'], 2), '0'), '.') ?>%
                                                <?php if ($coupon['max_discount_amount']): ?>
                                                    <small class="text-muted d-block">up to ৳<?= number_format($coupon['max_discount_amount'], 2) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                ৳<?= number_format($coupon['discount_value'], 2) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>৳<?= number_format($coupon['min_order_amount'], 2) ?></td>
                                        <td>
                                            <?= (int)$coupon['used_count'] ?> / <?= $coupon['usage_limit'] !== null ? (int)$coupon['usage_limit'] : '∞' ?>
                                        </td>
                                        <td>
                                            <?php if ($coupon['starts_at']): ?>from <?= date('d M Y', strtotime($coupon['starts_at'])) ?><br><?php endif; ?>
                                            <?= $coupon['expires_at'] ? 'until ' . date('d M Y', strtotime($coupon['expires_at'])) : 'No expiry' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $coupon['status'] == 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($coupon['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-coupon.php?id=<?= $coupon['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?toggle=<?= $coupon['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                            <a href="?delete=<?= $coupon['id'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this coupon? This cannot be undone!')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
