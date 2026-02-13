<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('login.php');
}

$current_month = date('m');
$current_year = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/uploads/logo/logo.png" type="image/png">
    <title>আঞ্চল-Aanchol - Sales Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
    
            <main class="col-md-10 ms-sm-auto px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                 <h1 class="h2">Sales Reports</h1>
                </div>
                <div class="card">
                <div class="card-header">
                    <h5>Generate Monthly Report</h5>
                </div>
            <div class="card-body">
                <form method="GET" action="generate-report.php" class="row g-3">
                    <div class="col-md-4">
                        <label for="month" class="form-label">Month</label>
                        <select class="form-control" id="month" name="month" required>
                            <?php for ($i = 1; $i <= 12; $i++): 
                                $selected = ($i == $current_month) ? 'selected' : '';
                                $month_name = date("F", mktime(0, 0, 0, $i, 10));
                            ?>
                                <option value="<?= $i ?>" <?= $selected ?>><?= $month_name ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="year" class="form-label">Year</label>
                        <select class="form-control" id="year" name="year" required>
                            <?php 
                            $start_year = 2023;
                            $end_year = $current_year;
                            for ($year = $start_year; $year <= $end_year; $year++): 
                                $selected = ($year == $current_year) ? 'selected' : '';
                            ?>
                                <option value="<?= $year ?>" <?= $selected ?>><?= $year ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-download"></i> Download CSV Report
                        </button>
                    </div>
                </form>

                <!-- Show selected month's summary -->
                <?php
                if (isset($_GET['month']) && isset($_GET['year'])) {
                    $month = $_GET['month'];
                    $year = $_GET['year'];
                    
                    if ($year > $current_year || ($year == $current_year && $month > $current_month)) {
                        echo '<div class="alert alert-danger mt-3">Cannot generate report for future months!</div>';
                    } else {
                        $sales = getSalesReport($month, $year);
                        $total_sales = getTotalSales($month, $year);
                ?>
                        <div class="mt-4">
                            <h5>Report Summary for <?= date("F Y", mktime(0, 0, 0, $month, 1, $year)) ?></h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Total Orders</h6>
                                            <h3><?= count($sales) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Total Revenue</h6>
                                            <h3>৳<?= number_format($total_sales, 2) ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Average Order Value</h6>
                                            <h3>৳<?= count($sales) > 0 ? number_format($total_sales / count($sales), 2) : '0.00' ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
            </div>
        </main>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>