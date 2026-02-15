<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Handle product deletion
if (isset($_GET['delete'])) {
    $product_id = $_GET['delete'];
    
    // First check if product has orders
    $sql = "SELECT COUNT(*) as order_count FROM order_items WHERE product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['order_count'] == 0) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $product_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Cannot delete product with existing orders!";
    }
    
    redirect('products.php');
}

// Get all products
$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC";
$stmt = $conn->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Manage Products</title>
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
                    <h1 class="h2">Manage Products</h1>
                    <a href="add-product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
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
                        <h5 class="mb-0">All Products (<?= count($products) ?>)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $product['id'] ?></td>
                                        <td>
                                            <img src="../uploads/<?= $product['image'] ?: 'default.jpg' ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover;"
                                                 alt="<?= htmlspecialchars($product['name']) ?>">
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= $product['category_name'] ?></td>
                                        <td>৳<?= number_format($product['price'], 2) ?></td>
                                        <td>
                                            <?php if ($product['stock'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php elseif ($product['stock'] <= 5): ?>
                                                <span class="badge bg-warning"><?= $product['stock'] ?> Left</span>
                                            <?php else: ?>
                                                <?= $product['stock'] ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $product['status'] == 'active' ? 'success' : 'danger' ?>">
                                                <?= ucfirst($product['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="?delete=<?= $product['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure? This cannot be undone!')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Stock Summary -->
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6>Total Products</h6>
                                        <h3><?= count($products) ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php
                                        $sql = "SELECT COUNT(*) as low_stock FROM products WHERE stock BETWEEN 1 AND 5 AND status = 'active'";
                                        $stmt = $conn->query($sql);
                                        $low_stock = $stmt->fetch(PDO::FETCH_ASSOC)['low_stock'];
                                        ?>
                                        <h6>Low Stock Items</h6>
                                        <h3 class="text-warning"><?= $low_stock ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php
                                        $sql = "SELECT COUNT(*) as out_of_stock FROM products WHERE stock = 0 AND status = 'active'";
                                        $stmt = $conn->query($sql);
                                        $out_of_stock = $stmt->fetch(PDO::FETCH_ASSOC)['out_of_stock'];
                                        ?>
                                        <h6>Out of Stock</h6>
                                        <h3 class="text-danger"><?= $out_of_stock ?></h3>
                                    </div>
                                </div>
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