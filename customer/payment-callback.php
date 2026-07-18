<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$orderNumber = null;
$status = null;
$reference = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_number'], $_POST['result'])) {
    $orderNumber = $_POST['order_number'];
    $status = $_POST['result'] === 'success' ? 'success' : 'failed';
} elseif (isset($_GET['order'], $_GET['status'])) {
    $orderNumber = $_GET['order'];
    $status = $_GET['status'] === 'success' ? 'success' : 'failed';
    $reference = $_GET['tran_id'] ?? null;
}

if (!$orderNumber) {
    redirect('order-history.php');
}

$order = getOrderByNumber($orderNumber);
if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    redirect('order-history.php');
}

if ($status === 'success') {
    $reference = $reference ?: generateFakeTransactionId($order['payment_method']);
    updatePaymentStatus($order['id'], 'paid', $reference);
    addTrackingEvent($order['id'], $order['status'], null, htmlspecialchars($order['payment_method']) . ' payment received (ref: ' . $reference . ').');
    $_SESSION['order_success'] = $order['order_number'];
    redirect('order-success.php');
} else {
    updatePaymentStatus($order['id'], 'failed', null);
    $_SESSION['error'] = 'Payment failed. You can retry payment or contact support.';
    redirect('order-details.php?id=' . $order['id']);
}
?>
