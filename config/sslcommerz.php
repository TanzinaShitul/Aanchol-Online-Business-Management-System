<?php
/**
 * SSLCommerz sandbox configuration.
 *
 * Prefer environment variables for secrets so credentials are not hardcoded
 * into the repository. If the environment variables are not set, the code
 * falls back to the shared demo sandbox credentials so local testing still works.
 */

$sslcz_store_id = getenv('SSL_COMMERZ_STORE_ID') ?: getenv('SSLCZ_STORE_ID') ?: 'testbox';
$sslcz_store_password = getenv('SSL_COMMERZ_STORE_PASSWORD') ?: getenv('SSLCZ_STORE_PASSWORD') ?: 'qwerty';
$sslcz_is_sandbox = getenv('SSL_COMMERZ_IS_SANDBOX') ?: 'true';

$sslcz_is_sandbox = filter_var($sslcz_is_sandbox, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
if ($sslcz_is_sandbox === null) {
    $sslcz_is_sandbox = true;
}

define('SSLCZ_STORE_ID', $sslcz_store_id);
define('SSLCZ_STORE_PASSWORD', $sslcz_store_password);
define('SSLCZ_IS_SANDBOX', $sslcz_is_sandbox);

define('SSLCZ_API_URL', SSLCZ_IS_SANDBOX
    ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
    : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php');

define('SSLCZ_VALIDATION_URL', SSLCZ_IS_SANDBOX
    ? 'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php'
    : 'https://securepay.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php');
?>
