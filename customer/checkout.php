<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if cart is empty
$cart_items = getCartItems($_SESSION['user_id']);
if (count($cart_items) == 0) {
    redirect('cart.php');
}

// Get user details
$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $division_id = $_POST['division'];
    $district_id = $_POST['district'];
    $upazila_id = $_POST['upazila'];
    $detailed_address = $_POST['detailed_address'];
    $phone = $_POST['phone'];
    $notes = $_POST['notes'] ?? '';
    
    // Place order
    $order_id = placeOrder($_SESSION['user_id'], $division_id, $district_id, $upazila_id, $detailed_address, $phone);
    
    if ($order_id) {
        // Get order number for confirmation
        $sql = "SELECT order_number FROM orders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['order_success'] = $order['order_number'];
        redirect('order-success.php');
    } else {
        $error = "Failed to place order. Please try again!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>Checkout - Aanchol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4 mb-5">
        <h1 class="mb-4">Checkout</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" value="<?= htmlspecialchars($user['name']) ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="division" class="form-label">Division (Area) *</label>
                                    <select class="form-control" id="division" name="division" required>
                                        <option value="">Select Division</option>
                                        <?php
                                        $divisions = getDivisions();
                                        foreach ($divisions as $division) {
                                            $selected = ($user['division_id'] == $division['id']) ? 'selected' : '';
                                            echo "<option value='{$division['id']}' {$selected}>{$division['name']} ({$division['name_en']})</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="district" class="form-label">District (Jela) *</label>
                                    <select class="form-control" id="district" name="district" required disabled>
                                        <option value="">Select District</option>
                                        <?php
                                        if ($user['division_id']) {
                                            $districts = getDistricts($user['division_id']);
                                            foreach ($districts as $district) {
                                                $selected = ($user['district_id'] == $district['id']) ? 'selected' : '';
                                                echo "<option value='{$district['id']}' {$selected}>{$district['name']} ({$district['name_en']})</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="upazila" class="form-label">Upazila (Thana) *</label>
                                    <select class="form-control" id="upazila" name="upazila" required disabled>
                                        <option value="">Select Upazila</option>
                                        <?php
                                        if ($user['district_id']) {
                                            $upazilas = getUpazilas($user['district_id']);
                                            foreach ($upazilas as $upazila) {
                                                $selected = ($user['upazila_id'] == $upazila['id']) ? 'selected' : '';
                                                echo "<option value='{$upazila['id']}' {$selected}>{$upazila['name']} ({$upazila['name_en']})</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="detailed_address" class="form-label">Detailed Address *</label>
                                    <textarea class="form-control" id="detailed_address" name="detailed_address" rows="2" required placeholder="House/Road/Area details"><?= htmlspecialchars($user['detailed_address']) ?></textarea>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="notes" class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Special instructions, delivery preferences, etc."></textarea>
                            </div>
                            
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Payment Method</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                        <label class="form-check-label" for="cod">
                                            <strong>Cash on Delivery (COD)</strong><br>
                                            <small class="text-muted">Pay when you receive the order</small>
                                        </label>
                                    </div>
                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i> Online payment options coming soon!
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-check-circle"></i> Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="order-summary">
                            <?php 
                            $subtotal = 0;
                            foreach ($cart_items as $item): 
                                $item_total = $item['price'] * $item['quantity'];
                                $subtotal += $item_total;
                            ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                                </div>
                                <div>৳<?= number_format($item_total, 2) ?></div>
                            </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Subtotal</div>
                                <div>৳<?= number_format($subtotal, 2) ?></div>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Shipping</div>
                                <div>৳50.00</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <div>Total</div>
                                <div class="text-primary">৳<?= number_format($subtotal + 50, 2) ?></div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Order Review:</h6>
                            <ul class="small">
                                <?php foreach ($cart_items as $item): ?>
                                <li><?= $item['quantity'] ?> × <?= htmlspecialchars($item['name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle"></i>
                            Please review your order before confirming. You will receive an order confirmation email.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Enable/disable dropdowns based on current selections
        document.addEventListener('DOMContentLoaded', function() {
            const divisionSelect = document.getElementById('division');
            const districtSelect = document.getElementById('district');
            const upazilaSelect = document.getElementById('upazila');
            
            // Enable district if division is selected
            if (divisionSelect.value) {
                districtSelect.disabled = false;
            }
            
            // Enable upazila if district is selected
            if (districtSelect.value) {
                upazilaSelect.disabled = false;
            }
        });
        
        document.getElementById('division').addEventListener('change', function() {
            const divisionId = this.value;
            const districtSelect = document.getElementById('district');
            const upazilaSelect = document.getElementById('upazila');
            
            // Reset districts and upazilas
            districtSelect.innerHTML = '<option value="">Select District</option>';
            upazilaSelect.innerHTML = '<option value="">Select Upazila</option>';
            districtSelect.disabled = true;
            upazilaSelect.disabled = true;
            
            if (divisionId) {
                // Fetch districts via AJAX
                fetch('../includes/get_locations.php?type=districts&division_id=' + divisionId)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(district => {
                            districtSelect.innerHTML += `<option value="${district.id}">${district.name} (${district.name_en})</option>`;
                        });
                        districtSelect.disabled = false;
                    });
            }
        });
        
        document.getElementById('district').addEventListener('change', function() {
            const districtId = this.value;
            const upazilaSelect = document.getElementById('upazila');
            
            // Reset upazilas
            upazilaSelect.innerHTML = '<option value="">Select Upazila</option>';
            upazilaSelect.disabled = true;
            
            if (districtId) {
                // Fetch upazilas via AJAX
                fetch('../includes/get_locations.php?type=upazilas&district_id=' + districtId)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(upazila => {
                            upazilaSelect.innerHTML += `<option value="${upazila.id}">${upazila.name} (${upazila.name_en})</option>`;
                        });
                        upazilaSelect.disabled = false;
                    });
            }
        });
    </script>
</body>
</html>