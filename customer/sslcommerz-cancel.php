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
        $_SESSION['error'] = "Payment was cancelled. You can retry anytime from your order details.";
        redirect('order-details.php?id=' . $order['id']);
    }
}

redirect('order-history.php');
?>
