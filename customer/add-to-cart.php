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
    
    // Check product stock
    $sql = "SELECT stock FROM products WHERE id = :id AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $product_id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product && $product['stock'] >= $quantity) {
        if (addToCart($_SESSION['user_id'], $product_id, $quantity)) {
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