<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Product ID required']);
    exit;
}

$product_id = $_GET['id'];
$sql = "SELECT stock FROM products WHERE id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $product_id);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if ($product) {
    echo json_encode(['stock' => $product['stock']]);
} else {
    echo json_encode(['error' => 'Product not found']);
}
?>