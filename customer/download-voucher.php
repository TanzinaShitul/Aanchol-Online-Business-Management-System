<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_GET['id'])) {
    die('Invalid request');
}

$order_id = (int) $_GET['id'];

// ================= ORDER INFO =================
$sql = "SELECT o.*, u.name, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        WHERE o.id = :id AND o.user_id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':id' => $order_id,
    ':user_id' => $_SESSION['user_id']
]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die('Order not found');
}

if ($order['payment_method'] === 'Cash on Delivery' || $order['payment_status'] !== 'paid') {
    header('Location: order-details.php?id=' . $order_id);
    exit;
}

// ================= ORDER ITEMS =================
$sql = "SELECT oi.*, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':order_id' => $order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ================= LOAD TCPDF VIA COMPOSER =================
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('<div style="font-family:Arial;padding:20px;"><h2>PDF Library Not Found</h2>' .
         '<p>TCPDF is not installed. To enable PDF voucher downloads, please run:</p>' .
         '<pre style="background:#f4f4f4;padding:10px;border:1px solid #ddd;">' .
         'cd ' . __DIR__ . '/..<br>' .
         'composer install' .
         '</pre>' .
         '<p>If you do not have Composer, download it from: <a href="https://getcomposer.org/download/">https://getcomposer.org/download/</a></p>' .
         '</div>');
}

require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists('TCPDF')) {
    die('TCPDF class could not be loaded. Please run composer install.');
}

// ================= CREATE PDF =================
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Aanchol');
$pdf->SetAuthor('Aanchol');
$pdf->SetTitle('Order Voucher');
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

$html = '
<h2 style="text-align:center;">Aanchol Order Voucher</h2>
<hr>

<table cellpadding="4">
<tr>
    <td><strong>Order Number:</strong> ' . $order['order_number'] . '</td>
    <td align="right"><strong>Date:</strong> ' . date('F d, Y', strtotime($order['order_date'])) . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Name:</strong> ' . $order['name'] . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Phone:</strong> ' . $order['phone'] . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Payment Method:</strong> ' . $order['payment_method'] . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Payment Status:</strong> ' . ucfirst($order['payment_status']) . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Payment Reference:</strong> ' . (!empty($order['payment_reference']) ? htmlspecialchars($order['payment_reference']) : 'N/A') . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Paid Amount:</strong> BDT ' . number_format((float)$order['total_amount'], 2) . '</td>
</tr>
<tr>
    <td colspan="2"><strong>Address:</strong> ' . $order['detailed_address'] . '</td>
</tr>
</table>

<br>

<table border="1" cellpadding="6" width="100%">
<tr style="background-color:#f2f2f2;">
    <th><b>Product</b></th>
    <th align="center"><b>Size</b></th>
    <th align="center"><b>Price</b></th>
    <th align="center"><b>Qty</b></th>
    <th align="center"><b>Total</b></th>
</tr>';

$subtotal = 0;

foreach ($items as $item) {
    $line_total = $item['price'] * $item['quantity'];
    $subtotal += $line_total;
    $size_display = !empty($item['size']) ? $item['size'] : '-';

    $html .= '
    <tr>
        <td>' . $item['product_name'] . '</td>
        <td align="center">' . $size_display . '</td>
        <td align="center">BDT ' . number_format($item['price'], 2) . '</td>
        <td align="center">' . $item['quantity'] . '</td>
        <td align="center">BDT ' . number_format($line_total, 2) . '</td>
    </tr>';
}

$discount_amount = (float)($order['discount_amount'] ?? 0);
$shipping_amount = $order['total_amount'] - $subtotal + $discount_amount;

$html .= '
<tr>
    <td colspan="4" align="right"><strong>Subtotal</strong></td>
    <td align="center">BDT ' . number_format($subtotal, 2) . '</td>
</tr>';

if ($discount_amount > 0) {
    $html .= '
<tr>
    <td colspan="4" align="right"><strong>Coupon (' . htmlspecialchars($order['coupon_code']) . ')</strong></td>
    <td align="center">- BDT ' . number_format($discount_amount, 2) . '</td>
</tr>';
}

$html .= '
<tr>
    <td colspan="4" align="right"><strong>Shipping</strong></td>
    <td align="center">BDT ' . number_format($shipping_amount, 2) . '</td>
</tr>
<tr>
    <td colspan="4" align="right"><strong>Grand Total</strong></td>
    <td align="center"><strong>BDT ' . number_format($order['total_amount'], 2) . '</strong></td>
</tr>
</table>

<br><br>

<p style="text-align:center;">
Thank you for shopping with <strong>Aanchol</strong> <br>
We hope to see you again!
</p>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('order-voucher-' . $order['order_number'] . '.pdf', 'D');
exit;
?>
