<?php
define('BASEPATH', 'tmp/');
define('APPPATH', 'application/');
require_once 'system/core/Common.php';

log_message('error', 'TEST LOG MESSAGE FROM SCRIPT');
echo "Log message sent.\n";
?>
