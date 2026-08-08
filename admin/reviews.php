<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

if (isset($_GET['toggle'])) {
    $sql = "SELECT status FROM reviews WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_GET['toggle']);
    $stmt->execute();
    $review = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($review) {
        $new_status = $review['status'] == 'approved' ? 'hidden' : 'approved';
        setReviewStatus($_GET['toggle'], $new_status);
        $_SESSION['success'] = "Review status updated!";
    }
    redirect('reviews.php');
}

if (isset($_GET['delete'])) {
    deleteReview($_GET['delete']);
    $_SESSION['success'] = "Review deleted!";
    redirect('reviews.php');
}

$reviews = getAllReviews();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Reviews</title>
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
                    <h1 class="h2">Customer Reviews</h1>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $_SESSION['success'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">All Reviews (<?= count($reviews) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Comment</th>
                                        <th>Verified</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($reviews) == 0): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">No reviews yet.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($reviews as $review): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($review['product_name']) ?></td>
                                        <td><?= htmlspecialchars($review['customer_name']) ?></td>
                                        <td>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?= $i <= $review['rating'] ? 'fas' : 'far' ?> fa-star text-warning"></i>
                                            <?php endfor; ?>
                                        </td>
                                        <td style="max-width:250px;"><?= htmlspecialchars($review['comment'] ?: '—') ?></td>
                                        <td>
                                            <?php if ($review['order_id']): ?>
                                                <span class="badge bg-success">Verified</span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('d M, Y', strtotime($review['created_at'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $review['status'] == 'approved' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($review['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?toggle=<?= $review['id'] ?>" class="btn btn-sm btn-warning" title="Toggle visibility">
                                                <i class="fas fa-eye<?= $review['status'] == 'approved' ? '' : '-slash' ?>"></i>
                                            </a>
                                            <a href="?delete=<?= $review['id'] ?>" class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this review permanently?')">
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
