<?php
$db_name = 'bill_xml_st_test_hang';
$host = 'localhost';
$user = 'root';
$pass = ''; // Assuming blank for Laragon default

$mysqli = new mysqli($host, $user, $pass);
$mysqli->query("DROP DATABASE IF EXISTS `{$db_name}`");
$mysqli->query("CREATE DATABASE `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->close();

$sql_file = 'E:/laragon-8-xml/www/bill_xml_saas/bill_xml.sql';

$start = microtime(true);
$command = 'cmd /c "E:\laragon-8-xml\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe -u root ' . escapeshellarg($db_name) . ' < ' . escapeshellarg($sql_file) . '"';

exec($command, $output, $return_var);

echo "Finished in " . round(microtime(true) - $start, 4) . " seconds.\n";
echo "Output: \n" . implode("\n", $output) . "\n";
echo "Return Var: $return_var\n";
