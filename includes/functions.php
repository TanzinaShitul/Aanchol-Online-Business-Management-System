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

function getCartItems($user_id) {
    global $conn;
    $sql = "SELECT c.*, p.name, p.price, p.image, p.stock FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

function placeOrder($user_id, $division_id, $district_id, $upazila_id, $detailed_address, $phone) {
    global $conn;
    
    try {
        $conn->beginTransaction();
        
        // Generate order number
        $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
        $cart_total = getCartTotal($user_id);
        $shipping = getShippingCost($division_id);
        $total_amount = $cart_total + $shipping;
        
        // Create order
        $sql = "INSERT INTO orders (user_id, order_number, total_amount, division_id, district_id, upazila_id, detailed_address, phone) 
                VALUES (:user_id, :order_number, :total_amount, :division_id, :district_id, :upazila_id, :detailed_address, :phone)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':order_number', $order_number);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':division_id', $division_id);
        $stmt->bindParam(':district_id', $district_id);
        $stmt->bindParam(':upazila_id', $upazila_id);
        $stmt->bindParam(':detailed_address', $detailed_address);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        $order_id = $conn->lastInsertId();
        
        // Add order items
        $cart_items = getCartItems($user_id);
        foreach ($cart_items as $item) {
            // Persist size with order item (if present)
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
        
        $conn->commit();
        return $order_id;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
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