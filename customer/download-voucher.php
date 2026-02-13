<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../tcpdf/tcpdf.php';

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

// ================= ORDER ITEMS =================
$sql = "SELECT oi.*, p.name AS product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id";
$stmt = $conn->prepare($sql);
$stmt->execute([':order_id' => $order_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <td colspan="2"><strong>Address:</strong> ' . $order['detailed_address'] . '</td>
</tr>
</table>

<br>

<table border="1" cellpadding="6" width="100%">
<tr style="background-color:#f2f2f2;">
    <th><b>Product</b></th>
    <th align="center"><b>Price</b></th>
    <th align="center"><b>Qty</b></th>
    <th align="center"><b>Total</b></th>
</tr>';

$subtotal = 0;

foreach ($items as $item) {
    $line_total = $item['price'] * $item['quantity'];
    $subtotal += $line_total;

    $html .= '
    <tr>
        <td>' . $item['product_name'] . '</td>
        <td align="center">BDT ' . number_format($item['price'], 2) . '</td>
        <td align="center">' . $item['quantity'] . '</td>
        <td align="center">BDT ' . number_format($line_total, 2) . '</td>
    </tr>';
}

$html .= '
<tr>
    <td colspan="3" align="right"><strong>Subtotal</strong></td>
    <td align="center">BDT ' . number_format($subtotal, 2) . '</td>
</tr>
<tr>
    <td colspan="3" align="right"><strong>Shipping</strong></td>
    <td align="center">BDT 50.00</td>
</tr>
<tr>
    <td colspan="3" align="right"><strong>Grand Total</strong></td>
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