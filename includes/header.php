<?php
require_once '../config/database.php';
require_once __DIR__ . '/functions.php';
// Use $cart_items if provided by the caller, otherwise fetch from DB when logged in
if (isset($cart_items)) {
    $cart_items_array = $cart_items;
} elseif (function_exists('isLoggedIn') && isLoggedIn() && isset($_SESSION['user_id'])) {
    $cart_items_array = getCartItems($_SESSION['user_id']);
} else {
    $cart_items_array = [];
}
$cartCount = is_array($cart_items_array) ? count($cart_items_array) : 0;
$wishlistCount = (function_exists('isLoggedIn') && isLoggedIn() && isset($_SESSION['user_id'])) ? getWishlistCount($_SESSION['user_id']) : 0;

$currentDir = basename(dirname($_SERVER['PHP_SELF']));
$authLoginHref = ($currentDir === 'customer') ? 'login.php' : 'customer/login.php';
$authRegisterHref = ($currentDir === 'customer') ? 'register.php' : 'customer/register.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php require_once __DIR__ . '/nav.php'; ?>
