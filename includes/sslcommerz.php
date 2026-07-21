<?php
require_once __DIR__ . '/../config/sslcommerz.php';

/**
 * Builds the site's absolute base URL (protocol + host + path up to the
 * project root) so SSLCommerz can call back to success/fail/cancel/ipn URLs.
 * Assumes the calling script lives one level under the project root
 * (e.g. /customer/checkout.php -> base is /project-root).
 */
function sslcommerzBaseUrl() {
    if (SSLCZ_BASE_URL !== '') {
        return SSLCZ_BASE_URL;
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') {
        throw new RuntimeException('Unable to determine the SSLCommerz callback URL.');
    }
    $script_dir = dirname($_SERVER['SCRIPT_NAME']); // e.g. /aanchol/customer
    $base = dirname($script_dir);                   // e.g. /aanchol
    $base = ($base === '.' || $base === DIRECTORY_SEPARATOR) ? '' : $base;
    return rtrim($protocol . $host . $base, '/');
}

/**
 * Step 1 of SSLCommerz integration: create a payment session.
 * $order must include: order_number, total_amount
 * $customer must include: name, email, phone, detailed_address
 * Returns the decoded JSON response array from SSLCommerz
 * (look for $response['status'] === 'SUCCESS' and $response['GatewayPageURL']).
 */
function sslcommerzInitSession($order, $customer) {
    if (SSLCZ_STORE_ID === '' || SSLCZ_STORE_PASSWORD === '') {
        return ['status' => 'FAILED', 'failedreason' => 'SSLCommerz sandbox credentials are not configured.'];
    }

    $base_url = sslcommerzBaseUrl();

    $post_data = [
        'store_id'      => SSLCZ_STORE_ID,
        'store_passwd'  => SSLCZ_STORE_PASSWORD,
        'total_amount'  => number_format((float)$order['total_amount'], 2, '.', ''),
        'currency'      => 'BDT',
        'tran_id'       => $order['order_number'],

        'success_url'   => $base_url . '/customer/sslcommerz-success.php',
        'fail_url'      => $base_url . '/customer/sslcommerz-fail.php',
        'cancel_url'    => $base_url . '/customer/sslcommerz-cancel.php',
        'ipn_url'       => $base_url . '/customer/sslcommerz-ipn.php',

        'cus_name'      => $customer['name'],
        'cus_email'     => $customer['email'],
        'cus_add1'      => $customer['detailed_address'] ?: 'N/A',
        'cus_city'      => 'Dhaka',
        'cus_postcode'  => '1000',
        'cus_country'   => 'Bangladesh',
        'cus_phone'     => $customer['phone'],

        'shipping_method'  => 'Courier',
        'num_of_item'      => 1,
        'product_name'     => 'Aanchol Order ' . $order['order_number'],
        'product_category' => 'Fashion',
        'product_profile'  => 'general',

        'ship_name'     => $customer['name'],
        'ship_add1'     => $customer['detailed_address'] ?: 'N/A',
        'ship_city'     => 'Dhaka',
        'ship_postcode' => '1000',
        'ship_country'  => 'Bangladesh',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SSLCZ_API_URL);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    $curl_error = curl_error($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    if (is_array($result) && ($result['status'] ?? '') === 'SUCCESS' && !empty($result['GatewayPageURL'])) {
        return $result;
    }

    return [
        'status' => 'FAILED',
        'failedreason' => $curl_error ?: ($result['failedreason'] ?? 'SSLCommerz did not create a payment session.'),
    ];
}

/**
 * Step 3: server-side validation of a completed transaction.
 * NEVER trust the success_url POST data alone — always confirm with this call.
 */
function sslcommerzValidateTransaction($val_id) {
    $url = SSLCZ_VALIDATION_URL . '?' . http_build_query([
        'val_id'       => $val_id,
        'store_id'     => SSLCZ_STORE_ID,
        'store_passwd' => SSLCZ_STORE_PASSWORD,
        'format'       => 'json',
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return null;
    }
    return json_decode($response, true);
}
?>
