<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/sslcommerz.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['order'])) {
    redirect('order-history.php');
}

$order = getOrderByNumber($_GET['order']);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    redirect('order-history.php');
}

if ($order['payment_status'] === 'paid') {
    redirect('order-details.php?id=' . $order['id']);
}

$sql = "SELECT * FROM users WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$response = sslcommerzInitSession($order, [
    'name' => $user['name'],
    'email' => $user['email'],
    'phone' => $order['phone'],
    'detailed_address' => $order['detailed_address'],
]);

if (isset($response['status']) && $response['status'] === 'SUCCESS' && !empty($response['GatewayPageURL'])) {
    header('Location: ' . $response['GatewayPageURL']);
    exit();
} else {
    $_SESSION['error'] = "Could not start payment: " . ($response['failedreason'] ?? 'Unknown error');
    redirect('order-details.php?id=' . $order['id']);
}
?>
