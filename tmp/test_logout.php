<?php
define('BASEPATH', 'dummy');
function config_item($item) {
    $config = [
        'sess_driver' => 'files',
        'sess_save_path' => NULL,
        'sess_expiration' => 7200
    ];
    return $config[$item] ?? NULL;
}

echo "Testing Logout logic...\n";
$sess_save_path = config_item('sess_save_path');
$sess_expiration = config_item('sess_expiration');

echo "sess_save_path: " . var_export($sess_save_path, true) . "\n";

if (is_null($sess_save_path) || $sess_save_path === '') {
    echo "ERROR: sess_save_path is empty/null. Database delete will fail.\n";
} else {
    echo "Query: DELETE FROM $sess_save_path WHERE timestamp <= " . (time() - $sess_expiration) . "\n";
}
