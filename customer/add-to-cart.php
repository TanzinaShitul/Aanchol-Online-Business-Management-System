<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_to'] = $_SERVER['HTTP_REFERER'] ?? 'products.php';
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'] ?? 1;
    $size = isset($_POST['size']) ? trim($_POST['size']) : null;
    
    // Check product stock
    $sql = "SELECT p.stock, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = :id AND p.status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product && $product['stock'] >= $quantity) {
        // Validate size based on category
        $cat = strtolower($product['category_name'] ?? '');
        $bangleSizes = ['22','24','26'];
        $clothingSizes = ['M','L','XL','XXL'];

        if (in_array($cat, ['bangles'])) {
            if (empty($size) || !in_array($size, $bangleSizes)) {
                $_SESSION['error'] = "Please select a valid bangle size.";
                redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
            }
        } elseif (in_array($cat, ['dress','panjabi'])) {
            if (empty($size) || !in_array(strtoupper($size), $clothingSizes)) {
                $_SESSION['error'] = "Please select a valid size (M, L, XL, XXL).";
                redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
            }
            $size = strtoupper($size);
        } else {
            $size = null;
        }

        if (addToCart($_SESSION['user_id'], $product_id, $quantity, $size)) {
            $_SESSION['success'] = "Product added to cart!";
        } else {
            $_SESSION['error'] = "Failed to add to cart!";
        }
    } else {
        $_SESSION['error'] = "Product is out of stock or quantity not available!";
    }
    
    redirect($_SERVER['HTTP_REFERER'] ?? 'products.php');
} else {
    redirect('products.php');
}
?>