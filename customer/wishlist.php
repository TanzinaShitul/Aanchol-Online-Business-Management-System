<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$wishlist_items = getWishlistItems($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>My Wishlist - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-4 mb-5">
        <h1 class="mb-4"><i class="fas fa-heart text-danger"></i> My Wishlist</h1>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (count($wishlist_items) > 0): ?>
            <div class="row">
                <?php foreach ($wishlist_items as $product): ?>
                <?php $pricing = getEffectivePrice($product); ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card product-card h-100">
                        <img src="<?= htmlspecialchars(getProductImageUrl($product['image'] ?? null)) ?>"
                             class="card-img-top"
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <div class="mb-2">
                                <?php if ($pricing['has_discount']): ?>
                                    <span class="text-muted text-decoration-line-through small d-block">৳<?= number_format($pricing['original_price'], 2) ?></span>
                                <?php endif; ?>
                                <span class="h5 text-primary">৳<?= number_format($pricing['final_price'], 2) ?></span>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="product-details.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary flex-fill">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" action="toggle-wishlist.php" class="flex-fill">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="redirect" value="wishlist.php">
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <div class="display-1 text-muted">
                    <i class="far fa-heart"></i>
                </div>
                <h3 class="mt-3">Your wishlist is empty</h3>
                <p class="text-muted">Tap the heart icon on any product to save it here.</p>
                <a href="products.php" class="btn btn-primary btn-lg">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
