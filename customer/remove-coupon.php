<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

unset($_SESSION['coupon_code']);
$_SESSION['success'] = "Coupon removed.";

redirect($_SERVER['HTTP_REFERER'] ?? 'cart.php');
?>
