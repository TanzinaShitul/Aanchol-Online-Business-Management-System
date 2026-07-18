<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['coupon_code'])) {
    $code = trim($_POST['coupon_code']);
    $cart_total = getCartTotal($_SESSION['user_id']);

    if ($cart_total <= 0) {
        $_SESSION['error'] = "Your cart is empty.";
        redirect($_SERVER['HTTP_REFERER'] ?? 'cart.php');
    }

    $result = validateCoupon($code, $_SESSION['user_id'], $cart_total);

    if ($result['valid']) {
        $_SESSION['coupon_code'] = strtoupper(trim($code));
        $_SESSION['success'] = $result['message'];
    } else {
        unset($_SESSION['coupon_code']);
        $_SESSION['error'] = $result['message'];
    }
}

redirect($_SERVER['HTTP_REFERER'] ?? 'cart.php');
?>
