<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';

// SSLCommerz posts here server-to-server, independent of the customer's
// browser. This URL must be publicly reachable to actually receive calls
// (won't fire on localhost unless tunneled, e.g. via ngrok) — it's an
// optional reliability layer on top of sslcommerz-success.php.

if (empty($_POST['val_id']) || empty($_POST['tran_id'])) {
    http_response_code(400);
    exit('Missing parameters');
}

$order = getOrderByNumber($_POST['tran_id']);
if (!$order) {
    http_response_code(404);
    exit('Order not found');
}

$validation = sslcommerzValidateTransaction($_POST['val_id']);

$is_valid = $validation
    && in_array($validation['status'] ?? '', ['VALID', 'VALIDATED'], true)
    && ($validation['tran_id'] ?? '') === $order['order_number']
    && round((float)$validation['amount'], 2) === round((float)$order['total_amount'], 2);

if ($is_valid && $order['payment_status'] !== 'paid') {
    $reference = $validation['bank_tran_id'] ?? $_POST['val_id'];
    $payment_method = sslcommerzPaymentMethod($validation);
    updatePaymentStatus($order['id'], 'paid', $reference, $payment_method);
    addTrackingEvent($order['id'], $order['status'], null, $payment_method . ' payment confirmed via SSLCommerz IPN (ref: ' . $reference . ').');
}

http_response_code(200);
echo 'OK';
?>
