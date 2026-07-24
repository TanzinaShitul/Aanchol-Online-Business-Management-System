<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$tran_id = $_POST['tran_id'] ?? $_GET['tran_id'] ?? null;

if ($tran_id) {
    $order = getOrderByNumber($tran_id);
    if ($order) {
        if (!isLoggedIn() || $_SESSION['user_id'] != $order['user_id']) {
            establishUserSession($order['user_id']);
        }
        if (restoreFailedOnlineOrder($order['id'])) {
            if (!empty($order['coupon_code'])) {
                $_SESSION['coupon_code'] = $order['coupon_code'];
            }
            $_SESSION['error'] = 'Payment failed. Your cart has been restored; please choose a payment method and try again.';
            redirect('checkout.php');
        }

        $_SESSION['error'] = 'We could not restore your checkout after the failed payment. Please contact support.';
        redirect('order-details.php?id=' . $order['id']);
    }
}

redirect('order-history.php');
?>
