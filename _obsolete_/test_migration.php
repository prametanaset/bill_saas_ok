<?php
define('ENVIRONMENT', 'development');
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('BASEPATH', FCPATH . 'system' . DIRECTORY_SEPARATOR);
define('APPPATH', FCPATH . 'application' . DIRECTORY_SEPARATOR);
require_once BASEPATH . 'core/CodeIgniter.php';
$CI =& get_instance();
$CI->load->model('store_model');

$start = microtime(true);
$res = $CI->store_model->provision_tenant_database(999, 'st9999');
$time = microtime(true) - $start;

echo "\nResult: " . ($res !== false ? "Success" : "Failed") . "\n";
echo "Time taken: " . number_format($time, 4) . " seconds\n";
