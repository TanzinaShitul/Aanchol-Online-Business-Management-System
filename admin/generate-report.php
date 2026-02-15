<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');

// Prevent future month selection
if ($year > date('Y') || ($year == date('Y') && $month > date('m'))) {
    die("Cannot generate report for future months!");
}

$sales = getSalesReport($month, $year);

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . $month . '_' . $year . '.csv"');

$output = fopen('php://output', 'w');

// CSV headers
fputcsv($output, [
    'Order ID', 'Order Number', 'Customer Name', 'Order Date', 
    'Total Amount', 'Status', 'Items Count', 'Phone', 'Address'
]);

// Data rows
foreach ($sales as $sale) {
    fputcsv($output, [
        $sale['id'],
        $sale['order_number'],
        $sale['customer_name'],
        $sale['order_date'],
        $sale['total_amount'],
        $sale['status'],
        $sale['item_count'],
        $sale['phone'],
        substr($sale['detailed_address'], 0, 50)
    ]);
}

fclose($output);
exit;
?>