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

$cart_total = getCartTotal($_SESSION['user_id']);

// Re-validate any applied coupon against the current cart total
$applied_coupon = null;
$coupon_discount = 0;
if (!empty($_SESSION['coupon_code'])) {
    $validation = validateCoupon($_SESSION['coupon_code'], $_SESSION['user_id'], $cart_total);
    if ($validation['valid']) {
        $applied_coupon = $validation['coupon'];
        $coupon_discount = $validation['discount_amount'];
    } else {
        unset($_SESSION['coupon_code']);
        $_SESSION['error'] = $validation['message'];
    }
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $division_id = $_POST['division'];
    $district_id = $_POST['district'];
    $upazila_id = $_POST['upazila'];
    $detailed_address = $_POST['detailed_address'];
    $phone = $_POST['phone'];
    $notes = $_POST['notes'] ?? '';

    $payment_map = [
        'cod'    => 'Cash on Delivery',
        'bkash'  => 'bKash',
        'nagad'  => 'Nagad',
        'rocket' => 'Rocket',
        'card'   => 'Card',
    ];
    $payment_choice = $_POST['payment_method'] ?? 'cod';
    $payment_method = $payment_map[$payment_choice] ?? 'Cash on Delivery';
    
    // Get location names to include in address
    $sql = "SELECT name_en FROM divisions WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $division_id);
    $stmt->execute();
    $division = $stmt->fetch(PDO::FETCH_ASSOC);
    $division_name = $division['name_en'] ?? '';
    
    $sql = "SELECT name_en FROM districts WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $district_id);
    $stmt->execute();
    $district = $stmt->fetch(PDO::FETCH_ASSOC);
    $district_name = $district['name_en'] ?? '';
    
    $sql = "SELECT name_en FROM upazilas WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $upazila_id);
    $stmt->execute();
    $upazila = $stmt->fetch(PDO::FETCH_ASSOC);
    $upazila_name = $upazila['name_en'] ?? '';
    
    // Build complete address with division, district, upazila
    $complete_address = $detailed_address . "\n" . $upazila_name . ", " . $district_name . ", " . $division_name;

    // Re-validate coupon one last time right before placing the order
    $coupon_id = null;
    $coupon_code = null;
    $discount_amount = 0;
    if (!empty($_SESSION['coupon_code'])) {
        $final_check = validateCoupon($_SESSION['coupon_code'], $_SESSION['user_id'], getCartTotal($_SESSION['user_id']));
        if ($final_check['valid']) {
            $coupon_id = $final_check['coupon']['id'];
            $coupon_code = $final_check['coupon']['code'];
            $discount_amount = $final_check['discount_amount'];
        } else {
            unset($_SESSION['coupon_code']);
        }
    }
    
    // Place order
    $order_id = placeOrder($_SESSION['user_id'], $division_id, $district_id, $upazila_id, $complete_address, $phone, $coupon_id, $coupon_code, $discount_amount, $payment_method);
    
    if ($order_id) {
        // Get order number for confirmation
        $sql = "SELECT order_number FROM orders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $order_id);
        $stmt->execute();
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        unset($_SESSION['coupon_code']);

        if ($payment_choice === 'cod') {
            $_SESSION['order_success'] = $order['order_number'];
            redirect('order-success.php');
        } elseif ($payment_choice === 'card') {
            redirect('sslcommerz-initiate.php?order=' . urlencode($order['order_number']));
        } else {
            redirect('sslcommerz-initiate.php?order=' . urlencode($order['order_number']));
        }
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
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" checked>
                                        <label class="form-check-label" for="cod">
                                            <strong>Cash on Delivery (COD)</strong><br>
                                            <small class="text-muted">Pay when you receive the order</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bkash" value="bkash">
                                        <label class="form-check-label" for="bkash">
                                            <strong style="color:#E2136E;">bKash</strong>
                                            <small class="text-muted d-block">Pay via bKash (sandbox)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="nagad" value="nagad">
                                        <label class="form-check-label" for="nagad">
                                            <strong style="color:#EC1D25;">Nagad</strong>
                                            <small class="text-muted d-block">Pay via Nagad (sandbox)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="rocket" value="rocket">
                                        <label class="form-check-label" for="rocket">
                                            <strong style="color:#8C3494;">Rocket</strong>
                                            <small class="text-muted d-block">Pay via Rocket (sandbox)</small>
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="payment_method" id="card" value="card">
                                        <label class="form-check-label" for="card">
                                            <strong>Credit / Debit Card</strong>
                                            <small class="text-muted d-block">Pay via card (sandbox)</small>
                                        </label>
                                    </div>
                                    <div class="alert alert-warning mt-3 mb-0 small">
                                        <i class="fas fa-flask"></i> Online payment options are running in <strong>sandbox/test mode</strong> — no real transactions occur.
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
                                    <?php if (!empty($item['has_discount'])): ?>
                                        <span class="badge bg-danger ms-1">-<?= rtrim(rtrim(number_format($item['discount_percent_applied'], 2), '0'), '.') ?>%</span>
                                    <?php endif; ?>
                                </div>
                                <div>৳<?= number_format($item_total, 2) ?></div>
                            </div>
                            <?php endforeach; ?>
                            
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Subtotal</div>
                                <div id="subtotal-amount">৳<?= number_format($subtotal, 2) ?></div>
                            </div>
                            <?php if ($applied_coupon): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <div>Coupon (<?= htmlspecialchars($applied_coupon['code']) ?>)</div>
                                <div>&minus;৳<?= number_format($coupon_discount, 2) ?></div>
                            </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>Shipping</div>
                                <div id="shipping-cost">৳80.00</div>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fw-bold">
                                <div>Total</div>
                                <div class="text-primary" id="total-cost">৳<?= number_format(max($subtotal + 80 - $coupon_discount, 0), 2) ?></div>
                            </div>
                        </div>

                        <?php if ($applied_coupon): ?>
                        <div class="alert alert-success mt-3 py-2 px-3 mb-0 small">
                            <i class="fas fa-tag"></i> Coupon <strong><?= htmlspecialchars($applied_coupon['code']) ?></strong> will be applied to this order.
                        </div>
                        <?php else: ?>
                        <div class="mt-3 small">
                            <a href="cart.php">Have a coupon code? Apply it from your cart.</a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mt-4">
                            <h6>Order Review:</h6>
                            <ul class="small">
                                <?php foreach ($cart_items as $item): ?>
                                <li><?= $item['quantity'] ?> × <?= htmlspecialchars($item['name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
    
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const subtotal = <?= $subtotal ?>;
        const couponDiscount = <?= $coupon_discount ?>;
        
        // Calculate and update shipping and total based on division
        function updateShippingCost(divisionId) {
            let shipping = 80; // Default for Dhaka
            
            // Check if division is Dhaka (ID = 1)
            if (divisionId === '1' || divisionId === 1) {
                shipping = 80;
            } else if (divisionId) {
                shipping = 130; // Outside Dhaka
            }
            
            const total = Math.max(subtotal + shipping - couponDiscount, 0);
            document.getElementById('shipping-cost').textContent = '৳' + shipping.toFixed(2);
            document.getElementById('total-cost').textContent = '৳' + total.toFixed(2);
        }
        
        // Enable/disable dropdowns based on current selections
        document.addEventListener('DOMContentLoaded', function() {
            const divisionSelect = document.getElementById('division');
            const districtSelect = document.getElementById('district');
            const upazilaSelect = document.getElementById('upazila');
            
            // Enable district if division is selected
            if (divisionSelect.value) {
                districtSelect.disabled = false;
                updateShippingCost(divisionSelect.value);
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
            
            // Update shipping cost based on division
            updateShippingCost(divisionId);
            
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
