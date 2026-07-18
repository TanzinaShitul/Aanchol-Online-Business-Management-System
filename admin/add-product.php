<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

// Get categories from database
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];
    $discount_percent = $_POST['discount_percent'] !== '' ? $_POST['discount_percent'] : 0;
    $discount_starts_at = $_POST['discount_starts_at'] !== '' ? $_POST['discount_starts_at'] : null;
    $discount_ends_at = $_POST['discount_ends_at'] !== '' ? $_POST['discount_ends_at'] : null;
    
    // Handle image upload
    $image = 'default.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $target_path = "../uploads/" . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image = $image_name;
            }
        }
    }

    if ($discount_percent < 0 || $discount_percent > 90) {
        $error = "Discount percentage must be between 0 and 90.";
    } else {
        // Insert product
        $sql = "INSERT INTO products (name, description, price, category_id, stock, image, status, discount_percent, discount_starts_at, discount_ends_at) 
                VALUES (:name, :description, :price, :category_id, :stock, :image, :status, :discount_percent, :discount_starts_at, :discount_ends_at)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':discount_percent', $discount_percent);
        $stmt->bindParam(':discount_starts_at', $discount_starts_at);
        $stmt->bindParam(':discount_ends_at', $discount_ends_at);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Product added successfully!";
            redirect('products.php');
        } else {
            $error = "Failed to add product!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Add Product - Aanchol</title>
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
                    <h1 class="h2">Add New Product</h1>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php if (!empty($categories)): ?>
                                            <?php foreach ($categories as $category): ?>
                                                <?php if (isset($category['name'])): ?>
                                                    <option value="<?= htmlspecialchars($category['id']) ?>">
                                                        <?= htmlspecialchars($category['name']) ?>
                                                    </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="">No categories found. Please add categories first.</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Price (৳) *</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="stock" class="form-label">Stock Quantity *</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Recommended size: 500x500 pixels</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3"><i class="fas fa-percentage"></i> Discount (optional)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="discount_percent" class="form-label">Discount %</label>
                                            <input type="number" class="form-control" id="discount_percent" name="discount_percent" step="0.01" min="0" max="90" value="0">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="discount_starts_at" class="form-label">Starts</label>
                                            <input type="date" class="form-control" id="discount_starts_at" name="discount_starts_at">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="discount_ends_at" class="form-label">Ends</label>
                                            <input type="date" class="form-control" id="discount_ends_at" name="discount_ends_at">
                                        </div>
                                    </div>
                                    <small class="text-muted">Leave dates blank for an always-on discount. Leave percent at 0 for no discount.</small>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Product
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
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
