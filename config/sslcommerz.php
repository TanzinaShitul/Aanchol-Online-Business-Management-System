<?php
/**
 * SSLCommerz sandbox configuration.
 *
 * Prefer environment variables for secrets. For XAMPP/local development you
 * may instead create config/sslcommerz.local.php from the supplied example.
 * That local file is ignored by Git and must never be committed.
 */

$sslcz_local = [];
$sslcz_local_file = __DIR__ . '/sslcommerz.local.php';
if (is_file($sslcz_local_file)) {
    $sslcz_local = require $sslcz_local_file;
    if (!is_array($sslcz_local)) {
        throw new RuntimeException('SSLCommerz local configuration must return an array.');
    }
}

$sslcz_store_id = getenv('SSL_COMMERZ_STORE_ID') ?: getenv('SSLCZ_STORE_ID') ?: ($sslcz_local['store_id'] ?? '');
$sslcz_store_password = getenv('SSL_COMMERZ_STORE_PASSWORD') ?: getenv('SSLCZ_STORE_PASSWORD') ?: ($sslcz_local['store_password'] ?? '');
$sslcz_is_sandbox = getenv('SSL_COMMERZ_IS_SANDBOX') ?: ($sslcz_local['is_sandbox'] ?? true);
$sslcz_base_url = getenv('SSL_COMMERZ_BASE_URL') ?: ($sslcz_local['base_url'] ?? '');

$sslcz_is_sandbox = filter_var($sslcz_is_sandbox, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($sslcz_is_sandbox === null) {
    $sslcz_is_sandbox = true;
}

define('SSLCZ_STORE_ID', $sslcz_store_id);
define('SSLCZ_STORE_PASSWORD', $sslcz_store_password);
define('SSLCZ_IS_SANDBOX', $sslcz_is_sandbox);
define('SSLCZ_BASE_URL', rtrim($sslcz_base_url, '/'));

define('SSLCZ_API_URL', SSLCZ_IS_SANDBOX
    ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
    : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php');

define('SSLCZ_VALIDATION_URL', SSLCZ_IS_SANDBOX
    ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
    : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php');
?>
