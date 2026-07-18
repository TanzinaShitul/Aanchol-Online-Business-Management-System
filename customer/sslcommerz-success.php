<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['val_id']) || empty($_POST['tran_id'])) {
    redirect('order-history.php');
}

$tran_id = $_POST['tran_id'];
$val_id = $_POST['val_id'];

$order = getOrderByNumber($tran_id);
if (!$order) {
    die('Order not found.');
}

// Never trust the posted data alone — re-validate with SSLCommerz directly.
$validation = sslcommerzValidateTransaction($val_id);

$is_valid = $validation
    && in_array($validation['status'] ?? '', ['VALID', 'VALIDATED'], true)
    && round((float)$validation['amount'], 2) === round((float)$order['total_amount'], 2)
    && strtoupper($validation['currency'] ?? '') === 'BDT';

// The session cookie is frequently dropped on this cross-site redirect from
// SSLCommerz's domain back to ours. Since we've just independently verified
// this transaction belongs to a real order, it's safe to re-establish the
// logged-in session from that order's owner rather than relying on the cookie.
if (!isLoggedIn() || $_SESSION['user_id'] != $order['user_id']) {
    establishUserSession($order['user_id']);
}

if ($is_valid) {
    $reference = $validation['bank_tran_id'] ?? $val_id;
    updatePaymentStatus($order['id'], 'paid', $reference);
    addTrackingEvent($order['id'], $order['status'], null, 'SSLCommerz payment received (ref: ' . $reference . ').');
    $_SESSION['order_success'] = $order['order_number'];
    redirect('order-success.php');
} else {
    updatePaymentStatus($order['id'], 'failed', null);
    $_SESSION['error'] = "We couldn't verify your payment with SSLCommerz. Please try again or contact support.";
    redirect('order-details.php?id=' . $order['id']);
}
?>
