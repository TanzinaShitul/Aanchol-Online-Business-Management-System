<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

if (!class_exists('TCPDF')) {
    die('TCPDF is not available. Please run composer install in the project root.');
}

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

// Calculate total sales for the month
$totalSales = 0;
foreach ($sales as $sale) {
    $totalSales += floatval($sale['total_amount']);
}

// Create PDF object
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->setCreator('Aanchol Online Business Management System');
$pdf->setAuthor('Admin');
$pdf->setTitle('Sales Report - ' . $month . '/' . $year);
$pdf->setSubject('Monthly Sales Report');

// Set default header and footer fonts
$pdf->setHeaderFont(['helvetica', '', 10]);
$pdf->setFooterFont(['helvetica', '', 8]);

// Set margins - center the report on page
$pdf->setMargins(25, 15, 25);
$pdf->setHeaderMargin(5);
$pdf->setFooterMargin(10);

// Set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Add a page
$pdf->addPage();

// Set font
$pdf->setFont('helvetica', 'B', 18);
$pdf->cell(0, 10, 'Aanchol', 0, 1, 'C');
$pdf->setFont('helvetica', 'B', 14);
$pdf->cell(0, 8, 'Sales Report - ' . date('F Y', mktime(0, 0, 0, $month, 1, $year)), 0, 1, 'C');
$pdf->ln(5);

// Table header
$pdf->setFont('helvetica', 'B', 9);
$pdf->setFillColor(220, 220, 220);

$w = array(12, 18, 30, 28, 22, 18, 12, 28); // Column widths that total to 168
$h = 7;

$pdf->cell($w[0], $h, 'ID', 1, 0, 'C', true);
$pdf->cell($w[1], $h, 'Order #', 1, 0, 'C', true);
$pdf->cell($w[2], $h, 'Customer', 1, 0, 'C', true);
$pdf->cell($w[3], $h, 'Date', 1, 0, 'C', true);
$pdf->cell($w[4], $h, 'Amount', 1, 0, 'C', true);
$pdf->cell($w[5], $h, 'Status', 1, 0, 'C', true);
$pdf->cell($w[6], $h, 'Items', 1, 0, 'C', true);
$pdf->cell($w[7], $h, 'Phone', 1, 1, 'C', true);

// Table data
$pdf->setFont('helvetica', '', 8);
$pdf->setFillColor(255, 255, 255);

foreach ($sales as $sale) {
    $pdf->cell($w[0], $h, substr($sale['id'], 0, 5), 1, 0, 'C');
    $pdf->cell($w[1], $h, substr($sale['order_number'], 0, 8), 1, 0, 'C');
    $pdf->cell($w[2], $h, substr($sale['customer_name'], 0, 20), 1, 0, 'C');
    $pdf->cell($w[3], $h, substr($sale['order_date'], 0, 16), 1, 0, 'C');
    $pdf->cell($w[4], $h, 'Tk' . number_format($sale['total_amount'], 0), 1, 0, 'C');
    $pdf->cell($w[5], $h, substr($sale['status'], 0, 10), 1, 0, 'C');
    $pdf->cell($w[6], $h, $sale['item_count'], 1, 0, 'C');
    $pdf->cell($w[7], $h, substr($sale['phone'], 0, 12), 1, 1, 'C');
}

// Total Sales row - single centered cell
$pdf->setFont('helvetica', 'B', 10);
$pdf->setFillColor(200, 200, 255);
$totalWidth = array_sum($w);
$pdf->cell($totalWidth, 10, 'Total Monthly Sales: Tk' . number_format($totalSales, 0), 1, 1, 'C', true);

// Output PDF
$pdf->output('sales_report_' . $month . '_' . $year . '.pdf', 'D');
exit;
?>