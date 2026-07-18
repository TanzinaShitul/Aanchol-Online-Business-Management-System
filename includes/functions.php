<?php
// includes/functions.php
require_once __DIR__ . '/../config/database.php';

function getProducts($category_id = null) {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    
    if ($category_id) {
        $sql .= " AND p.category_id = :category_id";
    }
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($category_id) {
        $stmt->bindParam(':category_id', $category_id);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductById($id) {
    global $conn;
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCategories() {
    global $conn;
    $sql = "SELECT * FROM categories ORDER BY name";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDivisions() {
    global $conn;
    $sql = "SELECT * FROM divisions ORDER BY name";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDistricts($division_id = null) {
    global $conn;
    $sql = "SELECT * FROM districts";
    if ($division_id) {
        $sql .= " WHERE division_id = :division_id";
    }
    $sql .= " ORDER BY name";
    $stmt = $conn->prepare($sql);
    if ($division_id) {
        $stmt->bindParam(':division_id', $division_id);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUpazilas($district_id = null) {
    global $conn;
    $sql = "SELECT * FROM upazilas";
    if ($district_id) {
        $sql .= " WHERE district_id = :district_id";
    }
    $sql .= " ORDER BY name";
    $stmt = $conn->prepare($sql);
    if ($district_id) {
        $stmt->bindParam(':district_id', $district_id);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/* =========================================================================
   PRODUCT DISCOUNTS
   ========================================================================= */

/**
 * Given a product row (must include price, discount_percent, discount_starts_at,
 * discount_ends_at), returns the effective selling price honoring the discount
 * window. Falls back gracefully if discount columns are missing/null.
 */
function getEffectivePrice($product) {
    $price = (float)$product['price'];
    $percent = isset($product['discount_percent']) ? (float)$product['discount_percent'] : 0;

    if ($percent <= 0) {
        return ['final_price' => $price, 'original_price' => $price, 'has_discount' => false, 'percent' => 0];
    }

    $today = date('Y-m-d');
    if (!empty($product['discount_starts_at']) && $product['discount_starts_at'] > $today) {
        return ['final_price' => $price, 'original_price' => $price, 'has_discount' => false, 'percent' => 0];
    }
    if (!empty($product['discount_ends_at']) && $product['discount_ends_at'] < $today) {
        return ['final_price' => $price, 'original_price' => $price, 'has_discount' => false, 'percent' => 0];
    }

    $final = round($price - ($price * $percent / 100), 2);
    return ['final_price' => $final, 'original_price' => $price, 'has_discount' => true, 'percent' => $percent];
}

/* =========================================================================
   CART
   ========================================================================= */

function addToCart($user_id, $product_id, $quantity = 1, $size = null) {
    global $conn;

    // Normalize size to string (empty string if null) for comparison
    $sizeVal = $size === null ? '' : (string)$size;

    // Check if already in cart for same size
    $sql = "SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id AND COALESCE(size, '') = :size";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':size', $sizeVal);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Update quantity for that size
        $sql = "UPDATE cart SET quantity = quantity + :quantity WHERE user_id = :user_id AND product_id = :product_id AND COALESCE(size, '') = :size";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':size', $sizeVal);
        return $stmt->execute();
    } else {
        // Insert new with size
        $sql = "INSERT INTO cart (user_id, product_id, quantity, size) VALUES (:user_id, :product_id, :quantity, :size)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':size', $sizeVal);
        return $stmt->execute();
    }
}

/**
 * Cart items with price/original_price resolved to the product's *current*
 * effective (discounted) price. This is what gets snapshotted into
 * order_items when the order is placed.
 */
function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock, p.discount_percent, p.discount_starts_at, p.discount_ends_at
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($items as &$item) {
        $pricing = getEffectivePrice($item);
        $item['original_price'] = $pricing['original_price'];
        $item['price'] = $pricing['final_price'];
        $item['has_discount'] = $pricing['has_discount'];
        $item['discount_percent_applied'] = $pricing['percent'];
    }
    unset($item);

    return $items;
}

function getCartTotal($user_id) {
    $items = getCartItems($user_id);
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function getShippingCost($division_id) {
    // Division ID 1 = Dhaka: 80 BDT, All others: 130 BDT
    return ($division_id == 1) ? 80 : 130;
}

/* =========================================================================
   COUPON SYSTEM
   ========================================================================= */

function getCouponByCode($code) {
    global $conn;
    $code = strtoupper(trim($code));
    $sql = "SELECT * FROM coupons WHERE code = :code";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':code', $code);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function validateCoupon($code, $user_id, $cart_total) {
    global $conn;

    if (empty($code) || $cart_total <= 0) {
        return ['valid' => false, 'message' => 'Enter a coupon code.', 'coupon' => null, 'discount_amount' => 0];
    }

    $coupon = getCouponByCode($code);

    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid coupon code.', 'coupon' => null, 'discount_amount' => 0];
    }
    if ($coupon['status'] !== 'active') {
        return ['valid' => false, 'message' => 'This coupon is no longer active.', 'coupon' => null, 'discount_amount' => 0];
    }

    $today = date('Y-m-d');
    if (!empty($coupon['starts_at']) && $coupon['starts_at'] > $today) {
        return ['valid' => false, 'message' => 'This coupon is not active yet.', 'coupon' => null, 'discount_amount' => 0];
    }
    if (!empty($coupon['expires_at']) && $coupon['expires_at'] < $today) {
        return ['valid' => false, 'message' => 'This coupon has expired.', 'coupon' => null, 'discount_amount' => 0];
    }
    if ($coupon['usage_limit'] !== null && (int)$coupon['used_count'] >= (int)$coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit.', 'coupon' => null, 'discount_amount' => 0];
    }
    if ($cart_total < (float)$coupon['min_order_amount']) {
        return ['valid' => false, 'message' => 'Minimum order amount for this coupon is ৳' . number_format($coupon['min_order_amount'], 2) . '.', 'coupon' => null, 'discount_amount' => 0];
    }

    $sql = "SELECT COUNT(*) as cnt FROM coupon_usage WHERE coupon_id = :coupon_id AND user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':coupon_id', $coupon['id']);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] > 0) {
        return ['valid' => false, 'message' => 'You have already used this coupon.', 'coupon' => null, 'discount_amount' => 0];
    }

    if ($coupon['discount_type'] === 'percentage') {
        $discount = $cart_total * ((float)$coupon['discount_value'] / 100);
        if (!empty($coupon['max_discount_amount']) && $discount > (float)$coupon['max_discount_amount']) {
            $discount = (float)$coupon['max_discount_amount'];
        }
    } else {
        $discount = (float)$coupon['discount_value'];
    }
    $discount = min($discount, $cart_total);

    return [
        'valid' => true,
        'message' => 'Coupon "' . $coupon['code'] . '" applied — you saved ৳' . number_format($discount, 2) . '.',
        'coupon' => $coupon,
        'discount_amount' => round($discount, 2)
    ];
}

function recordCouponUsage($coupon_id, $user_id, $order_id) {
    global $conn;
    $sql = "INSERT INTO coupon_usage (coupon_id, user_id, order_id) VALUES (:coupon_id, :user_id, :order_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':coupon_id', $coupon_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();

    $sql = "UPDATE coupons SET used_count = used_count + 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $coupon_id);
    $stmt->execute();
}

function getAllCoupons() {
    global $conn;
    $sql = "SELECT * FROM coupons ORDER BY created_at DESC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCouponById($id) {
    global $conn;
    $sql = "SELECT * FROM coupons WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createCoupon($data) {
    global $conn;
    $sql = "INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, starts_at, expires_at, status)
            VALUES (:code, :discount_type, :discount_value, :min_order_amount, :max_discount_amount, :usage_limit, :starts_at, :expires_at, :status)";
    $stmt = $conn->prepare($sql);
    $code = strtoupper(trim($data['code']));
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':discount_type', $data['discount_type']);
    $stmt->bindParam(':discount_value', $data['discount_value']);
    $stmt->bindParam(':min_order_amount', $data['min_order_amount']);
    $stmt->bindParam(':max_discount_amount', $data['max_discount_amount']);
    $stmt->bindParam(':usage_limit', $data['usage_limit']);
    $stmt->bindParam(':starts_at', $data['starts_at']);
    $stmt->bindParam(':expires_at', $data['expires_at']);
    $stmt->bindParam(':status', $data['status']);
    return $stmt->execute();
}

function updateCoupon($id, $data) {
    global $conn;
    $sql = "UPDATE coupons SET
                code = :code,
                discount_type = :discount_type,
                discount_value = :discount_value,
                min_order_amount = :min_order_amount,
                max_discount_amount = :max_discount_amount,
                usage_limit = :usage_limit,
                starts_at = :starts_at,
                expires_at = :expires_at,
                status = :status
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $code = strtoupper(trim($data['code']));
    $stmt->bindParam(':code', $code);
    $stmt->bindParam(':discount_type', $data['discount_type']);
    $stmt->bindParam(':discount_value', $data['discount_value']);
    $stmt->bindParam(':min_order_amount', $data['min_order_amount']);
    $stmt->bindParam(':max_discount_amount', $data['max_discount_amount']);
    $stmt->bindParam(':usage_limit', $data['usage_limit']);
    $stmt->bindParam(':starts_at', $data['starts_at']);
    $stmt->bindParam(':expires_at', $data['expires_at']);
    $stmt->bindParam(':status', $data['status']);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

function deleteCoupon($id) {
    global $conn;
    $sql = "SELECT COUNT(*) as cnt FROM coupon_usage WHERE coupon_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] > 0) {
        return false;
    }
    $sql = "DELETE FROM coupons WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    return $stmt->execute();
}

/* =========================================================================
   DELIVERY TRACKING
   ========================================================================= */

function addTrackingEvent($order_id, $status, $location = null, $note = null) {
    global $conn;
    $sql = "INSERT INTO order_tracking (order_id, status, location, note) VALUES (:order_id, :status, :location, :note)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':location', $location);
    $stmt->bindParam(':note', $note);
    return $stmt->execute();
}

function getTrackingEvents($order_id) {
    global $conn;
    $sql = "SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getOrderByNumberAndPhone($order_number, $phone) {
    global $conn;
    $sql = "SELECT * FROM orders WHERE order_number = :order_number AND phone = :phone";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_number', $order_number);
    $stmt->bindParam(':phone', $phone);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getOrderByNumber($order_number) {
    global $conn;
    $sql = "SELECT * FROM orders WHERE order_number = :order_number";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':order_number', $order_number);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================================================================
   REVIEWS & RATINGS
   ========================================================================= */

/**
 * A customer can review a product if they have at least one DELIVERED order
 * containing it, and haven't reviewed it before (one review per product).
 */
function canReviewProduct($user_id, $product_id) {
    global $conn;

    $sql = "SELECT COUNT(*) as cnt FROM reviews WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    if ($stmt->fetch(PDO::FETCH_ASSOC)['cnt'] > 0) {
        return false; // already reviewed
    }

    $sql = "SELECT COUNT(*) as cnt FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = :user_id AND oi.product_id = :product_id AND o.status = 'delivered'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['cnt'] > 0;
}

function addReview($product_id, $user_id, $rating, $comment) {
    global $conn;

    // Find a delivered order containing this product, to link as verified purchase
    $sql = "SELECT o.id FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.user_id = :user_id AND oi.product_id = :product_id AND o.status = 'delivered'
            ORDER BY o.order_date DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $order_id = $order['id'] ?? null;

    $rating = max(1, min(5, (int)$rating));

    $sql = "INSERT INTO reviews (product_id, user_id, order_id, rating, comment) VALUES (:product_id, :user_id, :order_id, :rating, :comment)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':rating', $rating);
    $stmt->bindParam(':comment', $comment);
    return $stmt->execute();
}

function getProductReviews($product_id) {
    global $conn;
    $sql = "SELECT r.*, u.name as customer_name FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = :product_id AND r.status = 'approved'
            ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getProductRatingSummary($product_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as review_count, AVG(rating) as avg_rating FROM reviews WHERE product_id = :product_id AND status = 'approved'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'count' => (int)($row['review_count'] ?? 0),
        'average' => $row['avg_rating'] !== null ? round((float)$row['avg_rating'], 1) : 0
    ];
}

function getAllReviews() {
    global $conn;
    $sql = "SELECT r.*, u.name as customer_name, p.name as product_name FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC";
    $stmt = $conn->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function setReviewStatus($review_id, $status) {
    global $conn;
    $sql = "UPDATE reviews SET status = :status WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $review_id);
    return $stmt->execute();
}

function deleteReview($review_id) {
    global $conn;
    $sql = "DELETE FROM reviews WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $review_id);
    return $stmt->execute();
}

/* =========================================================================
   WISHLIST
   ========================================================================= */

function isInWishlist($user_id, $product_id) {
    global $conn;
    $sql = "SELECT id FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    $stmt->execute();
    return $stmt->rowCount() > 0;
}

function addToWishlist($user_id, $product_id) {
    global $conn;
    if (isInWishlist($user_id, $product_id)) {
        return true;
    }
    $sql = "INSERT INTO wishlist (user_id, product_id) VALUES (:user_id, :product_id)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    return $stmt->execute();
}

function removeFromWishlist($user_id, $product_id) {
    global $conn;
    $sql = "DELETE FROM wishlist WHERE user_id = :user_id AND product_id = :product_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':product_id', $product_id);
    return $stmt->execute();
}

function getWishlistItems($user_id) {
    global $conn;
    $sql = "SELECT w.id as wishlist_id, w.added_at, p.* FROM wishlist w
            JOIN products p ON w.product_id = p.id
            WHERE w.user_id = :user_id
            ORDER BY w.added_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getWishlistCount($user_id) {
    global $conn;
    $sql = "SELECT COUNT(*) as cnt FROM wishlist WHERE user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return (int)$stmt->fetch(PDO::FETCH_ASSOC)['cnt'];
}

/* =========================================================================
   SESSION RECOVERY (for payment gateway redirects)
   ========================================================================= */

/**
 * Cross-site redirects from a payment gateway (SSLCommerz posting back to
 * success/fail/cancel URLs) often arrive without the session cookie, since
 * browsers restrict cookies on cross-site requests. Rather than depending
 * on that cookie, we re-hydrate the session directly from the DB using the
 * user_id already tied to the (independently validated) order. This is safe
 * because the order lookup itself is keyed off a unique tran_id, and for
 * the success path the transaction is additionally verified against
 * SSLCommerz's Validation API before this is ever called.
 */
function establishUserSession($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

/* =========================================================================
   PAYMENT GATEWAY (simulated)
   ========================================================================= */

function updatePaymentStatus($order_id, $status, $reference = null) {
    global $conn;
    $sql = "UPDATE orders SET payment_status = :status, payment_reference = :reference WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':reference', $reference);
    $stmt->bindParam(':id', $order_id);
    return $stmt->execute();
}

function generateFakeTransactionId($prefix = 'TXN') {
    return strtoupper($prefix) . date('ymdHis') . rand(1000, 9999);
}

/* =========================================================================
   ORDERS
   ========================================================================= */

function placeOrder($user_id, $division_id, $district_id, $upazila_id, $detailed_address, $phone, $coupon_id = null, $coupon_code = null, $discount_amount = 0, $payment_method = 'Cash on Delivery') {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Generate order number
        $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
        $cart_total = getCartTotal($user_id);
        $shipping = getShippingCost($division_id);
        $discount_amount = min((float)$discount_amount, $cart_total);
        $total_amount = $cart_total + $shipping - $discount_amount;
        $payment_status = ($payment_method === 'Cash on Delivery') ? 'unpaid' : 'unpaid';
        
        // Create order
        $sql = "INSERT INTO orders (user_id, order_number, total_amount, coupon_code, discount_amount, division_id, district_id, upazila_id, detailed_address, phone, payment_method, payment_status) 
                VALUES (:user_id, :order_number, :total_amount, :coupon_code, :discount_amount, :division_id, :district_id, :upazila_id, :detailed_address, :phone, :payment_method, :payment_status)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':order_number', $order_number);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':coupon_code', $coupon_code);
        $stmt->bindParam(':discount_amount', $discount_amount);
        $stmt->bindParam(':division_id', $division_id);
        $stmt->bindParam(':district_id', $district_id);
        $stmt->bindParam(':upazila_id', $upazila_id);
        $stmt->bindParam(':detailed_address', $detailed_address);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':payment_method', $payment_method);
        $stmt->bindParam(':payment_status', $payment_status);
        $stmt->execute();
        $order_id = $conn->lastInsertId();
        
        // Add order items
        $cart_items = getCartItems($user_id);
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price, size) 
                VALUES (:order_id, :product_id, :quantity, :price, :size)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':order_id', $order_id);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':price', $item['price']);
            $sizeVal = isset($item['size']) ? $item['size'] : null;
            $stmt->bindParam(':size', $sizeVal);
            $stmt->execute();
            
            // Update product stock
            $sql = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':quantity', $item['quantity']);
            $stmt->bindParam(':product_id', $item['product_id']);
            $stmt->execute();
        }
        
        // Clear cart
        $sql = "DELETE FROM cart WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        // Record coupon redemption
        if ($coupon_id) {
            recordCouponUsage($coupon_id, $user_id, $order_id);
        }

        // Seed the tracking timeline
        addTrackingEvent($order_id, 'pending', null, 'Order placed by customer.');
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}
function getProductImageUrl($image) {
    $fallback = '../images/uploads/logo/logo.png';

    if (empty($image)) {
        return $fallback;
    }

    $base_name = basename($image);
    $root_upload = __DIR__ . '/../uploads/' . $base_name;
    if (file_exists($root_upload)) {
        return '../uploads/' . $base_name;
    }

    $legacy_upload = __DIR__ . '/../images/uploads/products/' . $base_name;
    if (file_exists($legacy_upload)) {
        return '../images/uploads/products/' . $base_name;
    }

    return $fallback;
}
// Admin functions
function getLowStockProducts($threshold = 5) {
    global $conn;
    $sql = "SELECT * FROM products WHERE stock <= :threshold AND status = 'active' ORDER BY stock ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':threshold', $threshold);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesReport($month, $year) {
    global $conn;
    $sql = "SELECT o.*, u.name as customer_name, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE MONTH(o.order_date) = :month AND YEAR(o.order_date) = :year 
            ORDER BY o.order_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':month', $month);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalSales($month, $year) {
    global $conn;
    $sql = "SELECT SUM(total_amount) as total FROM orders 
            WHERE MONTH(order_date) = :month AND YEAR(order_date) = :year";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':month', $month);
    $stmt->bindParam(':year', $year);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

 function categoryIcon($name)
    {
        switch ($name) {
            case 'Bangles':
                return 'fas fa-ring';
            case 'Sarees':
                return 'fas fa-female';
            case 'Panjabi':
                return 'fas fa-male';
            case 'Dress':
                return 'fas fa-tshirt';
            case 'Bags':
                return 'fas fa-shopping-bag';
            default:
                return 'fas fa-box';
        }
    }

function getWhereAmI() {
        return basename($_SERVER['PHP_SELF']);
    }
?>
