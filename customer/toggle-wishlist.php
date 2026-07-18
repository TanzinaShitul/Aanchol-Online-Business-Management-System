<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $product_id = (int)$_POST['product_id'];

    if (isInWishlist($_SESSION['user_id'], $product_id)) {
        removeFromWishlist($_SESSION['user_id'], $product_id);
        $_SESSION['success'] = "Removed from wishlist.";
    } else {
        addToWishlist($_SESSION['user_id'], $product_id);
        $_SESSION['success'] = "Added to wishlist!";
    }
}

$redirect = $_POST['redirect'] ?? ($_SERVER['HTTP_REFERER'] ?? 'products.php');
redirect($redirect);
?>
